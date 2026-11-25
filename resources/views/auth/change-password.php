<div class="panel" style="max-width:520px">
  <div class="panel-header"><h3><?= !empty($force) ? 'Change Password Required' : 'Change Password' ?></h3></div>
  <div class="panel-body">
    <?php if (!empty($force)): ?>
      <div class="alert alert-warning">For security, please set a new password before continuing.</div>
    <?php endif; ?>
    <form method="post" action="<?= url('change-password') ?>">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Current Password</label>
        <div class="pwd-wrap">
          <input type="password" class="form-control" name="current_password" required>
          <button type="button" class="pwd-toggle" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">New Password</label>
        <div class="pwd-wrap">
          <input type="password" class="form-control" name="new_password" minlength="8" required>
          <button type="button" class="pwd-toggle" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <div class="pwd-wrap">
          <input type="password" class="form-control" name="confirm_password" minlength="8" required>
          <button type="button" class="pwd-toggle" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
        </div>
      </div>
      <button class="btn btn-brand">Update Password</button>
    </form>
  </div>
</div>
<script>
document.querySelectorAll('.pwd-toggle').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var input = btn.parentElement.querySelector('input');
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.classList.toggle('is-on', show);
    btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    btn.querySelector('i').className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
  });
});
</script>
