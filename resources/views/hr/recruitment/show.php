<?php
use App\Models\Candidate;

$c = $candidate;
$v = fn($x) => ($x === null || $x === '') ? '—' : e((string) $x);
$inr = fn($x) => $x !== null && $x !== '' ? '₹' . inr($x) . ' / yr' : '—';
$month = fn($m) => $m ? date('M Y', strtotime($m . '-01')) : '—';
$types = ['full_time' => 'Full Time', 'intern' => 'Internship', 'contract' => 'Contract', 'part_time' => 'Part Time'];
$age = $c['date_of_birth'] ? (new DateTime($c['date_of_birth']))->diff(new DateTime())->y . ' yrs' : null;
$canConvert = !$c['employee_id'] && $c['status'] === 'selected';
$offerReady = $c['status'] === 'selected' && (float) $c['offered_salary'] > 0 && $c['joining_date'];
$suggestedSalary = (float) $c['offered_salary'] ?: ($c['expected_ctc'] ? round($c['expected_ctc'] / 12 / 500) * 500 : 0);
$offerUrl = url('recruitment/' . $c['id'] . '/offer-letter');
$kv = function (array $rows) {
    $html = '<div class="row g-3">';
    foreach ($rows as $row) {
        $html .= '<div class="col-' . ($row[2] ?? 'md-4') . '"><div class="kv-label">' . e($row[0]) . '</div><div class="kv-value">' . $row[1] . '</div></div>';
    }
    return $html . '</div>';
};
?>
<?php
$head = fn(string $icon, string $title, string $sub, string $end = '') =>
    '<div class="ep-card-head"><span class="ep-card-ico"><i class="fa-solid ' . $icon . '"></i></span><div><h3>' . $title . '</h3><small>' . $sub . '</small></div>'
    . ($end !== '' ? '<div class="ms-auto">' . $end . '</div>' : '') . '</div>';
$expLabel = Candidate::experienceLabel((int) $c['total_experience_months'], (bool) $c['is_fresher']);
?>
<div class="ep-topbar">
  <a href="<?= url('recruitment') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-arrow-left me-1"></i>Back to Applications</a>
</div>

<div class="ep-hero">
  <div class="ep-hero-top">
    <?php if ($c['photo_path']): ?>
      <img src="<?= url('recruitment/' . $c['id'] . '/file/photo') ?>" class="ep-avatar cand-hero-photo" alt="">
    <?php else: ?>
      <div class="ep-avatar"><?= e(strtoupper(mb_substr($c['first_name'], 0, 1) . mb_substr($c['last_name'], 0, 1))) ?></div>
    <?php endif; ?>
    <div class="ep-id">
      <span class="badge-status <?= Candidate::statusBadge($c['status']) ?>"><?= e(Candidate::statusLabel($c['status'])) ?></span>
      <?php if ($c['employee_id']): ?><span class="badge-status badge-late ms-1">Employee</span><?php endif; ?>
      <div class="ep-code"><?= e($c['application_no']) ?></div>
      <div class="ep-name"><?= e($c['full_name']) ?></div>
      <div class="ep-meta">
        <span><i class="fa-solid fa-briefcase"></i><?= e($c['position']) ?></span>
        <span><i class="fa-solid fa-location-dot"></i><?= $v($c['city']) ?></span>
        <span class="ep-type"><?= e($types[$c['employment_type']] ?? 'Full Time') ?></span>
      </div>
    </div>
    <div class="ep-actions">
      <a href="<?= url('recruitment/' . $c['id'] . '/print') ?>" target="_blank" class="btn btn-sm btn-brand"><i class="fa-solid fa-print me-1"></i>A4 Application Form</a>
      <?php if ($offerReady): ?>
        <a href="<?= $offerUrl ?>" target="_blank" class="btn btn-sm btn-success"><i class="fa-solid fa-file-signature me-1"></i>Offer Letter</a>
      <?php endif; ?>
      <?php if ($c['resume_path']): ?>
        <a href="<?= url('recruitment/' . $c['id'] . '/file/resume') ?>" target="_blank" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-file-pdf me-1"></i>Resume</a>
      <?php endif; ?>
      <?php if ($c['employee_id']): ?>
        <a href="<?= url('employees/' . $c['employee_id']) ?>" class="btn btn-sm btn-dark"><i class="fa-solid fa-id-badge me-1"></i>Employee Profile</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="ep-chips cand-chips">
    <div class="ep-chip t-orange"><span class="ep-chip-ico"><i class="fa-solid fa-calendar-plus"></i></span><div><small>Applied On</small><b><?= format_date($c['created_at']) ?></b></div></div>
    <div class="ep-chip t-orange"><span class="ep-chip-ico"><i class="fa-solid fa-phone"></i></span><div><small>Mobile</small><b><?= e($c['mobile']) ?></b></div></div>
    <div class="ep-chip t-orange"><span class="ep-chip-ico"><i class="fa-solid fa-envelope"></i></span><div><small>Email</small><b class="ep-break" title="<?= e($c['email']) ?>"><?= e($c['email']) ?></b></div></div>
    <div class="ep-chip t-orange"><span class="ep-chip-ico"><i class="fa-solid fa-building"></i></span><div><small>Experience</small><b><?= e($expLabel) ?></b></div></div>
    <?php if ($offerReady): ?>
      <div class="ep-chip t-green"><span class="ep-chip-ico"><i class="fa-solid fa-indian-rupee-sign"></i></span><div><small>Final Salary</small><b><?= money($c['offered_salary']) ?> / mo</b><em>Expected <?= $inr($c['expected_ctc']) ?></em></div></div>
    <?php else: ?>
      <div class="ep-chip t-orange"><span class="ep-chip-ico"><i class="fa-solid fa-indian-rupee-sign"></i></span><div><small>Expected CTC</small><b><?= $inr($c['expected_ctc']) ?></b></div></div>
    <?php endif; ?>
    <div class="ep-chip t-orange"><span class="ep-chip-ico"><i class="fa-solid fa-hourglass-half"></i></span><div><small>Notice Period</small><b><?= $v($c['notice_period']) ?></b></div></div>
  </div>
</div>

<div class="row g-3">
  <div class="col-xl-8">
    <div class="ep-card cp tone-orange">
      <?= $head('fa-briefcase', 'Applied Position', 'Role &amp; application source') ?>
      <div class="cp-body"><?= $kv([
        ['Position', $v($c['position'])], ['Employment Type', $v($types[$c['employment_type']] ?? null)], ['Source', $v($c['source'])],
        ['Referred By', $v($c['referred_by'])],
      ]) ?></div>
    </div>

    <div class="ep-card cp tone-orange">
      <?= $head('fa-user', 'Personal Details', 'Identity &amp; contact information') ?>
      <div class="cp-body"><?= $kv([
        ['Mobile', '<a href="tel:' . e($c['mobile']) . '">' . e($c['mobile']) . '</a>'], ['Parents Contact', !empty($c['parent_mobile']) ? '<a href="tel:' . e($c['parent_mobile']) . '">' . e($c['parent_mobile']) . '</a>' : $v($c['alt_mobile'])],
        ['Date of Birth', $c['date_of_birth'] ? format_date($c['date_of_birth']) . ' (' . $age . ')' : '—'],
        ['Email', '<a href="mailto:' . e($c['email']) . '">' . e($c['email']) . '</a>', 'md-8'], ['Gender', $v($c['gender'] ? ucfirst($c['gender']) : null)],
        ['Marital Status', $v($c['marital_status'])], ['Aadhaar No.', $v($c['aadhaar_number'])], ['PAN No.', $v($c['pan_number'])],
        ['Address', $v($c['address']), 'md-8'], ['City / State / PIN', $v(trim(implode(', ', array_filter([$c['city'], $c['state'], $c['pincode']]))))],
      ]) ?></div>
    </div>

    <div class="ep-card cp tone-orange">
      <?= $head('fa-graduation-cap', 'Education', 'Academic qualifications') ?>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead><tr><th>Degree</th><th>College / University</th><th>Specialization</th><th>Year</th><th>% / CGPA</th></tr></thead>
          <tbody>
          <?php foreach ($c['education'] as $ed): ?>
            <tr><td class="fw-bold"><?= $v($ed['degree']) ?></td><td><?= $v($ed['institute']) ?></td><td><?= $v($ed['specialization']) ?></td><td><?= $v($ed['passing_year']) ?></td><td><span class="cp-pill"><?= $v($ed['percentage_grade']) ?></span></td></tr>
          <?php endforeach; ?>
          <?php if (!$c['education']): ?><tr><td colspan="5" class="text-muted text-center">Not provided</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="ep-card cp tone-orange">
      <?= $head('fa-building', 'Experience', 'Previous employment', '<span class="cp-pill">' . e($expLabel) . '</span>') ?>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead><tr><th>Company</th><th>Designation</th><th>Period</th><th>Technologies / Responsibilities</th></tr></thead>
          <tbody>
          <?php foreach ($c['experience'] as $ex): ?>
            <tr>
              <td class="fw-bold"><?= $v($ex['company']) ?></td>
              <td><?= $v($ex['designation']) ?></td>
              <td class="text-nowrap"><?= $month($ex['from_month']) ?> – <?= $ex['is_current'] ? 'Present' : $month($ex['to_month']) ?>
                <div class="small text-muted"><?= e(Candidate::experienceLabel(Candidate::monthsBetween($ex['from_month'], $ex['is_current'] ? null : $ex['to_month']))) ?></div></td>
              <td><?= $ex['tech_stack'] ? '<b>' . e($ex['tech_stack']) . '</b><br>' : '' ?><span class="small"><?= nl2br(e((string) $ex['responsibilities'])) ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$c['experience']): ?><tr><td colspan="4" class="text-muted text-center">Fresher</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="ep-card cp tone-orange">
      <?= $head('fa-indian-rupee-sign', 'Compensation &amp; Availability', 'Salary expectation &amp; joining') ?>
      <div class="cp-body"><?= $kv([
        ['Current CTC', $c['is_fresher'] ? 'Fresher' : $inr($c['current_ctc'])], ['Expected CTC', $inr($c['expected_ctc'])], ['Notice Period', $v($c['notice_period'])],
        ['Current Location', $v($c['current_location'])], ['Ready to Relocate', $c['ready_to_relocate'] ? 'Yes' : 'No'],
      ]) ?></div>
    </div>

    <div class="ep-card cp tone-orange">
      <?= $head('fa-code', 'Skills &amp; Profile', 'Technical skills, links &amp; motivation') ?>
      <div class="cp-body">
        <?php $link = fn($u) => $u ? '<a href="' . e($u) . '" target="_blank" rel="noopener">' . e($u) . '</a>' : '—'; ?>
        <?= $kv([
          ['Primary Skills', $v($c['primary_skills']), 'md-6'], ['Other Skills / Tools', $v($c['secondary_skills']), 'md-6'],
          ['Certifications', $v($c['certifications']), '12'],
          ['GitHub', $link($c['github_url'])], ['LinkedIn', $link($c['linkedin_url'])], ['Portfolio', $link($c['portfolio_url'])],
          ['Best Project', nl2br($v($c['best_project'])), '12'], ['Why join us', nl2br($v($c['why_join'])), '12'],
        ]) ?>
      </div>
    </div>
  </div>

  <div class="col-xl-4">
    <div class="ep-card cp tone-orange">
      <?= $head('fa-gavel', 'Final Decision', 'Selected or Not Selected') ?>
      <div class="cp-body">
        <form method="post" action="<?= url('recruitment/' . $c['id'] . '/status') ?>" class="js-loading">
          <?= csrf_field() ?>
          <label class="form-label">HR Remarks</label>
          <textarea name="hr_remarks" id="decisionRemarks" class="form-control mb-3" rows="3" placeholder="Reason / interview summary"><?= e($c['hr_remarks'] ?? '') ?></textarea>
          <div class="decision-btns">
            <button type="button" class="decision-btn is-yes <?= $c['status'] === 'selected' ? 'active' : '' ?>" data-bs-toggle="modal" data-bs-target="#selectModal">
              <i class="fa-solid fa-circle-check"></i>Selected
            </button>
            <button name="status" value="rejected" class="decision-btn is-no <?= $c['status'] === 'rejected' ? 'active' : '' ?>" <?= $c['employee_id'] ? 'disabled' : '' ?>>
              <i class="fa-solid fa-circle-xmark"></i>Not Selected
            </button>
          </div>
        </form>
      </div>
    </div>

    <?php if ($offerReady): ?>
      <div class="ep-card cp tone-green">
        <?= $head('fa-file-signature', 'Offer Details', 'Finalised on selection', '<button type="button" class="btn btn-sm btn-soft" data-bs-toggle="modal" data-bs-target="#selectModal"><i class="fa-solid fa-pen me-1"></i>Edit</button>') ?>
        <div class="cp-body">
          <div class="salary-now">
            <small>Final monthly salary</small>
            <b><?= money($c['offered_salary']) ?></b>
            <span><?= e(amount_in_words($c['offered_salary'])) ?></span>
          </div>
          <?= $kv([
            ['Joining Date', format_date($c['joining_date']), '6'],
            ['Probation', (int) $c['probation_months'] . ' month(s)', '6'],
            ['Accept Offer By', $c['offer_valid_till'] ? format_date($c['offer_valid_till']) : '—', '6'],
            ['Annual', money($c['offered_salary'] * 12), '6'],
          ]) ?>
          <a href="<?= $offerUrl ?>" target="_blank" class="btn btn-success w-100 mt-3"><i class="fa-solid fa-print me-1"></i>Open Offer Letter (A4)</a>
        </div>
      </div>
    <?php endif; ?>

    <div class="ep-card cp tone-orange" id="interviews">
      <?= $head('fa-user-check', 'Interview Rounds', count($c['interviews']) . ' round(s) recorded', '<button type="button" class="btn btn-sm btn-brand" data-bs-toggle="modal" data-bs-target="#roundModal"><i class="fa-solid fa-plus me-1"></i>Add</button>') ?>
      <div class="cp-body">
        <?php if (!$c['interviews']): ?>
          <p class="text-muted small mb-0">No interview rounds recorded yet.</p>
        <?php endif; ?>
        <?php foreach ($c['interviews'] as $r):
          $scores = array_filter([$r['technical_score'], $r['communication_score'], $r['problem_solving_score'], $r['attitude_score']], fn($s) => $s !== null);
          $avg = $scores ? round(array_sum($scores) / count($scores), 1) : null;
          $resBadge = ['pass' => 'badge-active', 'fail' => 'badge-inactive', 'hold' => 'badge-pending', 'pending' => 'badge-late'][$r['result']];
        ?>
          <div class="round-item">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <b><?= e(Candidate::ROUNDS[$r['round']] ?? $r['round']) ?></b>
                <div class="small text-muted"><?= format_date($r['interview_at'], 'd M Y, h:i A') ?> · <?= e(ucwords(str_replace('_', ' ', $r['mode']))) ?><?= $r['interviewer'] ? ' · ' . e($r['interviewer']) : '' ?></div>
              </div>
              <div class="d-flex align-items-center gap-1">
                <span class="badge-status <?= $resBadge ?>"><?= e(ucfirst($r['result'])) ?></span>
                <form method="post" action="<?= url('recruitment/' . $c['id'] . '/interviews/' . $r['id'] . '/delete') ?>">
                  <?= csrf_field() ?>
                  <button class="btn-icon danger" title="Remove" data-confirm="Remove this interview round?" data-confirm-title="Remove Round"><i class="fa-solid fa-trash"></i></button>
                </form>
              </div>
            </div>
            <?php if ($scores): ?>
              <div class="round-scores">
                <span>Tech <b><?= $v($r['technical_score']) ?></b></span><span>Comm <b><?= $v($r['communication_score']) ?></b></span>
                <span>Logic <b><?= $v($r['problem_solving_score']) ?></b></span><span>Attitude <b><?= $v($r['attitude_score']) ?></b></span>
                <span class="avg">Avg <b><?= $avg ?>/5</b></span>
              </div>
            <?php endif; ?>
            <?php if ($r['feedback']): ?><p class="small mb-0 mt-1"><?= nl2br(e($r['feedback'])) ?></p><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($canConvert): ?>
      <div class="ep-card cp tone-green">
        <?= $head('fa-user-plus', 'Convert to Employee', 'Create employee record') ?>
        <div class="cp-body">
          <form method="post" action="<?= url('recruitment/' . $c['id'] . '/convert') ?>">
            <?= csrf_field() ?>
            <label class="form-label">Joining Date</label>
            <input type="text" name="joining_date" class="form-control datepicker mb-3" value="<?= e($c['joining_date'] ?: date('Y-m-d')) ?>">
            <?php if ((float) $c['offered_salary'] > 0): ?><p class="small text-muted mb-3">Salary <b><?= money($c['offered_salary']) ?> / month</b> will be set on the employee automatically.</p><?php endif; ?>
            <button class="btn btn-success w-100" data-confirm="Create an employee record from this application?" data-confirm-title="Convert to Employee">
              <i class="fa-solid fa-id-badge me-1"></i>Create Employee
            </button>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <div class="ep-card cp tone-red">
      <?= $head('fa-circle-info', 'Application Info', 'Submitted ' . format_date($c['created_at'], 'd M Y, h:i A')) ?>
      <div class="cp-body small text-muted">
        <?= $c['ip_address'] ? 'Submitted from IP <b>' . e($c['ip_address']) . '</b>' : '' ?>
        <form method="post" action="<?= url('recruitment/' . $c['id'] . '/delete') ?>" class="mt-2">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-outline-danger w-100" data-confirm="Delete this application permanently?" data-confirm-title="Delete Application"><i class="fa-solid fa-trash me-1"></i>Delete Application</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade crud-modal" id="selectModal" tabindex="-1"<?= isset($_GET['select']) ? ' data-auto-open' : '' ?>>
  <div class="modal-dialog modal-lg">
    <form method="post" action="<?= url('recruitment/' . $c['id'] . '/select') ?>" class="modal-content js-loading">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-indian-rupee-sign me-2"></i>Finalise Salary &amp; Select</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="select-summary">
          <div class="cand-avatar"><?= e(strtoupper(mb_substr($c['first_name'], 0, 1) . mb_substr($c['last_name'], 0, 1))) ?></div>
          <div class="min-w-0">
            <b><?= e($c['full_name']) ?></b>
            <small><?= e($c['position']) ?> · <?= e($c['application_no']) ?></small>
          </div>
          <div class="ms-auto text-end">
            <small>Expected</small>
            <b><?= $inr($c['expected_ctc']) ?></b>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label required">Final Monthly Salary (₹)</label>
            <input type="number" name="offered_salary" class="form-control form-control-lg js-salary-input" min="1" step="1" required value="<?= $suggestedSalary ? (int) $suggestedSalary : '' ?>" placeholder="e.g. 25000">
            <div class="form-text js-salary-words"></div>
          </div>
          <div class="col-md-6">
            <label class="form-label required">Joining Date</label>
            <input type="date" name="joining_date" class="form-control form-control-lg" required min="<?= date('Y-m-d') ?>" value="<?= e($c['joining_date'] ?: date('Y-m-d', strtotime('+7 days'))) ?>">
            <div class="form-text">Offer letter will mention this joining date.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Probation (months)</label>
            <input type="number" name="probation_months" class="form-control" min="0" max="12" value="<?= (int) ($c['probation_months'] ?? 3) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Accept Offer By</label>
            <input type="date" name="offer_valid_till" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= e($c['offer_valid_till'] ?: date('Y-m-d', strtotime('+3 days'))) ?>">
          </div>
          <div class="col-12">
            <label class="form-label">HR Remarks</label>
            <textarea name="hr_remarks" id="selectRemarks" class="form-control" rows="2" placeholder="Interview summary"><?= e($c['hr_remarks'] ?? '') ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-check">
              <input type="checkbox" name="open_offer" value="1" class="form-check-input" checked>
              <span class="form-check-label">Open the A4 offer letter after saving</span>
            </label>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-ink" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-brand"><i class="fa-solid fa-circle-check me-1"></i><?= $offerReady ? 'Update Offer' : 'Confirm Selection' ?></button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade crud-modal" id="roundModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="post" action="<?= url('recruitment/' . $c['id'] . '/interviews') ?>" class="modal-content js-loading">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-user-check me-2 text-warning"></i>Add Interview Round</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label required">Round</label>
            <select name="round" class="form-select" required>
              <?php foreach (Candidate::ROUNDS as $key => $label): ?><option value="<?= $key ?>"><?= e($label) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4"><label class="form-label">Date &amp; Time</label><input type="datetime-local" name="interview_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>"></div>
          <div class="col-md-4">
            <label class="form-label">Mode</label>
            <select name="mode" class="form-select"><option value="in_person">In Person</option><option value="video">Video Call</option><option value="phone">Phone</option></select>
          </div>
          <div class="col-md-6"><label class="form-label">Interviewer</label><input name="interviewer" class="form-control" maxlength="120" placeholder="e.g. Tech Lead name"></div>
          <div class="col-md-6">
            <label class="form-label">Result</label>
            <select name="result" class="form-select"><option value="pending">Pending</option><option value="pass">Pass</option><option value="fail">Fail</option><option value="hold">Hold</option></select>
          </div>
          <?php foreach (['technical_score' => 'Technical', 'communication_score' => 'Communication', 'problem_solving_score' => 'Problem Solving', 'attitude_score' => 'Attitude'] as $name => $label): ?>
            <div class="col-6 col-md-3">
              <label class="form-label"><?= $label ?> (1–5)</label>
              <select name="<?= $name ?>" class="form-select"><option value="">—</option><?php for ($i = 1; $i <= 5; $i++): ?><option><?= $i ?></option><?php endfor; ?></select>
            </div>
          <?php endforeach; ?>
          <div class="col-12"><label class="form-label">Feedback</label><textarea name="feedback" class="form-control" rows="3" placeholder="Strengths, gaps, recommendation"></textarea></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-ink" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-brand"><i class="fa-solid fa-check me-1"></i>Save Round</button>
      </div>
    </form>
  </div>
</div>
