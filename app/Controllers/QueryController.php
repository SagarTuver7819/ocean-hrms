<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class QueryController extends Controller
{
    public function index(): void
    {
        $db = $this->db();
        if (Auth::isEmployeePortal()) {
            $stmt = $db->prepare('SELECT * FROM employee_queries WHERE employee_id = ? ORDER BY id DESC');
            $stmt->execute([Auth::employeeId()]);
            $this->view('employee/queries', [
                'title' => 'My Queries',
                'page' => 'emp-queries',
                'queries' => $stmt->fetchAll(),
            ]);
            return;
        }

        if (!can('query.view') && !can('*')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }

        $status = $this->input('status', 'open');
        $sql = "SELECT q.*, e.first_name, e.last_name, e.employee_code
                FROM employee_queries q
                JOIN employees e ON e.id = q.employee_id";
        $params = [];
        if ($status !== 'all') {
            if ($status === 'open') {
                $sql .= " WHERE q.status IN ('open','in_progress')";
            } else {
                $sql .= ' WHERE q.status = ?';
                $params[] = $status;
            }
        }
        $sql .= ' ORDER BY q.created_at DESC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $this->view('hr/queries/index', [
            'title' => 'Employee Queries',
            'page' => 'queries',
            'queries' => $stmt->fetchAll(),
            'status' => $status,
        ]);
    }

    public function create(): void
    {
        $this->view(Auth::isEmployeePortal() ? 'employee/query-create' : 'hr/queries/create', [
            'title' => 'Raise Query',
            'page' => Auth::isEmployeePortal() ? 'emp-queries' : 'queries',
        ]);
    }

    public function store(): void
    {
        $this->requireCsrf('queries/create');
        $empId = Auth::employeeId() ?: (int) $this->input('employee_id');
        if (!$empId) {
            $this->flash('error', 'Employee required.');
            $this->redirect('queries/create');
        }
        $db = $this->db();
        $db->prepare(
            'INSERT INTO employee_queries (employee_id, category, subject, description, status, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        )->execute([
            $empId,
            $this->input('category', 'other'),
            trim((string) $this->input('subject')),
            trim((string) $this->input('description')),
            'open',
        ]);
        $this->flash('success', 'Query raised successfully.');
        $this->redirect(Auth::isEmployeePortal() ? 'employee/queries' : 'queries');
    }

    public function show(string $id): void
    {
        $db = $this->db();
        $stmt = $db->prepare(
            "SELECT q.*, e.first_name, e.last_name, e.employee_code, e.user_id AS emp_user_id
             FROM employee_queries q JOIN employees e ON e.id = q.employee_id WHERE q.id = ?"
        );
        $stmt->execute([(int) $id]);
        $query = $stmt->fetch();
        if (!$query) {
            $this->flash('error', 'Query not found.');
            $this->redirect('queries');
        }

        // Employee can only view own
        if (Auth::isEmployeePortal() && (int) $query['employee_id'] !== (int) Auth::employeeId()) {
            $this->flash('error', 'Access denied.');
            $this->redirect('employee/queries');
        }

        $replies = $db->prepare(
            "SELECT r.*, u.name AS user_name FROM employee_query_replies r
             JOIN users u ON u.id = r.user_id WHERE r.query_id = ? ORDER BY r.id"
        );
        $replies->execute([(int) $id]);

        $this->view(Auth::isEmployeePortal() ? 'employee/query-show' : 'hr/queries/show', [
            'title' => 'Query #' . $id,
            'page' => Auth::isEmployeePortal() ? 'emp-queries' : 'queries',
            'query' => $query,
            'replies' => $replies->fetchAll(),
        ]);
    }

    public function reply(string $id): void
    {
        $this->requireCsrf('queries/' . $id);
        $db = $this->db();
        $stmt = $db->prepare('SELECT * FROM employee_queries WHERE id = ?');
        $stmt->execute([(int) $id]);
        $query = $stmt->fetch();
        if (!$query) {
            $this->flash('error', 'Query not found.');
            $this->redirect('queries');
        }
        if (Auth::isEmployeePortal() && (int) $query['employee_id'] !== (int) Auth::employeeId()) {
            $this->flash('error', 'Access denied.');
            $this->redirect('employee/queries');
        }

        $db->prepare(
            'INSERT INTO employee_query_replies (query_id, user_id, message, created_at) VALUES (?, ?, ?, NOW())'
        )->execute([(int) $id, Auth::id(), trim((string) $this->input('message'))]);

        $status = $this->input('status');
        if ($status && (can('query.reply') || can('*')) && !Auth::isEmployeePortal()) {
            $db->prepare('UPDATE employee_queries SET status=?, updated_at=NOW() WHERE id=?')
                ->execute([$status, (int) $id]);
        } else {
            $db->prepare("UPDATE employee_queries SET status='in_progress', updated_at=NOW() WHERE id=? AND status='open'")
                ->execute([(int) $id]);
        }

        $this->flash('success', 'Reply sent.');
        $this->redirect('queries/' . $id);
    }
}
