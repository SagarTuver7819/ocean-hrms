<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Services\Letterhead;
use Throwable;

class OrgChartController extends Controller
{
    private const PHOTO_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    public function index(): void
    {
        $this->guardView();
        $data = $this->chartData();
        $data['title'] = 'Organization Chart';
        $data['page'] = 'org-chart';
        $data['isAdmin'] = can('employee.edit') || can('*');
        $data['isEmp'] = Auth::isEmployeePortal();
        $this->view('hr/org-chart/index', $data);
    }

    /** Printable A4 on Ocean Infotech letterhead (HR / admin only) */
    public function print(): void
    {
        $this->guardView();
        if (Auth::isEmployeePortal()) {
            $this->flash('error', 'Print is not available on employee portal.');
            $this->redirect('org-chart');
        }
        $data = $this->chartData();
        $data['letterhead'] = Letterhead::config();
        $data['backUrl'] = url('org-chart');
        $this->view('hr/org-chart/print', $data, null);
    }

    public function saveLeaders(): void
    {
        if (!can('employee.edit') && !can('*')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('org-chart');
        }
        $this->requireCsrf('org-chart');

        $founder = mb_substr(trim((string) $this->input('founder_name', '')), 0, 120);
        $co = mb_substr(trim((string) $this->input('co_founder_name', '')), 0, 120);

        $this->putSetting('org_founder_name', $founder);
        $this->putSetting('org_co_founder_name', $co);
        $this->putSetting('org_founder_id', '');
        $this->putSetting('org_co_founder_id', '');

        try {
            foreach (['founder' => 'org_founder_photo', 'co_founder' => 'org_co_founder_photo'] as $field => $key) {
                if (!empty($_POST['remove_' . $field . '_photo'])) {
                    $this->removePhotoFile((string) setting($key, ''));
                    $this->putSetting($key, '');
                    continue;
                }
                if (empty($_FILES[$field . '_photo']) || ($_FILES[$field . '_photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $old = (string) setting($key, '');
                $path = store_upload($_FILES[$field . '_photo'], 'public/assets/uploads/org', self::PHOTO_MIMES, $field . '_');
                if ($path) {
                    $rel = preg_replace('#^public/#', '', $path);
                    $this->removePhotoFile($old);
                    $this->putSetting($key, $rel);
                }
            }
        } catch (Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('org-chart');
        }

        $this->flash('success', 'Founder / Co-Founder saved.');
        $this->redirect('org-chart');
    }

    private function guardView(): void
    {
        if (!Auth::check() || (!can('employee.view') && !can('*') && !Auth::isEmployeePortal())) {
            $this->flash('error', 'Access denied.');
            $this->redirect('login');
        }
    }

    private function chartData(): array
    {
        $db = $this->db();

        $founderName = trim((string) setting('org_founder_name', ''));
        $coFounderName = trim((string) setting('org_co_founder_name', ''));
        $founderPhoto = trim((string) setting('org_founder_photo', ''));
        $coFounderPhoto = trim((string) setting('org_co_founder_photo', ''));

        $founder = $founderName !== '' ? $this->leaderNode($founderName, 'Founder / Direct Owner', $founderPhoto) : null;
        $coFounder = $coFounderName !== '' ? $this->leaderNode($coFounderName, 'Co-Founder / Direct Owner', $coFounderPhoto) : null;

        $hasLeaderCol = (bool) $db->query(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'employees' AND COLUMN_NAME = 'reporting_leader'"
        )->fetchColumn();

        $leaderCol = $hasLeaderCol ? 'e.reporting_leader' : 'NULL';
        $employees = $db->query(
            "SELECT e.id, e.employee_code, e.first_name, e.middle_name, e.last_name, e.profile_photo,
                    e.reporting_manager_id, {$leaderCol} AS reporting_leader, e.status, e.employment_type,
                    e.mobile, e.official_email, e.personal_email, e.joining_date, e.work_location,
                    g.name AS designation_name, d.name AS department_name,
                    CONCAT(m.first_name, ' ', m.last_name) AS manager_name,
                    CASE
                      WHEN {$leaderCol} = 'founder' THEN CONCAT(COALESCE(NULLIF(sf.setting_value, ''), 'Founder'), ' (Direct Owner)')
                      WHEN {$leaderCol} = 'co_founder' THEN CONCAT(COALESCE(NULLIF(scf.setting_value, ''), 'Co-Founder'), ' (Direct Owner)')
                      ELSE NULL
                    END AS leader_manager_name
             FROM employees e
             LEFT JOIN designations g ON g.id = e.designation_id
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN employees m ON m.id = e.reporting_manager_id
             LEFT JOIN settings sf ON sf.setting_key = 'org_founder_name'
             LEFT JOIN settings scf ON scf.setting_key = 'org_co_founder_name'
             WHERE e.status NOT IN ('inactive','resigned','draft')
             ORDER BY e.first_name, e.last_name"
        )->fetchAll();

        $byId = [];
        foreach ($employees as $e) {
            $e['full_name'] = trim(($e['first_name'] ?? '') . ' ' . ($e['middle_name'] ?? '') . ' ' . ($e['last_name'] ?? ''));
            $e['full_name'] = preg_replace('/\s+/', ' ', $e['full_name']);
            $e['initials'] = mb_strtoupper(mb_substr($e['first_name'] ?? '?', 0, 1) . mb_substr($e['last_name'] ?? '', 0, 1));
            $e['children'] = [];
            $e['is_leader'] = false;
            if (!empty($e['leader_manager_name'])) {
                $e['reports_to'] = $e['leader_manager_name'];
            } elseif (!empty(trim((string) ($e['manager_name'] ?? '')))) {
                $e['reports_to'] = trim($e['manager_name']);
            } else {
                $e['reports_to'] = '';
            }
            $byId[(int) $e['id']] = $e;
        }

        $childrenOf = [];
        foreach ($byId as $id => $e) {
            $mgr = (int) ($e['reporting_manager_id'] ?? 0);
            if ($mgr > 0 && isset($byId[$mgr]) && $mgr !== $id) {
                $childrenOf[$mgr][] = $id;
            }
        }

        $build = function (int $id, array $stack = []) use (&$build, &$byId, &$childrenOf): array {
            $node = $byId[$id];
            $kids = [];
            $stack[$id] = true;
            foreach ($childrenOf[$id] ?? [] as $cid) {
                if (isset($stack[$cid])) {
                    continue;
                }
                $kids[] = $build($cid, $stack);
            }
            usort($kids, fn($a, $b) => strcasecmp($a['full_name'], $b['full_name']));
            $node['children'] = $kids;
            return $node;
        };

        $attached = [];
        $founderKids = [];
        $coKids = [];
        $orphanRoots = [];

        foreach ($byId as $id => $e) {
            $mgr = (int) ($e['reporting_manager_id'] ?? 0);
            if ($mgr > 0 && isset($byId[$mgr])) {
                continue; // not a root — sits under another employee
            }
            $leader = (string) ($e['reporting_leader'] ?? '');
            $tree = $build($id);
            if ($leader === 'founder' && $founder) {
                $founderKids[] = $tree;
                $attached[$id] = true;
            } elseif ($leader === 'co_founder' && $coFounder) {
                $coKids[] = $tree;
                $attached[$id] = true;
            } else {
                $orphanRoots[] = $tree;
            }
        }

        // Roots without Direct Owner → hang under Founder (or Co-Founder) so one connected chart
        if ($founder) {
            $founderKids = array_merge($founderKids, $orphanRoots);
            $orphanRoots = [];
        } elseif ($coFounder) {
            $coKids = array_merge($coKids, $orphanRoots);
            $orphanRoots = [];
        }

        usort($founderKids, fn($a, $b) => strcasecmp($a['full_name'], $b['full_name']));
        usort($coKids, fn($a, $b) => strcasecmp($a['full_name'], $b['full_name']));
        usort($orphanRoots, fn($a, $b) => strcasecmp($a['full_name'], $b['full_name']));

        if ($founder) {
            $founder['children'] = $founderKids;
        }
        if ($coFounder) {
            $coFounder['children'] = $coKids;
        }

        return [
            'founder'        => $founder,
            'coFounder'      => $coFounder,
            'teamRoots'      => $orphanRoots, // only when no Founder/Co-Founder set
            'founderName'    => $founderName,
            'coFounderName'  => $coFounderName,
            'founderPhoto'   => $founderPhoto,
            'coFounderPhoto' => $coFounderPhoto,
            'empCount'       => count($byId),
        ];
    }

    private function leaderNode(string $name, string $role, string $photo): array
    {
        $parts = preg_split('/\s+/', $name, 2) ?: [$name];
        $initials = mb_strtoupper(mb_substr($parts[0] ?? '?', 0, 1) . mb_substr($parts[1] ?? $parts[0] ?? '', 0, 1));
        return [
            'id' => 0,
            'full_name' => $name,
            'initials' => $initials,
            'profile_photo' => $photo !== '' ? $photo : null,
            'designation_name' => $role,
            'department_name' => '',
            'employee_code' => '',
            'children' => [],
            'is_leader' => true,
        ];
    }

    private function removePhotoFile(string $relative): void
    {
        if ($relative === '') {
            return;
        }
        $full = BASE_PATH . '/public/' . ltrim($relative, '/');
        if (is_file($full)) {
            @unlink($full);
        }
    }

    private function putSetting(string $key, string $value): void
    {
        $db = $this->db();
        $db->prepare(
            'INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()'
        )->execute([$key, $value]);
        settings_all(true);
    }
}
