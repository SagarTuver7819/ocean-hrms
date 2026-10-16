<?php
use App\Models\Candidate;

$company = setting('company_name', $app['company']);
$intro = setting('careers_intro', '');
$degrees = ['B.E. / B.Tech', 'M.E. / M.Tech', 'MCA', 'BCA', 'M.Sc (IT / CS)', 'B.Sc (IT / CS)', 'MBA', 'BBA', 'B.Com', 'Diploma', 'HSC (12th)', 'SSC (10th)'];
$skillHints = ['PHP', 'Laravel', 'CodeIgniter', 'JavaScript', 'TypeScript', 'React', 'Angular', 'Vue.js', 'Node.js', 'Python', 'Django', 'Java', 'Spring Boot', '.NET', 'Flutter', 'React Native', 'Android', 'iOS', 'MySQL', 'MongoDB', 'PostgreSQL', 'AWS', 'Azure', 'Docker', 'Git', 'Figma', 'Manual Testing', 'Selenium', 'SEO', 'WordPress'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#211F23">
  <title>Careers · <?= e($company) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="<?= asset('assets/css/careers.css') ?>">
</head>
<body>

<header class="cr-top">
  <img src="<?= asset($app['brand']['logo']) ?>" alt="<?= e($company) ?>">
</header>

<main class="cr-wrap">

  <!-- Intro -->
  <section class="cr-card cr-intro" id="crIntro">
    <div class="cr-logo"><img src="<?= asset($app['brand']['logo']) ?>" alt=""></div>
    <span class="cr-kicker">Join our team</span>
    <h1><?= e($company) ?></h1>
    <p class="cr-sub">Software · Web · Mobile · Cloud</p>
    <?php if ($intro): ?><p class="cr-text"><?= nl2br(e($intro)) ?></p><?php endif; ?>
    <ul class="cr-steps-info">
      <li><i class="fa-solid fa-briefcase"></i> Select position</li>
      <li><i class="fa-solid fa-user"></i> Personal details</li>
      <li><i class="fa-solid fa-graduation-cap"></i> Education &amp; experience</li>
      <li><i class="fa-solid fa-code"></i> Skills &amp; resume</li>
    </ul>
    <button type="button" class="cr-btn cr-btn-primary w-100" id="crStart">Start Application <i class="fa-solid fa-arrow-right ms-1"></i></button>
    <p class="cr-note"><i class="fa-regular fa-clock me-1"></i>Takes about 5 minutes · Keep your resume ready</p>
  </section>

  <!-- Wizard -->
  <form id="crForm" class="d-none" method="post" action="<?= url('apply') ?>" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <input type="text" name="website" class="cr-hp" tabindex="-1" autocomplete="off">

    <div class="cr-progress">
      <div class="cr-progress-bar"><span id="crBar"></span></div>
      <div class="cr-progress-meta"><b id="crStepTitle">Position</b><span id="crStepCount">Step 1 of 6</span></div>
    </div>

    <!-- Step 1: Position -->
    <section class="cr-card cr-step" data-step="1" data-title="Position Applied For">
      <h2><i class="fa-solid fa-briefcase"></i> Position Applied For</h2>
      <p class="cr-hint">Choose the role you are interviewing for.</p>

      <div class="mb-3">
        <label class="form-label req">Position</label>
        <select class="form-select" name="position" id="crDesig" required>
          <option value="">Select position</option>
          <?php foreach ($positions as $p): ?>
            <option value="<?= e($p) ?>"><?= e($p) ?></option>
          <?php endforeach; ?>
          <option value="__other">Other</option>
        </select>
      </div>
      <div class="mb-3 d-none" id="crOtherWrap">
        <label class="form-label req">Enter position</label>
        <input class="form-control" name="position_other" id="crOther" maxlength="150" placeholder="e.g. Flutter Developer">
      </div>

      <label class="form-label">Employment type</label>
      <div class="cr-chips mb-3">
        <?php foreach (['full_time' => 'Full Time', 'intern' => 'Internship', 'part_time' => 'Part Time'] as $v => $l): ?>
          <label class="cr-chip"><input type="radio" name="employment_type" value="<?= $v ?>" <?= $v === 'full_time' ? 'checked' : '' ?>><span><?= $l ?></span></label>
        <?php endforeach; ?>
      </div>

      <div class="row g-3">
        <div class="col-12 col-sm-6">
          <label class="form-label">How did you hear about us?</label>
          <select class="form-select" name="source" id="crSource">
            <option value="">Select</option>
            <?php foreach (Candidate::SOURCES as $src): ?>
              <option><?= e($src) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-sm-6 d-none" id="crRefWrap">
          <label class="form-label">Referred by (employee name)</label>
          <input class="form-control" name="referred_by" maxlength="120">
        </div>
        <div class="col-12 col-sm-6 d-none" id="crSourceOtherWrap">
          <label class="form-label req">Please specify</label>
          <input class="form-control" name="source_other" id="crSourceOther" maxlength="50" placeholder="e.g. Friend, Newspaper">
        </div>
      </div>
    </section>

    <!-- Step 2: Personal -->
    <section class="cr-card cr-step d-none" data-step="2" data-title="Personal Details">
      <h2><i class="fa-solid fa-user"></i> Personal Details</h2>
      <div class="row g-3">
        <div class="col-12 col-sm-4"><label class="form-label req">First Name</label><input class="form-control" name="first_name" required maxlength="80" autocomplete="given-name"></div>
        <div class="col-12 col-sm-4"><label class="form-label">Middle Name</label><input class="form-control" name="middle_name" maxlength="80" autocomplete="additional-name"></div>
        <div class="col-12 col-sm-4"><label class="form-label req">Last Name</label><input class="form-control" name="last_name" required maxlength="80" autocomplete="family-name"></div>
        <div class="col-12 col-sm-6"><label class="form-label req">Mobile</label><input class="form-control" type="tel" name="mobile" required pattern="[6-9][0-9]{9}" maxlength="10" inputmode="numeric" placeholder="10-digit mobile" autocomplete="tel-national"></div>
        <div class="col-12 col-sm-6"><label class="form-label req">Parents Contact Number</label><input class="form-control" type="tel" name="parent_mobile" required pattern="[6-9][0-9]{9}" maxlength="10" inputmode="numeric" placeholder="Father / Mother mobile"></div>
        <div class="col-12"><label class="form-label req">Email</label><input class="form-control" type="email" name="email" required maxlength="150" autocomplete="email"></div>
        <div class="col-8 col-sm-4"><label class="form-label">Date of Birth</label><input class="form-control" type="date" name="date_of_birth" id="crDob" max="<?= date('Y-m-d', strtotime('-15 years')) ?>"></div>
        <div class="col-4 col-sm-2"><label class="form-label">Age</label><input class="form-control" id="crAge" readonly tabindex="-1" placeholder="—"></div>
        <div class="col-6 col-sm-3">
          <label class="form-label">Gender</label>
          <select class="form-select" name="gender"><option value="">Select</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select>
        </div>
        <div class="col-6 col-sm-3">
          <label class="form-label">Marital Status</label>
          <select class="form-select" name="marital_status"><option value="">Select</option><option>Single</option><option>Married</option><option>Other</option></select>
        </div>
        <div class="col-12"><label class="form-label req">Current Address</label><textarea class="form-control" name="address" rows="2" required maxlength="1000" autocomplete="street-address"></textarea></div>
        <div class="col-6 col-sm-4"><label class="form-label">City</label><input class="form-control" name="city" maxlength="80"></div>
        <div class="col-6 col-sm-4"><label class="form-label">State</label><input class="form-control" name="state" maxlength="80" value="Gujarat"></div>
        <div class="col-12 col-sm-4"><label class="form-label">Pincode</label><input class="form-control" name="pincode" pattern="[0-9]{6}" maxlength="6" inputmode="numeric"></div>
        <div class="col-12 col-sm-6"><label class="form-label">Aadhaar No.</label><input class="form-control" name="aadhaar_number" pattern="[0-9]{12}" maxlength="12" inputmode="numeric" placeholder="12 digits"></div>
        <div class="col-12 col-sm-6"><label class="form-label">PAN No.</label><input class="form-control text-uppercase" name="pan_number" pattern="[A-Za-z]{5}[0-9]{4}[A-Za-z]" maxlength="10" placeholder="ABCDE1234F"></div>
      </div>
    </section>

    <!-- Step 3: Education -->
    <section class="cr-card cr-step d-none" data-step="3" data-title="Education">
      <h2><i class="fa-solid fa-graduation-cap"></i> Education</h2>
      <p class="cr-hint">Add your qualifications, highest first.</p>
      <datalist id="crDegrees"><?php foreach ($degrees as $dg): ?><option value="<?= e($dg) ?>"><?php endforeach; ?></datalist>
      <div id="crEduList"></div>
      <button type="button" class="cr-btn cr-btn-ghost w-100" id="crAddEdu"><i class="fa-solid fa-plus me-1"></i> Add Qualification</button>
    </section>

    <!-- Step 4: Experience (hidden for Internship) -->
    <section class="cr-card cr-step d-none" data-step="4" data-title="Experience & CTC" id="crStepExp">
      <h2 id="crStepExpTitle"><i class="fa-solid fa-building"></i> Experience &amp; CTC</h2>
      <p class="cr-hint d-none" id="crInternNote">Internship selected — work experience is not required. Add expected stipend if any.</p>
      <label class="cr-switch mb-3" id="crFresherWrap">
        <input type="checkbox" name="is_fresher" value="1" id="crFresher">
        <span>I am a fresher (no work experience)</span>
      </label>

      <div id="crExpBlock">
        <p class="cr-hint">Add each company with From / To month. Total experience is calculated automatically.</p>
        <div id="crExpList"></div>
        <button type="button" class="cr-btn cr-btn-ghost w-100 mb-3" id="crAddExp"><i class="fa-solid fa-plus me-1"></i> Add Company</button>
        <div class="cr-total">Total experience: <b id="crTotalExp">—</b></div>
      </div>

      <div class="row g-3 mt-1">
        <div class="col-6" id="crCurCtcWrap"><label class="form-label">Current CTC (₹ / year)</label><input class="form-control" type="number" name="current_ctc" min="0" step="1000" inputmode="numeric" placeholder="e.g. 360000"></div>
        <div class="col-6"><label class="form-label req" id="crExpCtcLabel">Expected CTC (₹ / year)</label><input class="form-control" type="number" name="expected_ctc" min="0" step="1000" inputmode="numeric" required placeholder="e.g. 480000" id="crExpCtc"></div>
        <div class="col-6">
          <label class="form-label">Notice Period</label>
          <select class="form-select" name="notice_period">
            <option value="">Select</option>
            <?php foreach (Candidate::NOTICE_PERIODS as $np): ?><option><?= e($np) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-6"><label class="form-label">Current Location</label><input class="form-control" name="current_location" maxlength="120" placeholder="City"></div>
        <div class="col-12">
          <label class="cr-switch"><input type="checkbox" name="ready_to_relocate" value="1"><span>Ready to relocate</span></label>
        </div>
      </div>
    </section>

    <!-- Step 5: Skills -->
    <section class="cr-card cr-step d-none" data-step="5" data-title="Technical Skills">
      <h2><i class="fa-solid fa-code"></i> Technical Skills</h2>
      <div class="mb-2">
        <label class="form-label req">Primary Skills</label>
        <textarea class="form-control" name="primary_skills" id="crSkills" rows="2" required maxlength="1000" placeholder="e.g. PHP, Laravel, MySQL, JavaScript"></textarea>
      </div>
      <div class="cr-tags mb-3" id="crSkillHints">
        <?php foreach ($skillHints as $sk): ?><button type="button" class="cr-tag"><?= e($sk) ?></button><?php endforeach; ?>
      </div>
      <div class="row g-3">
        <div class="col-12"><label class="form-label">Other Skills / Tools</label><textarea class="form-control" name="secondary_skills" rows="2" maxlength="1000" placeholder="e.g. Git, Jira, Postman, Docker"></textarea></div>
        <div class="col-12"><label class="form-label">Certifications</label><input class="form-control" name="certifications" maxlength="1000" placeholder="e.g. AWS Cloud Practitioner"></div>
        <div class="col-12 col-sm-4"><label class="form-label">GitHub</label><input class="form-control" type="url" name="github_url" maxlength="255" placeholder="https://github.com/…"></div>
        <div class="col-12 col-sm-4"><label class="form-label">LinkedIn</label><input class="form-control" type="url" name="linkedin_url" maxlength="255" placeholder="https://linkedin.com/in/…"></div>
        <div class="col-12 col-sm-4"><label class="form-label">Portfolio / Website</label><input class="form-control" type="url" name="portfolio_url" maxlength="255" placeholder="https://…"></div>
        <div class="col-12"><label class="form-label">Best project you worked on</label><textarea class="form-control" name="best_project" rows="3" maxlength="2000" placeholder="Project name, your role, technologies used"></textarea></div>
        <div class="col-12"><label class="form-label">Why do you want to join <?= e($company) ?>?</label><textarea class="form-control" name="why_join" rows="2" maxlength="2000"></textarea></div>
      </div>
    </section>

    <!-- Step 6: Documents -->
    <section class="cr-card cr-step d-none" data-step="6" data-title="Documents & Submit">
      <h2><i class="fa-solid fa-file-arrow-up"></i> Documents &amp; Submit</h2>
      <div class="row g-3">
        <div class="col-12 col-sm-6">
          <label class="form-label">Passport Photo</label>
          <label class="cr-upload" id="crPhotoBox">
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" id="crPhoto">
            <img id="crPhotoPreview" alt="" class="d-none">
            <span class="cr-upload-txt"><i class="fa-solid fa-camera"></i> Tap to upload photo<small>JPG / PNG · max 5MB</small></span>
          </label>
        </div>
        <div class="col-12 col-sm-6">
          <label class="form-label req">Resume / CV</label>
          <label class="cr-upload">
            <input type="file" name="resume" accept=".pdf,.doc,.docx" id="crResume" required>
            <span class="cr-upload-txt" id="crResumeTxt"><i class="fa-solid fa-file-pdf"></i> Tap to upload resume<small>PDF / DOC / DOCX · max 5MB</small></span>
          </label>
        </div>
      </div>

      <div class="cr-review mt-3" id="crReview"></div>

      <label class="cr-check mt-3">
        <input type="checkbox" name="declaration" value="1" required>
        <span>I declare that the information provided is true and complete to the best of my knowledge.</span>
      </label>
    </section>

    <div class="cr-nav">
      <button type="button" class="cr-btn cr-btn-ghost" id="crBack"><i class="fa-solid fa-arrow-left me-1"></i> Back</button>
      <button type="button" class="cr-btn cr-btn-primary" id="crNext">Next <i class="fa-solid fa-arrow-right ms-1"></i></button>
      <button type="submit" class="cr-btn cr-btn-primary d-none" id="crSubmit"><i class="fa-solid fa-paper-plane me-1"></i> Submit Application</button>
    </div>
  </form>
</main>

<footer class="cr-foot">© <?= date('Y') ?> <?= e($company) ?> · Designed &amp; Developed by Ocean Infotech</footer>

<template id="tplEdu">
  <div class="cr-row">
    <button type="button" class="cr-row-del" title="Remove"><i class="fa-solid fa-xmark"></i></button>
    <div class="row g-2">
      <div class="col-12 col-sm-6"><label class="form-label req">Degree / Course</label><input class="form-control" name="edu[__i__][degree]" list="crDegrees" maxlength="120" required></div>
      <div class="col-12 col-sm-6"><label class="form-label">College / University</label><input class="form-control" name="edu[__i__][institute]" maxlength="150"></div>
      <div class="col-12 col-sm-6"><label class="form-label">Specialization</label><input class="form-control" name="edu[__i__][specialization]" maxlength="120" placeholder="e.g. Computer Engineering"></div>
      <div class="col-6 col-sm-3"><label class="form-label">Year</label><input class="form-control" name="edu[__i__][year]" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" placeholder="YYYY"></div>
      <div class="col-6 col-sm-3"><label class="form-label">% / CGPA</label><input class="form-control" name="edu[__i__][percentage]" maxlength="40"></div>
    </div>
  </div>
</template>

<template id="tplExp">
  <div class="cr-row">
    <button type="button" class="cr-row-del" title="Remove"><i class="fa-solid fa-xmark"></i></button>
    <div class="row g-2">
      <div class="col-12 col-sm-6"><label class="form-label req">Company Name</label><input class="form-control" name="exp[__i__][company]" maxlength="150" required></div>
      <div class="col-12 col-sm-6"><label class="form-label">Designation</label><input class="form-control" name="exp[__i__][designation]" maxlength="120"></div>
      <div class="col-6"><label class="form-label req">From</label><input class="form-control js-from" type="month" name="exp[__i__][from]" required max="<?= date('Y-m') ?>"></div>
      <div class="col-6"><label class="form-label">To</label><input class="form-control js-to" type="month" name="exp[__i__][to]" max="<?= date('Y-m') ?>"></div>
      <div class="col-12 d-flex justify-content-between align-items-center">
        <label class="cr-switch sm"><input type="checkbox" class="js-current" name="exp[__i__][is_current]" value="1"><span>Currently working here</span></label>
        <small class="cr-dur js-dur">—</small>
      </div>
      <div class="col-12"><label class="form-label">Technologies used</label><input class="form-control" name="exp[__i__][tech_stack]" maxlength="255" placeholder="e.g. Laravel, Vue.js, MySQL"></div>
      <div class="col-12"><label class="form-label">Key Responsibilities</label><textarea class="form-control" name="exp[__i__][responsibilities]" rows="2" maxlength="2000"></textarea></div>
    </div>
  </div>
</template>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= asset('assets/js/careers.js') ?>"></script>
</body>
</html>
