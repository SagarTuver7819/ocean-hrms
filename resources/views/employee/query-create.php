<div class="panel" style="max-width:720px">
  <div class="panel-header"><h3>Raise Query</h3></div>
  <div class="panel-body">
    <form method="post" action="<?= url('queries') ?>">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Category</label>
        <select class="form-select" name="category" required>
          <?php foreach (['salary','attendance','leave','payroll','documents','hr','it_support','asset','other'] as $c): ?>
            <option value="<?= $c ?>"><?= ucwords(str_replace('_',' ',$c)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3"><label class="form-label">Subject</label><input class="form-control" name="subject" required></div>
      <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="5" required></textarea></div>
      <button class="btn btn-brand">Submit Query</button>
    </form>
  </div>
</div>
