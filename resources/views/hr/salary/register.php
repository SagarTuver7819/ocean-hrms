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

<div class="filter-bar">
  <form class="row g-2 align-items-end" method="get" action="<?= url('salary/register') ?>">
    <div class="col-auto">
      <label class="form-label">Month</label>
      <div class="d-flex gap-1 align-items-center">
        <a href="<?= $q($prev, $includeInterns) ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-chevron-left"></i></a>
        <input type="month" name="month" class="form-control form-control-sm" value="<?= e($month) ?>" onchange="this.form.submit()">
        <a href="<?= $q($next, $includeInterns) ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-chevron-right"></i></a>
      </div>
    </div>
    <div class="col-auto">
      <label class="form-label d-block">&nbsp;</label>
      <div class="form-check form-switch mt-1">
        <input class="form-check-input" type="checkbox" role="switch" name="include_interns" value="1" id="incIntern"
          <?= $includeInterns ? 'checked' : '' ?> onchange="this.form.submit()">
        <label class="form-check-label fw-semibold" for="incIntern">Include Intern employees</label>
      </div>
    </div>
    <div class="col-auto">
      <label class="form-label d-block">&nbsp;</label>
      <a href="<?= e($pdfUrl) ?>" target="_blank" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-print me-1"></i>Print / PDF</a>
    </div>
    <div class="col-12">
      <small class="text-muted fw-semibold">
        As of <?= format_date($register['as_of']) ?> · Month days <?= (int) $register['days_in_month'] ?> ·
        Leave = Paid Leave only · Sick Leave unpaid (not in Gross) · Deduction = PT only · No UAN
      </small>
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
  <div class="panel-header">
    <h3>Salaried Persons (Fixed) · <?= e($register['label']) ?></h3>
    <small class="text-muted"><?= (int) $t['employees'] ?> employees</small>
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
            <th></th>
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
.sal-reg-table { font-size: 12.5px; }
.sal-reg-table th, .sal-reg-table td { white-space: nowrap; vertical-align: middle; border-color: #e7d3b0 !important; }
.sal-h-group th {
  text-align: center; font-size: 11px; letter-spacing: .04em; text-transform: uppercase;
  border-bottom: 0 !important; padding: 8px 10px !important;
  background: #fff4e5 !important; color: #8a5200 !important; font-weight: 800;
}
.sal-g-info, .sal-g-earn, .sal-g-gross, .sal-g-ded, .sal-g-net, .sal-g-bank {
  background: #fff4e5 !important; color: #8a5200 !important;
}
.sal-h-cols th { background: #fffaf2 !important; color: #8a5200 !important; font-size: 11px; font-weight: 800; }
.sal-reg-table tbody tr:nth-child(even) td { background: #fffcf7; }
.sal-row-nosalary td { background: #fafafa !important; }
.sal-v-salary, .sal-v-net { color: #15803d !important; font-weight: 800; }
.sal-v-gross { color: #211F23 !important; font-weight: 800; }
.sal-v-absent { color: #dc2626 !important; font-weight: 700; }
.sal-v-ded { color: #e11d48 !important; font-weight: 700; }
.sal-reg-foot th {
  background: #fff4e5 !important; border-top: 2px solid #F5A01B !important;
  font-weight: 800; color: #8a5200;
}
.sal-pt-edit { margin: 0; min-width: 72px; }
.sal-pt-edit .form-control {
  font-weight: 800; text-align: right; border-color: #fde7c2; background: #fffaf2; color: #8a5200;
  padding: 2px 6px; height: 28px; font-size: 12px;
}
</style>
