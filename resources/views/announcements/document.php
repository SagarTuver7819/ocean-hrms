<?php
$lh = $letterhead;
$company = $lh['company'] ?? 'Ocean Infotech';
$item = $item ?? [];
$title = (string) ($item['title'] ?? 'Announcement');
$desc = trim((string) ($item['description'] ?? ''));
$published = !empty($item['publish_date']) ? format_date($item['publish_date']) : '—';
$expiry = !empty($item['expiry_date']) ? format_date($item['expiry_date']) : null;
$dept = $item['department_name'] ?? null;

// Normalise description into clean paragraphs (avoid blank pages from extra newlines)
$paras = preg_split("/\R{2,}/", $desc) ?: [];
$paras = array_values(array_filter(array_map('trim', $paras), static fn($p) => $p !== ''));
if (!$paras && $desc !== '') {
    $paras = [preg_replace("/\R+/", ' ', $desc) ?: $desc];
}
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
    .ann-sheet {
      font-family: "Plus Jakarta Sans", Arial, Helvetica, sans-serif;
      color: var(--ink);
    }
    .ann-kicker {
      text-align: center; margin: 0 0 4mm;
    }
    .ann-kicker .label {
      display: block; font-size: 11px; font-weight: 800;
      letter-spacing: .14em; text-transform: uppercase; color: #9a5b00;
    }
    .ann-kicker .co {
      display: inline-block; margin-top: 2mm;
      background: var(--soft); color: #9a5b00;
      border: 1px solid #f7cf8f; border-radius: 20px;
      padding: .7mm 4mm; font-weight: 800; font-size: 11px;
    }
    .ann-meta {
      display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 3mm;
      margin: 0 0 5mm; padding: 3mm 0;
      border-top: 1.5px solid var(--brand);
      border-bottom: 1px solid var(--line);
    }
    .ann-meta div { min-width: 0; }
    .ann-meta small {
      display: block; margin-bottom: .8mm;
      font-size: 9px; font-weight: 800; letter-spacing: .08em;
      text-transform: uppercase; color: #94a3b8;
    }
    .ann-meta b {
      display: block; font-size: 12px; font-weight: 700; color: var(--ink);
    }
    .ann-badge {
      display: inline-block; padding: .4mm 2.4mm; border-radius: 999px;
      background: #fff4e5; color: #c47800; font-weight: 800; font-size: 10.5px;
    }
    .ann-title {
      margin: 0 0 4mm; padding-bottom: 2.5mm;
      font-size: 17px; font-weight: 800; line-height: 1.35;
      color: var(--ink); border-bottom: 2px solid #f5c56a;
    }
    .ann-body { margin: 0 0 4mm; }
    .ann-body p {
      margin: 0 0 3.2mm; font-size: 12.5px; line-height: 1.7;
      color: #374151; text-align: justify; hyphens: auto;
    }
    .ann-body p:last-child { margin-bottom: 0; }
    .ann-sign {
      margin-top: 8mm; padding-top: 4mm;
      border-top: 1px dashed var(--line);
      display: flex; justify-content: space-between; gap: 8mm;
      font-size: 11px; color: #4b5563;
    }
    .ann-sign small {
      display: block; font-size: 9px; font-weight: 800;
      letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; margin-bottom: 1mm;
    }
    .ann-sign b { font-size: 12px; font-weight: 800; color: var(--ink); }
    .ann-note {
      margin-top: 6mm; text-align: center;
      font-size: 9.5px; color: #6b7280; font-weight: 600;
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
  <!-- One keep-together block so title + meta + heading stay on the first content page -->
  <div class="ann-sheet keep-next">
    <div class="ann-kicker">
      <span class="label">Company Announcement</span>
      <span class="co"><?= e($company) ?></span>
    </div>

    <div class="ann-meta">
      <div>
        <small>Publish Date</small>
        <b><?= e($published) ?></b>
      </div>
      <div>
        <small>Valid Until</small>
        <b><?= e($expiry ?: '—') ?></b>
      </div>
      <div>
        <small>Audience</small>
        <b><?= $dept ? e($dept) : '<span class="ann-badge">All Employees</span>' ?></b>
      </div>
    </div>

    <div class="ann-title"><?= e($title) ?></div>

    <div class="ann-body">
      <?php if (!$paras): ?>
        <p><em>No description provided.</em></p>
      <?php else: ?>
        <?php foreach ($paras as $p): ?>
          <p><?= nl2br(e($p)) ?></p>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="ann-sign">
      <div>
        <small>Issued by</small>
        <b><?= e($company) ?></b>
        Human Resources
      </div>
      <div style="text-align:right">
        <small>Document date</small>
        <b><?= date('d M Y') ?></b>
      </div>
    </div>

    <p class="ann-note">Official notice · Ocean HRMS · Computer generated</p>
  </div>
</div>

<script src="<?= asset('assets/js/paged.js') ?>?v=<?= @filemtime(BASE_PATH . '/public/assets/js/paged.js') ?>"></script>
<?php if (isset($_GET['print'])): ?><script>document.addEventListener('paged:ready', () => setTimeout(() => window.print(), 300));</script><?php endif; ?>
</body>
</html>
