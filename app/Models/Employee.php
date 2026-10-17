<?php

namespace App\Models;

use App\Core\Model;

class Employee extends Model
{
    protected string $table = 'employees';

    public function listFiltered(array $filters = []): array
    {
        $sql = "SELECT e.*, d.name AS department_name, des.name AS designation_name,
                       CASE
                         WHEN e.reporting_leader = 'founder' THEN CONCAT(COALESCE(NULLIF(sf.setting_value, ''), 'Founder'), ' (Direct Owner)')
                         WHEN e.reporting_leader = 'co_founder' THEN CONCAT(COALESCE(NULLIF(scf.setting_value, ''), 'Co-Founder'), ' (Direct Owner)')
                         ELSE CONCAT(m.first_name, ' ', m.last_name)
                       END AS manager_name
                FROM employees e
                LEFT JOIN departments d ON d.id = e.department_id
                LEFT JOIN designations des ON des.id = e.designation_id
                LEFT JOIN employees m ON m.id = e.reporting_manager_id
                LEFT JOIN settings sf ON sf.setting_key = 'org_founder_name' AND e.reporting_leader = 'founder'
                LEFT JOIN settings scf ON scf.setting_key = 'org_co_founder_name' AND e.reporting_leader = 'co_founder'
                WHERE 1=1";
        $params = [];

        if (!empty($filters['department_id'])) {
            $sql .= ' AND e.department_id = ?';
            $params[] = $filters['department_id'];
        }
        if (!empty($filters['designation_id'])) {
            $sql .= ' AND e.designation_id = ?';
            $params[] = $filters['designation_id'];
        }
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'inactive') {
                $sql .= " AND e.status IN ('inactive','resigned')";
            } else {
                $sql .= ' AND e.status = ?';
                $params[] = $filters['status'];
            }
        }
        if (!empty($filters['employment_type'])) {
            $sql .= ' AND e.employment_type = ?';
            $params[] = $filters['employment_type'];
        }
        if (!empty($filters['q'])) {
            $sql .= ' AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_code LIKE ? OR e.official_email LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            array_push($params, $q, $q, $q, $q);
        }

        $sql .= ' ORDER BY e.id DESC';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function fullProfile(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            "SELECT e.*, d.name AS department_name, des.name AS designation_name,
                    t.name AS team_name, b.name AS branch_name, s.name AS shift_name,
                    s.start_time AS shift_start, s.end_time AS shift_end,
                    CASE
                      WHEN e.reporting_leader = 'founder' THEN CONCAT(COALESCE(NULLIF(sf.setting_value, ''), 'Founder'), ' (Direct Owner)')
                      WHEN e.reporting_leader = 'co_founder' THEN CONCAT(COALESCE(NULLIF(scf.setting_value, ''), 'Co-Founder'), ' (Direct Owner)')
                      ELSE CONCAT(m.first_name, ' ', m.last_name)
                    END AS manager_name,
                    m.employee_code AS manager_code, u.username AS login_username,
                    u.status AS login_status, u.last_login_at
             FROM employees e
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN designations des ON des.id = e.designation_id
             LEFT JOIN teams t ON t.id = e.team_id
             LEFT JOIN branches b ON b.id = e.branch_id
             LEFT JOIN shifts s ON s.id = e.shift_id
             LEFT JOIN employees m ON m.id = e.reporting_manager_id
             LEFT JOIN settings sf ON sf.setting_key = 'org_founder_name' AND e.reporting_leader = 'founder'
             LEFT JOIN settings scf ON scf.setting_key = 'org_co_founder_name' AND e.reporting_leader = 'co_founder'
             LEFT JOIN users u ON u.id = e.user_id
             WHERE e.id = ?"
        );
        $stmt->execute([$id]);
        $emp = $stmt->fetch();
        if (!$emp) {
            return null;
        }

        foreach (['employee_bank_details', 'employee_emergency_contacts'] as $table) {
            $s = $this->db()->prepare("SELECT * FROM {$table} WHERE employee_id = ? LIMIT 1");
            $s->execute([$id]);
            $emp[str_replace('employee_', '', $table)] = $s->fetch() ?: null;
        }

        $s = $this->db()->prepare('SELECT * FROM employee_documents WHERE employee_id = ? ORDER BY id DESC');
        $s->execute([$id]);
        $emp['documents'] = $s->fetchAll();

        $s = $this->db()->prepare('SELECT * FROM employee_education WHERE employee_id = ? ORDER BY passing_year DESC');
        $s->execute([$id]);
        $emp['education'] = $s->fetchAll();

        $s = $this->db()->prepare('SELECT * FROM employee_experience WHERE employee_id = ? ORDER BY joining_date DESC');
        $s->execute([$id]);
        $emp['experience'] = $s->fetchAll();

        $s = $this->db()->prepare('SELECT * FROM employee_timeline WHERE employee_id = ? ORDER BY event_date DESC, id DESC LIMIT 50');
        $s->execute([$id]);
        $emp['timeline'] = $s->fetchAll();

        return $emp;
    }

    public function nextCode(): string
    {
        $year = date('Y');
        $prefix = 'OI' . $year;
        // Use highest existing code for this year (safe after deletes / soft-exits)
        $stmt = $this->db()->prepare(
            "SELECT employee_code FROM employees WHERE employee_code LIKE ? ORDER BY employee_code DESC LIMIT 1"
        );
        $stmt->execute([$prefix . '%']);
        $last = (string) ($stmt->fetchColumn() ?: '');
        $n = 1;
        if ($last !== '' && preg_match('/^OI' . $year . '(\d+)$/', $last, $m)) {
            $n = (int) $m[1] + 1;
        }
        return $prefix . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }

    public function addTimeline(int $employeeId, string $title, ?string $description = null, ?string $eventDate = null): void
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO employee_timeline (employee_id, title, description, event_date, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $employeeId,
            $title,
            $description,
            $eventDate ?: date('Y-m-d'),
            \App\Core\Auth::id(),
        ]);
    }
}
