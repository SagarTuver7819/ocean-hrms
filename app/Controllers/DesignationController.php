<?php

namespace App\Controllers;

use App\Core\Controller;

class DesignationController extends Controller
{
    public function index(): void
    {
        if (!can('employee.view') && !can('*')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }
        $db = $this->db();
        $items = $db->query(
            'SELECT des.*, d.name AS department_name
             FROM designations des
             LEFT JOIN departments d ON d.id = des.department_id
             ORDER BY des.name'
        )->fetchAll();
        $this->view('hr/masters/designations', [
            'title' => 'Designations',
            'page' => 'designations',
            'items' => $items,
            'departments' => $db->query('SELECT id, name FROM departments WHERE status = 1 ORDER BY name')->fetchAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireCsrf('designations');
        $db = $this->db();
        $db->prepare('INSERT INTO designations (name, department_id, status, created_at) VALUES (?, ?, 1, NOW())')
            ->execute([
                trim((string) $this->input('name')),
                $this->input('department_id') ?: null,
            ]);
        $this->flash('success', 'Designation added.');
        $this->redirect('designations');
    }

    public function update(string $id): void
    {
        $this->requireCsrf('designations');
        $this->db()->prepare(
            'UPDATE designations SET name=?, department_id=?, status=?, updated_at=NOW() WHERE id=?'
        )->execute([
            trim((string) $this->input('name')),
            $this->input('department_id') ?: null,
            (int) $this->input('status', 1),
            (int) $id,
        ]);
        $this->flash('success', 'Designation updated.');
        $this->redirect('designations');
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf('designations');
        $this->db()->prepare('DELETE FROM designations WHERE id = ?')->execute([(int) $id]);
        $this->flash('success', 'Designation deleted.');
        $this->redirect('designations');
    }
}
