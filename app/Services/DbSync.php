<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Idempotent DB patches for local → live sync via /db-sync URL.
 * Safe to re-run. Add new schema/data fixes at the bottom of run().
 */
class DbSync
{
    /** @return list<string> */
    public static function run(): array
    {
        $db = Database::connect();
        $log = [];

        $hasColumn = static function (string $table, string $col) use ($db): bool {
            $stmt = $db->prepare(
                'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
            );
            $stmt->execute([$table, $col]);
            return (bool) $stmt->fetchColumn();
        };

        $addColumn = static function (string $table, string $col, string $def) use ($db, $hasColumn, &$log): void {
            if ($hasColumn($table, $col)) {
                $log[] = "skip · {$table}.{$col} already exists";
                return;
            }
            $db->exec("ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$def}");
            $log[] = "ok · added {$table}.{$col}";
        };

        $ensurePermission = static function (string $name, string $slug, string $module) use ($db, &$log): void {
            $stmt = $db->prepare('SELECT id FROM permissions WHERE slug = ?');
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn()) {
                $log[] = "skip · permission {$slug}";
                return;
            }
            $db->prepare('INSERT INTO permissions (name, slug, module, created_at) VALUES (?, ?, ?, NOW())')
                ->execute([$name, $slug, $module]);
            $log[] = "ok · permission {$slug}";
        };

        $grantToRole = static function (string $roleSlug, string $permSlug) use ($db, &$log): void {
            $sql = 'INSERT IGNORE INTO role_permissions (role_id, permission_id)
                    SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
                    WHERE r.slug = ? AND p.slug = ?';
            $stmt = $db->prepare($sql);
            $stmt->execute([$roleSlug, $permSlug]);
            $log[] = $stmt->rowCount()
                ? "ok · granted {$permSlug} → {$roleSlug}"
                : "skip · {$roleSlug} already has {$permSlug}";
        };

        // ——— Patches (append new ones below; never remove applied ones) ———

        // 2026-10: employee exit / soft-delete
        $addColumn('employees', 'exit_date', 'DATE NULL AFTER confirmation_date');
        $ensurePermission('Delete Employee', 'employee.delete', 'employee');
        $grantToRole('super_admin', 'employee.delete');
        foreach (['hr_admin', 'admin', 'hr'] as $role) {
            $exists = $db->prepare('SELECT id FROM roles WHERE slug = ?');
            $exists->execute([$role]);
            if ($exists->fetchColumn()) {
                $grantToRole($role, 'employee.delete');
            }
        }

        // Official Ocean departments + designations (incl. Flutter / BDM / BDE)
        foreach (OceanOrgMasters::sync($db) as $line) {
            $log[] = $line;
        }

        // Letters table (if older live DB)
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
        $log[] = 'ok · hr_letters table checked';

        // Portal / attendance columns commonly missing on older installs
        $portalCols = [
            'users' => ['login_secret' => 'VARCHAR(255) NULL AFTER password'],
            'shifts' => [
                'code' => 'VARCHAR(20) NULL AFTER name',
                'break_minutes' => 'INT NOT NULL DEFAULT 60 AFTER late_after_minutes',
                'half_day_minutes' => 'INT NOT NULL DEFAULT 240 AFTER break_minutes',
                'full_day_minutes' => 'INT NOT NULL DEFAULT 480 AFTER half_day_minutes',
                'week_offs' => "VARCHAR(20) NOT NULL DEFAULT '0' AFTER full_day_minutes",
            ],
            'attendance' => [
                'shift_id' => 'INT UNSIGNED NULL AFTER attendance_date',
                'break_minutes' => 'INT NOT NULL DEFAULT 0 AFTER working_minutes',
                'early_leave_minutes' => 'INT NOT NULL DEFAULT 0 AFTER late_minutes',
            ],
            'leave_types' => [
                'monthly_accrual' => 'DECIMAL(4,1) NOT NULL DEFAULT 0 AFTER days_per_year',
            ],
        ];
        foreach ($portalCols as $table => $cols) {
            foreach ($cols as $col => $def) {
                // Only add if parent table exists
                $t = $db->query("SHOW TABLES LIKE " . $db->quote($table))->fetch();
                if (!$t) {
                    $log[] = "skip · table {$table} missing";
                    continue 2;
                }
                $addColumn($table, $col, $def);
            }
        }

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
                KEY employee_id (employee_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $log[] = 'ok · attendance_breaks table checked';

        // Employee self-fill QR / WhatsApp invites
        $db->exec(
            "CREATE TABLE IF NOT EXISTS employee_self_fill_invites (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                token VARCHAR(64) NOT NULL UNIQUE,
                title VARCHAR(150) NOT NULL DEFAULT 'Employee details form',
                expires_at DATETIME NOT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_by INT UNSIGNED NULL,
                created_at DATETIME NULL,
                KEY idx_active_exp (is_active, expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $db->exec(
            "CREATE TABLE IF NOT EXISTS employee_self_fill_logs (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                invite_id INT UNSIGNED NOT NULL,
                employee_id INT UNSIGNED NOT NULL,
                ip_address VARCHAR(45) NULL,
                created_at DATETIME NULL,
                KEY invite_id (invite_id),
                KEY employee_id (employee_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $log[] = 'ok · employee self-fill tables checked';

        // Org chart: Founder / Co-Founder (manual names + photos in settings)
        $orgDefaults = [
            'org_founder_name' => '',
            'org_co_founder_name' => '',
            'org_founder_photo' => '',
            'org_co_founder_photo' => '',
        ];
        $putSetting = $db->prepare(
            'INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE updated_at = NOW()'
        );
        foreach ($orgDefaults as $key => $val) {
            $putSetting->execute([$key, $val]);
        }
        $log[] = 'ok · org chart settings keys checked';

        $orgDir = dirname(__DIR__, 2) . '/public/assets/uploads/org';
        if (!is_dir($orgDir)) {
            mkdir($orgDir, 0755, true);
            $log[] = 'ok · created uploads/org folder';
        } else {
            $log[] = 'skip · uploads/org exists';
        }

        $syncVersion = '2026-10-16-org-chart-self-fill-v2';
        $db->prepare(
            "INSERT INTO settings (setting_key, setting_value, updated_at) VALUES ('db_sync_version', ?, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()"
        )->execute([$syncVersion]);
        $db->prepare(
            "INSERT INTO settings (setting_key, setting_value, updated_at) VALUES ('db_sync_last_run', ?, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()"
        )->execute([date('Y-m-d H:i:s')]);

        $log[] = 'ok · sync version ' . $syncVersion;
        $log[] = 'done · ' . date('d-m-Y H:i:s');
        return $log;
    }

    public static function token(): string
    {
        $cfg = require dirname(__DIR__) . '/Config/app.php';
        if (!empty($cfg['db_sync_token'])) {
            return (string) $cfg['db_sync_token'];
        }
        return substr(hash('sha256', ($cfg['key'] ?? 'ocean') . '|db-sync'), 0, 24);
    }
}
