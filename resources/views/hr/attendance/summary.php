<?php
$num = fn($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
$prev = date('Y-m', strtotime($month . '-01 -1 month'));
$next = date('Y-m', strtotime($month . '-01 +1 month'));
?>
<div class="page-head">
  <div>
    <h1>Monthly Attendance Summary</h1>
    <p><?= date('F Y', strtotime($month . '-01')) ?> · Present, absent, late, leave &amp; working hours per employee</p>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <a href="<?= url('attendance/summary?month=' . $prev) ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-chevron-left"></i></a>
    <form method="get"><input type="month" name="month" class="form-control form-control-sm" value="<?= e($month) ?>" onchange="this.form.submit()"></form>
    <a href="<?= url('attendance/summary?month=' . $next) ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-chevron-right"></i></a>
    <a href="<?= url('attendance/report?from=' . $month . '-01&to=' . min(date('Y-m-t', strtotime($month . '-01')), date('Y-m-d'))) ?>" class="btn btn-sm btn-brand"><i class="fa-solid fa-file-lines me-1"></i>Day-wise Report</a>
  </div>
</div>

<div class="panel table-card">
  <div class="panel-body">
    <div class="table-responsive">
      <table class="table table-hover datatable dt-export align-middle mb-0">
        <thead>
          <tr>
            <th>Sr.</th><th>Employee</th><th>Shift</th><th>Working Days</th><th>Present</th><th>Absent</th><th>Late</th>
            <th>Half Day</th><th>Leave</th><th>Worked</th><th>Avg / Day</th><th>Break</th><th>OT</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($employees as $i => $emp): $s = $emp['stats']; ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td class="att-emp"><a class="text-decoration-none text-dark" href="<?= url('attendance/report?employee_id=' . $emp['id'] . '&from=' . $month . '-01&to=' . date('Y-m-t', strtotime($month . '-01'))) ?>"><b><?= e($emp['first_name'] . ' ' . $emp['last_name']) ?></b></a><small><?= e($emp['employee_code']) ?> · <?= e($emp['department_name'] ?? '—') ?></small></td>
            <td><small><?= e($emp['shift_name'] ?? 'Default') ?></small></td>
            <td><?= $s['working_days'] ?></td>
            <td><span class="badge-status badge-present"><?= $num($s['present']) ?></span></td>
            <td><span class="badge-status badge-<?= $s['absent'] ? 'absent' : 'present' ?>"><?= $s['absent'] ?></span></td>
            <td class="<?= $s['late'] ? 'text-danger fw-bold' : '' ?>"><?= $s['late'] ?></td>
            <td><?= $s['half_day'] ?></td>
            <td><?= $s['leave'] ?></td>
            <td class="fw-bold"><?= hm($s['worked']) ?></td>
            <td><?= hm($s['avg']) ?></td>
            <td><?= hm($s['break']) ?></td>
            <td><?= hm($s['overtime']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
