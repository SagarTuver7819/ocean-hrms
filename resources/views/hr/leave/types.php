<?php $num = fn($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.'); ?>
<div class="page-head">
  <div>
    <h1>Leave Type Master</h1>
    <p>Company policy: 1 paid leave credited every month · Set a monthly credit to auto-build balances</p>
  </div>
  <button type="button" class="btn btn-sm btn-brand" data-bs-toggle="modal" data-bs-target="#leaveTypeModal"
    data-add-modal="#leaveTypeModal" data-action="<?= url('leave/types') ?>" data-title="Add Leave Type">
    <i class="fa-solid fa-plus me-1"></i> Add Leave Type
  </button>
</div>

<div class="panel table-card">
  <div class="panel-body">
    <table class="table table-hover datatable align-middle mb-0">
      <thead>
        <tr><th>Sr.</th><th>Name</th><th>Code</th><th>Credit</th><th>Per Year</th><th>Paid</th><th>Status</th><th width="80">Action</th></tr>
      </thead>
      <tbody>
      <?php foreach ($types as $i => $t): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td class="fw-semibold"><?= e($t['name']) ?></td>
          <td><span class="badge-status badge-late"><?= e($t['code']) ?></span></td>
          <td><?= (float) $t['monthly_accrual'] > 0 ? '<b>' . $num($t['monthly_accrual']) . '</b> / month' : ((float) $t['days_per_year'] > 0 ? 'Fixed yearly' : '<span class="text-muted">No limit</span>') ?></td>
          <td><?= (float) $t['days_per_year'] > 0 ? $num($t['days_per_year']) : '—' ?></td>
          <td><?= $t['is_paid'] ? 'Yes' : 'No' ?></td>
          <td><span class="badge-status badge-<?= $t['status'] ? 'active' : 'inactive' ?>"><?= $t['status'] ? 'Active' : 'Inactive' ?></span></td>
          <td>
            <button type="button" class="btn-icon" title="Edit" data-bs-toggle="modal" data-bs-target="#leaveTypeModal"
              data-action="<?= url('leave/types/' . $t['id'] . '/update') ?>" data-title="Edit Leave Type"
              data-edit='<?= e(json_encode(['name' => $t['name'], 'code' => $t['code'], 'monthly_accrual' => (float) $t['monthly_accrual'], 'days_per_year' => (float) $t['days_per_year'], 'is_paid' => $t['is_paid'], 'status' => $t['status']])) ?>'>
              <i class="fa-solid fa-pen"></i>
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade crud-modal" id="leaveTypeModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="<?= url('leave/types') ?>" class="modal-content js-loading">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-layer-group me-2"></i><span class="modal-title-text">Add Leave Type</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-8"><label class="form-label required">Name</label><input class="form-control" name="name" required placeholder="Paid Leave"></div>
          <div class="col-4"><label class="form-label required">Code</label><input class="form-control" name="code" required placeholder="PL"></div>
          <div class="col-6"><label class="form-label">Credit per Month</label><input type="number" step="0.5" min="0" class="form-control" name="monthly_accrual" value="1"><div class="form-text">0 = not monthly</div></div>
          <div class="col-6"><label class="form-label">Days / Year</label><input type="number" step="0.5" min="0" class="form-control" name="days_per_year" value="0"><div class="form-text">Used only when monthly credit is 0. 0 + 0 = unlimited</div></div>
          <div class="col-6"><label class="form-label">Status</label><select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
          <div class="col-6 d-flex align-items-end"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_paid" value="1" id="paid" checked><label for="paid" class="form-check-label">Paid Leave</label></div></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-ink" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-brand"><i class="fa-solid fa-check me-1"></i><span class="btn-submit-text">Save</span></button>
      </div>
    </form>
  </div>
</div>
