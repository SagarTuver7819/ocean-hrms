<div class="hero-card">
  <div class="hero-id">
    <div class="hero-logo"><img src="<?= asset($app['brand']['logo']) ?>" alt="Ocean Infotech"></div>
    <div>
      <h1><?= greeting() ?>, <span class="hl"><?= e($greetingName) ?></span></h1>
      <p>HR Workspace · <b><?= e(mb_strtoupper($designation)) ?></b> · <?= date('l, d F Y') ?></p>
    </div>
  </div>
<div class="metric-row">
  <a class="metric-card metric-green" href="<?= url('attendance') ?>">
    <span class="m-ico"><i class="fa-solid fa-user-check"></i></span>
    <div class="label">Present Today</div>
    <div class="value"><?= (int) $stats['present_today'] ?></div>
    <div class="sub"><?= (int) $presentPct ?>% of active staff</div>
  </a>
  <a class="metric-card metric-red" href="<?= url('attendance/report') ?>">
    <span class="m-ico"><i class="fa-solid fa-user-xmark"></i></span>
    <div class="label">Absent / Leave</div>
    <div class="value"><?= (int) $stats['absent_today'] ?></div>
    <div class="sub"><?= (int) $stats['on_leave'] ?> on leave today</div>
  </a>
  <a class="metric-card metric-blue" href="<?= url('leave?status=pending') ?>">
    <span class="m-ico"><i class="fa-solid fa-inbox"></i></span>
    <div class="label">Leave Queue</div>
    <div class="value"><?= (int) $stats['pending_leave'] ?></div>
    <div class="sub">pending approvals</div>
  </a>
  <a class="metric-card metric-teal" href="<?= url('queries?status=open') ?>">
    <span class="m-ico"><i class="fa-solid fa-comments"></i></span>
    <div class="label">Employee Query</div>
    <div class="value"><?= (int) $stats['pending_queries'] ?></div>
    <div class="sub">open tickets</div>
  </a>
</div>
</div>

<div class="quick-actions">
  <a class="primary" href="<?= url('employees/create') ?>"><i class="fa-solid fa-user-plus"></i> Add Employee</a>
  <a href="<?= url('attendance') ?>"><i class="fa-solid fa-fingerprint"></i> Attendance</a>
  <a href="<?= url('leave') ?>"><i class="fa-solid fa-calendar-check"></i> Leave Desk</a>
  <a href="<?= url('announcements') ?>"><i class="fa-solid fa-bullhorn"></i> Add Announcement</a>
  <a href="<?= url('queries') ?>"><i class="fa-solid fa-headset"></i> Employee Query</a>
  <a href="<?= url('salary') ?>"><i class="fa-solid fa-wallet"></i> Employee Salary</a>
  <a href="<?= url('employees') ?>"><i class="fa-solid fa-users"></i> All Employees</a>
</div>

<div class="snap-row">
  <div class="snap-card">
    <h3>People Snapshot <a href="<?= url('employees') ?>">All Employees</a></h3>
    <div class="snap-pills">
      <a class="snap-pill" href="<?= url('employees?status=active') ?>"><b><?= (int) $stats['active_employees'] ?></b><span>Active</span></a>
      <a class="snap-pill" href="<?= url('employees') ?>"><b><?= (int) $stats['new_joiners'] ?></b><span>Join</span></a>
      <a class="snap-pill" href="<?= url('employees?status=inactive') ?>"><b><?= (int) $stats['exits'] ?></b><span>Exit</span></a>
      <a class="snap-pill" href="<?= url('employees') ?>"><b><?= (int) $stats['total_employees'] ?></b><span>Total</span></a>
    </div>
  </div>
  <div class="snap-card">
    <h3>Today Ops <a href="<?= url('attendance') ?>">Attendance</a></h3>
    <div class="snap-pills">
      <a class="snap-pill" href="<?= url('attendance') ?>"><b><?= (int) $stats['present_today'] ?></b><span>Present</span></a>
      <a class="snap-pill" href="<?= url('attendance/report') ?>"><b><?= (int) $stats['absent_today'] ?></b><span>Absent</span></a>
      <a class="snap-pill" href="<?= url('leave?status=pending') ?>"><b><?= (int) $stats['pending_leave'] ?></b><span>Leave Q</span></a>
      <a class="snap-pill" href="<?= url('queries') ?>"><b><?= (int) $stats['pending_queries'] ?></b><span>Query</span></a>
    </div>
  </div>
  <div class="snap-card">
    <h3>Departments <a href="<?= url('settings') ?>">Manage</a></h3>
    <?php if (empty($deptChart)): ?>
      <p class="text-muted mb-0 small">No departments yet.</p>
    <?php else: ?>
    <?php $deptTones = ['blue', 'orange', 'violet', 'green', 'pink', 'teal', 'red', 'amber']; ?>
    <div class="dept-grid">
    <?php foreach ($deptChart as $i => $d): ?>
      <div class="dept-item tone-<?= $deptTones[$i % count($deptTones)] ?>">
        <div class="d-flex justify-content-between align-items-center gap-2">
          <span class="dept-name text-truncate"><?= e($d['name']) ?></span>
          <span class="dept-count"><?= (int) $d['total'] ?></span>
        </div>
        <div class="progress">
          <div class="progress-bar" style="width:<?= max(4, min(100, ((int)$d['total'] / max(1, (int)$stats['total_employees'])) * 100)) ?>%"></div>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="dcard tone-blue h-100">
      <div class="dcard-head">
        <span class="dcard-ico"><i class="fa-solid fa-user-plus"></i></span>
        <div class="flex-grow-1"><h3>Monthly Join Employees</h3><small><?= date('M Y') ?> · <?= count($recentJoiners) ?> joined</small></div>
        <a href="<?= url('employees') ?>" class="dcard-btn">View all</a>
      </div>
      <div class="dcard-body p-0">
        <div class="dash-grid">
          <table class="table mb-0 align-middle">
            <thead><tr><th>Employee Code</th><th>Name</th><th>Date of Joining</th><th>Department</th></tr></thead>
            <tbody>
            <?php if (empty($recentJoiners)): ?>
              <tr><td colspan="4" class="dcard-empty">No new joiners this month</td></tr>
            <?php else: foreach ($recentJoiners as $e): ?>
              <tr>
                <td><a href="<?= url('employees/'.$e['id']) ?>" class="fw-bold text-decoration-none" style="color:#1d4ed8"><?= e($e['employee_code']) ?></a></td>
                <td class="text-uppercase"><?= e($e['first_name'].' '.$e['last_name']) ?></td>
                <td style="color:#1d4ed8"><?= date('d-m-Y', strtotime($e['joining_date'])) ?></td>
                <td><span class="dept-pill"><?= e($e['department_name'] ?? '—') ?></span></td>
              </tr>
            <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="dcard tone-orange h-100">
      <div class="dcard-head">
        <span class="dcard-ico"><i class="fa-solid fa-calendar-check"></i></span>
        <div class="flex-grow-1"><h3>Leave Queue</h3><small><?= count($pendingLeaves) ?> pending approval</small></div>
        <a href="<?= url('leave?status=pending') ?>" class="dcard-btn">View all</a>
      </div>
      <div class="dcard-body p-0">
        <div class="dash-grid dash-grid-orange">
          <table class="table mb-0 align-middle">
            <thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th></tr></thead>
            <tbody>
            <?php if (empty($pendingLeaves)): ?>
              <tr><td colspan="4" class="dcard-empty">No pending leaves</td></tr>
            <?php else: foreach ($pendingLeaves as $l): ?>
              <tr>
                <td><strong><?= e($l['first_name'].' '.$l['last_name']) ?></strong><br><small class="text-muted"><?= e($l['employee_code']) ?></small></td>
                <td><span class="dept-pill" style="background:#fff1dc;color:#b45309"><?= e($l['leave_type']) ?></span></td>
                <td><?= format_date($l['from_date']) ?> – <?= format_date($l['to_date']) ?></td>
                <td class="fw-bold"><?= e($l['days']) ?></td>
              </tr>
            <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-xxl-3 col-md-6">
    <div class="dcard tone-violet h-100">
      <div class="dcard-head">
        <span class="dcard-ico"><i class="fa-solid fa-bullhorn"></i></span>
        <div class="flex-grow-1"><h3>Announcements</h3><small><?= count($announcements) ?> total</small></div>
        <a href="<?= url('announcements') ?>" class="dcard-btn">View all</a>
      </div>
      <div class="dcard-body">
        <?php if (empty($announcements)): ?>
          <div class="dcard-empty">No announcements yet</div>
        <?php else: foreach ($announcements as $a): ?>
          <div class="ditem">
            <span class="ditem-date"><?= date('d-m-Y', strtotime($a['publish_date'])) ?></span>
            <div class="ditem-text">
              <b><?= e($a['title']) ?></b>
              <small><?= e(mb_strimwidth(strip_tags($a['description'] ?? ''), 0, 60, '…')) ?></small>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xxl-3 col-md-6">
    <div class="dcard tone-blue h-100">
      <div class="dcard-head">
        <span class="dcard-ico"><i class="fa-solid fa-headset"></i></span>
        <div class="flex-grow-1"><h3>Employee Query</h3><small><?= count($openQueries) ?> open tickets</small></div>
        <a href="<?= url('queries') ?>" class="dcard-btn">View all</a>
      </div>
      <div class="dcard-body">
        <?php if (empty($openQueries)): ?>
          <div class="dcard-empty">No open queries</div>
        <?php else: foreach ($openQueries as $q): ?>
          <a class="ditem" href="<?= url('queries/'.$q['id']) ?>">
            <span class="ditem-date">#<?= (int) $q['id'] ?></span>
            <div class="ditem-text">
              <b><?= e($q['subject']) ?></b>
              <small><?= e($q['first_name'].' '.$q['last_name']) ?> · <?= e(ucwords(str_replace('_', ' ', $q['category']))) ?></small>
            </div>
          </a>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xxl-3 col-md-6">
    <div class="dcard tone-amber h-100">
      <div class="dcard-head">
        <span class="dcard-ico"><i class="fa-solid fa-award"></i></span>
        <div class="flex-grow-1"><h3>Work Anniversaries</h3><small>Next 30 days · <?= count($anniversaries) ?></small></div>
      </div>
      <div class="dcard-body">
        <?php if (empty($anniversaries)): ?>
          <div class="dcard-empty">No upcoming anniversaries</div>
        <?php else: foreach ($anniversaries as $a): ?>
          <div class="ditem">
            <span class="ditem-date">In <?= (int) $a['in_days'] ?>d</span>
            <div class="ditem-text">
              <b class="text-uppercase"><?= e($a['first_name'].' '.$a['last_name']) ?></b>
              <small><?= (int) $a['years'] ?> yr · <?= e($a['department_name'] ?? '') ?></small>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xxl-3 col-md-6">
    <div class="dcard tone-pink h-100">
      <div class="dcard-head">
        <span class="dcard-ico"><i class="fa-solid fa-cake-candles"></i></span>
        <div class="flex-grow-1"><h3>Upcoming Birthdays</h3><small>Next 45 days · <?= count($birthdays) ?></small></div>
      </div>
      <div class="dcard-body">
        <?php if (empty($birthdays)): ?>
          <div class="dcard-empty">Add DOB on employee profiles to see birthdays</div>
        <?php else: foreach ($birthdays as $b): ?>
          <div class="ditem">
            <span class="ditem-date">In <?= (int) $b['in_days'] ?>d</span>
            <div class="ditem-text">
              <b class="text-uppercase"><?= e($b['first_name'].' '.$b['last_name']) ?></b>
              <small><?= e($b['employee_code']) ?></small>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="dcard tone-teal">
  <div class="dcard-head">
    <span class="dcard-ico"><i class="fa-solid fa-umbrella-beach"></i></span>
    <div class="flex-grow-1"><h3>Upcoming Holidays</h3><small>Next 45 days · <?= count($holidays) ?></small></div>
    <a href="<?= url('holidays') ?>" class="dcard-btn">Manage</a>
  </div>
  <div class="dcard-body">
    <?php if (empty($holidays)): ?>
      <div class="dcard-empty">No upcoming holidays</div>
    <?php else: ?>
      <div class="holiday-strip">
        <?php foreach ($holidays as $h):
          $from = $h['holiday_date'];
          $to = $h['to_date'] ?? $from;
          $label = ($to && $to !== $from)
            ? date('d M', strtotime($from)) . ' – ' . date('d M Y', strtotime($to))
            : date('D, M Y', strtotime($from));
        ?>
          <div class="holiday-chip">
            <span class="hd-day"><?= date('d', strtotime($from)) ?></span>
            <div><b><?= e($h['name']) ?></b><small><?= e($label) ?></small></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
