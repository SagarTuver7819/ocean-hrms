<?php

/**
 * Align master data to IT / Software company structure.
 * Run once: php database/update_it_masters.php
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$db = Database::connect();

$itDepartments = [
    ['Software Development', 'DEV'],
    ['Quality Assurance', 'QA'],
    ['UI / UX Design', 'UX'],
    ['DevOps & Infrastructure', 'DEVOPS'],
    ['Project Management', 'PM'],
    ['Human Resources', 'HR'],
    ['Business Development', 'BD'],
    ['Accounts & Finance', 'FIN'],
    ['IT Support', 'IT'],
];

$itDesignations = [
    ['Software Engineer', 'Software Development'],
    ['Senior Software Engineer', 'Software Development'],
    ['Team Lead', 'Software Development'],
    ['Project Manager', 'Project Management'],
    ['QA Engineer', 'Quality Assurance'],
    ['UI/UX Designer', 'UI / UX Design'],
    ['DevOps Engineer', 'DevOps & Infrastructure'],
    ['HR Executive', 'Human Resources'],
    ['HR Head', 'Human Resources'],
    ['Business Analyst', 'Business Development'],
    ['IT Support Executive', 'IT Support'],
];

// Keep existing rows used by FKs; upsert by name
foreach ($itDepartments as [$name, $code]) {
    $exists = $db->prepare('SELECT id FROM departments WHERE name = ?');
    $exists->execute([$name]);
    if ($exists->fetch()) {
        $db->prepare('UPDATE departments SET code = ?, status = 1, updated_at = NOW() WHERE name = ?')
            ->execute([$code, $name]);
    } else {
        $db->prepare('INSERT INTO departments (name, code, status, created_at) VALUES (?, ?, 1, NOW())')
            ->execute([$name, $code]);
    }
}

// Soft-hide non-IT legacy names if any (none expected)
foreach ($itDesignations as [$desig, $deptName]) {
    $deptId = $db->prepare('SELECT id FROM departments WHERE name = ?');
    $deptId->execute([$deptName]);
    $did = $deptId->fetchColumn();
    $exists = $db->prepare('SELECT id FROM designations WHERE name = ?');
    $exists->execute([$desig]);
    if ($exists->fetch()) {
        $db->prepare('UPDATE designations SET department_id = ?, status = 1, updated_at = NOW() WHERE name = ?')
            ->execute([$did ?: null, $desig]);
    } else {
        $db->prepare('INSERT INTO designations (name, department_id, status, created_at) VALUES (?, ?, 1, NOW())')
            ->execute([$desig, $did ?: null]);
    }
}

// Enrich demo employee for IT view
$db->exec("UPDATE employees SET date_of_birth = '1996-10-14', department_id = (SELECT id FROM departments WHERE name='Software Development' LIMIT 1), designation_id = (SELECT id FROM designations WHERE name='Software Engineer' LIMIT 1) WHERE employee_code = 'OI20260001'");

$db->prepare(
    "INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW())
     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()"
)->execute(['company_type', 'IT / Software Development']);

echo "IT company masters updated successfully.\n";
