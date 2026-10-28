<?php
$num = fn($n) => number_format((float) $n, 1);
$t = $register['totals'];
$prev = date('Y-m', strtotime($month . '-01 -1 month'));
$next = date('Y-m', strtotime($month . '-01 +1 month'));
$q = static function (string $m, bool $interns, bool $pdf = false): string {
    $base = $pdf ? 'salary/register/pdf' : 'salary/register';
    return url($base . '?month=' . $m . ($interns ? '&include_interns=1' : ''));
};
$pdfUrl = $q($month, $includeInterns, true);
?>
<div class="page-head">
  <div>
    <h1>Monthly Salary Register</h1>
    <p><?= e($register['label']) ?> · <?= e($register['formula']) ?></p>
  </div>
  <div class="d-flex gap-2 flex-wrap align-items-center">
    <a href="<?= e($pdfUrl) ?>" target="_blank" class="btn btn-sm btn-brand"><i class="fa-solid fa-file-pdf me-1"></i>Download PDF</a>
    <a href="<?= url('salary') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-wallet me-1"></i>Employee Salary</a>
  </div>
</div>

<div class="sal-filter-card">
  <form method="get" action="<?= url('salary/register') ?>" class="sal-filter-form">
    <div class="sal-filter-main">
      <div class="sal-filter-field">
        <span class="sal-filter-label"><i class="fa-regular fa-calendar me-1"></i>Month</span>
        <div class="sal-month-nav">
          <a href="<?= $q($prev, $includeInterns) ?>" class="sal-month-btn" title="Previous month"><i class="fa-solid fa-chevron-left"></i></a>
          <input type="month" name="month" class="sal-month-input" value="<?= e($month) ?>" onchange="this.form.submit()">
          <a href="<?= $q($next, $includeInterns) ?>" class="sal-month-btn" title="Next month"><i class="fa-solid fa-chevron-right"></i></a>
        </div>
      </div>

      <div class="sal-filter-divider"></div>

      <label class="sal-intern-switch" for="incIntern">
        <input class="form-check-input" type="checkbox" role="switch" name="include_interns" value="1" id="incIntern"
          <?= $includeInterns ? 'checked' : '' ?> onchange="this.form.submit()">
        <span>
          <b>Include Interns</b>
          <small>Optional · salary register</small>
        </span>
      </label>

      <div class="sal-filter-divider"></div>

      <a href="<?= e($pdfUrl) ?>" target="_blank" class="sal-print-btn">
        <i class="fa-solid fa-print"></i>
        <span>Print / PDF</span>
      </a>
    </div>
    <div class="sal-filter-note">
      <i class="fa-solid fa-circle-info"></i>
      As of <?= format_date($register['as_of']) ?> · Month days <?= (int) $register['days_in_month'] ?> ·
      Leave = Paid Leave only · Sick Leave unpaid (not in Gross) · Deduction = PT only · No UAN
    </div>
  </form>
</div>

<div class="metric-row cols-6">
  <div class="metric-card metric-dark"><span class="m-ico"><i class="fa-solid fa-users"></i></span><div class="label">Employees</div><div class="value"><?= (int) $t['employees'] ?></div><div class="sub">Salary set: <?= (int) $t['with_salary'] ?></div></div>
  <div class="metric-card metric-orange"><span class="m-ico"><i class="fa-solid fa-file-invoice-dollar"></i></span><div class="label">Decided Total</div><div class="value" style="font-size:18px"><?= money($t['decided']) ?></div><div class="sub">fixed monthly</div></div>
  <div class="metric-card metric-sky"><span class="m-ico"><i class="fa-solid fa-calendar-day"></i></span><div class="label">Total Days</div><div class="value"><?= $num($t['total_days']) ?></div><div class="sub">P + Off + Hol + Leave</div></div>
  <div class="metric-card metric-teal"><span class="m-ico"><i class="fa-solid fa-coins"></i></span><div class="label">Gross</div><div class="value" style="font-size:18px"><?= money($t['gross']) ?></div><div class="sub">earned</div></div>
  <div class="metric-card metric-blue"><span class="m-ico"><i class="fa-solid fa-receipt"></i></span><div class="label">PT Total</div><div class="value" style="font-size:18px"><?= money($t['pt']) ?></div><div class="sub">only deduction</div></div>
  <div class="metric-card metric-green"><span class="m-ico"><i class="fa-solid fa-hand-holding-dollar"></i></span><div class="label">Net Payable</div><div class="value" style="font-size:18px"><?= money($t['net']) ?></div><div class="sub">Gross − PT</div></div>
</div>

<div class="panel table-card sal-reg-panel">
  <div class="panel-header sal-reg-titlebar">
    <h3>Salaried Persons (Fixed) · <?= e($register['label']) ?></h3>
    <small>Attendance-wise fixed salary · <?= (int) $t['employees'] ?> employees</small>
  </div>
  <div class="panel-body p-0">
    <div class="table-responsive">
      <table class="table table-hover sal-reg-table datatable dt-export align-middle mb-0">
        <thead>
          <tr class="sal-h-group">
            <th colspan="4" class="sal-g-info">Employee</th>
            <th colspan="6" class="sal-g-earn">Earning</th>
            <th colspan="2" class="sal-g-gross">Gross</th>
            <th colspan="2" class="sal-g-ded">Deduction</th>
            <th class="sal-g-net">Net</th>
            <th colspan="2" class="sal-g-bank">Bank</th>
            <th class="sal-g-info"></th>
          </tr>
          <tr class="sal-h-cols">
            <th class="sal-c-info">Emp Code</th>
            <th class="sal-c-info">Employee Name</th>
            <th class="sal-c-info">Desig.</th>
            <th class="sal-c-info">D.O.J.</th>
            <th class="sal-c-earn">Salary</th>
            <th class="sal-c-earn">P.Days</th>
            <th class="sal-c-earn">W.Off</th>
            <th class="sal-c-earn">Holiday</th>
            <th class="sal-c-earn">Leave</th>
            <th class="sal-c-earn">Absent</th>
            <th class="sal-c-gross">Total Days</th>
            <th class="sal-c-gross">Gross</th>
            <th class="sal-c-ded">P.T.</th>
            <th class="sal-c-ded">Tot. Ded.</th>
            <th class="sal-c-net">Net</th>
            <th class="sal-c-bank">A/C No.</th>
            <th class="sal-c-bank">IFSC</th>
            <th class="sal-c-info"></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($register['rows'] as $i => $r): ?>
          <tr class="<?= !$r['has_salary'] ? 'sal-row-nosalary' : '' ?>">
            <td class="fw-bold"><?= e($r['employee_code']) ?></td>
            <td>
              <b><?= e($r['name']) ?></b>
              <?php if ($r['is_intern']): ?><small class="d-block text-warning">Intern</small><?php endif; ?>
              <small class="d-block text-muted"><?= e($r['department_name']) ?></small>
            </td>
            <td><small><?= e($r['designation_name'] ?: '—') ?></small></td>
            <td data-order="<?= e($r['joining_date'] ?? '') ?>"><small><?= $r['joining_date'] ? date('d-m-Y', strtotime($r['joining_date'])) : '—' ?></small></td>
            <td class="text-end sal-v-salary"><?= $r['has_salary'] ? money($r['monthly_salary']) : '—' ?></td>
            <td class="text-end"><?= $num($r['present']) ?></td>
            <td class="text-end"><?= $num($r['week_off']) ?></td>
            <td class="text-end"><?= $num($r['holiday']) ?></td>
            <td class="text-end" title="Paid Leave"><?= $num($r['leave']) ?></td>
            <td class="text-end <?= $r['absent'] > 0 ? 'sal-v-absent' : '' ?>"><?= $num($r['absent']) ?></td>
            <td class="fw-bold text-end"><?= $num($r['total_days']) ?></td>
            <td class="text-end sal-v-gross"><?= $r['has_salary'] ? money($r['gross']) : '—' ?></td>
            <td class="text-end">
              <?php if ($r['has_salary']): ?>
                <form method="post" action="<?= url('salary/employee/' . $r['employee_id'] . '/pt') ?>" class="sal-pt-edit">
                  <?= csrf_field() ?>
                  <input type="hidden" name="month" value="<?= e($month) ?>">
                  <?php if ($includeInterns): ?><input type="hidden" name="include_interns" value="1"><?php endif; ?>
                  <input type="number" name="professional_tax" class="form-control form-control-sm" min="0" step="1"
                    value="<?= (int) $r['pt_configured'] ?>" title="Edit PT" onchange="this.form.requestSubmit()">
                </form>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td class="text-end sal-v-ded"><?= $r['has_salary'] ? money($r['professional_tax']) : '—' ?></td>
            <td class="text-end sal-v-net"><?= $r['has_salary'] ? money($r['net']) : '—' ?></td>
            <td><small class="font-monospace"><?= e($r['account_number'] ?: '—') ?></small></td>
            <td><small class="font-monospace"><?= e($r['ifsc_code'] ?: '—') ?></small></td>
            <td>
              <a href="<?= url('salary/employee/' . $r['employee_id']) ?>" class="btn-icon" title="Edit salary / PT"><i class="fa-solid fa-pen"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="sal-reg-foot">
            <th colspan="4">Total</th>
            <th class="text-end sal-v-salary"><?= money($t['decided']) ?></th>
            <th class="text-end"><?= $num($t['present']) ?></th>
            <th class="text-end"><?= $num($t['week_off']) ?></th>
            <th class="text-end"><?= $num($t['holiday']) ?></th>
            <th class="text-end"><?= $num($t['leave']) ?></th>
            <th class="text-end sal-v-absent"><?= $num($t['absent']) ?></th>
            <th class="text-end"><?= $num($t['total_days']) ?></th>
            <th class="text-end sal-v-gross"><?= money($t['gross']) ?></th>
            <th></th>
            <th class="text-end sal-v-ded"><?= money($t['pt']) ?></th>
            <th class="text-end sal-v-net"><?= money($t['net']) ?></th>
            <th colspan="3"></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<style>
/* —— Filter bar —— */
.sal-filter-card {
  background: #fff; border: 1px solid var(--line); border-radius: 16px;
  box-shadow: var(--shadow-sm); padding: 14px 16px; margin-bottom: 16px;
}
.sal-filter-main {
  display: flex; flex-wrap: wrap; align-items: center; gap: 14px 18px;
}
.sal-filter-field { display: flex; flex-direction: column; gap: 6px; }
.sal-filter-label {
  font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: var(--muted);
}
.sal-month-nav {
  display: inline-flex; align-items: center; gap: 0;
  border: 1.5px solid #f5c56a; border-radius: 12px; overflow: hidden; background: #fffaf2;
  box-shadow: 0 2px 8px rgba(245,160,27,.08);
}
.sal-month-btn {
  width: 38px; height: 40px; display: grid; place-items: center;
  color: #8a5200; text-decoration: none; background: #fff7ea; border: 0;
  transition: background .15s;
}
.sal-month-btn:hover { background: #fde7c2; color: #8a5200; }
.sal-month-input {
  border: 0; background: transparent; height: 40px; min-width: 160px;
  padding: 0 12px; font-weight: 800; color: var(--ink); outline: none;
}
.sal-filter-divider { width: 1px; height: 36px; background: #e5e7eb; }
.sal-intern-switch {
  display: inline-flex; align-items: center; gap: 10px; margin: 0; cursor: pointer;
  padding: 8px 12px; border-radius: 12px; border: 1px solid var(--line); background: #fafafa;
}
.sal-intern-switch:hover { border-color: #f5c56a; background: #fffaf2; }
.sal-intern-switch .form-check-input {
  width: 2.4em; height: 1.3em; margin: 0; cursor: pointer;
  background-color: #cbd5e1; border-color: #94a3b8;
}
.sal-intern-switch .form-check-input:checked {
  background-color: var(--brand); border-color: var(--brand);
}
.sal-intern-switch b { display: block; font-size: 13px; color: var(--ink); line-height: 1.2; }
.sal-intern-switch small { display: block; font-size: 11px; color: var(--muted); font-weight: 600; }
.sal-print-btn {
  display: inline-flex; align-items: center; gap: 8px;
  height: 40px; padding: 0 16px; border-radius: 12px;
  border: 1.5px solid var(--line); background: #fff; color: var(--ink);
  font-weight: 800; font-size: 13px; text-decoration: none;
  transition: .15s;
}
.sal-print-btn:hover { border-color: var(--brand); color: var(--brand-dark); background: #fff7ea; }
.sal-print-btn i { color: var(--brand); }
.sal-filter-note {
  margin-top: 12px; padding-top: 10px; border-top: 1px dashed #e5e7eb;
  font-size: 12px; font-weight: 600; color: var(--muted); line-height: 1.45;
}
.sal-filter-note i { color: var(--brand); margin-right: 4px; }
@media (max-width: 768px) {
  .sal-filter-divider { display: none; }
  .sal-month-input { min-width: 130px; }
}

.sal-reg-titlebar {
  background: #4b5563 !important; color: #fff !important;
  border-bottom: 0 !important; padding: 12px 16px !important;
}
.sal-reg-titlebar h3 { color: #fff !important; margin: 0; font-size: 14px; }
.sal-reg-titlebar small { color: #e5e7eb !important; font-weight: 600; }

/* —— Table color code (accounts-style) —— */
.sal-reg-table { font-size: 12.5px; }
.sal-reg-table th, .sal-reg-table td { white-space: nowrap; vertical-align: middle; border-color: #d1d5db !important; }
.sal-h-group th {
  text-align: center; font-size: 11px; letter-spacing: .05em; text-transform: uppercase;
  border-bottom: 0 !important; padding: 8px 10px !important; font-weight: 800;
}
.sal-g-info { background: #e5e7eb !important; color: #374151 !important; }
.sal-g-earn { background: #86efac !important; color: #14532d !important; }
.sal-g-gross { background: #bbf7d0 !important; color: #166534 !important; }
.sal-g-ded { background: #fde68a !important; color: #92400e !important; }
.sal-g-net { background: #fbbf24 !important; color: #78350f !important; }
.sal-g-bank { background: #e5e7eb !important; color: #374151 !important; }

.sal-h-cols th { font-size: 11px; font-weight: 800; }
.sal-c-info { background: #f3f4f6 !important; color: #374151 !important; }
.sal-c-earn { background: #dcfce7 !important; color: #166534 !important; }
.sal-c-gross { background: #ecfdf5 !important; color: #065f46 !important; }
.sal-c-ded { background: #fef9c3 !important; color: #854d0e !important; }
.sal-c-net { background: #fef3c7 !important; color: #92400e !important; }
.sal-c-bank { background: #f3f4f6 !important; color: #374151 !important; }

.sal-reg-table tbody tr:nth-child(even) td { background: #fafafa; }
.sal-row-nosalary td { background: #f8fafc !important; }
.sal-v-salary, .sal-v-net { color: #15803d !important; font-weight: 800; }
.sal-v-gross { color: #211F23 !important; font-weight: 800; }
.sal-v-absent { color: #dc2626 !important; font-weight: 700; }
.sal-v-ded { color: #e11d48 !important; font-weight: 700; }
.sal-reg-foot th {
  background: #fff7ea !important; border-top: 2px solid #F5A01B !important;
  font-weight: 800; color: #211F23;
}
.sal-pt-edit { margin: 0; min-width: 72px; }
.sal-pt-edit .form-control {
  font-weight: 800; text-align: right; border-color: #fde68a; background: #fffbeb; color: #92400e;
  padding: 2px 6px; height: 28px; font-size: 12px;
}
</style>
