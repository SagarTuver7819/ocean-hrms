<?php
$lh = $letterhead;
$p = $person;
$company = $lh['company'];
$salute = match ($p['gender'] ?? '') { 'male' => 'Mr.', 'female' => 'Ms.', default => '' };
$fullName = trim($salute . ' ' . $p['name']);
$firstName = trim($salute . ' ' . explode(' ', $p['name'])[0]);
$d = fn(?string $date) => $date ? date('d-m-Y', strtotime($date)) : '—';
$rupee = fn($n) => '₹ ' . inr($n);
$subjects = [
    'offer'       => 'Offer of Employment',
    'appointment' => 'Appointment Letter',
    'experience'  => 'Experience Certificate',
    'relieving'   => 'Relieving Letter',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($typeLabel . ' · ' . $ref['no']) ?></title>
  <?php \App\Core\View::partial('partials/letterhead-css', ['letterhead' => $lh]); ?>
  <style>
    body { font-size: 12px; line-height: 1.6; }
    .watermark { position: absolute; inset: 0; display: grid; place-items: center; z-index: 0; pointer-events: none; }
    .watermark img { width: 110mm; opacity: .055; }
    .lt-meta { display: flex; justify-content: space-between; font-weight: 700; margin: 0 0 4mm; padding-bottom: 2mm; border-bottom: 1px solid var(--line); }
    .lt-to { display: flex; justify-content: space-between; gap: 10mm; margin-bottom: 5mm; }
    .lt-to .addr b { display: block; font-size: 12.5px; text-transform: uppercase; }
    .lt-to .addr span { display: block; color: #374151; }
    .lt-to .side { text-align: right; font-weight: 700; white-space: nowrap; }
    .lt-subject { text-align: center; font-weight: 800; font-size: 13px; letter-spacing: .04em; text-transform: uppercase; text-decoration: underline; text-underline-offset: 3px; margin: 2mm 0 5mm; }
    .lt-body p { margin: 0 0 3mm; text-align: justify; }
    .lt-body h4 { margin: 4mm 0 1mm; font-size: 12px; font-weight: 800; color: var(--ink); }
    .lt-body h4 span { color: var(--brand); margin-right: 4px; }
    .lt-body ul, .lt-body ol { margin: 0 0 3mm; padding-left: 6mm; }
    .lt-body li { margin-bottom: 1mm; text-align: justify; }
    .lt-hl { background: var(--soft); border-left: 4px solid var(--brand); padding: 3mm 4mm; margin: 3mm 0 4mm; border-radius: 0 6px 6px 0; }
    .lt-hl table { width: 100%; border-collapse: collapse; }
    .lt-hl td { padding: 1mm 0; vertical-align: top; }
    .lt-hl td:first-child { width: 42%; color: #4b5563; font-weight: 600; }
    .lt-hl td:last-child { font-weight: 800; }
    .lt-sign { display: flex; justify-content: space-between; align-items: flex-end; gap: 10mm; margin-top: 12mm; page-break-inside: avoid; }
    .lt-sign .blk { min-width: 60mm; }
    .lt-sign .blk .line { border-top: 1px solid var(--ink); margin-top: 16mm; padding-top: 2mm; font-weight: 800; }
    .lt-sign .blk small { display: block; color: #4b5563; font-weight: 600; }
    .lt-accept { margin-top: 8mm; border: 1px dashed #9ca3af; border-radius: 8px; padding: 4mm 5mm; page-break-inside: avoid; }
    .lt-accept h5 { margin: 0 0 2mm; font-size: 11.5px; text-transform: uppercase; letter-spacing: .06em; }
    .lt-accept .row { display: flex; justify-content: space-between; gap: 8mm; margin-top: 10mm; }
    .lt-accept .row div { flex: 1; border-top: 1px solid var(--ink); padding-top: 1.5mm; text-align: center; font-weight: 700; font-size: 11px; }
  </style>
</head>
<body>

<form class="toolbar" method="get">
  <?php foreach (array_diff_key($_GET, ['date' => 1, 'last_day' => 1]) as $k => $v): if (is_scalar($v)): ?>
    <input type="hidden" name="<?= e($k) ?>" value="<?= e((string) $v) ?>">
  <?php endif; endforeach; ?>
  <span><b><?= e($ref['no']) ?></b> · <?= e($typeLabel) ?> · <?= e($p['code']) ?></span>
  <div class="tb-actions">
    <label>Letter date <input type="date" name="date" value="<?= e($ref['date']) ?>" onchange="this.form.submit()"></label>
    <?php if ($type === 'relieving'): ?>
      <label>Last working day <input type="date" name="last_day" value="<?= e($lastDay) ?>" onchange="this.form.submit()"></label>
    <?php endif; ?>
    <span class="tb-pages" id="pageCount">Preparing A4…</span>
    <a class="tb-btn ghost" href="<?= e($backUrl) ?>">← Back</a>
    <button type="button" onclick="window.print()">Print / Save PDF</button>
  </div>
</form>

<template id="lhTpl"><?php \App\Core\View::partial('partials/letterhead-layer', ['letterhead' => $lh, 'footerNote' => $typeLabel]); ?><div class="watermark"><img src="<?= e($lh['logo']) ?>" alt=""></div></template>
<div id="pages" data-mode="<?= e($lh['mode']) ?>" data-top="<?= (int) $lh['top_mm'] ?>" data-bottom="<?= (int) $lh['bottom_mm'] ?>" data-side="<?= (int) $lh['side_mm'] ?>"></div>

<div class="flow lt-body" id="flow">

      <div class="lt-meta">
        <span>Ref: <?= e($ref['no']) ?></span>
        <span>Date: <?= $d($ref['date']) ?></span>
      </div>

      <?php if ($type === 'experience'): ?>
        <div class="lt-subject" style="margin-top:6mm">To Whom It May Concern</div>
      <?php else: ?>
        <div class="lt-to">
          <div class="addr">
            To,
            <b><?= e($fullName) ?></b>
            <?php if ($p['designation']): ?><span><?= e($p['designation']) ?><?= $p['department'] ? ' · ' . e($p['department']) : '' ?></span><?php endif; ?>
            <?php if ($p['address']): ?><span><?= e($p['address']) ?></span><?php endif; ?>
            <span>Mobile: <?= e($p['mobile'] ?: '—') ?></span>
            <span><?= $type === 'offer' && !$employee ? 'Application No' : 'Emp. Code' ?>: <b style="display:inline"><?= e($p['code']) ?></b></span>
          </div>
          <div class="side">
            <?php if (in_array($type, ['offer', 'appointment'], true)): ?>Date of Joining:<br><?= $d($joiningDate) ?><?php endif; ?>
            <?php if ($type === 'relieving'): ?>Last Working Day:<br><?= $d($lastDay) ?><?php endif; ?>
          </div>
        </div>
        <div class="lt-subject">Subject : <?= e($subjects[$type]) ?></div>
      <?php endif; ?>

      <?php require __DIR__ . '/body-' . $type . '.php'; ?>

      <div class="lt-sign keep-prev">
        <div class="blk">
          For, <b><?= e($company) ?></b>
          <div class="line"><?= e($letter['signatory']) ?><small><?= e($letter['signatoryDesignation']) ?></small></div>
        </div>
        <?php if ($type === 'appointment'): ?>
          <div class="blk" style="text-align:right">
            Accepted by,
            <div class="line"><?= e($fullName) ?><small>Employee Signature &amp; Date</small></div>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($type === 'offer'): ?>
        <div class="lt-accept">
          <h5>Acceptance</h5>
          I, <b><?= e($p['name']) ?></b>, have read and understood the terms of this offer and accept the position of
          <b><?= e($p['designation'] ?: '—') ?></b><?php $payAcc = (float) ($monthlySalary ?? 0); ?> at a fixed monthly salary of <b><?= $payAcc > 0 ? $rupee($payAcc) : 'as agreed' ?></b>. I will join on <b><?= $d($joiningDate) ?></b>.
          <div class="row"><div>Candidate Signature</div><div>Date</div><div>Place</div></div>
        </div>
      <?php endif; ?>


</div>

<script src="<?= asset('assets/js/paged.js') ?>?v=<?= @filemtime(BASE_PATH . '/public/assets/js/paged.js') ?>"></script>
<?php if (isset($_GET['print'])): ?><script>document.addEventListener('paged:ready', () => setTimeout(() => window.print(), 300));</script><?php endif; ?>
</body>
</html>
