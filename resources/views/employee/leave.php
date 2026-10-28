<?php
$num = fn($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
$paid = null;
$sick = null;
foreach ($balances as $b) {
    if (($b['code'] ?? '') === 'PL') {
        $paid = $b;
    }
    if (($b['code'] ?? '') === 'SL') {
        $sick = $b;
    }
}
$monthNames = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];
$year = (int) ($year ?? date('Y'));
$credited = (int) ($creditedMonths ?? 0);
$creditedList = $creditedMonthList ?? [];
$remainMonths = [];
$lastCredited = $creditedList ? end($creditedList) : ((int) date('n', strtotime($policyStart ?? '2026-10-01')) - 1);
for ($m = $lastCredited + 1; $m <= 12; $m++) {
    $remainMonths[] = $m;
}
$pct = $paid && $paid['allocated'] > 0 ? min(100, (($paid['used'] + $paid['pending']) / $paid['allocated']) * 100) : 0;
?>
<div class="page-head">
  <div>
    <h1>My Leave</h1>
    <p>2 leave types · Paid Leave (1 / month, no carry-forward) · Sick Leave (unpaid)</p>
  </div>
</div>

<div class="leave-balance-grid">
  <div class="leave-bal-card is-paid">
    <div class="lb-top">
      <span class="lb-ico"><i class="fa-solid fa-umbrella-beach"></i></span>
      <div>
        <small>Paid Leave · PL</small>
        <b><?= $year ?> balance</b>
      </div>
    </div>
    <div class="lb-value"><?= $paid ? $num($paid['available']) : '0' ?> <span>available</span></div>
    <div class="lc-bar"><i style="width:<?= round($pct) ?>%"></i></div>
    <div class="lb-meta">
      <span>Earned <b><?= $paid ? $num($paid['allocated']) : '0' ?></b></span>
      <span>Used <b><?= $paid ? $num($paid['used']) : '0' ?></b></span>
      <span>Pending <b><?= $paid ? $num($paid['pending']) : '0' ?></b></span>
    </div>
  </div>

  <div class="leave-bal-card is-sick">
    <div class="lb-top">
      <span class="lb-ico"><i class="fa-solid fa-notes-medical"></i></span>
      <div>
        <small>Sick Leave · SL</small>
        <b>Unpaid</b>
      </div>
    </div>
    <div class="lb-value lb-open">Open <span>no balance limit</span></div>
    <p class="lb-note">Sick leave is unpaid. Use when Paid Leave balance is over, or for illness.</p>
  </div>

  <div class="leave-bal-card is-policy">
    <div class="lb-top">
      <span class="lb-ico"><i class="fa-solid fa-calendar-check"></i></span>
      <div>
        <small>Monthly credit</small>
        <b><?= $credited ?> of <?= $credited + count($remainMonths) ?> months</b>
      </div>
    </div>
    <div class="leave-month-track">
      <?php foreach ($creditedList as $m): ?>
        <span class="lm done" title="<?= $monthNames[$m] ?>"><?= $monthNames[$m] ?></span>
      <?php endforeach; ?>
      <?php foreach ($remainMonths as $m): ?>
        <span class="lm soon" title="<?= $monthNames[$m] ?> · credits on 1st"><?= $monthNames[$m] ?></span>
      <?php endforeach; ?>
    </div>
    <p class="lb-note">1 Paid Leave credited each month · unused days do <b>not</b> carry to <?= $year + 1 ?>.</p>
  </div>
</div>

<div class="leave-apply-layout">
  <div class="leave-apply-panel">
    <div class="ep-card cp tone-orange leave-apply-card">
      <div class="ep-card-head">
        <span class="ep-card-ico"><i class="fa-solid fa-calendar-plus"></i></span>
        <div><h3>Apply Leave</h3><small>HR is notified instantly</small></div>
      </div>
      <div class="cp-body">
        <form method="post" action="<?= url('leave') ?>" id="leaveForm" class="js-loading emp-form" data-days-url="<?= url('leave/days') ?>">
          <?= csrf_field() ?>

          <label class="form-label required">Leave Type</label>
          <div class="leave-type-picks mb-3">
            <?php foreach ($balances as $i => $b): ?>
              <label class="leave-type-pick <?= $b['code'] === 'PL' ? 'is-paid' : 'is-sick' ?>">
                <input type="radio" name="leave_type_id" value="<?= (int) $b['leave_type_id'] ?>" <?= $i === 0 ? 'checked' : '' ?> required>
                <span>
                  <i class="fa-solid <?= $b['code'] === 'PL' ? 'fa-umbrella-beach' : 'fa-notes-medical' ?>"></i>
                  <b><?= e($b['name']) ?></b>
                  <?php if ($b['tracked']): ?>
                    <small><?= $num($b['available']) ?> available · paid</small>
                  <?php else: ?>
                    <small>Unpaid · no balance cut</small>
                  <?php endif; ?>
                </span>
              </label>
            <?php endforeach; ?>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label required">From</label>
              <input type="date" class="form-control" name="from_date" min="<?= date('Y-m-d', strtotime('-7 days')) ?>" required>
            </div>
            <div class="col-6">
              <label class="form-label required">To</label>
              <input type="date" class="form-control" name="to_date" required>
            </div>
          </div>

          <div class="mb-3" id="leaveDurationWrap">
            <label class="form-label required">Duration</label>
            <div class="leave-duration-picks">
              <label class="leave-duration-pick">
                <input type="radio" name="leave_duration" value="full" checked>
                <span><i class="fa-solid fa-calendar-day"></i><b>Full Day</b><small>Counts as 1 leave day</small></span>
              </label>
              <label class="leave-duration-pick">
                <input type="radio" name="leave_duration" value="half">
                <span><i class="fa-solid fa-adjust"></i><b>Half Day</b><small>0.5 · same date only</small></span>
              </label>
            </div>
            <div class="form-text" id="leaveDurationHint">Half day needs the same From and To date.</div>
          </div>

          <div class="leave-days-preview mb-3" id="leaveDays">Select dates to see leave days</div>

          <div class="mb-3">
            <label class="form-label required">Reason</label>
            <textarea class="form-control" name="reason" rows="3" required placeholder="Short reason for HR (e.g. Family function / Fever)"></textarea>
          </div>

          <button class="btn btn-brand w-100 btn-lg leave-submit-btn">
            <i class="fa-solid fa-paper-plane me-1"></i><span class="btn-submit-text">Submit Leave Request</span>
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="leave-history-panel">
    <div class="panel table-card leave-history-card">
      <div class="panel-header">
        <h3>Leave History</h3>
        <small class="text-muted">Your applications</small>
      </div>
      <div class="panel-body">
        <?php if (!$requests): ?>
          <div class="leave-empty">
            <i class="fa-regular fa-calendar"></i>
            <p>No leave applied yet. Use the form to submit your first request.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover datatable align-middle mb-0">
              <thead>
                <tr><th>Applied</th><th>Type</th><th>Dates</th><th>Days</th><th>Reason</th><th>Status</th><th></th></tr>
              </thead>
              <tbody>
              <?php foreach ($requests as $r): ?>
                <tr>
                  <td data-order="<?= e($r['created_at']) ?>"><?= format_date($r['created_at']) ?></td>
                  <td>
                    <span class="badge-status <?= !empty($r['is_paid']) ? 'badge-present' : 'badge-late' ?>"><?= e($r['code']) ?></span>
                    <?= e($r['leave_type']) ?>
                  </td>
                  <td>
                    <?= format_date($r['from_date']) ?>
                    <?= $r['to_date'] !== $r['from_date'] ? ' – ' . format_date($r['to_date']) : '' ?>
                  </td>
                  <td class="fw-bold">
                    <?= $num($r['days']) ?>
                    <?php if ((float) $r['days'] <= 0.5 && $r['from_date'] === $r['to_date']): ?>
                      <small class="d-block text-muted">Half Day</small>
                    <?php elseif ($r['from_date'] === $r['to_date']): ?>
                      <small class="d-block text-muted">Full Day</small>
                    <?php endif; ?>
                  </td>
                  <td class="att-breaks">
                    <?= e(mb_strimwidth($r['reason'] ?? '', 0, 60, '…')) ?>
                    <?= $r['hr_remark'] ? '<small class="d-block text-dark">HR: ' . e($r['hr_remark']) . '</small>' : '' ?>
                  </td>
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
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
