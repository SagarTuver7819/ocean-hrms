<?php

namespace App\Controllers;

use App\Core\Controller;

class HolidayController extends Controller
{
    public function index(): void
    {
        $db = $this->db();
        $this->view('hr/holidays', [
            'title' => 'Holiday Master',
            'page' => 'holidays',
            'items' => $db->query('SELECT * FROM holidays ORDER BY holiday_date')->fetchAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireCsrf('holidays');
        $this->db()->prepare(
            'INSERT INTO holidays (name, holiday_date, holiday_type, description, location, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        )->execute([
            trim((string) $this->input('name')),
            $this->input('holiday_date'),
            $this->input('holiday_type', 'public'),
            $this->input('description'),
            $this->input('location'),
        ]);
        activity_log('holiday', 'created');
        $this->flash('success', 'Holiday added successfully.');
        $this->redirect('holidays');
    }

    public function update(string $id): void
    {
        $this->requireCsrf('holidays');
        $this->db()->prepare(
            'UPDATE holidays SET name=?, holiday_date=?, holiday_type=?, description=?, location=? WHERE id=?'
        )->execute([
            trim((string) $this->input('name')),
            $this->input('holiday_date'),
            $this->input('holiday_type', 'public'),
            $this->input('description'),
            $this->input('location'),
            (int) $id,
        ]);
        activity_log('holiday', 'updated', (int) $id);
        $this->flash('success', 'Holiday updated successfully.');
        $this->redirect('holidays');
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf('holidays');
        $this->db()->prepare('DELETE FROM holidays WHERE id = ?')->execute([(int) $id]);
        activity_log('holiday', 'deleted', (int) $id);
        $this->flash('success', 'Holiday deleted.');
        $this->redirect('holidays');
    }
}
