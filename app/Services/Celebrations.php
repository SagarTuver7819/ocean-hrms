<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/** Upcoming birthdays & work anniversaries for dashboards. */
class Celebrations
{
    /** @return list<array<string,mixed>> */
    public static function upcomingBirthdays(int $withinDays = 45, int $limit = 8): array
    {
        $today = date('Y-m-d');
        $rows = Database::all(
            "SELECT e.id, e.employee_code, e.first_name, e.last_name, e.date_of_birth, e.profile_photo,
                    e.user_id, d.name AS department_name, g.name AS designation_name
             FROM employees e
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN designations g ON g.id = e.designation_id
             WHERE e.date_of_birth IS NOT NULL AND e.status IN ('active','probation','onboarding')
             ORDER BY DATE_FORMAT(e.date_of_birth, '%m%d')"
        );
        $out = [];
        foreach ($rows as $b) {
            $md = date('m-d', strtotime($b['date_of_birth']));
            $next = date('Y') . '-' . $md;
            if ($next < $today) {
                $next = ((int) date('Y') + 1) . '-' . $md;
            }
            $days = (int) ((strtotime($next) - strtotime($today)) / 86400);
            if ($days > $withinDays) {
                continue;
            }
            $b['next_date'] = $next;
            $b['in_days'] = $days;
            $b['is_today'] = $days === 0;
            $out[] = $b;
        }
        usort($out, fn($a, $b) => $a['in_days'] <=> $b['in_days']);
        return array_slice($out, 0, $limit);
    }

    /** @return list<array<string,mixed>> */
    public static function upcomingAnniversaries(int $withinDays = 30, int $limit = 5): array
    {
        $today = date('Y-m-d');
        $rows = Database::all(
            "SELECT e.id, e.employee_code, e.first_name, e.last_name, e.joining_date, e.profile_photo,
                    e.user_id, d.name AS department_name, g.name AS designation_name
             FROM employees e
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN designations g ON g.id = e.designation_id
             WHERE e.joining_date IS NOT NULL AND e.status IN ('active','probation','onboarding')
               AND e.joining_date < CURDATE()
             ORDER BY DATE_FORMAT(e.joining_date, '%m%d')"
        );
        $out = [];
        foreach ($rows as $a) {
            $md = date('m-d', strtotime($a['joining_date']));
            $next = date('Y') . '-' . $md;
            if ($next < $today) {
                $next = ((int) date('Y') + 1) . '-' . $md;
            }
            $days = (int) ((strtotime($next) - strtotime($today)) / 86400);
            if ($days > $withinDays) {
                continue;
            }
            $years = max(1, (int) date('Y', strtotime($next)) - (int) date('Y', strtotime($a['joining_date'])));
            $a['next_date'] = $next;
            $a['in_days'] = $days;
            $a['years'] = $years;
            $a['is_today'] = $days === 0;
            $out[] = $a;
        }
        usort($out, fn($a, $b) => $a['in_days'] <=> $b['in_days']);
        return array_slice($out, 0, $limit);
    }

    /** IDs already wished today by this employee for a type. */
    public static function wishedToday(int $fromEmployeeId, string $type = 'birthday'): array
    {
        try {
            $rows = Database::all(
                'SELECT to_employee_id FROM employee_wishes
                 WHERE from_employee_id = ? AND wish_type = ? AND wish_date = CURDATE()',
                [$fromEmployeeId, $type]
            );
            return array_map('intval', array_column($rows, 'to_employee_id'));
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function sendWish(int $fromEmployeeId, int $toEmployeeId, string $type = 'birthday'): array
    {
        if ($fromEmployeeId === $toEmployeeId) {
            return ['ok' => false, 'message' => 'You cannot wish yourself.'];
        }
        if (!in_array($type, ['birthday', 'anniversary'], true)) {
            return ['ok' => false, 'message' => 'Invalid wish type.'];
        }

        $to = Database::first(
            "SELECT id, user_id, first_name, last_name, date_of_birth, joining_date, status
             FROM employees WHERE id = ? AND status IN ('active','probation','onboarding')",
            [$toEmployeeId]
        );
        if (!$to) {
            return ['ok' => false, 'message' => 'Employee not found.'];
        }

        $from = Database::first(
            'SELECT first_name, last_name, employee_code FROM employees WHERE id = ?',
            [$fromEmployeeId]
        );
        if (!$from) {
            return ['ok' => false, 'message' => 'Your profile was not found.'];
        }

        // Allow wish only if celebration is within next 7 days (or today)
        $dateField = $type === 'birthday' ? 'date_of_birth' : 'joining_date';
        if (empty($to[$dateField])) {
            return ['ok' => false, 'message' => 'Celebration date is not set.'];
        }
        $today = date('Y-m-d');
        $md = date('m-d', strtotime($to[$dateField]));
        $next = date('Y') . '-' . $md;
        if ($next < $today) {
            $next = ((int) date('Y') + 1) . '-' . $md;
        }
        $inDays = (int) ((strtotime($next) - strtotime($today)) / 86400);
        if ($inDays > 7) {
            return ['ok' => false, 'message' => 'Wish is available within 7 days of the date.'];
        }

        $db = Database::connect();
        try {
            $db->prepare(
                'INSERT INTO employee_wishes (from_employee_id, to_employee_id, wish_type, wish_date, created_at)
                 VALUES (?, ?, ?, CURDATE(), NOW())'
            )->execute([$fromEmployeeId, $toEmployeeId, $type]);
        } catch (\PDOException $e) {
            if ((int) $e->getCode() === 23000) {
                return ['ok' => false, 'message' => 'You already sent a wish today.'];
            }
            return ['ok' => false, 'message' => 'Could not send wish. Run DB sync first.'];
        }

        $fromName = trim($from['first_name'] . ' ' . $from['last_name']);
        $title = $type === 'birthday' ? 'Birthday wish 🎂' : 'Work anniversary wish 🎉';
        $message = $type === 'birthday'
            ? $fromName . ' wished you a Happy Birthday!'
            : $fromName . ' wished you a Happy Work Anniversary!';

        if (!empty($to['user_id'])) {
            notify((int) $to['user_id'], $title, $message, 'dashboard');
        }

        return ['ok' => true, 'message' => 'Wish sent to ' . trim($to['first_name'] . ' ' . $to['last_name']) . '!'];
    }
}
