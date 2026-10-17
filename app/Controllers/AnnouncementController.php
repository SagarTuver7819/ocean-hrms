<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class AnnouncementController extends Controller
{
    public function index(): void
    {
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

    public function store(): void
    {
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
        activity_log('announcement', 'created');

        if ($status === 1) {
            $preview = mb_strimwidth(preg_replace('/\s+/', ' ', strip_tags($description)) ?? '', 0, 120, '…');
            notify_employees(
                'New Announcement',
                $title . ($preview !== '' ? ' — ' . $preview : ''),
                '/announcements',
                $deptId
            );
        }

        $this->flash('success', 'Announcement published successfully.' . ($status === 1 ? ' Employees notified.' : ''));
        $this->redirect('announcements');
    }

    public function update(string $id): void
    {
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
        $this->requireCsrf('announcements');
        $this->db()->prepare('DELETE FROM announcements WHERE id = ?')->execute([(int) $id]);
        activity_log('announcement', 'deleted', (int) $id);
        $this->flash('success', 'Announcement deleted.');
        $this->redirect('announcements');
    }
}
