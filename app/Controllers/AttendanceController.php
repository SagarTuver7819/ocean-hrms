<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Database;
use App\Core\View;
use App\Services\Attendance;
use App\Services\Leave;

class AttendanceController extends Controller
{
    private function authorizeHr(): void
    {
        if (!can('attendance.view') && !can('*')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }
    }

    public function index(): void
    {
        if (Auth::isEmployeePortal()) {
            $this->employeePage((int) Auth::employeeId());
            return;
        }
        $this->authorizeHr();

        $today = date('Y-m-d');
        $rows = Database::all(
            "SELECT e.id AS employee_id, e.employee_code, e.first_name, e.last_name, d.name AS department_name,
                    s.name AS shift_name, s.start_time, s.end_time,
                    a.id, a.punch_in, a.punch_out, a.working_minutes, a.break_minutes, a.late_minutes, a.overtime_minutes, a.status,
                    (SELECT COUNT(*) FROM attendance_breaks b WHERE b.attendance_id = a.id) AS break_count,
                    (SELECT b.break_out FROM attendance_breaks b WHERE b.attendance_id = a.id AND b.break_in IS NULL LIMIT 1) AS on_break_since,
                    (SELECT COUNT(*) FROM leave_requests l WHERE l.employee_id = e.id AND l.status = 'approved' AND ? BETWEEN l.from_date AND l.to_date) AS on_leave
             FROM employees e
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN shifts s ON s.id = e.shift_id
             LEFT JOIN attendance a ON a.employee_id = e.id AND a.attendance_date = ?
             WHERE e.status NOT IN ('inactive','resigned', 'draft')
             ORDER BY a.punch_in IS NULL, a.punch_in, e.employee_code",
            [$today, $today]
        );

        $summary = ['total' => count($rows), 'in' => 0, 'working' => 0, 'on_break' => 0, 'completed' => 0, 'late' => 0, 'not_in' => 0, 'leave' => 0];
        $now = time();
        foreach ($rows as &$r) {
            [$r['live_label'], $r['live_class']] = Attendance::liveStatus($r['id'] ? $r : null, (bool) $r['on_break_since'], (bool) $r['on_leave']);
            if ($r['punch_in'] && !$r['punch_out']) {
                $breakSecs = (int) $r['break_minutes'] * 60 + ($r['on_break_since'] ? $now - strtotime($r['on_break_since']) : 0);
                $r['working_minutes'] = intdiv(max(0, $now - strtotime($r['punch_in']) - $breakSecs), 60);
            }
            $summary['in'] += $r['punch_in'] ? 1 : 0;
            $summary['late'] += (int) $r['late_minutes'] > 0 ? 1 : 0;
            $summary[match ($r['live_class']) { 'working' => 'working', 'on_break' => 'on_break', 'present' => 'completed', 'leave' => 'leave', default => 'not_in' }]++;
        }
        unset($r);

        $this->view('hr/attendance/index', [
            'title' => 'Attendance',
            'page' => 'attendance',
            'summary' => $summary,
            'rows' => $rows,
            'today' => $today,
        ]);
    }

    private function employeePage(int $empId): void
    {
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $this->input('month')) ? $this->input('month') : date('Y-m');
        $rows = Database::all(
            "SELECT a.*, GROUP_CONCAT(CONCAT(DATE_FORMAT(b.break_out, '%h:%i %p'), ' – ', IFNULL(DATE_FORMAT(b.break_in, '%h:%i %p'), 'on break')) ORDER BY b.id SEPARATOR '|') AS break_list
             FROM attendance a LEFT JOIN attendance_breaks b ON b.attendance_id = a.id
             WHERE a.employee_id = ? AND DATE_FORMAT(a.attendance_date, '%Y-%m') = ?
             GROUP BY a.id ORDER BY a.attendance_date DESC",
            [$empId, $month]
        );
        $this->view('employee/attendance', [
            'title' => 'My Attendance',
            'page' => 'emp-attendance',
            'snap' => Attendance::snapshot($empId),
            'rows' => $rows,
            'month' => $month,
            'stats' => self::monthStats($empId, $month, $rows),
        ]);
    }

    /** Totals for one employee's month; absent = working days so far with no attendance or leave. */
    public static function monthStats(int $empId, string $month, array $rows): array
    {
        $first = $month . '-01';
        $last = min(date('Y-m-t', strtotime($first)), date('Y-m-d'));
        $joining = Database::value('SELECT joining_date FROM employees WHERE id = ?', [$empId]);
        $start = $joining && $joining > $first ? $joining : $first;
        $workingDays = $start <= $last ? Leave::workingDates($empId, $start, $last) : [];

        $present = $late = $half = $leave = $worked = $break = $ot = 0;
        $marked = [];
        foreach ($rows as $r) {
            $marked[$r['attendance_date']] = true;
            if ($r['punch_in']) {
                $present += match ($r['status']) { 'half_day' => 0.5, 'absent' => 0, default => 1 };
                $late += (int) $r['late_minutes'] > 0 ? 1 : 0;
                $half += $r['status'] === 'half_day' ? 1 : 0;
                $worked += (int) $r['working_minutes'];
                $break += (int) $r['break_minutes'];
                $ot += (int) $r['overtime_minutes'];
            } elseif ($r['status'] === 'leave') {
                $leave++;
            }
        }
        $completedDays = count(array_filter($rows, fn($r) => $r['punch_out']));
        return [
            'working_days' => count($workingDays),
            'present' => $present,
            'late' => $late,
            'half_day' => $half,
            'leave' => $leave,
            'absent' => count(array_filter($workingDays, fn($d) => !isset($marked[$d]) && $d < date('Y-m-d')))
                + count(array_filter($rows, fn($r) => $r['status'] === 'absent')),
            'worked' => $worked,
            'break' => $break,
            'overtime' => $ot,
            'avg' => $completedDays ? intdiv($worked, $completedDays) : 0,
        ];
    }

    public function punch(): void
    {
        $ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
        $back = Auth::isEmployeePortal() ? ($this->input('back') === 'attendance' ? 'employee/attendance' : 'employee/dashboard') : 'attendance';

        if (!CSRF::validate($this->input('_csrf'))) {
            $ajax ? $this->json(['ok' => false, 'message' => 'Session expired. Please refresh the page.'], 419) : $this->requireCsrf($back);
        }
        $empId = Auth::employeeId();
        if (!$empId) {
            $ajax ? $this->json(['ok' => false, 'message' => 'Only employees can punch attendance.'], 403) : $this->redirect('attendance');
        }

        $result = Attendance::punch($empId, (string) $this->input('type', ''));
        if ($ajax) {
            ob_start();
            View::partial('partials/punch-widget', ['snap' => Attendance::snapshot($empId), 'back' => $this->input('back')]);
            $this->json($result + ['html' => ob_get_clean()], $result['ok'] ? 200 : 422);
        }
        $this->flash($result['ok'] ? 'success' : 'error', $result['message']);
        $this->redirect($back);
    }

    public function report(): void
    {
        $this->authorizeHr();
        $from = $this->date('from', date('Y-m-d'));
        $to = max($from, $this->date('to', $from));
        $empId = (int) $this->input('employee_id');
        $deptId = (int) $this->input('department_id');

        $sql = "SELECT a.*, e.employee_code, e.first_name, e.last_name, d.name AS department_name, s.name AS shift_name,
                       GROUP_CONCAT(CONCAT(DATE_FORMAT(b.break_out, '%h:%i %p'), ' – ', IFNULL(DATE_FORMAT(b.break_in, '%h:%i %p'), 'on break')) ORDER BY b.id SEPARATOR '|') AS break_list
                FROM attendance a
                JOIN employees e ON e.id = a.employee_id
                LEFT JOIN departments d ON d.id = e.department_id
                LEFT JOIN shifts s ON s.id = a.shift_id
                LEFT JOIN attendance_breaks b ON b.attendance_id = a.id
                WHERE a.attendance_date BETWEEN ? AND ?";
        $params = [$from, $to];
        if ($empId) { $sql .= ' AND a.employee_id = ?'; $params[] = $empId; }
        if ($deptId) { $sql .= ' AND e.department_id = ?'; $params[] = $deptId; }
        $rows = Database::all($sql . ' GROUP BY a.id ORDER BY a.attendance_date DESC, a.punch_in', $params);

        $totals = ['days' => count($rows), 'worked' => 0, 'break' => 0, 'late' => 0, 'overtime' => 0];
        foreach ($rows as $r) {
            $totals['worked'] += (int) $r['working_minutes'];
            $totals['break'] += (int) $r['break_minutes'];
            $totals['late'] += (int) $r['late_minutes'] > 0 ? 1 : 0;
            $totals['overtime'] += (int) $r['overtime_minutes'];
        }

        $this->view('hr/attendance/report', [
            'title' => 'Daily Attendance Report',
            'page' => 'attendance-report',
            'rows' => $rows,
            'totals' => $totals,
            'from' => $from,
            'to' => $to,
            'employeeId' => $empId,
            'departmentId' => $deptId,
            'employees' => Database::all("SELECT id, employee_code, first_name, last_name FROM employees ORDER BY employee_code"),
            'departments' => Database::all('SELECT id, name FROM departments WHERE status = 1 ORDER BY name'),
        ]);
    }

    public function summary(): void
    {
        $this->authorizeHr();
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $this->input('month')) ? $this->input('month') : date('Y-m');
        $employees = Database::all(
            "SELECT e.id, e.employee_code, e.first_name, e.last_name, d.name AS department_name, s.name AS shift_name
             FROM employees e LEFT JOIN departments d ON d.id = e.department_id LEFT JOIN shifts s ON s.id = e.shift_id
             WHERE e.status NOT IN ('inactive','resigned','draft') ORDER BY e.employee_code"
        );
        foreach ($employees as &$emp) {
            $rows = Database::all("SELECT * FROM attendance WHERE employee_id = ? AND DATE_FORMAT(attendance_date, '%Y-%m') = ?", [$emp['id'], $month]);
            $emp['stats'] = self::monthStats((int) $emp['id'], $month, $rows);
        }
        unset($emp);

        $this->view('hr/attendance/summary', [
            'title' => 'Monthly Attendance Summary',
            'page' => 'attendance-summary',
            'employees' => $employees,
            'month' => $month,
        ]);
    }

    private function date(string $key, string $default): string
    {
        $v = (string) $this->input($key, '');
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : $default;
    }
}
