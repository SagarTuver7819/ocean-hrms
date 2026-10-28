<?php
$q = static function (array $extra = []) use ($shiftFilter, $liveFilter): string {
    $p = array_filter([
        'shift' => $extra['shift'] ?? $shiftFilter,
        'live' => $extra['live'] ?? $liveFilter,
    ], static fn($v) => $v !== '' && $v !== null);
    return url('attendance' . ($p ? '?' . http_build_query($p) : ''));
};
?>
<div class="page-head">
  <div>
    <h1>Attendance · Today</h1>
    <p>Live punch · shift-wise view · <?= date('l, d M Y', strtotime($today)) ?> · auto-refresh 1 min</p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= url('attendance/report') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-file-lines me-1"></i>Daily Report</a>
    <a href="<?= url('attendance/summary') ?>" class="btn btn-sm btn-brand"><i class="fa-solid fa-chart-column me-1"></i>Monthly Summary</a>
  </div>
</div>

<div class="metric-row cols-6">
  <a href="<?= $q(['live' => '']) ?>" class="metric-card metric-dark text-decoration-none"><span class="m-ico"><i class="fa-solid fa-users"></i></span><div class="label">Employees</div><div class="value"><?= $summary['total'] ?></div><div class="sub">Punched in: <?= $summary['in'] ?></div></a>
  <a href="<?= $q(['live' => 'working']) ?>" class="metric-card metric-orange text-decoration-none"><span class="m-ico"><i class="fa-solid fa-laptop-code"></i></span><div class="label">Working Now</div><div class="value"><?= $summary['working'] ?></div><div class="sub">On break: <?= $summary['on_break'] ?></div></a>
  <a href="<?= $q(['live' => 'completed']) ?>" class="metric-card metric-green text-decoration-none"><span class="m-ico"><i class="fa-solid fa-circle-check"></i></span><div class="label">Completed</div><div class="value"><?= $summary['completed'] ?></div><div class="sub">punched out</div></a>
  <a href="<?= $q(['live' => 'late']) ?>" class="metric-card metric-blue text-decoration-none"><span class="m-ico"><i class="fa-solid fa-clock"></i></span><div class="label">Late</div><div class="value"><?= $summary['late'] ?></div><div class="sub">after grace (excl. interns)</div></a>
  <a href="<?= $q(['live' => 'half_day']) ?>" class="metric-card metric-sky text-decoration-none"><span class="m-ico"><i class="fa-solid fa-adjust"></i></span><div class="label">Half Day</div><div class="value"><?= $summary['half_day'] ?></div><div class="sub">below full hours</div></a>
  <a href="<?= $q(['live' => 'not_in']) ?>" class="metric-card metric-red text-decoration-none"><span class="m-ico"><i class="fa-solid fa-user-xmark"></i></span><div class="label">Not Punched</div><div class="value"><?= $summary['not_in'] ?></div><div class="sub">On leave: <?= $summary['leave'] ?></div></a>
</div>

<?php if (!empty($byShift)): ?>
<div class="att-shift-bar">
  <a href="<?= $q(['shift' => '', 'live' => $liveFilter]) ?>" class="att-shift-chip <?= $shiftFilter === '' ? 'active' : '' ?>">
    <b>All Shifts</b><small><?= (int) array_sum(array_column($byShift, 'total')) ?> emp</small>
  </a>
  <?php foreach ($byShift as $key => $s): ?>
    <a href="<?= $q(['shift' => $key, 'live' => $liveFilter]) ?>" class="att-shift-chip <?= $shiftFilter === $key ? 'active' : '' ?>">
      <b><?= e($s['name']) ?></b>
      <small><?= e($s['code']) ?> · <?= (int) $s['total'] ?> · In <?= (int) $s['in'] ?> · Live <?= (int) $s['working'] ?></small>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="panel table-card">
  <div class="panel-header">
    <h3>Today's Attendance Grid</h3>
    <small class="text-muted">
      <?php if ($shiftFilter || $liveFilter): ?>
        Filtered
        <?php if ($shiftFilter): ?> · shift <?= e($byShift[$shiftFilter]['name'] ?? $shiftFilter) ?><?php endif; ?>
        <?php if ($liveFilter): ?> · <?= e(str_replace('_', ' ', $liveFilter)) ?><?php endif; ?>
        · <a href="<?= url('attendance') ?>">Clear</a>
      <?php else: ?>
        Interns: flexible 4h day · late N/A · under 4h = Half Day (not Absent)
      <?php endif; ?>
    </small>
  </div>
  <div class="panel-body">
    <div class="table-responsive">
      <table class="table table-hover datatable dt-export align-middle mb-0">
        <thead>
          <tr>
            <th>Sr.</th>
            <th>Employee</th>
            <th>Shift</th>
            <th>Punch In</th>
            <th>Breaks</th>
            <th>Punch Out</th>
            <th>Break</th>
            <th>Worked</th>
            <th>Late</th>
            <th>Day Status</th>
            <th>Live</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $i => $r): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td class="att-emp">
              <b><?= e($r['first_name'] . ' ' . $r['last_name']) ?></b>
              <small>
                <?= e($r['employee_code']) ?>
                · <?= e($r['department_name'] ?? '—') ?>
                <?php if (($r['employment_type'] ?? '') === 'intern'): ?>
                  · <span class="text-warning fw-bold">Intern</span>
                <?php elseif (!empty($r['designation_name'])): ?>
                  · <?= e($r['designation_name']) ?>
                <?php endif; ?>
              </small>
            </td>
            <td>
              <small class="fw-bold"><?= e($r['shift_name'] ?? 'Default') ?></small>
              <?php if ($r['start_time']): ?>
                <small class="d-block text-muted"><?= date('h:i A', strtotime($r['start_time'])) ?> – <?= date('h:i A', strtotime($r['end_time'])) ?></small>
              <?php endif; ?>
            </td>
            <td class="fw-bold"><?= format_time($r['punch_in']) ?></td>
            <td>
              <?= (int) $r['break_count'] ?: '—' ?>
              <?php if ($r['on_break_since']): ?>
                <small class="d-block text-info">since <?= format_time($r['on_break_since']) ?></small>
              <?php endif; ?>
            </td>
            <td class="fw-bold"><?= format_time($r['punch_out']) ?></td>
            <td><?= hm($r['break_minutes']) ?></td>
            <td class="fw-bold"><?= hm($r['working_minutes']) ?></td>
            <td class="<?= ($r['show_late'] && $r['late_minutes']) ? 'text-danger fw-bold' : '' ?>">
              <?= $r['show_late'] ? hm($r['late_minutes']) : '<span class="text-muted">N/A</span>' ?>
            </td>
            <td>
              <?php if ($r['day_status']): ?>
                <span class="badge-status badge-<?= e($r['day_status']) ?>"><?= e(ucwords(str_replace('_', ' ', $r['day_status']))) ?></span>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td><span class="badge-status badge-<?= e($r['live_class']) ?>"><?= e($r['live_label']) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
.att-shift-bar {
  display: flex; flex-wrap: wrap; gap: 10px; margin: 0 0 16px;
}
.att-shift-chip {
  display: flex; flex-direction: column; gap: 2px;
  min-width: 140px; padding: 10px 14px; border-radius: 14px;
  background: #fff; border: 1px solid var(--line); text-decoration: none; color: inherit;
  box-shadow: var(--shadow-sm); transition: .15s;
}
.att-shift-chip:hover { border-color: var(--brand); color: inherit; }
.att-shift-chip.active {
  border-color: #f5c56a;
  background: linear-gradient(180deg, #fff7ea, #fff);
  box-shadow: 0 6px 16px rgba(245,160,27,.18);
}
.att-shift-chip b { font-size: 13px; font-weight: 800; }
.att-shift-chip small { font-size: 11px; color: var(--muted); font-weight: 600; }
</style>
<script>setTimeout(function () { if (!document.querySelector('.modal.show')) window.location.reload(); }, 60000);</script>
