<?php

namespace App\Services;

use App\Core\Database;

/**
 * Monthly salary register (accounts-style):
 * Gross = Decided × (P.Days + W.Off + Holiday + Leave) ÷ month days
 * Deduction = Professional Tax only · Net = Gross − PT
 * Interns excluded by default.
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
                       d.name AS department_name, g.name AS designation_name, s.name AS shift_name,
                       b.bank_name, b.account_number, b.ifsc_code, b.account_holder_name
                FROM employees e
                LEFT JOIN departments d ON d.id = e.department_id
                LEFT JOIN designations g ON g.id = e.designation_id
                LEFT JOIN shifts s ON s.id = e.shift_id
                LEFT JOIN employee_bank_details b ON b.employee_id = e.id
                WHERE e.status = 'active'";
        if (!$includeInterns) {
            $sql .= " AND e.employment_type <> 'intern'";
        }
        $sql .= ' ORDER BY d.name, e.first_name, e.last_name';
        $employees = Database::all($sql);

        $rows = [];
        $totals = [
            'employees' => 0,
            'with_salary' => 0,
            'interns' => 0,
            'decided' => 0.0,
            'present' => 0.0,
            'week_off' => 0.0,
            'holiday' => 0.0,
            'leave' => 0.0,
            'total_days' => 0.0,
            'gross' => 0.0,
            'pt' => 0.0,
            'net' => 0.0,
            'absent' => 0.0,
        ];

        foreach ($employees as $emp) {
            $empId = (int) $emp['id'];
            $isIntern = ($emp['employment_type'] ?? '') === 'intern';
            $salaryRow = Salary::current($empId);
            $monthly = Payslip::monthlySalaryFor($empId, $last);
            $pt = $monthly > 0 ? Salary::ptAmount($empId, $salaryRow) : 0.0;
            $att = self::dayBreakdown($empId, $first, $last, $asOf, (string) ($emp['joining_date'] ?? ''));

            // Paid earning days for Gross (Sick/unpaid leave not included — unpaid)
            $totalDays = round(
                $att['present'] + $att['week_off'] + $att['holiday'] + $att['leave'],
                1
            );
            $gross = $monthly > 0 && $daysInMonth > 0
                ? round($monthly * $totalDays / $daysInMonth, 2)
                : 0.0;
            // PT only when there is gross payable
            $ptApply = $gross > 0 ? $pt : 0.0;
            $net = max(0, round($gross - $ptApply, 2));

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
                'present' => $att['present'],
                'week_off' => $att['week_off'],
                'holiday' => $att['holiday'],
                'leave' => $att['leave'],
                'sick_leave' => $att['sick_leave'],
                'absent' => $att['absent'],
                'total_days' => $totalDays,
                'gross' => $gross,
                'professional_tax' => $ptApply,
                'pt_configured' => $pt,
                'net' => $net,
                'bank_name' => $emp['bank_name'] ?? '',
                'account_number' => $emp['account_number'] ?? '',
                'ifsc_code' => $emp['ifsc_code'] ?? '',
                'account_holder_name' => $emp['account_holder_name'] ?? '',
            ];

            $rows[] = $row;
            $totals['employees']++;
            $totals['interns'] += $isIntern ? 1 : 0;
            $totals['present'] += $att['present'];
            $totals['week_off'] += $att['week_off'];
            $totals['holiday'] += $att['holiday'];
            $totals['leave'] += $att['leave'];
            $totals['total_days'] += $totalDays;
            $totals['absent'] += $att['absent'];
            if ($monthly > 0) {
                $totals['with_salary']++;
                $totals['decided'] += $monthly;
                $totals['gross'] += $gross;
                $totals['pt'] += $ptApply;
                $totals['net'] += $net;
            }
        }

        return [
            'month' => $month,
            'label' => date('F Y', strtotime($first)),
            'as_of' => $asOf,
            'days_in_month' => $daysInMonth,
            'include_interns' => $includeInterns,
            'rows' => $rows,
            'totals' => $totals,
            'formula' => 'Gross = Decided Salary × (P.Days + W.Off + Holiday + Leave) ÷ month days · Net = Gross − PT',
        ];
    }

    /**
     * Calendar breakdown for salary:
     * present / week_off / holiday / leave (paid) / sick_leave (unpaid) / absent
     */
    public static function dayBreakdown(int $empId, string $first, string $last, string $asOf, string $joiningDate): array
    {
        $from = $joiningDate && $joiningDate > $first ? $joiningDate : $first;
        if ($from > $asOf) {
            return [
                'present' => 0.0, 'week_off' => 0.0, 'holiday' => 0.0,
                'leave' => 0.0, 'sick_leave' => 0.0, 'absent' => 0.0,
                'working_days' => 0,
            ];
        }

        $shift = Attendance::shiftFor($empId);
        $weekOffs = Attendance::weekOffs($shift);
        $halfWeekOffs = Attendance::halfWeekOffs($shift);
        $holidays = array_flip(Leave::holidayDatesBetween($from, $asOf));

        $byDate = [];
        foreach (Database::all(
            'SELECT * FROM attendance WHERE employee_id = ? AND attendance_date BETWEEN ? AND ?',
            [$empId, $first, $last]
        ) as $r) {
            $byDate[$r['attendance_date']] = $r;
        }

        // Approved leave map: date => 'paid'|'sick'
        $leaveMap = [];
        $leaves = Database::all(
            "SELECT lr.from_date, lr.to_date, lr.days, lt.is_paid, lt.code
             FROM leave_requests lr
             JOIN leave_types lt ON lt.id = lr.leave_type_id
             WHERE lr.employee_id = ? AND lr.status = 'approved' AND lr.from_date <= ? AND lr.to_date >= ?",
            [$empId, $asOf, $from]
        );
        foreach ($leaves as $l) {
            $rangeFrom = max($l['from_date'], $from);
            $rangeTo = min($l['to_date'], $asOf);
            if ($rangeTo < $rangeFrom) {
                continue;
            }
            $half = (float) $l['days'] === 0.5 && $l['from_date'] === $l['to_date'];
            foreach (Leave::workingDates($empId, $rangeFrom, $rangeTo) as $ld) {
                $leaveMap[$ld] = [
                    'paid' => (bool) $l['is_paid'],
                    'weight' => ($half && $ld === $l['from_date'] && $l['from_date'] === $l['to_date'])
                        ? 0.5
                        : Leave::dayWeight($empId, $ld),
                ];
            }
        }

        $present = $weekOff = $holiday = $leave = $sick = $absent = 0.0;
        $workingDayCount = 0;

        for ($t = strtotime($from); $t <= strtotime($asOf); $t += 86400) {
            $d = date('Y-m-d', $t);
            $w = (int) date('w', $t);
            $isWeekOff = in_array($w, $weekOffs, true);
            $isHalfOff = in_array($w, $halfWeekOffs, true);
            $isHoliday = isset($holidays[$d]);

            if ($isWeekOff) {
                $weekOff += 1.0;
                continue;
            }
            if ($isHoliday) {
                $holiday += 1.0;
                continue;
            }
            if ($isHalfOff) {
                // Half week-off day: 0.5 off + remaining half can be present/leave/absent
                $weekOff += 0.5;
                $workingDayCount += 0.5;
                if (isset($leaveMap[$d])) {
                    $wLeave = min(0.5, (float) $leaveMap[$d]['weight']);
                    if ($leaveMap[$d]['paid']) {
                        $leave += $wLeave;
                    } else {
                        $sick += $wLeave;
                    }
                    continue;
                }
                $r = $byDate[$d] ?? null;
                if ($r && !empty($r['punch_in'])) {
                    $status = Attendance::displayStatus($r, $shift, $empId, empty($r['punch_out']));
                    if ($status === 'half_day' || $status === 'present' || $status === 'late') {
                        // Cap credit for half-off day at 0.5 present for salary share of that day
                        $present += ($status === 'half_day') ? 0.25 : 0.5;
                    } elseif ($status === 'absent') {
                        $absent += 0.5;
                    }
                } else {
                    $absent += 0.5;
                }
                continue;
            }

            // Full working day
            $workingDayCount++;
            if (isset($leaveMap[$d])) {
                $wLeave = (float) $leaveMap[$d]['weight'];
                if ($leaveMap[$d]['paid']) {
                    $leave += $wLeave;
                } else {
                    $sick += $wLeave;
                    // unpaid leave does not earn salary — treat remainder as absent if half
                    if ($wLeave < 1) {
                        $absent += (1 - $wLeave);
                    }
                }
                continue;
            }

            $r = $byDate[$d] ?? null;
            if ($r && !empty($r['punch_in'])) {
                $status = Attendance::displayStatus($r, $shift, $empId, empty($r['punch_out']));
                if ($status === 'half_day') {
                    $present += 0.5;
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
            'present' => round($present, 1),
            'week_off' => round($weekOff, 1),
            'holiday' => round($holiday, 1),
            'leave' => round($leave, 1),
            'sick_leave' => round($sick, 1),
            'absent' => round($absent, 1),
            'working_days' => (int) round($workingDayCount),
        ];
    }

    /** @deprecated use dayBreakdown — kept for Payslip compatibility */
    public static function attendanceForMonth(int $empId, string $first, string $last, string $asOf, string $joiningDate): array
    {
        $b = self::dayBreakdown($empId, $first, $last, $asOf, $joiningDate);
        return [
            'working_days' => $b['working_days'],
            'present' => $b['present'],
            'half_day' => 0.0,
            'absent' => $b['absent'],
            'paid_leave' => $b['leave'],
            'unpaid_leave' => $b['sick_leave'],
        ];
    }
}
