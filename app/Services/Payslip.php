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

        $asOf = min($last, date('Y-m-d'));
        $att = SalaryRegister::attendanceForMonth($employeeId, $first, $last, $asOf, (string) ($emp['joining_date'] ?? ''));
        $present = $att['present'];
        $absent = $att['absent'];
        $paidLeave = $att['paid_leave'];
        $unpaidLeave = $att['unpaid_leave'];

        $workingDays = $att['working_days'];
        $lop = min($daysInMonth, $preJoin + $absent + $unpaidLeave);
        $lopAmount = $monthly > 0 ? round($monthly / $daysInMonth * $lop) : 0;
        $pt = $monthly > 0 ? Salary::ptAmount($employeeId) : 0.0;
        $totalDeduction = $lopAmount + $pt;

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
            'professional_tax' => $pt,
            'total_deduction' => $totalDeduction,
            'net' => max(0, $monthly - $totalDeduction),
        ];
    }
}
