<?php

/**
 * Flat salary model: one final monthly salary per employee (no Basic / HRA / DA structure),
 * plus offer details captured when a candidate is selected. Safe to re-run.
 * Run: php database/migrate_flat_salary.php
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$db = Database::connect();
$hasColumn = fn(string $table, string $col): bool => (bool) Database::value(
    'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
    [$table, $col]
);

// 1. Candidate offer details
$candidateCols = [
    'offered_salary'   => 'DECIMAL(12,2) NULL AFTER expected_ctc',
    'joining_date'     => 'DATE NULL AFTER offered_salary',
    'probation_months' => 'TINYINT UNSIGNED NULL AFTER joining_date',
    'offer_valid_till' => 'DATE NULL AFTER probation_months',
    'selected_at'      => 'DATETIME NULL AFTER offer_valid_till',
];
foreach ($candidateCols as $col => $def) {
    if (!$hasColumn('candidates', $col)) {
        $db->exec("ALTER TABLE candidates ADD COLUMN {$col} {$def}");
        echo "candidates.{$col} added\n";
    }
}

// 2. employee_salaries -> single monthly_salary
if (!$hasColumn('employee_salaries', 'monthly_salary')) {
    $backup = 'employee_salaries_bk_' . date('Ymd');
    $db->exec("CREATE TABLE IF NOT EXISTS {$backup} AS SELECT * FROM employee_salaries");
    echo "Backup table {$backup} created\n";

    $db->exec('ALTER TABLE employee_salaries ADD COLUMN monthly_salary DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER employee_id');
    $db->exec('UPDATE employee_salaries SET monthly_salary = COALESCE(NULLIF(gross_salary, 0), net_salary, 0)');
    $drop = ['basic_salary', 'hra', 'conveyance', 'special_allowance', 'medical_allowance', 'other_allowance',
        'pf', 'pt', 'tds', 'esic', 'loan', 'advance', 'other_deduction', 'ctc', 'gross_salary', 'total_deduction', 'net_salary'];
    $db->exec('ALTER TABLE employee_salaries ' . implode(', ', array_map(fn($c) => "DROP COLUMN {$c}", $drop)));
    echo "employee_salaries converted to monthly_salary\n";
}

// 3. salary_histories -> monthly_salary
if (!$hasColumn('salary_histories', 'monthly_salary')) {
    $backup = 'salary_histories_bk_' . date('Ymd');
    $db->exec("CREATE TABLE IF NOT EXISTS {$backup} AS SELECT * FROM salary_histories");
    $db->exec('ALTER TABLE salary_histories ADD COLUMN monthly_salary DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER employee_id');
    $db->exec('UPDATE salary_histories SET monthly_salary = COALESCE(NULLIF(gross_salary, 0), net_salary, ROUND(ctc / 12), 0)');
    $db->exec('ALTER TABLE salary_histories DROP COLUMN ctc, DROP COLUMN gross_salary, DROP COLUMN net_salary');
    echo "salary_histories converted (backup {$backup})\n";
}

// 4. Offer details for candidates already hired
$db->exec(
    "UPDATE candidates c
     JOIN employees e ON e.id = c.employee_id
     LEFT JOIN employee_salaries s ON s.employee_id = e.id
     SET c.offered_salary   = COALESCE(c.offered_salary, s.monthly_salary),
         c.joining_date     = COALESCE(c.joining_date, e.joining_date),
         c.probation_months = COALESCE(c.probation_months, 3),
         c.selected_at      = COALESCE(c.selected_at, c.updated_at, c.created_at)
     WHERE c.status = 'selected'"
);

$db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'letter_notice_period' AND setting_value = ''")->execute(['1 month']);

echo "Flat salary migration complete.\n";
