<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Models\Employee;

/**
 * Time-limited employee self-fill campaign (QR / WhatsApp).
 * Always ADD NEW employee (like Add Employee) · auto code · never edits existing · no salary.
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
                ? "Namaste,\n\nPlease register as a NEW employee using this link (limited time).\nA new employee code will be auto-generated.\n{$fillUrl}\n\n— Ocean Infotech HR"
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

        $db = $this->db();
        $departments = $db->query('SELECT id, name FROM departments WHERE status = 1 ORDER BY name')->fetchAll();
        $designations = $db->query(
            'SELECT id, name, department_id FROM designations WHERE status = 1 ORDER BY name'
        )->fetchAll();

        $this->view('employees/self-fill-form', [
            'title'         => 'Employee Details',
            'invite'        => $invite,
            'token'         => $token,
            'nextCode'      => $this->employees->nextCode(),
            'departments'   => $departments,
            'designations'  => $designations,
        ], null);
    }

    /** Legacy endpoint — form no longer verifies by code */
    public function verify(string $token): void
    {
        $this->json(['ok' => false, 'message' => 'Verification not required. Employee code is auto-generated.'], 410);
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
        if (empty($_POST['declaration'])) {
            $errors[] = 'Please accept the declaration.';
        }

        $designationId = (int) $this->input('designation_id', 0);
        $departmentId = (int) $this->input('department_id', 0);
        $desRow = null;
        if ($designationId > 0) {
            $st = $this->db()->prepare('SELECT id, department_id FROM designations WHERE id = ? AND status = 1');
            $st->execute([$designationId]);
            $desRow = $st->fetch();
            if (!$desRow) {
                $errors[] = 'Invalid designation selected.';
            } elseif ($departmentId < 1 && !empty($desRow['department_id'])) {
                $departmentId = (int) $desRow['department_id'];
            }
        }
        if ($departmentId > 0) {
            $st = $this->db()->prepare('SELECT id FROM departments WHERE id = ? AND status = 1');
            $st->execute([$departmentId]);
            if (!$st->fetch()) {
                $errors[] = 'Invalid department selected.';
            }
        }

        $employmentType = $s('employment_type', 40);
        if ($employmentType !== '' && !in_array($employmentType, ['full_time', 'part_time', 'intern', 'freelancer', 'contract', 'consultant'], true)) {
            $employmentType = 'full_time';
        }
        $joiningDate = $s('joining_date', 10);
        if ($joiningDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $joiningDate)) {
            $errors[] = 'Joining date is invalid.';
        }

        if ($errors) {
            $this->json(['ok' => false, 'message' => implode(' ', $errors)], 422);
        }

        // Self-fill = ADD NEW employee only (never edit existing)
        $dup = $this->db()->prepare(
            "SELECT employee_code FROM employees
             WHERE REPLACE(REPLACE(REPLACE(IFNULL(mobile,''),' ',''),'-',''),'+','') LIKE ?
               AND status NOT IN ('inactive','resigned')
             LIMIT 1"
        );
        $dup->execute(['%' . $mobile]);
        if ($dupCode = $dup->fetchColumn()) {
            $this->json([
                'ok' => false,
                'message' => 'This mobile is already registered (' . $dupCode . '). Self-fill only adds NEW employees. Contact HR to edit an existing profile.',
            ], 422);
        }

        $same = !empty($_POST['same_as_current']) ? 1 : 0;
        $off = strtolower($s('official_email', 150));
        $code = $this->employees->nextCode();
        $data = [
            'employee_code' => $code,
            'first_name' => $first,
            'middle_name' => $nullable('middle_name', 80),
            'last_name' => $last,
            'gender' => in_array($s('gender', 20), ['male', 'female', 'other'], true) ? $s('gender', 20) : null,
            'date_of_birth' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $s('date_of_birth', 10)) ? $s('date_of_birth', 10) : null,
            'blood_group' => $nullable('blood_group', 10),
            'marital_status' => $nullable('marital_status', 40),
            'mobile' => $mobile,
            'personal_email' => $personalEmail !== '' ? strtolower($personalEmail) : null,
            'official_email' => ($off !== '' && filter_var($off, FILTER_VALIDATE_EMAIL)) ? $off : null,
            'nationality' => $nullable('nationality', 80) ?: 'Indian',
            'current_address' => $nullable('current_address', 1000),
            'current_city' => $nullable('current_city', 80),
            'current_state' => $nullable('current_state', 80),
            'current_country' => $nullable('current_country', 80) ?: 'India',
            'current_pincode' => $nullable('current_pincode', 20),
            'same_as_current' => $same,
            'pan_number' => $pan !== '' ? $pan : null,
            'aadhaar_number' => $aadhaar !== '' ? $aadhaar : null,
            'department_id' => $departmentId > 0 ? $departmentId : null,
            'designation_id' => $designationId > 0 ? $designationId : null,
            'employment_type' => $employmentType !== '' ? $employmentType : 'full_time',
            'work_location' => $nullable('work_location', 120),
            'joining_date' => $joiningDate !== '' ? $joiningDate : date('Y-m-d'),
            'status' => 'onboarding',
            'created_at' => date('Y-m-d H:i:s'),
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

        $db = $this->db();
        try {
            $empId = $this->employees->create($data);
        } catch (\Throwable $e) {
            // Race on employee_code — retry once with fresh code
            $data['employee_code'] = $this->employees->nextCode();
            $code = $data['employee_code'];
            $empId = $this->employees->create($data);
        }
        $this->employees->addTimeline($empId, 'Employee joined', $first . ' ' . $last . ' added via self-fill QR (new employee)', $data['joining_date']);
        activity_log('employee', 'created', $empId, null, $data);

        try {
            \App\Services\EmployeeLogin::issue($empId);
        } catch (\Throwable $e) {
            // Login optional for self-fill; HR can issue later
        }

        $msg = 'Thank you! New employee profile created in Ocean HRMS.';

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

        $this->json([
            'ok' => true,
            'message' => $msg,
            'employee_code' => $code,
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
