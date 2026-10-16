<?php

namespace App\Controllers;

use App\Core\Controller;

class DepartmentController extends Controller
{
    public function index(): void
    {
        if (!can('employee.view') && !can('*')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }
        $db = $this->db();
        $this->view('hr/masters/departments', [
            'title' => 'Departments',
            'page' => 'departments',
            'items' => $db->query('SELECT * FROM departments ORDER BY name')->fetchAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireCsrf('departments');
        $db = $this->db();
        $db->prepare('INSERT INTO departments (name, code, status, created_at) VALUES (?, ?, 1, NOW())')
            ->execute([trim((string) $this->input('name')), trim((string) $this->input('code'))]);
        activity_log('department', 'created');
        $this->flash('success', 'Department added successfully.');
        $this->redirect('departments');
    }

    public function update(string $id): void
    {
        $this->requireCsrf('departments');
        $db = $this->db();
        $db->prepare('UPDATE departments SET name=?, code=?, status=?, updated_at=NOW() WHERE id=?')
            ->execute([
                trim((string) $this->input('name')),
                trim((string) $this->input('code')),
                (int) $this->input('status', 1),
                (int) $id,
            ]);
        activity_log('department', 'updated', (int) $id);
        $this->flash('success', 'Department updated successfully.');
        $this->redirect('departments');
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf('departments');
        $this->db()->prepare('DELETE FROM departments WHERE id = ?')->execute([(int) $id]);
        activity_log('department', 'deleted', (int) $id);
        $this->flash('success', 'Department deleted successfully.');
        $this->redirect('departments');
    }
}
