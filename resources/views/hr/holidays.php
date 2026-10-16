<div class="page-head">
  <div>
    <h1>Holiday Master</h1>
    <p>Company holiday calendar · CRUD</p>
  </div>
  <button type="button" class="btn btn-sm btn-brand"
    data-bs-toggle="modal" data-bs-target="#holidayModal"
    data-add-modal="#holidayModal"
    data-action="<?= url('holidays') ?>"
    data-title="Add Holiday">
    <i class="fa-solid fa-plus me-1"></i> Add Holiday
  </button>
</div>

<div class="panel table-card">
  <div class="panel-body">
    <div class="table-responsive">
      <table class="table table-hover datatable dt-export align-middle mb-0">
        <thead>
          <tr>
            <th width="60">Sr.</th>
            <th>Date</th>
            <th>Holiday Name</th>
            <th>Type</th>
            <th>Location</th>
            <th>Description</th>
            <th width="120">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $i => $item): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td class="fw-semibold"><?= format_date($item['holiday_date']) ?></td>
            <td><?= e($item['name']) ?></td>
            <td><span class="badge-status badge-late"><?= e($item['holiday_type']) ?></span></td>
            <td><?= e($item['location'] ?? 'All') ?></td>
            <td><?= e($item['description'] ?? '—') ?></td>
            <td>
              <div class="action-btns">
                <button type="button" class="btn-icon" title="Edit"
                  data-bs-toggle="modal" data-bs-target="#holidayModal"
                  data-edit='<?= e(json_encode($item)) ?>'
                  data-action="<?= url('holidays/'.$item['id'].'/update') ?>"
                  data-title="Edit Holiday">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <form method="post" action="<?= url('holidays/'.$item['id'].'/delete') ?>" class="d-inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn-icon danger" title="Delete"
                    data-confirm="Delete this holiday?" data-confirm-title="Delete Holiday">
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

<div class="modal fade crud-modal" id="holidayModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="<?= url('holidays') ?>" class="modal-content js-loading">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-umbrella-beach me-2 text-warning"></i><span class="modal-title-text">Add Holiday</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label required">Holiday Name</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label required">Date</label>
          <input type="text" name="holiday_date" class="form-control datepicker" required placeholder="YYYY-MM-DD">
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Type</label>
            <select name="holiday_type" class="form-select">
              <option value="public">Public</option>
              <option value="company">Company</option>
              <option value="optional">Optional</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" value="All">
          </div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-control">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-ink" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-brand"><span class="btn-submit-text">Save</span></button>
      </div>
    </form>
  </div>
</div>
