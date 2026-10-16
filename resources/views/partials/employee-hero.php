<?php
$e = $employee;
$salary = $salary ?? null;
$actions = $actions ?? '';
$fullName = trim(($e['first_name'] ?? '') . ' ' . ($e['middle_name'] ?? '') . ' ' . ($e['last_name'] ?? ''));
$fullName = preg_replace('/\s+/', ' ', $fullName);
$initials = strtoupper(mb_substr($e['first_name'] ?? '', 0, 1) . mb_substr($e['last_name'] ?? '', 0, 1));
$shiftTime = !empty($e['shift_start'])
    ? date('h:i A', strtotime($e['shift_start'])) . ' – ' . date('h:i A', strtotime($e['shift_end']))
    : '';
$dash = fn($v) => ($v === null || $v === '') ? '—' : $v;
?>
<div class="ep-hero">
  <div class="ep-hero-top">
    <div class="ep-avatar-wrap">
      <div class="ep-avatar <?= !empty($e['profile_photo']) ? 'has-photo' : '' ?>">
        <?php if (!empty($e['profile_photo'])): ?>
          <img src="<?= asset($e['profile_photo']) ?>" alt="<?= e($fullName) ?>">
        <?php else: ?>
          <?= e($initials ?: '?') ?>
        <?php endif; ?>
      </div>
      <?php if (!empty($photoAction)): ?>
        <form method="post" action="<?= e($photoAction) ?>" enctype="multipart/form-data" class="js-photo-form">
          <?= csrf_field() ?>
          <label class="ep-avatar-btn" title="<?= !empty($e['profile_photo']) ? 'Change photo' : 'Upload photo' ?>">
            <i class="fa-solid fa-camera"></i>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="d-none js-photo-input">
          </label>
        </form>
        <?php if (!empty($e['profile_photo'])): ?>
          <form method="post" action="<?= e($photoAction) ?>/remove">
            <?= csrf_field() ?>
            <button type="submit" class="ep-avatar-btn remove" title="Remove photo" data-confirm="Remove the profile photo?"><i class="fa-solid fa-trash-can"></i></button>
          </form>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <div class="ep-id">
      <span class="badge-status badge-<?= e($e['status']) ?>"><?= e(str_replace('_', ' ', $e['status'])) ?></span>
      <div class="ep-code"><?= e($e['employee_code']) ?></div>
      <h1 class="ep-name"><?= e($fullName) ?></h1>
      <div class="ep-meta">
        <span><i class="fa-solid fa-briefcase"></i><?= e($dash($e['designation_name'] ?? null)) ?></span>
        <span><i class="fa-solid fa-building"></i><?= e($dash($e['department_name'] ?? null)) ?></span>
        <?php if (!empty($e['employment_type'])): ?>
          <span class="ep-type"><?= e(ucwords(str_replace('_', ' ', $e['employment_type']))) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <?php if ($actions !== ''): ?>
      <div class="ep-actions"><?= $actions ?></div>
    <?php endif; ?>
  </div>

  <div class="ep-chips">
    <div class="ep-chip t-orange">
      <span class="ep-chip-ico"><i class="fa-solid fa-calendar-check"></i></span>
      <div><small>Joining Date</small><b><?= format_date($e['joining_date'] ?? null, 'd-m-Y') ?></b></div>
    </div>
    <div class="ep-chip t-dark">
      <span class="ep-chip-ico"><i class="fa-solid fa-clock"></i></span>
      <div><small>Shift</small><b><?= e($dash($e['shift_name'] ?? null)) ?></b><?php if ($shiftTime): ?><em><?= e($shiftTime) ?></em><?php endif; ?></div>
    </div>
    <div class="ep-chip t-orange">
      <span class="ep-chip-ico"><i class="fa-solid fa-envelope"></i></span>
      <div><small>Official Mail ID</small><b class="ep-break" title="<?= e($e['official_email'] ?? '') ?>"><?= e($dash($e['official_email'] ?? null)) ?></b></div>
    </div>
    <div class="ep-chip t-dark">
      <span class="ep-chip-ico"><i class="fa-solid fa-mobile-screen"></i></span>
      <div><small>Mobile</small><b><?= e($dash($e['mobile'] ?? null)) ?></b></div>
    </div>
    <div class="ep-chip t-orange">
      <span class="ep-chip-ico"><i class="fa-solid fa-user-tie"></i></span>
      <div><small>Reporting Head</small><b><?= e($dash($e['manager_name'] ?? null)) ?></b></div>
    </div>
    <div class="ep-chip t-dark">
      <span class="ep-chip-ico"><i class="fa-solid fa-right-to-bracket"></i></span>
      <div><small>Employee Login</small>
        <?php if (!empty($e['login_username'])): ?>
          <b><span class="code-pill"><?= e($e['login_username']) ?></span></b>
        <?php else: ?>
          <b class="text-muted">Not created</b>
        <?php endif; ?>
      </div>
    </div>
    <?php if (!empty($showSalary)): ?>
    <div class="ep-chip t-orange">
      <span class="ep-chip-ico"><i class="fa-solid fa-indian-rupee-sign"></i></span>
      <div><small>Monthly Salary</small><b><?= $salary ? money($salary['monthly_salary']) : '—' ?></b></div>
    </div>
    <?php endif; ?>
  </div>
</div>
