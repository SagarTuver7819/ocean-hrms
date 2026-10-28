<?php

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Employee;

/**
 * Single final monthly salary per employee + editable Professional Tax (default ₹200).
 */
class Salary
{
    public const DEFAULT_PT = 200;

    public static function current(int $employeeId): ?array
    {
        return Database::first('SELECT * FROM employee_salaries WHERE employee_id = ?', [$employeeId]);
    }

    public static function history(int $employeeId): array
    {
        return Database::all(
            'SELECT h.*, u.username AS updated_by FROM salary_histories h
             LEFT JOIN users u ON u.id = h.created_by
             WHERE h.employee_id = ? ORDER BY h.effective_from DESC, h.id DESC',
            [$employeeId]
        );
    }

    /** PT for payroll: stored value, or default 200 when salary exists, else 0. */
    public static function ptAmount(int $employeeId, ?array $salaryRow = null): float
    {
        $row = $salaryRow ?? self::current($employeeId);
        if (!$row || (float) ($row['monthly_salary'] ?? 0) <= 0) {
            return 0.0;
        }
        if (array_key_exists('professional_tax', $row) && $row['professional_tax'] !== null && $row['professional_tax'] !== '') {
            return max(0, round((float) $row['professional_tax'], 2));
        }
        return (float) self::DEFAULT_PT;
    }

    /** Saves monthly salary + PT and records history. */
    public static function set(int $employeeId, float $monthly, string $effectiveFrom, string $reason, ?float $pt = null): void
    {
        $old = self::current($employeeId);
        $ptAmount = $pt !== null ? max(0, round($pt, 2)) : self::DEFAULT_PT;
        if ($old) {
            Database::run(
                'UPDATE employee_salaries SET monthly_salary = ?, professional_tax = ?, effective_from = ?, updated_at = NOW() WHERE employee_id = ?',
                [$monthly, $ptAmount, $effectiveFrom, $employeeId]
            );
        } else {
            Database::run(
                'INSERT INTO employee_salaries (employee_id, monthly_salary, professional_tax, effective_from, created_at) VALUES (?, ?, ?, ?, NOW())',
                [$employeeId, $monthly, $ptAmount, $effectiveFrom]
            );
        }
        Database::run(
            'INSERT INTO salary_histories (employee_id, monthly_salary, effective_from, reason, snapshot_json, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [
                $employeeId,
                $monthly,
                $effectiveFrom,
                $reason,
                json_encode(['monthly_salary' => $monthly, 'professional_tax' => $ptAmount]),
                Auth::id(),
            ]
        );
        (new Employee())->addTimeline(
            $employeeId,
            'Salary updated',
            $reason . ' · ' . money($monthly) . ' / month · PT ' . money($ptAmount),
            $effectiveFrom
        );
        activity_log(
            'salary',
            'updated',
            $employeeId,
            $old ? ['monthly_salary' => $old['monthly_salary'], 'professional_tax' => $old['professional_tax'] ?? null] : null,
            ['monthly_salary' => $monthly, 'professional_tax' => $ptAmount]
        );
    }

    /** Update only PT (keeps decided salary). */
    public static function setPt(int $employeeId, float $pt): void
    {
        $old = self::current($employeeId);
        if (!$old) {
            return;
        }
        $ptAmount = max(0, round($pt, 2));
        Database::run(
            'UPDATE employee_salaries SET professional_tax = ?, updated_at = NOW() WHERE employee_id = ?',
            [$ptAmount, $employeeId]
        );
        activity_log('salary', 'pt_updated', $employeeId, ['professional_tax' => $old['professional_tax'] ?? null], ['professional_tax' => $ptAmount]);
    }
}
