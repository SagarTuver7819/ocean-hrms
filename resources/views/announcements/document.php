<?php
$lh = $letterhead;
$company = $lh['company'] ?? 'Ocean Infotech';
$item = $item ?? [];
$title = (string) ($item['title'] ?? 'Announcement');
$desc = (string) ($item['description'] ?? '');
$published = !empty($item['publish_date']) ? format_date($item['publish_date']) : '—';
$expiry = !empty($item['expiry_date']) ? format_date($item['expiry_date']) : null;
$dept = $item['department_name'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?> · <?= e($company) ?></title>
  <?php \App\Core\View::partial('partials/letterhead-css', ['letterhead' => $lh]); ?>
  <style>
    body { font-size: 12px; }
    .ann-doc-title { text-align: center; margin: 0 0 6mm; }
    .ann-doc-title b {
      display: block; font-size: 16px; font-weight: 800;
      letter-spacing: .1em; text-transform: uppercase;
    }
    .ann-doc-title span {
      display: inline-block; margin-top: 2mm;
      background: var(--soft); color: #9a5b00;
      border: 1px solid #f7cf8f; border-radius: 20px;
      padding: .8mm 4mm; font-weight: 800; font-size: 11px;
    }
    .ann-doc-meta {
      display: flex; flex-wrap: wrap; gap: 3mm 8mm;
      justify-content: space-between; align-items: center;
      margin-bottom: 5mm; padding-bottom: 3mm;
      border-bottom: 1px solid var(--line);
      font-size: 11px; font-weight: 700; color: #4b5563;
    }
    .ann-doc-meta small { color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; font-size: 9.5px; display: block; }
    .ann-doc-heading {
      margin: 0 0 4mm; font-size: 18px; font-weight: 800;
      color: var(--ink); line-height: 1.3;
    }
    .ann-doc-body {
      font-size: 12.5px; line-height: 1.65; color: #374151;
      white-space: pre-wrap; word-break: break-word;
    }
    .ann-doc-foot {
      margin-top: 10mm; padding-top: 4mm; border-top: 1px dashed var(--line);
      font-size: 10px; color: #6b7280; text-align: center;
    }
    .ann-doc-badge {
      display: inline-block; padding: .5mm 2.5mm; border-radius: 999px;
      background: #fff4e5; color: #c47800; font-weight: 800; font-size: 10px;
    }
  </style>
</head>
<body>

<div class="toolbar">
  <span><b>Announcement</b> · <?= e($company) ?></span>
  <div class="tb-actions">
    <span class="tb-pages" id="pageCount">Preparing A4…</span>
    <a class="tb-btn ghost" href="<?= e($backUrl) ?>">← Back</a>
    <button type="button" onclick="window.print()">Print / Save PDF</button>
  </div>
</div>

<template id="lhTpl"><?php \App\Core\View::partial('partials/letterhead-layer', ['letterhead' => $lh, 'footerNote' => 'Company Announcement']); ?></template>
<div id="pages" data-mode="<?= e($lh['mode']) ?>" data-top="<?= (int) $lh['top_mm'] ?>" data-bottom="<?= (int) $lh['bottom_mm'] ?>" data-side="<?= (int) $lh['side_mm'] ?>"></div>

<div class="flow" id="flow">
  <div class="ann-doc-title">
    <b>Company Announcement</b>
    <span><?= e($company) ?></span>
  </div>

  <div class="ann-doc-meta">
    <div><small>Publish Date</small><?= e($published) ?></div>
    <?php if ($expiry): ?>
      <div><small>Valid Until</small><?= e($expiry) ?></div>
    <?php endif; ?>
    <div>
      <small>Audience</small>
      <?= $dept ? e($dept) : '<span class="ann-doc-badge">All Employees</span>' ?>
    </div>
  </div>

  <h1 class="ann-doc-heading"><?= e($title) ?></h1>
  <div class="ann-doc-body"><?= nl2br(e($desc)) ?></div>

  <p class="ann-doc-foot">
    This is an official notice from <?= e($company) ?> · Generated from Ocean HRMS on <?= date('d-m-Y') ?>
  </p>
</div>

<script src="<?= asset('assets/js/paged.js') ?>?v=<?= @filemtime(BASE_PATH . '/public/assets/js/paged.js') ?>"></script>
<?php if (isset($_GET['print'])): ?><script>document.addEventListener('paged:ready', () => setTimeout(() => window.print(), 300));</script><?php endif; ?>
</body>
</html>
