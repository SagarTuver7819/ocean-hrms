<?php

namespace App\Services;

use App\Core\Database;

/**
 * Leave balances. Types with a monthly credit (company policy: 1 paid leave per month)
 * earn that credit for every month from January — or the joining month — up to the current month.
 * Balances reset each calendar year.
 */
class Leave
{
    public static function creditedMonths(?string $joiningDate, int $year): int
    {
        $currentYear = (int) date('Y');
        if ($year > $currentYear) return 0;
        $first = 1;
        if ($joiningDate) {
            $joinYear = (int) date('Y', strtotime($joiningDate));
            if ($joinYear > $year) return 0;
            if ($joinYear === $year) $first = (int) date('n', strtotime($joiningDate));
        }
        $last = $year < $currentYear ? 12 : (int) date('n');
        return max(0, $last - $first + 1);
    }

    public static function syncBalances(int $employeeId, ?int $year = null): void
    {
        $year ??= (int) date('Y');
        $months = self::creditedMonths(Database::value('SELECT joining_date FROM employees WHERE id = ?', [$employeeId]), $year);
        $types = Database::all('SELECT * FROM leave_types WHERE status = 1 AND (monthly_accrual > 0 OR days_per_year > 0)');
        foreach ($types as $t) {
            $allocated = (float) $t['monthly_accrual'] > 0 ? $months * (float) $t['monthly_accrual'] : (float) $t['days_per_year'];
            Database::run(
                'INSERT INTO leave_balances (employee_id, leave_type_id, year, allocated, used, pending) VALUES (?, ?, ?, ?, 0, 0)
                 ON DUPLICATE KEY UPDATE allocated = VALUES(allocated)',
                [$employeeId, $t['id'], $year, $allocated]
            );
        }
    }

    /** Active leave types with this year's balance; types without a balance (e.g. unpaid) have available = null. */
    public static function balances(int $employeeId, ?int $year = null): array
    {
        $year ??= (int) date('Y');
        self::syncBalances($employeeId, $year);
        $rows = Database::all(
            'SELECT lt.id AS leave_type_id, lt.name, lt.code, lt.is_paid, lt.monthly_accrual, lt.days_per_year,
                    lb.allocated, lb.used, lb.pending
             FROM leave_types lt
             LEFT JOIN leave_balances lb ON lb.leave_type_id = lt.id AND lb.employee_id = ? AND lb.year = ?
             WHERE lt.status = 1
             ORDER BY lt.monthly_accrual DESC, lt.is_paid DESC, lt.name',
            [$employeeId, $year]
        );
        foreach ($rows as &$r) {
            $tracked = (float) $r['monthly_accrual'] > 0 || (float) $r['days_per_year'] > 0;
            $r['tracked'] = $tracked;
            $r['available'] = $tracked ? max(0, (float) $r['allocated'] - (float) $r['used'] - (float) $r['pending']) : null;
        }
        return $rows;
    }

    /** Dates in the range the employee would actually be absent: skips weekly offs and company holidays. */
    public static function workingDates(int $employeeId, string $from, string $to): array
    {
        $offs = Attendance::weekOffs(Attendance::shiftFor($employeeId));
        $holidays = array_column(Database::all('SELECT holiday_date FROM holidays WHERE holiday_date BETWEEN ? AND ?', [$from, $to]), 'holiday_date');
        $dates = [];
        for ($t = strtotime($from); $t <= strtotime($to); $t += 86400) {
            $d = date('Y-m-d', $t);
            if (!in_array((int) date('w', $t), $offs, true) && !in_array($d, $holidays, true)) {
                $dates[] = $d;
            }
        }
        return $dates;
    }

    public static function countDays(int $employeeId, string $from, string $to, bool $halfDay = false): float
    {
        $days = count(self::workingDates($employeeId, $from, $to));
        return $halfDay && $from === $to ? min(0.5, $days) : (float) $days;
    }
}
