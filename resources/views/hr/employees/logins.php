<?php
$total = count($rows);
$withLogin = count(array_filter($rows, fn($r) => $r['user_id']));
$active = count(array_filter($rows, fn($r) => $r['user_id'] && $r['login_status']));
$visible = count(array_filter($rows, fn($r) => $r['password'] !== null));
$portalUrl = url('login');
?>
<div class="page-head">
  <div>
    <h1>Employee Logins</h1>
    <p>Auto-generated portal usernames &amp; passwords · Username is the employee code</p>
  </div>
  <form method="post" action="<?= url('employees/logins/generate') ?>">
    <?= csrf_field() ?>
    <button class="btn btn-sm btn-brand"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Generate Missing Logins</button>
  </form>
</div>

<div class="metric-row">
  <div class="metric-card metric-blue"><div class="label">Employees</div><div class="value"><?= $total ?></div></div>
  <div class="metric-card metric-green"><div class="label">Logins Created</div><div class="value"><?= $withLogin ?></div></div>
  <div class="metric-card metric-orange"><div class="label">Active Logins</div><div class="value"><?= $active ?></div></div>
  <div class="metric-card metric-teal"><div class="label">Password Visible</div><div class="value"><?= $visible ?></div></div>
</div>

<div class="panel table-card">
  <div class="panel-header">
    <h3>Portal Credentials</h3>
    <small class="text-muted">Employees log in on the <b>Employee</b> tab at <code><?= e($portalUrl) ?></code></small>
  </div>
  <div class="panel-body">
    <div class="table-responsive">
      <table class="table table-hover datatable align-middle mb-0">
        <thead>
          <tr>
            <th width="50">Sr.</th>
            <th>Employee</th>
            <th>Username</th>
            <th>Password</th>
            <th>Status</th>
            <th>Last Login</th>
            <th width="170">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $i => $r): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td>
              <a href="<?= url('employees/' . $r['id']) ?>" class="fw-bold text-decoration-none text-dark"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></a>
              <small class="d-block text-muted"><?= e($r['employee_code']) ?><?= $r['designation_name'] ? ' · ' . e($r['designation_name']) : '' ?></small>
            </td>
            <?php if (!$r['user_id']): ?>
              <td><span class="text-muted"><i class="fa-solid fa-circle-exclamation text-warning me-1"></i>No login yet</span></td>
              <td>—</td>
              <td><span class="badge-status badge-inactive">None</span></td>
              <td><span class="text-muted">Never</span></td>
              <td>
                <form method="post" action="<?= url('employees/' . $r['id'] . '/login') ?>">
                  <?= csrf_field() ?><input type="hidden" name="back" value="logins">
                  <button class="btn btn-sm btn-brand"><i class="fa-solid fa-key me-1"></i>Create Login</button>
                </form>
              </td>
            <?php else: ?>
              <td>
                <span class="cred-chip"><code><?= e($r['username']) ?></code>
                  <button type="button" class="cred-btn js-copy" data-copy="<?= e($r['username']) ?>" title="Copy username"><i class="fa-regular fa-copy"></i></button>
                </span>
              </td>
              <td>
                <?php if ($r['password'] !== null): ?>
                  <span class="cred-chip">
                    <code class="js-secret" data-secret="<?= e($r['password']) ?>">••••••••••</code>
                    <button type="button" class="cred-btn js-reveal" title="Show / hide"><i class="fa-regular fa-eye"></i></button>
                    <button type="button" class="cred-btn js-copy" data-copy="<?= e($r['password']) ?>" title="Copy password"><i class="fa-regular fa-copy"></i></button>
                  </span>
                <?php else: ?>
                  <span class="badge-status badge-probation" title="The employee set their own password. Reset to issue a new one.">Changed by employee</span>
                <?php endif; ?>
              </td>
              <td><span class="badge-status badge-<?= $r['login_status'] ? 'active' : 'inactive' ?>"><?= $r['login_status'] ? 'Active' : 'Disabled' ?></span></td>
              <td><?= $r['last_login_at'] ? date('d M Y, h:i A', strtotime($r['last_login_at'])) : '<span class="text-muted">Never</span>' ?></td>
              <td>
                <div class="action-btns">
                  <?php if ($r['password'] !== null): ?>
                    <button type="button" class="btn-icon js-copy" title="Copy login details"
                      data-copy="<?= e("Ocean HRMS Employee Login\nURL: {$portalUrl}\nUsername: {$r['username']}\nPassword: {$r['password']}") ?>"><i class="fa-solid fa-share-nodes"></i></button>
                  <?php endif; ?>
                  <form method="post" action="<?= url('employees/' . $r['id'] . '/login') ?>" class="d-inline">
                    <?= csrf_field() ?><input type="hidden" name="back" value="logins">
                    <button class="btn-icon" title="Reset password" data-confirm="Generate a new password for <?= e($r['first_name']) ?>? The old password will stop working." data-confirm-title="Reset Password"><i class="fa-solid fa-rotate"></i></button>
                  </form>
                  <form method="post" action="<?= url('employees/' . $r['id'] . '/login/toggle') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <button class="btn-icon <?= $r['login_status'] ? 'danger' : 'success' ?>" title="<?= $r['login_status'] ? 'Disable login' : 'Enable login' ?>"
                      data-confirm="<?= $r['login_status'] ? 'Block' : 'Allow' ?> portal access for <?= e($r['first_name']) ?>?" data-confirm-title="<?= $r['login_status'] ? 'Disable' : 'Enable' ?> Login">
                      <i class="fa-solid <?= $r['login_status'] ? 'fa-user-lock' : 'fa-user-check' ?>"></i>
                    </button>
                  </form>
                </div>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
