<?php
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$ranges = [
    'Today' => [$today, $today],
    'Yesterday' => [$yesterday, $yesterday],
    'Last 7 days' => [date('Y-m-d', strtotime('-6 days')), $today],
    'This Month' => [date('Y-m-01'), $today],
    'Last Month' => [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))],
];
$q = fn(array $r) => url('attendance/report?from=' . $r[0] . '&to=' . $r[1] . ($employeeId ? '&employee_id=' . $employeeId : '') . ($departmentId ? '&department_id=' . $departmentId : ''));
?>
<div class="page-head">
  <div>
    <h1>Daily Attendance Report</h1>
    <p><?= $from === $to ? date('l, d M Y', strtotime($from)) : date('d M Y', strtotime($from)) . ' – ' . date('d M Y', strtotime($to)) ?> · Punch, break &amp; working-hour details · Export to Excel</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('attendance') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-signal me-1"></i>Live Today</a>
    <a href="<?= url('attendance/summary') ?>" class="btn btn-sm btn-brand"><i class="fa-solid fa-chart-column me-1"></i>Monthly Summary</a>
  </div>
</div>

<div class="filter-bar">
  <form class="row g-2 align-items-end" method="get">
    <div class="col-md-2"><label class="form-label">From</label><input type="date" name="from" class="form-control" value="<?= e($from) ?>"></div>
    <div class="col-md-2"><label class="form-label">To</label><input type="date" name="to" class="form-control" value="<?= e($to) ?>"></div>
    <div class="col-md-3">
      <label class="form-label">Employee</label>
      <select name="employee_id" class="form-select select2">
        <option value="">All employees</option>
        <?php foreach ($employees as $emp): ?><option value="<?= $emp['id'] ?>" <?= $employeeId === (int) $emp['id'] ? 'selected' : '' ?>><?= e($emp['employee_code'] . ' – ' . $emp['first_name'] . ' ' . $emp['last_name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Department</label>
      <select name="department_id" class="form-select">
        <option value="">All departments</option>
        <?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>" <?= $departmentId === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><button class="btn btn-brand w-100"><i class="fa-solid fa-filter me-1"></i>Show Report</button></div>
    <div class="col-12">
      <div class="quick-range">
        <?php foreach ($ranges as $label => $r): ?><a href="<?= $q($r) ?>" class="<?= $r === [$from, $to] ? 'active' : '' ?>"><?= $label ?></a><?php endforeach; ?>
      </div>
    </div>
  </form>
</div>

<div class="metric-row">
  <div class="metric-card metric-dark"><span class="m-ico"><i class="fa-solid fa-list-check"></i></span><div class="label">Records</div><div class="value"><?= $totals['days'] ?></div><div class="sub">attendance entries</div></div>
  <div class="metric-card metric-teal"><span class="m-ico"><i class="fa-solid fa-hourglass-half"></i></span><div class="label">Total Worked</div><div class="value" style="font-size:22px"><?= hm($totals['worked'], '0h') ?></div><div class="sub">Overtime <?= hm($totals['overtime'], '0h') ?></div></div>
  <div class="metric-card metric-sky"><span class="m-ico"><i class="fa-solid fa-mug-hot"></i></span><div class="label">Total Break</div><div class="value" style="font-size:22px"><?= hm($totals['break'], '0h') ?></div><div class="sub">all breaks</div></div>
  <div class="metric-card metric-blue"><span class="m-ico"><i class="fa-solid fa-clock"></i></span><div class="label">Late Arrivals</div><div class="value"><?= $totals['late'] ?></div><div class="sub">after grace time</div></div>
</div>

<div class="panel table-card">
  <div class="panel-body">
    <div class="table-responsive">
      <table class="table table-hover datatable dt-export align-middle mb-0">
        <thead>
          <tr>
            <th>Sr.</th><th>Date</th><th>Employee</th><th>Shift</th><th>Punch In</th><th>Break Out – In</th><th>Punch Out</th>
            <th>Break</th><th>Worked</th><th>Late</th><th>Early Out</th><th>OT</th><th>Status</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $i => $r): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td data-order="<?= e($r['attendance_date']) ?>"><b><?= date('d M Y', strtotime($r['attendance_date'])) ?></b><small class="d-block text-muted"><?= date('l', strtotime($r['attendance_date'])) ?></small></td>
            <td class="att-emp"><b><?= e($r['first_name'] . ' ' . $r['last_name']) ?></b><small><?= e($r['employee_code']) ?> · <?= e($r['department_name'] ?? '—') ?></small></td>
            <td><small><?= e($r['shift_name'] ?? '—') ?></small></td>
            <td class="fw-bold"><?= format_time($r['punch_in']) ?></td>
            <td class="att-breaks"><?= break_chips($r['break_list']) ?></td>
            <td class="fw-bold"><?= format_time($r['punch_out']) ?></td>
            <td><?= hm($r['break_minutes']) ?></td>
            <td class="fw-bold"><?= hm($r['working_minutes']) ?></td>
            <td class="<?= $r['late_minutes'] ? 'text-danger fw-bold' : '' ?>"><?= hm($r['late_minutes']) ?></td>
            <td><?= hm($r['early_leave_minutes']) ?></td>
            <td><?= hm($r['overtime_minutes']) ?></td>
            <td><span class="badge-status badge-<?= e($r['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $r['status']))) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
