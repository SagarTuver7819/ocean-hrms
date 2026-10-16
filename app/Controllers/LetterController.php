<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Candidate;
use App\Models\Employee;
use App\Services\Letterhead;
use App\Services\Salary;

/**
 * Printable A4 HR letters on the company letterhead.
 */
class LetterController extends Controller
{
    public const TYPES = [
        'employment'  => ['Employment Details Form', 'EMPFORM'],
        'offer'       => ['Offer Letter', 'OFFER'],
        'appointment' => ['Appointment / Joining Letter', 'APPOINT'],
        'experience'  => ['Experience Letter', 'EXP'],
        'relieving'   => ['Relieving Letter', 'RELIEVE'],
    ];

    public function employee(string $id, string $type): void
    {
        if (!can('employee.view')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }
        if (!isset(self::TYPES[$type])) {
            $this->redirect('employees/' . (int) $id);
        }
        $e = (new Employee())->fullProfile((int) $id);
        if (!$e) {
            $this->flash('error', 'Employee not found.');
            $this->redirect('employees');
        }

        $salary = Salary::current((int) $id);

        if ($type === 'employment') {
            $this->view('letters/employment-form', [
                'employee'   => $e,
                'salary'     => $salary,
                'letterhead' => Letterhead::config(),
                'backUrl'    => url('employees/' . (int) $id),
            ], null);
            return;
        }

        $letterDate = $this->dateParam('date') ?? date('Y-m-d');
        $lastDay = $this->dateParam('last_day');
        $meta = array_filter(['last_day' => $lastDay]);
        $ref = $this->reference($type, (int) $id, null, $letterDate, $meta);

        $this->render($type, [
            'person' => [
                'name'        => preg_replace('/\s+/', ' ', trim($e['first_name'] . ' ' . ($e['middle_name'] ?? '') . ' ' . $e['last_name'])),
                'gender'      => $e['gender'],
                'designation' => $e['designation_name'] ?? '',
                'department'  => $e['department_name'] ?? '',
                'code'        => $e['employee_code'],
                'mobile'      => $e['mobile'],
                'address'     => implode(', ', array_filter([$e['current_address'], $e['current_city'], $e['current_state'], $e['current_pincode']])),
            ],
            'employee'    => $e,
            'monthlySalary' => $salary ? (float) $salary['monthly_salary'] : null,
            'joiningDate' => $e['joining_date'],
            'probation'   => max(1, (int) round(((int) ($e['probation_period_days'] ?: 90)) / 30)),
            'lastDay'     => $lastDay ?? date('Y-m-d'),
            'validTill'   => date('Y-m-d', strtotime($letterDate . ' +3 days')),
            'ref'         => $ref,
            'backUrl'     => url('employees/' . (int) $id),
        ]);
    }

    public function candidateOffer(string $id): void
    {
        if (!can('recruitment.manage')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }
        $c = (new Candidate())->fullProfile((int) $id);
        if (!$c || $c['status'] !== 'selected') {
            $this->flash('error', 'Offer letters can be generated only for selected candidates.');
            $this->redirect('recruitment/' . (int) $id);
        }
        if ((float) $c['offered_salary'] <= 0 || !$c['joining_date']) {
            $this->flash('info', 'Finalise the salary and joining date first.');
            $this->redirect('recruitment/' . (int) $id . '?select=1');
        }

        $previous = $this->existing('offer', null, (int) $id);
        $meta = [
            'monthly_salary' => (float) $c['offered_salary'],
            'joining_date'   => $c['joining_date'],
            'valid_till'     => $c['offer_valid_till'] ?: date('Y-m-d', strtotime('+3 days')),
            'probation'      => (int) ($c['probation_months'] ?? 3),
        ];
        $letterDate = $this->dateParam('date') ?? ($previous['letter_date'] ?? date('Y-m-d'));
        $ref = $this->reference('offer', null, (int) $id, $letterDate, $meta);

        $this->render('offer', [
            'person' => [
                'name'        => $c['full_name'],
                'gender'      => $c['gender'],
                'designation' => $c['position'],
                'department'  => $c['department_name'] ?? '',
                'code'        => $c['application_no'],
                'mobile'      => $c['mobile'],
                'address'     => implode(', ', array_filter([$c['address'], $c['city'], $c['state'], $c['pincode']])),
            ],
            'employee'    => null,
            'monthlySalary' => $meta['monthly_salary'],
            'joiningDate' => $meta['joining_date'],
            'probation'   => $meta['probation'],
            'validTill'   => $meta['valid_till'],
            'lastDay'     => null,
            'ref'         => $ref,
            'backUrl'     => url('recruitment/' . (int) $id),
        ]);
    }

    private function render(string $type, array $data): void
    {
        $this->view('letters/document', $data + [
            'type'       => $type,
            'typeLabel'  => self::TYPES[$type][0],
            'letterhead' => Letterhead::config(),
            'letter'     => [
                'signatory'   => setting('letter_signatory_name', 'HR Manager'),
                'signatoryDesignation' => setting('letter_signatory_designation', 'Human Resources'),
                'weekOff'     => setting('letter_week_off', 'Sunday'),
                'jurisdiction'=> setting('letter_jurisdiction', 'Ahmedabad, Gujarat'),
                'notice'      => setting('letter_notice_period', '1 month'),
            ],
            'shift' => $this->shiftHours($data['employee']['shift_id'] ?? null),
        ], null);
    }

    private function shiftHours(?int $shiftId): string
    {
        $db = $this->db();
        $s = $shiftId
            ? $db->prepare('SELECT start_time, end_time FROM shifts WHERE id = ?')
            : $db->prepare('SELECT start_time, end_time FROM shifts WHERE status = 1 ORDER BY id LIMIT 1');
        $s->execute($shiftId ? [$shiftId] : []);
        $row = $s->fetch();
        return $row ? date('h:i A', strtotime($row['start_time'])) . ' to ' . date('h:i A', strtotime($row['end_time'])) : '09:30 AM to 06:30 PM';
    }

    private function dateParam(string $key): ?string
    {
        $v = (string) ($_GET[$key] ?? '');
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) ? $v : null;
    }

    private function existing(string $type, ?int $employeeId, ?int $candidateId): ?array
    {
        $col = $employeeId ? 'employee_id' : 'candidate_id';
        $s = $this->db()->prepare("SELECT * FROM hr_letters WHERE letter_type = ? AND {$col} = ? ORDER BY id DESC LIMIT 1");
        $s->execute([$type, $employeeId ?? $candidateId]);
        return $s->fetch() ?: null;
    }

    /** Reuses the letter's reference number; issues a new one the first time. */
    private function reference(string $type, ?int $employeeId, ?int $candidateId, string $letterDate, array $meta): array
    {
        $db = $this->db();
        $row = $this->existing($type, $employeeId, $candidateId);
        if ($row) {
            $db->prepare('UPDATE hr_letters SET letter_date = ?, meta = ? WHERE id = ?')
                ->execute([$letterDate, $meta ? json_encode($meta) : $row['meta'], $row['id']]);
            return ['no' => $row['ref_no'], 'date' => $letterDate];
        }

        $ts = strtotime($letterDate);
        $fyStart = (int) date('n', $ts) >= 4 ? (int) date('y', $ts) : (int) date('y', $ts) - 1;
        $base = rtrim(setting('letter_ref_prefix', 'OI/HR'), '/') . '/' . self::TYPES[$type][1] . '/' . sprintf('%02d-%02d', $fyStart, $fyStart + 1) . '/';
        $n = $db->prepare('SELECT COUNT(*) FROM hr_letters WHERE ref_no LIKE ?');
        $n->execute([$base . '%']);
        $refNo = $base . str_pad((string) ((int) $n->fetchColumn() + 1), 4, '0', STR_PAD_LEFT);

        $db->prepare('INSERT INTO hr_letters (letter_type, ref_no, employee_id, candidate_id, letter_date, meta, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())')
            ->execute([$type, $refNo, $employeeId, $candidateId, $letterDate, $meta ? json_encode($meta) : null, Auth::id()]);
        activity_log('letters', 'generated', $employeeId ?? $candidateId, null, ['type' => $type, 'ref' => $refNo]);
        return ['no' => $refNo, 'date' => $letterDate];
    }
}
