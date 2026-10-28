<?php
$lh = $letterhead;
$e = $slip['employee'];
$bank = $e['employee_bank_details'] ?? [];
$name = preg_replace('/\s+/', ' ', trim($e['first_name'] . ' ' . ($e['middle_name'] ?? '') . ' ' . $e['last_name']));
$rupee = fn($n) => '₹ ' . inr($n);
$num = fn($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
$account = !empty($bank['account_number']) ? str_repeat('X', max(0, strlen($bank['account_number']) - 4)) . substr($bank['account_number'], -4) : '—';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e('Salary Slip · ' . $slip['label'] . ' · ' . $e['employee_code']) ?></title>
  <?php \App\Core\View::partial('partials/letterhead-css', ['letterhead' => $lh]); ?>
  <style>
    body { font-size: 11.5px; }
    .ps-title { text-align: center; margin: 2mm 0 5mm; }
    .ps-title b { display: block; font-size: 15px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .ps-title span { display: inline-block; margin-top: 1.5mm; background: var(--soft); color: #9a5b00; border: 1px solid #f7cf8f; border-radius: 20px; padding: .8mm 4mm; font-weight: 800; font-size: 11px; }
    .ps-box { border: 1px solid var(--line); border-radius: 8px; overflow: hidden; margin-bottom: 5mm; }
    .ps-box h5 { margin: 0; padding: 2mm 4mm; background: var(--ink); color: #fff; font-size: 10.5px; letter-spacing: .08em; text-transform: uppercase; }
    .ps-info { display: grid; grid-template-columns: 1fr 1fr; }
    .ps-info div { display: flex; padding: 1.8mm 4mm; border-bottom: 1px solid #eee; }
    .ps-info div:nth-child(odd) { border-right: 1px solid #eee; }
    .ps-info small { width: 40%; color: #6b7280; font-weight: 600; font-size: 11px; }
    .ps-info b { flex: 1; font-weight: 700; }
    .ps-days { display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; }
    .ps-days div { padding: 2.5mm 1mm; border-right: 1px solid #eee; }
    .ps-days div:last-child { border-right: 0; background: var(--soft); }
    .ps-days small { display: block; color: #6b7280; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
    .ps-days b { display: block; font-size: 15px; font-weight: 800; margin-top: 1mm; }
    .ps-table { width: 100%; border-collapse: collapse; }
    .ps-table th { background: var(--soft); text-align: left; padding: 2.2mm 4mm; font-size: 10.5px; text-transform: uppercase; letter-spacing: .05em; border-bottom: 2px solid var(--brand); }
    .ps-table td { padding: 2.2mm 4mm; border-bottom: 1px solid #eee; vertical-align: top; }
    .ps-table .amt { text-align: right; font-weight: 700; white-space: nowrap; }
    .ps-table .sep { border-left: 1px solid var(--line); }
    .ps-table tfoot td { font-weight: 800; background: #fafafa; border-top: 1px solid var(--line); }
    .ps-net { display: flex; justify-content: space-between; align-items: center; gap: 6mm; background: linear-gradient(90deg, var(--ink), #3a363d); color: #fff; border-radius: 8px; padding: 4mm 5mm; border-left: 6px solid var(--brand); }
    .ps-net small { display: block; color: #f7cf8f; font-weight: 700; font-size: 10px; text-transform: uppercase; letter-spacing: .08em; }
    .ps-net b { font-size: 20px; font-weight: 800; }
    .ps-net .words { font-size: 11px; font-weight: 600; opacity: .9; text-align: right; max-width: 60%; }
    .ps-note { margin-top: 6mm; text-align: center; color: #6b7280; font-size: 10px; }
  </style>
</head>
<body>

<div class="toolbar">
  <span><b>Salary Slip</b> · <?= e($slip['label']) ?> · <?= e($e['employee_code']) ?></span>
  <div class="tb-actions">
    <span class="tb-pages" id="pageCount">Preparing A4…</span>
    <a class="tb-btn ghost" href="<?= e($backUrl) ?>">← Back</a>
    <button type="button" onclick="window.print()">Print / Save PDF</button>
  </div>
</div>

<template id="lhTpl"><?php \App\Core\View::partial('partials/letterhead-layer', ['letterhead' => $lh, 'footerNote' => 'Salary Slip']); ?></template>
<div id="pages" data-mode="<?= e($lh['mode']) ?>" data-top="<?= (int) $lh['top_mm'] ?>" data-bottom="<?= (int) $lh['bottom_mm'] ?>" data-side="<?= (int) $lh['side_mm'] ?>"></div>

<div class="flow" id="flow">
  <div class="ps-title"><b>Salary Slip</b><span>For the month of <?= e($slip['label']) ?></span></div>

  <div class="ps-box">
    <h5>Employee Details</h5>
    <div class="ps-info">
      <div><small>Employee Name</small><b><?= e($name) ?></b></div>
      <div><small>Employee Code</small><b><?= e($e['employee_code']) ?></b></div>
      <div><small>Designation</small><b><?= e($e['designation_name'] ?: '—') ?></b></div>
      <div><small>Department</small><b><?= e($e['department_name'] ?: '—') ?></b></div>
      <div><small>Date of Joining</small><b><?= $e['joining_date'] ? date('d-m-Y', strtotime($e['joining_date'])) : '—' ?></b></div>
      <div><small>PAN</small><b><?= e($e['pan_number'] ?: '—') ?></b></div>
      <div><small>Bank</small><b><?= e($bank['bank_name'] ?? '—') ?></b></div>
      <div><small>Account No.</small><b><?= e($account) ?></b></div>
    </div>
  </div>

  <div class="ps-box">
    <h5>Attendance Summary</h5>
    <div class="ps-days">
      <div><small>Month Days</small><b><?= $slip['days_in_month'] ?></b></div>
      <div><small>Working Days</small><b><?= $slip['working_days'] ?></b></div>
      <div><small>Week Off / Holiday</small><b><?= $slip['offs'] ?></b></div>
      <div><small>Paid Leave</small><b><?= $num($slip['paid_leave']) ?></b></div>
      <div><small>Absent</small><b><?= $num($slip['absent']) ?></b></div>
      <div><small>Loss of Pay</small><b><?= $num($slip['lop']) ?></b></div>
      <div><small>Paid Days</small><b><?= $num($slip['paid_days']) ?></b></div>
    </div>
  </div>

  <div class="ps-box">
    <table class="ps-table">
      <thead><tr><th>Earnings</th><th class="amt">Amount</th><th class="sep">Deductions</th><th class="amt">Amount</th></tr></thead>
      <tbody>
        <tr>
          <td>Monthly Salary (Fixed)</td><td class="amt"><?= $rupee($slip['monthly']) ?></td>
          <td class="sep">Loss of Pay (<?= $num($slip['lop']) ?> day<?= $slip['lop'] == 1 ? '' : 's' ?>)<?php if ($slip['pre_join']): ?><br><small style="color:#6b7280">incl. <?= $slip['pre_join'] ?> day(s) before joining</small><?php endif; ?></td>
          <td class="amt"><?= $rupee($slip['lop_amount']) ?></td>
        </tr>
        <tr>
          <td></td><td class="amt"></td>
          <td class="sep">Professional Tax (PT)</td>
          <td class="amt"><?= $rupee($slip['professional_tax'] ?? 0) ?></td>
        </tr>
      </tbody>
      <tfoot><tr><td>Gross Earnings</td><td class="amt"><?= $rupee($slip['monthly']) ?></td><td class="sep">Total Deductions</td><td class="amt"><?= $rupee($slip['total_deduction'] ?? $slip['lop_amount']) ?></td></tr></tfoot>
    </table>
  </div>

  <div class="ps-net">
    <div><small>Net Pay</small><b><?= $rupee($slip['net']) ?></b></div>
    <div class="words"><?= e(amount_in_words($slip['net'])) ?></div>
  </div>

  <p class="ps-note">This is a computer-generated salary slip and does not require a signature.</p>
</div>

<script src="<?= asset('assets/js/paged.js') ?>?v=<?= @filemtime(BASE_PATH . '/public/assets/js/paged.js') ?>"></script>
<?php if (isset($_GET['print'])): ?><script>document.addEventListener('paged:ready', () => setTimeout(() => window.print(), 300));</script><?php endif; ?>
</body>
</html>
