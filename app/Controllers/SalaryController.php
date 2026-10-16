<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Employee;
use App\Services\Salary;

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
                "SELECT es.*, e.employee_code, e.first_name, e.last_name, d.name AS department_name, g.name AS designation_name
                 FROM employee_salaries es
                 JOIN employees e ON e.id = es.employee_id
                 LEFT JOIN departments d ON d.id = e.department_id
                 LEFT JOIN designations g ON g.id = e.designation_id
                 ORDER BY e.first_name"
            ),
        ]);
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

        Salary::set((int) $id, $monthly, $effective, $reason);
        $this->flash('success', 'Salary saved. History preserved.');
        $this->redirect('salary/employee/' . $id);
    }
}
