<?php
$company = setting('company_name', $app['company']);
?>
<div class="page-head">
  <div>
    <h1>Employee Self-Fill QR</h1>
    <p>Time-limited link · employees fill personal / bank / KYC / emergency (no salary) · saves into Employee master</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('employees') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-arrow-left me-1"></i>Employees</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="ep-card cp tone-orange">
      <div class="ep-card-head">
        <span class="ep-card-ico"><i class="fa-solid fa-link"></i></span>
        <div><h3>Generate new link</h3><small>Old active link will be deactivated</small></div>
      </div>
      <div class="cp-body">
        <form method="post" action="<?= url('employees/self-fill') ?>" class="js-loading">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="Employee details form" maxlength="150">
          </div>
          <div class="mb-3">
            <label class="form-label">Valid for (days)</label>
            <select name="days" class="form-select">
              <?php foreach ([1, 2, 3, 5, 7, 10, 14, 21, 30] as $d): ?>
                <option value="<?= $d ?>" <?= $d === 7 ? 'selected' : '' ?>><?= $d ?> day<?= $d > 1 ? 's' : '' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-brand w-100"><i class="fa-solid fa-qrcode me-1"></i>Generate QR &amp; Link</button>
        </form>
      </div>
    </div>

    <?php if ($invites): ?>
      <div class="ep-card cp mt-3">
        <div class="ep-card-head">
          <span class="ep-card-ico"><i class="fa-solid fa-clock-rotate-left"></i></span>
          <div><h3>Recent invites</h3><small>Who filled &amp; expiry</small></div>
        </div>
        <div class="cp-body p-0">
          <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
              <thead><tr><th>Title</th><th>Expires</th><th>Filled</th><th></th></tr></thead>
              <tbody>
              <?php foreach ($invites as $inv):
                $live = (int) $inv['is_active'] === 1 && strtotime($inv['expires_at']) > time();
              ?>
                <tr>
                  <td>
                    <b><?= e($inv['title']) ?></b>
                    <div class="small text-muted"><?= $live ? '<span class="text-success">Active</span>' : 'Inactive' ?></div>
                  </td>
                  <td class="small"><?= e(date('d-m-Y H:i', strtotime($inv['expires_at']))) ?></td>
                  <td><span class="badge bg-light text-dark"><?= (int) $inv['filled_count'] ?></span></td>
                  <td class="text-end">
                    <?php if ($live): ?>
                      <form method="post" action="<?= url('employees/self-fill/' . $inv['id'] . '/revoke') ?>" class="d-inline">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-outline-danger" data-confirm="Deactivate this link now?">Revoke</button>
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
    <?php endif; ?>
  </div>

  <div class="col-lg-7">
    <?php if ($active && $fillUrl): ?>
      <?php if ($isLocal): ?>
        <div class="alert alert-warning small"><i class="fa-solid fa-triangle-exclamation me-1"></i>
          QR currently points to localhost. On live, set <code>public_url</code> in env.php to <b>https://hrms.oceanhub.co.in</b>.
        </div>
      <?php endif; ?>

      <div class="ep-card cp tone-orange mb-3">
        <div class="ep-card-head">
          <span class="ep-card-ico"><i class="fa-brands fa-whatsapp"></i></span>
          <div><h3>Share with employees</h3><small>Expires <?= e(date('d M Y, h:i A', strtotime($active['expires_at']))) ?></small></div>
        </div>
        <div class="cp-body">
          <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-ink" id="sfCopy"><i class="fa-regular fa-copy me-1"></i>Copy Link</button>
            <a class="btn btn-sm btn-success" id="sfWa" target="_blank" rel="noopener"
               href="https://wa.me/?text=<?= rawurlencode($waText) ?>">
              <i class="fa-brands fa-whatsapp me-1"></i>WhatsApp Share
            </a>
            <button type="button" class="btn btn-sm btn-outline-ink" id="sfDownload"><i class="fa-solid fa-download me-1"></i>QR PNG</button>
            <button type="button" class="btn btn-sm btn-brand" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print</button>
          </div>
          <div class="input-group mb-2">
            <input type="text" class="form-control" id="sfUrl" readonly value="<?= e($fillUrl) ?>">
          </div>
          <p class="small text-muted mb-0">Employee opens link → <b>Employee Code auto-generated (read-only)</b> → fills form → new employee added in HRMS (salary not included). Same mobile updates existing record.</p>
        </div>
      </div>

      <div class="qr-poster" id="sfPoster">
        <div class="qr-poster-head">
          <img src="<?= asset($app['brand']['logo']) ?>" alt="">
          <div><b><?= e($company) ?></b><small>Employee self-fill · Limited time</small></div>
        </div>
        <h2>SCAN TO FILL DETAILS</h2>
        <p>Personal · Address · Bank · KYC · Emergency<br><small>Valid until <?= e(date('d M Y', strtotime($active['expires_at']))) ?></small></p>
        <div class="qr-box"><div id="sfQr"></div></div>
        <div class="qr-url"><?= e($fillUrl) ?></div>
        <ul class="qr-steps">
          <li><span>1</span>Scan / open link</li>
          <li><span>2</span>Auto employee code</li>
          <li><span>3</span>Fill your details</li>
          <li><span>4</span>Submit → saved in HRMS</li>
        </ul>
      </div>
    <?php else: ?>
      <div class="ep-card cp">
        <div class="cp-body text-center py-5">
          <i class="fa-solid fa-qrcode fa-3x mb-3" style="color:var(--brand)"></i>
          <h3>No active link</h3>
          <p class="text-muted mb-0">Generate a link on the left, then share QR or WhatsApp with your team.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
  .qr-poster { max-width: 520px; margin: 0 auto; background: #fff; border: 2px solid var(--ink, #211F23); border-radius: 18px; padding: 26px 28px; text-align: center; }
  .qr-poster-head { display: flex; align-items: center; gap: 14px; justify-content: center; padding-bottom: 14px; border-bottom: 3px solid var(--brand, #F5A01B); text-align: left; }
  .qr-poster-head img { height: 56px; }
  .qr-poster-head b { display: block; font-size: 18px; font-weight: 800; }
  .qr-poster-head small { display: block; color: #6b7280; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; font-size: 11px; }
  .qr-poster h2 { margin: 18px 0 4px; font-weight: 800; letter-spacing: .04em; font-size: 24px; }
  .qr-poster p { color: #4b5563; margin-bottom: 14px; }
  .qr-box { display: inline-block; padding: 14px; border: 1px solid #e5e7eb; border-radius: 16px; }
  .qr-box img, .qr-box canvas { display: block; width: 260px !important; height: 260px !important; }
  .qr-url { margin-top: 12px; font-weight: 700; font-size: 12.5px; word-break: break-all; }
  .qr-steps { list-style: none; padding: 0; margin: 16px 0 0; display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; text-align: left; }
  .qr-steps li { background: #fff7e8; border-radius: 10px; padding: 8px 10px; font-size: 12.5px; font-weight: 700; }
  .qr-steps span { display: inline-grid; place-items: center; width: 22px; height: 22px; border-radius: 50%; background: var(--brand, #F5A01B); color: #fff; margin-right: 6px; font-size: 11px; }
  @media print {
    @page { size: A4; margin: 12mm; }
    .sidebar, .topbar, .app-footer, .page-head, .ep-card, .alert { display: none !important; }
    .col-lg-5 { display: none !important; }
    .col-lg-7 { width: 100% !important; max-width: 100% !important; }
    .qr-poster { border-width: 2px; max-width: 150mm; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
</style>

<?php if ($fillUrl): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const url = <?= json_encode($fillUrl) ?>;
  new QRCode(document.getElementById('sfQr'), { text: url, width: 520, height: 520, colorDark: '#211F23', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.H });
  document.getElementById('sfCopy').addEventListener('click', function () {
    navigator.clipboard.writeText(url).then(() => window.oceanToast ? oceanToast('success', 'Link copied') : alert('Copied'));
  });
  document.getElementById('sfDownload').addEventListener('click', function () {
    const canvas = document.querySelector('#sfQr canvas');
    const a = document.createElement('a');
    a.href = canvas.toDataURL('image/png');
    a.download = 'employee-self-fill-qr.png';
    a.click();
  });
});
</script>
<?php endif; ?>
