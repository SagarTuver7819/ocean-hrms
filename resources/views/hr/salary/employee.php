<?php
$monthly = (float) ($salary['monthly_salary'] ?? 0);
$pt = $salary
    ? (float) ($salary['professional_tax'] ?? \App\Services\Salary::DEFAULT_PT)
    : (float) \App\Services\Salary::DEFAULT_PT;
$approxNet = $monthly > 0 ? max(0, $monthly - $pt) : 0;
?>
<div class="page-head">
  <div>
    <h1>Employee Salary</h1>
    <p><?= e($employee['first_name'] . ' ' . $employee['last_name']) ?> · <?= e($employee['employee_code']) ?></p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('salary/register') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-table me-1"></i>Register</a>
    <a href="<?= url('salary') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-arrow-left me-1"></i>Salary List</a>
    <a href="<?= url('employees/' . $employee['id']) ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-id-badge me-1"></i>Employee Profile</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-xl-5">
    <form method="post" action="<?= url('salary/employee/' . $employee['id']) ?>" class="ep-card cp tone-orange js-loading">
      <?= csrf_field() ?>
      <div class="ep-card-head">
        <span class="ep-card-ico"><i class="fa-solid fa-indian-rupee-sign"></i></span>
        <div><h3>Decided Monthly Salary</h3><small>Fixed amount · PT auto-deducted (editable)</small></div>
      </div>
      <div class="cp-body">
        <div class="salary-now">
          <small>Current decided salary</small>
          <b><?= $salary ? money($monthly) : 'Not set' ?></b>
          <?php if ($salary): ?>
            <span><?= e(amount_in_words($monthly)) ?> · PT <?= money($pt) ?> · ≈ <?= money($approxNet) ?> after PT</span>
          <?php endif; ?>
        </div>
        <label class="form-label required">Monthly Salary (₹)</label>
        <input type="number" name="monthly_salary" class="form-control form-control-lg js-salary-input mb-1" min="1" step="1" required value="<?= $monthly ? (int) $monthly : '' ?>" placeholder="e.g. 25000">
        <div class="form-text mb-3 js-salary-words"></div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label required">Professional Tax (PT)</label>
            <div class="input-group">
              <span class="input-group-text">₹</span>
              <input type="number" name="professional_tax" class="form-control" min="0" step="1" required value="<?= (int) $pt ?>" placeholder="200">
            </div>
            <div class="form-text">Default ₹200 · set 0 if exempt</div>
          </div>
          <div class="col-md-6"><label class="form-label">Effective From</label><input type="date" class="form-control" name="effective_from" value="<?= e(date('Y-m-d')) ?>"></div>
          <div class="col-12"><label class="form-label">Reason</label><input class="form-control" name="reason" maxlength="255" placeholder="Increment / Revision" value="<?= $salary ? 'Salary revision' : 'Initial salary' ?>"></div>
        </div>
        <button class="btn btn-brand w-100 mt-4"><i class="fa-solid fa-check me-1"></i>Save Salary</button>
      </div>
    </form>
  </div>

  <div class="col-xl-7">
    <div class="ep-card cp tone-orange">
      <div class="ep-card-head">
        <span class="ep-card-ico"><i class="fa-solid fa-clock-rotate-left"></i></span>
        <div><h3>Salary History</h3><small><?= count($history) ?> revision(s)</small></div>
      </div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead><tr><th>Effective</th><th>Monthly Salary</th><th>Annual</th><th>Reason</th><th>By</th></tr></thead>
          <tbody>
          <?php foreach ($history as $h): ?>
            <tr>
              <td><?= format_date($h['effective_from']) ?></td>
              <td class="fw-bold"><?= money($h['monthly_salary']) ?></td>
              <td><?= money($h['monthly_salary'] * 12) ?></td>
              <td><?= e($h['reason']) ?></td>
              <td class="text-muted"><?= e($h['updated_by'] ?? '—') ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$history): ?><tr><td colspan="5" class="text-center text-muted py-4">No salary history yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
