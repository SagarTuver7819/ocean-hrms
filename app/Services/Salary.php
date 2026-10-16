<?php

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Employee;

/**
 * Single final monthly salary per employee (no Basic / HRA / DA structure).
 */
class Salary
{
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

    /** Saves the monthly salary and records it in the salary history. */
    public static function set(int $employeeId, float $monthly, string $effectiveFrom, string $reason): void
    {
        $old = self::current($employeeId);
        if ($old) {
            Database::run(
                'UPDATE employee_salaries SET monthly_salary = ?, effective_from = ?, updated_at = NOW() WHERE employee_id = ?',
                [$monthly, $effectiveFrom, $employeeId]
            );
        } else {
            Database::run(
                'INSERT INTO employee_salaries (employee_id, monthly_salary, effective_from, created_at) VALUES (?, ?, ?, NOW())',
                [$employeeId, $monthly, $effectiveFrom]
            );
        }
        Database::run(
            'INSERT INTO salary_histories (employee_id, monthly_salary, effective_from, reason, snapshot_json, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$employeeId, $monthly, $effectiveFrom, $reason, json_encode(['monthly_salary' => $monthly]), Auth::id()]
        );
        (new Employee())->addTimeline($employeeId, 'Salary updated', $reason . ' · ' . money($monthly) . ' / month', $effectiveFrom);
        activity_log('salary', 'updated', $employeeId, $old ? ['monthly_salary' => $old['monthly_salary']] : null, ['monthly_salary' => $monthly]);
    }
}
