<?php

namespace App\Core;

use PDO;

class Auth
{
    public static function attempt(string $login, string $password, string $portal = 'admin'): bool
    {
        $db = Database::connect();

        if ($portal === 'employee') {
            $stmt = $db->prepare(
                "SELECT u.*, e.id AS employee_id, e.employee_code, e.first_name, e.last_name, e.status AS emp_status
                 FROM users u
                 INNER JOIN employees e ON e.user_id = u.id
                 WHERE (u.email = ? OR e.employee_code = ?) AND u.status = 1
                 LIMIT 1"
            );
            $stmt->execute([$login, $login]);
        } else {
            $stmt = $db->prepare(
                "SELECT * FROM users WHERE (email = ? OR username = ?) AND status = 1 LIMIT 1"
            );
            $stmt->execute([$login, $login]);
        }

        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password'])) {
            self::recordFailedAttempt($login);
            return false;
        }

        if ($portal === 'employee' && empty($user['employee_id'])) {
            return false;
        }

        // Employee-only accounts always land in the employee portal, whichever tab was used
        if ($portal === 'admin' && self::loadRoles((int) $user['id']) === ['employee']) {
            $emp = $db->prepare('SELECT id AS employee_id, employee_code, first_name, last_name FROM employees WHERE user_id = ?');
            $emp->execute([$user['id']]);
            if ($row = $emp->fetch()) {
                $user = array_merge($user, $row);
                $portal = 'employee';
            }
        }

        self::clearFailedAttempts($login);
        self::login($user, $portal);
        return true;
    }

    public static function login(array $user, string $portal = 'admin'): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'            => (int) $user['id'],
            'name'          => $user['name'] ?? trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
            'email'         => $user['email'],
            'username'      => $user['username'] ?? null,
            'portal'        => $portal,
            'employee_id'   => isset($user['employee_id']) ? (int) $user['employee_id'] : null,
            'employee_code' => $user['employee_code'] ?? null,
            'must_change_password' => (int) ($user['must_change_password'] ?? 0),
        ];
        $_SESSION['permissions'] = self::loadPermissions((int) $user['id']);
        $_SESSION['roles'] = self::loadRoles((int) $user['id']);
        $_SESSION['last_activity'] = time();

        $db = Database::connect();
        $stmt = $db->prepare('UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?');
        $stmt->execute([$_SERVER['REMOTE_ADDR'] ?? null, $user['id']]);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function hasPermission(string $permission): bool
    {
        $perms = $_SESSION['permissions'] ?? [];
        if (in_array('*', $perms, true) || in_array($permission, $perms, true)) {
            return true;
        }
        return false;
    }

    public static function hasRole(string $role): bool
    {
        return in_array($role, $_SESSION['roles'] ?? [], true);
    }

    public static function isEmployeePortal(): bool
    {
        return ($_SESSION['user']['portal'] ?? '') === 'employee';
    }

    public static function employeeId(): ?int
    {
        return $_SESSION['user']['employee_id'] ?? null;
    }

    private static function loadPermissions(int $userId): array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT DISTINCT p.slug
             FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             INNER JOIN user_roles ur ON ur.role_id = rp.role_id
             WHERE ur.user_id = ?"
        );
        $stmt->execute([$userId]);
        $slugs = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $roleStmt = $db->prepare(
            "SELECT r.slug FROM roles r
             INNER JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = ?"
        );
        $roleStmt->execute([$userId]);
        $roles = $roleStmt->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('super_admin', $roles, true)) {
            return ['*'];
        }
        return $slugs;
    }

    private static function loadRoles(int $userId): array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT r.slug FROM roles r
             INNER JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = ?"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    private static function recordFailedAttempt(string $login): void
    {
        $key = 'login_fail_' . md5(strtolower($login));
        $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
        $_SESSION[$key . '_time'] = time();
    }

    private static function clearFailedAttempts(string $login): void
    {
        $key = 'login_fail_' . md5(strtolower($login));
        unset($_SESSION[$key], $_SESSION[$key . '_time']);
    }

    public static function isLocked(string $login): bool
    {
        $key = 'login_fail_' . md5(strtolower($login));
        $fails = (int) ($_SESSION[$key] ?? 0);
        $time = (int) ($_SESSION[$key . '_time'] ?? 0);
        if ($fails >= 5 && (time() - $time) < 900) {
            return true;
        }
        if ($fails >= 5 && (time() - $time) >= 900) {
            self::clearFailedAttempts($login);
        }
        return false;
    }
}
