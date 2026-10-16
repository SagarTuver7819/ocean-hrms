<?php
$lh = $letterhead;
$e = $employee;
$bank = $e['bank_details'] ?? [];
$ec = $e['emergency_contacts'] ?? [];
$salary = $salary ?? null;
$name = preg_replace('/\s+/', ' ', trim(($e['first_name'] ?? '') . ' ' . ($e['middle_name'] ?? '') . ' ' . ($e['last_name'] ?? '')));
$d = fn(?string $date) => $date ? date('d-m-Y', strtotime($date)) : '—';
$v = fn($x) => ($x === null || $x === '') ? '—' : (string) $x;
$addr = function (string $prefix) use ($e, $v): string {
    $parts = array_filter([
        $e[$prefix . '_address'] ?? '', $e[$prefix . '_city'] ?? '', $e[$prefix . '_state'] ?? '',
        $e[$prefix . '_country'] ?? '', $e[$prefix . '_pincode'] ?? '',
    ], fn($p) => trim((string) $p) !== '');
    return $parts ? implode(', ', $parts) : '—';
};
$shiftTime = !empty($e['shift_start'])
    ? date('h:i A', strtotime($e['shift_start'])) . ' – ' . date('h:i A', strtotime($e['shift_end']))
    : '—';
$cell = fn(string $label, $value) => '<div class="ef-cell"><span>' . e($label) . '</span><b>' . e($v($value)) . '</b></div>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employment Details · <?= e($e['employee_code']) ?></title>
  <?php \App\Core\View::partial('partials/letterhead-css', ['letterhead' => $lh]); ?>
  <style>
    body { font-size: 11px; }
    .ef-head { display: flex; gap: 5mm; align-items: flex-start; margin-bottom: 4mm; padding-bottom: 3mm; border-bottom: 2px solid var(--brand); }
    .ef-head .co { flex: 1; }
    .ef-head .co b { display: block; font-size: 16px; font-weight: 800; }
    .ef-head .co small { color: #6b7280; font-weight: 600; }
    .ef-photo { width: 28mm; height: 34mm; border: 1.5px solid var(--line); border-radius: 4px; overflow: hidden; background: #f8fafc; flex-shrink: 0; display: grid; place-items: center; }
    .ef-photo img { width: 100%; height: 100%; object-fit: cover; }
    .ef-photo span { color: #9ca3af; font-size: 9px; font-weight: 700; text-align: center; padding: 2mm; }
    .ef-title { text-align: center; margin: 2mm 0 4mm; }
    .ef-title b { display: block; font-size: 14px; letter-spacing: .1em; text-transform: uppercase; font-weight: 800; }
    .ef-title span { display: inline-block; margin-top: 1.5mm; background: var(--soft); color: #9a5b00; border: 1px solid #f7cf8f; border-radius: 20px; padding: .6mm 3.5mm; font-weight: 800; font-size: 10.5px; }
    .ef-meta { display: flex; justify-content: space-between; font-weight: 700; margin-bottom: 3mm; font-size: 11px; }
    .ef-sec { border: 1px solid var(--line); border-radius: 6px; overflow: hidden; margin-bottom: 3.5mm; page-break-inside: avoid; }
    .ef-sec h5 { margin: 0; padding: 1.8mm 3.5mm; background: var(--ink); color: #fff; font-size: 10px; letter-spacing: .08em; text-transform: uppercase; }
    .ef-grid { display: grid; grid-template-columns: 1fr 1fr; }
    .ef-grid.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
    .ef-cell { padding: 1.8mm 3.5mm; border-bottom: 1px solid #eee; border-right: 1px solid #eee; min-height: 9mm; }
    .ef-cell:nth-child(2n) { border-right: 0; }
    .ef-grid.cols-3 .ef-cell:nth-child(2n) { border-right: 1px solid #eee; }
    .ef-grid.cols-3 .ef-cell:nth-child(3n) { border-right: 0; }
    .ef-cell.full { grid-column: 1 / -1; border-right: 0; }
    .ef-cell span { display: block; color: #6b7280; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; margin-bottom: .5mm; }
    .ef-cell b { display: block; font-size: 11.5px; font-weight: 700; color: var(--ink); word-break: break-word; }
    .ef-sign { display: flex; justify-content: space-between; gap: 8mm; margin-top: 8mm; page-break-inside: avoid; }
    .ef-sign .blk { flex: 1; text-align: center; }
    .ef-sign .blk .line { border-top: 1px solid var(--ink); margin-top: 14mm; padding-top: 1.5mm; font-weight: 800; font-size: 10.5px; }
    .ef-note { margin-top: 4mm; text-align: center; color: #6b7280; font-size: 9.5px; }
  </style>
</head>
<body>

<div class="toolbar">
  <span><b>Employment Details</b> · <?= e($e['employee_code']) ?> · <?= e($name) ?></span>
  <div class="tb-actions">
    <span class="tb-pages" id="pageCount">Preparing A4…</span>
    <a class="tb-btn ghost" href="<?= e($backUrl) ?>">← Back</a>
    <button type="button" onclick="window.print()">Print / Save PDF</button>
  </div>
</div>

<template id="lhTpl"><?php \App\Core\View::partial('partials/letterhead-layer', ['letterhead' => $lh, 'footerNote' => 'Employment Details']); ?></template>
<div id="pages" data-mode="<?= e($lh['mode']) ?>" data-top="<?= (int) $lh['top_mm'] ?>" data-bottom="<?= (int) $lh['bottom_mm'] ?>" data-side="<?= (int) $lh['side_mm'] ?>"></div>

<div class="flow" id="flow">
  <div class="ef-head">
    <div class="co">
      <b><?= e($lh['company']) ?></b>
      <small><?= e($lh['tagline'] ?: 'Employee Master Record') ?></small>
      <?php if ($lh['address']): ?><small style="display:block;margin-top:1mm"><?= e($lh['address']) ?></small><?php endif; ?>
    </div>
    <div class="ef-photo">
      <?php if (!empty($e['profile_photo'])): ?>
        <img src="<?= asset($e['profile_photo']) ?>" alt="">
      <?php else: ?>
        <span>PHOTO</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="ef-title">
    <b>Employment Details Form</b>
    <span><?= e($e['employee_code']) ?> · <?= e(ucwords(str_replace('_', ' ', (string) $e['status']))) ?></span>
  </div>
  <div class="ef-meta">
    <span>Generated: <?= date('d-m-Y') ?></span>
    <span>Joining: <?= $d($e['joining_date'] ?? null) ?></span>
  </div>

  <div class="ef-sec">
    <h5>1. Personal Information</h5>
    <div class="ef-grid">
      <?= $cell('Full Name', $name) ?>
      <?= $cell('Employee Code', $e['employee_code'] ?? null) ?>
      <?= $cell('Date of Birth', $d($e['date_of_birth'] ?? null)) ?>
      <?= $cell('Gender', ucfirst((string) ($e['gender'] ?? ''))) ?>
      <?= $cell('Blood Group', $e['blood_group'] ?? null) ?>
      <?= $cell('Marital Status', ucfirst((string) ($e['marital_status'] ?? ''))) ?>
      <?= $cell('Nationality', $e['nationality'] ?? null) ?>
      <?= $cell('Aadhaar Number', $e['aadhaar_number'] ?? null) ?>
      <?= $cell('PAN Number', $e['pan_number'] ?? null) ?>
      <div class="ef-cell"><span>Mobile</span><b><?= e($v($e['mobile'] ?? null)) ?></b></div>
    </div>
  </div>

  <div class="ef-sec">
    <h5>2. Contact &amp; Address</h5>
    <div class="ef-grid">
      <?= $cell('Personal Email', $e['personal_email'] ?? null) ?>
      <?= $cell('Official Email', $e['official_email'] ?? null) ?>
      <div class="ef-cell full"><?= '<span>Present Address</span><b>' . e($addr('current')) . '</b>' ?></div>
      <div class="ef-cell full"><?= '<span>Permanent Address</span><b>' . e(!empty($e['same_as_current']) ? $addr('current') : $addr('permanent')) . '</b>' ?></div>
    </div>
  </div>

  <div class="ef-sec">
    <h5>3. Job Information</h5>
    <div class="ef-grid">
      <?= $cell('Department', $e['department_name'] ?? null) ?>
      <?= $cell('Designation', $e['designation_name'] ?? null) ?>
      <?= $cell('Employment Type', ucwords(str_replace('_', ' ', (string) ($e['employment_type'] ?? '')))) ?>
      <?= $cell('Team', $e['team_name'] ?? null) ?>
      <?= $cell('Branch', $e['branch_name'] ?? null) ?>
      <?= $cell('Work Location', $e['work_location'] ?? null) ?>
      <?= $cell('Reporting Head', $e['manager_name'] ?? null) ?>
      <?= $cell('Date of Joining', $d($e['joining_date'] ?? null)) ?>
      <?= $cell('Probation', !empty($e['probation_period_days']) ? $e['probation_period_days'] . ' days' : null) ?>
      <?= $cell('Confirmation Date', $d($e['confirmation_date'] ?? null)) ?>
      <?= $cell('Notice Period', !empty($e['notice_period_days']) ? $e['notice_period_days'] . ' days' : null) ?>
      <?= $cell('Shift', trim(($e['shift_name'] ?? '') . ($shiftTime !== '—' ? ' · ' . $shiftTime : '')) ?: null) ?>
      <?php if ($salary): ?>
        <?= $cell('Monthly Salary', '₹ ' . inr($salary['monthly_salary'])) ?>
        <?= $cell('Effective From', $d($salary['effective_from'] ?? null)) ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="ef-sec">
    <h5>4. Bank Information</h5>
    <div class="ef-grid">
      <?= $cell('Bank Name', $bank['bank_name'] ?? null) ?>
      <?= $cell('Account Holder', $bank['account_holder_name'] ?? null) ?>
      <?= $cell('Account Number', $bank['account_number'] ?? null) ?>
      <?= $cell('IFSC Code', $bank['ifsc_code'] ?? null) ?>
      <?= $cell('Branch', $bank['branch'] ?? null) ?>
      <?= $cell('Payment Mode', ucwords(str_replace('_', ' ', (string) ($bank['payment_mode'] ?? '')))) ?>
    </div>
  </div>

  <div class="ef-sec">
    <h5>5. Emergency Contact</h5>
    <div class="ef-grid cols-3">
      <?= $cell('Contact Name', $ec['contact_name'] ?? null) ?>
      <?= $cell('Relationship', $ec['relationship'] ?? null) ?>
      <?= $cell('Mobile', $ec['mobile'] ?? null) ?>
    </div>
  </div>

  <div class="ef-sec">
    <h5>6. Login</h5>
    <div class="ef-grid">
      <?= $cell('Username', $e['login_username'] ?? 'Not created') ?>
      <?= $cell('Login Status', !empty($e['login_username']) ? ((int) ($e['login_status'] ?? 0) ? 'Active' : 'Disabled') : '—') ?>
    </div>
  </div>

  <div class="ef-sign">
    <div class="blk"><div class="line">Employee Signature</div></div>
    <div class="blk"><div class="line">HR / Authorised Signatory</div></div>
  </div>
  <p class="ef-note">This form is generated from Ocean HRMS employee master data. Verify details before filing.</p>
</div>

<script src="<?= asset('assets/js/paged.js') ?>?v=<?= @filemtime(BASE_PATH . '/public/assets/js/paged.js') ?>"></script>
<?php if (isset($_GET['print'])): ?><script>document.addEventListener('paged:ready', () => setTimeout(() => window.print(), 300));</script><?php endif; ?>
</body>
</html>
