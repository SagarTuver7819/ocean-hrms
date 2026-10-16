<?php
$lh = $letterhead;
$footerNote = $footerNote ?? 'Human Resources';
?>
<?php if ($lh['mode'] === 'full'): ?>
  <img class="lh-full" src="<?= e($lh['full']) ?>" alt="">
<?php elseif ($lh['mode'] === 'header_footer'): ?>
  <?php if ($lh['header']): ?><img class="lh-header" src="<?= e($lh['header']) ?>" alt=""><?php endif; ?>
  <?php if ($lh['footer']): ?><img class="lh-footer" src="<?= e($lh['footer']) ?>" alt=""><?php endif; ?>
<?php else: ?>
  <div class="lh-header lh-auto-head">
    <img src="<?= e($lh['logo']) ?>" alt="">
    <div class="co"><b><?= e($lh['company']) ?></b><small><?= e($lh['tagline']) ?></small></div>
    <div class="ct">
      <?php if ($lh['address']): ?><?= nl2br(e($lh['address'])) ?><br><?php endif; ?>
      <?= e(implode(' · ', array_filter([$lh['phone'], $lh['email'], $lh['website']]))) ?>
    </div>
  </div>
  <div class="lh-footer lh-auto-foot">
    <span><?= e($lh['company']) ?> · Human Resources</span>
    <span><?= e($footerNote) ?></span>
  </div>
<?php endif; ?>
