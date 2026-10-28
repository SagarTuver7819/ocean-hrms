<div class="page-head">
  <div>
    <h1>Employee Salary</h1>
    <p>Decided monthly salary per employee · used by Salary Register · history on every revision</p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= url('salary/register') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-table me-1"></i>Salary Register</a>
    <a href="<?= url('employees') ?>" class="btn btn-sm btn-brand"><i class="fa-solid fa-user-plus me-1"></i>Assign from Employee</a>
  </div>
</div>

<div class="panel table-card">
  <div class="panel-body">
    <table class="table table-hover datatable dt-export align-middle mb-0">
      <thead>
        <tr>
          <th>Sr.</th>
          <th>Code</th>
          <th>Employee</th>
          <th>Type</th>
          <th>Department</th>
          <th>Monthly Salary</th>
          <th>PT</th>
          <th>Annual</th>
          <th>Effective</th>
          <th width="100">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $i => $r): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td class="fw-bold"><?= e($r['employee_code']) ?></td>
          <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?><small class="d-block text-muted"><?= e($r['designation_name'] ?? '') ?></small></td>
          <td>
            <?php if (($r['employment_type'] ?? '') === 'intern'): ?>
              <span class="badge-status badge-late">Intern</span>
            <?php else: ?>
              <span class="badge-status badge-present">Staff</span>
            <?php endif; ?>
          </td>
          <td><?= e($r['department_name'] ?? '—') ?></td>
          <td class="fw-bold text-success"><?= money($r['monthly_salary']) ?></td>
          <td><?= money($r['professional_tax'] ?? \App\Services\Salary::DEFAULT_PT) ?></td>
          <td><?= money($r['monthly_salary'] * 12) ?></td>
          <td><?= format_date($r['effective_from']) ?></td>
          <td>
            <div class="action-btns">
              <a href="<?= url('salary/employee/' . $r['employee_id']) ?>" class="btn-icon" title="Manage"><i class="fa-solid fa-pen"></i></a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
