<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ShiftController extends Controller
{
    private function authorize(): void
    {
        if (!can('settings.manage') && !can('attendance.view') && !can('*')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }
    }

    public function index(): void
    {
        $this->authorize();
        $this->view('hr/masters/shifts', [
            'title' => 'Shift Master',
            'page' => 'shifts',
            'shifts' => Database::all(
                'SELECT s.*, (SELECT COUNT(*) FROM employees e WHERE e.shift_id = s.id) AS employees FROM shifts s ORDER BY s.start_time, s.name'
            ),
            'employees' => Database::all(
                "SELECT e.id, e.employee_code, e.first_name, e.last_name, e.shift_id, d.name AS department_name
                 FROM employees e LEFT JOIN departments d ON d.id = e.department_id
                 WHERE e.status NOT IN ('inactive','resigned') ORDER BY e.employee_code"
            ),
        ]);
    }

    public function store(): void
    {
        $this->authorize();
        $this->requireCsrf('shifts');
        $data = $this->collect();
        Database::run(
            'INSERT INTO shifts (name, code, start_time, end_time, late_after_minutes, break_minutes, half_day_minutes, full_day_minutes, week_offs, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
            array_values($data)
        );
        activity_log('shift', 'created', (int) $this->db()->lastInsertId(), null, $data);
        $this->flash('success', 'Shift "' . $data['name'] . '" added.');
        $this->redirect('shifts');
    }

    public function update(string $id): void
    {
        $this->authorize();
        $this->requireCsrf('shifts');
        $data = $this->collect();
        Database::run(
            'UPDATE shifts SET name = ?, code = ?, start_time = ?, end_time = ?, late_after_minutes = ?, break_minutes = ?, half_day_minutes = ?, full_day_minutes = ?, week_offs = ?, status = ?, updated_at = NOW() WHERE id = ?',
            [...array_values($data), (int) $id]
        );
        activity_log('shift', 'updated', (int) $id, null, $data);
        $this->flash('success', 'Shift "' . $data['name'] . '" updated.');
        $this->redirect('shifts');
    }

    public function destroy(string $id): void
    {
        $this->authorize();
        $this->requireCsrf('shifts');
        if ((int) Database::value('SELECT COUNT(*) FROM employees WHERE shift_id = ?', [(int) $id]) > 0) {
            $this->flash('error', 'Move the employees on this shift to another shift before deleting it.');
            $this->redirect('shifts');
        }
        Database::run('DELETE FROM shifts WHERE id = ?', [(int) $id]);
        activity_log('shift', 'deleted', (int) $id);
        $this->flash('success', 'Shift deleted.');
        $this->redirect('shifts');
    }

    /** Bulk assignment from the employee table on the shift page. */
    public function assign(): void
    {
        $this->authorize();
        $this->requireCsrf('shifts');
        $changed = 0;
        $stmt = $this->db()->prepare('UPDATE employees SET shift_id = ?, updated_at = NOW() WHERE id = ? AND NOT (shift_id <=> ?)');
        foreach ((array) $this->input('shift', []) as $empId => $shiftId) {
            $shiftId = $shiftId !== '' ? (int) $shiftId : null;
            $stmt->execute([$shiftId, (int) $empId, $shiftId]);
            $changed += $stmt->rowCount();
        }
        activity_log('shift', 'assigned', null, null, ['changed' => $changed]);
        $this->flash('success', $changed ? "Shift updated for {$changed} employee(s)." : 'No shift changes to save.');
        $this->redirect('shifts');
    }

    private function collect(): array
    {
        $hours = fn(string $key, float $default) => (int) round(max(0, (float) $this->input($key, $default)) * 60);
        $offs = array_map('intval', (array) $this->input('week_offs', []));
        return [
            'name' => trim((string) $this->input('name')) ?: 'Shift',
            'code' => strtoupper(trim((string) $this->input('code'))) ?: null,
            'start_time' => substr((string) $this->input('start_time', '09:30'), 0, 5) . ':00',
            'end_time' => substr((string) $this->input('end_time', '18:30'), 0, 5) . ':00',
            'late_after_minutes' => max(0, (int) $this->input('late_after_minutes', 15)),
            'break_minutes' => max(0, (int) $this->input('break_minutes', 60)),
            'half_day_minutes' => $hours('half_day_hours', 4),
            'full_day_minutes' => $hours('full_day_hours', 8),
            'week_offs' => implode(',', array_unique(array_filter($offs, fn($d) => $d >= 0 && $d <= 6))),
            'status' => (int) $this->input('status', 1) ? 1 : 0,
        ];
    }
}
