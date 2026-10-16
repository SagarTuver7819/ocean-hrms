<div class="page-head">
  <div>
    <h1>Attendance · Today</h1>
    <p>Live punch status of every employee · <?= date('l, d M Y', strtotime($today)) ?> · refreshes every minute</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('attendance/report') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-file-lines me-1"></i>Daily Report</a>
    <a href="<?= url('attendance/summary') ?>" class="btn btn-sm btn-brand"><i class="fa-solid fa-chart-column me-1"></i>Monthly Summary</a>
  </div>
</div>

<div class="metric-row cols-6">
  <div class="metric-card metric-dark"><span class="m-ico"><i class="fa-solid fa-users"></i></span><div class="label">Employees</div><div class="value"><?= $summary['total'] ?></div><div class="sub">Punched in: <?= $summary['in'] ?></div></div>
  <div class="metric-card metric-orange"><span class="m-ico"><i class="fa-solid fa-laptop-code"></i></span><div class="label">Working Now</div><div class="value"><?= $summary['working'] ?></div><div class="sub">at their desk</div></div>
  <div class="metric-card metric-sky"><span class="m-ico"><i class="fa-solid fa-mug-hot"></i></span><div class="label">On Break</div><div class="value"><?= $summary['on_break'] ?></div><div class="sub">break out</div></div>
  <div class="metric-card metric-green"><span class="m-ico"><i class="fa-solid fa-circle-check"></i></span><div class="label">Completed</div><div class="value"><?= $summary['completed'] ?></div><div class="sub">punched out</div></div>
  <div class="metric-card metric-blue"><span class="m-ico"><i class="fa-solid fa-clock"></i></span><div class="label">Late</div><div class="value"><?= $summary['late'] ?></div><div class="sub">after grace time</div></div>
  <div class="metric-card metric-red"><span class="m-ico"><i class="fa-solid fa-user-xmark"></i></span><div class="label">Not Punched</div><div class="value"><?= $summary['not_in'] ?></div><div class="sub">On leave: <?= $summary['leave'] ?></div></div>
</div>

<div class="panel table-card">
  <div class="panel-header">
    <h3>Today's Attendance Grid</h3>
    <small class="text-muted">Working time is live for employees still in office</small>
  </div>
  <div class="panel-body">
    <div class="table-responsive">
      <table class="table table-hover datatable dt-export align-middle mb-0">
        <thead>
          <tr>
            <th>Sr.</th><th>Employee</th><th>Shift</th><th>Punch In</th><th>Breaks</th><th>Punch Out</th>
            <th>Break</th><th>Worked</th><th>Late</th><th>Status</th><th>Live</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $i => $r): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td class="att-emp"><b><?= e($r['first_name'] . ' ' . $r['last_name']) ?></b><small><?= e($r['employee_code']) ?> · <?= e($r['department_name'] ?? '—') ?></small></td>
            <td><small><?= e($r['shift_name'] ?? 'Default') ?><?= $r['start_time'] ? '<br>' . date('h:i A', strtotime($r['start_time'])) . ' – ' . date('h:i A', strtotime($r['end_time'])) : '' ?></small></td>
            <td class="fw-bold"><?= format_time($r['punch_in']) ?></td>
            <td><?= (int) $r['break_count'] ?: '—' ?><?= $r['on_break_since'] ? '<small class="d-block text-info">since ' . format_time($r['on_break_since']) . '</small>' : '' ?></td>
            <td class="fw-bold"><?= format_time($r['punch_out']) ?></td>
            <td><?= hm($r['break_minutes']) ?></td>
            <td class="fw-bold"><?= hm($r['working_minutes']) ?></td>
            <td class="<?= $r['late_minutes'] ? 'text-danger fw-bold' : '' ?>"><?= hm($r['late_minutes']) ?></td>
            <td><?= $r['status'] && $r['punch_in'] ? '<span class="badge-status badge-' . e($r['status']) . '">' . e(ucwords(str_replace('_', ' ', $r['status']))) . '</span>' : '—' ?></td>
            <td><span class="badge-status badge-<?= e($r['live_class']) ?>"><?= e($r['live_label']) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>setTimeout(function () { if (!document.querySelector('.modal.show')) window.location.reload(); }, 60000);</script>
