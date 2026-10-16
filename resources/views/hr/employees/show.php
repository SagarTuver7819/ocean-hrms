<?php
$e = $employee;
$tab = $tab ?? 'profile';
if (in_array($tab, ['overview', 'personal', 'employment'], true)) $tab = 'profile';
$creds = $_SESSION['temp_credentials'] ?? null;
unset($_SESSION['temp_credentials']);
$tabUrl = fn(string $k) => url('employees/' . $e['id'] . '?tab=' . $k);

$letterUrl = fn(string $type) => url('employees/' . $e['id'] . '/letters/' . $type);

ob_start(); ?>
  <div class="dropdown">
    <button type="button" class="btn btn-soft btn-sm dropdown-toggle" data-bs-toggle="dropdown"><i class="fa-solid fa-envelope-open-text me-1"></i>Letters</button>
    <ul class="dropdown-menu dropdown-menu-end letter-menu">
      <li><a class="dropdown-item" target="_blank" href="<?= $letterUrl('employment') ?>"><span class="lm-ico t-violet"><i class="fa-solid fa-id-card"></i></span><div><b>Employment Details Form</b><small>Full profile · print / PDF</small></div></a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item" target="_blank" href="<?= $letterUrl('offer') ?>"><span class="lm-ico t-green"><i class="fa-solid fa-file-signature"></i></span><div><b>Offer Letter</b><small>Monthly salary &amp; joining terms</small></div></a></li>
      <li><a class="dropdown-item" target="_blank" href="<?= $letterUrl('appointment') ?>"><span class="lm-ico t-orange"><i class="fa-solid fa-file-contract"></i></span><div><b>Appointment / Joining Letter</b><small>Terms &amp; fixed monthly salary</small></div></a></li>
      <li><a class="dropdown-item" target="_blank" href="<?= $letterUrl('experience') ?>"><span class="lm-ico t-blue"><i class="fa-solid fa-award"></i></span><div><b>Experience Letter</b><small>Joining → till date</small></div></a></li>
      <li><a class="dropdown-item" target="_blank" href="<?= $letterUrl('relieving') ?>"><span class="lm-ico t-red"><i class="fa-solid fa-door-open"></i></span><div><b>Relieving Letter</b><small>Last working day</small></div></a></li>
      <?php if (!empty($candidateId)): ?>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" target="_blank" href="<?= url('recruitment/' . $candidateId . '/print') ?>"><span class="lm-ico t-violet"><i class="fa-solid fa-file-lines"></i></span><div><b>Application Form</b><small>Original A4 application</small></div></a></li>
      <?php endif; ?>
    </ul>
  </div>
  <a href="<?= url('salary/employee/' . $e['id']) ?>" class="btn btn-soft btn-sm"><i class="fa-solid fa-indian-rupee-sign me-1"></i>Salary Details</a>
  <?php if (empty($e['user_id'])): ?>
    <form method="post" action="<?= url('employees/' . $e['id'] . '/login') ?>" class="d-inline">
      <?= csrf_field() ?>
      <button class="btn btn-outline-ink btn-sm" onclick="return confirm('Create employee login?')"><i class="fa-solid fa-key me-1"></i>Create Login</button>
    </form>
  <?php else: ?>
    <a href="<?= url('employees/logins') ?>" class="btn btn-soft btn-sm"><i class="fa-solid fa-key me-1"></i>Login Details</a>
  <?php endif; ?>
  <a href="<?= url('employees/' . $e['id'] . '/edit') ?>" class="btn btn-brand btn-sm"><i class="fa-solid fa-pen me-1"></i>Edit Employee</a>
<?php $actions = ob_get_clean();

$tabs = [
    'profile'    => ['Profile', 'fa-id-card', null],
    'documents'  => ['Documents', 'fa-folder-open', count($e['documents'] ?? [])],
    'leave'      => ['Leave History', 'fa-umbrella-beach', count($leaves)],
    'attendance' => ['Attendance', 'fa-fingerprint', null],
    'salary'     => ['Salary', 'fa-indian-rupee-sign', null],
    'timeline'   => ['Timeline', 'fa-timeline', count($e['timeline'] ?? [])],
];
?>

<div class="ep-topbar">
  <a href="<?= url('employees') ?>" class="btn btn-soft btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>Back to Employee List</a>
</div>

<?php if ($creds): ?>
<div class="alert alert-warning d-flex flex-wrap align-items-center gap-3">
  <span><i class="fa-solid fa-key me-1"></i><strong>Employee portal login</strong></span>
  <span>Username: <code><?= e($creds['employee_code']) ?></code></span>
  <span>Password: <code><?= e($creds['password']) ?></code></span>
  <a href="<?= url('employees/logins') ?>" class="ms-auto">All employee logins →</a>
</div>
<?php endif; ?>

<?php \App\Core\View::partial('partials/employee-hero', ['employee' => $e, 'salary' => $salary, 'actions' => $actions, 'showSalary' => true, 'photoAction' => can('employee.edit') ? url('employees/' . $e['id'] . '/photo') : null]); ?>

<nav class="ep-tabs" aria-label="Employee sections">
  <?php foreach ($tabs as $k => [$label, $icon, $count]): ?>
    <a href="<?= $tabUrl($k) ?>" class="ep-tab <?= $tab === $k ? 'active' : '' ?>">
      <span class="ep-tab-ico"><i class="fa-solid <?= $icon ?>"></i></span><?= $label ?>
      <?php if ($count !== null): ?><span class="ep-tab-count"><?= (int) $count ?></span><?php endif; ?>
    </a>
  <?php endforeach; ?>
</nav>

<?php if ($tab === 'profile'): ?>
  <?php \App\Core\View::partial('partials/employee-info', ['employee' => $e, 'salary' => $salary, 'showSalary' => true]); ?>

<?php elseif ($tab === 'salary'): ?>
  <div class="ep-card tone-orange">
    <div class="ep-card-head">
      <span class="ep-card-ico"><i class="fa-solid fa-indian-rupee-sign"></i></span>
      <div><h3>Salary</h3><small>Final monthly salary</small></div>
      <a href="<?= url('salary/employee/' . $e['id']) ?>" class="btn btn-soft btn-sm ms-auto"><?= $salary ? 'Manage' : 'Set Salary' ?></a>
    </div>
    <div class="ep-card-body">
      <?php if (!$salary): ?>
        <div class="dcard-empty">No salary assigned yet.</div>
      <?php else: ?>
        <div class="ep-stats">
          <div class="ep-stat t-orange"><small>Monthly Salary</small><b><?= money($salary['monthly_salary']) ?></b></div>
          <div class="ep-stat t-dark"><small>Annual</small><b><?= money($salary['monthly_salary'] * 12) ?></b></div>
          <div class="ep-stat t-green"><small>Effective From</small><b><?= format_date($salary['effective_from']) ?></b></div>
        </div>
        <p class="small text-muted mt-3 mb-0"><?= e(amount_in_words($salary['monthly_salary'])) ?> per month.</p>
      <?php endif; ?>
    </div>
  </div>

<?php elseif ($tab === 'attendance'): ?>
  <div class="ep-card tone-blue">
    <div class="ep-card-head">
      <span class="ep-card-ico"><i class="fa-solid fa-fingerprint"></i></span>
      <div><h3>Recent Attendance</h3><small>Last 10 punch records</small></div>
    </div>
    <div class="ep-card-body p-0">
      <div class="table-responsive">
        <table class="table ep-table mb-0">
          <thead><tr><th>Date</th><th>Punch In</th><th>Punch Out</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (!$attendance): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No attendance records.</td></tr>
          <?php else: foreach ($attendance as $a): ?>
            <tr>
              <td class="fw-bold"><?= format_date($a['attendance_date'], 'd-m-Y') ?></td>
              <td><?= $a['punch_in'] ? date('h:i A', strtotime($a['punch_in'])) : '—' ?></td>
              <td><?= $a['punch_out'] ? date('h:i A', strtotime($a['punch_out'])) : '—' ?></td>
              <td><span class="badge-status badge-<?= e($a['status']) ?>"><?= e(str_replace('_', ' ', $a['status'])) ?></span></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php elseif ($tab === 'leave'): ?>
  <div class="ep-card tone-orange">
    <div class="ep-card-head">
      <span class="ep-card-ico"><i class="fa-solid fa-umbrella-beach"></i></span>
      <div><h3>Leave History</h3><small>Last 10 leave requests</small></div>
    </div>
    <div class="ep-card-body p-0">
      <div class="table-responsive">
        <table class="table ep-table mb-0">
          <thead><tr><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (!$leaves): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No leave requests.</td></tr>
          <?php else: foreach ($leaves as $l): ?>
            <tr>
              <td><span class="dept-pill"><?= e($l['leave_type']) ?></span></td>
              <td><?= format_date($l['from_date'], 'd-m-Y') ?></td>
              <td><?= format_date($l['to_date'], 'd-m-Y') ?></td>
              <td class="fw-bold"><?= e($l['days']) ?></td>
              <td><span class="badge-status badge-<?= e($l['status']) ?>"><?= e($l['status']) ?></span></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php elseif ($tab === 'documents'): ?>
  <div class="ep-card tone-violet">
    <div class="ep-card-head">
      <span class="ep-card-ico"><i class="fa-solid fa-folder-open"></i></span>
      <div><h3>Documents</h3><small>Upload &amp; track employee files</small></div>
    </div>
    <div class="ep-card-body">
      <form class="row g-2 mb-3" method="post" action="<?= url('employees/' . $e['id'] . '/documents') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="col-md-3"><input class="form-control" name="document_name" placeholder="Document name" required></div>
        <div class="col-md-2">
          <select class="form-select" name="document_type">
            <?php foreach (['resume','aadhaar','pan','passport','education','experience','bank_proof','photograph','offer_letter','appointment_letter','other'] as $t): ?>
              <option value="<?= $t ?>"><?= ucwords(str_replace('_', ' ', $t)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2"><input type="date" class="form-control" name="expiry_date" title="Expiry date"></div>
        <div class="col-md-3"><input type="file" class="form-control" name="document" required></div>
        <div class="col-md-2"><button class="btn btn-brand w-100"><i class="fa-solid fa-upload me-1"></i>Upload</button></div>
      </form>
      <div class="table-responsive">
        <table class="table ep-table mb-0">
          <thead><tr><th>Name</th><th>Type</th><th>Uploaded</th><th>Expiry</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (empty($e['documents'])): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No documents uploaded.</td></tr>
          <?php else: foreach ($e['documents'] as $d): ?>
            <tr>
              <td class="fw-bold"><?= e($d['document_name']) ?></td>
              <td><span class="dept-pill"><?= e(ucwords(str_replace('_', ' ', $d['document_type']))) ?></span></td>
              <td><?= format_date($d['upload_date'], 'd-m-Y') ?></td>
              <td><?= format_date($d['expiry_date'] ?? null, 'd-m-Y') ?></td>
              <td><span class="badge-status badge-<?= e($d['status']) ?>"><?= e($d['status']) ?></span></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php elseif ($tab === 'timeline'): ?>
  <div class="ep-card tone-teal">
    <div class="ep-card-head">
      <span class="ep-card-ico"><i class="fa-solid fa-timeline"></i></span>
      <div><h3>Timeline</h3><small>Employment events</small></div>
    </div>
    <div class="ep-card-body">
      <?php if (empty($e['timeline'])): ?>
        <div class="dcard-empty">No timeline events yet.</div>
      <?php else: ?>
      <div class="timeline">
        <?php foreach ($e['timeline'] as $t): ?>
          <div class="timeline-item">
            <div class="date"><?= format_date($t['event_date'], 'd-m-Y') ?></div>
            <div class="title"><?= e($t['title']) ?></div>
            <?php if ($t['description']): ?><div class="text-muted small"><?= e($t['description']) ?></div><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
