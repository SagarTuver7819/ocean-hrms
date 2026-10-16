<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Employee;
use App\Services\Attendance;
use App\Services\EmployeePhoto;
use App\Services\Leave;
use App\Services\Letterhead;
use App\Services\Payslip;

class EmployeePortalController extends Controller
{
    private function requireEmployee(): int
    {
        $empId = Auth::employeeId();
        if (!$empId) {
            $this->flash('error', 'Employee profile not linked to this account.');
            $this->redirect('dashboard');
        }
        return $empId;
    }

    public function dashboard(): void
    {
        $empId = $this->requireEmployee();
        $db = $this->db();
        $today = date('Y-m-d');

        $leaveBalances = array_values(array_filter(Leave::balances($empId), fn($b) => $b['tracked']));

        $pendingQ = $db->prepare("SELECT COUNT(*) FROM employee_queries WHERE employee_id = ? AND status IN ('open','in_progress')");
        $pendingQ->execute([$empId]);

        $leaves = $db->prepare(
            "SELECT lr.*, lt.name AS leave_type FROM leave_requests lr
             JOIN leave_types lt ON lt.id = lr.leave_type_id
             WHERE lr.employee_id = ? ORDER BY lr.id DESC LIMIT 5"
        );
        $leaves->execute([$empId]);

        $holidays = $db->query(
            'SELECT * FROM holidays WHERE holiday_date >= CURDATE() ORDER BY holiday_date LIMIT 5'
        )->fetchAll();

        $announcements = $db->query(
            'SELECT * FROM announcements WHERE status = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE())
             ORDER BY id DESC LIMIT 5'
        )->fetchAll();

        $monthRows = Database::all("SELECT * FROM attendance WHERE employee_id = ? AND DATE_FORMAT(attendance_date, '%Y-%m') = ?", [$empId, date('Y-m')]);

        $emp = (new Employee())->fullProfile($empId);

        $this->view('employee/dashboard', [
            'title' => 'My Dashboard',
            'page' => 'emp-dashboard',
            'employee' => $emp,
            'snap' => Attendance::snapshot($empId),
            'monthStats' => AttendanceController::monthStats($empId, date('Y-m'), $monthRows),
            'leaveBalances' => $leaveBalances,
            'lastSlip' => Payslip::months($empId)[0] ?? null,
            'pendingQueries' => (int) $pendingQ->fetchColumn(),
            'recentLeaves' => $leaves->fetchAll(),
            'holidays' => $holidays,
            'announcements' => $announcements,
        ]);
    }

    public function profile(): void
    {
        $empId = $this->requireEmployee();
        $employee = (new Employee())->fullProfile($empId);
        $this->view('employee/profile', [
            'title' => 'My Profile',
            'page' => 'emp-profile',
            'employee' => $employee,
        ]);
    }

    public function photo(): void
    {
        $empId = $this->requireEmployee();
        $this->requireCsrf('employee/profile');
        try {
            EmployeePhoto::save($empId, $_FILES['photo'] ?? null);
            $this->flash('success', 'Your profile photo has been updated.');
        } catch (\RuntimeException $ex) {
            $this->flash('error', $ex->getMessage());
        }
        $this->redirect($this->input('back') === 'dashboard' ? 'employee/dashboard' : 'employee/profile');
    }

    public function removePhoto(): void
    {
        $empId = $this->requireEmployee();
        $this->requireCsrf('employee/profile');
        EmployeePhoto::remove($empId);
        $this->flash('success', 'Your profile photo has been removed.');
        $this->redirect('employee/profile');
    }

    public function attendance(): void
    {
        $_GET['from_portal'] = 1;
        (new AttendanceController())->index();
    }

    public function leave(): void
    {
        (new LeaveController())->index();
    }

    public function salary(): void
    {
        $empId = $this->requireEmployee();
        $this->view('employee/salary', [
            'title' => 'Salary Slips',
            'page' => 'emp-salary',
            'months' => Payslip::months($empId),
        ]);
    }

    public function salarySlip(string $month): void
    {
        $empId = $this->requireEmployee();
        $slip = preg_match('/^\d{4}-\d{2}$/', $month) ? Payslip::build($empId, $month) : null;
        if (!$slip) {
            $this->flash('error', 'Salary slip is not available for this month.');
            $this->redirect('employee/salary');
        }
        activity_log('payslip', 'viewed', $empId, null, ['month' => $month]);
        $this->view('payslip/document', [
            'slip' => $slip,
            'letterhead' => Letterhead::config(),
            'backUrl' => url('employee/salary'),
        ], null);
    }

    public function documents(): void
    {
        $empId = $this->requireEmployee();
        $db = $this->db();
        $docs = $db->prepare('SELECT * FROM employee_documents WHERE employee_id = ? ORDER BY id DESC');
        $docs->execute([$empId]);
        $this->view('employee/documents', [
            'title' => 'My Documents',
            'page' => 'emp-documents',
            'documents' => $docs->fetchAll(),
        ]);
    }

    public function queries(): void
    {
        (new QueryController())->index();
    }
}
