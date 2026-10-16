<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        if (Auth::isEmployeePortal()) {
            $this->redirect('employee/dashboard');
        }

        if (!can('dashboard.view') && !can('*')) {
            $this->flash('error', 'You do not have permission to view the HR dashboard.');
            $this->redirect('employee/dashboard');
        }

        $db = $this->db();
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');

        $stats = [
            'total_employees'  => (int) $db->query("SELECT COUNT(*) FROM employees")->fetchColumn(),
            'active_employees' => (int) $db->query("SELECT COUNT(*) FROM employees WHERE status IN ('active','probation')")->fetchColumn(),
            'new_joiners'      => (int) $db->query("SELECT COUNT(*) FROM employees WHERE joining_date >= '{$monthStart}'")->fetchColumn(),
            'exits'            => (int) $db->query("SELECT COUNT(*) FROM employees WHERE status IN ('inactive','resigned')")->fetchColumn(),
            'present_today'    => 0,
            'absent_today'     => 0,
            'on_leave'         => 0,
            'late_today'       => 0,
            'pending_leave'    => (int) $db->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'pending'")->fetchColumn(),
            'pending_queries'  => (int) $db->query("SELECT COUNT(*) FROM employee_queries WHERE status IN ('open','in_progress')")->fetchColumn(),
        ];

        $stmt = $db->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date = ? AND status IN ('present','late','wfh','half_day')");
        $stmt->execute([$today]);
        $stats['present_today'] = (int) $stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date = ? AND status = 'late'");
        $stmt->execute([$today]);
        $stats['late_today'] = (int) $stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM leave_requests WHERE status = 'approved' AND ? BETWEEN from_date AND to_date");
        $stmt->execute([$today]);
        $stats['on_leave'] = (int) $stmt->fetchColumn();

        $stats['absent_today'] = max(0, $stats['active_employees'] - $stats['present_today'] - $stats['on_leave']);
        $presentPct = $stats['active_employees'] > 0
            ? round(($stats['present_today'] / $stats['active_employees']) * 100)
            : 0;

        $headcount = $db->prepare(
            "SELECT COUNT(*) FROM employees e
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN designations g ON g.id = e.designation_id
             WHERE e.status NOT IN ('inactive','resigned') AND (g.name = ? OR d.name = ?)"
        );
        $deptChart = [];
        foreach (CareerController::positions() as $position) {
            $headcount->execute([$position, $position]);
            $deptChart[] = ['name' => $position, 'total' => (int) $headcount->fetchColumn()];
        }

        $recentJoiners = $db->query(
            "SELECT e.id, e.employee_code, e.first_name, e.last_name, e.joining_date, e.status, d.name AS department_name
             FROM employees e
             LEFT JOIN departments d ON d.id = e.department_id
             WHERE e.joining_date >= '{$monthStart}'
             ORDER BY e.joining_date DESC LIMIT 8"
        )->fetchAll();

        $pendingLeaves = $db->query(
            "SELECT lr.*, e.first_name, e.last_name, e.employee_code, lt.name AS leave_type
             FROM leave_requests lr
             JOIN employees e ON e.id = lr.employee_id
             JOIN leave_types lt ON lt.id = lr.leave_type_id
             WHERE lr.status = 'pending'
             ORDER BY lr.created_at DESC LIMIT 5"
        )->fetchAll();

        $openQueries = $db->query(
            "SELECT q.*, e.first_name, e.last_name, e.employee_code
             FROM employee_queries q
             JOIN employees e ON e.id = q.employee_id
             WHERE q.status IN ('open','in_progress')
             ORDER BY q.id DESC LIMIT 5"
        )->fetchAll();

        $announcements = $db->query(
            "SELECT * FROM announcements WHERE status = 1
             AND (expiry_date IS NULL OR expiry_date >= CURDATE())
             ORDER BY id DESC LIMIT 5"
        )->fetchAll();

        $holidays = $db->query(
            "SELECT * FROM holidays WHERE holiday_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 45 DAY)
             ORDER BY holiday_date LIMIT 6"
        )->fetchAll();

        $birthdays = $db->query(
            "SELECT id, employee_code, first_name, last_name, date_of_birth, department_id,
                    DATE_FORMAT(date_of_birth, '%d-%m') AS md
             FROM employees
             WHERE date_of_birth IS NOT NULL AND status IN ('active','probation')
             ORDER BY DATE_FORMAT(date_of_birth, '%m%d')
             LIMIT 20"
        )->fetchAll();

        // Filter next 45 days birthdays in PHP for simplicity
        $upcomingBday = [];
        foreach ($birthdays as $b) {
            if (empty($b['date_of_birth'])) continue;
            $next = date('Y') . '-' . date('m-d', strtotime($b['date_of_birth']));
            if ($next < $today) {
                $next = (date('Y') + 1) . '-' . date('m-d', strtotime($b['date_of_birth']));
            }
            $days = (int) ((strtotime($next) - strtotime($today)) / 86400);
            if ($days <= 45) {
                $b['next_date'] = $next;
                $b['in_days'] = $days;
                $upcomingBday[] = $b;
            }
        }
        usort($upcomingBday, fn($a, $b) => $a['in_days'] <=> $b['in_days']);
        $upcomingBday = array_slice($upcomingBday, 0, 8);

        $anniversaries = $db->query(
            "SELECT e.id, e.employee_code, e.first_name, e.last_name, e.joining_date, d.name AS department_name
             FROM employees e
             LEFT JOIN departments d ON d.id = e.department_id
             WHERE e.joining_date IS NOT NULL AND e.status IN ('active','probation')
             ORDER BY DATE_FORMAT(e.joining_date, '%m%d') LIMIT 30"
        )->fetchAll();
        $upcomingAnniv = [];
        foreach ($anniversaries as $a) {
            $next = date('Y') . '-' . date('m-d', strtotime($a['joining_date']));
            if ($next < $today) {
                $next = (date('Y') + 1) . '-' . date('m-d', strtotime($a['joining_date']));
            }
            $days = (int) ((strtotime($next) - strtotime($today)) / 86400);
            if ($days <= 30) {
                $years = (int) date('Y') - (int) date('Y', strtotime($a['joining_date']));
                if ($next < date('Y') . '-01-01' || date('m-d', strtotime($a['joining_date'])) > date('m-d')) {
                    // anniversary year calc
                }
                $years = max(1, (int) date('Y', strtotime($next)) - (int) date('Y', strtotime($a['joining_date'])));
                $a['next_date'] = $next;
                $a['in_days'] = $days;
                $a['years'] = $years;
                $upcomingAnniv[] = $a;
            }
        }
        usort($upcomingAnniv, fn($a, $b) => $a['in_days'] <=> $b['in_days']);
        $upcomingAnniv = array_slice($upcomingAnniv, 0, 5);

        $user = Auth::user();

        $designation = '';
        if (!empty($user['employee_id'])) {
            $st = $db->prepare(
                'SELECT d.name AS department, g.name AS designation FROM employees e
                 LEFT JOIN departments d ON d.id = e.department_id
                 LEFT JOIN designations g ON g.id = e.designation_id
                 WHERE e.id = ?'
            );
            $st->execute([$user['employee_id']]);
            $row = $st->fetch() ?: [];
            $designation = implode(' · ', array_filter([$row['department'] ?? '', $row['designation'] ?? '']));
        }
        if ($designation === '') {
            $roleSlug = $_SESSION['roles'][0] ?? null;
            if ($roleSlug) {
                $st = $db->prepare('SELECT name FROM roles WHERE slug = ? LIMIT 1');
                $st->execute([$roleSlug]);
                $designation = (string) ($st->fetchColumn() ?: '');
            }
        }


        $this->view('hr/dashboard', [
            'title' => 'HR Dashboard',
            'stats' => $stats,
            'presentPct' => $presentPct,
            'deptChart' => $deptChart,
            'recentJoiners' => $recentJoiners,
            'pendingLeaves' => $pendingLeaves,
            'openQueries' => $openQueries,
            'announcements' => $announcements,
            'holidays' => $holidays,
            'birthdays' => $upcomingBday,
            'anniversaries' => $upcomingAnniv,
            'greetingName' => $user['name'] ?? 'Admin',
            'designation' => $designation !== '' ? $designation : 'Administrator',
            'page' => 'dashboard',
        ]);
    }
}
