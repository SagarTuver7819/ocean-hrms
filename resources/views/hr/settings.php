<div class="page-head">
  <div>
    <h1>Company Settings</h1>
    <p>Company profile used on letterhead, career page and documents</p>
  </div>
  <a href="<?= url('settings/letterhead') ?>" class="btn btn-sm btn-brand"><i class="fa-solid fa-file-image me-1"></i> Letterhead Setup</a>
</div>

<form method="post" action="<?= url('settings') ?>" class="js-loading">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-lg-7">
      <div class="panel h-100">
        <div class="panel-header"><h3>Company Profile</h3></div>
        <div class="panel-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Company Name</label><input class="form-control" name="company_name" value="<?= e($settings['company_name'] ?? 'Ocean Infotech') ?>"></div>
            <div class="col-md-6"><label class="form-label">Tagline</label><input class="form-control" name="company_tagline" value="<?= e($settings['company_tagline'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">HR Email</label><input class="form-control" type="email" name="company_email" value="<?= e($settings['company_email'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="company_phone" value="<?= e($settings['company_phone'] ?? '') ?>"></div>
            <div class="col-12"><label class="form-label">Website</label><input class="form-control" name="company_website" value="<?= e($settings['company_website'] ?? '') ?>" placeholder="www.oceaninfotech.com"></div>
            <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="company_address" rows="3"><?= e($settings['company_address'] ?? '') ?></textarea></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="panel h-100">
        <div class="panel-header"><h3>Career Page</h3></div>
        <div class="panel-body">
          <label class="form-label">Positions on application form <small class="text-muted">(one per line)</small></label>
          <textarea class="form-control mb-1" name="careers_positions" rows="6"><?= e($settings['careers_positions'] ?? \App\Controllers\CareerController::DEFAULT_POSITIONS) ?></textarea>
          <small class="text-muted d-block mb-3">"Other" with a text box is always shown at the end.</small>
          <label class="form-label">Intro text on QR application form</label>
          <textarea class="form-control" name="careers_intro" rows="5"><?= e($settings['careers_intro'] ?? '') ?></textarea>
          <a href="<?= url('apply') ?>" target="_blank" class="small d-inline-block mt-2"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Preview career form</a>
        </div>
      </div>
    </div>
    <div class="col-12">
      <div class="panel">
        <div class="panel-header"><h3><i class="fa-solid fa-envelope-open-text me-2 text-warning"></i>HR Letters</h3><small class="text-muted">Used on Offer, Appointment, Experience &amp; Relieving letters</small></div>
        <div class="panel-body">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Reference Prefix</label><input class="form-control" name="letter_ref_prefix" value="<?= e($settings['letter_ref_prefix'] ?? 'OI/HR') ?>" placeholder="OI/HR"><small class="text-muted">e.g. OI/HR/OFFER/26-27/0001</small></div>
            <div class="col-md-4"><label class="form-label">Signatory Name</label><input class="form-control" name="letter_signatory_name" value="<?= e($settings['letter_signatory_name'] ?? '') ?>" placeholder="HR Manager"></div>
            <div class="col-md-4"><label class="form-label">Signatory Designation</label><input class="form-control" name="letter_signatory_designation" value="<?= e($settings['letter_signatory_designation'] ?? '') ?>" placeholder="Human Resources"></div>
            <div class="col-md-4"><label class="form-label">Week Off</label><input class="form-control" name="letter_week_off" value="<?= e($settings['letter_week_off'] ?? 'Sunday') ?>"></div>
            <div class="col-md-4"><label class="form-label">Notice Period (after confirmation)</label><input class="form-control" name="letter_notice_period" value="<?= e($settings['letter_notice_period'] ?? '1 month') ?>"></div>
            <div class="col-md-4"><label class="form-label">Jurisdiction / Default Posting</label><input class="form-control" name="letter_jurisdiction" value="<?= e($settings['letter_jurisdiction'] ?? 'Ahmedabad, Gujarat') ?>"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="mt-3"><button class="btn btn-brand"><i class="fa-solid fa-check me-1"></i>Save Settings</button></div>
</form>
