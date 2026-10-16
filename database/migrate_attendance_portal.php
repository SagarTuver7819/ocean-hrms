<?php

/**
 * Employee portal setup: shift master fields, break tracking, stored login passwords,
 * monthly leave credit (1 paid leave per month) and auto logins for every employee.
 * Safe to re-run. Run: php database/migrate_attendance_portal.php
 * Pass --reset-logins to regenerate passwords for employees whose password HR cannot see.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Services\EmployeeLogin;
use App\Services\Leave;

$db = Database::connect();
$hasColumn = fn(string $table, string $col): bool => (bool) Database::value(
    'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
    [$table, $col]
);
$addColumns = function (string $table, array $cols) use ($db, $hasColumn): void {
    foreach ($cols as $col => $def) {
        if (!$hasColumn($table, $col)) {
            $db->exec("ALTER TABLE {$table} ADD COLUMN {$col} {$def}");
            echo "{$table}.{$col} added\n";
        }
    }
};

// 1. Shift master
$addColumns('shifts', [
    'code'             => 'VARCHAR(20) NULL AFTER name',
    'break_minutes'    => 'INT NOT NULL DEFAULT 60 AFTER late_after_minutes',
    'half_day_minutes' => 'INT NOT NULL DEFAULT 240 AFTER break_minutes',
    'full_day_minutes' => 'INT NOT NULL DEFAULT 480 AFTER half_day_minutes',
    'week_offs'        => "VARCHAR(20) NOT NULL DEFAULT '0' AFTER full_day_minutes",
    'updated_at'       => 'DATETIME NULL AFTER created_at',
]);
$db->exec("UPDATE shifts SET code = 'GEN' WHERE code IS NULL AND name = 'General Shift'");

// 2. Attendance: breaks + extra calculations
$addColumns('attendance', [
    'shift_id'            => 'INT UNSIGNED NULL AFTER attendance_date',
    'break_minutes'       => 'INT NOT NULL DEFAULT 0 AFTER working_minutes',
    'early_leave_minutes' => 'INT NOT NULL DEFAULT 0 AFTER late_minutes',
]);
$db->exec("ALTER TABLE attendance_logs MODIFY punch_type ENUM('in','out','break_out','break_in') NOT NULL");
$db->exec(
    'CREATE TABLE IF NOT EXISTS attendance_breaks (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        attendance_id INT UNSIGNED NOT NULL,
        employee_id INT UNSIGNED NOT NULL,
        break_out DATETIME NOT NULL,
        break_in DATETIME NULL,
        minutes INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL,
        KEY attendance_id (attendance_id),
        KEY employee_id (employee_id),
        CONSTRAINT attendance_breaks_att FOREIGN KEY (attendance_id) REFERENCES attendance (id) ON DELETE CASCADE,
        CONSTRAINT attendance_breaks_emp FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);
$db->exec('UPDATE attendance a JOIN employees e ON e.id = a.employee_id SET a.shift_id = e.shift_id WHERE a.shift_id IS NULL');

// 3. Stored (encrypted) generated password
$addColumns('users', ['login_secret' => 'VARCHAR(255) NULL AFTER password']);

// 4. Leave policy: one paid leave credited every month, unpaid leave for anything beyond
$addColumns('leave_types', ['monthly_accrual' => 'DECIMAL(4,1) NOT NULL DEFAULT 0 AFTER days_per_year']);
if (!Database::value("SELECT COUNT(*) FROM settings WHERE setting_key = 'leave_policy_applied'")) {
    $db->exec("UPDATE leave_types SET name = 'Paid Leave', monthly_accrual = 1, days_per_year = 12, is_paid = 1, status = 1 WHERE code = 'PL'");
    $db->exec("UPDATE leave_types SET status = 1, monthly_accrual = 0, days_per_year = 0 WHERE code = 'UL'");
    $db->exec("UPDATE leave_types SET status = 0 WHERE code NOT IN ('PL','UL')");
    $db->exec("INSERT INTO settings (setting_key, setting_value, updated_at) VALUES ('leave_policy_applied', '1 paid leave per month', NOW())");
    echo "Leave policy applied: Paid Leave 1/month, Unpaid Leave unlimited, other types deactivated\n";
}
foreach (Database::all('SELECT id FROM employees') as $e) {
    Leave::syncBalances((int) $e['id']);
}
echo "Leave balances synced\n";

// 5. Logins for every employee
$created = EmployeeLogin::issueMissing();
echo "Employee logins created: {$created}\n";
if (in_array('--reset-logins', $argv ?? [], true)) {
    $hidden = Database::all('SELECT e.id FROM employees e JOIN users u ON u.id = e.user_id WHERE u.login_secret IS NULL');
    foreach ($hidden as $e) {
        EmployeeLogin::issue((int) $e['id']);
    }
    echo 'Passwords regenerated: ' . count($hidden) . "\n";
}

echo "Done.\n";
