<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Employee;
use RuntimeException;

/**
 * Employee portal logins: username = employee code, auto-generated password.
 * The generated password is kept encrypted so HR can show it to the employee;
 * it is cleared as soon as the employee sets their own password.
 */
class EmployeeLogin
{
    public static function generatePassword(): string
    {
        return 'Ocean@' . random_int(1000, 9999);
    }

    /** Creates the login, or resets the password of an existing one. Returns username + plain password. */
    public static function issue(int $employeeId): array
    {
        $emp = Database::first('SELECT * FROM employees WHERE id = ?', [$employeeId]);
        if (!$emp) {
            throw new RuntimeException('Employee not found.');
        }

        $password = self::generatePassword();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $secret = self::encrypt($password);
        $name = trim($emp['first_name'] . ' ' . $emp['last_name']);
        $username = $emp['employee_code'];

        if (!empty($emp['user_id'])) {
            Database::run(
                'UPDATE users SET name = ?, username = ?, password = ?, login_secret = ?, must_change_password = 0, status = 1, updated_at = NOW() WHERE id = ?',
                [$name, $username, $hash, $secret, $emp['user_id']]
            );
            activity_log('employee', 'login_reset', $employeeId);
            return ['username' => $username, 'password' => $password, 'user_id' => (int) $emp['user_id']];
        }

        $email = $emp['official_email'] ?: strtolower($username) . '@oceaninfotech.com';
        if (Database::value('SELECT COUNT(*) FROM users WHERE email = ?', [$email])) {
            $email = strtolower($username) . '.' . $employeeId . '@oceaninfotech.com';
        }

        Database::run(
            'INSERT INTO users (name, username, email, password, login_secret, status, must_change_password, created_at)
             VALUES (?, ?, ?, ?, ?, 1, 0, NOW())',
            [$name, $username, $email, $hash, $secret]
        );
        $userId = (int) Database::connect()->lastInsertId();

        $roleId = Database::value("SELECT id FROM roles WHERE slug = 'employee'");
        if ($roleId) {
            Database::run('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)', [$userId, $roleId]);
        }
        Database::run('UPDATE employees SET user_id = ?, updated_at = NOW() WHERE id = ?', [$userId, $employeeId]);

        (new Employee())->addTimeline($employeeId, 'Employee login created', 'Portal username ' . $username);
        notify($userId, 'Welcome to Ocean HRMS', 'Your employee login is ready. Use the employee portal to punch attendance and apply for leave.', 'employee/dashboard');
        activity_log('employee', 'login_created', $employeeId);

        return ['username' => $username, 'password' => $password, 'user_id' => $userId];
    }

    /** Issues logins for every employee that does not have one yet. Returns the number created. */
    public static function issueMissing(): int
    {
        $ids = Database::all("SELECT id FROM employees WHERE user_id IS NULL AND status NOT IN ('inactive','resigned')");
        foreach ($ids as $row) {
            self::issue((int) $row['id']);
        }
        return count($ids);
    }

    public static function encrypt(string $plain): string
    {
        $iv = random_bytes(16);
        return base64_encode($iv . openssl_encrypt($plain, 'aes-256-cbc', self::key(), OPENSSL_RAW_DATA, $iv));
    }

    public static function decrypt(?string $blob): ?string
    {
        $raw = $blob ? base64_decode($blob, true) : false;
        if ($raw === false || strlen($raw) < 17) {
            return null;
        }
        $plain = openssl_decrypt(substr($raw, 16), 'aes-256-cbc', self::key(), OPENSSL_RAW_DATA, substr($raw, 0, 16));
        return $plain === false ? null : $plain;
    }

    private static function key(): string
    {
        $app = require BASE_PATH . '/app/Config/app.php';
        return hash('sha256', (string) ($app['key'] ?? 'ocean-hrms'), true);
    }
}
