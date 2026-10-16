<?php
$paid = $leaveBalances[0] ?? null;
$present = rtrim(rtrim(number_format($monthStats['present'], 1), '0'), '.');
?>
<div class="hero-card">
  <div class="hero-id">
    <?php if (!empty($employee['profile_photo'])): ?>
      <button type="button" class="hero-logo has-photo js-photo-view" title="View photo"
        data-photo="<?= e(asset($employee['profile_photo'])) ?>"
        data-name="<?= e(trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''))) ?>">
        <img src="<?= asset($employee['profile_photo']) ?>" alt="">
      </button>
    <?php else: ?>
      <a href="<?= url('employee/profile') ?>" class="hero-logo" title="My Profile"><img src="<?= asset($app['brand']['logo']) ?>" alt="Ocean Infotech"></a>
    <?php endif; ?>
    <div>
      <h1><?= greeting() ?>, <span class="hl"><?= e(($employee['first_name'] ?? $authUser['name']) . ' ' . ($employee['last_name'] ?? '')) ?></span></h1>
      <p><?= e($employee['employee_code'] ?? '') ?> · <?= e(mb_strtoupper($employee['department_name'] ?? 'Ocean Infotech')) ?><?php if (!empty($employee['designation_name'])): ?> · <b><?= e(mb_strtoupper($employee['designation_name'])) ?></b><?php endif; ?> · <?= date('l, d F Y') ?></p>
    </div>
  </div>
</div>

<?php \App\Core\View::partial('partials/punch-widget', ['snap' => $snap, 'back' => 'dashboard']); ?>

<div class="metric-row">
  <a href="<?= url('employee/attendance') ?>" class="metric-card metric-green text-decoration-none"><span class="m-ico"><i class="fa-solid fa-calendar-check"></i></span><div class="label">Present this month</div><div class="value"><?= $present ?> <small style="font-size:13px">/ <?= $monthStats['working_days'] ?></small></div><div class="sub">Late <?= $monthStats['late'] ?> · Absent <?= $monthStats['absent'] ?></div></a>
  <a href="<?= url('employee/attendance') ?>" class="metric-card metric-teal text-decoration-none"><span class="m-ico"><i class="fa-solid fa-hourglass-half"></i></span><div class="label">Worked this month</div><div class="value" style="font-size:22px"><?= hm($monthStats['worked'], '0h') ?></div><div class="sub">Avg <?= hm($monthStats['avg'], '0h') ?> per day</div></a>
  <a href="<?= url('employee/leave') ?>" class="metric-card metric-violet text-decoration-none"><span class="m-ico"><i class="fa-solid fa-umbrella-beach"></i></span><div class="label">Paid leave available</div><div class="value"><?= $paid ? rtrim(rtrim(number_format($paid['available'], 1), '0'), '.') : '0' ?></div><div class="sub">1 leave credited every month</div></a>
  <a href="<?= url('employee/salary') ?>" class="metric-card metric-blue text-decoration-none"><span class="m-ico"><i class="fa-solid fa-file-invoice"></i></span><div class="label">Salary slip</div><div class="value" style="font-size:20px"><?= $lastSlip ? date('M Y', strtotime($lastSlip . '-01')) : '—' ?></div><div class="sub"><?= $lastSlip ? 'Latest slip · View / Download' : 'Available after first month' ?></div></a>
</div>

<div class="dash-actions">
  <a href="<?= url('employee/leave') ?>"><span class="t-red"><i class="fa-solid fa-calendar-plus"></i></span>Apply Leave</a>
  <a href="<?= url('employee/attendance') ?>"><span class="t-sky"><i class="fa-solid fa-fingerprint"></i></span>My Attendance</a>
  <a href="<?= url('employee/salary') ?>"><span class="t-green"><i class="fa-solid fa-file-invoice"></i></span>Salary Slip</a>
  <a href="<?= url('queries/create') ?>"><span class="t-violet"><i class="fa-solid fa-headset"></i></span>Raise Query<?php if ($pendingQueries): ?><em><?= (int) $pendingQueries ?> open</em><?php endif; ?></a>
  <a href="<?= url('employee/profile') ?>"><span class="t-orange"><i class="fa-solid fa-user"></i></span>My Profile</a>
</div>

<div class="row g-3 dash-cards">
  <div class="col-xl-5 col-lg-12">
    <div class="ep-card cp tone-orange">
      <div class="ep-card-head">
        <span class="ep-card-ico"><i class="fa-solid fa-calendar-check"></i></span>
        <div><h3>Recent Leave</h3><small>Your last 5 applications</small></div>
        <a href="<?= url('employee/leave') ?>" class="btn btn-sm btn-soft ms-auto">Apply / View</a>
      </div>
      <?php if (!$recentLeaves): ?>
        <div class="dash-empty"><i class="fa-regular fa-calendar"></i>No leave applied yet.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table mb-0"><thead><tr><th>Type</th><th>Dates</th><th>Days</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($recentLeaves as $l): ?>
            <tr>
              <td class="fw-semibold"><?= e($l['leave_type']) ?></td>
              <td class="text-nowrap"><?= format_date($l['from_date']) ?><?= $l['to_date'] !== $l['from_date'] ? ' – ' . format_date($l['to_date']) : '' ?></td>
              <td><?= (float) $l['days'] ?></td>
              <td><span class="badge-status badge-<?= e($l['status']) ?>"><?= e(ucfirst($l['status'])) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody></table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-xl-4 col-lg-6">
    <div class="ep-card cp tone-orange">
      <div class="ep-card-head">
        <span class="ep-card-ico"><i class="fa-solid fa-bullhorn"></i></span>
        <div><h3>Announcements</h3><small>Latest company notices</small></div>
      </div>
      <?php if (!$announcements): ?>
        <div class="dash-empty"><i class="fa-regular fa-bell"></i>No announcements.</div>
      <?php endif; ?>
      <div class="dash-list">
        <?php foreach ($announcements as $a): ?>
          <div class="dash-item">
            <span class="di-ico t-orange"><i class="fa-solid fa-bullhorn"></i></span>
            <div><b><?= e($a['title']) ?></b><p><?= nl2br(e($a['description'])) ?></p></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-lg-6">
    <div class="ep-card cp tone-orange">
      <div class="ep-card-head">
        <span class="ep-card-ico"><i class="fa-solid fa-umbrella-beach"></i></span>
        <div><h3>Holidays</h3><small>Upcoming company holidays</small></div>
      </div>
      <?php if (!$holidays): ?>
        <div class="dash-empty"><i class="fa-regular fa-calendar"></i>No upcoming holidays.</div>
      <?php endif; ?>
      <div class="dash-list">
        <?php foreach ($holidays as $h): $ts = strtotime($h['holiday_date']); ?>
          <div class="dash-item">
            <span class="di-date"><b><?= date('d', $ts) ?></b><?= date('M', $ts) ?></span>
            <div><b><?= e($h['name']) ?></b><p><?= date('l', $ts) ?></p></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
