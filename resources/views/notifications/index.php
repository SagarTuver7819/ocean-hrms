<div class="page-head">
  <div>
    <h1>Notifications</h1>
    <p>Attendance punches, leave requests, approvals and other alerts</p>
  </div>
  <form method="post" action="<?= url('notifications/read-all') ?>">
    <?= csrf_field() ?>
    <button class="btn btn-sm btn-brand"><i class="fa-solid fa-check-double me-1"></i>Mark all as read</button>
  </form>
</div>

<div class="panel">
  <div class="panel-body p-0 notif-page">
    <?php \App\Core\View::partial('partials/notification-items', ['items' => $items]); ?>
  </div>
</div>
