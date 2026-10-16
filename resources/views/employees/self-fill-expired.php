<?php $company = setting('company_name', $app['company'] ?? 'Ocean Infotech'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#211F23">
  <title>Link expired · <?= e($company) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('assets/css/careers.css') ?>">
</head>
<body>
<header class="cr-top"><img src="<?= asset($app['brand']['logo']) ?>" alt="<?= e($company) ?>"></header>
<main class="cr-wrap">
  <section class="cr-card cr-intro">
    <div class="cr-logo"><img src="<?= asset($app['brand']['logo']) ?>" alt=""></div>
    <span class="cr-kicker">Ocean HRMS</span>
    <h1>Link expired</h1>
    <p class="cr-text text-center"><?= e($message ?? 'This form link is no longer valid.') ?></p>
    <p class="cr-note"><i class="fa-solid fa-headset me-1"></i>Please contact HR for a new QR / WhatsApp link.</p>
  </section>
</main>
</body>
</html>
