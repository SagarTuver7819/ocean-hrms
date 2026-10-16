<?php
ob_start(); ?>
  <a href="<?= url('employee/documents') ?>" class="btn btn-soft btn-sm"><i class="fa-solid fa-folder-open me-1"></i>My Documents</a>
  <a href="<?= url('change-password') ?>" class="btn btn-brand btn-sm"><i class="fa-solid fa-key me-1"></i>Change Password</a>
<?php $actions = ob_get_clean(); ?>

<?php \App\Core\View::partial('partials/employee-hero', ['employee' => $employee, 'actions' => $actions, 'photoAction' => url('employee/photo')]); ?>
<?php \App\Core\View::partial('partials/employee-info', ['employee' => $employee]); ?>
