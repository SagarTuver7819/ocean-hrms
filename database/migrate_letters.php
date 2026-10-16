<?php

/**
 * HR letters module + two-step recruitment decision (safe to re-run).
 * Run: php database/migrate_letters.php
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$db = Database::connect();

$db->exec(
    "CREATE TABLE IF NOT EXISTS hr_letters (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        letter_type ENUM('offer','appointment','experience','relieving') NOT NULL,
        ref_no VARCHAR(60) NOT NULL UNIQUE,
        employee_id INT UNSIGNED NULL,
        candidate_id INT UNSIGNED NULL,
        letter_date DATE NOT NULL,
        meta JSON NULL,
        created_by INT UNSIGNED NULL,
        created_at DATETIME NULL,
        INDEX idx_letter_emp (employee_id, letter_type),
        INDEX idx_letter_cand (candidate_id, letter_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

$db->exec("UPDATE candidates SET status = 'new' WHERE status IN ('shortlisted','interview','technical_round','hr_round','on_hold')");
$db->exec("UPDATE candidates SET status = 'selected' WHERE status IN ('offered','joined')");

$deptId = function (string $name) use ($db): ?int {
    $s = $db->prepare('SELECT id FROM departments WHERE name = ? LIMIT 1');
    $s->execute([$name]);
    return ($id = $s->fetchColumn()) ? (int) $id : null;
};
$designations = [
    'Web Developer'   => 'Software Development',
    'Web Designer'    => 'UI / UX Design',
    'Designing'       => 'UI / UX Design',
    'Receptionist'    => 'Human Resources',
    'Sales Executive' => 'Business Development',
];
$find = $db->prepare('SELECT id FROM designations WHERE name = ? LIMIT 1');
$insert = $db->prepare('INSERT INTO designations (name, department_id, status, created_at) VALUES (?, ?, 1, NOW())');
foreach ($designations as $name => $dept) {
    $find->execute([$name]);
    if (!$find->fetchColumn()) {
        $insert->execute([$name, $deptId($dept)]);
    }
}

$defaults = [
    'letter_ref_prefix'            => 'OI/HR',
    'letter_signatory_name'        => 'HR Manager',
    'letter_signatory_designation' => 'Human Resources',
    'letter_week_off'              => 'Sunday',
    'letter_jurisdiction'          => 'Ahmedabad, Gujarat',
    'letter_notice_period'         => '1 month',
];
$stmt = $db->prepare('INSERT IGNORE INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW())');
foreach ($defaults as $key => $value) {
    $stmt->execute([$key, $value]);
}

echo "Letters module migrated successfully.\n";
