<?php
$lh = $letterhead;
$company = $lh['company'] ?? 'Ocean Infotech';

$renderNode = function (array $node, bool $leader = false, string $badge = '') use (&$renderNode): void {
    $photo = !empty($node['profile_photo']) ? asset($node['profile_photo']) : null;
    $desig = $badge !== '' ? $badge : ($node['designation_name'] ?: 'Team Member');
    $isLeader = $leader || !empty($node['is_leader']);
    ?>
    <li>
      <div class="ocp-card <?= $isLeader ? 'is-leader' : '' ?>">
        <div class="ocp-photo">
          <?php if ($photo): ?>
            <img src="<?= e($photo) ?>" alt="">
          <?php else: ?>
            <span><?= e($node['initials']) ?></span>
          <?php endif; ?>
        </div>
        <b><?= e($node['full_name']) ?></b>
        <small class="ocp-role"><?= e($desig) ?></small>
        <?php if (!empty($node['department_name']) && $badge === ''): ?>
          <small class="ocp-dept"><?= e($node['department_name']) ?></small>
        <?php endif; ?>
        <?php if (!empty($node['employee_code'])): ?>
          <small class="ocp-code"><?= e($node['employee_code']) ?></small>
        <?php endif; ?>
      </div>
      <?php if (!empty($node['children'])): ?>
        <ul>
          <?php foreach ($node['children'] as $child) {
              $renderNode($child);
          } ?>
        </ul>
      <?php endif; ?>
    </li>
    <?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Organization Chart · <?= e($company) ?></title>
  <?php \App\Core\View::partial('partials/letterhead-css', ['letterhead' => $lh]); ?>
  <style>
    body { font-size: 11px; }
    .ocp-title { text-align: center; margin: 0 0 4mm; }
    .ocp-title b { display: block; font-size: 15px; letter-spacing: .1em; text-transform: uppercase; font-weight: 800; }
    .ocp-title span {
      display: inline-block; margin-top: 1.5mm; background: var(--soft); color: #9a5b00;
      border: 1px solid #f7cf8f; border-radius: 20px; padding: .6mm 3.5mm; font-weight: 800; font-size: 10.5px;
    }
    .ocp-meta { display: flex; justify-content: space-between; font-weight: 700; margin-bottom: 4mm; font-size: 10.5px; color: #4b5563; }
    .ocp-sec { margin-top: 3mm; text-align: center; }
    .ocp-sec h5 {
      margin: 0 0 3mm; font-size: 10px; letter-spacing: .08em; text-transform: uppercase;
      color: #9a5b00; font-weight: 800;
    }
    .ocp-dual { display: flex; justify-content: center; margin-bottom: 2mm; }
    .ocp-tree-dual { gap: 8mm; }
    .ocp-tree-dual > li { padding-top: 0; }
    .ocp-tree-dual > li::before, .ocp-tree-dual > li::after { display: none; }

    .ocp-tree, .ocp-tree ul {
      padding-top: 5mm; position: relative;
      display: flex; justify-content: center; align-items: flex-start;
      list-style: none; margin: 0;
    }
    .ocp-tree { padding-top: 0; }
    .ocp-tree li { position: relative; padding: 5mm 2.5mm 0; text-align: center; list-style: none; }
    .ocp-tree li::before {
      content: ''; position: absolute; top: 0; left: 50%;
      border-left: 1.2px solid #f0c78a; height: 5mm; width: 0;
    }
    .ocp-tree li::after {
      content: ''; position: absolute; top: 0; left: 0; right: 0;
      border-top: 1.2px solid #f0c78a; height: 0;
    }
    .ocp-tree > li::before, .ocp-tree > li::after { display: none; }
    .ocp-tree li:only-child::after { display: none; }
    .ocp-tree li:first-child::after { left: 50%; }
    .ocp-tree li:last-child::after { right: 50%; }
    .ocp-tree ul::before {
      content: ''; position: absolute; top: 0; left: 50%;
      border-left: 1.2px solid #f0c78a; width: 0; height: 5mm;
    }

    .ocp-card {
      display: inline-flex; flex-direction: column; align-items: center;
      width: 34mm; padding: 2.2mm 1.8mm;
      background: #fff; border: 1px solid #e7e5ea; border-radius: 3mm;
      position: relative; z-index: 1; box-shadow: 0 1px 2px rgba(0,0,0,.04);
    }
    .ocp-card.is-leader {
      width: 40mm; border-color: #f5c56a;
      background: linear-gradient(180deg, #fffaf2, #fff);
    }
    .ocp-card.is-leader::before {
      content: ''; position: absolute; left: 0; right: 0; top: 0; height: 1.2mm;
      border-radius: 3mm 3mm 0 0; background: #F5A01B;
    }
    .ocp-photo {
      width: 14mm; height: 14mm; border-radius: 50%; overflow: hidden;
      display: grid; place-items: center; margin-bottom: 1.5mm;
      background: #fff1dc; color: #c47800; font-weight: 800; font-size: 11px;
      border: 1.5px solid #fff; box-shadow: 0 0 0 1.2px #f5c56a;
    }
    .ocp-card.is-leader .ocp-photo { width: 16mm; height: 16mm; box-shadow: 0 0 0 1.5px #F5A01B; }
    .ocp-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ocp-card b { display: block; font-size: 9.5px; font-weight: 800; line-height: 1.2; margin-bottom: .5mm; }
    .ocp-role { display: block; font-size: 8px; font-weight: 800; color: #c47800; text-transform: uppercase; letter-spacing: .02em; }
    .ocp-dept, .ocp-code { display: block; font-size: 7.5px; color: #6b7280; font-weight: 600; margin-top: .3mm; }
    .ocp-note { margin-top: 5mm; text-align: center; color: #6b7280; font-size: 9px; }
  </style>
</head>
<body>

<div class="toolbar">
  <span><b>Organization Chart</b> · <?= e($company) ?></span>
  <div class="tb-actions">
    <span class="tb-pages" id="pageCount">Preparing A4…</span>
    <a class="tb-btn ghost" href="<?= e($backUrl) ?>">← Back</a>
    <button type="button" onclick="window.print()">Print / Save PDF</button>
  </div>
</div>

<template id="lhTpl"><?php \App\Core\View::partial('partials/letterhead-layer', ['letterhead' => $lh, 'footerNote' => 'Organization Chart']); ?></template>
<div id="pages" data-mode="<?= e($lh['mode']) ?>" data-top="<?= (int) $lh['top_mm'] ?>" data-bottom="<?= (int) $lh['bottom_mm'] ?>" data-side="<?= (int) $lh['side_mm'] ?>"></div>

<div class="flow" id="flow">
  <div class="ocp-title">
    <b>Organization Chart</b>
    <span><?= e($company) ?></span>
  </div>
  <div class="ocp-meta">
    <span>Generated: <?= date('d-m-Y') ?></span>
    <span>Confidential · Internal use</span>
  </div>

  <?php
    $dualLeaders = !empty($dualLeaders) && $founder && $coFounder;
    $sharedTeam = $sharedTeam ?? [];
  ?>
  <?php if ($dualLeaders): ?>
    <div class="ocp-sec">
      <h5>Leadership</h5>
      <div class="ocp-dual">
        <?php
          $f = $founder; $f['children'] = [];
          $c = $coFounder; $c['children'] = [];
        ?>
        <ul class="ocp-tree ocp-tree-dual">
          <?php $renderNode($f, true, 'Founder'); ?>
          <?php $renderNode($c, true, 'Co-Founder'); ?>
        </ul>
      </div>
      <?php if (!empty($sharedTeam)): ?>
        <ul class="ocp-tree">
          <?php foreach ($sharedTeam as $n) {
              $renderNode($n);
          } ?>
        </ul>
      <?php endif; ?>
    </div>
  <?php elseif ($founder || $coFounder): ?>
    <div class="ocp-sec">
      <h5>Leadership</h5>
      <ul class="ocp-tree">
        <?php if ($founder) {
            $renderNode($founder, true, 'Founder');
        } ?>
        <?php if ($coFounder) {
            $renderNode($coFounder, true, 'Co-Founder');
        } ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (!$dualLeaders && $teamRoots): ?>
    <div class="ocp-sec">
      <h5>Employees</h5>
      <ul class="ocp-tree">
        <?php foreach ($teamRoots as $n) {
            $renderNode($n);
        } ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (!$founder && !$coFounder && !$teamRoots && empty($sharedTeam)): ?>
    <p class="ocp-note">No organization data available.</p>
  <?php else: ?>
    <p class="ocp-note">Printed on <?= e($company) ?> letterhead from Ocean HRMS.</p>
  <?php endif; ?>
</div>

<script src="<?= asset('assets/js/paged.js') ?>?v=<?= @filemtime(BASE_PATH . '/public/assets/js/paged.js') ?>"></script>
<?php if (isset($_GET['print'])): ?><script>document.addEventListener('paged:ready', () => setTimeout(() => window.print(), 300));</script><?php endif; ?>
</body>
</html>
