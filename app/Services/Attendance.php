<?php

namespace App\Services;

use App\Core\Database;

/**
 * Web attendance: Punch In -> (Break Out -> Break In)* -> Punch Out.
 * Working time = punch-out minus punch-in minus all breaks.
 *
 * General policy: flexible punch 09:00–10:00, grace to 10:15, then late.
 * Required work 8h 20m; max break 1h (total day span 9h 20m).
 * Interns: flexible punch, late not counted.
 */
class Attendance
{
    public const DEFAULT_SHIFT = [
        'id' => null, 'name' => 'General Shift', 'code' => 'GEN',
        'start_time' => '09:00:00', 'end_time' => '18:20:00',
        'flexible_minutes' => 60, 'late_after_minutes' => 15, 'skip_late' => 0,
        'break_minutes' => 60, 'half_day_minutes' => 250, 'full_day_minutes' => 500,
        'week_offs' => '0', 'half_week_offs' => '',
    ];

    public const WEEKDAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public static function shiftFor(int $employeeId): array
    {
        return Database::first('SELECT s.* FROM employees e JOIN shifts s ON s.id = e.shift_id WHERE e.id = ?', [$employeeId])
            ?? Database::first('SELECT * FROM shifts WHERE status = 1 ORDER BY id LIMIT 1')
            ?? self::DEFAULT_SHIFT;
    }

    public static function isIntern(int $employeeId): bool
    {
        $type = (string) Database::value('SELECT employment_type FROM employees WHERE id = ?', [$employeeId]);
        return $type === 'intern';
    }

    /** Weekday numbers (0 = Sunday) the shift treats as full weekly off. */
    public static function weekOffs(array $shift): array
    {
        return array_map('intval', array_filter(explode(',', (string) ($shift['week_offs'] ?? '')), 'strlen'));
    }

    /** Weekday numbers treated as half-day weekly off (e.g. Saturday afternoon for BDM). */
    public static function halfWeekOffs(array $shift): array
    {
        return array_map('intval', array_filter(explode(',', (string) ($shift['half_week_offs'] ?? '')), 'strlen'));
    }

    /** Latest punch-in time before late starts (start + flexible + grace). */
    public static function lateDeadline(array $shift, string $date): int
    {
        $start = strtotime($date . ' ' . $shift['start_time']);
        $flex = (int) ($shift['flexible_minutes'] ?? 0);
        $grace = (int) ($shift['late_after_minutes'] ?? 0);
        return $start + ($flex + $grace) * 60;
    }

    public static function skipsLate(int $employeeId, array $shift): bool
    {
        return self::isIntern($employeeId) || !empty($shift['skip_late']);
    }

    /** The open session (punched in, not out, started within 20 hours — covers night shifts), else today's row. */
    public static function current(int $employeeId): ?array
    {
        return Database::first(
            'SELECT * FROM attendance WHERE employee_id = ? AND punch_in IS NOT NULL AND punch_out IS NULL AND punch_in >= ? ORDER BY punch_in DESC LIMIT 1',
            [$employeeId, date('Y-m-d H:i:s', time() - 20 * 3600)]
        ) ?? Database::first('SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ?', [$employeeId, date('Y-m-d')]);
    }

    public static function breaks(int $attendanceId): array
    {
        return Database::all('SELECT * FROM attendance_breaks WHERE attendance_id = ? ORDER BY id', [$attendanceId]);
    }

    public static function state(?array $row, array $breaks): string
    {
        if (!$row || !$row['punch_in']) {
            return 'not_started';
        }
        if ($row['punch_out']) {
            return 'done';
        }
        foreach ($breaks as $b) {
            if (!$b['break_in']) {
                return 'on_break';
            }
        }
        return 'working';
    }

    /** Everything the punch widget needs, with live totals in seconds. */
    public static function snapshot(int $employeeId): array
    {
        $shift = self::shiftFor($employeeId);
        $row = self::current($employeeId);
        $breaks = $row ? self::breaks((int) $row['id']) : [];
        $now = time();

        $breakSecs = 0;
        $breakStartedAt = null;
        foreach ($breaks as $b) {
            if (!$b['break_in']) {
                $breakStartedAt = strtotime($b['break_out']);
            }
            $breakSecs += max(0, ($b['break_in'] ? strtotime($b['break_in']) : $now) - strtotime($b['break_out']));
        }
        $workSecs = 0;
        if ($row && $row['punch_in']) {
            $end = $row['punch_out'] ? strtotime($row['punch_out']) : $now;
            $workSecs = max(0, $end - strtotime($row['punch_in']) - $breakSecs);
        }

        $today = date('Y-m-d');
        $wday = (int) date('w');
        $skipLate = self::skipsLate($employeeId, $shift);
        $flex = (int) ($shift['flexible_minutes'] ?? 0);

        return [
            'shift' => $shift,
            'row' => $row,
            'breaks' => $breaks,
            'state' => self::state($row, $breaks),
            'workSecs' => $workSecs,
            'breakSecs' => $breakSecs,
            'breakStartedAt' => $breakStartedAt,
            'targetSecs' => max(60, (int) $shift['full_day_minutes'] * 60),
            'maxBreakSecs' => max(0, (int) $shift['break_minutes'] * 60),
            'skipLate' => $skipLate,
            'isIntern' => self::isIntern($employeeId),
            'flexibleMinutes' => $flex,
            'lateDeadline' => self::lateDeadline($shift, $today),
            'holiday' => Database::value(
                'SELECT name FROM holidays
                 WHERE ? BETWEEN holiday_date AND COALESCE(to_date, holiday_date)
                 ORDER BY holiday_date LIMIT 1',
                [$today]
            ),
            'weekOff' => in_array($wday, self::weekOffs($shift), true),
            'halfWeekOff' => in_array($wday, self::halfWeekOffs($shift), true),
            'onLeave' => (bool) Database::value(
                "SELECT COUNT(*) FROM leave_requests WHERE employee_id = ? AND status = 'approved' AND ? BETWEEN from_date AND to_date",
                [$employeeId, $today]
            ),
        ];
    }

    /** Applies one punch action. Returns ['ok' => bool, 'message' => string]. */
    public static function punch(int $employeeId, string $action): array
    {
        $snap = self::snapshot($employeeId);
        $row = $snap['row'];
        $shift = $snap['shift'];
        $state = $snap['state'];
        $now = date('Y-m-d H:i:s');
        $nowLabel = date('h:i A');
        $name = (string) Database::value("SELECT CONCAT(first_name, ' ', last_name) FROM employees WHERE id = ?", [$employeeId]);
        $maxBreak = (int) ($shift['break_minutes'] ?? 60);

        switch ($action) {
            case 'in':
                if ($state !== 'not_started') {
                    return self::fail('You have already punched in today.');
                }
                $today = date('Y-m-d');
                $late = 0;
                $status = 'present';
                if (!self::skipsLate($employeeId, $shift)) {
                    $deadline = self::lateDeadline($shift, $today);
                    if (time() > $deadline) {
                        $late = intdiv(time() - $deadline, 60);
                        $status = 'late';
                    }
                }
                if ($row) {
                    Database::run(
                        'UPDATE attendance SET punch_in = ?, shift_id = ?, late_minutes = ?, status = ?, updated_at = NOW() WHERE id = ?',
                        [$now, $shift['id'], $late, $status, $row['id']]
                    );
                } else {
                    Database::run(
                        'INSERT INTO attendance (employee_id, attendance_date, shift_id, punch_in, late_minutes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())',
                        [$employeeId, $today, $shift['id'], $now, $late, $status]
                    );
                }
                self::log($employeeId, 'in', $now);
                $lateMsg = $late ? ' (late by ' . hm($late) . ')' : '';
                notify_hr('Punch In · ' . $name, $name . ' punched in at ' . $nowLabel . $lateMsg, 'attendance');
                return ['ok' => true, 'message' => 'Punched in at ' . $nowLabel . ($late ? ' · Late by ' . hm($late) : ' · On time')];

            case 'break_out':
                if ($state !== 'working') {
                    return self::fail($state === 'on_break' ? 'You are already on a break.' : 'Please punch in first.');
                }
                $used = (int) Database::value(
                    'SELECT COALESCE(SUM(minutes), 0) FROM attendance_breaks WHERE attendance_id = ?',
                    [$row['id']]
                );
                if ($maxBreak > 0 && $used >= $maxBreak) {
                    return self::fail('Maximum break of ' . hm($maxBreak) . ' already used today.');
                }
                Database::run(
                    'INSERT INTO attendance_breaks (attendance_id, employee_id, break_out, created_at) VALUES (?, ?, ?, NOW())',
                    [$row['id'], $employeeId, $now]
                );
                self::log($employeeId, 'break_out', $now);
                $left = max(0, $maxBreak - $used);
                return ['ok' => true, 'message' => 'Break started at ' . $nowLabel . ($maxBreak ? ' · ' . hm($left) . ' break left' : '')];

            case 'break_in':
                if ($state !== 'on_break') {
                    return self::fail('You are not on a break.');
                }
                $minutes = self::closeBreak((int) $row['id'], $now, $maxBreak);
                self::log($employeeId, 'break_in', $now);
                $msg = 'Back from break at ' . $nowLabel . ' · Break ' . hm($minutes, '0m');
                if ($maxBreak > 0 && $minutes >= $maxBreak) {
                    $msg .= ' (max ' . hm($maxBreak) . ' reached)';
                }
                return ['ok' => true, 'message' => $msg];

            case 'out':
                if ($state !== 'working' && $state !== 'on_break') {
                    return self::fail($state === 'done' ? 'You have already punched out today.' : 'Please punch in first.');
                }
                if ($state === 'on_break') {
                    self::closeBreak((int) $row['id'], $now, $maxBreak);
                    self::log($employeeId, 'break_in', $now);
                }
                $breakMin = (int) Database::value('SELECT COALESCE(SUM(minutes), 0) FROM attendance_breaks WHERE attendance_id = ?', [$row['id']]);
                $worked = max(0, intdiv(strtotime($now) - strtotime($row['punch_in']), 60) - $breakMin);

                $shiftEnd = strtotime($row['attendance_date'] . ' ' . $shift['end_time']);
                if ($shift['end_time'] <= $shift['start_time']) {
                    $shiftEnd += 86400;
                }
                $shiftLength = intdiv($shiftEnd - strtotime($row['attendance_date'] . ' ' . $shift['start_time']), 60);
                $early = min($shiftLength, max(0, intdiv($shiftEnd - time(), 60)));
                $overtime = max(0, $worked - (int) $shift['full_day_minutes']);
                $status = match (true) {
                    $worked < (int) $shift['half_day_minutes'] => 'absent',
                    $worked < (int) $shift['full_day_minutes'] => 'half_day',
                    default => ((int) ($row['late_minutes'] ?? 0) > 0 ? 'late' : 'present'),
                };

                Database::run(
                    'UPDATE attendance SET punch_out = ?, working_minutes = ?, break_minutes = ?, overtime_minutes = ?, early_leave_minutes = ?, status = ?, updated_at = NOW() WHERE id = ?',
                    [$now, $worked, $breakMin, $overtime, $early, $status, $row['id']]
                );
                self::log($employeeId, 'out', $now);
                notify_hr('Punch Out · ' . $name, $name . ' punched out at ' . $nowLabel . ' · worked ' . hm($worked, '0m') . ($breakMin ? ', break ' . hm($breakMin) : ''), 'attendance');
                return ['ok' => true, 'message' => 'Punched out at ' . $nowLabel . ' · Worked ' . hm($worked, '0m')];
        }

        return self::fail('Unknown punch action.');
    }

    private static function closeBreak(int $attendanceId, string $now, int $maxBreak = 0): int
    {
        $open = Database::first('SELECT * FROM attendance_breaks WHERE attendance_id = ? AND break_in IS NULL ORDER BY id DESC LIMIT 1', [$attendanceId]);
        if (!$open) {
            return 0;
        }
        $minutes = max(0, intdiv(strtotime($now) - strtotime($open['break_out']), 60));
        if ($maxBreak > 0) {
            $usedOther = (int) Database::value(
                'SELECT COALESCE(SUM(minutes), 0) FROM attendance_breaks WHERE attendance_id = ? AND id <> ?',
                [$attendanceId, $open['id']]
            );
            $minutes = min($minutes, max(0, $maxBreak - $usedOther));
        }
        Database::run('UPDATE attendance_breaks SET break_in = ?, minutes = ? WHERE id = ?', [$now, $minutes, $open['id']]);
        Database::run(
            'UPDATE attendance SET break_minutes = (SELECT COALESCE(SUM(minutes), 0) FROM attendance_breaks WHERE attendance_id = ?), updated_at = NOW() WHERE id = ?',
            [$attendanceId, $attendanceId]
        );
        return $minutes;
    }

    private static function log(int $employeeId, string $type, string $at): void
    {
        Database::run(
            'INSERT INTO attendance_logs (employee_id, punch_type, punched_at, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())',
            [$employeeId, $type, $at, $_SERVER['REMOTE_ADDR'] ?? null]
        );
    }

    private static function fail(string $message): array
    {
        return ['ok' => false, 'message' => $message];
    }

    /** Live label for a row on the HR grid. */
    public static function liveStatus(?array $row, bool $onBreak, bool $onLeave): array
    {
        if ($onLeave && (!$row || !$row['punch_in'])) {
            return ['On Leave', 'leave'];
        }
        if (!$row || !$row['punch_in']) {
            return ['Not Punched', 'absent'];
        }
        if ($row['punch_out']) {
            return ['Completed', 'present'];
        }
        return $onBreak ? ['On Break', 'on_break'] : ['Working', 'working'];
    }
}
