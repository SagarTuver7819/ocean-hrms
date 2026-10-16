<?php
/**
 * Removes all test / transactional data before going live.
 * Keeps: masters (departments, designations, teams, branches, shifts, leave types, holidays),
 * settings & letterhead, roles & permissions, and the admin + HR Head accounts (passwords reset).
 * Run: php database/clean_for_live.php
 */
require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

$db = Database::connect();
$db->exec('SET FOREIGN_KEY_CHECKS = 0');

$truncate = [
    'activity_logs', 'notifications',
    'attendance', 'attendance_breaks', 'attendance_logs',
    'candidates', 'candidate_education', 'candidate_experience', 'candidate_interviews',
    'employees', 'employee_bank_details', 'employee_documents', 'employee_education',
    'employee_emergency_contacts', 'employee_experience', 'employee_queries', 'employee_query_replies',
    'employee_salaries', 'employee_timeline', 'salary_histories', 'salary_components',
    'hr_letters', 'leave_balances', 'leave_requests', 'company_policies',
];
foreach ($truncate as $t) {
    $db->exec("TRUNCATE TABLE `$t`");
    echo "cleared  $t\n";
}

foreach ($db->query("SHOW TABLES LIKE '%\\_bk\\_%'")->fetchAll(PDO::FETCH_COLUMN) as $t) {
    $db->exec("DROP TABLE `$t`");
    echo "dropped  $t\n";
}

$db->exec("DELETE FROM user_roles WHERE user_id IN (SELECT id FROM users WHERE username NOT IN ('admin','hrhead'))");
$db->exec("DELETE FROM users WHERE username NOT IN ('admin','hrhead')");
$db->exec('ALTER TABLE users AUTO_INCREMENT = 3');
$db->exec("DELETE FROM announcements WHERE title <> 'Welcome to Ocean HRMS'");

$passwords = [];
foreach (['admin', 'hrhead'] as $u) {
    $plain = 'Oh@' . substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, 3) . random_int(1000, 9999) . substr(str_shuffle('abcdefghjkmnpqrstuvwxyz'), 0, 3);
    Database::run(
        'UPDATE users SET password = ?, login_secret = NULL, must_change_password = 1, last_login_at = NULL, last_login_ip = NULL, updated_at = NOW() WHERE username = ?',
        [password_hash($plain, PASSWORD_DEFAULT), $u]
    );
    $passwords[$u] = $plain;
}

$db->exec('SET FOREIGN_KEY_CHECKS = 1');

foreach (glob(BASE_PATH . '/public/assets/uploads/photos/*') ?: [] as $f) @unlink($f);
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BASE_PATH . '/storage/uploads', FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $f) {
    $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
}

echo "\nNew passwords (must be changed at first login):\n";
foreach ($passwords as $u => $p) echo "  $u : $p\n";
