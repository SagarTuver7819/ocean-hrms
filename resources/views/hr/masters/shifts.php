<?php
use App\Services\Attendance;

$days = Attendance::WEEKDAYS;
$offNames = fn(string $csv) => implode(', ', array_map(fn($d) => substr($days[(int) $d], 0, 3), array_filter(explode(',', $csv), 'strlen'))) ?: 'None';
$span = function (array $s): int {
    $mins = (strtotime($s['end_time']) - strtotime($s['start_time'])) / 60;
    return (int) ($mins <= 0 ? $mins + 1440 : $mins);
};
?>
<div class="page-head">
  <div>
    <h1>Shift Master</h1>
    <p>Shift timings, grace time, break &amp; working-hour rules · assign shifts to employees</p>
  </div>
  <button type="button" class="btn btn-sm btn-brand" data-bs-toggle="modal" data-bs-target="#shiftModal"
    data-add-modal="#shiftModal" data-action="<?= url('shifts') ?>" data-title="Add Shift">
    <i class="fa-solid fa-plus me-1"></i>Add Shift
  </button>
</div>

<div class="shift-grid">
  <?php foreach ($shifts as $s): ?>
    <div class="shift-card <?= $s['status'] ? '' : 'is-off' ?>">
      <div class="shift-top">
        <span class="shift-ico"><i class="fa-solid fa-business-time"></i></span>
        <div class="min-w-0">
          <b><?= e($s['name']) ?></b>
          <small><?= e($s['code'] ?: 'SHIFT') ?> · <?= (int) $s['employees'] ?> employee(s)</small>
        </div>
        <span class="badge-status badge-<?= $s['status'] ? 'active' : 'inactive' ?> ms-auto"><?= $s['status'] ? 'Active' : 'Inactive' ?></span>
      </div>
      <div class="shift-time">
        <span><?= date('h:i A', strtotime($s['start_time'])) ?></span>
        <i class="fa-solid fa-arrow-right-long"></i>
        <span><?= date('h:i A', strtotime($s['end_time'])) ?></span>
        <em><?= hm($span($s)) ?></em>
      </div>
      <div class="shift-rules">
        <div><small>Grace</small><b><?= (int) $s['late_after_minutes'] ?> min</b></div>
        <div><small>Break</small><b><?= (int) $s['break_minutes'] ?> min</b></div>
        <div><small>Full Day</small><b><?= hm($s['full_day_minutes']) ?></b></div>
        <div><small>Half Day</small><b><?= hm($s['half_day_minutes']) ?></b></div>
      </div>
      <div class="shift-foot">
        <span><i class="fa-solid fa-calendar-xmark me-1"></i>Week off: <b><?= e($offNames($s['week_offs'])) ?></b></span>
        <div class="action-btns ms-auto">
          <button type="button" class="btn-icon" title="Edit" data-bs-toggle="modal" data-bs-target="#shiftModal"
            data-action="<?= url('shifts/' . $s['id'] . '/update') ?>" data-title="Edit Shift"
            data-edit='<?= e(json_encode([
                'name' => $s['name'], 'code' => $s['code'],
                'start_time' => substr($s['start_time'], 0, 5), 'end_time' => substr($s['end_time'], 0, 5),
                'late_after_minutes' => $s['late_after_minutes'], 'break_minutes' => $s['break_minutes'],
                'full_day_hours' => round($s['full_day_minutes'] / 60, 2), 'half_day_hours' => round($s['half_day_minutes'] / 60, 2),
                'week_offs[]' => array_values(array_filter(explode(',', $s['week_offs']), 'strlen')), 'status' => $s['status'],
            ])) ?>'><i class="fa-solid fa-pen"></i></button>
          <form method="post" action="<?= url('shifts/' . $s['id'] . '/delete') ?>" class="d-inline">
            <?= csrf_field() ?>
            <button class="btn-icon danger" title="Delete" data-confirm="Delete shift <?= e($s['name']) ?>?" data-confirm-title="Delete Shift"><i class="fa-solid fa-trash"></i></button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<form method="post" action="<?= url('shifts/assign') ?>" class="panel table-card js-loading">
  <?= csrf_field() ?>
  <div class="panel-header">
    <h3>Employee Shift Assignment</h3>
    <div class="d-flex gap-2 align-items-center">
      <select class="form-select form-select-sm js-shift-all" style="width:190px">
        <option value="">Set all to…</option>
        <?php foreach ($shifts as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-sm btn-brand"><i class="fa-solid fa-check me-1"></i><span class="btn-submit-text">Save Assignment</span></button>
    </div>
  </div>
  <div class="panel-body">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th width="50">Sr.</th><th>Code</th><th>Employee</th><th>Department</th><th width="280">Shift</th></tr></thead>
      <tbody>
      <?php foreach ($employees as $i => $emp): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td class="fw-bold"><?= e($emp['employee_code']) ?></td>
          <td><?= e($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
          <td><?= e($emp['department_name'] ?? '—') ?></td>
          <td>
            <select name="shift[<?= $emp['id'] ?>]" class="form-select form-select-sm js-shift-pick" style="min-width:270px">
              <option value="">Default shift</option>
              <?php foreach ($shifts as $s): ?>
                <option value="<?= $s['id'] ?>" <?= (int) $emp['shift_id'] === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> (<?= date('h:i A', strtotime($s['start_time'])) ?> – <?= date('h:i A', strtotime($s['end_time'])) ?>)</option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</form>

<div class="modal fade crud-modal" id="shiftModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="post" action="<?= url('shifts') ?>" class="modal-content js-loading">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-business-time me-2"></i><span class="modal-title-text">Add Shift</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-8"><label class="form-label required">Shift Name</label><input name="name" class="form-control" required placeholder="e.g. General Shift"></div>
          <div class="col-md-4"><label class="form-label">Code</label><input name="code" class="form-control" placeholder="GEN"></div>
          <div class="col-md-3"><label class="form-label required">Start Time</label><input type="time" name="start_time" class="form-control" value="09:30" required></div>
          <div class="col-md-3"><label class="form-label required">End Time</label><input type="time" name="end_time" class="form-control" value="18:30" required></div>
          <div class="col-md-3"><label class="form-label">Grace (min)</label><input type="number" min="0" name="late_after_minutes" class="form-control" value="15"></div>
          <div class="col-md-3"><label class="form-label">Break Allowed (min)</label><input type="number" min="0" name="break_minutes" class="form-control" value="60"></div>
          <div class="col-md-3"><label class="form-label">Full Day (hours)</label><input type="number" min="0" step="0.25" name="full_day_hours" class="form-control" value="8"></div>
          <div class="col-md-3"><label class="form-label">Half Day (hours)</label><input type="number" min="0" step="0.25" name="half_day_hours" class="form-control" value="4"></div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select>
          </div>
          <div class="col-12">
            <label class="form-label">Weekly Off</label>
            <div class="weekday-pick">
              <?php foreach ($days as $n => $d): ?>
                <label><input type="checkbox" name="week_offs[]" value="<?= $n ?>" <?= $n === 0 ? 'checked' : '' ?>><span><?= substr($d, 0, 3) ?></span></label>
              <?php endforeach; ?>
            </div>
            <div class="form-text">Late is marked after start time + grace. Below the half-day hours a day counts as Half Day; above full-day hours counts as overtime.</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-ink" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-brand"><i class="fa-solid fa-check me-1"></i><span class="btn-submit-text">Save</span></button>
      </div>
    </form>
  </div>
</div>
