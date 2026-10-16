<?php
$icon = function (string $title): string {
    foreach (['Punch In' => 'fa-right-to-bracket', 'Punch Out' => 'fa-right-from-bracket', 'Leave' => 'fa-calendar-check', 'application' => 'fa-user-plus', 'Welcome' => 'fa-hand-sparkles'] as $needle => $ico) {
        if (stripos($title, $needle) !== false) return $ico;
    }
    return 'fa-bell';
};
?>
<?php if (!$items): ?>
  <div class="notif-empty"><i class="fa-regular fa-bell-slash d-block fs-4 mb-2"></i>No notifications yet</div>
<?php endif; ?>
<?php foreach ($items as $n): ?>
  <a href="<?= url('notifications/' . $n['id'] . '/open') ?>" class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
    <span class="ni-ico"><i class="fa-solid <?= $icon($n['title']) ?>"></i></span>
    <div class="min-w-0">
      <b><?= e($n['title']) ?></b>
      <p><?= e($n['message']) ?></p>
      <small><?= time_ago($n['created_at']) ?></small>
    </div>
  </a>
<?php endforeach; ?>
