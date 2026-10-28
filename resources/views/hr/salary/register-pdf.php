<?php
$num = fn($n) => number_format((float) $n, 1);
$amt = fn($n) => '₹ ' . number_format((float) $n, 2);
$t = $register['totals'];
$back = url('salary/register?month=' . $month . ($includeInterns ? '&include_interns=1' : ''));
$company = $company ?? 'Ocean Infotech';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Salary Register · <?= e($register['label']) ?> · <?= e($company) ?></title>
  <style>
    :root {
      --brand: #F5A01B;
      --ink: #211F23;
      --peach: #fff4e5;
      --peach-deep: #fde7c2;
      --brown: #8a5200;
      --green: #15803d;
      --red: #dc2626;
      --ded: #e11d48;
      --line: #e7d3b0;
    }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: "Segoe UI", system-ui, sans-serif; color: var(--ink); font-size: 10px; background: #f3f4f6; }
    .toolbar {
      position: sticky; top: 0; z-index: 20; display: flex; justify-content: space-between; align-items: center;
      gap: 12px; padding: 10px 16px; background: #211F23; color: #fff;
    }
    .toolbar b { font-size: 13px; }
    .tb-actions { display: flex; gap: 8px; }
    .tb-btn {
      border: 0; border-radius: 8px; padding: 7px 14px; font-weight: 700; font-size: 12px; cursor: pointer;
      text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
    }
    .tb-btn.primary { background: var(--brand); color: #211F23; }
    .tb-btn.ghost { background: rgba(255,255,255,.12); color: #fff; }
    .sheet {
      max-width: 1400px; margin: 16px auto; background: #fff; border-radius: 10px;
      box-shadow: 0 8px 24px rgba(0,0,0,.08); overflow: hidden; padding: 14px 16px 20px;
    }
    .head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 10px; border-bottom: 2px solid var(--brand); padding-bottom: 10px; }
    .head h1 { margin: 0; font-size: 16px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .head p { margin: 3px 0 0; color: #6b7280; font-weight: 600; }
    .meta { text-align: right; font-size: 10px; font-weight: 600; color: #6b7280; }
    .meta b { display: block; color: var(--ink); font-size: 12px; }

    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid var(--line); padding: 5px 6px; vertical-align: middle; }
    thead .g th {
      text-align: center; font-size: 9px; text-transform: uppercase; letter-spacing: .04em;
      font-weight: 800; background: var(--peach) !important; color: var(--brown) !important;
      border-color: var(--peach-deep); padding: 7px 6px;
    }
    thead .c th {
      background: #fffaf2 !important; color: var(--brown) !important;
      font-size: 9px; font-weight: 800; white-space: nowrap; border-color: var(--peach-deep);
    }
    tbody td { font-size: 9.5px; background: #fff; }
    tbody tr:nth-child(even) td { background: #fffcf7; }
    .r { text-align: right; white-space: nowrap; }
    .c { text-align: center; }
    .name { font-weight: 800; color: var(--ink); }
    .muted { color: #9ca3af; font-size: 8.5px; font-weight: 600; }
    .v-salary, .v-net { color: var(--green) !important; font-weight: 800; }
    .v-gross { color: var(--ink) !important; font-weight: 800; }
    .v-absent { color: var(--red) !important; font-weight: 700; }
    .v-ded { color: var(--ded) !important; font-weight: 700; }
    .pt-box {
      display: inline-block; min-width: 42px; padding: 2px 6px; border-radius: 6px;
      background: #fff7ea; border: 1px solid #fde7c2; color: var(--brown); font-weight: 800; text-align: right;
    }
    tfoot th {
      background: var(--peach) !important; color: var(--brown) !important; font-weight: 800;
      border-top: 2px solid var(--brand); border-color: var(--peach-deep);
    }
    .note { margin-top: 10px; color: #6b7280; font-size: 9px; font-weight: 600; }
    @page { size: A4 landscape; margin: 8mm; }
    @media print {
      body { background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .toolbar { display: none !important; }
      .sheet { margin: 0; box-shadow: none; border-radius: 0; max-width: none; padding: 0; }
      thead .g th, thead .c th, tfoot th, tbody tr:nth-child(even) td, .pt-box {
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
      }
    }
  </style>
</head>
<body>
<div class="toolbar">
  <b>Salary Register · <?= e($register['label']) ?></b>
  <div class="tb-actions">
    <a class="tb-btn ghost" href="<?= e($back) ?>">← Back</a>
    <button type="button" class="tb-btn primary" onclick="window.print()">Print / Save PDF</button>
  </div>
</div>

<div class="sheet">
  <div class="head">
    <div>
      <h1><?= e($company) ?></h1>
      <p>Monthly Salary Register · Salaried Persons (Fixed)</p>
      <p><?= e($register['formula']) ?></p>
    </div>
    <div class="meta">
      <b><?= e($register['label']) ?></b>
      As of <?= date('d-m-Y', strtotime($register['as_of'])) ?><br>
      Month days: <?= (int) $register['days_in_month'] ?>
      <?= $includeInterns ? ' · Interns included' : ' · Interns excluded' ?>
    </div>
  </div>

  <table>
    <thead>
      <tr class="g">
        <th colspan="4">Employee</th>
        <th colspan="6">Earning</th>
        <th colspan="2">Gross</th>
        <th colspan="2">Deduction</th>
        <th>Net</th>
        <th colspan="2">Bank</th>
      </tr>
      <tr class="c">
        <th>Emp Code</th>
        <th>Employee Name</th>
        <th>Desig.</th>
        <th>D.O.J.</th>
        <th>Salary</th>
        <th>P.Days</th>
        <th>W.Off</th>
        <th>Holiday</th>
        <th>Leave</th>
        <th>Absent</th>
        <th>Total Days</th>
        <th>Gross</th>
        <th>P.T.</th>
        <th>Tot. Ded.</th>
        <th>Net</th>
        <th>A/C No.</th>
        <th>IFSC</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($register['rows'] as $r): ?>
      <tr>
        <td><?= e($r['employee_code']) ?></td>
        <td>
          <span class="name"><?= e($r['name']) ?></span>
          <div class="muted"><?= e($r['department_name']) ?><?= $r['is_intern'] ? ' · Intern' : '' ?></div>
        </td>
        <td><?= e($r['designation_name'] ?: '—') ?></td>
        <td class="c"><?= $r['joining_date'] ? date('d-m-Y', strtotime($r['joining_date'])) : '—' ?></td>
        <td class="r <?= $r['has_salary'] ? 'v-salary' : '' ?>"><?= $r['has_salary'] ? $amt($r['monthly_salary']) : '—' ?></td>
        <td class="r"><?= $num($r['present']) ?></td>
        <td class="r"><?= $num($r['week_off']) ?></td>
        <td class="r"><?= $num($r['holiday']) ?></td>
        <td class="r"><?= $num($r['leave']) ?></td>
        <td class="r <?= $r['absent'] > 0 ? 'v-absent' : '' ?>"><?= $num($r['absent']) ?></td>
        <td class="r"><b><?= $num($r['total_days']) ?></b></td>
        <td class="r v-gross"><?= $r['has_salary'] ? $amt($r['gross']) : '—' ?></td>
        <td class="r"><?php if ($r['has_salary']): ?><span class="pt-box"><?= (int) $r['professional_tax'] ?></span><?php else: ?>—<?php endif; ?></td>
        <td class="r <?= $r['has_salary'] && $r['professional_tax'] > 0 ? 'v-ded' : '' ?>"><?= $r['has_salary'] ? $amt($r['professional_tax']) : '—' ?></td>
        <td class="r <?= $r['has_salary'] ? 'v-net' : '' ?>"><?= $r['has_salary'] ? $amt($r['net']) : '—' ?></td>
        <td><?= e($r['account_number'] ?: '—') ?></td>
        <td><?= e($r['ifsc_code'] ?: '—') ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$register['rows']): ?>
      <tr><td colspan="17" class="c" style="padding:16px;color:#6b7280">No employees in this register.</td></tr>
    <?php endif; ?>
    </tbody>
    <tfoot>
      <tr>
        <th colspan="4">Total</th>
        <th class="r v-salary"><?= $amt($t['decided']) ?></th>
        <th class="r"><?= $num($t['present']) ?></th>
        <th class="r"><?= $num($t['week_off']) ?></th>
        <th class="r"><?= $num($t['holiday']) ?></th>
        <th class="r"><?= $num($t['leave']) ?></th>
        <th class="r v-absent"><?= $num($t['absent']) ?></th>
        <th class="r"><?= $num($t['total_days']) ?></th>
        <th class="r v-gross"><?= $amt($t['gross']) ?></th>
        <th class="r"></th>
        <th class="r v-ded"><?= $amt($t['pt']) ?></th>
        <th class="r v-net"><?= $amt($t['net']) ?></th>
        <th colspan="2"></th>
      </tr>
    </tfoot>
  </table>

  <p class="note">
    Leave = Paid Leave only · Sick Leave unpaid (not in Gross) · Deduction = PT only ·
    Generated <?= date('d-m-Y h:i A') ?> · Ocean HRMS
  </p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  if (new URLSearchParams(location.search).get('autoprint') === '1') {
    setTimeout(function () { window.print(); }, 250);
  }
});
</script>
</body>
</html>
