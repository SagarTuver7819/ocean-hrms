<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Employee;

/**
 * Monthly salary slip built from the fixed monthly salary and the month's attendance.
 * Loss of pay = days before joining + absent days (half day = 0.5) + approved unpaid leave.
 */
class Payslip
{
    /** Completed months (newest first) for which a slip can be generated. */
    public static function months(int $employeeId): array
    {
        $joining = Database::value('SELECT joining_date FROM employees WHERE id = ?', [$employeeId]);
        $first = Database::value('SELECT MIN(effective_from) FROM salary_histories WHERE employee_id = ?', [$employeeId])
            ?: Database::value('SELECT effective_from FROM employee_salaries WHERE employee_id = ?', [$employeeId]);
        if (!$first && !Salary::current($employeeId)) {
            return [];
        }
        $start = date('Y-m', strtotime(max((string) $joining, (string) $first) ?: date('Y-m-d')));
        $end = date('Y-m', strtotime('first day of last month'));

        $months = [];
        for ($m = $end; $m >= $start; $m = date('Y-m', strtotime($m . '-01 -1 month'))) {
            $months[] = $m;
        }
        return $months;
    }

    public static function monthlySalaryFor(int $employeeId, string $monthEnd): float
    {
        $hist = Database::value(
            'SELECT monthly_salary FROM salary_histories WHERE employee_id = ? AND effective_from <= ? ORDER BY effective_from DESC, id DESC LIMIT 1',
            [$employeeId, $monthEnd]
        );
        return (float) ($hist ?? Salary::current($employeeId)['monthly_salary'] ?? 0);
    }

    public static function build(int $employeeId, string $month): ?array
    {
        if (!in_array($month, self::months($employeeId), true)) {
            return null;
        }
        $emp = (new Employee())->fullProfile($employeeId);
        $first = $month . '-01';
        $last = date('Y-m-t', strtotime($first));
        $daysInMonth = (int) date('t', strtotime($first));
        $monthly = self::monthlySalaryFor($employeeId, $last);

        $preJoin = 0;
        if ($emp['joining_date'] > $first && $emp['joining_date'] <= $last) {
            $preJoin = (int) ((strtotime($emp['joining_date']) - strtotime($first)) / 86400);
        }
        $from = max($first, (string) $emp['joining_date']);

        $present = $absent = 0.0;
        foreach (Database::all('SELECT status, punch_in FROM attendance WHERE employee_id = ? AND attendance_date BETWEEN ? AND ?', [$employeeId, $first, $last]) as $r) {
            if ($r['status'] === 'absent') $absent += 1;
            elseif ($r['status'] === 'half_day') { $absent += 0.5; $present += 0.5; }
            elseif ($r['punch_in']) $present += 1;
        }

        $paidLeave = $unpaidLeave = 0.0;
        $leaves = Database::all(
            "SELECT lr.from_date, lr.to_date, lr.days, lt.is_paid FROM leave_requests lr JOIN leave_types lt ON lt.id = lr.leave_type_id
             WHERE lr.employee_id = ? AND lr.status = 'approved' AND lr.from_date <= ? AND lr.to_date >= ?",
            [$employeeId, $last, $first]
        );
        foreach ($leaves as $l) {
            $days = (float) $l['days'] === 0.5 ? 0.5 : count(Leave::workingDates($employeeId, max($l['from_date'], $first), min($l['to_date'], $last)));
            $l['is_paid'] ? $paidLeave += $days : $unpaidLeave += $days;
        }

        $workingDays = $from <= $last ? count(Leave::workingDates($employeeId, $from, $last)) : 0;
        $lop = min($daysInMonth, $preJoin + $absent + $unpaidLeave);
        $lopAmount = round($monthly / $daysInMonth * $lop);

        return [
            'month' => $month,
            'label' => date('F Y', strtotime($first)),
            'employee' => $emp,
            'monthly' => $monthly,
            'days_in_month' => $daysInMonth,
            'working_days' => $workingDays,
            'offs' => max(0, $daysInMonth - $preJoin - $workingDays),
            'present' => $present,
            'paid_leave' => $paidLeave,
            'unpaid_leave' => $unpaidLeave,
            'absent' => $absent,
            'pre_join' => $preJoin,
            'lop' => $lop,
            'paid_days' => $daysInMonth - $lop,
            'lop_amount' => $lopAmount,
            'net' => max(0, $monthly - $lopAmount),
        ];
    }
}
