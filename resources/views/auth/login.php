<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'Login - Ocean HRMS') ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('assets/css/login.css') ?>?v=<?= @filemtime(BASE_PATH . '/public/assets/css/login.css') ?>">
</head>
<body class="login-page">

  <div class="bg-scene" aria-hidden="true">
    <div class="bg-orb one"></div>
    <div class="bg-orb two"></div>
    <div class="bg-orb three"></div>

    <i class="bg-icon slate gear-xl fa-solid fa-users"></i>
    <i class="bg-icon gear-md fa-solid fa-id-badge"></i>
    <i class="bg-icon slate gear-sm fa-solid fa-briefcase"></i>
    <i class="bg-icon flame-a fa-solid fa-code"></i>
    <i class="bg-icon flame-b fa-solid fa-laptop-code"></i>
    <i class="bg-icon slate drop-a fa-solid fa-calendar-check"></i>
    <i class="bg-icon slate drop-b fa-solid fa-chart-line"></i>
    <i class="bg-icon shield fa-solid fa-shield-halved"></i>
    <i class="bg-icon hydrant fa-solid fa-user-tie"></i>
    <i class="bg-icon extinguisher fa-solid fa-building"></i>
    <i class="bg-icon slate nut fa-solid fa-indian-rupee-sign"></i>

    <div class="hr-line">
      <div class="hr-desk">
        <span class="hr-desk-lamp"></span>
        <svg class="hr-desk-svg" viewBox="0 0 150 110" fill="none">
          <rect x="8" y="48" width="118" height="14" rx="3" fill="#475569"/>
          <rect x="18" y="62" width="10" height="40" rx="2" fill="#64748b"/>
          <rect x="106" y="62" width="10" height="40" rx="2" fill="#64748b"/>
          <rect x="28" y="18" width="72" height="48" rx="6" fill="#1e293b"/>
          <rect x="34" y="24" width="60" height="34" rx="3" fill="#0f172a"/>
          <rect class="hr-screen" x="38" y="28" width="52" height="26" rx="2" fill="#22c55e" opacity="0.55"/>
          <rect x="52" y="66" width="28" height="4" rx="1" fill="#94a3b8"/>
          <circle cx="128" cy="40" r="10" fill="#F5A01B" opacity="0.95"/>
          <text x="128" y="44" text-anchor="middle" font-size="9" font-weight="800" fill="#fff" font-family="Outfit,sans-serif">HR</text>
        </svg>
        <span class="hr-paper p1"></span>
        <span class="hr-paper p2"></span>
      </div>

      <div class="hr-belt"></div>
      <div class="hr-lane">
        <span class="hr-item" style="--d:-0s">
          <span class="hr-item-ico">
            <svg viewBox="0 0 40 40"><rect x="6" y="2" width="28" height="36" rx="3" fill="#fff" stroke="#cbd5e1"/><circle cx="20" cy="14" r="6" fill="#ffedd5"/><rect x="11" y="24" width="18" height="3" rx="1" fill="#e2e8f0"/><rect x="13" y="30" width="14" height="3" rx="1" fill="#e2e8f0"/></svg>
          </span>
          <b class="hr-item-label">CV</b>
        </span>
        <span class="hr-item" style="--d:-3s">
          <span class="hr-item-ico">
            <svg viewBox="0 0 40 40"><rect x="8" y="4" width="24" height="32" rx="3" fill="#fff" stroke="#94a3b8"/><circle cx="20" cy="14" r="5" fill="#64748b"/><rect x="12" y="22" width="16" height="8" rx="1" fill="#ffedd5"/></svg>
          </span>
          <b class="hr-item-label">ID</b>
        </span>
        <span class="hr-item hot" style="--d:-6s">
          <span class="hr-item-ico">
            <svg viewBox="0 0 40 40"><rect x="7" y="5" width="26" height="30" rx="4" fill="#F5A01B"/><circle cx="20" cy="15" r="5" fill="#fff7ed"/><rect x="12" y="23" width="16" height="3" rx="1" fill="#fff" opacity=".85"/><rect x="14" y="29" width="12" height="3" rx="1" fill="#fff" opacity=".55"/></svg>
          </span>
          <b class="hr-item-label">JOIN</b>
        </span>
        <span class="hr-item" style="--d:-9s">
          <span class="hr-item-ico">
            <svg viewBox="0 0 40 40"><rect x="10" y="4" width="20" height="32" rx="3" fill="#1e293b"/><circle cx="20" cy="16" r="7" fill="none" stroke="#22c55e" stroke-width="2"/><circle cx="20" cy="16" r="2" fill="#22c55e"/><rect x="14" y="28" width="12" height="3" rx="1" fill="#94a3b8"/></svg>
          </span>
          <b class="hr-item-label">PUNCH</b>
        </span>
        <span class="hr-item" style="--d:-12s">
          <span class="hr-item-ico">
            <svg viewBox="0 0 40 40"><rect x="8" y="4" width="24" height="32" rx="3" fill="#fff" stroke="#f59e0b"/><path d="M14 14h12M14 20h10M14 26h8" stroke="#fbbf24" stroke-width="2" stroke-linecap="round"/></svg>
          </span>
          <b class="hr-item-label">LEAVE</b>
        </span>
        <span class="hr-item hot" style="--d:-15s">
          <span class="hr-item-ico">
            <svg viewBox="0 0 40 40"><rect x="6" y="6" width="28" height="28" rx="3" fill="#16a34a"/><text x="20" y="24" text-anchor="middle" font-size="14" font-weight="800" fill="#fff" font-family="Outfit,sans-serif">₹</text><rect x="12" y="28" width="16" height="3" rx="1" fill="#bbf7d0"/></svg>
          </span>
          <b class="hr-item-label">PAY</b>
        </span>
      </div>

      <div class="hr-gate">
        <svg viewBox="0 0 70 100" width="70" height="100">
          <path d="M8 100 V22 Q8 12 18 12 H52 Q62 12 62 22 V100" fill="none" stroke="#475569" stroke-width="5" stroke-linecap="round"/>
          <rect x="16" y="0" width="38" height="16" rx="8" fill="#F5A01B"/>
          <text x="35" y="11.5" text-anchor="middle" font-size="10" font-weight="800" fill="#fff" font-family="Outfit,sans-serif">HR</text>
          <circle class="hr-lamp" cx="58" cy="6" r="3.5" fill="#22c55e"/>
        </svg>
        <span class="hr-beam"></span>
      </div>

      <div class="hr-done">
        <span class="hr-done-card">
          <i></i><i></i><i></i>
          <b class="hr-item-label ok">PAID</b>
        </span>
        <span class="hr-done-box">
          <strong>NEFT</strong>
          <small>DONE</small>
        </span>
      </div>
    </div>
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
