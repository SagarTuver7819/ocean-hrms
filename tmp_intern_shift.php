<?php
require __DIR__ . '/app/bootstrap.php';

App\Core\Database::run(
    "UPDATE shifts SET
        flexible_minutes = 540,
        late_after_minutes = 0,
        skip_late = 1,
        break_minutes = 30,
        full_day_minutes = 240,
        half_day_minutes = 1,
        status = 1
     WHERE code = 'INT'"
);

$row = App\Core\Database::first(
    "SELECT code,name,flexible_minutes,skip_late,full_day_minutes,half_day_minutes,break_minutes FROM shifts WHERE code='INT'"
);
echo 'INT shift: ' . json_encode($row) . PHP_EOL;

$rows = App\Core\Database::all(
    "SELECT a.id, a.employee_id, a.working_minutes, a.status, e.employment_type, s.skip_late, s.full_day_minutes
     FROM attendance a
     JOIN employees e ON e.id = a.employee_id
     LEFT JOIN shifts s ON s.id = COALESCE(a.shift_id, e.shift_id)
     WHERE a.attendance_date = CURDATE()
       AND (e.employment_type = 'intern' OR IFNULL(s.skip_late,0) = 1)
       AND a.punch_out IS NOT NULL"
);
foreach ($rows as $r) {
    $shift = [
        'skip_late' => 1,
        'full_day_minutes' => (int) ($r['full_day_minutes'] ?: 240),
    ];
    $status = App\Services\Attendance::dayStatus((int) $r['working_minutes'], $shift, 0, (int) $r['employee_id']);
    App\Core\Database::run(
        'UPDATE attendance SET status = ?, late_minutes = 0, early_leave_minutes = 0 WHERE id = ?',
        [$status, $r['id']]
    );
    echo "att#{$r['id']} worked={$r['working_minutes']} {$r['status']} -> {$status}\n";
}
echo "done\n";
