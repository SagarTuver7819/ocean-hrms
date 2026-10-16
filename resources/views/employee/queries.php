<div class="panel">
  <div class="panel-header">
    <h3>My Queries</h3>
    <a href="<?= url('queries/create') ?>" class="btn btn-sm btn-brand">Raise Query</a>
  </div>
  <div class="panel-body p-0">
    <table class="table mb-0 datatable">
      <thead><tr><th>#</th><th>Category</th><th>Subject</th><th>Status</th><th>Date</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($queries as $q): ?>
        <tr>
          <td><?= (int)$q['id'] ?></td>
          <td><?= e(str_replace('_',' ',$q['category'])) ?></td>
          <td><?= e($q['subject']) ?></td>
          <td><span class="badge-status badge-<?= e($q['status']) ?>"><?= e($q['status']) ?></span></td>
          <td><?= format_date($q['created_at'], 'd M Y') ?></td>
          <td><a href="<?= url('queries/'.$q['id']) ?>" class="btn btn-sm btn-soft">View</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
