<?php $company = setting('company_name', $app['company']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#211F23">
  <title>Application Submitted · <?= e($company) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('assets/css/careers.css') ?>">
</head>
<body>
<header class="cr-top">
  <img src="<?= asset($app['brand']['logo']) ?>" alt="<?= e($company) ?>">
</header>

<main class="cr-wrap">
  <section class="cr-card cr-done">
    <div class="cr-done-ico"><i class="fa-solid fa-check"></i></div>
    <h1 class="h4 fw-bold mb-1">Thank you, <?= e($candidate['first_name']) ?>!</h1>
    <p class="text-muted mb-0">Your application for <b><?= e($candidate['position']) ?></b> has been submitted.</p>
    <div class="cr-appno"><?= e($candidate['application_no']) ?></div>
    <p class="text-muted small">Please show this application number at the HR desk. Our team will call you for the interview rounds.</p>

    <div class="cr-review text-start my-3">
      <div class="cr-review-row"><span>Name</span><b><?= e($candidate['full_name']) ?></b></div>
      <div class="cr-review-row"><span>Mobile</span><b><?= e($candidate['mobile']) ?></b></div>
      <div class="cr-review-row"><span>Email</span><b><?= e($candidate['email']) ?></b></div>
      <div class="cr-review-row"><span>Submitted</span><b><?= format_date($candidate['created_at'], 'd M Y, h:i A') ?></b></div>
    </div>

    <a href="<?= url('apply/print/' . $candidate['public_token']) ?>" target="_blank" class="cr-btn cr-btn-primary w-100 mb-2">
      <i class="fa-solid fa-print me-1"></i> Print / Download Application
    </a>
    <a href="<?= url('apply') ?>" class="cr-btn cr-btn-ghost w-100"><i class="fa-solid fa-rotate-left me-1"></i> Submit another application</a>
  </section>
</main>
<footer class="cr-foot">© <?= date('Y') ?> <?= e($company) ?> · Designed &amp; Developed by Ocean Infotech</footer>
</body>
</html>
