<?php
$e = $employee;
$salary = $salary ?? null;
$bank = $e['bank_details'] ?? null;
$ec = $e['emergency_contacts'] ?? null;
$dash = fn($v) => ($v === null || $v === '') ? '—' : $v;
$fullName = preg_replace('/\s+/', ' ', trim(($e['first_name'] ?? '') . ' ' . ($e['middle_name'] ?? '') . ' ' . ($e['last_name'] ?? '')));
$address = function (string $prefix) use ($e): string {
    $parts = array_filter([
        $e[$prefix . '_address'] ?? '', $e[$prefix . '_city'] ?? '', $e[$prefix . '_state'] ?? '', $e[$prefix . '_pincode'] ?? '',
    ], fn($p) => trim((string) $p) !== '');
    return $parts ? implode(', ', $parts) : '—';
};
$shiftTime = !empty($e['shift_start'])
    ? date('h:i A', strtotime($e['shift_start'])) . ' – ' . date('h:i A', strtotime($e['shift_end']))
    : '—';
$row = function (string $label, $value, bool $highlight = false) use ($dash): string {
    return '<div class="ep-row' . ($highlight ? ' is-key' : '') . '"><span>' . e($label) . '</span><b>' . e((string) $dash($value)) . '</b></div>';
};
?>
<div class="row g-3">
  <div class="col-xl-6">
    <div class="ep-card tone-orange">
      <div class="ep-card-head">
        <span class="ep-card-ico"><i class="fa-solid fa-id-card"></i></span>
        <div><h3>Personal Information</h3><small>Identity &amp; contact details</small></div>
      </div>
      <div class="ep-card-body">
        <?= $row('Employee Full Name', $fullName, true) ?>
        <?= $row('Date of Birth', format_date($e['date_of_birth'] ?? null, 'd-m-Y')) ?>
        <?= $row('Gender', ucfirst((string) ($e['gender'] ?? ''))) ?>
        <?= $row('Blood Group', $e['blood_group'] ?? null) ?>
        <?= $row('Marital Status', ucfirst((string) ($e['marital_status'] ?? ''))) ?>
        <?= $row('Nationality', $e['nationality'] ?? null) ?>

        <div class="ep-sec">Contact</div>
        <?= $row('Mobile Number', $e['mobile'] ?? null) ?>
        <?= $row('Personal Email', $e['personal_email'] ?? null) ?>
        <?= $row('Official Email', $e['official_email'] ?? null) ?>
        <?= $row('Emergency Contact Name', $ec['contact_name'] ?? null) ?>
        <?= $row('Emergency Contact Relation', $ec['relationship'] ?? null) ?>
        <?= $row('Emergency Mobile', $ec['mobile'] ?? null) ?>

        <div class="ep-sec">Documents</div>
        <?= $row('Aadhaar Number', $e['aadhaar_number'] ?? null) ?>
        <?= $row('PAN Number', $e['pan_number'] ?? null) ?>

        <div class="ep-sec">Address</div>
        <?= $row('Present Address', $address('current')) ?>
        <?= $row('Permanent Address', !empty($e['same_as_current']) ? $address('current') : $address('permanent')) ?>
      </div>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="ep-card tone-dark">
      <div class="ep-card-head">
        <span class="ep-card-ico"><i class="fa-solid fa-briefcase"></i></span>
        <div><h3>Job Information</h3><small>Role, shift &amp; payroll basics</small></div>
      </div>
      <div class="ep-card-body">
        <?= $row('Department', $e['department_name'] ?? null) ?>
        <?= $row('Designation', $e['designation_name'] ?? null, true) ?>
        <?= $row('Employment Type', ucwords(str_replace('_', ' ', (string) ($e['employment_type'] ?? '')))) ?>
        <?= $row('Team', $e['team_name'] ?? null) ?>
        <?= $row('Branch', $e['branch_name'] ?? null) ?>
        <?= $row('Work Location', $e['work_location'] ?? null) ?>
        <?= $row('Reporting Head', $e['manager_name'] ?? null) ?>

        <div class="ep-sec">Joining &amp; Status</div>
        <?= $row('Date of Joining', format_date($e['joining_date'] ?? null, 'd-m-Y')) ?>
        <?= $row('Probation Period', !empty($e['probation_period_days']) ? $e['probation_period_days'] . ' days' : null) ?>
        <?= $row('Confirmation Date', format_date($e['confirmation_date'] ?? null, 'd-m-Y')) ?>
        <?= $row('Exit Date', format_date($e['exit_date'] ?? null, 'd-m-Y')) ?>
        <?= $row('Notice Period', !empty($e['notice_period_days']) ? $e['notice_period_days'] . ' days' : null) ?>
        <div class="ep-row"><span>Status</span><b><span class="badge-status badge-<?= e($e['status']) ?>"><?= e(str_replace('_', ' ', $e['status'])) ?></span></b></div>

        <div class="ep-sec">Shift</div>
        <div class="ep-row"><span>Shift Type</span><b><?php if (!empty($e['shift_name'])): ?><span class="ep-tag"><?= e($e['shift_name']) ?></span><?php else: ?>—<?php endif; ?></b></div>
        <?= $row('Shift Time', $shiftTime) ?>

        <?php if (!empty($showSalary)): ?>
        <div class="ep-sec">Salary</div>
        <?= $row('Monthly Salary', $salary ? money($salary['monthly_salary']) : null, true) ?>
        <?= $row('Annual', $salary ? money($salary['monthly_salary'] * 12) : null) ?>
        <?= $row('Effective From', $salary ? format_date($salary['effective_from']) : null) ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="ep-card tone-orange">
      <div class="ep-card-head">
        <span class="ep-card-ico"><i class="fa-solid fa-building-columns"></i></span>
        <div><h3>Bank Information</h3><small>Salary account details</small></div>
      </div>
      <div class="ep-card-body">
        <?= $row('Bank Name', $bank['bank_name'] ?? null) ?>
        <?= $row('Account Holder', $bank['account_holder_name'] ?? null) ?>
        <?= $row('Account Number', $bank['account_number'] ?? null, true) ?>
        <?= $row('IFSC Code', $bank['ifsc_code'] ?? null) ?>
        <?= $row('Branch', $bank['branch'] ?? null) ?>
        <?= $row('Payment Mode', ucwords(str_replace('_', ' ', (string) ($bank['payment_mode'] ?? '')))) ?>
      </div>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="ep-card tone-dark">
      <div class="ep-card-head">
        <span class="ep-card-ico"><i class="fa-solid fa-key"></i></span>
        <div><h3>Login Details</h3><small>Employee portal access</small></div>
      </div>
      <div class="ep-card-body">
        <?= $row('Employee Login', $e['login_username'] ?? 'Not created') ?>
        <?php if (!empty($e['login_username'])): ?>
          <?= $row('Login Status', (int) $e['login_status'] ? 'Active' : 'Disabled') ?>
          <?= $row('Last Login', $e['last_login_at'] ? date('d-m-Y h:i A', strtotime($e['last_login_at'])) : 'Never logged in') ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
