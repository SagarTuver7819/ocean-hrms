<?php
use App\Services\Attendance;

$days = Attendance::WEEKDAYS;
$dayList = static function (string $csv) use ($days): string {
    $parts = array_filter(explode(',', $csv), 'strlen');
    if (!$parts) {
        return 'None';
    }
    return implode(', ', array_map(static fn($d) => substr($days[(int) $d], 0, 3), $parts));
};
$span = function (array $s): int {
    $mins = (strtotime($s['end_time']) - strtotime($s['start_time'])) / 60;
    return (int) ($mins <= 0 ? $mins + 1440 : $mins);
};
$lateLabel = static function (array $s): string {
    if (!empty($s['skip_late'])) {
        return 'No late';
    }
    $flex = (int) ($s['flexible_minutes'] ?? 0);
    $grace = (int) $s['late_after_minutes'];
    if ($flex > 0) {
        $until = date('h:i A', strtotime($s['start_time']) + ($flex + $grace) * 60);
        return 'After ' . $until;
    }
    return $grace . ' min grace';
};
?>
<div class="page-head">
  <div>
    <h1>Shift Master</h1>
    <p>Flexible punch · grace · max break · working hours · week offs (full / half day)</p>
  </div>
  <button type="button" class="btn btn-sm btn-brand" data-bs-toggle="modal" data-bs-target="#shiftModal"
    data-add-modal="#shiftModal" data-action="<?= url('shifts') ?>" data-title="Add Shift">
    <i class="fa-solid fa-plus me-1"></i>Add Shift
  </button>
</div>

<div class="alert alert-light border mb-3" style="border-radius:14px">
  <b>Ocean policy</b> —
  General: flexible <b>09:00–10:00</b>, late after <b>10:15</b>, work <b>8h 20m</b>, max break <b>1h</b> (day span 9h 20m).
  Internship: flexible (morning / evening OK), <b>4 hours</b> full day, <b>no late</b>, punch-out never Absent if worked.
  BDM: Sunday off + Saturday <b>2nd half</b> off.
  Leave: <b>Paid Leave 1 / month</b> (from Oct 2026 · no carry-forward) · <b>Sick Leave</b> unpaid.
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
        <div><small>Flexible</small><b><?= (int) ($s['flexible_minutes'] ?? 0) ?> min</b></div>
        <div><small>Late</small><b><?= e($lateLabel($s)) ?></b></div>
        <div><small>Break max</small><b><?= (int) $s['break_minutes'] ?> min</b></div>
        <div><small>Full Day</small><b><?= hm($s['full_day_minutes']) ?></b></div>
        <div><small>Half Day</small><b><?= hm($s['half_day_minutes']) ?></b></div>
      </div>
      <div class="shift-foot">
        <span>
          <i class="fa-solid fa-calendar-xmark me-1"></i>
          Off: <b><?= e($dayList($s['week_offs'] ?? '')) ?></b>
          <?php if (!empty($s['half_week_offs'])): ?>
            · Half: <b><?= e($dayList($s['half_week_offs'])) ?></b>
          <?php endif; ?>
        </span>
        <div class="action-btns ms-auto">
          <button type="button" class="btn-icon" title="Edit" data-bs-toggle="modal" data-bs-target="#shiftModal"
            data-action="<?= url('shifts/' . $s['id'] . '/update') ?>" data-title="Edit Shift"
            data-edit='<?= e(json_encode([
                'name' => $s['name'], 'code' => $s['code'],
                'start_time' => substr($s['start_time'], 0, 5), 'end_time' => substr($s['end_time'], 0, 5),
                'flexible_minutes' => (int) ($s['flexible_minutes'] ?? 0),
                'late_after_minutes' => $s['late_after_minutes'],
                'skip_late' => (int) ($s['skip_late'] ?? 0),
                'break_minutes' => $s['break_minutes'],
                'full_day_hours' => round($s['full_day_minutes'] / 60, 2),
                'half_day_hours' => round($s['half_day_minutes'] / 60, 2),
                'week_offs[]' => array_values(array_filter(explode(',', $s['week_offs'] ?? ''), 'strlen')),
                'half_week_offs[]' => array_values(array_filter(explode(',', $s['half_week_offs'] ?? ''), 'strlen')),
                'status' => $s['status'],
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
      <thead><tr><th width="50">Sr.</th><th>Code</th><th>Employee</th><th>Type / Role</th><th width="280">Shift</th></tr></thead>
      <tbody>
      <?php foreach ($employees as $i => $emp): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td class="fw-bold"><?= e($emp['employee_code']) ?></td>
          <td><?= e($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
          <td>
            <small class="text-muted"><?= e(ucwords(str_replace('_', ' ', (string) ($emp['employment_type'] ?? '')))) ?></small>
            <?php if (!empty($emp['designation_name'])): ?> · <?= e($emp['designation_name']) ?><?php endif; ?>
          </td>
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
          <div class="col-md-3"><label class="form-label required">Start Time</label><input type="time" name="start_time" class="form-control" value="09:00" required></div>
          <div class="col-md-3"><label class="form-label required">End Time</label><input type="time" name="end_time" class="form-control" value="18:20" required></div>
          <div class="col-md-3"><label class="form-label">Flexible (min)</label><input type="number" min="0" name="flexible_minutes" class="form-control" value="60"><div class="form-text">09:00–10:00 → 60</div></div>
          <div class="col-md-3"><label class="form-label">Grace after flex (min)</label><input type="number" min="0" name="late_after_minutes" class="form-control" value="15"></div>
          <div class="col-md-3"><label class="form-label">Break max (min)</label><input type="number" min="0" name="break_minutes" class="form-control" value="60"></div>
          <div class="col-md-3"><label class="form-label">Full Day (hours)</label><input type="number" min="0" step="0.05" name="full_day_hours" class="form-control" value="8.33"><div class="form-text">8.33 = 8h 20m</div></div>
          <div class="col-md-3"><label class="form-label">Half Day (hours)</label><input type="number" min="0" step="0.05" name="half_day_hours" class="form-control" value="4.17"></div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select>
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="skip_late" value="1" id="skipLate">
              <label for="skipLate" class="form-check-label">Do not count late (Internship)</label>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">Full Weekly Off</label>
            <div class="weekday-pick">
              <?php foreach ($days as $n => $d): ?>
                <label><input type="checkbox" name="week_offs[]" value="<?= $n ?>" <?= $n === 0 ? 'checked' : '' ?>><span><?= substr($d, 0, 3) ?></span></label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">Half-day Weekly Off (2nd half)</label>
            <div class="weekday-pick">
              <?php foreach ($days as $n => $d): ?>
                <label><input type="checkbox" name="half_week_offs[]" value="<?= $n ?>"><span><?= substr($d, 0, 3) ?></span></label>
              <?php endforeach; ?>
            </div>
            <div class="form-text">Late = after start + flexible + grace. Work target is Full Day hours even if break is shorter. Half-day off (e.g. Sat for BDM) counts as 0.5 for leave.</div>
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
