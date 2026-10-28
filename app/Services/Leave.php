<?php

namespace App\Services;

use App\Core\Database;

/**
 * Leave balances. Company policy: 1 paid leave credited every month from the joining month
 * through the current month. Unused balance does not carry forward into the next calendar year
 * (allocation is recalculated per year).
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

    /**
     * Leave-day weight for a calendar date: 0 = full off/holiday, 0.5 = half week-off (e.g. BDM Sat afternoon), 1 = full working day.
     * Balances use joining-month → current month credits; unused days do not carry to the next calendar year.
     */
    public static function dayWeight(int $employeeId, string $date): float
    {
        $shift = Attendance::shiftFor($employeeId);
        $w = (int) date('w', strtotime($date));
        if (in_array($w, Attendance::weekOffs($shift), true)) {
            return 0.0;
        }
        if (in_array($date, self::holidayDatesBetween($date, $date), true)) {
            return 0.0;
        }
        if (in_array($w, Attendance::halfWeekOffs($shift), true)) {
            return 0.5;
        }
        return 1.0;
    }

    /** Dates in the range the employee would actually be absent: skips full weekly offs and company holidays. */
    public static function workingDates(int $employeeId, string $from, string $to): array
    {
        $dates = [];
        for ($t = strtotime($from); $t <= strtotime($to); $t += 86400) {
            $d = date('Y-m-d', $t);
            if (self::dayWeight($employeeId, $d) > 0) {
                $dates[] = $d;
            }
        }
        return $dates;
    }

    /** Expand holiday From–To ranges into individual Y-m-d dates overlapping [$from, $to]. */
    public static function holidayDatesBetween(string $from, string $to): array
    {
        $rows = Database::all(
            'SELECT holiday_date, COALESCE(to_date, holiday_date) AS end_date
             FROM holidays
             WHERE holiday_date <= ? AND COALESCE(to_date, holiday_date) >= ?',
            [$to, $from]
        );
        $set = [];
        foreach ($rows as $r) {
            $start = strtotime((string) $r['holiday_date']);
            $end = strtotime((string) $r['end_date']);
            if ($start === false || $end === false) {
                continue;
            }
            for ($t = $start; $t <= $end; $t += 86400) {
                $d = date('Y-m-d', $t);
                if ($d >= $from && $d <= $to) {
                    $set[$d] = true;
                }
            }
        }
        return array_keys($set);
    }

    public static function countDays(int $employeeId, string $from, string $to, bool $halfDay = false): float
    {
        $total = 0.0;
        foreach (self::workingDates($employeeId, $from, $to) as $d) {
            $total += self::dayWeight($employeeId, $d);
        }
        if ($halfDay && $from === $to) {
            return min(0.5, $total);
        }
        return round($total, 1);
    }
}
