<?php
require __DIR__ . '/app/bootstrap.php';

$db = App\Core\Database::connect();
$has = (bool) App\Core\Database::value(
    "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'employee_salaries' AND COLUMN_NAME = 'professional_tax'"
);
if (!$has) {
    $db->exec('ALTER TABLE employee_salaries ADD COLUMN professional_tax DECIMAL(12,2) NOT NULL DEFAULT 200 AFTER monthly_salary');
    echo "added professional_tax\n";
} else {
    echo "professional_tax exists\n";
}
$db->exec('UPDATE employee_salaries SET professional_tax = 200 WHERE professional_tax = 0 AND monthly_salary > 0');
$rows = App\Core\Database::all('SELECT employee_id, monthly_salary, professional_tax FROM employee_salaries');
foreach ($rows as $r) {
    echo json_encode($r) . "\n";
}
$reg = App\Services\SalaryRegister::build(date('Y-m'), false);
foreach ($reg['rows'] as $r) {
    echo $r['name'] . ' sal=' . $r['monthly_salary'] . ' pt=' . $r['professional_tax'] . ' net=' . $r['net'] . "\n";
}
echo "done\n";
