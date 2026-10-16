<?php
$company = setting('company_name', $app['company'] ?? 'Ocean Infotech');
$expires = date('d M Y, h:i A', strtotime($invite['expires_at']));
$nextCode = $nextCode ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#211F23">
  <title>Employee Details · <?= e($company) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="<?= asset('assets/css/careers.css') ?>">
  <style>
    .sf-code-ro {
      background: #fff7ea !important; border-color: #fde7c2 !important;
      color: #8a5200 !important; font-weight: 800; letter-spacing: .04em;
    }
  </style>
</head>
<body>

<header class="cr-top">
  <img src="<?= asset($app['brand']['logo']) ?>" alt="<?= e($company) ?>">
</header>

<main class="cr-wrap">

  <section class="cr-card cr-intro" id="sfIntro">
    <div class="cr-logo"><img src="<?= asset($app['brand']['logo']) ?>" alt=""></div>
    <span class="cr-kicker">New employee registration</span>
    <h1><?= e($invite['title']) ?></h1>
    <p class="cr-sub"><?= e($company) ?></p>
    <p class="cr-text text-center">This form creates a <b>new</b> employee record. Your employee code is auto-generated. Fill all details except salary (HR only).</p>
    <ul class="cr-steps-info">
      <li><i class="fa-solid fa-user"></i> Personal</li>
      <li><i class="fa-solid fa-briefcase"></i> Designation</li>
      <li><i class="fa-solid fa-building-columns"></i> Bank &amp; KYC</li>
      <li><i class="fa-solid fa-phone"></i> Emergency</li>
    </ul>
    <p class="cr-note"><i class="fa-regular fa-clock me-1"></i>Link valid until <?= e($expires) ?></p>
    <button type="button" class="cr-btn cr-btn-primary w-100" id="sfStart">Start <i class="fa-solid fa-arrow-right ms-1"></i></button>
  </section>

  <form id="sfForm" class="d-none" method="post" action="<?= url('fill/' . $token) ?>" novalidate>
    <?= csrf_field() ?>
    <input type="text" name="website" class="cr-hp" tabindex="-1" autocomplete="off">

    <div class="cr-progress">
      <div class="cr-progress-bar"><span id="sfBar"></span></div>
      <div class="cr-progress-meta"><b id="sfStepTitle">Personal</b><span id="sfStepCount">Step 1 of 6</span></div>
    </div>

    <!-- 1 Personal (+ auto code) -->
    <section class="cr-card cr-step" data-step="1" data-title="Personal details">
      <h2><i class="fa-solid fa-user"></i> Personal</h2>
      <p class="cr-hint">New employee code (auto) · cannot be changed · this is not an edit form.</p>
      <div class="mb-3">
        <label class="form-label">New Employee Code</label>
        <input class="form-control sf-code-ro" name="employee_code_display" id="sfCode" value="<?= e($nextCode) ?>" readonly tabindex="-1">
      </div>
      <div class="row g-3">
        <div class="col-6"><label class="form-label req">First name</label><input class="form-control" name="first_name" required></div>
        <div class="col-6"><label class="form-label">Middle</label><input class="form-control" name="middle_name"></div>
        <div class="col-12"><label class="form-label req">Last name</label><input class="form-control" name="last_name" required></div>
        <div class="col-6">
          <label class="form-label">Gender</label>
          <select class="form-select" name="gender">
            <option value="">Select</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="col-6"><label class="form-label">Date of birth</label><input type="date" class="form-control" name="date_of_birth"></div>
        <div class="col-6">
          <label class="form-label">Blood group</label>
          <select class="form-select" name="blood_group">
            <option value="">Select</option>
            <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
              <option><?= $bg ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6">
          <label class="form-label">Marital status</label>
          <select class="form-select" name="marital_status">
            <option value="">Select</option>
            <?php foreach (['Single','Married','Divorced','Widowed'] as $ms): ?>
              <option><?= $ms ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12"><label class="form-label req">Mobile</label><input class="form-control" name="mobile" required inputmode="numeric" maxlength="10"></div>
        <div class="col-12"><label class="form-label">Personal email</label><input type="email" class="form-control" name="personal_email"></div>
        <div class="col-12"><label class="form-label">Official email</label><input type="email" class="form-control" name="official_email"></div>
        <div class="col-12"><label class="form-label">Nationality</label><input class="form-control" name="nationality" value="Indian"></div>
      </div>
    </section>

    <!-- 2 Employment (no salary) -->
    <section class="cr-card cr-step d-none" data-step="2" data-title="Employment">
      <h2><i class="fa-solid fa-briefcase"></i> Employment</h2>
      <p class="cr-hint">Department, designation &amp; job details. Salary is not collected here.</p>
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label req">Designation</label>
          <select class="form-select" name="designation_id" id="sfDesignation" required>
            <option value="">Select designation</option>
            <?php foreach ($designations as $g): ?>
              <option value="<?= (int) $g['id'] ?>" data-dept="<?= (int) ($g['department_id'] ?? 0) ?>"><?= e($g['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Department</label>
          <select class="form-select" name="department_id" id="sfDepartment">
            <option value="">Select department</option>
            <?php foreach ($departments as $d): ?>
              <option value="<?= (int) $d['id'] ?>"><?= e($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Employment type</label>
          <select class="form-select" name="employment_type">
            <option value="full_time">Full Time</option>
            <option value="intern">Intern</option>
            <option value="part_time">Part Time</option>
            <option value="contract">Contract</option>
            <option value="freelancer">Freelancer</option>
            <option value="consultant">Consultant</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Joining date</label>
          <input type="date" class="form-control" name="joining_date" value="<?= e(date('Y-m-d')) ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Work location</label>
          <input class="form-control" name="work_location" placeholder="e.g. Rajkot Office">
        </div>
      </div>
    </section>

    <!-- 3 Address -->
    <section class="cr-card cr-step d-none" data-step="3" data-title="Address">
      <h2><i class="fa-solid fa-location-dot"></i> Address</h2>
      <p class="cr-hint">Current &amp; permanent address.</p>
      <label class="form-label">Current address</label>
      <textarea class="form-control mb-3" name="current_address" rows="2"></textarea>
      <div class="row g-3 mb-3">
        <div class="col-6"><label class="form-label">City</label><input class="form-control" name="current_city"></div>
        <div class="col-6"><label class="form-label">State</label><input class="form-control" name="current_state"></div>
        <div class="col-6"><label class="form-label">Country</label><input class="form-control" name="current_country" value="India"></div>
        <div class="col-6"><label class="form-label">Pincode</label><input class="form-control" name="current_pincode" inputmode="numeric"></div>
      </div>
      <label class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="same_as_current" value="1" id="sfSame">
        <span class="form-check-label fw-bold">Permanent same as current</span>
      </label>
      <div id="sfPermWrap">
        <label class="form-label">Permanent address</label>
        <textarea class="form-control mb-3" name="permanent_address" rows="2"></textarea>
        <div class="row g-3">
          <div class="col-6"><label class="form-label">City</label><input class="form-control" name="permanent_city"></div>
          <div class="col-6"><label class="form-label">State</label><input class="form-control" name="permanent_state"></div>
          <div class="col-6"><label class="form-label">Country</label><input class="form-control" name="permanent_country" value="India"></div>
          <div class="col-6"><label class="form-label">Pincode</label><input class="form-control" name="permanent_pincode" inputmode="numeric"></div>
        </div>
      </div>
    </section>

    <!-- 4 Bank -->
    <section class="cr-card cr-step d-none" data-step="4" data-title="Bank details">
      <h2><i class="fa-solid fa-building-columns"></i> Bank</h2>
      <p class="cr-hint">Salary account (amount is managed by HR — not shown here).</p>
      <div class="row g-3">
        <div class="col-12"><label class="form-label">Bank name</label><input class="form-control" name="bank_name"></div>
        <div class="col-12"><label class="form-label">Account holder</label><input class="form-control" name="account_holder_name"></div>
        <div class="col-12"><label class="form-label">Account number</label><input class="form-control" name="account_number" inputmode="numeric"></div>
        <div class="col-6"><label class="form-label">IFSC</label><input class="form-control" name="ifsc_code" style="text-transform:uppercase" placeholder="HDFC0001234"></div>
        <div class="col-6"><label class="form-label">Branch</label><input class="form-control" name="bank_branch"></div>
        <div class="col-12">
          <label class="form-label">Payment mode</label>
          <select class="form-select" name="payment_mode">
            <option value="bank_transfer">Bank Transfer</option>
            <option value="cheque">Cheque</option>
            <option value="cash">Cash</option>
          </select>
        </div>
      </div>
    </section>

    <!-- 5 KYC -->
    <section class="cr-card cr-step d-none" data-step="5" data-title="KYC">
      <h2><i class="fa-solid fa-id-card"></i> KYC</h2>
      <p class="cr-hint">PAN &amp; Aadhaar for company records.</p>
      <div class="mb-3">
        <label class="form-label">PAN</label>
        <input class="form-control" name="pan_number" maxlength="10" style="text-transform:uppercase" placeholder="ABCDE1234F">
      </div>
      <div class="mb-0">
        <label class="form-label">Aadhaar (12 digit)</label>
        <input class="form-control" name="aadhaar_number" inputmode="numeric" maxlength="12" placeholder="XXXX XXXX XXXX">
      </div>
    </section>

    <!-- 6 Emergency -->
    <section class="cr-card cr-step d-none" data-step="6" data-title="Emergency contact">
      <h2><i class="fa-solid fa-phone-volume"></i> Emergency</h2>
      <p class="cr-hint">Person we should contact in an emergency.</p>
      <div class="row g-3">
        <div class="col-12"><label class="form-label">Contact name</label><input class="form-control" name="emergency_name"></div>
        <div class="col-12">
          <label class="form-label">Relationship</label>
          <select class="form-select" name="emergency_relationship">
            <option value="">Select</option>
            <?php foreach (['Father','Mother','Spouse','Brother','Sister','Friend','Other'] as $r): ?>
              <option><?= $r ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12"><label class="form-label">Mobile</label><input class="form-control" name="emergency_mobile" inputmode="numeric" maxlength="10"></div>
        <div class="col-12"><label class="form-label">Alternate</label><input class="form-control" name="emergency_alternate" inputmode="numeric" maxlength="10"></div>
        <div class="col-12"><label class="form-label">Address</label><input class="form-control" name="emergency_address"></div>
      </div>
      <label class="form-check mt-3">
        <input class="form-check-input" type="checkbox" name="declaration" value="1" id="sfDecl" required>
        <span class="form-check-label">I confirm the above details are correct.</span>
      </label>
    </section>

    <div class="cr-nav">
      <button type="button" class="cr-btn cr-btn-ghost" id="sfPrev" disabled><i class="fa-solid fa-arrow-left"></i> Back</button>
      <button type="button" class="cr-btn cr-btn-primary" id="sfNext">Next <i class="fa-solid fa-arrow-right"></i></button>
      <button type="submit" class="cr-btn cr-btn-primary d-none" id="sfSubmit"><i class="fa-solid fa-check me-1"></i> Submit</button>
    </div>
  </form>

  <section class="cr-card cr-intro d-none" id="sfDone">
    <div class="cr-logo"><i class="fa-solid fa-circle-check" style="font-size:42px;color:#16a34a"></i></div>
    <span class="cr-kicker">Submitted</span>
    <h1>Thank you!</h1>
    <p class="cr-text text-center" id="sfDoneMsg">Your details are saved in Ocean HRMS.</p>
  </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.SF = {
  submitUrl: <?= json_encode(url('fill/' . $token)) ?>,
  csrf: <?= json_encode(csrf_token()) ?>
};
</script>
<script src="<?= asset('assets/js/self-fill.js') ?>?v=4"></script>
</body>
</html>
