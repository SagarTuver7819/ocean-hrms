<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'Ocean HRMS') ?> · Ocean Infotech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>?v=<?= @filemtime(BASE_PATH . '/public/assets/css/app.css') ?>">
  <script>
    try { if (localStorage.getItem('oceanSidebar') === 'collapsed' && window.innerWidth >= 992) document.documentElement.classList.add('sb-collapsed'); } catch (e) {}
  </script>
</head>
<body
  data-flash-type="<?= e($flash['type'] ?? '') ?>"
  data-flash-message="<?= e($flash['message'] ?? '') ?>"
>
<?php $isEmp = \App\Core\Auth::isEmployeePortal(); $page = $page ?? ''; ?>
<div class="app-shell">
  <?php \App\Core\View::partial('partials/sidebar', compact('app', 'page', 'isEmp', 'authUser')); ?>
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <div class="main-wrap">
    <header class="topbar">
      <div class="topbar-left">
        <button class="icon-btn" id="sidebarToggle" type="button" title="Toggle menu" aria-label="Toggle menu">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>

      <div class="topbar-center">
        <div class="info-bar">
          <span class="info-item"><span class="info-ico t-red"><i class="fa-regular fa-clock"></i></span><span class="lbl">Time :</span> <b id="liveClock"><?= date('h:i:s A') ?></b></span>
          <span class="info-item"><span class="info-ico t-green"><i class="fa-regular fa-calendar"></i></span><span class="lbl">Day :</span> <b id="liveDay"><?= date('l') ?></b></span>
          <span class="info-item info-user"><span class="info-ico t-violet"><i class="fa-solid fa-user-check"></i></span><span class="lbl">Logged in as :</span> <b><?= e($authUser['name'] ?? 'User') ?></b><?php if (!empty($authUser['username'])): ?> <span class="info-handle">(@<?= e($authUser['username']) ?>)</span><?php endif; ?></span>
        </div>
        <div class="top-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <form action="<?= url($isEmp ? 'employee/profile' : 'employees') ?>" method="get">
            <input type="search" name="q" placeholder="Search employee name / code…" value="<?= e($_GET['q'] ?? '') ?>">
          </form>
        </div>
      </div>

      <div class="topbar-right">
        <?php
          $notifItems = $authUser ? \App\Controllers\NotificationController::recent((int) $authUser['id']) : [];
          $notifUnread = $authUser ? \App\Controllers\NotificationController::unread((int) $authUser['id']) : 0;
        ?>
        <div class="dropdown notif-wrap" id="notifWrap" data-feed="<?= url('notifications/feed') ?>" data-last="<?= (int) ($notifItems[0]['id'] ?? 0) ?>">
          <button type="button" class="icon-btn" title="Notifications" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Notifications">
            <i class="fa-regular fa-bell"></i>
            <span class="notif-count" id="notifCount"><?= $notifUnread ? ($notifUnread > 99 ? '99+' : $notifUnread) : '' ?></span>
          </button>
          <div class="dropdown-menu dropdown-menu-end notif-menu">
            <div class="notif-menu-head">
              <b><i class="fa-solid fa-bell me-1"></i>Notifications</b>
              <form method="post" action="<?= url('notifications/read-all') ?>" class="m-0">
                <?= csrf_field() ?><input type="hidden" name="back" value="<?= e(preg_replace('#^.*?/public/?#', '', (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH))) ?>">
                <button type="submit">Mark all read</button>
              </form>
            </div>
            <div class="notif-list" id="notifList"><?php \App\Core\View::partial('partials/notification-items', ['items' => $notifItems]); ?></div>
            <a href="<?= url('notifications') ?>" class="notif-menu-foot">View all notifications</a>
          </div>
        </div>
        <a href="<?= url('change-password') ?>" class="icon-btn" title="Change Password"><i class="fa-solid fa-key"></i></a>
        <div class="user-chip" title="<?= e(($authUser['name'] ?? 'User') . ' · ' . ($isEmp ? 'Employee' : ($_SESSION['roles'][0] ?? 'admin'))) ?>">
          <?php $headerPhoto = !empty($authUser['employee_id']) ? \App\Core\Database::value('SELECT profile_photo FROM employees WHERE id = ?', [$authUser['employee_id']]) : null; ?>
          <?php if ($headerPhoto): ?>
            <div class="avatar has-photo"><img src="<?= asset($headerPhoto) ?>" alt=""></div>
          <?php else: ?>
            <div class="avatar"><?= strtoupper(substr($authUser['name'] ?? 'U', 0, 1)) ?></div>
          <?php endif; ?>
        </div>
        <a href="<?= url('logout') ?>" class="icon-btn icon-btn-danger" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
      </div>
    </header>

    <main class="content">
      <?= $content ?>
    </main>

    <div class="app-footer">Designed &amp; Developed by Ocean Infotech</div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="<?= asset('assets/js/app.js') ?>?v=<?= @filemtime(BASE_PATH . '/public/assets/js/app.js') ?>"></script>
</body>
</html>
