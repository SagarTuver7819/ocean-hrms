<?php

namespace App\Services;

use App\Core\Database;

/**
 * Monthly salary register from decided salary + attendance.
 * Interns excluded by default (optional include). LOP = pre-join + absent + unpaid leave.
 */
class SalaryRegister
{
    /**
     * @return array{month:string,label:string,include_interns:bool,rows:array,totals:array}
     */
    public static function build(string $month, bool $includeInterns = false): array
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }
        $first = $month . '-01';
        $last = date('Y-m-t', strtotime($first));
        $asOf = min($last, date('Y-m-d'));
        $daysInMonth = (int) date('t', strtotime($first));

        $sql = "SELECT e.id, e.employee_code, e.first_name, e.last_name, e.joining_date, e.employment_type, e.status,
                       d.name AS department_name, g.name AS designation_name, s.name AS shift_name
                FROM employees e
                LEFT JOIN departments d ON d.id = e.department_id
                LEFT JOIN designations g ON g.id = e.designation_id
                LEFT JOIN shifts s ON s.id = e.shift_id
                WHERE e.status = 'active'";
        if (!$includeInterns) {
            $sql .= " AND e.employment_type <> 'intern'";
        }
        $sql .= ' ORDER BY e.first_name, e.last_name';
        $employees = Database::all($sql);

        $rows = [];
        $totals = [
            'employees' => 0,
            'with_salary' => 0,
            'interns' => 0,
            'decided' => 0.0,
            'present' => 0.0,
            'absent' => 0.0,
            'half_day' => 0.0,
            'paid_leave' => 0.0,
            'unpaid_leave' => 0.0,
            'lop' => 0.0,
            'lop_amount' => 0.0,
            'pt' => 0.0,
            'net' => 0.0,
        ];

        foreach ($employees as $emp) {
            $empId = (int) $emp['id'];
            $isIntern = ($emp['employment_type'] ?? '') === 'intern';
            $salaryRow = Salary::current($empId);
            $monthly = Payslip::monthlySalaryFor($empId, $last);
            $pt = $monthly > 0 ? Salary::ptAmount($empId, $salaryRow) : 0.0;
            $att = self::attendanceForMonth($empId, $first, $last, $asOf, (string) ($emp['joining_date'] ?? ''));

            $preJoin = 0;
            if (!empty($emp['joining_date']) && $emp['joining_date'] > $first && $emp['joining_date'] <= $last) {
                $preJoin = (int) ((strtotime($emp['joining_date']) - strtotime($first)) / 86400);
            }

            $lop = round(min($daysInMonth, $preJoin + $att['absent'] + $att['unpaid_leave']), 1);
            $perDay = $daysInMonth > 0 ? $monthly / $daysInMonth : 0;
            $lopAmount = $monthly > 0 ? round($perDay * $lop) : 0;
            $net = $monthly > 0 ? max(0, round($monthly - $lopAmount - $pt)) : 0;

            $row = [
                'employee_id' => $empId,
                'employee_code' => $emp['employee_code'],
                'name' => trim($emp['first_name'] . ' ' . $emp['last_name']),
                'department_name' => $emp['department_name'] ?? '—',
                'designation_name' => $emp['designation_name'] ?? '',
                'shift_name' => $emp['shift_name'] ?? '—',
                'employment_type' => $emp['employment_type'] ?? 'full_time',
                'is_intern' => $isIntern,
                'joining_date' => $emp['joining_date'],
                'monthly_salary' => $monthly,
                'has_salary' => $monthly > 0,
                'professional_tax' => $pt,
                'days_in_month' => $daysInMonth,
                'working_days' => $att['working_days'],
                'present' => $att['present'],
                'half_day' => $att['half_day'],
                'paid_leave' => $att['paid_leave'],
                'unpaid_leave' => $att['unpaid_leave'],
                'absent' => $att['absent'],
                'pre_join' => $preJoin,
                'lop' => $lop,
                'paid_days' => round($daysInMonth - $lop, 1),
                'lop_amount' => $lopAmount,
                'net' => $net,
            ];

            $rows[] = $row;
            $totals['employees']++;
            $totals['interns'] += $isIntern ? 1 : 0;
            if ($monthly > 0) {
                $totals['with_salary']++;
                $totals['decided'] += $monthly;
                $totals['lop_amount'] += $lopAmount;
                $totals['pt'] += $pt;
                $totals['net'] += $net;
            }
            $totals['present'] += $att['present'];
            $totals['absent'] += $att['absent'];
            $totals['half_day'] += $att['half_day'];
            $totals['paid_leave'] += $att['paid_leave'];
            $totals['unpaid_leave'] += $att['unpaid_leave'];
            $totals['lop'] += $lop;
        }

        return [
            'month' => $month,
            'label' => date('F Y', strtotime($first)),
            'as_of' => $asOf,
            'days_in_month' => $daysInMonth,
            'include_interns' => $includeInterns,
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    /** Attendance + leave for salary (displayStatus so intern 4h / half-day stay correct). */
    public static function attendanceForMonth(int $empId, string $first, string $last, string $asOf, string $joiningDate): array
    {
        $from = $joiningDate && $joiningDate > $first ? $joiningDate : $first;
        $workingDates = $from <= $asOf ? Leave::workingDates($empId, $from, $asOf) : [];
        $shift = Attendance::shiftFor($empId);

        $byDate = [];
        foreach (Database::all(
            'SELECT * FROM attendance WHERE employee_id = ? AND attendance_date BETWEEN ? AND ?',
            [$empId, $first, $last]
        ) as $r) {
            $byDate[$r['attendance_date']] = $r;
        }

        $leaveDates = []; // date => is_paid
        $paidLeave = $unpaidLeave = 0.0;
        $leaves = Database::all(
            "SELECT lr.from_date, lr.to_date, lr.days, lt.is_paid
             FROM leave_requests lr
             JOIN leave_types lt ON lt.id = lr.leave_type_id
             WHERE lr.employee_id = ? AND lr.status = 'approved' AND lr.from_date <= ? AND lr.to_date >= ?",
            [$empId, $last, $first]
        );
        foreach ($leaves as $l) {
            $rangeFrom = max($l['from_date'], $first, $from);
            $rangeTo = min($l['to_date'], $asOf);
            if ($rangeTo < $rangeFrom) {
                continue;
            }
            $half = (float) $l['days'] === 0.5 && $l['from_date'] === $l['to_date'];
            $days = Leave::countDays($empId, $rangeFrom, $rangeTo, $half);
            if ($l['is_paid']) {
                $paidLeave += $days;
            } else {
                $unpaidLeave += $days;
            }
            foreach (Leave::workingDates($empId, $rangeFrom, $rangeTo) as $ld) {
                $leaveDates[$ld] = (bool) $l['is_paid'];
            }
        }

        $present = $halfDay = $absent = 0.0;
        foreach ($workingDates as $d) {
            if (isset($leaveDates[$d])) {
                continue;
            }
            $r = $byDate[$d] ?? null;
            if ($r && !empty($r['punch_in'])) {
                $status = Attendance::displayStatus($r, $shift, $empId, empty($r['punch_out']));
                if ($status === 'half_day') {
                    $present += 0.5;
                    $halfDay += 1;
                    $absent += 0.5;
                } elseif ($status === 'absent') {
                    $absent += 1;
                } else {
                    $present += 1;
                }
                continue;
            }
            $absent += 1;
        }

        return [
            'working_days' => count($workingDates),
            'present' => round($present, 1),
            'half_day' => round($halfDay, 1),
            'absent' => round($absent, 1),
            'paid_leave' => round($paidLeave, 1),
            'unpaid_leave' => round($unpaidLeave, 1),
        ];
    }
}
