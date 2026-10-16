<div class="page-head">
  <div>
    <h1>Employee Query / Helpdesk</h1>
    <p>HR · IT Support · Payroll · Attendance tickets</p>
  </div>
</div>

<div class="status-tabs">
  <?php foreach (['open'=>'Open','resolved'=>'Resolved','closed'=>'Closed','all'=>'All'] as $k=>$label): ?>
    <a href="<?= url('queries?status='.$k) ?>" class="<?= ($status ?? '') === $k ? 'active' : '' ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<div class="panel table-card">
  <div class="panel-body">
    <table class="table table-hover datatable dt-export align-middle mb-0">
      <thead>
        <tr>
          <th>Ticket</th>
          <th>Employee</th>
          <th>Category</th>
          <th>Subject</th>
          <th>Status</th>
          <th>Date</th>
          <th width="90">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($queries as $q): ?>
        <tr>
          <td class="fw-bold">#<?= (int)$q['id'] ?></td>
          <td>
            <div class="fw-semibold"><?= e($q['first_name'].' '.$q['last_name']) ?></div>
            <small class="text-muted"><?= e($q['employee_code']) ?></small>
          </td>
          <td><span class="badge-status badge-late"><?= e(str_replace('_',' ',$q['category'])) ?></span></td>
          <td><?= e($q['subject']) ?></td>
          <td><span class="badge-status badge-<?= e($q['status']) ?>"><?= e($q['status']) ?></span></td>
          <td><?= format_date($q['created_at'], 'd M Y H:i') ?></td>
          <td>
            <a href="<?= url('queries/'.$q['id']) ?>" class="btn-icon" title="Open"><i class="fa-solid fa-eye"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
