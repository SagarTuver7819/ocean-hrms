<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Services\Letterhead;

class AnnouncementController extends Controller
{
    public function index(): void
    {
        if (Auth::isEmployeePortal()) {
            $this->employeeIndex();
            return;
        }

        $db = $this->db();
        $this->view('hr/announcements', [
            'title' => 'Announcements',
            'page' => 'announcements',
            'items' => $db->query(
                'SELECT a.*, d.name AS department_name
                 FROM announcements a
                 LEFT JOIN departments d ON d.id = a.target_department_id
                 ORDER BY a.id DESC'
            )->fetchAll(),
            'departments' => $db->query('SELECT id, name FROM departments WHERE status = 1 ORDER BY name')->fetchAll(),
        ]);
    }

    /** Employee portal: list of active announcements */
    private function employeeIndex(): void
    {
        $items = $this->visibleForEmployee();
        $this->view('employee/announcements', [
            'title' => 'Announcements',
            'page' => 'announcements',
            'items' => $items,
        ]);
    }

    /** Full A4 view on company letterhead */
    public function show(string $id): void
    {
        $row = $this->findVisible((int) $id);
        if (!$row) {
            $this->flash('error', 'Announcement not found or no longer available.');
            $this->redirect('announcements');
        }

        $back = Auth::isEmployeePortal() ? url('announcements') : url('announcements');
        $this->view('announcements/document', [
            'item' => $row,
            'letterhead' => Letterhead::config(),
            'backUrl' => $back,
            'isEmp' => Auth::isEmployeePortal(),
        ], null);
    }

    public function store(): void
    {
        $this->guardHr();
        $this->requireCsrf('announcements');
        $title = trim((string) $this->input('title'));
        $description = trim((string) $this->input('description'));
        $deptId = $this->input('target_department_id') ? (int) $this->input('target_department_id') : null;
        $status = (int) $this->input('status', 1);

        $this->db()->prepare(
            'INSERT INTO announcements (title, description, publish_date, expiry_date, target_department_id, status, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
        )->execute([
            $title,
            $description,
            $this->input('publish_date') ?: date('Y-m-d'),
            $this->input('expiry_date') ?: null,
            $deptId,
            $status,
            Auth::id(),
        ]);
        $newId = (int) $this->db()->lastInsertId();
        activity_log('announcement', 'created', $newId ?: null);

        if ($status === 1) {
            $preview = mb_strimwidth((string) preg_replace('/\s+/', ' ', strip_tags($description)), 0, 120, '…');
            notify_employees(
                'New Announcement',
                $title . ($preview !== '' ? ' — ' . $preview : ''),
                $newId > 0 ? '/announcements/' . $newId : '/announcements',
                $deptId
            );
        }

        $this->flash('success', 'Announcement published successfully.' . ($status === 1 ? ' Employees notified.' : ''));
        $this->redirect('announcements');
    }

    public function update(string $id): void
    {
        $this->guardHr();
        $this->requireCsrf('announcements');
        $this->db()->prepare(
            'UPDATE announcements SET title=?, description=?, publish_date=?, expiry_date=?, target_department_id=?, status=? WHERE id=?'
        )->execute([
            trim((string) $this->input('title')),
            trim((string) $this->input('description')),
            $this->input('publish_date') ?: date('Y-m-d'),
            $this->input('expiry_date') ?: null,
            $this->input('target_department_id') ?: null,
            (int) $this->input('status', 1),
            (int) $id,
        ]);
        activity_log('announcement', 'updated', (int) $id);
        $this->flash('success', 'Announcement updated successfully.');
        $this->redirect('announcements');
    }

    public function destroy(string $id): void
    {
        $this->guardHr();
        $this->requireCsrf('announcements');
        $this->db()->prepare('DELETE FROM announcements WHERE id = ?')->execute([(int) $id]);
        activity_log('announcement', 'deleted', (int) $id);
        $this->flash('success', 'Announcement deleted.');
        $this->redirect('announcements');
    }

    private function guardHr(): void
    {
        if (Auth::isEmployeePortal()) {
            $this->flash('error', 'Access denied.');
            $this->redirect('announcements');
        }
    }

    /** @return list<array<string,mixed>> */
    private function visibleForEmployee(): array
    {
        $deptId = $this->employeeDepartmentId();
        $sql = 'SELECT a.*, d.name AS department_name
                FROM announcements a
                LEFT JOIN departments d ON d.id = a.target_department_id
                WHERE a.status = 1
                  AND (a.expiry_date IS NULL OR a.expiry_date >= CURDATE())
                  AND (a.publish_date IS NULL OR a.publish_date <= CURDATE())
                  AND (a.target_department_id IS NULL';
        $params = [];
        if ($deptId) {
            $sql .= ' OR a.target_department_id = ?';
            $params[] = $deptId;
        }
        $sql .= ') ORDER BY a.publish_date DESC, a.id DESC';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return array<string,mixed>|null */
    private function findVisible(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        if (Auth::isEmployeePortal()) {
            foreach ($this->visibleForEmployee() as $row) {
                if ((int) $row['id'] === $id) {
                    return $row;
                }
            }
            return null;
        }

        $stmt = $this->db()->prepare(
            'SELECT a.*, d.name AS department_name
             FROM announcements a
             LEFT JOIN departments d ON d.id = a.target_department_id
             WHERE a.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function employeeDepartmentId(): ?int
    {
        $empId = Auth::employeeId();
        if (!$empId) {
            return null;
        }
        $stmt = $this->db()->prepare('SELECT department_id FROM employees WHERE id = ?');
        $stmt->execute([(int) $empId]);
        $dept = $stmt->fetchColumn();
        return $dept ? (int) $dept : null;
    }
}
