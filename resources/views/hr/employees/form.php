<?php
$e = $employee;
$isEdit = ($mode ?? '') === 'edit';
$bank = $e['bank_details'] ?? [];
$em = $e['emergency_contacts'] ?? [];

$head = fn(string $icon, string $title, string $sub) =>
    '<div class="ep-card-head"><span class="ep-card-ico"><i class="fa-solid ' . $icon . '"></i></span><div><h3>' . $title . '</h3><small>' . $sub . '</small></div></div>';

$field = function (string $label, string $name, $value = '', string $type = 'text', array $opt = []): string {
    $req = !empty($opt['required']);
    $attrs = ($req ? ' required' : '') . (!empty($opt['readonly']) ? ' readonly' : '') . (isset($opt['placeholder']) ? ' placeholder="' . e($opt['placeholder']) . '"' : '');
    return '<div class="' . ($opt['col'] ?? 'col-lg-3 col-md-6') . '"><label class="form-label' . ($req ? ' required' : '') . '">' . $label . '</label>'
        . '<input type="' . $type . '" class="form-control" name="' . $name . '" value="' . e((string) ($value ?? '')) . '"' . $attrs . '></div>';
};

$select = function (string $label, string $name, array $options, $current, array $opt = []): string {
    $html = '<div class="' . ($opt['col'] ?? 'col-lg-3 col-md-6') . '"><label class="form-label">' . $label . '</label>'
        . '<select class="form-select' . (!empty($opt['search']) ? ' select2' : '') . '" name="' . $name . '">';
    if (!array_key_exists('placeholder', $opt) || $opt['placeholder'] !== false) $html .= '<option value="">Select</option>';
    $current = (string) ($current ?? '');
    if ($current !== '' && !array_key_exists($current, $options)) $options = [$current => $current] + $options;
    foreach ($options as $value => $text) {
        $html .= '<option value="' . e((string) $value) . '"' . ((string) $value === $current ? ' selected' : '') . '>' . e($text) . '</option>';
    }
    return $html . '</select></div>';
};

$pairs = fn(array $rows) => array_column($rows, 'name', 'id');
$titled = fn(array $keys) => array_combine($keys, array_map(fn($k) => ucwords(str_replace('_', ' ', $k)), $keys));

$tabs = [
    'personal'   => ['Personal', 'fa-user'],
    'address'    => ['Address', 'fa-location-dot'],
    'employment' => ['Employment', 'fa-briefcase'],
    'bank'       => ['Bank', 'fa-building-columns'],
    'compliance' => ['KYC', 'fa-id-card'],
    'emergency'  => ['Emergency', 'fa-phone-volume'],
];
?>
<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit Employee' : 'Add Employee' ?></h1>
    <p><?= $isEdit ? e(trim(($e['first_name'] ?? '') . ' ' . ($e['last_name'] ?? ''))) . ' · ' . e($e['employee_code'] ?? '') : 'Employee master form · Ocean Infotech' ?></p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('employees') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-arrow-left me-1"></i>Employee List</a>
    <button form="employeeForm" class="btn btn-sm btn-brand"><i class="fa-solid fa-check me-1"></i><?= $isEdit ? 'Update Employee' : 'Save Employee' ?></button>
  </div>
</div>

<form method="post" id="employeeForm" action="<?= $isEdit ? url('employees/' . $e['id']) : url('employees') ?>" class="emp-form js-loading">
  <?= csrf_field() ?>

  <nav class="ep-tabs nav" role="tablist" aria-label="Employee form sections">
    <?php $i = 0; foreach ($tabs as $key => [$label, $icon]): $i++; ?>
      <button type="button" class="ep-tab <?= $i === 1 ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-<?= $key ?>" role="tab">
        <span class="ep-tab-ico"><i class="fa-solid <?= $icon ?>"></i></span><span class="ef-step"><small>Step <?= $i ?></small><?= $label ?></span>
      </button>
    <?php endforeach; ?>
  </nav>

  <div class="ep-card cp tone-orange">
    <div class="tab-content">

      <div class="tab-pane fade show active" id="tab-personal" role="tabpanel">
        <?= $head('fa-user', 'Personal Details', 'Name, contact & identity') ?>
        <div class="cp-body">
          <div class="row g-3">
            <?= $field('Employee Code', 'employee_code', $e['employee_code'] ?? $nextCode, 'text', ['readonly' => $isEdit]) ?>
            <?= $field('First Name', 'first_name', $e['first_name'] ?? '', 'text', ['required' => true]) ?>
            <?= $field('Middle Name', 'middle_name', $e['middle_name'] ?? '') ?>
            <?= $field('Last Name', 'last_name', $e['last_name'] ?? '', 'text', ['required' => true]) ?>
            <?= $select('Gender', 'gender', $titled(['male', 'female', 'other']), $e['gender'] ?? '') ?>
            <?= $field('Date of Birth', 'date_of_birth', $e['date_of_birth'] ?? '', 'date') ?>
            <?= $select('Blood Group', 'blood_group', array_combine($bg = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], $bg), $e['blood_group'] ?? '') ?>
            <?= $select('Marital Status', 'marital_status', array_combine($ms = ['Single', 'Married', 'Divorced', 'Widowed'], $ms), $e['marital_status'] ?? '') ?>
            <?= $field('Mobile', 'mobile', $e['mobile'] ?? '', 'tel', ['placeholder' => '10-digit mobile']) ?>
            <?= $field('Personal Email', 'personal_email', $e['personal_email'] ?? '', 'email') ?>
            <?= $field('Official Email', 'official_email', $e['official_email'] ?? '', 'email', ['placeholder' => 'name@oceaninfotech.com']) ?>
            <?= $field('Nationality', 'nationality', $e['nationality'] ?? 'Indian') ?>
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="tab-address" role="tabpanel">
        <?= $head('fa-location-dot', 'Address', 'Current & permanent address') ?>
        <div class="cp-body">
          <div class="form-section-title mt-0">Current Address</div>
          <div class="row g-3">
            <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="current_address" rows="2"><?= e($e['current_address'] ?? '') ?></textarea></div>
            <?= $field('City', 'current_city', $e['current_city'] ?? '') ?>
            <?= $field('State', 'current_state', $e['current_state'] ?? '') ?>
            <?= $field('Country', 'current_country', $e['current_country'] ?? 'India') ?>
            <?= $field('Pincode', 'current_pincode', $e['current_pincode'] ?? '') ?>
          </div>
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4">
            <div class="form-section-title m-0 border-0 p-0">Permanent Address</div>
            <label class="form-check m-0">
              <input class="form-check-input" type="checkbox" name="same_as_current" value="1" id="same_as_current" <?= !empty($e['same_as_current']) ? 'checked' : '' ?>>
              <span class="form-check-label">Same as current address</span>
            </label>
          </div>
          <hr class="ef-rule">
          <div class="row g-3">
            <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="permanent_address" rows="2"><?= e($e['permanent_address'] ?? '') ?></textarea></div>
            <?= $field('City', 'permanent_city', $e['permanent_city'] ?? '') ?>
            <?= $field('State', 'permanent_state', $e['permanent_state'] ?? '') ?>
            <?= $field('Country', 'permanent_country', $e['permanent_country'] ?? 'India') ?>
            <?= $field('Pincode', 'permanent_pincode', $e['permanent_pincode'] ?? '') ?>
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="tab-employment" role="tabpanel">
        <?= $head('fa-briefcase', 'Employment', 'Role, reporting & work details') ?>
        <div class="cp-body">
          <div class="row g-3">
            <?= $field('Joining Date', 'joining_date', $e['joining_date'] ?? '', 'date') ?>
            <?= $select('Employment Type', 'employment_type', $titled(['full_time', 'part_time', 'intern', 'freelancer', 'contract', 'consultant']), $e['employment_type'] ?? 'full_time', ['placeholder' => false]) ?>
            <?= $select('Department', 'department_id', $pairs($departments), $e['department_id'] ?? '', ['search' => true]) ?>
            <?= $select('Designation', 'designation_id', $pairs($designations), $e['designation_id'] ?? '', ['search' => true]) ?>
            <?= $select('Reporting Manager', 'reporting_manager_id', $pairs($managers), $e['reporting_manager_id'] ?? '', ['search' => true]) ?>
            <?= $select('Team', 'team_id', $pairs($teams), $e['team_id'] ?? '') ?>
            <?= $select('Branch', 'branch_id', $pairs($branches), $e['branch_id'] ?? '') ?>
            <?= $select('Shift', 'shift_id', $pairs($shifts), $e['shift_id'] ?? '') ?>
            <?= $field('Work Location', 'work_location', $e['work_location'] ?? '', 'text', ['placeholder' => 'e.g. Ahmedabad Office']) ?>
            <?= $field('Probation (days)', 'probation_period_days', $e['probation_period_days'] ?? 90, 'number') ?>
            <?= $field('Confirmation Date', 'confirmation_date', $e['confirmation_date'] ?? '', 'date') ?>
            <?= $field('Notice Period (days)', 'notice_period_days', $e['notice_period_days'] ?? 30, 'number') ?>
            <?= $select('Status', 'status', $titled(['draft', 'onboarding', 'probation', 'active', 'inactive', 'resigned']), $e['status'] ?? 'onboarding', ['placeholder' => false]) ?>
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="tab-bank" role="tabpanel">
        <?= $head('fa-building-columns', 'Bank Details', 'Salary is paid to this account') ?>
        <div class="cp-body">
          <div class="row g-3">
            <?= $field('Bank Name', 'bank_name', $bank['bank_name'] ?? '', 'text', ['col' => 'col-lg-4 col-md-6']) ?>
            <?= $field('Account Holder', 'account_holder_name', $bank['account_holder_name'] ?? '', 'text', ['col' => 'col-lg-4 col-md-6']) ?>
            <?= $field('Account Number', 'account_number', $bank['account_number'] ?? '', 'text', ['col' => 'col-lg-4 col-md-6']) ?>
            <?= $field('IFSC', 'ifsc_code', $bank['ifsc_code'] ?? '', 'text', ['col' => 'col-lg-4 col-md-6', 'placeholder' => 'e.g. HDFC0001234']) ?>
            <?= $field('Branch', 'bank_branch', $bank['branch'] ?? '', 'text', ['col' => 'col-lg-4 col-md-6']) ?>
            <?= $select('Payment Mode', 'payment_mode', ['bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'cash' => 'Cash'], $bank['payment_mode'] ?? 'bank_transfer', ['col' => 'col-lg-4 col-md-6', 'placeholder' => false]) ?>
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="tab-compliance" role="tabpanel">
        <?= $head('fa-id-card', 'KYC', 'PAN & Aadhaar details') ?>
        <div class="cp-body">
          <div class="row g-3">
            <?= $field('PAN', 'pan_number', $e['pan_number'] ?? '', 'text', ['placeholder' => 'ABCDE1234F', 'col' => 'col-lg-4 col-md-6']) ?>
            <?= $field('Aadhaar', 'aadhaar_number', $e['aadhaar_number'] ?? '', 'text', ['placeholder' => '12-digit number', 'col' => 'col-lg-4 col-md-6']) ?>
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="tab-emergency" role="tabpanel">
        <?= $head('fa-phone-volume', 'Emergency Contact', 'Person to contact in an emergency') ?>
        <div class="cp-body">
          <div class="row g-3">
            <?= $field('Contact Name', 'emergency_name', $em['contact_name'] ?? '', 'text', ['col' => 'col-lg-4 col-md-6']) ?>
            <?= $select('Relationship', 'emergency_relationship', array_combine($rel = ['Father', 'Mother', 'Spouse', 'Brother', 'Sister', 'Friend', 'Other'], $rel), $em['relationship'] ?? '', ['col' => 'col-lg-4 col-md-6']) ?>
            <?= $field('Mobile', 'emergency_mobile', $em['mobile'] ?? '', 'tel', ['col' => 'col-lg-4 col-md-6']) ?>
            <?= $field('Alternate Number', 'emergency_alternate', $em['alternate_number'] ?? '', 'tel', ['col' => 'col-lg-4 col-md-6']) ?>
            <?= $field('Address', 'emergency_address', $em['address'] ?? '', 'text', ['col' => 'col-lg-8']) ?>
          </div>
        </div>
      </div>

    </div>

    <div class="ef-actions">
      <button type="button" class="btn btn-sm btn-outline-ink js-step" data-step="-1"><i class="fa-solid fa-arrow-left me-1"></i>Previous</button>
      <span class="ef-progress js-step-label">Step 1 of <?= count($tabs) ?></span>
      <div class="d-flex gap-2">
        <a href="<?= url('employees') ?>" class="btn btn-sm btn-outline-ink">Cancel</a>
        <button type="button" class="btn btn-sm btn-soft js-step" data-step="1">Next<i class="fa-solid fa-arrow-right ms-1"></i></button>
        <button class="btn btn-sm btn-brand"><i class="fa-solid fa-check me-1"></i><span class="btn-submit-text"><?= $isEdit ? 'Update Employee' : 'Save Employee' ?></span></button>
      </div>
    </div>
  </div>
</form>
