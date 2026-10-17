<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Services\Leave;

class LeaveController extends Controller
{
    public function index(): void
    {
        if (Auth::isEmployeePortal()) {
            $empId = (int) Auth::employeeId();
            $this->view('employee/leave', [
                'title' => 'My Leave',
                'page' => 'emp-leave',
                'balances' => Leave::balances($empId),
                'requests' => Database::all(
                    'SELECT lr.*, lt.name AS leave_type, lt.code FROM leave_requests lr
                     JOIN leave_types lt ON lt.id = lr.leave_type_id
                     WHERE lr.employee_id = ? ORDER BY lr.id DESC',
                    [$empId]
                ),
            ]);
            return;
        }

        if (!can('leave.view') && !can('*')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }

        $status = in_array($this->input('status'), ['pending', 'approved', 'rejected', 'cancelled', 'all'], true) ? $this->input('status') : 'pending';
        $sql = "SELECT lr.*, e.first_name, e.last_name, e.employee_code, lt.name AS leave_type, lt.code, lt.is_paid,
                       lb.allocated, lb.used, lb.pending AS pending_days
                FROM leave_requests lr
                JOIN employees e ON e.id = lr.employee_id
                JOIN leave_types lt ON lt.id = lr.leave_type_id
                LEFT JOIN leave_balances lb ON lb.employee_id = lr.employee_id AND lb.leave_type_id = lr.leave_type_id AND lb.year = YEAR(lr.from_date)";
        $params = [];
        if ($status !== 'all') {
            $sql .= ' WHERE lr.status = ?';
            $params[] = $status;
        }
        $counts = array_column(Database::all('SELECT status, COUNT(*) c FROM leave_requests GROUP BY status'), 'c', 'status');

        $this->view('hr/leave/index', [
            'title' => 'Leave Management',
            'page' => 'leave',
            'requests' => Database::all($sql . ' ORDER BY lr.created_at DESC', $params),
            'status' => $status,
            'counts' => $counts,
        ]);
    }

    /** AJAX preview of how many leave days a date range costs. */
    public function days(): void
    {
        $empId = (int) (Auth::employeeId() ?: $this->input('employee_id'));
        $from = (string) $this->input('from');
        $to = (string) $this->input('to');
        if (!$empId || !strtotime($from) || !strtotime($to) || $to < $from) {
            $this->json(['days' => 0, 'message' => 'Select a valid date range']);
        }
        $half = $this->wantsHalfDay($from, $to);
        $days = Leave::countDays($empId, $from, $to, $half);
        $span = (int) ((strtotime($to) - strtotime($from)) / 86400) + 1;
        $skipped = $span - count(Leave::workingDates($empId, $from, $to));
        $label = $half
            ? 'Half Day · 0.5 leave day'
            : ($days . ' Full Day leave' . ($days == 1 ? '' : 's'));
        $this->json([
            'days' => $days,
            'message' => '<i class="fa-solid fa-calendar-day me-1"></i>' . $label
                . ($skipped ? ' <small class="fw-semibold">(' . $skipped . ' week-off / holiday skipped)</small>' : ''),
        ]);
    }

    public function store(): void
    {
        $back = Auth::isEmployeePortal() ? 'employee/leave' : 'leave';
        $this->requireCsrf($back);

        $empId = Auth::employeeId() ?: (can('leave.create') ? (int) $this->input('employee_id') : 0);
        if (!$empId) {
            $this->flash('error', 'Employee context missing.');
            $this->redirect($back);
        }

        $from = (string) $this->input('from_date');
        $to = (string) $this->input('to_date', $from);
        $typeId = (int) $this->input('leave_type_id');
        $reason = trim((string) $this->input('reason'));
        $type = Database::first('SELECT * FROM leave_types WHERE id = ? AND status = 1', [$typeId]);
        if (!$type || !strtotime($from) || !strtotime($to) || $to < $from || $reason === '') {
            $this->flash('error', 'Please choose a leave type, valid dates and a reason.');
            $this->redirect($back);
        }

        $half = $this->wantsHalfDay($from, $to);
        if ($half && $from !== $to) {
            $this->flash('error', 'Half Day leave requires the same From and To date.');
            $this->redirect($back);
        }
        $days = Leave::countDays($empId, $from, $to, $half);
        if ($days <= 0) {
            $this->flash('error', 'The selected dates are all weekly offs or holidays — no leave needed.');
            $this->redirect($back);
        }
        $overlap = Database::value(
            "SELECT COUNT(*) FROM leave_requests WHERE employee_id = ? AND status IN ('pending','approved') AND from_date <= ? AND to_date >= ?",
            [$empId, $to, $from]
        );
        if ($overlap) {
            $this->flash('error', 'You already have a leave request for these dates.');
            $this->redirect($back);
        }

        $balance = null;
        foreach (Leave::balances($empId, (int) date('Y', strtotime($from))) as $b) {
            if ((int) $b['leave_type_id'] === $typeId) $balance = $b;
        }
        if ($balance && $balance['tracked'] && $days > $balance['available']) {
            $this->flash('error', 'Only ' . (float) $balance['available'] . ' ' . $type['name'] . ' available (1 credited every month). Apply the remaining days as Unpaid Leave.');
            $this->redirect($back);
        }

        Database::run(
            "INSERT INTO leave_requests (employee_id, leave_type_id, from_date, to_date, days, reason, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())",
            [$empId, $typeId, $from, $to, $days, $reason]
        );
        $requestId = (int) $this->db()->lastInsertId();
        if ($balance && $balance['tracked']) {
            Database::run(
                'UPDATE leave_balances SET pending = pending + ? WHERE employee_id = ? AND leave_type_id = ? AND year = ?',
                [$days, $empId, $typeId, date('Y', strtotime($from))]
            );
        }

        $name = (string) Database::value("SELECT CONCAT(first_name, ' ', last_name) FROM employees WHERE id = ?", [$empId]);
        $dates = $from === $to ? date('d M', strtotime($from)) : date('d M', strtotime($from)) . ' – ' . date('d M', strtotime($to));
        $dur = $half ? 'Half Day (0.5)' : ($days . ' Full Day');
        notify_hr('Leave request · ' . $name, $name . ' applied for ' . $dur . ' · ' . $type['name'] . ' (' . $dates . ').', 'leave?status=pending');
        activity_log('leave', 'applied', $requestId);
        $this->flash('success', 'Leave request submitted · ' . $dur . '. HR has been notified.');
        $this->redirect($back);
    }

    private function wantsHalfDay(string $from, string $to): bool
    {
        $duration = strtolower((string) $this->input('leave_duration', ''));
        if ($duration === 'half') {
            return $from === $to;
        }
        if ($duration === 'full') {
            return false;
        }
        // Legacy checkbox support
        return $from === $to && (bool) $this->input('half_day');
    }

    public function cancel(string $id): void
    {
        $this->requireCsrf('employee/leave');
        $req = Database::first('SELECT * FROM leave_requests WHERE id = ? AND employee_id = ?', [(int) $id, (int) Auth::employeeId()]);
        if (!$req || $req['status'] !== 'pending') {
            $this->flash('error', 'Only your pending requests can be cancelled.');
            $this->redirect('employee/leave');
        }
        Database::run("UPDATE leave_requests SET status = 'cancelled', updated_at = NOW() WHERE id = ?", [$req['id']]);
        $this->releasePending($req);
        activity_log('leave', 'cancelled', (int) $id);
        $this->flash('success', 'Leave request cancelled.');
        $this->redirect('employee/leave');
    }

    public function approve(string $id): void
    {
        $this->decide((int) $id, 'approved');
    }

    public function reject(string $id): void
    {
        $this->decide((int) $id, 'rejected');
    }

    private function decide(int $id, string $status): void
    {
        if (!can('leave.approve') && !can('leave.reject') && !can('*')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('leave');
        }
        $this->requireCsrf('leave');

        $req = Database::first('SELECT lr.*, lt.name AS type_name FROM leave_requests lr JOIN leave_types lt ON lt.id = lr.leave_type_id WHERE lr.id = ?', [$id]);
        if (!$req || $req['status'] !== 'pending') {
            $this->flash('error', 'Leave request not available.');
            $this->redirect('leave');
        }

        Database::run(
            'UPDATE leave_requests SET status = ?, hr_status = ?, hr_remark = ?, approved_by = ?, updated_at = NOW() WHERE id = ?',
            [$status, $status, $this->input('remark'), Auth::id(), $id]
        );
        $year = date('Y', strtotime($req['from_date']));
        if ($status === 'approved') {
            Database::run(
                'UPDATE leave_balances SET used = used + ?, pending = GREATEST(pending - ?, 0) WHERE employee_id = ? AND leave_type_id = ? AND year = ?',
                [$req['days'], $req['days'], $req['employee_id'], $req['leave_type_id'], $year]
            );
            $mark = $this->db()->prepare(
                "INSERT INTO attendance (employee_id, attendance_date, status, remarks, created_at) VALUES (?, ?, 'leave', ?, NOW())
                 ON DUPLICATE KEY UPDATE status = IF(punch_in IS NULL, 'leave', status), remarks = IF(punch_in IS NULL, VALUES(remarks), remarks)"
            );
            foreach (Leave::workingDates((int) $req['employee_id'], $req['from_date'], $req['to_date']) as $date) {
                $mark->execute([$req['employee_id'], $date, $req['type_name']]);
            }
        } else {
            $this->releasePending($req);
        }

        $userId = Database::value('SELECT user_id FROM employees WHERE id = ?', [$req['employee_id']]);
        if ($userId) {
            $dates = format_date($req['from_date']) . ($req['to_date'] !== $req['from_date'] ? ' – ' . format_date($req['to_date']) : '');
            notify((int) $userId, 'Leave ' . ucfirst($status), 'Your ' . $req['type_name'] . ' for ' . $dates . ' has been ' . $status . '.' . ($this->input('remark') ? ' Remark: ' . $this->input('remark') : ''), 'employee/leave');
        }

        activity_log('leave', $status, $id);
        $this->flash('success', 'Leave ' . $status . '.');
        $this->redirect('leave');
    }

    private function releasePending(array $req): void
    {
        Database::run(
            'UPDATE leave_balances SET pending = GREATEST(pending - ?, 0) WHERE employee_id = ? AND leave_type_id = ? AND year = ?',
            [$req['days'], $req['employee_id'], $req['leave_type_id'], date('Y', strtotime($req['from_date']))]
        );
    }

    public function types(): void
    {
        if (!can('*') && !can('settings.manage')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }
        $this->view('hr/leave/types', [
            'title' => 'Leave Types',
            'page' => 'leave-types',
            'types' => Database::all('SELECT * FROM leave_types ORDER BY status DESC, monthly_accrual DESC, name'),
        ]);
    }

    public function storeType(): void
    {
        $this->requireCsrf('leave/types');
        Database::run(
            'INSERT INTO leave_types (name, code, days_per_year, monthly_accrual, is_paid, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())',
            $this->typeData()
        );
        $this->flash('success', 'Leave type added.');
        $this->redirect('leave/types');
    }

    public function updateType(string $id): void
    {
        $this->requireCsrf('leave/types');
        Database::run(
            'UPDATE leave_types SET name = ?, code = ?, days_per_year = ?, monthly_accrual = ?, is_paid = ?, status = ? WHERE id = ?',
            [...$this->typeData(), (int) $id]
        );
        activity_log('leave_type', 'updated', (int) $id);
        $this->flash('success', 'Leave type updated.');
        $this->redirect('leave/types');
    }

    private function typeData(): array
    {
        $monthly = max(0, (float) $this->input('monthly_accrual', 0));
        return [
            trim((string) $this->input('name')),
            strtoupper(trim((string) $this->input('code'))),
            $monthly > 0 ? $monthly * 12 : max(0, (float) $this->input('days_per_year', 0)),
            $monthly,
            $this->input('is_paid') ? 1 : 0,
            (int) $this->input('status', 1) ? 1 : 0,
        ];
    }
}
