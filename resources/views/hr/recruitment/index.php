<?php
use App\Models\Candidate;

$qs = fn(array $extra) => '?' . http_build_query(array_filter(array_merge($filters, ['view' => $viewMode], $extra), fn($v) => $v !== '' && $v !== null));
$total = array_sum($counts);
$tabs = ['' => ['All', $total]];
foreach (Candidate::STATUSES as $key => [$label]) {
    $tabs[$key] = [$label, $counts[$key]];
}
?>
<div class="page-head">
  <div>
    <h1>Recruitment Applications</h1>
    <p><?= (int) $counts['new'] ?> pending · <?= (int) $counts['selected'] ?> selected · Public career form via fixed QR</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <div class="view-switch">
      <a href="<?= e($qs(['view' => 'list'])) ?>" class="vs-btn vs-blue <?= $viewMode === 'list' ? 'active' : '' ?>"><i class="fa-solid fa-list"></i>List</a>
      <a href="<?= e($qs(['view' => 'grid'])) ?>" class="vs-btn vs-green <?= $viewMode === 'grid' ? 'active' : '' ?>"><i class="fa-solid fa-table-cells-large"></i>Grid</a>
      <a href="<?= url('recruitment/interviews') ?>" class="vs-btn vs-violet"><i class="fa-solid fa-user-check"></i>Interviews</a>
    </div>
    <a href="<?= url('recruitment/qr') ?>" class="btn btn-sm btn-brand"><i class="fa-solid fa-qrcode me-1"></i>Fixed QR Code</a>
    <a href="<?= url('apply') ?>" target="_blank" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Apply Form</a>
  </div>
</div>

<div class="status-tabs mb-3">
  <?php foreach ($tabs as $key => [$label, $n]): ?>
    <a href="<?= e($qs(['status' => $key])) ?>" class="<?= $filters['status'] === $key ? 'active' : '' ?>"><?= e($label) ?> <span class="count-chip"><?= (int) $n ?></span></a>
  <?php endforeach; ?>
</div>

<form class="filter-bar panel mb-3" method="get">
  <input type="hidden" name="view" value="<?= e($viewMode) ?>">
  <input type="hidden" name="status" value="<?= e($filters['status']) ?>">
  <div class="row g-2 align-items-end p-3">
    <div class="col-md-6"><input class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Search name, mobile, app no, skill…"></div>
    <div class="col-md-4">
      <select name="position" class="form-select select2" data-placeholder="All positions">
        <option value="">All positions</option>
        <?php foreach ($positions as $p): ?><option value="<?= e($p) ?>" <?= $filters['position'] === $p ? 'selected' : '' ?>><?= e($p) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
      <button class="btn btn-brand flex-grow-1"><i class="fa-solid fa-filter me-1"></i>Filter</button>
      <a href="<?= url('recruitment?view=' . $viewMode) ?>" class="btn btn-outline-ink" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
    </div>
  </div>
</form>

<?php if (!$candidates): ?>
  <div class="panel"><div class="panel-body text-center py-5 text-muted">
    <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>No applications found. Share the <a href="<?= url('recruitment/qr') ?>">career QR code</a> with candidates.
  </div></div>
<?php elseif ($viewMode === 'grid'): ?>
  <div class="row g-3">
    <?php foreach ($candidates as $c): ?>
      <div class="col-md-6 col-xxl-4">
        <?php
          $skills = array_values(array_filter(array_map('trim', explode(',', (string) $c['primary_skills']))));
          $facts = [
              ['fa-phone', 'Mobile', $c['mobile']],
              (float) ($c['offered_salary'] ?? 0) > 0
                  ? ['fa-indian-rupee-sign', 'Final Salary', '₹' . inr($c['offered_salary']) . ' / mo']
                  : ['fa-indian-rupee-sign', 'Expected CTC', $c['expected_ctc'] ? '₹' . inr($c['expected_ctc']) : '—'],
              ['fa-building', 'Experience', Candidate::experienceLabel((int) $c['total_experience_months'], (bool) $c['is_fresher'])],
              ['fa-hourglass-half', 'Notice', $c['notice_period'] ?: '—'],
          ];
        ?>
        <div class="cand-card">
          <div class="cand-top">
            <div class="cand-avatar"><?= e(strtoupper(mb_substr($c['first_name'], 0, 1) . mb_substr($c['last_name'], 0, 1))) ?></div>
            <div class="min-w-0 flex-grow-1">
              <h3 class="text-truncate"><?= e($c['first_name'] . ' ' . $c['last_name']) ?></h3>
              <div class="cand-no"><?= e($c['application_no']) ?></div>
            </div>
            <span class="badge-status <?= Candidate::statusBadge($c['status']) ?>"><?= e(Candidate::statusLabel($c['status'])) ?></span>
          </div>
          <div class="cand-role">
            <i class="fa-solid fa-briefcase"></i>
            <div class="min-w-0"><b class="text-truncate d-block"><?= e($c['position_other'] ?: ($c['designation_name'] ?: '—')) ?></b><small><?= e($c['department_name'] ?: '—') ?></small></div>
            <?php if ($c['employee_id']): ?><span class="cand-emp"><i class="fa-solid fa-id-badge me-1"></i>Employee</span><?php endif; ?>
          </div>
          <div class="cand-facts">
            <?php foreach ($facts as [$icon, $label, $value]): ?>
              <div><span class="cf-ico"><i class="fa-solid <?= $icon ?>"></i></span><div class="min-w-0"><small><?= $label ?></small><b class="text-truncate d-block"><?= e($value) ?></b></div></div>
            <?php endforeach; ?>
          </div>
          <div class="cand-mail text-truncate"><i class="fa-solid fa-envelope"></i><?= e($c['email']) ?></div>
          <div class="cand-tags">
            <?php foreach (array_slice($skills, 0, 4) as $skill): ?><span><?= e($skill) ?></span><?php endforeach; ?>
            <?php if (count($skills) > 4): ?><span class="more">+<?= count($skills) - 4 ?></span><?php endif; ?>
            <?php if (!$skills): ?><span class="more">No skills listed</span><?php endif; ?>
          </div>
          <div class="cand-foot">
            <small><i class="fa-regular fa-calendar me-1"></i><?= format_date($c['created_at'], 'd-m-Y') ?></small>
            <div class="action-btns">
              <a href="<?= url('recruitment/' . $c['id'] . '/print') ?>" target="_blank" class="btn-icon" title="Print application form"><i class="fa-solid fa-print"></i></a>
              <?php if ($c['status'] === 'selected'): ?>
                <a href="<?= url('recruitment/' . $c['id'] . '/offer-letter') ?>" target="_blank" class="btn-icon tone-green" title="Offer letter"><i class="fa-solid fa-file-signature"></i></a>
              <?php endif; ?>
              <a href="<?= url('recruitment/' . $c['id'] . '/file/resume') ?>" target="_blank" class="btn-icon" title="Resume"><i class="fa-solid fa-file-pdf"></i></a>
              <a href="<?= url('recruitment/' . $c['id']) ?>" class="btn btn-sm btn-brand"><i class="fa-solid fa-eye me-1"></i>View</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="panel table-card">
    <div class="panel-body">
      <div class="table-responsive">
        <table class="table table-hover datatable dt-export align-middle mb-0">
          <thead>
            <tr><th>App No</th><th>Candidate</th><th>Position</th><th>Experience</th><th>Expected CTC</th><th>Notice</th><th>Applied</th><th>Status</th><th width="130">Action</th></tr>
          </thead>
          <tbody>
          <?php foreach ($candidates as $c): ?>
            <tr>
              <td class="fw-semibold"><?= e($c['application_no']) ?></td>
              <td><b><?= e($c['first_name'] . ' ' . $c['last_name']) ?></b><div class="small text-muted"><?= e($c['mobile']) ?></div></td>
              <td><?= e($c['position_other'] ?: ($c['designation_name'] ?: '—')) ?><div class="small text-muted"><?= e($c['department_name'] ?: '') ?></div></td>
              <td><?= e(Candidate::experienceLabel((int) $c['total_experience_months'], (bool) $c['is_fresher'])) ?></td>
              <td><?= $c['expected_ctc'] ? '₹' . inr($c['expected_ctc']) : '—' ?></td>
              <td><?= e($c['notice_period'] ?: '—') ?></td>
              <td data-order="<?= e($c['created_at']) ?>"><?= format_date($c['created_at'], 'd-m-Y') ?></td>
              <td><span class="badge-status <?= Candidate::statusBadge($c['status']) ?>"><?= e(Candidate::statusLabel($c['status'])) ?></span></td>
              <td>
                <div class="action-btns">
                  <a href="<?= url('recruitment/' . $c['id']) ?>" class="btn-icon" title="View"><i class="fa-solid fa-eye"></i></a>
                  <a href="<?= url('recruitment/' . $c['id'] . '/print?print=1') ?>" target="_blank" class="btn-icon" title="Print"><i class="fa-solid fa-print"></i></a>
                  <form method="post" action="<?= url('recruitment/' . $c['id'] . '/delete') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon danger" title="Delete" data-confirm="Delete this application permanently?" data-confirm-title="Delete Application"><i class="fa-solid fa-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>
