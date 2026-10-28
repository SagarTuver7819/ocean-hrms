<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Employee;
use App\Services\Salary;
use App\Services\SalaryRegister;

class SalaryController extends Controller
{
    private function guard(string $permission): void
    {
        if (!can($permission) && !can('*')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }
    }

    public function index(): void
    {
        $this->guard('salary.view');
        $this->view('hr/salary/index', [
            'title' => 'Employee Salary',
            'page'  => 'salary',
            'rows'  => Database::all(
                "SELECT es.*, e.employee_code, e.first_name, e.last_name, e.employment_type,
                        d.name AS department_name, g.name AS designation_name
                 FROM employee_salaries es
                 JOIN employees e ON e.id = es.employee_id
                 LEFT JOIN departments d ON d.id = e.department_id
                 LEFT JOIN designations g ON g.id = e.designation_id
                 ORDER BY e.first_name"
            ),
        ]);
    }

    /** Monthly salary register from decided salary + attendance. Interns off by default. */
    public function register(): void
    {
        $this->guard('salary.view');
        $month = (string) $this->input('month', date('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }
        $includeInterns = (bool) $this->input('include_interns');
        $data = SalaryRegister::build($month, $includeInterns);
        $this->view('hr/salary/register', [
            'title' => 'Salary Register',
            'page' => 'salary-register',
            'register' => $data,
            'month' => $data['month'],
            'includeInterns' => $includeInterns,
        ]);
    }

    /** Print / Save as PDF (browser print). */
    public function registerPdf(): void
    {
        $this->guard('salary.view');
        $month = (string) $this->input('month', date('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }
        $includeInterns = (bool) $this->input('include_interns');
        $data = SalaryRegister::build($month, $includeInterns);
        $app = require dirname(__DIR__) . '/Config/app.php';
        $this->view('hr/salary/register-pdf', [
            'register' => $data,
            'month' => $data['month'],
            'includeInterns' => $includeInterns,
            'letterhead' => \App\Services\Letterhead::config(),
            'company' => $app['company'] ?? 'Ocean Infotech',
        ], null);
    }

    public function employee(string $id): void
    {
        $this->guard('salary.view');
        $employee = (new Employee())->find((int) $id);
        if (!$employee) {
            $this->flash('error', 'Employee not found.');
            $this->redirect('salary');
        }
        $this->view('hr/salary/employee', [
            'title'    => 'Salary - ' . $employee['first_name'],
            'page'     => 'salary',
            'employee' => $employee,
            'salary'   => Salary::current((int) $id),
            'history'  => Salary::history((int) $id),
        ]);
    }

    public function save(string $id): void
    {
        if (!can('salary.edit') && !can('salary.create') && !can('*')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('salary');
        }
        $this->requireCsrf('salary/employee/' . $id);

        $monthly = round((float) $this->input('monthly_salary', 0), 2);
        if ($monthly <= 0) {
            $this->flash('error', 'Enter the final monthly salary.');
            $this->redirect('salary/employee/' . $id);
        }
        $effective = (string) $this->input('effective_from', '');
        $effective = preg_match('/^\d{4}-\d{2}-\d{2}$/', $effective) ? $effective : date('Y-m-d');
        $reason = trim((string) $this->input('reason', '')) ?: 'Salary update';
        $pt = round((float) $this->input('professional_tax', Salary::DEFAULT_PT), 2);
        if ($pt < 0) {
            $pt = 0;
        }

        Salary::set((int) $id, $monthly, $effective, $reason, $pt);
        $this->flash('success', 'Salary saved · PT ' . money($pt) . ' · history preserved.');
        $this->redirect('salary/employee/' . $id);
    }

    /** Quick PT update from Salary Register. */
    public function savePt(string $id): void
    {
        if (!can('salary.edit') && !can('salary.create') && !can('*')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('salary/register');
        }
        $back = 'salary/register';
        $month = (string) $this->input('month', date('Y-m'));
        if (preg_match('/^\d{4}-\d{2}$/', $month)) {
            $back .= '?month=' . $month;
            if ($this->input('include_interns')) {
                $back .= '&include_interns=1';
            }
        }
        $this->requireCsrf($back);
        if (!Salary::current((int) $id)) {
            $this->flash('error', 'Set decided salary first, then edit PT.');
            $this->redirect($back);
        }
        $pt = max(0, round((float) $this->input('professional_tax', Salary::DEFAULT_PT), 2));
        Salary::setPt((int) $id, $pt);
        $this->flash('success', 'Professional Tax updated to ' . money($pt) . '.');
        $this->redirect($back);
    }
}
