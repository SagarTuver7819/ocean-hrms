<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Models\Employee;

/**
 * Time-limited employee self-fill campaign (QR / WhatsApp).
 * Employees verify with code + mobile, then update their own profile (no salary).
 */
class EmployeeSelfFillController extends Controller
{
    private Employee $employees;

    public function __construct()
    {
        $this->employees = new Employee();
    }

    /** Admin: create invite + QR / WhatsApp share */
    public function admin(): void
    {
        $this->authorize('employee.edit');
        $this->ensureTables();

        $db = $this->db();
        $invites = $db->query(
            "SELECT i.*,
                    (SELECT COUNT(*) FROM employee_self_fill_logs l WHERE l.invite_id = i.id) AS filled_count
             FROM employee_self_fill_invites i
             ORDER BY i.id DESC
             LIMIT 30"
        )->fetchAll();

        $active = null;
        foreach ($invites as $inv) {
            if ((int) $inv['is_active'] === 1 && strtotime($inv['expires_at']) > time()) {
                $active = $inv;
                break;
            }
        }

        $app = require dirname(__DIR__) . '/Config/app.php';
        $fillUrl = null;
        if ($active) {
            $fillUrl = rtrim($app['public_url'] ?: $app['url'], '/') . '/fill/' . $active['token'];
        }

        $this->view('hr/employees/self-fill', [
            'title'   => 'Employee Self-Fill QR',
            'page'    => 'employee-self-fill',
            'invites' => $invites,
            'active'  => $active,
            'fillUrl' => $fillUrl,
            'isLocal' => $fillUrl ? (bool) preg_match('#^https?://(localhost|127\.0\.0\.1)#', $fillUrl) : false,
            'waText'  => $fillUrl
                ? "Namaste,\n\nPlease fill your employee details using this link (valid for limited time):\n{$fillUrl}\n\n— Ocean Infotech HR"
                : '',
        ]);
    }

    public function createInvite(): void
    {
        $this->authorize('employee.edit');
        $this->requireCsrf('employees/self-fill');
        $this->ensureTables();

        $days = max(1, min(30, (int) $this->input('days', 7)));
        $title = trim((string) $this->input('title', 'Employee details form'));
        if ($title === '') {
            $title = 'Employee details form';
        }

        $db = $this->db();
        // Deactivate previous active invites
        $db->exec('UPDATE employee_self_fill_invites SET is_active = 0 WHERE is_active = 1');

        $token = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', time() + ($days * 86400));
        $db->prepare(
            'INSERT INTO employee_self_fill_invites (token, title, expires_at, is_active, created_by, created_at)
             VALUES (?, ?, ?, 1, ?, NOW())'
        )->execute([$token, $title, $expires, Auth::id()]);

        $this->flash('success', "Self-fill link ready · valid {$days} day(s). Share QR / WhatsApp with employees.");
        $this->redirect('employees/self-fill');
    }

    public function revoke(string $id): void
    {
        $this->authorize('employee.edit');
        $this->requireCsrf('employees/self-fill');
        $this->ensureTables();
        $this->db()->prepare('UPDATE employee_self_fill_invites SET is_active = 0 WHERE id = ?')->execute([(int) $id]);
        $this->flash('success', 'Invite link deactivated.');
        $this->redirect('employees/self-fill');
    }

    /** Public mobile form */
    public function form(string $token): void
    {
        $this->ensureTables();
        $invite = $this->findInvite($token);
        if (!$invite) {
            $this->view('employees/self-fill-expired', [
                'title'   => 'Link expired',
                'message' => 'This employee form link is invalid or has expired. Please contact HR for a new QR / link.',
            ], null);
            return;
        }

        $this->view('employees/self-fill-form', [
            'title'  => 'Employee Details',
            'invite' => $invite,
            'token'  => $token,
        ], null);
    }

    /** Public: verify employee code + mobile → return prefill JSON */
    public function verify(string $token): void
    {
        $this->ensureTables();
        if (!$this->findInvite($token)) {
            $this->json(['ok' => false, 'message' => 'This link has expired. Contact HR.'], 410);
        }
        if (!CSRF::validate($this->input('_csrf'))) {
            $this->json(['ok' => false, 'message' => 'Session expired. Refresh and try again.'], 419);
        }

        $code = strtoupper(trim((string) $this->input('employee_code', '')));
        $mobile = preg_replace('/\D/', '', (string) $this->input('mobile', ''));
        if ($code === '' || !preg_match('/^[6-9]\d{9}$/', $mobile)) {
            $this->json(['ok' => false, 'message' => 'Enter valid Employee Code and 10-digit mobile.'], 422);
        }

        $stmt = $this->db()->prepare(
            "SELECT e.*, b.bank_name, b.account_holder_name, b.account_number, b.ifsc_code, b.branch AS bank_branch, b.payment_mode,
                    em.contact_name AS emergency_name, em.relationship AS emergency_relationship,
                    em.mobile AS emergency_mobile, em.alternate_number AS emergency_alternate, em.address AS emergency_address,
                    d.name AS department_name, g.name AS designation_name
             FROM employees e
             LEFT JOIN employee_bank_details b ON b.employee_id = e.id
             LEFT JOIN employee_emergency_contacts em ON em.employee_id = e.id
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN designations g ON g.id = e.designation_id
             WHERE UPPER(e.employee_code) = ? AND e.status NOT IN ('inactive','resigned')
             LIMIT 1"
        );
        $stmt->execute([$code]);
        $emp = $stmt->fetch();
        if (!$emp) {
            $this->json(['ok' => false, 'message' => 'Employee code not found (or exited). Contact HR.'], 404);
        }

        $stored = preg_replace('/\D/', '', (string) ($emp['mobile'] ?? ''));
        // If HR already stored a mobile, it must match; if blank, first self-fill can set it
        if ($stored !== '' && !str_ends_with($stored, $mobile) && $stored !== $mobile) {
            $this->json(['ok' => false, 'message' => 'Mobile number does not match our records for this employee.'], 404);
        }

        $_SESSION['self_fill_emp'] = (int) $emp['id'];
        $_SESSION['self_fill_token'] = $token;

        $this->json([
            'ok' => true,
            'employee' => [
                'id' => (int) $emp['id'],
                'employee_code' => $emp['employee_code'],
                'first_name' => $emp['first_name'],
                'middle_name' => $emp['middle_name'],
                'last_name' => $emp['last_name'],
                'gender' => $emp['gender'],
                'date_of_birth' => $emp['date_of_birth'],
                'blood_group' => $emp['blood_group'],
                'marital_status' => $emp['marital_status'],
                'mobile' => $emp['mobile'],
                'personal_email' => $emp['personal_email'],
                'official_email' => $emp['official_email'],
                'nationality' => $emp['nationality'] ?: 'Indian',
                'current_address' => $emp['current_address'],
                'current_city' => $emp['current_city'],
                'current_state' => $emp['current_state'],
                'current_country' => $emp['current_country'] ?: 'India',
                'current_pincode' => $emp['current_pincode'],
                'permanent_address' => $emp['permanent_address'],
                'permanent_city' => $emp['permanent_city'],
                'permanent_state' => $emp['permanent_state'],
                'permanent_country' => $emp['permanent_country'] ?: 'India',
                'permanent_pincode' => $emp['permanent_pincode'],
                'same_as_current' => (int) ($emp['same_as_current'] ?? 0),
                'pan_number' => $emp['pan_number'],
                'aadhaar_number' => $emp['aadhaar_number'],
                'bank_name' => $emp['bank_name'],
                'account_holder_name' => $emp['account_holder_name'],
                'account_number' => $emp['account_number'],
                'ifsc_code' => $emp['ifsc_code'],
                'bank_branch' => $emp['bank_branch'],
                'payment_mode' => $emp['payment_mode'] ?: 'bank_transfer',
                'emergency_name' => $emp['emergency_name'],
                'emergency_relationship' => $emp['emergency_relationship'],
                'emergency_mobile' => $emp['emergency_mobile'],
                'emergency_alternate' => $emp['emergency_alternate'],
                'emergency_address' => $emp['emergency_address'],
                'department_name' => $emp['department_name'],
                'designation_name' => $emp['designation_name'],
            ],
        ]);
    }

    public function submit(string $token): void
    {
        $this->ensureTables();
        $invite = $this->findInvite($token);
        if (!$invite) {
            $this->json(['ok' => false, 'message' => 'This link has expired. Contact HR.'], 410);
        }
        if (!CSRF::validate($this->input('_csrf'))) {
            $this->json(['ok' => false, 'message' => 'Session expired. Refresh and try again.'], 419);
        }
        if (trim((string) $this->input('website', '')) !== '') {
            $this->json(['ok' => false, 'message' => 'Unable to submit.'], 422);
        }

        $empId = (int) ($_SESSION['self_fill_emp'] ?? 0);
        if ($empId < 1 || ($_SESSION['self_fill_token'] ?? '') !== $token) {
            $this->json(['ok' => false, 'message' => 'Please verify Employee Code & mobile again.'], 403);
        }

        $emp = $this->employees->find($empId);
        if (!$emp || in_array($emp['status'], ['inactive', 'resigned'], true)) {
            $this->json(['ok' => false, 'message' => 'Employee record not available.'], 404);
        }

        $s = fn(string $k, int $max = 255) => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);
        $nullable = fn(string $k, int $max = 255) => ($v = $s($k, $max)) === '' ? null : $v;

        $first = $s('first_name', 80);
        $last = $s('last_name', 80);
        $mobile = preg_replace('/\D/', '', $s('mobile', 20));
        $errors = [];
        if ($first === '' || $last === '') {
            $errors[] = 'First and last name are required.';
        }
        if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
            $errors[] = 'Enter a valid 10-digit mobile.';
        }
        $personalEmail = $s('personal_email', 150);
        if ($personalEmail !== '' && !filter_var($personalEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Personal email is invalid.';
        }
        $pan = strtoupper($s('pan_number', 20));
        if ($pan !== '' && !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan)) {
            $errors[] = 'PAN format looks invalid.';
        }
        $aadhaar = preg_replace('/\D/', '', $s('aadhaar_number', 20));
        if ($aadhaar !== '' && !preg_match('/^\d{12}$/', $aadhaar)) {
            $errors[] = 'Aadhaar must be 12 digits.';
        }
        if ($errors) {
            $this->json(['ok' => false, 'message' => implode(' ', $errors)], 422);
        }

        $same = !empty($_POST['same_as_current']) ? 1 : 0;
        $data = [
            'first_name' => $first,
            'middle_name' => $nullable('middle_name', 80),
            'last_name' => $last,
            'gender' => in_array($s('gender', 20), ['male', 'female', 'other'], true) ? $s('gender', 20) : null,
            'date_of_birth' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $s('date_of_birth', 10)) ? $s('date_of_birth', 10) : null,
            'blood_group' => $nullable('blood_group', 10),
            'marital_status' => $nullable('marital_status', 40),
            'mobile' => $mobile,
            'personal_email' => $personalEmail !== '' ? strtolower($personalEmail) : null,
            'nationality' => $nullable('nationality', 80) ?: 'Indian',
            'current_address' => $nullable('current_address', 1000),
            'current_city' => $nullable('current_city', 80),
            'current_state' => $nullable('current_state', 80),
            'current_country' => $nullable('current_country', 80) ?: 'India',
            'current_pincode' => $nullable('current_pincode', 20),
            'same_as_current' => $same,
            'pan_number' => $pan !== '' ? $pan : null,
            'aadhaar_number' => $aadhaar !== '' ? $aadhaar : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($same) {
            $data['permanent_address'] = $data['current_address'];
            $data['permanent_city'] = $data['current_city'];
            $data['permanent_state'] = $data['current_state'];
            $data['permanent_country'] = $data['current_country'];
            $data['permanent_pincode'] = $data['current_pincode'];
        } else {
            $data['permanent_address'] = $nullable('permanent_address', 1000);
            $data['permanent_city'] = $nullable('permanent_city', 80);
            $data['permanent_state'] = $nullable('permanent_state', 80);
            $data['permanent_country'] = $nullable('permanent_country', 80) ?: 'India';
            $data['permanent_pincode'] = $nullable('permanent_pincode', 20);
        }
        // Official email only if empty on record (HR-owned)
        if (empty($emp['official_email'])) {
            $off = strtolower($s('official_email', 150));
            if ($off !== '' && filter_var($off, FILTER_VALIDATE_EMAIL)) {
                $data['official_email'] = $off;
            }
        }

        $this->employees->update($empId, $data);

        $db = $this->db();
        $bank = [
            $nullable('bank_name', 120),
            $nullable('account_holder_name', 120),
            $nullable('account_number', 60),
            strtoupper($s('ifsc_code', 20)) ?: null,
            $nullable('bank_branch', 120),
            in_array($s('payment_mode', 40), ['bank_transfer', 'cheque', 'cash'], true) ? $s('payment_mode', 40) : 'bank_transfer',
        ];
        $exists = $db->prepare('SELECT id FROM employee_bank_details WHERE employee_id = ?');
        $exists->execute([$empId]);
        if ($exists->fetch()) {
            $db->prepare(
                'UPDATE employee_bank_details SET bank_name=?, account_holder_name=?, account_number=?, ifsc_code=?, branch=?, payment_mode=?, updated_at=NOW() WHERE employee_id=?'
            )->execute([...$bank, $empId]);
        } else {
            $db->prepare(
                'INSERT INTO employee_bank_details (employee_id, bank_name, account_holder_name, account_number, ifsc_code, branch, payment_mode, created_at)
                 VALUES (?,?,?,?,?,?,?,NOW())'
            )->execute([$empId, ...$bank]);
        }

        $emergency = [
            $nullable('emergency_name', 120),
            $nullable('emergency_relationship', 80),
            preg_replace('/\D/', '', $s('emergency_mobile', 20)) ?: null,
            preg_replace('/\D/', '', $s('emergency_alternate', 20)) ?: null,
            $nullable('emergency_address', 500),
        ];
        $exists = $db->prepare('SELECT id FROM employee_emergency_contacts WHERE employee_id = ?');
        $exists->execute([$empId]);
        if ($exists->fetch()) {
            $db->prepare(
                'UPDATE employee_emergency_contacts SET contact_name=?, relationship=?, mobile=?, alternate_number=?, address=? WHERE employee_id=?'
            )->execute([...$emergency, $empId]);
        } else {
            $db->prepare(
                'INSERT INTO employee_emergency_contacts (employee_id, contact_name, relationship, mobile, alternate_number, address, created_at)
                 VALUES (?,?,?,?,?,?,NOW())'
            )->execute([$empId, ...$emergency]);
        }

        $db->prepare(
            'INSERT INTO employee_self_fill_logs (invite_id, employee_id, ip_address, created_at) VALUES (?, ?, ?, NOW())'
        )->execute([(int) $invite['id'], $empId, $_SERVER['REMOTE_ADDR'] ?? null]);

        $this->employees->addTimeline($empId, 'Profile self-filled', 'Employee updated personal / bank / KYC / emergency via QR form');

        unset($_SESSION['self_fill_emp'], $_SESSION['self_fill_token']);

        $this->json([
            'ok' => true,
            'message' => 'Thank you! Your details are saved in Ocean HRMS.',
            'employee_code' => $emp['employee_code'],
        ]);
    }

    private function findInvite(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            return null;
        }
        $stmt = $this->db()->prepare(
            'SELECT * FROM employee_self_fill_invites
             WHERE token = ? AND is_active = 1 AND expires_at > NOW() LIMIT 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function ensureTables(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $db = $this->db();
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
        $done = true;
    }

    private function authorize(string $permission): void
    {
        if (!Auth::check() || !Auth::hasPermission($permission)) {
            $this->flash('error', 'Access denied.');
            $this->redirect('login');
        }
    }
}
