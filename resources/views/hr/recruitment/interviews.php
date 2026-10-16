<?php use App\Models\Candidate; ?>
<div class="page-head">
  <div>
    <h1>Interview Candidates</h1>
    <p>Pending candidates · record interview rounds, then mark Selected or Not Selected</p>
  </div>
  <div class="d-flex gap-2">
    <div class="view-switch">
      <a href="<?= url('recruitment?view=list') ?>" class="vs-btn vs-blue"><i class="fa-solid fa-list"></i>List</a>
      <a href="<?= url('recruitment?view=grid') ?>" class="vs-btn vs-green"><i class="fa-solid fa-table-cells-large"></i>Grid</a>
      <a href="<?= url('recruitment/interviews') ?>" class="vs-btn vs-violet active"><i class="fa-solid fa-user-check"></i>Interviews</a>
    </div>
    <a href="<?= url('recruitment/qr') ?>" class="btn btn-sm btn-brand"><i class="fa-solid fa-qrcode me-1"></i>QR</a>
  </div>
</div>

<div class="panel table-card">
  <div class="panel-body">
    <div class="table-responsive">
      <table class="table table-hover datatable dt-export align-middle mb-0">
        <thead>
          <tr><th>Candidate</th><th>Position</th><th>Experience</th><th>Rounds</th><th>Last Interview</th><th>Status</th><th width="230" class="no-sort no-export">Decision</th><th width="90" class="no-sort no-export">Action</th></tr>
        </thead>
        <tbody>
        <?php foreach ($candidates as $c): ?>
          <tr>
            <td><b><?= e($c['first_name'] . ' ' . $c['last_name']) ?></b><div class="small text-muted"><?= e($c['application_no']) ?> · <?= e($c['mobile']) ?></div></td>
            <td><?= e($c['position_other'] ?: ($c['designation_name'] ?: '—')) ?></td>
            <td><?= e(Candidate::experienceLabel((int) $c['total_experience_months'], (bool) $c['is_fresher'])) ?></td>
            <td><span class="count-chip"><?= (int) $c['rounds_count'] ?></span></td>
            <td data-order="<?= e((string) $c['last_interview_at']) ?>"><?= $c['last_interview_at'] ? format_date($c['last_interview_at'], 'd-m-Y h:i A') : '<span class="text-muted">Not scheduled</span>' ?></td>
            <td><span class="badge-status <?= Candidate::statusBadge($c['status']) ?>"><?= e(Candidate::statusLabel($c['status'])) ?></span></td>
            <td>
              <form method="post" action="<?= url('recruitment/' . $c['id'] . '/status') ?>" class="d-flex gap-1">
                <?= csrf_field() ?>
                <input type="hidden" name="back" value="interviews">
                <input type="hidden" name="hr_remarks" value="<?= e($c['hr_remarks'] ?? '') ?>">
                <select name="status" class="form-select form-select-sm" required>
                  <option value="">Choose…</option>
                  <?php foreach (Candidate::DECISIONS as $key): ?>
                    <option value="<?= $key ?>"><?= e(Candidate::statusLabel($key)) ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-brand" title="Update"><i class="fa-solid fa-check"></i></button>
              </form>
            </td>
            <td>
              <div class="action-btns">
                <a href="<?= url('recruitment/' . $c['id'] . '#interviews') ?>" class="btn-icon" title="Open"><i class="fa-solid fa-user-check"></i></a>
                <a href="<?= url('recruitment/' . $c['id'] . '/print?print=1') ?>" target="_blank" class="btn-icon" title="Print"><i class="fa-solid fa-print"></i></a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
