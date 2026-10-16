<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Candidate;
use App\Models\Employee;
use App\Services\EmployeeLogin;
use App\Services\Letterhead;
use App\Services\Salary;
use Throwable;

class RecruitmentController extends Controller
{
    private function guard(): void
    {
        if (!can('recruitment.manage')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }
    }

    private function guardPost(string $back): void
    {
        $this->guard();
        $this->requireCsrf($back, 'Invalid token. Please try again.');
    }

    private function candidateOr404(string $id): array
    {
        $c = (new Candidate())->fullProfile((int) $id);
        if (!$c) {
            $this->flash('error', 'Application not found.');
            $this->redirect('recruitment');
        }
        return $c;
    }

    public function index(): void
    {
        $this->guard();
        $model = new Candidate();
        $filters = [
            'status'   => $_GET['status'] ?? '',
            'position' => trim($_GET['position'] ?? ''),
            'q'        => trim($_GET['q'] ?? ''),
        ];
        $positions = $this->db()->query(
            "SELECT DISTINCT position_other FROM candidates WHERE position_other IS NOT NULL AND position_other <> ''"
        )->fetchAll(\PDO::FETCH_COLUMN);
        $positions = array_values(array_unique(array_merge(CareerController::positions(), $positions)));

        $this->view('hr/recruitment/index', [
            'title'      => 'Recruitment Applications',
            'page'       => 'recruitment',
            'candidates' => $model->listFiltered($filters),
            'counts'     => $model->statusCounts(),
            'filters'    => $filters,
            'positions'  => $positions,
            'viewMode'   => ($_GET['view'] ?? 'grid') === 'list' ? 'list' : 'grid',
        ]);
    }

    public function interviews(): void
    {
        $this->guard();
        $status = $_GET['status'] ?? '';
        $filters = [
            'statuses' => in_array($status, Candidate::INTERVIEW_STAGES, true) ? [$status] : Candidate::INTERVIEW_STAGES,
            'q'        => trim($_GET['q'] ?? ''),
        ];
        $this->view('hr/recruitment/interviews', [
            'title'      => 'Interview Candidates',
            'page'       => 'recruitment-interviews',
            'candidates' => (new Candidate())->listFiltered($filters),
            'status'     => $status,
            'q'          => $filters['q'],
        ]);
    }

    public function qr(): void
    {
        $this->guard();
        $app = require BASE_PATH . '/app/Config/app.php';
        $applyUrl = rtrim($app['public_url'] ?: $app['url'], '/') . '/apply';
        $this->view('hr/recruitment/qr', [
            'title'    => 'Apply QR Code',
            'page'     => 'recruitment-qr',
            'applyUrl' => $applyUrl,
            'isLocal'  => (bool) preg_match('#^https?://(localhost|127\.0\.0\.1)#', $applyUrl),
            'company'  => setting('company_name', 'Ocean Infotech'),
        ]);
    }

    public function show(string $id): void
    {
        $this->guard();
        $c = $this->candidateOr404($id);
        $this->view('hr/recruitment/show', [
            'title'     => 'Application · ' . $c['application_no'],
            'page'      => 'recruitment',
            'candidate' => $c,
        ]);
    }

    public function printForm(string $id): void
    {
        $this->guard();
        $c = $this->candidateOr404($id);
        $this->view('recruitment/print', [
            'title'      => 'Application · ' . $c['application_no'],
            'candidate'  => $c,
            'letterhead' => Letterhead::config(),
            'hrCopy'     => true,
        ], null);
    }

    public function file(string $id, string $type): void
    {
        $this->guard();
        $c = (new Candidate())->find((int) $id);
        $path = match ($type) {
            'resume' => $c['resume_path'] ?? null,
            'photo'  => $c['photo_path'] ?? null,
            default  => null,
        };
        $full = $path ? realpath(BASE_PATH . '/' . $path) : false;
        $root = realpath(BASE_PATH . '/storage/uploads/recruitment');
        if (!$full || !$root || !str_starts_with($full, $root) || !is_file($full)) {
            http_response_code(404);
            exit('File not found.');
        }
        $name = preg_replace('/[^A-Za-z0-9_-]+/', '_', $c['first_name'] . '_' . $c['last_name']) . '_' . $type . '.' . pathinfo($full, PATHINFO_EXTENSION);
        header('Content-Type: ' . (mime_content_type($full) ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($full));
        header('Content-Disposition: ' . (isset($_GET['download']) ? 'attachment' : 'inline') . '; filename="' . $name . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($full);
        exit;
    }

    public function updateStatus(string $id): void
    {
        $this->guardPost('recruitment/' . $id);
        $c = $this->candidateOr404($id);
        $status = (string) $this->input('status');
        if (!in_array($status, Candidate::DECISIONS, true)) {
            $this->flash('error', 'Please choose Selected or Not Selected.');
            $this->redirect('recruitment/' . $id);
        }
        if ($c['employee_id'] && $status !== 'selected') {
            $this->flash('error', 'This candidate is already an employee.');
            $this->redirect('recruitment/' . $id);
        }
        if ($status === 'selected') {
            $this->flash('info', 'Finalise the salary and joining date to select this candidate.');
            $this->redirect('recruitment/' . $id . '?select=1');
        }
        (new Candidate())->update((int) $id, [
            'status'     => $status,
            'hr_remarks' => trim((string) $this->input('hr_remarks', '')) ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        activity_log('recruitment', 'status_changed', (int) $id, ['status' => $c['status']], ['status' => $status]);
        $this->flash('success', 'Status updated to ' . Candidate::statusLabel($status) . '.');
        $this->redirect($this->input('back') === 'interviews' ? 'recruitment/interviews' : 'recruitment/' . $id);
    }

    /** Marks the candidate Selected with the finalised salary and joining details, then opens the offer letter. */
    public function select(string $id): void
    {
        $back = 'recruitment/' . $id . '?select=1';
        $this->guardPost($back);
        $c = $this->candidateOr404($id);

        $date = fn(string $key) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $v = (string) $this->input($key, '')) && strtotime($v) ? $v : null;
        $salary = round((float) $this->input('offered_salary', 0), 2);
        $joining = $date('joining_date');
        $validTill = $date('offer_valid_till');
        if ($salary <= 0 || !$joining) {
            $this->flash('error', 'Final monthly salary and joining date are required.');
            $this->redirect($back);
        }
        if ($validTill && $validTill > $joining) {
            $validTill = $joining;
        }

        (new Candidate())->update((int) $id, [
            'status'           => 'selected',
            'offered_salary'   => $salary,
            'joining_date'     => $joining,
            'probation_months' => max(0, min(12, (int) $this->input('probation_months', 3))),
            'offer_valid_till' => $validTill ?? date('Y-m-d', min(strtotime('+3 days'), strtotime($joining))),
            'hr_remarks'       => trim((string) $this->input('hr_remarks', '')) ?: null,
            'selected_at'      => $c['selected_at'] ?: date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        activity_log('recruitment', 'selected', (int) $id, ['status' => $c['status']], ['status' => 'selected', 'salary' => $salary, 'joining' => $joining]);

        $this->flash('success', 'Candidate selected · salary ' . money($salary) . ' / month · joining ' . format_date($joining) . '.');
        $this->redirect($this->input('open_offer') ? 'recruitment/' . $id . '/offer-letter' : 'recruitment/' . $id);
    }

    public function storeInterview(string $id): void
    {
        $this->guardPost('recruitment/' . $id);
        $this->candidateOr404($id);
        $round = (string) $this->input('round');
        $score = fn(string $k) => ($n = (int) $this->input($k, 0)) >= 1 && $n <= 5 ? $n : null;
        $at = (string) $this->input('interview_at', '');

        $this->db()->prepare(
            'INSERT INTO candidate_interviews
             (candidate_id, round, interview_at, mode, interviewer, technical_score, communication_score, problem_solving_score, attitude_score, result, feedback, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        )->execute([
            (int) $id,
            isset(Candidate::ROUNDS[$round]) ? $round : 'technical',
            strtotime($at) ? date('Y-m-d H:i:s', strtotime($at)) : date('Y-m-d H:i:s'),
            in_array($this->input('mode'), ['in_person', 'video', 'phone'], true) ? $this->input('mode') : 'in_person',
            mb_substr(trim((string) $this->input('interviewer', '')), 0, 120) ?: null,
            $score('technical_score'), $score('communication_score'), $score('problem_solving_score'), $score('attitude_score'),
            in_array($this->input('result'), ['pending', 'pass', 'fail', 'hold'], true) ? $this->input('result') : 'pending',
            trim((string) $this->input('feedback', '')) ?: null,
            Auth::id(),
        ]);

        activity_log('recruitment', 'interview_added', (int) $id);
        $this->flash('success', 'Interview round saved.');
        $this->redirect('recruitment/' . $id . '#interviews');
    }

    public function deleteInterview(string $id, string $roundId): void
    {
        $this->guardPost('recruitment/' . $id);
        $this->db()->prepare('DELETE FROM candidate_interviews WHERE id = ? AND candidate_id = ?')
            ->execute([(int) $roundId, (int) $id]);
        $this->flash('success', 'Interview round removed.');
        $this->redirect('recruitment/' . $id . '#interviews');
    }

    public function convert(string $id): void
    {
        $this->guardPost('recruitment/' . $id);
        if (!can('employee.create')) {
            $this->flash('error', 'You do not have permission to create employees.');
            $this->redirect('recruitment/' . $id);
        }
        $c = $this->candidateOr404($id);
        if ($c['employee_id']) {
            $this->redirect('employees/' . $c['employee_id']);
        }
        if ($c['status'] !== 'selected') {
            $this->flash('error', 'Only selected candidates can be converted to employees.');
            $this->redirect('recruitment/' . $id);
        }

        $joining = (string) $this->input('joining_date', '');
        $joining = preg_match('/^\d{4}-\d{2}-\d{2}$/', $joining) ? $joining : ($c['joining_date'] ?: date('Y-m-d'));
        $db = $this->db();
        $employees = new Employee();
        try {
            $db->beginTransaction();
            $empId = $employees->create([
                'employee_code'   => $employees->nextCode(),
                'first_name'      => $c['first_name'],
                'middle_name'     => $c['middle_name'],
                'last_name'       => $c['last_name'],
                'gender'          => $c['gender'],
                'date_of_birth'   => $c['date_of_birth'],
                'mobile'          => $c['mobile'],
                'personal_email'  => $c['email'],
                'marital_status'  => $c['marital_status'],
                'current_address' => $c['address'],
                'current_city'    => $c['city'],
                'current_state'   => $c['state'],
                'current_pincode' => $c['pincode'],
                'joining_date'    => $joining,
                'employment_type' => $c['employment_type'],
                'department_id'   => $c['department_id'],
                'designation_id'  => $c['designation_id'],
                'status'          => 'onboarding',
                'probation_period_days' => $c['probation_months'] !== null ? (int) $c['probation_months'] * 30 : 90,
                'pan_number'      => $c['pan_number'],
                'aadhaar_number'  => $c['aadhaar_number'],
                'created_at'      => date('Y-m-d H:i:s'),
            ]);

            $edu = $db->prepare('INSERT INTO employee_education (employee_id, degree, institute, passing_year, percentage_grade, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
            foreach ($c['education'] as $e) {
                $year = preg_match('/^\d{4}$/', (string) $e['passing_year']) ? $e['passing_year'] : null;
                $edu->execute([$empId, trim($e['degree'] . ($e['specialization'] ? ' (' . $e['specialization'] . ')' : '')), $e['institute'], $year, $e['percentage_grade']]);
            }
            $exp = $db->prepare('INSERT INTO employee_experience (employee_id, company, designation, joining_date, leaving_date, total_experience, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            foreach ($c['experience'] as $x) {
                $exp->execute([
                    $empId, $x['company'], $x['designation'],
                    $x['from_month'] ? $x['from_month'] . '-01' : null,
                    (!$x['is_current'] && $x['to_month']) ? $x['to_month'] . '-01' : null,
                    Candidate::experienceLabel(Candidate::monthsBetween($x['from_month'], $x['is_current'] ? null : $x['to_month'])),
                ]);
            }
            if ($c['resume_path']) {
                $db->prepare(
                    "INSERT INTO employee_documents (employee_id, document_name, document_type, file_path, upload_date, uploaded_by, status, created_at)
                     VALUES (?, 'Resume / CV', 'Resume', ?, CURDATE(), ?, 'pending', NOW())"
                )->execute([$empId, $c['resume_path'], Auth::id()]);
            }

            if ((float) $c['offered_salary'] > 0) {
                Salary::set($empId, (float) $c['offered_salary'], $joining, 'Final salary at selection');
            }
            (new Candidate())->update((int) $id, ['employee_id' => $empId, 'updated_at' => date('Y-m-d H:i:s')]);
            $employees->addTimeline($empId, 'Hired via recruitment', 'Application ' . $c['application_no'] . ' for ' . $c['position'], $joining);
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            $this->flash('error', 'Conversion failed: ' . (APP_DEBUG ? $e->getMessage() : 'please try again.'));
            $this->redirect('recruitment/' . $id);
        }

        activity_log('recruitment', 'converted_to_employee', (int) $id, null, ['employee_id' => $empId]);
        $login = EmployeeLogin::issue($empId);
        $this->flash('success', 'Employee created · Login ' . $login['username'] . ' / ' . $login['password'] . '. Complete the remaining details.');
        $this->redirect('employees/' . $empId . '/edit');
    }

    public function destroy(string $id): void
    {
        $this->guardPost('recruitment');
        $c = (new Candidate())->find((int) $id);
        if ($c) {
            foreach (['resume_path', 'photo_path'] as $col) {
                if ($c[$col] && !$c['employee_id'] && is_file(BASE_PATH . '/' . $c[$col])) {
                    @unlink(BASE_PATH . '/' . $c[$col]);
                }
            }
            (new Candidate())->delete((int) $id);
            activity_log('recruitment', 'deleted', (int) $id);
        }
        $this->flash('success', 'Application deleted.');
        $this->redirect('recruitment');
    }
}
