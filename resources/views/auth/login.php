<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'Login - Ocean HRMS') ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('assets/css/login.css') ?>">
</head>
<body class="login-page">
  <div class="login-bg" aria-hidden="true">
    <div class="login-bg-glow login-bg-glow-a"></div>
    <div class="login-bg-glow login-bg-glow-b"></div>
    <div class="login-bg-grid"></div>
  </div>

  <div class="login-center">
    <div class="login-card anim-in">
      <span class="login-hub-badge">Command Hub</span>

      <div class="login-brand">
        <div class="login-since">Ocean Infotech</div>
        <img src="<?= asset($app['brand']['logo']) ?>" alt="Ocean Infotech" class="login-logo">
        <h1 class="login-portal-title">Human Resource Management</h1>
        <p class="login-tagline">Be seen, get connected · Precision HR Node</p>
      </div>

      <?php if (!empty($flash)): ?>
        <div class="login-alert <?= ($flash['type'] ?? '') === 'success' ? 'success' : '' ?>">
          <i class="fa-solid fa-circle-exclamation mt-1"></i>
          <span><?= e($flash['message']) ?></span>
        </div>
      <?php elseif (!empty($timeout)): ?>
        <div class="login-alert">
          <i class="fa-solid fa-clock mt-1"></i>
          <span>Session expired. Please sign in again.</span>
        </div>
      <?php endif; ?>

      <form class="login-form" method="post" action="<?= url('login') ?>" autocomplete="off">
        <?= csrf_field() ?>

        <div class="login-field">
          <label>Login as</label>
          <div class="login-roles">
            <label class="login-role">
              <input type="radio" name="portal" value="admin" checked>
              <div class="login-role-box">
                <span class="login-role-icon"><i class="fa-solid fa-shield-halved"></i></span>
                <b>Admin Login</b>
                <small>Admin &amp; HR staff</small>
              </div>
            </label>
            <label class="login-role">
              <input type="radio" name="portal" value="employee">
              <div class="login-role-box">
                <span class="login-role-icon"><i class="fa-solid fa-id-badge"></i></span>
                <b>Employee Login</b>
                <small>Staff portal access</small>
              </div>
            </label>
          </div>
        </div>

        <div class="login-field">
          <label for="login">Credential ID</label>
          <div class="login-input-wrap">
            <i class="fa-solid fa-user-shield"></i>
            <input id="login" type="text" name="login" placeholder="Email / Username / Employee Code" required>
          </div>
        </div>

        <div class="login-field">
          <label for="password">Encryption Key</label>
          <div class="login-input-wrap">
            <i class="fa-solid fa-lock"></i>
            <input id="password" type="password" name="password" placeholder="Encryption Key" required>
          </div>
        </div>

        <button type="submit" class="login-btn">
          <span class="login-btn-text">Validate &amp; Initiate Session <i class="fa-solid fa-bolt"></i></span>
        </button>
      </form>

      <div class="login-card-foot">
        <span class="foot-label">Designed &amp; Developed by</span>
        <span class="foot-brand">Ocean Infotech</span>
      </div>
    </div>

    <p class="login-copy">© <?= date('Y') ?> Ocean Infotech · Secure Admin &amp; Employee portals</p>
  </div>
</body>
</html>
