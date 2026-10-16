<div class="panel" style="max-width:520px">
  <div class="panel-header"><h3><?= !empty($force) ? 'Change Password Required' : 'Change Password' ?></h3></div>
  <div class="panel-body">
    <?php if (!empty($force)): ?>
      <div class="alert alert-warning">For security, please set a new password before continuing.</div>
    <?php endif; ?>
    <form method="post" action="<?= url('change-password') ?>">
      <?= csrf_field() ?>
      <div class="mb-3"><label class="form-label">Current Password</label><input type="password" class="form-control" name="current_password" required></div>
      <div class="mb-3"><label class="form-label">New Password</label><input type="password" class="form-control" name="new_password" minlength="8" required></div>
      <div class="mb-3"><label class="form-label">Confirm Password</label><input type="password" class="form-control" name="confirm_password" minlength="8" required></div>
      <button class="btn btn-brand">Update Password</button>
    </form>
  </div>
</div>
