<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Ocean Infotech official departments + designations (single source of truth).
 */
class OceanOrgMasters
{
    /** @return list<string> */
    public static function positions(): array
    {
        return [
            'Intern Employee',
            'Junior Designer',
            'Senior Designer',
            'Junior Web Designer',
            'Senior Web Designer',
            'Junior Shopify Developer',
            'Senior Shopify Developer',
            'Junior Wordpress Developer',
            'Senior Wordpress Developer',
            'Junior Web Developer',
            'Senior Web Developer',
            'Sr. Flutter Developer',
            'BDM',
            'BDE',
        ];
    }

    /**
     * Department name => list of designation names under it.
     * @return array<string, list<string>>
     */
    public static function tree(): array
    {
        return [
            'Internship' => ['Intern Employee'],
            'Designing' => ['Junior Designer', 'Senior Designer'],
            'Web Designing' => ['Junior Web Designer', 'Senior Web Designer'],
            'Shopify Development' => ['Junior Shopify Developer', 'Senior Shopify Developer'],
            'Wordpress Development' => ['Junior Wordpress Developer', 'Senior Wordpress Developer'],
            'Web Development' => ['Junior Web Developer', 'Senior Web Developer'],
            'Flutter Development' => ['Sr. Flutter Developer'],
            'Business Development' => ['BDM', 'BDE'],
        ];
    }

    /** @return list<string> human-readable log lines */
    public static function sync(?PDO $db = null): array
    {
        $db = $db ?: Database::connect();
        $log = [];
        $tree = self::tree();
        $keepDepts = array_keys($tree);
        $keepDesigs = self::positions();

        $findDept = $db->prepare('SELECT id FROM departments WHERE name = ? LIMIT 1');
        $insDept = $db->prepare('INSERT INTO departments (name, code, status, created_at) VALUES (?, ?, 1, NOW())');
        $updDept = $db->prepare('UPDATE departments SET code = ?, status = 1, updated_at = NOW() WHERE id = ?');

        $deptIds = [];
        $codes = [
            'Internship' => 'INTERN',
            'Designing' => 'DESIGN',
            'Web Designing' => 'WEBDES',
            'Shopify Development' => 'SHOPIFY',
            'Wordpress Development' => 'WP',
            'Web Development' => 'WEBDEV',
            'Flutter Development' => 'FLUTTER',
            'Business Development' => 'BD',
        ];

        foreach ($keepDepts as $name) {
            $code = $codes[$name] ?? strtoupper(substr(preg_replace('/\W+/', '', $name), 0, 10));
            $findDept->execute([$name]);
            $id = $findDept->fetchColumn();
            if ($id) {
                $updDept->execute([$code, $id]);
                $log[] = "ok · department {$name}";
            } else {
                $insDept->execute([$name, $code]);
                $id = (int) $db->lastInsertId();
                $log[] = "ok · department added {$name}";
            }
            $deptIds[$name] = (int) $id;
        }

        // Deactivate other departments
        $placeholders = implode(',', array_fill(0, count($keepDepts), '?'));
        $stmt = $db->prepare("UPDATE departments SET status = 0, updated_at = NOW() WHERE name NOT IN ({$placeholders}) AND status = 1");
        $stmt->execute($keepDepts);
        if ($stmt->rowCount()) {
            $log[] = 'ok · deactivated ' . $stmt->rowCount() . ' other department(s)';
        }

        $findDes = $db->prepare('SELECT id FROM designations WHERE name = ? LIMIT 1');
        $insDes = $db->prepare('INSERT INTO designations (name, department_id, status, created_at) VALUES (?, ?, 1, NOW())');
        $updDes = $db->prepare('UPDATE designations SET department_id = ?, status = 1, updated_at = NOW() WHERE id = ?');

        foreach ($tree as $deptName => $designations) {
            $deptId = $deptIds[$deptName] ?? null;
            foreach ($designations as $desig) {
                $findDes->execute([$desig]);
                $id = $findDes->fetchColumn();
                if ($id) {
                    $updDes->execute([$deptId, $id]);
                    $log[] = "ok · designation {$desig}";
                } else {
                    $insDes->execute([$desig, $deptId]);
                    $log[] = "ok · designation added {$desig}";
                }
            }
        }

        $ph = implode(',', array_fill(0, count($keepDesigs), '?'));
        $stmt = $db->prepare("UPDATE designations SET status = 0, updated_at = NOW() WHERE name NOT IN ({$ph}) AND status = 1");
        $stmt->execute($keepDesigs);
        if ($stmt->rowCount()) {
            $log[] = 'ok · deactivated ' . $stmt->rowCount() . ' other designation(s)';
        }

        // Recruitment / dashboard position list
        $positionsText = implode("\n", $keepDesigs);
        $db->prepare(
            "INSERT INTO settings (setting_key, setting_value, updated_at) VALUES ('careers_positions', ?, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()"
        )->execute([$positionsText]);
        $log[] = 'ok · careers_positions updated (' . count($keepDesigs) . ' roles)';

        return $log;
    }
}
