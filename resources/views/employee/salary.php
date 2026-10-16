<div class="page-head">
  <div>
    <h1>Salary Slips</h1>
    <p>Download your monthly salary slip · A slip is available once the month is complete</p>
  </div>
</div>

<?php if (!$months): ?>
  <div class="ep-card cp tone-orange">
    <div class="cp-body text-center py-5">
      <span class="slip-empty-ico"><i class="fa-solid fa-file-invoice"></i></span>
      <h3 class="h6 fw-bold mt-3 mb-1">No salary slip yet</h3>
      <p class="text-muted small mb-0">Your first salary slip will appear here after your first month is complete.</p>
    </div>
  </div>
<?php else: ?>
  <div class="slip-grid">
    <?php foreach ($months as $i => $m): $ts = strtotime($m . '-01'); ?>
      <div class="slip-card <?= $i === 0 ? 'latest' : '' ?>">
        <span class="slip-ico"><i class="fa-solid fa-file-invoice"></i></span>
        <div class="slip-info">
          <b><?= date('F Y', $ts) ?></b>
          <small><?= $i === 0 ? 'Latest salary slip' : 'Salary slip' ?></small>
        </div>
        <div class="slip-actions">
          <a href="<?= url('employee/salary/' . $m) ?>" target="_blank" class="btn btn-sm btn-soft" title="View"><i class="fa-solid fa-eye"></i></a>
          <a href="<?= url('employee/salary/' . $m . '?print=1') ?>" target="_blank" class="btn btn-sm btn-brand" title="Download PDF"><i class="fa-solid fa-download me-1"></i>Download</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
