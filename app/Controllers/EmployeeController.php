<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Employee;
use App\Services\EmployeeLogin;
use App\Services\EmployeePhoto;
use App\Services\Salary;

class EmployeeController extends Controller
{
    private Employee $employees;

    public function __construct()
    {
        $this->employees = new Employee();
    }

    public function index(): void
    {
        $this->authorize('employee.view');
        $filters = [
            'department_id' => $this->input('department_id'),
            'designation_id' => $this->input('designation_id'),
            'status' => $this->input('status'),
            'employment_type' => $this->input('employment_type'),
            'q' => $this->input('q'),
        ];
        $db = $this->db();
        $this->view('hr/employees/index', [
            'title' => 'Employees',
            'page' => 'employees',
            'employees' => $this->employees->listFiltered($filters),
            'departments' => $db->query('SELECT id, name FROM departments WHERE status = 1 ORDER BY name')->fetchAll(),
            'designations' => $db->query('SELECT id, name FROM designations WHERE status = 1 ORDER BY name')->fetchAll(),
            'filters' => $filters,
        ]);
    }

    public function create(): void
    {
        $this->authorize('employee.create');
        $this->view('hr/employees/form', $this->formData([
            'title' => 'Add Employee',
            'page' => 'employees',
            'employee' => null,
            'mode' => 'create',
            'nextCode' => $this->employees->nextCode(),
        ]));
    }

    public function store(): void
    {
        $this->authorize('employee.create');
        $this->requireCsrf('employees/create', 'Invalid security token.');

        $data = $this->collectEmployeeData();
        $data['employee_code'] = $data['employee_code'] ?: $this->employees->nextCode();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $id = $this->employees->create($data);
        $this->saveRelated($id);
        $this->employees->addTimeline($id, 'Employee joined', $data['first_name'] . ' ' . $data['last_name'] . ' profile created', $data['joining_date'] ?? date('Y-m-d'));
        activity_log('employee', 'created', $id, null, $data);
        $login = EmployeeLogin::issue($id);
        $_SESSION['temp_credentials'] = ['employee_code' => $login['username'], 'password' => $login['password']];
        $this->flash('success', 'Employee created · Login ' . $login['username'] . ' / ' . $login['password']);
        $this->redirect('employees/' . $id);
    }

    public function show(string $id): void
    {
        $this->authorize('employee.view');
        $employee = $this->employees->fullProfile((int) $id);
        if (!$employee) {
            $this->flash('error', 'Employee not found.');
            $this->redirect('employees');
        }

        $db = $this->db();
        $attendance = $db->prepare('SELECT * FROM attendance WHERE employee_id = ? ORDER BY attendance_date DESC LIMIT 10');
        $attendance->execute([(int) $id]);
        $leaves = $db->prepare(
            "SELECT lr.*, lt.name AS leave_type FROM leave_requests lr
             JOIN leave_types lt ON lt.id = lr.leave_type_id
             WHERE lr.employee_id = ? ORDER BY lr.id DESC LIMIT 10"
        );
        $leaves->execute([(int) $id]);
        $candidate = $db->prepare('SELECT id FROM candidates WHERE employee_id = ? LIMIT 1');
        $candidate->execute([(int) $id]);

        $this->view('hr/employees/show', [
            'candidateId' => (int) $candidate->fetchColumn() ?: null,
            'title' => $employee['first_name'] . ' ' . $employee['last_name'],
            'page' => 'employees',
            'employee' => $employee,
            'salary' => Salary::current((int) $id),
            'attendance' => $attendance->fetchAll(),
            'leaves' => $leaves->fetchAll(),
            'tab' => $this->input('tab', 'overview'),
        ]);
    }

    public function edit(string $id): void
    {
        $this->authorize('employee.edit');
        $employee = $this->employees->fullProfile((int) $id);
        if (!$employee) {
            $this->flash('error', 'Employee not found.');
            $this->redirect('employees');
        }
        $this->view('hr/employees/form', $this->formData([
            'title' => 'Edit Employee',
            'page' => 'employees',
            'employee' => $employee,
            'mode' => 'edit',
            'nextCode' => $employee['employee_code'],
        ]));
    }

    public function update(string $id): void
    {
        $this->authorize('employee.edit');
        $this->requireCsrf('employees/' . $id . '/edit', 'Invalid security token.');
        $old = $this->employees->find((int) $id);
        if (!$old) {
            $this->flash('error', 'Employee not found.');
            $this->redirect('employees');
        }
        $data = $this->collectEmployeeData();
        unset($data['employee_code']); // code immutable
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->employees->update((int) $id, $data);
        $this->saveRelated((int) $id);
        activity_log('employee', 'updated', (int) $id, $old, $data);
        $this->flash('success', 'Employee updated successfully.');
        $this->redirect('employees/' . $id);
    }

    public function destroy(string $id): void
    {
        $this->authorize('employee.delete');
        $this->requireCsrf('employees');
        $emp = $this->employees->find((int) $id);
        if (!$emp) {
            $this->flash('error', 'Employee not found.');
            $this->redirect('employees');
        }
        $exitDate = $this->input('exit_date') ?: date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $exitDate)) {
            $exitDate = date('Y-m-d');
        }
        $this->employees->update((int) $id, [
            'status' => 'resigned',
            'exit_date' => $exitDate,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        if (!empty($emp['user_id'])) {
            $this->db()->prepare('UPDATE users SET status = 0, updated_at = NOW() WHERE id = ?')->execute([(int) $emp['user_id']]);
        }
        $this->employees->addTimeline((int) $id, 'Employee exited', 'Marked resigned · Exit date ' . date('d-m-Y', strtotime($exitDate)), $exitDate);
        activity_log('employee', 'deleted', (int) $id, $emp, ['status' => 'resigned', 'exit_date' => $exitDate]);
        $this->flash('success', $emp['employee_code'] . ' marked as exited (resigned). Login disabled.');
        $this->redirect('employees?status=inactive');
    }

    public function createLogin(string $id): void
    {
        $this->authorize('employee.edit');
        $back = $this->input('back') === 'logins' ? 'employees/logins' : 'employees/' . $id;
        $this->requireCsrf($back, 'Invalid security token.');

        if (!$this->employees->find((int) $id)) {
            $this->flash('error', 'Employee not found.');
            $this->redirect('employees/logins');
        }
        $login = EmployeeLogin::issue((int) $id);
        $_SESSION['temp_credentials'] = ['employee_code' => $login['username'], 'password' => $login['password']];
        $this->flash('success', 'Login ready · Username ' . $login['username'] . ' · Password ' . $login['password']);
        $this->redirect($back);
    }

    public function logins(): void
    {
        $this->authorize('employee.edit');
        $this->view('hr/employees/logins', [
            'title' => 'Employee Logins',
            'page' => 'employee-logins',
            'rows' => $this->loginRows(),
        ]);
    }

    /** Excel/CSV download: ?type=all|active|intern */
    public function exportLogins(): void
    {
        $this->authorize('employee.edit');
        $type = strtolower((string) $this->input('type', 'all'));
        if (!in_array($type, ['all', 'active', 'intern'], true)) {
            $type = 'all';
        }

        $rows = $this->loginRows();
        if ($type === 'active') {
            $rows = array_values(array_filter(
                $rows,
                fn($r) => in_array($r['emp_status'] ?? '', ['active', 'probation'], true)
            ));
        } elseif ($type === 'intern') {
            $rows = array_values(array_filter(
                $rows,
                fn($r) => ($r['employment_type'] ?? '') === 'intern'
                    || strcasecmp((string) ($r['department_name'] ?? ''), 'Internship') === 0
            ));
        }

        $labels = [
            'all' => 'All_Employee_Logins',
            'active' => 'Active_Employees_Login_Sheet',
            'intern' => 'Internship_Employees_Login_Sheet',
        ];
        $filename = ($labels[$type] ?? 'Employee_Logins') . '_' . date('Ymd_Hi') . '.csv';
        $portalUrl = url('login');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, [
            'Sr', 'Employee Code', 'Name', 'Department', 'Designation',
            'Employment Type', 'Status', 'Mobile',
            'Username (Login ID)', 'Password', 'Login Status', 'Last Login', 'Portal URL', 'Notes',
        ]);

        foreach ($rows as $i => $r) {
            $pwd = $r['password'];
            $note = '';
            if (empty($r['user_id'])) {
                $pwd = '';
                $note = 'No login yet';
            } elseif ($pwd === null) {
                $pwd = '';
                $note = 'Password changed by employee — reset from Employee Logins';
            }
            $loginStatus = empty($r['user_id']) ? 'None' : (!empty($r['login_status']) ? 'Active' : 'Disabled');
            fputcsv($out, [
                $i + 1,
                $r['employee_code'] ?? '',
                trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                $r['department_name'] ?? '',
                $r['designation_name'] ?? '',
                ucwords(str_replace('_', ' ', (string) ($r['employment_type'] ?? ''))),
                ucfirst((string) ($r['emp_status'] ?? '')),
                $r['mobile'] ?? '',
                $r['username'] ?? ($r['employee_code'] ?? ''),
                $pwd,
                $loginStatus,
                !empty($r['last_login_at']) ? date('d-M-Y h:i A', strtotime($r['last_login_at'])) : 'Never',
                $portalUrl,
                $note,
            ]);
        }
        fclose($out);
        exit;
    }

    /** @return list<array<string,mixed>> */
    private function loginRows(): array
    {
        $rows = $this->db()->query(
            "SELECT e.id, e.employee_code, e.first_name, e.last_name, e.status AS emp_status,
                    e.employment_type, e.mobile,
                    d.name AS department_name, g.name AS designation_name,
                    u.id AS user_id, u.username, u.login_secret, u.status AS login_status, u.last_login_at
             FROM employees e
             LEFT JOIN users u ON u.id = e.user_id
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN designations g ON g.id = e.designation_id
             ORDER BY e.employee_code"
        )->fetchAll();
        foreach ($rows as &$r) {
            $r['password'] = EmployeeLogin::decrypt($r['login_secret']);
        }
        unset($r);
        return $rows;
    }

    public function issueAllLogins(): void
    {
        $this->authorize('employee.edit');
        $this->requireCsrf('employees/logins');
        $count = EmployeeLogin::issueMissing();
        $this->flash('success', $count ? $count . ' employee login(s) generated.' : 'Every employee already has a login.');
        $this->redirect('employees/logins');
    }

    public function toggleLogin(string $id): void
    {
        $this->authorize('employee.edit');
        $this->requireCsrf('employees/logins');
        $emp = $this->employees->find((int) $id);
        if (empty($emp['user_id'])) {
            $this->flash('error', 'This employee has no login yet.');
            $this->redirect('employees/logins');
        }
        $this->db()->prepare('UPDATE users SET status = 1 - status, updated_at = NOW() WHERE id = ?')->execute([$emp['user_id']]);
        activity_log('employee', 'login_toggled', (int) $id);
        $this->flash('success', 'Login status updated for ' . $emp['employee_code'] . '.');
        $this->redirect('employees/logins');
    }

    public function photo(string $id): void
    {
        $this->authorize('employee.edit');
        $this->requireCsrf('employees/' . (int) $id);
        try {
            EmployeePhoto::save((int) $id, $_FILES['photo'] ?? null);
            $this->flash('success', 'Profile photo updated.');
        } catch (\RuntimeException $ex) {
            $this->flash('error', $ex->getMessage());
        }
        $this->redirect('employees/' . (int) $id);
    }

    public function removePhoto(string $id): void
    {
        $this->authorize('employee.edit');
        $this->requireCsrf('employees/' . (int) $id);
        EmployeePhoto::remove((int) $id);
        $this->flash('success', 'Profile photo removed.');
        $this->redirect('employees/' . (int) $id);
    }

    public function uploadDocument(string $id): void
    {
        $this->authorize('employee.edit');
        $this->requireCsrf('employees/' . $id . '?tab=documents', 'Invalid security token.');

        if (empty($_FILES['document']['name'])) {
            $this->flash('error', 'Please select a file.');
            $this->redirect('employees/' . $id . '?tab=documents');
        }

        $file = $_FILES['document'];
        $app = require BASE_PATH . '/app/Config/app.php';
        if ($file['size'] > $app['upload_max']) {
            $this->flash('error', 'File exceeds 5MB limit.');
            $this->redirect('employees/' . $id . '?tab=documents');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $app['allowed_mimes'], true)) {
            $this->flash('error', 'Invalid file type.');
            $this->redirect('employees/' . $id . '?tab=documents');
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $dir = BASE_PATH . '/storage/uploads/employees/' . (int) $id;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = uniqid('doc_', true) . '.' . strtolower($ext);
        $dest = $dir . '/' . $filename;
        move_uploaded_file($file['tmp_name'], $dest);

        $db = $this->db();
        $stmt = $db->prepare(
            'INSERT INTO employee_documents (employee_id, document_name, document_type, file_path, upload_date, expiry_date, uploaded_by, status, created_at)
             VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, NOW())'
        );
        $relPath = 'storage/uploads/employees/' . (int) $id . '/' . $filename;
        $stmt->execute([
            (int) $id,
            $this->input('document_name', $file['name']),
            $this->input('document_type', 'other'),
            $relPath,
            $this->input('expiry_date') ?: null,
            Auth::id(),
            'pending',
        ]);
        $this->employees->addTimeline((int) $id, 'Document uploaded', (string) $this->input('document_name', $file['name']));
        $this->flash('success', 'Document uploaded.');
        $this->redirect('employees/' . $id . '?tab=documents');
    }

    private function authorize(string $permission): void
    {
        if (!can($permission) && !can('*')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }
    }

    private function formData(array $extra): array
    {
        $db = $this->db();
        return array_merge($extra, [
            'departments' => $db->query('SELECT id, name FROM departments WHERE status = 1 ORDER BY name')->fetchAll(),
            'designations' => $db->query('SELECT id, name FROM designations WHERE status = 1 ORDER BY name')->fetchAll(),
            'teams' => $db->query('SELECT id, name FROM teams WHERE status = 1 ORDER BY name')->fetchAll(),
            'branches' => $db->query('SELECT id, name FROM branches WHERE status = 1 ORDER BY name')->fetchAll(),
            'shifts' => $db->query('SELECT id, name FROM shifts WHERE status = 1 ORDER BY name')->fetchAll(),
            'managers' => $db->query("SELECT id, CONCAT(first_name,' ',last_name,' (',employee_code,')') AS name FROM employees WHERE status IN ('active','probation') ORDER BY first_name")->fetchAll(),
        ]);
    }

    private function collectEmployeeData(): array
    {
        $fields = [
            'employee_code','first_name','middle_name','last_name','gender','date_of_birth','blood_group',
            'mobile','personal_email','official_email','marital_status','nationality',
            'current_address','current_city','current_state','current_country','current_pincode',
            'permanent_address','permanent_city','permanent_state','permanent_country','permanent_pincode',
            'joining_date','employment_type','work_location','confirmation_date','exit_date',
            'pan_number','aadhaar_number','status',
        ];
        $data = [];
        foreach ($fields as $f) {
            $val = $this->input($f);
            $data[$f] = $val !== '' ? $val : null;
        }
        if (in_array($data['status'] ?? '', ['inactive', 'resigned'], true) && empty($data['exit_date'])) {
            $data['exit_date'] = date('Y-m-d');
        }
        if (!in_array($data['status'] ?? '', ['inactive', 'resigned'], true)) {
            // keep exit_date if already set historically; only clear when moving back to active-like status without a date posted
            if (($this->input('exit_date') ?? '') === '') {
                $data['exit_date'] = null;
            }
        }
        $data['same_as_current'] = $this->input('same_as_current') ? 1 : 0;
        if ($data['same_as_current']) {
            $data['permanent_address'] = $data['current_address'];
            $data['permanent_city'] = $data['current_city'];
            $data['permanent_state'] = $data['current_state'];
            $data['permanent_country'] = $data['current_country'];
            $data['permanent_pincode'] = $data['current_pincode'];
        }
        foreach (['department_id','designation_id','reporting_manager_id','team_id','branch_id','shift_id','probation_period_days','notice_period_days'] as $intField) {
            $v = $this->input($intField);
            $data[$intField] = $v !== '' && $v !== null ? (int) $v : null;
        }
        return $data;
    }

    private function saveRelated(int $employeeId): void
    {
        $db = $this->db();
        $bank = [
            'bank_name' => $this->input('bank_name'),
            'account_holder_name' => $this->input('account_holder_name'),
            'account_number' => $this->input('account_number'),
            'ifsc_code' => $this->input('ifsc_code'),
            'branch' => $this->input('bank_branch'),
            'payment_mode' => $this->input('payment_mode', 'bank_transfer'),
        ];
        $exists = $db->prepare('SELECT id FROM employee_bank_details WHERE employee_id = ?');
        $exists->execute([$employeeId]);
        if ($exists->fetch()) {
            $db->prepare(
                'UPDATE employee_bank_details SET bank_name=?, account_holder_name=?, account_number=?, ifsc_code=?, branch=?, payment_mode=?, updated_at=NOW() WHERE employee_id=?'
            )->execute([...array_values($bank), $employeeId]);
        } else {
            $db->prepare(
                'INSERT INTO employee_bank_details (employee_id, bank_name, account_holder_name, account_number, ifsc_code, branch, payment_mode, created_at)
                 VALUES (?,?,?,?,?,?,?,NOW())'
            )->execute([$employeeId, ...array_values($bank)]);
        }

        $emergency = [
            $this->input('emergency_name'),
            $this->input('emergency_relationship'),
            $this->input('emergency_mobile'),
            $this->input('emergency_alternate'),
            $this->input('emergency_address'),
        ];
        $exists = $db->prepare('SELECT id FROM employee_emergency_contacts WHERE employee_id = ?');
        $exists->execute([$employeeId]);
        if ($exists->fetch()) {
            $db->prepare(
                'UPDATE employee_emergency_contacts SET contact_name=?, relationship=?, mobile=?, alternate_number=?, address=? WHERE employee_id=?'
            )->execute([...$emergency, $employeeId]);
        } else {
            $db->prepare(
                'INSERT INTO employee_emergency_contacts (employee_id, contact_name, relationship, mobile, alternate_number, address, created_at)
                 VALUES (?,?,?,?,?,?,NOW())'
            )->execute([$employeeId, ...$emergency]);
        }
    }
}
