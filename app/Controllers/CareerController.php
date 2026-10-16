<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\CSRF;
use App\Models\Candidate;
use App\Services\Letterhead;
use RuntimeException;
use Throwable;

/**
 * Public career application (opened by scanning the recruitment QR code).
 */
class CareerController extends Controller
{
    private const RESUME_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    private const PHOTO_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    public const DEFAULT_POSITIONS = "Web Developer\nWeb Designer\nDesigning\nReceptionist\nSales Executive";

    public static function positions(): array
    {
        $list = preg_split('/\R/', (string) setting('careers_positions', self::DEFAULT_POSITIONS));
        return array_values(array_unique(array_filter(array_map('trim', $list), fn($p) => $p !== '' && strcasecmp($p, 'other') !== 0)));
    }

    public function apply(): void
    {
        $this->view('careers/apply', [
            'title'     => 'Career Application',
            'positions' => self::positions(),
        ], null);
    }

    public function submit(): void
    {
        if (!CSRF::validate($this->input('_csrf'))) {
            $this->json(['ok' => false, 'message' => 'Session expired. Please refresh the page and try again.'], 419);
        }
        if (trim((string) $this->input('website', '')) !== '') {
            $this->json(['ok' => false, 'message' => 'Unable to submit application.'], 422);
        }

        $s = fn(string $k, int $max = 255) => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);
        $nullable = fn(string $k, int $max = 255) => ($v = $s($k, $max)) === '' ? null : $v;

        $position = $s('position', 150);
        if ($position === '__other') {
            $position = $s('position_other', 150);
        } elseif (!in_array($position, self::positions(), true)) {
            $position = '';
        }
        $data = [
            'first_name' => $s('first_name', 80),
            'last_name'  => $s('last_name', 80),
            'mobile'     => preg_replace('/\D/', '', $s('mobile', 20)),
            'parent_mobile' => preg_replace('/\D/', '', $s('parent_mobile', 20)),
            'email'      => strtolower($s('email', 150)),
            'address'    => $s('address', 1000),
        ];

        $source = in_array($_POST['source'] ?? '', Candidate::SOURCES, true) ? $_POST['source'] : null;
        if ($source === 'Other' && ($detail = $s('source_other', 50)) !== '') {
            $source = 'Other: ' . $detail;
        }

        $errors = [];
        if ($position === '') $errors[] = 'Please select the position you are applying for.';
        if ($data['first_name'] === '' || $data['last_name'] === '') $errors[] = 'First and last name are required.';
        if (!preg_match('/^[6-9]\d{9}$/', $data['mobile'])) $errors[] = 'Enter a valid 10-digit mobile number.';
        if (!preg_match('/^[6-9]\d{9}$/', $data['parent_mobile'])) $errors[] = 'Enter a valid 10-digit parents contact number.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
        if ($data['address'] === '') $errors[] = 'Address is required.';
        if (empty($_POST['declaration'])) $errors[] = 'Please accept the declaration.';
        if (empty($_FILES['resume']) || $_FILES['resume']['error'] === UPLOAD_ERR_NO_FILE) $errors[] = 'Please upload your resume.';
        if ($errors) {
            $this->json(['ok' => false, 'message' => implode(' ', $errors)], 422);
        }

        $db = $this->db();
        $st = $db->prepare('SELECT id, department_id FROM designations WHERE name = ? AND status = 1 LIMIT 1');
        $st->execute([$position]);
        $des = $st->fetch() ?: null;
        $designationId = $des ? (int) $des['id'] : null;
        $departmentId = $des['department_id'] ?? null;

        $employmentType = in_array($_POST['employment_type'] ?? '', ['full_time', 'intern', 'part_time'], true)
            ? $_POST['employment_type'] : 'full_time';
        // Internship applicants do not submit work experience
        $isFresher = (!empty($_POST['is_fresher']) || $employmentType === 'intern') ? 1 : 0;
        $experience = [];
        $totalMonths = 0;
        if (!$isFresher) {
            foreach ((array) ($_POST['exp'] ?? []) as $row) {
                $company = mb_substr(trim((string) ($row['company'] ?? '')), 0, 150);
                if ($company === '') continue;
                $current = !empty($row['is_current']) ? 1 : 0;
                $from = preg_match('/^\d{4}-\d{2}$/', $row['from'] ?? '') ? $row['from'] : null;
                $to = $current ? null : (preg_match('/^\d{4}-\d{2}$/', $row['to'] ?? '') ? $row['to'] : null);
                $totalMonths += Candidate::monthsBetween($from, $to);
                $experience[] = [
                    $company,
                    mb_substr(trim((string) ($row['designation'] ?? '')), 0, 120) ?: null,
                    $from, $to, $current,
                    mb_substr(trim((string) ($row['tech_stack'] ?? '')), 0, 255) ?: null,
                    mb_substr(trim((string) ($row['responsibilities'] ?? '')), 0, 2000) ?: null,
                ];
            }
        }

        $model = new Candidate();
        $uploadDir = 'storage/uploads/recruitment/' . date('Y/m');
        $saved = [];
        try {
            $resume = store_upload($_FILES['resume'] ?? null, $uploadDir, self::RESUME_MIMES, 'cv_');
            $saved[] = $resume;
            $photo = store_upload($_FILES['photo'] ?? null, $uploadDir, self::PHOTO_MIMES, 'ph_');
            if ($photo) $saved[] = $photo;

            $db->beginTransaction();
            $dob = $s('date_of_birth', 10);
            $id = $model->create([
                'application_no'          => $model->nextApplicationNo(),
                'public_token'            => bin2hex(random_bytes(16)),
                'department_id'           => $departmentId,
                'designation_id'          => $designationId,
                'position_other'          => $position,
                'employment_type'         => $employmentType,
                'source'                  => $source,
                'referred_by'             => $nullable('referred_by', 120),
                'first_name'              => $data['first_name'],
                'middle_name'             => $nullable('middle_name', 80),
                'last_name'               => $data['last_name'],
                'mobile'                  => $data['mobile'],
                'parent_mobile'           => $data['parent_mobile'],
                'email'                   => $data['email'],
                'date_of_birth'           => preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob) ? $dob : null,
                'gender'                  => in_array($_POST['gender'] ?? '', ['male','female','other'], true) ? $_POST['gender'] : null,
                'marital_status'          => $nullable('marital_status', 40),
                'address'                 => $data['address'],
                'city'                    => $nullable('city', 80),
                'state'                   => $nullable('state', 80),
                'pincode'                 => $nullable('pincode', 20),
                'aadhaar_number'          => $nullable('aadhaar_number', 20),
                'pan_number'              => ($p = $nullable('pan_number', 20)) ? strtoupper($p) : null,
                'is_fresher'              => $isFresher,
                'total_experience_months' => $totalMonths,
                'current_ctc'             => ($employmentType !== 'intern' && is_numeric($_POST['current_ctc'] ?? null)) ? (float) $_POST['current_ctc'] : null,
                'expected_ctc'            => is_numeric($_POST['expected_ctc'] ?? null) ? (float) $_POST['expected_ctc'] : null,
                'notice_period'           => in_array($_POST['notice_period'] ?? '', Candidate::NOTICE_PERIODS, true) ? $_POST['notice_period'] : null,
                'current_location'        => $nullable('current_location', 120),
                'ready_to_relocate'       => !empty($_POST['ready_to_relocate']) ? 1 : 0,
                'primary_skills'          => $nullable('primary_skills', 1000),
                'secondary_skills'        => $nullable('secondary_skills', 1000),
                'certifications'          => $nullable('certifications', 1000),
                'github_url'              => filter_var($s('github_url'), FILTER_VALIDATE_URL) ?: null,
                'linkedin_url'            => filter_var($s('linkedin_url'), FILTER_VALIDATE_URL) ?: null,
                'portfolio_url'           => filter_var($s('portfolio_url'), FILTER_VALIDATE_URL) ?: null,
                'best_project'            => $nullable('best_project', 2000),
                'why_join'                => $nullable('why_join', 2000),
                'photo_path'              => $photo,
                'resume_path'             => $resume,
                'declaration'             => 1,
                'status'                  => 'new',
                'ip_address'              => $_SERVER['REMOTE_ADDR'] ?? null,
                'created_at'              => date('Y-m-d H:i:s'),
            ]);

            $edu = $db->prepare('INSERT INTO candidate_education (candidate_id, degree, institute, specialization, passing_year, percentage_grade) VALUES (?, ?, ?, ?, ?, ?)');
            foreach ((array) ($_POST['edu'] ?? []) as $row) {
                $degree = mb_substr(trim((string) ($row['degree'] ?? '')), 0, 120);
                if ($degree === '') continue;
                $edu->execute([
                    $id, $degree,
                    mb_substr(trim((string) ($row['institute'] ?? '')), 0, 150) ?: null,
                    mb_substr(trim((string) ($row['specialization'] ?? '')), 0, 120) ?: null,
                    mb_substr(trim((string) ($row['year'] ?? '')), 0, 10) ?: null,
                    mb_substr(trim((string) ($row['percentage'] ?? '')), 0, 40) ?: null,
                ]);
            }

            $exp = $db->prepare('INSERT INTO candidate_experience (candidate_id, company, designation, from_month, to_month, is_current, tech_stack, responsibilities) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            foreach ($experience as $row) {
                $exp->execute([$id, ...$row]);
            }
            $db->commit();
        } catch (RuntimeException $e) {
            $this->cleanup($db, $saved);
            $this->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            $this->cleanup($db, $saved);
            $this->json(['ok' => false, 'message' => APP_DEBUG ? $e->getMessage() : 'Something went wrong. Please try again.'], 500);
        }

        $token = $db->query('SELECT public_token FROM candidates WHERE id = ' . (int) $id)->fetchColumn();
        $this->notifyHr($db, (int) $id, $data['first_name'] . ' ' . $data['last_name']);

        $this->json(['ok' => true, 'redirect' => url('apply/thank-you/' . $token)]);
    }

    public function thanks(string $token): void
    {
        $candidate = (new Candidate())->findByToken($token);
        if (!$candidate) {
            $this->redirect('apply');
        }
        $this->view('careers/thanks', [
            'title'     => 'Application Submitted',
            'candidate' => $candidate,
        ], null);
    }

    public function printApplication(string $token): void
    {
        $candidate = (new Candidate())->findByToken($token);
        if (!$candidate) {
            $this->redirect('apply');
        }
        $this->view('recruitment/print', [
            'title'      => 'Application · ' . $candidate['application_no'],
            'candidate'  => $candidate,
            'letterhead' => Letterhead::config(),
            'hrCopy'     => false,
        ], null);
    }

    private function cleanup(\PDO $db, array $files): void
    {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        foreach ($files as $f) {
            if ($f && is_file(BASE_PATH . '/' . $f)) {
                @unlink(BASE_PATH . '/' . $f);
            }
        }
    }

    private function notifyHr(\PDO $db, int $candidateId, string $name): void
    {
        notify_hr('New job application', $name . ' applied via career QR.', 'recruitment/' . $candidateId);
    }
}
