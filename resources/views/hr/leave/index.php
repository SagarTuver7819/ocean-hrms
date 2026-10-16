<?php
$num = fn($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
$tabs = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled', 'all' => 'All'];
?>
<div class="page-head">
  <div>
    <h1>Leave Desk</h1>
    <p>Employee leave requests · Policy: 1 paid leave credited per month</p>
  </div>
  <a href="<?= url('leave/types') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-layer-group me-1"></i>Leave Types</a>
</div>

<div class="status-tabs">
  <?php foreach ($tabs as $k => $label): ?>
    <a href="<?= url('leave?status=' . $k) ?>" class="<?= $status === $k ? 'active' : '' ?>"><?= $label ?>
      <span class="count-chip"><?= $k === 'all' ? array_sum($counts) : (int) ($counts[$k] ?? 0) ?></span></a>
  <?php endforeach; ?>
</div>

<div class="panel table-card">
  <div class="panel-body">
    <div class="table-responsive">
      <table class="table table-hover datatable dt-export align-middle mb-0">
        <thead>
          <tr><th>Sr.</th><th>Employee</th><th>Leave Type</th><th>Dates</th><th>Days</th><th>Balance</th><th>Reason</th><th>Applied</th><th>Status</th><th width="110">Action</th></tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $i => $r): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td class="att-emp"><b><?= e($r['first_name'] . ' ' . $r['last_name']) ?></b><small><?= e($r['employee_code']) ?></small></td>
            <td><span class="badge-status badge-late"><?= e($r['code']) ?></span> <?= e($r['leave_type']) ?></td>
            <td><?= format_date($r['from_date']) ?><?= $r['to_date'] !== $r['from_date'] ? '<br><small class="text-muted">to ' . format_date($r['to_date']) . '</small>' : '' ?></td>
            <td class="fw-bold"><?= $num($r['days']) ?></td>
            <td>
              <?php if ($r['allocated'] !== null): ?>
                <b><?= $num($r['allocated'] - $r['used'] - $r['pending_days']) ?></b> <small class="text-muted">free of <?= $num($r['allocated']) ?></small>
              <?php else: ?><small class="text-muted"><?= $r['is_paid'] ? 'Not tracked' : 'Unpaid' ?></small><?php endif; ?>
            </td>
            <td class="att-breaks"><?= e(mb_strimwidth($r['reason'] ?? '', 0, 60, '…')) ?></td>
            <td data-order="<?= e($r['created_at']) ?>"><small><?= format_date($r['created_at']) ?></small></td>
            <td><span class="badge-status badge-<?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
            <td>
              <?php if ($r['status'] === 'pending'): ?>
                <div class="action-btns">
                  <form method="post" action="<?= url('leave/' . $r['id'] . '/approve') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <button class="btn-icon success" title="Approve" data-confirm="Approve <?= $num($r['days']) ?> day(s) leave for <?= e($r['first_name']) ?>?" data-confirm-title="Approve Leave"><i class="fa-solid fa-check"></i></button>
                  </form>
                  <form method="post" action="<?= url('leave/' . $r['id'] . '/reject') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <button class="btn-icon danger" title="Reject" data-confirm="Reject this leave request?" data-confirm-title="Reject Leave"><i class="fa-solid fa-xmark"></i></button>
                  </form>
                </div>
              <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
