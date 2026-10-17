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
            'items' => $db->query(
                'SELECT * FROM holidays ORDER BY holiday_date, COALESCE(to_date, holiday_date)'
            )->fetchAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireCsrf('holidays');
        [$from, $to] = $this->rangeDates();
        if (!$from) {
            $this->flash('error', 'From Date is required.');
            $this->redirect('holidays');
        }
        $this->db()->prepare(
            'INSERT INTO holidays (name, holiday_date, to_date, holiday_type, description, location, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        )->execute([
            trim((string) $this->input('name')),
            $from,
            $to,
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
        [$from, $to] = $this->rangeDates();
        if (!$from) {
            $this->flash('error', 'From Date is required.');
            $this->redirect('holidays');
        }
        $this->db()->prepare(
            'UPDATE holidays SET name=?, holiday_date=?, to_date=?, holiday_type=?, description=?, location=? WHERE id=?'
        )->execute([
            trim((string) $this->input('name')),
            $from,
            $to,
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

    /** @return array{0:?string,1:?string} [from, to] */
    private function rangeDates(): array
    {
        $from = trim((string) ($this->input('holiday_date') ?: $this->input('from_date') ?: ''));
        $to = trim((string) ($this->input('to_date') ?: ''));
        if ($from === '') {
            return [null, null];
        }
        if ($to === '') {
            $to = $from;
        }
        if (strtotime($to) < strtotime($from)) {
            $to = $from;
        }
        return [$from, $to];
    }
}
