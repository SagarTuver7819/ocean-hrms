<?php

namespace App\Services;

use App\Core\Database;

/**
 * Leave policy:
 * - Paid Leave (PL): 1 day credited every month from joining (or policy start), no year carry-forward.
 * - Sick Leave (SL): unpaid, unlimited (not tracked against a balance).
 * Accrual for a year stops at Dec; unused PL does not roll into the next year.
 */
class Leave
{
    /** Company leave accrual begins this month (Oct 2026 → months 10,11,12 = up to 3 for year 2026). */
    public const POLICY_START = '2026-10-01';

    public static function creditedMonths(?string $joiningDate, int $year): int
    {
        $currentYear = (int) date('Y');
        if ($year > $currentYear) {
            return 0;
        }

        $policyTs = strtotime(self::POLICY_START);
        $policyYear = (int) date('Y', $policyTs);
        $policyMonth = (int) date('n', $policyTs);

        if ($year < $policyYear) {
            return 0;
        }

        $first = 1;
        if ($year === $policyYear) {
            $first = $policyMonth;
        }
        if ($joiningDate) {
            $joinYear = (int) date('Y', strtotime($joiningDate));
            $joinMonth = (int) date('n', strtotime($joiningDate));
            if ($joinYear > $year) {
                return 0;
            }
            if ($joinYear === $year) {
                $first = max($first, $joinMonth);
            }
        }

        $last = $year < $currentYear ? 12 : (int) date('n');
        return max(0, $last - $first + 1);
    }

    /** Month numbers already credited this year (for UI timeline). */
    public static function creditedMonthList(?string $joiningDate, int $year): array
    {
        $count = self::creditedMonths($joiningDate, $year);
        if ($count <= 0) {
            return [];
        }
        $policyTs = strtotime(self::POLICY_START);
        $policyYear = (int) date('Y', $policyTs);
        $first = $year === $policyYear ? (int) date('n', $policyTs) : 1;
        if ($joiningDate) {
            $joinYear = (int) date('Y', strtotime($joiningDate));
            if ($joinYear === $year) {
                $first = max($first, (int) date('n', strtotime($joiningDate)));
            }
        }
        $months = [];
        for ($i = 0; $i < $count; $i++) {
            $months[] = $first + $i;
        }
        return $months;
    }

    public static function syncBalances(int $employeeId, ?int $year = null): void
    {
        $year ??= (int) date('Y');
        $months = self::creditedMonths(Database::value('SELECT joining_date FROM employees WHERE id = ?', [$employeeId]), $year);
        $types = Database::all('SELECT * FROM leave_types WHERE status = 1 AND (monthly_accrual > 0 OR days_per_year > 0)');
        foreach ($types as $t) {
            $allocated = (float) $t['monthly_accrual'] > 0
                ? $months * (float) $t['monthly_accrual']
                : (float) $t['days_per_year'];
            Database::run(
                'INSERT INTO leave_balances (employee_id, leave_type_id, year, allocated, used, pending) VALUES (?, ?, ?, ?, 0, 0)
                 ON DUPLICATE KEY UPDATE allocated = VALUES(allocated)',
                [$employeeId, $t['id'], $year, $allocated]
            );
        }
    }

    /** Sync PL balances for every active employee for the given year. */
    public static function syncAllBalances(?int $year = null): int
    {
        $year ??= (int) date('Y');
        $ids = Database::all("SELECT id FROM employees WHERE status = 'active'");
        foreach ($ids as $row) {
            self::syncBalances((int) $row['id'], $year);
        }
        return count($ids);
    }

    /** Active leave types with this year's balance; Sick Leave has available = null (unlimited unpaid). */
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
             ORDER BY lt.is_paid DESC, lt.monthly_accrual DESC, lt.name',
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
