<div class="page-head">
  <div>
    <h1>All Employees Report</h1>
    <p>IT departments · Staff directory · CRUD actions</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="<?= url('dashboard') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-arrow-left me-1"></i> Dashboard</a>
    <a href="<?= url('employees/create') ?>" class="btn btn-sm btn-brand"><i class="fa-solid fa-plus me-1"></i> Add Employee</a>
  </div>
</div>

<div class="status-tabs">
  <a class="<?= empty($filters['status']) ? 'active' : '' ?>" href="<?= url('employees') ?>">All</a>
  <a class="<?= ($filters['status']??'')==='active' ? 'active' : '' ?>" href="<?= url('employees?status=active') ?>">Active</a>
  <a class="<?= ($filters['status']??'')==='probation' ? 'active' : '' ?>" href="<?= url('employees?status=probation') ?>">Probation</a>
  <a class="<?= ($filters['status']??'')==='onboarding' ? 'active' : '' ?>" href="<?= url('employees?status=onboarding') ?>">Onboarding</a>
  <a class="<?= in_array($filters['status']??'', ['inactive','resigned'], true) ? 'active' : '' ?>" href="<?= url('employees?status=inactive') ?>">Exit / Inactive</a>
</div>

<div class="filter-bar">
  <form class="row g-2 align-items-end" method="get">
    <div class="col-md-3">
      <label class="form-label">Search</label>
      <input type="text" name="q" class="form-control" placeholder="Name / code / email" value="<?= e($filters['q'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">IT Department</label>
      <select name="department_id" class="form-select select2">
        <option value="">All Departments</option>
        <?php foreach ($departments as $d): ?>
          <option value="<?= $d['id'] ?>" <?= ($filters['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="">All</option>
        <?php foreach (['active','probation','onboarding','inactive','resigned'] as $s): ?>
          <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-brand w-100"><i class="fa-solid fa-filter me-1"></i>Filter</button>
    </div>
  </form>
</div>

<div class="panel table-card">
  <div class="panel-body">
    <div class="table-responsive">
      <table class="table table-hover datatable dt-export align-middle mb-0">
        <thead>
          <tr>
            <th class="no-sort" width="50">Sr.</th>
            <th>Code</th>
            <th>Employee</th>
            <th>Department</th>
            <th>Designation</th>
            <th>Joining</th>
            <th>Manager</th>
            <th>Type</th>
            <th>Status</th>
            <th class="no-sort no-export text-center">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($employees as $i => $e): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><a class="code-pill" href="<?= url('employees/'.$e['id']) ?>"><?= e($e['employee_code']) ?></a></td>
            <td>
              <div class="emp-cell">
                <div class="emp-av"><?= strtoupper(substr($e['first_name'],0,1)) ?></div>
                <div>
                  <div class="emp-name"><?= e($e['first_name'].' '.$e['last_name']) ?></div>
                  <small><?= e($e['official_email'] ?? '') ?></small>
                </div>
              </div>
            </td>
            <td><?= e($e['department_name'] ?? '—') ?></td>
            <td><?= e($e['designation_name'] ?? '—') ?></td>
            <td><?= format_date($e['joining_date']) ?></td>
            <td><?= e($e['manager_name'] ?? '—') ?></td>
            <td><?= e(ucwords(str_replace('_',' ', $e['employment_type'] ?? ''))) ?></td>
            <td><span class="badge-status badge-<?= e($e['status']) ?>"><?= e($e['status']) ?></span></td>
            <td class="text-center">
              <div class="action-btns">
                <a href="<?= url('employees/'.$e['id']) ?>" class="btn-icon tone-blue" title="View"><i class="fa-solid fa-eye"></i></a>
                <a href="<?= url('employees/'.$e['id'].'/edit') ?>" class="btn-icon tone-orange" title="Edit"><i class="fa-solid fa-pen"></i></a>
                <a href="<?= url('salary/employee/'.$e['id']) ?>" class="btn-icon tone-green" title="Salary"><i class="fa-solid fa-indian-rupee-sign"></i></a>
                <a href="<?= url('employees/'.$e['id'].'?tab=documents') ?>" class="btn-icon tone-violet" title="Documents"><i class="fa-solid fa-folder"></i></a>
                <?php if (can('employee.delete') && !in_array($e['status'], ['resigned', 'inactive'], true)): ?>
                  <form method="post" action="<?= url('employees/' . $e['id'] . '/delete') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon tone-red" title="Exit / Delete"
                      data-confirm="Mark <?= e($e['employee_code']) ?> as exited (resigned)? Login will be disabled.">
                      <i class="fa-solid fa-user-xmark"></i>
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
