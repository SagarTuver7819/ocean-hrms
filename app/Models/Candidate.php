<?php

namespace App\Models;

use App\Core\Model;

class Candidate extends Model
{
    protected string $table = 'candidates';

    public const STATUSES = [
        'new'      => ['Pending Review', 'badge-pending'],
        'selected' => ['Selected', 'badge-active'],
        'rejected' => ['Not Selected', 'badge-inactive'],
    ];

    public const DECISIONS = ['selected', 'rejected'];

    public const INTERVIEW_STAGES = ['new'];

    public const ROUNDS = [
        'screening'    => 'Screening',
        'technical'    => 'Technical Interview',
        'machine_test' => 'Machine / Coding Test',
        'managerial'   => 'Managerial Round',
        'hr'           => 'HR Round',
    ];

    public const SOURCES = ['Walk-in', 'LinkedIn', 'Naukri', 'Indeed', 'Company Website', 'Employee Referral', 'College / Campus', 'Consultant', 'Social Media', 'Other'];

    public const NOTICE_PERIODS = ['Immediate', '15 Days', '30 Days', '45 Days', '60 Days', '90 Days'];

    public static function statusLabel(string $status): string
    {
        return self::STATUSES[$status][0] ?? ucfirst(str_replace('_', ' ', $status));
    }

    public static function statusBadge(string $status): string
    {
        return self::STATUSES[$status][1] ?? 'badge-pending';
    }

    public static function experienceLabel(int $months, bool $fresher = false): string
    {
        if ($fresher || $months <= 0) {
            return 'Fresher';
        }
        $y = intdiv($months, 12);
        $m = $months % 12;
        $parts = [];
        if ($y) $parts[] = $y . ' yr' . ($y > 1 ? 's' : '');
        if ($m) $parts[] = $m . ' month' . ($m > 1 ? 's' : '');
        return implode(' ', $parts);
    }

    public static function monthsBetween(?string $from, ?string $to): int
    {
        if (!$from || !preg_match('/^\d{4}-\d{2}$/', $from)) {
            return 0;
        }
        $to = ($to && preg_match('/^\d{4}-\d{2}$/', $to)) ? $to : date('Y-m');
        [$fy, $fm] = array_map('intval', explode('-', $from));
        [$ty, $tm] = array_map('intval', explode('-', $to));
        return max(0, ($ty - $fy) * 12 + ($tm - $fm) + 1);
    }

    public function nextApplicationNo(): string
    {
        $prefix = 'OIR-' . date('Ymd') . '-';
        $stmt = $this->db()->prepare('SELECT COUNT(*) FROM candidates WHERE application_no LIKE ?');
        $stmt->execute([$prefix . '%']);
        return $prefix . str_pad((string) ((int) $stmt->fetchColumn() + 1), 4, '0', STR_PAD_LEFT);
    }

    public function listFiltered(array $filters = []): array
    {
        $sql = "SELECT c.*, d.name AS department_name, des.name AS designation_name,
                       (SELECT COUNT(*) FROM candidate_interviews ci WHERE ci.candidate_id = c.id) AS rounds_count,
                       (SELECT ci.interview_at FROM candidate_interviews ci WHERE ci.candidate_id = c.id ORDER BY ci.interview_at DESC, ci.id DESC LIMIT 1) AS last_interview_at
                FROM candidates c
                LEFT JOIN departments d ON d.id = c.department_id
                LEFT JOIN designations des ON des.id = c.designation_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['statuses'])) {
            $sql .= ' AND c.status IN (' . implode(',', array_fill(0, count($filters['statuses']), '?')) . ')';
            array_push($params, ...$filters['statuses']);
        } elseif (!empty($filters['status'])) {
            $sql .= ' AND c.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['department_id'])) {
            $sql .= ' AND c.department_id = ?';
            $params[] = $filters['department_id'];
        }
        if (!empty($filters['position'])) {
            $sql .= ' AND c.position_other = ?';
            $params[] = $filters['position'];
        }
        if (!empty($filters['q'])) {
            $sql .= ' AND (c.first_name LIKE ? OR c.last_name LIKE ? OR c.mobile LIKE ? OR c.email LIKE ? OR c.application_no LIKE ? OR c.primary_skills LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            array_push($params, $q, $q, $q, $q, $q, $q);
        }

        $sql .= ' ORDER BY c.id DESC';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function statusCounts(): array
    {
        $counts = array_fill_keys(array_keys(self::STATUSES), 0);
        foreach ($this->db()->query('SELECT status, COUNT(*) n FROM candidates GROUP BY status') as $row) {
            $counts[$row['status']] = (int) $row['n'];
        }
        return $counts;
    }

    public function fullProfile(int $id): ?array
    {
        return $this->loadProfile('c.id = ?', $id);
    }

    public function findByToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            return null;
        }
        return $this->loadProfile('c.public_token = ?', $token);
    }

    private function loadProfile(string $where, int|string $value): ?array
    {
        $stmt = $this->db()->prepare(
            "SELECT c.*, d.name AS department_name, des.name AS designation_name
             FROM candidates c
             LEFT JOIN departments d ON d.id = c.department_id
             LEFT JOIN designations des ON des.id = c.designation_id
             WHERE {$where} LIMIT 1"
        );
        $stmt->execute([$value]);
        $c = $stmt->fetch();
        if (!$c) {
            return null;
        }
        $id = (int) $c['id'];

        $s = $this->db()->prepare('SELECT * FROM candidate_education WHERE candidate_id = ? ORDER BY id');
        $s->execute([$id]);
        $c['education'] = $s->fetchAll();

        $s = $this->db()->prepare('SELECT * FROM candidate_experience WHERE candidate_id = ? ORDER BY is_current DESC, from_month DESC');
        $s->execute([$id]);
        $c['experience'] = $s->fetchAll();

        $s = $this->db()->prepare('SELECT * FROM candidate_interviews WHERE candidate_id = ? ORDER BY interview_at, id');
        $s->execute([$id]);
        $c['interviews'] = $s->fetchAll();

        $c['full_name'] = trim($c['first_name'] . ' ' . ($c['middle_name'] ? $c['middle_name'] . ' ' : '') . $c['last_name']);
        $c['position'] = $c['position_other'] ?: ($c['designation_name'] ?: '—');
        return $c;
    }
}
