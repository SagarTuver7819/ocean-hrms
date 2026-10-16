<?php
/** @var array $snap from App\Services\Attendance::snapshot() */
$state = $snap['state'];
$row = $snap['row'];
$shift = $snap['shift'];
$breaks = $snap['breaks'];
$lastBreak = $breaks ? end($breaks) : null;
$late = (int) ($row['late_minutes'] ?? 0);
$targetMin = (int) $shift['full_day_minutes'];
$labels = [
    'not_started' => ['Not Punched In', 'fa-fingerprint'],
    'working'     => ['Working', 'fa-laptop-code'],
    'on_break'    => ['On Break', 'fa-mug-hot'],
    'done'        => ['Day Completed', 'fa-circle-check'],
];
[$stateLabel, $stateIcon] = $labels[$state];
$steps = [
    ['Punch In', 'fa-right-to-bracket', $row['punch_in'] ?? null],
    ['Break Out', 'fa-mug-hot', $lastBreak['break_out'] ?? null],
    ['Break In', 'fa-rotate-left', $lastBreak['break_in'] ?? null],
    ['Punch Out', 'fa-right-from-bracket', $row['punch_out'] ?? null],
];
$notice = $snap['holiday'] ? 'Today is a holiday: ' . $snap['holiday'] : ($snap['onLeave'] ? 'You are on approved leave today.' : ($snap['weekOff'] ? 'Today is your weekly off.' : null));
?>
<div class="punch-widget state-<?= $state ?>" id="punchWidget"
     data-state="<?= $state ?>" data-work="<?= (int) $snap['workSecs'] ?>" data-break="<?= (int) $snap['breakSecs'] ?>"
     data-target="<?= (int) $snap['targetSecs'] ?>" data-url="<?= url('attendance/punch') ?>" data-csrf="<?= e(csrf_token()) ?>" data-back="<?= e($back ?? '') ?>">
  <div class="pw-clock">
    <div class="pw-ring">
      <svg viewBox="0 0 120 120" aria-hidden="true">
        <defs><linearGradient id="pwGrad" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#F5A01B"/><stop offset="1" stop-color="#d4890f"/></linearGradient></defs>
        <circle class="pw-track" cx="60" cy="60" r="52"/>
        <circle class="pw-progress" cx="60" cy="60" r="52"/>
      </svg>
      <div class="pw-center">
        <span class="pw-ico"><i class="fa-solid <?= $stateIcon ?>"></i></span>
        <b class="js-pw-work">00:00:00</b>
        <small><?= $state === 'on_break' ? 'Worked so far' : 'Worked today' ?></small>
      </div>
    </div>
    <div class="pw-target">Target <b><?= hm($targetMin) ?></b> · <span class="js-pw-left"></span></div>
  </div>

  <div class="pw-main">
    <div class="pw-head">
      <span class="pw-state"><i class="pw-dot"></i><?= $stateLabel ?></span>
      <span class="pw-shift"><i class="fa-solid fa-business-time me-1"></i><?= e($shift['name']) ?> · <?= date('h:i A', strtotime($shift['start_time'])) ?> – <?= date('h:i A', strtotime($shift['end_time'])) ?></span>
    </div>
    <?php if ($notice): ?><div class="pw-notice"><i class="fa-solid fa-circle-info me-1"></i><?= e($notice) ?></div><?php endif; ?>

    <ol class="pw-steps">
      <?php foreach ($steps as $i => [$label, $icon, $at]): ?>
        <li class="<?= $at ? 'done' : '' ?>">
          <span class="pw-step-ico"><i class="fa-solid <?= $at ? 'fa-check' : $icon ?>"></i></span>
          <small><?= $label ?></small>
          <b><?= $at ? date('h:i A', strtotime($at)) : '--:--' ?></b>
        </li>
      <?php endforeach; ?>
    </ol>

    <div class="pw-actions">
      <?php if ($state === 'not_started'): ?>
        <button type="button" class="pw-btn pw-in js-punch" data-type="in"><span class="pw-ripple"></span><i class="fa-solid fa-fingerprint"></i>Punch In</button>
      <?php elseif ($state === 'working'): ?>
        <button type="button" class="pw-btn pw-break js-punch" data-type="break_out"><i class="fa-solid fa-mug-hot"></i>Break Out</button>
        <button type="button" class="pw-btn pw-out js-punch" data-type="out" data-confirm-punch="Punch out for today?"><i class="fa-solid fa-right-from-bracket"></i>Punch Out</button>
      <?php elseif ($state === 'on_break'): ?>
        <button type="button" class="pw-btn pw-resume js-punch" data-type="break_in"><span class="pw-ripple"></span><i class="fa-solid fa-rotate-left"></i>Break In <em class="js-pw-break">00:00</em></button>
        <button type="button" class="pw-btn pw-out ghost js-punch" data-type="out" data-confirm-punch="You are on a break. End the break and punch out?"><i class="fa-solid fa-right-from-bracket"></i>Punch Out</button>
      <?php else: ?>
        <div class="pw-done"><i class="fa-solid fa-champagne-glasses"></i>Great work today! See you tomorrow.</div>
      <?php endif; ?>
    </div>

    <div class="pw-stats">
      <div><small>Late</small><b class="<?= $late ? 'text-danger' : '' ?>"><?= $row && $row['punch_in'] ? hm($late, 'On time') : '—' ?></b></div>
      <div><small>Break<?= count($breaks) > 1 ? 's (' . count($breaks) . ')' : '' ?></small><b class="js-pw-breaktotal"><?= hm($snap['breakSecs'] / 60, '0m') ?></b></div>
      <div><small><?= $state === 'done' ? 'Overtime' : 'Shift Ends' ?></small><b><?= $state === 'done' ? hm($row['overtime_minutes'] ?? 0, '0m') : date('h:i A', strtotime($shift['end_time'])) ?></b></div>
    </div>
  </div>
</div>
