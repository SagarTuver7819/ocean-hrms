<div class="page-head">
  <div>
    <h1>Announcements / Circulars</h1>
    <p>Company notices for employees · CRUD</p>
  </div>
  <button type="button" class="btn btn-sm btn-brand"
    data-bs-toggle="modal" data-bs-target="#annModal"
    data-add-modal="#annModal"
    data-action="<?= url('announcements') ?>"
    data-title="Publish Announcement">
    <i class="fa-solid fa-plus me-1"></i> Add Announcement
  </button>
</div>

<div class="panel table-card">
  <div class="panel-body">
    <div class="table-responsive">
      <table class="table table-hover datatable dt-export align-middle mb-0">
        <thead>
          <tr>
            <th width="60">Sr.</th>
            <th>Title</th>
            <th>Department</th>
            <th>Publish</th>
            <th>Expiry</th>
            <th>Status</th>
            <th width="120">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $i => $item): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td>
              <div class="fw-semibold"><?= e($item['title']) ?></div>
              <small class="text-muted"><?= e(mb_strimwidth(strip_tags($item['description'] ?? ''), 0, 60, '…')) ?></small>
            </td>
            <td><?= e($item['department_name'] ?? 'All') ?></td>
            <td><?= format_date($item['publish_date']) ?></td>
            <td><?= format_date($item['expiry_date']) ?></td>
            <td><span class="badge-status badge-<?= $item['status'] ? 'active' : 'inactive' ?>"><?= $item['status'] ? 'Active' : 'Inactive' ?></span></td>
            <td>
              <div class="action-btns">
                <button type="button" class="btn-icon" title="Edit"
                  data-bs-toggle="modal" data-bs-target="#annModal"
                  data-edit='<?= e(json_encode([
                    'title'=>$item['title'],
                    'description'=>$item['description'],
                    'publish_date'=>$item['publish_date'],
                    'expiry_date'=>$item['expiry_date'],
                    'target_department_id'=>$item['target_department_id'],
                    'status'=>$item['status'],
                  ])) ?>'
                  data-action="<?= url('announcements/'.$item['id'].'/update') ?>"
                  data-title="Edit Announcement">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <form method="post" action="<?= url('announcements/'.$item['id'].'/delete') ?>" class="d-inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn-icon danger" title="Delete"
                    data-confirm="Delete this announcement?" data-confirm-title="Delete Announcement">
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

<div class="modal fade crud-modal" id="annModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="post" action="<?= url('announcements') ?>" class="modal-content js-loading">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-bullhorn me-2 text-warning"></i><span class="modal-title-text">Publish Announcement</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label required">Title</label>
          <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label required">Description</label>
          <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Publish Date</label>
            <input type="text" name="publish_date" class="form-control datepicker" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Expiry Date</label>
            <input type="text" name="expiry_date" class="form-control datepicker">
          </div>
          <div class="col-md-4">
            <label class="form-label">Target Department</label>
            <select name="target_department_id" class="form-select">
              <option value="">All Departments</option>
              <?php foreach ($departments as $d): ?>
                <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-ink" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-brand"><span class="btn-submit-text">Publish</span></button>
      </div>
    </form>
  </div>
</div>
