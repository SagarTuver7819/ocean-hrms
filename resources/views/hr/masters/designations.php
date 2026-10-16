<div class="page-head">
  <div>
    <h1>Designation Master</h1>
    <p>Job titles mapped to IT departments</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('dashboard') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
    <button type="button" class="btn btn-sm btn-brand"
      data-bs-toggle="modal" data-bs-target="#desigModal"
      data-add-modal="#desigModal"
      data-action="<?= url('designations') ?>"
      data-title="Add Designation">
      <i class="fa-solid fa-plus me-1"></i> Add Designation
    </button>
  </div>
</div>

<div class="panel table-card">
  <div class="panel-body">
    <div class="table-responsive">
      <table class="table table-hover datatable dt-export align-middle mb-0">
        <thead>
          <tr>
            <th width="60">Sr.</th>
            <th>Designation</th>
            <th>Department</th>
            <th>Status</th>
            <th width="120">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $i => $item): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td class="fw-semibold"><?= e($item['name']) ?></td>
            <td><?= e($item['department_name'] ?? '—') ?></td>
            <td><span class="badge-status badge-<?= $item['status'] ? 'active' : 'inactive' ?>"><?= $item['status'] ? 'Active' : 'Inactive' ?></span></td>
            <td>
              <div class="action-btns">
                <button type="button" class="btn-icon" title="Edit"
                  data-bs-toggle="modal" data-bs-target="#desigModal"
                  data-edit='<?= e(json_encode(['name'=>$item['name'],'department_id'=>$item['department_id'],'status'=>$item['status']])) ?>'
                  data-action="<?= url('designations/'.$item['id'].'/update') ?>"
                  data-title="Edit Designation">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <form method="post" action="<?= url('designations/'.$item['id'].'/delete') ?>" class="d-inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn-icon danger" title="Delete"
                    data-confirm="Delete this designation?" data-confirm-title="Delete Designation">
                    <i class="fa-solid fa-trash"></i>
                  </button>
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

<div class="modal fade crud-modal" id="desigModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="<?= url('designations') ?>" class="modal-content js-loading">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-id-card me-2 text-warning"></i><span class="modal-title-text">Add Designation</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label required">Designation Name</label>
          <input type="text" name="name" class="form-control" required placeholder="e.g. Software Engineer">
        </div>
        <div class="mb-3">
          <label class="form-label">Department</label>
          <select name="department_id" class="form-select select2" data-placeholder="Select department">
            <option value="">Select</option>
            <?php foreach ($departments as $d): ?>
              <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-0">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-ink" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-brand"><i class="fa-solid fa-check me-1"></i><span class="btn-submit-text">Save</span></button>
      </div>
    </form>
  </div>
</div>
