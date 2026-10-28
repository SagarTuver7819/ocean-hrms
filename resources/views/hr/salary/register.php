<?php
$num = fn($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
$t = $register['totals'];
$prev = date('Y-m', strtotime($month . '-01 -1 month'));
$next = date('Y-m', strtotime($month . '-01 +1 month'));
$q = static function (string $m, bool $interns): string {
    return url('salary/register?month=' . $m . ($interns ? '&include_interns=1' : ''));
};
?>
<div class="page-head">
  <div>
    <h1>Monthly Salary Register</h1>
    <p><?= e($register['label']) ?> · Decided salary − LOP from attendance · Interns <?= $includeInterns ? 'included' : 'hidden' ?></p>
  </div>
  <div class="d-flex gap-2 flex-wrap align-items-center">
    <a href="<?= url('salary') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-wallet me-1"></i>Employee Salary</a>
    <a href="<?= url('attendance/summary?month=' . $month) ?>" class="btn btn-sm btn-brand"><i class="fa-solid fa-calendar-check me-1"></i>Attendance Summary</a>
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
      <button class="btn btn-sm btn-brand"><i class="fa-solid fa-filter me-1"></i>Apply</button>
    </div>
    <div class="col-12">
      <small class="text-muted fw-semibold">
        As of <?= format_date($register['as_of']) ?> ·
        Calendar days <?= (int) $register['days_in_month'] ?> ·
        Net = Decided − LOP − PT (default ₹200, editable) ·
        Week-offs / holidays / Paid Leave are paid.
      </small>
    </div>
  </form>
</div>

<div class="metric-row cols-6">
  <div class="metric-card metric-dark"><span class="m-ico"><i class="fa-solid fa-users"></i></span><div class="label">Employees</div><div class="value"><?= (int) $t['employees'] ?></div><div class="sub">Salary set: <?= (int) $t['with_salary'] ?><?= $includeInterns ? ' · Interns ' . (int) $t['interns'] : '' ?></div></div>
  <div class="metric-card metric-orange"><span class="m-ico"><i class="fa-solid fa-file-invoice-dollar"></i></span><div class="label">Decided Total</div><div class="value" style="font-size:18px"><?= money($t['decided']) ?></div><div class="sub">fixed monthly</div></div>
  <div class="metric-card metric-red"><span class="m-ico"><i class="fa-solid fa-user-xmark"></i></span><div class="label">LOP Amount</div><div class="value" style="font-size:18px"><?= money($t['lop_amount']) ?></div><div class="sub">LOP days <?= $num($t['lop']) ?></div></div>
  <div class="metric-card metric-blue"><span class="m-ico"><i class="fa-solid fa-receipt"></i></span><div class="label">PT Total</div><div class="value" style="font-size:18px"><?= money($t['pt'] ?? 0) ?></div><div class="sub">Professional Tax</div></div>
  <div class="metric-card metric-sky"><span class="m-ico"><i class="fa-solid fa-user-check"></i></span><div class="label">Present</div><div class="value"><?= $num($t['present']) ?></div><div class="sub">Half day: <?= $num($t['half_day']) ?></div></div>
  <div class="metric-card metric-green"><span class="m-ico"><i class="fa-solid fa-hand-holding-dollar"></i></span><div class="label">Net Payable</div><div class="value" style="font-size:18px"><?= money($t['net']) ?></div><div class="sub">after LOP + PT</div></div>
</div>

<div class="panel table-card">
  <div class="panel-header">
    <h3>Salary Register · <?= e($register['label']) ?></h3>
    <small class="text-muted">Export Excel / PDF from table tools</small>
  </div>
  <div class="panel-body">
    <div class="table-responsive">
      <table class="table table-hover datatable dt-export align-middle mb-0">
        <thead>
          <tr>
            <th>Sr.</th>
            <th>Employee</th>
            <th>Type</th>
            <th>Decided Salary</th>
            <th>Working Days</th>
            <th>Present</th>
            <th>Half Day</th>
            <th>Paid Leave</th>
            <th>Sick / Unpaid</th>
            <th>Absent</th>
            <th>LOP Days</th>
            <th>LOP ₹</th>
            <th>PT ₹</th>
            <th>Paid Days</th>
            <th>Net Payable</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($register['rows'] as $i => $r): ?>
          <tr class="<?= !$r['has_salary'] ? 'sal-row-nosalary' : '' ?>">
            <td><?= $i + 1 ?></td>
            <td class="att-emp">
              <b><?= e($r['name']) ?></b>
              <small><?= e($r['employee_code']) ?> · <?= e($r['department_name']) ?><?= $r['designation_name'] ? ' · ' . e($r['designation_name']) : '' ?></small>
            </td>
            <td>
              <?php if ($r['is_intern']): ?>
                <span class="badge-status badge-late">Intern</span>
              <?php else: ?>
                <span class="badge-status badge-present">Staff</span>
              <?php endif; ?>
            </td>
            <td class="fw-bold <?= $r['has_salary'] ? 'text-success' : 'text-muted' ?>">
              <?= $r['has_salary'] ? money($r['monthly_salary']) : 'Not set' ?>
            </td>
            <td><?= (int) $r['working_days'] ?></td>
            <td><?= $num($r['present']) ?></td>
            <td><?= $num($r['half_day']) ?></td>
            <td><?= $num($r['paid_leave']) ?></td>
            <td class="<?= $r['unpaid_leave'] > 0 ? 'text-danger fw-bold' : '' ?>"><?= $num($r['unpaid_leave']) ?></td>
            <td class="<?= $r['absent'] > 0 ? 'text-danger fw-bold' : '' ?>"><?= $num($r['absent']) ?></td>
            <td class="fw-bold"><?= $num($r['lop']) ?></td>
            <td class="<?= $r['lop_amount'] > 0 ? 'text-danger' : '' ?>"><?= $r['has_salary'] ? money($r['lop_amount']) : '—' ?></td>
            <td>
              <?php if ($r['has_salary']): ?>
                <form method="post" action="<?= url('salary/employee/' . $r['employee_id'] . '/pt') ?>" class="sal-pt-edit">
                  <?= csrf_field() ?>
                  <input type="hidden" name="month" value="<?= e($month) ?>">
                  <?php if ($includeInterns): ?><input type="hidden" name="include_interns" value="1"><?php endif; ?>
                  <input type="number" name="professional_tax" class="form-control form-control-sm" min="0" step="1"
                    value="<?= (int) $r['professional_tax'] ?>" title="Edit PT" onchange="this.form.requestSubmit()">
                </form>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
            <td><?= $num($r['paid_days']) ?></td>
            <td class="fw-bold <?= $r['has_salary'] ? 'text-success' : 'text-muted' ?>">
              <?= $r['has_salary'] ? money($r['net']) : '—' ?>
            </td>
            <td>
              <div class="action-btns">
                <a href="<?= url('salary/employee/' . $r['employee_id']) ?>" class="btn-icon" title="Set / edit salary &amp; PT"><i class="fa-solid fa-pen"></i></a>
                <a href="<?= url('attendance/report?employee_id=' . $r['employee_id'] . '&from=' . $month . '-01&to=' . date('Y-m-t', strtotime($month . '-01'))) ?>" class="btn-icon tone-green" title="Attendance"><i class="fa-solid fa-calendar-days"></i></a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="sal-reg-foot">
            <th colspan="3">Total</th>
            <th><?= money($t['decided']) ?></th>
            <th></th>
            <th><?= $num($t['present']) ?></th>
            <th><?= $num($t['half_day']) ?></th>
            <th><?= $num($t['paid_leave']) ?></th>
            <th><?= $num($t['unpaid_leave']) ?></th>
            <th><?= $num($t['absent']) ?></th>
            <th><?= $num($t['lop']) ?></th>
            <th><?= money($t['lop_amount']) ?></th>
            <th><?= money($t['pt'] ?? 0) ?></th>
            <th></th>
            <th><?= money($t['net']) ?></th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<style>
.sal-row-nosalary td { background: #fafafa; }
.sal-reg-foot th {
  background: #fff7ea !important; border-top: 2px solid #f5c56a !important;
  font-weight: 800; color: var(--ink); white-space: nowrap;
}
.sal-pt-edit { margin: 0; min-width: 88px; }
.sal-pt-edit .form-control {
  font-weight: 800; text-align: right; border-color: #fde7c2; background: #fffaf2;
  padding: 4px 8px; height: 32px;
}
.sal-pt-edit .form-control:focus { border-color: var(--brand); box-shadow: 0 0 0 .2rem rgba(245,160,27,.18); }
</style>
