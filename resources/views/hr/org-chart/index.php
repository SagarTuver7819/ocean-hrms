<?php
$renderNode = function (array $node, bool $leader = false, string $badge = '') use (&$renderNode): void {
    $photo = !empty($node['profile_photo']) ? asset($node['profile_photo']) : null;
    $desig = $badge !== '' ? $badge : ($node['designation_name'] ?: 'Team Member');
    $dept = $node['department_name'] ?? '';
    $isCustomLeader = !empty($node['is_leader']);
    $canOpen = !$isCustomLeader && (can('employee.view') || can('*')) && !empty($node['id']);
    $href = $canOpen ? url('employees/' . $node['id']) : null;
    ?>
    <li>
      <div class="oc-card <?= $leader || $isCustomLeader ? 'oc-leader' : '' ?>">
        <button type="button" class="oc-photo <?= $photo ? 'has-photo js-photo-view' : '' ?>"
          <?php if ($photo): ?> data-photo="<?= e($photo) ?>" data-name="<?= e($node['full_name']) ?>"<?php endif; ?>
          title="<?= e($node['full_name']) ?>">
          <?php if ($photo): ?>
            <img src="<?= e($photo) ?>" alt="<?= e($node['full_name']) ?>">
          <?php else: ?>
            <span><?= e($node['initials']) ?></span>
          <?php endif; ?>
        </button>
        <div class="oc-meta">
          <?php if ($href): ?>
            <b><a href="<?= e($href) ?>" class="oc-name"><?= e($node['full_name']) ?></a></b>
          <?php else: ?>
            <b><?= e($node['full_name']) ?></b>
          <?php endif; ?>
          <small class="oc-role"><?= e($desig) ?></small>
          <?php if ($dept && $badge === ''): ?><small class="oc-dept"><?= e($dept) ?></small><?php endif; ?>
          <?php if (!empty($node['employee_code'])): ?>
            <small class="oc-code"><?= e($node['employee_code']) ?></small>
          <?php endif; ?>
        </div>
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

<div class="page-head">
  <div>
    <h1>Organization Chart</h1>
    <p>Founder &amp; Co-Founder (manual names) · employees by reporting manager</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('org-chart/print') ?>" class="btn btn-sm btn-outline-ink" target="_blank">
      <i class="fa-solid fa-print me-1"></i>Print on Letterhead
    </a>
    <?php if ($isAdmin): ?>
      <button type="button" class="btn btn-sm btn-brand" data-bs-toggle="modal" data-bs-target="#ocLeadersModal">
        <i class="fa-solid fa-user-tie me-1"></i>Add Founder / Co-Founder
      </button>
    <?php endif; ?>
  </div>
</div>

<?php if (!$founder && !$coFounder): ?>
  <div class="alert alert-warning">
    <i class="fa-solid fa-circle-info me-1"></i>
    Founder / Co-Founder not added yet.
    <?php if ($isAdmin): ?> Click <b>Add Founder / Co-Founder</b> and enter names.
    <?php else: ?> Ask HR to add leaders.<?php endif; ?>
  </div>
<?php endif; ?>

<div class="oc-wrap">
  <div class="oc-toolbar no-print">
    <button type="button" class="btn btn-sm btn-outline-ink" id="ocZoomOut" title="Zoom out"><i class="fa-solid fa-minus"></i></button>
    <button type="button" class="btn btn-sm btn-outline-ink" id="ocZoomIn" title="Zoom in"><i class="fa-solid fa-plus"></i></button>
    <a href="<?= url('org-chart/print') ?>" class="btn btn-sm btn-outline-ink" target="_blank"><i class="fa-solid fa-print me-1"></i>Letterhead Print</a>
  </div>

  <div class="oc-stage" id="ocStage">
    <div class="oc-scale" id="ocScale">
      <?php if ($founder || $coFounder): ?>
        <div class="oc-roots">
          <ul class="oc-tree oc-tree-roots">
            <?php if ($founder) {
                $renderNode($founder, true, 'Founder');
            } ?>
            <?php if ($coFounder) {
                $renderNode($coFounder, true, 'Co-Founder');
            } ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($teamRoots): ?>
        <div class="oc-section">
          <h3><i class="fa-solid fa-users me-1"></i> Employees</h3>
          <ul class="oc-tree">
            <?php foreach ($teamRoots as $n) {
                $renderNode($n);
            } ?>
          </ul>
        </div>
      <?php elseif (!$founder && !$coFounder): ?>
        <div class="text-center text-muted py-5">No active employees to show.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($isAdmin): ?>
<div class="modal fade" id="ocLeadersModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="<?= url('org-chart/leaders') ?>" class="modal-content js-loading" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title">Add Founder &amp; Co-Founder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted mb-3">Enter names manually (not from employee list). Optional photo. Employees below still follow <b>Reporting Manager</b>.</p>

        <div class="mb-3">
          <label class="form-label">Founder name</label>
          <input type="text" name="founder_name" class="form-control" maxlength="120"
                 value="<?= e($founderName) ?>" placeholder="e.g. Ramesh Patel">
        </div>
        <div class="mb-3">
          <label class="form-label">Founder photo (optional)</label>
          <input type="file" name="founder_photo" class="form-control" accept="image/jpeg,image/png,image/webp">
          <?php if ($founderPhoto): ?>
            <label class="form-check mt-2 mb-0">
              <input class="form-check-input" type="checkbox" name="remove_founder_photo" value="1">
              <span class="form-check-label">Remove current photo</span>
            </label>
          <?php endif; ?>
        </div>

        <hr class="my-3">

        <div class="mb-3">
          <label class="form-label">Co-Founder name</label>
          <input type="text" name="co_founder_name" class="form-control" maxlength="120"
                 value="<?= e($coFounderName) ?>" placeholder="e.g. Suresh Patel">
        </div>
        <div class="mb-0">
          <label class="form-label">Co-Founder photo (optional)</label>
          <input type="file" name="co_founder_photo" class="form-control" accept="image/jpeg,image/png,image/webp">
          <?php if ($coFounderPhoto): ?>
            <label class="form-check mt-2 mb-0">
              <input class="form-check-input" type="checkbox" name="remove_co_founder_photo" value="1">
              <span class="form-check-label">Remove current photo</span>
            </label>
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-ink" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-brand"><i class="fa-solid fa-check me-1"></i>Save</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<link rel="stylesheet" href="<?= asset('assets/css/org-chart.css') ?>">
<script>
(function () {
  let z = 1;
  const scale = document.getElementById('ocScale');
  const apply = () => { if (scale) scale.style.transform = 'scale(' + z + ')'; };
  document.getElementById('ocZoomIn')?.addEventListener('click', () => { z = Math.min(1.4, z + 0.1); apply(); });
  document.getElementById('ocZoomOut')?.addEventListener('click', () => { z = Math.max(0.55, z - 0.1); apply(); });
})();
</script>
