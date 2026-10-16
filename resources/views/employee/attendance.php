<?php
$prev = date('Y-m', strtotime($month . '-01 -1 month'));
$next = date('Y-m', strtotime($month . '-01 +1 month'));
?>
<div class="page-head">
  <div>
    <h1>My Attendance</h1>
    <p>Punch In · Break Out · Break In · Punch Out — <?= date('l, d F Y') ?></p>
  </div>
</div>

<?php \App\Core\View::partial('partials/punch-widget', ['snap' => $snap, 'back' => 'attendance']); ?>

<div class="metric-row cols-6">
  <div class="metric-card metric-green"><span class="m-ico"><i class="fa-solid fa-user-check"></i></span><div class="label">Present</div><div class="value"><?= rtrim(rtrim(number_format($stats['present'], 1), '0'), '.') ?></div><div class="sub">of <?= $stats['working_days'] ?> working days</div></div>
  <div class="metric-card metric-red"><span class="m-ico"><i class="fa-solid fa-user-xmark"></i></span><div class="label">Absent</div><div class="value"><?= $stats['absent'] ?></div><div class="sub">no punch, no leave</div></div>
  <div class="metric-card metric-blue"><span class="m-ico"><i class="fa-solid fa-clock"></i></span><div class="label">Late</div><div class="value"><?= $stats['late'] ?></div><div class="sub">Half days: <?= $stats['half_day'] ?></div></div>
  <div class="metric-card metric-violet"><span class="m-ico"><i class="fa-solid fa-plane-departure"></i></span><div class="label">Leave</div><div class="value"><?= $stats['leave'] ?></div><div class="sub">approved days</div></div>
  <div class="metric-card metric-teal"><span class="m-ico"><i class="fa-solid fa-hourglass-half"></i></span><div class="label">Worked</div><div class="value" style="font-size:22px"><?= hm($stats['worked'], '0h') ?></div><div class="sub">Avg <?= hm($stats['avg'], '0h') ?> / day</div></div>
  <div class="metric-card metric-sky"><span class="m-ico"><i class="fa-solid fa-mug-hot"></i></span><div class="label">Break</div><div class="value" style="font-size:22px"><?= hm($stats['break'], '0h') ?></div><div class="sub">OT <?= hm($stats['overtime'], '0h') ?></div></div>
</div>

<div class="panel table-card">
  <div class="panel-header">
    <h3>Attendance · <?= date('F Y', strtotime($month . '-01')) ?></h3>
    <div class="d-flex gap-2 align-items-center">
      <a href="<?= url('employee/attendance?month=' . $prev) ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-chevron-left"></i></a>
      <form method="get" class="d-flex gap-2"><input type="month" name="month" class="form-control form-control-sm" value="<?= e($month) ?>" onchange="this.form.submit()"></form>
      <a href="<?= url('employee/attendance?month=' . $next) ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-chevron-right"></i></a>
    </div>
  </div>
  <div class="panel-body">
    <div class="table-responsive">
      <table class="table table-hover datatable align-middle mb-0" data-order='[[0,"desc"]]'>
        <thead><tr><th>Date</th><th>Punch In</th><th>Breaks</th><th>Punch Out</th><th>Break</th><th>Worked</th><th>Late</th><th>OT</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td data-order="<?= e($r['attendance_date']) ?>"><b><?= date('d M', strtotime($r['attendance_date'])) ?></b> <small class="text-muted"><?= date('D', strtotime($r['attendance_date'])) ?></small></td>
            <td><?= format_time($r['punch_in']) ?></td>
            <td class="att-breaks"><?= break_chips($r['break_list']) ?></td>
            <td><?= format_time($r['punch_out']) ?></td>
            <td><?= hm($r['break_minutes']) ?></td>
            <td class="fw-bold"><?= hm($r['working_minutes']) ?></td>
            <td class="<?= $r['late_minutes'] ? 'text-danger fw-bold' : '' ?>"><?= hm($r['late_minutes']) ?></td>
            <td><?= hm($r['overtime_minutes']) ?></td>
            <td><span class="badge-status badge-<?= e($r['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $r['status']))) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
