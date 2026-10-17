<?php
$num = fn($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
$tracked = array_values(array_filter($balances, fn($b) => $b['tracked']));
?>
<div class="page-head">
  <div>
    <h1>My Leave</h1>
    <p>Company policy: 1 paid leave is credited every month · Extra days can be applied as Unpaid Leave</p>
  </div>
</div>

<div class="leave-cards">
  <?php foreach ($tracked as $i => $b): $pct = $b['allocated'] > 0 ? min(100, ($b['used'] + $b['pending']) / $b['allocated'] * 100) : 0; ?>
    <div class="leave-card <?= $i === 0 ? 'is-main' : '' ?>">
      <small><?= e($b['name']) ?> · <?= date('Y') ?></small>
      <div class="lc-value"><?= $num($b['available']) ?> <span>available</span></div>
      <div class="lc-bar"><i style="width:<?= round($pct) ?>%"></i></div>
      <div class="lc-meta"><span>Earned <?= $num($b['allocated']) ?></span><span>Used <?= $num($b['used']) ?></span><span>Pending <?= $num($b['pending']) ?></span></div>
    </div>
  <?php endforeach; ?>
  <div class="leave-card">
    <small>How it works</small>
    <div class="lc-value" style="font-size:20px"><?= $tracked && (float) $tracked[0]['monthly_accrual'] > 0 ? $num($tracked[0]['monthly_accrual']) . ' leave / month' : 'Leave policy' ?></div>
    <div class="text-muted small fw-semibold">Credited on the 1st of every month from your joining month. Sundays, weekly offs and company holidays are not counted as leave.</div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="ep-card cp tone-orange">
      <div class="ep-card-head"><span class="ep-card-ico"><i class="fa-solid fa-calendar-plus"></i></span><div><h3>Apply Leave</h3><small>HR is notified instantly</small></div></div>
      <div class="cp-body">
        <form method="post" action="<?= url('leave') ?>" id="leaveForm" class="js-loading emp-form" data-days-url="<?= url('leave/days') ?>">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label required">Leave Type</label>
            <select class="form-select" name="leave_type_id" required>
              <?php foreach ($balances as $b): ?>
                <option value="<?= $b['leave_type_id'] ?>"><?= e($b['name']) ?><?= $b['tracked'] ? ' (' . $num($b['available']) . ' available)' : ($b['is_paid'] ? '' : ' (no pay)') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row g-2 mb-2">
            <div class="col-6"><label class="form-label required">From</label><input type="date" class="form-control" name="from_date" min="<?= date('Y-m-d', strtotime('-7 days')) ?>" required></div>
            <div class="col-6"><label class="form-label required">To</label><input type="date" class="form-control" name="to_date" required></div>
          </div>
          <div class="mb-3" id="leaveDurationWrap">
            <label class="form-label required">Leave Duration</label>
            <div class="leave-duration-picks">
              <label class="leave-duration-pick">
                <input type="radio" name="leave_duration" value="full" checked>
                <span><i class="fa-solid fa-calendar-day"></i><b>Full Day</b><small>1 full leave day</small></span>
              </label>
              <label class="leave-duration-pick">
                <input type="radio" name="leave_duration" value="half">
                <span><i class="fa-solid fa-adjust"></i><b>Half Day</b><small>0.5 leave day · same date only</small></span>
              </label>
            </div>
            <div class="form-text" id="leaveDurationHint">Half day is available when From and To are the same date.</div>
          </div>
          <div class="leave-days-preview mb-3" id="leaveDays">Select dates to see leave days</div>
          <div class="mb-3"><label class="form-label required">Reason</label><textarea class="form-control" name="reason" rows="3" required placeholder="e.g. Family function"></textarea></div>
          <button class="btn btn-brand w-100"><i class="fa-solid fa-paper-plane me-1"></i><span class="btn-submit-text">Submit Leave Request</span></button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="panel table-card">
      <div class="panel-header"><h3>Leave History</h3></div>
      <div class="panel-body">
        <table class="table table-hover datatable align-middle mb-0">
          <thead><tr><th>Applied</th><th>Type</th><th>Dates</th><th>Days</th><th>Reason</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($requests as $r): ?>
            <tr>
              <td data-order="<?= e($r['created_at']) ?>"><?= format_date($r['created_at']) ?></td>
              <td><span class="badge-status badge-late"><?= e($r['code']) ?></span> <?= e($r['leave_type']) ?></td>
              <td><?= format_date($r['from_date']) ?><?= $r['to_date'] !== $r['from_date'] ? ' – ' . format_date($r['to_date']) : '' ?></td>
              <td class="fw-bold">
                <?= $num($r['days']) ?>
                <?php if ((float) $r['days'] <= 0.5 && $r['from_date'] === $r['to_date']): ?>
                  <small class="d-block text-muted">Half Day</small>
                <?php elseif ($r['from_date'] === $r['to_date']): ?>
                  <small class="d-block text-muted">Full Day</small>
                <?php endif; ?>
              </td>
              <td class="att-breaks"><?= e(mb_strimwidth($r['reason'] ?? '', 0, 60, '…')) ?><?= $r['hr_remark'] ? '<small class="d-block text-dark">HR: ' . e($r['hr_remark']) . '</small>' : '' ?></td>
              <td><span class="badge-status badge-<?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
              <td>
                <?php if ($r['status'] === 'pending'): ?>
                  <form method="post" action="<?= url('leave/' . $r['id'] . '/cancel') ?>">
                    <?= csrf_field() ?>
                    <button class="btn-icon danger" title="Cancel request" data-confirm="Cancel this leave request?" data-confirm-title="Cancel Leave"><i class="fa-solid fa-xmark"></i></button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
