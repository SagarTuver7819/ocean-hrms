<div class="panel">
  <div class="panel-header"><h3>My Documents</h3></div>
  <div class="panel-body p-0">
    <table class="table mb-0">
      <thead><tr><th>Name</th><th>Type</th><th>Uploaded</th><th>Status</th></tr></thead>
      <tbody>
      <?php if (empty($documents)): ?>
        <tr><td colspan="4" class="text-center text-muted py-4">No documents uploaded</td></tr>
      <?php else: foreach ($documents as $d): ?>
        <tr>
          <td><?= e($d['document_name']) ?></td>
          <td><?= e($d['document_type']) ?></td>
          <td><?= format_date($d['upload_date']) ?></td>
          <td><?= e($d['status']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
