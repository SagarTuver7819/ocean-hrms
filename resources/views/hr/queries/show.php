<div class="panel">
  <div class="panel-header">
    <h3>#<?= (int)$query['id'] ?> · <?= e($query['subject']) ?></h3>
    <span class="badge-status badge-<?= e($query['status']) ?>"><?= e($query['status']) ?></span>
  </div>
  <div class="panel-body">
    <p class="mb-1"><strong><?= e($query['first_name'].' '.$query['last_name']) ?></strong> (<?= e($query['employee_code']) ?>)</p>
    <p class="text-muted small mb-3"><?= e(str_replace('_',' ',$query['category'])) ?> · <?= format_date($query['created_at'], 'd M Y H:i') ?></p>
    <p><?= nl2br(e($query['description'])) ?></p>

    <hr>
    <h6 class="fw-bold mb-3">Conversation</h6>
    <?php foreach ($replies as $r): ?>
      <div class="border rounded-3 p-3 mb-2">
        <div class="d-flex justify-content-between"><strong><?= e($r['user_name']) ?></strong><small class="text-muted"><?= format_date($r['created_at'], 'd M Y H:i') ?></small></div>
        <div class="mt-1"><?= nl2br(e($r['message'])) ?></div>
      </div>
    <?php endforeach; ?>

    <form method="post" action="<?= url('queries/'.$query['id'].'/reply') ?>" class="mt-3">
      <?= csrf_field() ?>
      <div class="mb-3"><label class="form-label">Reply</label><textarea class="form-control" name="message" rows="3" required></textarea></div>
      <div class="mb-3">
        <label class="form-label">Update Status</label>
        <select class="form-select" name="status">
          <?php foreach (['in_progress','resolved','closed','open'] as $s): ?>
            <option value="<?= $s ?>" <?= $query['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-brand">Send Reply</button>
    </form>
  </div>
</div>
