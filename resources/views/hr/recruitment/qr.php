<div class="page-head no-print">
  <div>
    <h1>Fixed Career QR Code</h1>
    <p>Print and place at reception. Candidates scan → fill the application form on mobile.</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('recruitment') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-arrow-left me-1"></i>Applications</a>
    <button type="button" class="btn btn-sm btn-outline-ink" id="qrCopy"><i class="fa-regular fa-copy me-1"></i>Copy Link</button>
    <button type="button" class="btn btn-sm btn-outline-ink" id="qrDownload"><i class="fa-solid fa-download me-1"></i>Download PNG</button>
    <button type="button" class="btn btn-sm btn-brand" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print / PDF</button>
  </div>
</div>

<?php if ($isLocal): ?>
  <div class="alert alert-warning no-print small"><i class="fa-solid fa-triangle-exclamation me-1"></i>
    This QR points to <b>localhost</b>, which phones cannot open. Open this page using the PC's network IP (e.g. <code>http://192.168.x.x/Ocean_HRMS/public/recruitment/qr</code>) or your live domain, then print.
  </div>
<?php endif; ?>
<div class="qr-poster" id="qrPoster">
  <div class="qr-poster-head">
    <img src="<?= asset($app['brand']['logo']) ?>" alt="">
    <div><b><?= e($company) ?></b><small>Careers · Scan to apply</small></div>
  </div>
  <h2>SCAN TO APPLY</h2>
  <p>Open camera / QR scanner → fill the career application form</p>
  <div class="qr-box"><div id="qrCode"></div></div>
  <div class="qr-url"><?= e($applyUrl) ?></div>
  <ul class="qr-steps">
    <li><span>1</span>Scan QR</li>
    <li><span>2</span>Select position</li>
    <li><span>3</span>Fill details &amp; upload resume</li>
    <li><span>4</span>Show application no. at HR desk</li>
  </ul>
</div>

<style>
  .qr-poster { max-width: 520px; margin: 0 auto; background: #fff; border: 2px solid var(--ink, #211F23); border-radius: 18px; padding: 26px 28px; text-align: center; }
  .qr-poster-head { display: flex; align-items: center; gap: 14px; justify-content: center; padding-bottom: 14px; border-bottom: 3px solid var(--brand, #F5A01B); text-align: left; }
  .qr-poster-head img { height: 56px; }
  .qr-poster-head b { display: block; font-size: 18px; font-weight: 800; }
  .qr-poster-head small { display: block; color: #6b7280; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; font-size: 11px; }
  .qr-poster h2 { margin: 18px 0 4px; font-weight: 800; letter-spacing: .06em; font-size: 28px; }
  .qr-poster p { color: #4b5563; margin-bottom: 14px; }
  .qr-box { display: inline-block; padding: 14px; border: 1px solid #e5e7eb; border-radius: 16px; }
  .qr-box img, .qr-box canvas { display: block; width: 260px !important; height: 260px !important; }
  .qr-url { margin-top: 12px; font-weight: 700; font-size: 13px; word-break: break-all; }
  .qr-steps { list-style: none; padding: 0; margin: 16px 0 0; display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; text-align: left; }
  .qr-steps li { background: #fff7e8; border-radius: 10px; padding: 8px 10px; font-size: 12.5px; font-weight: 700; }
  .qr-steps span { display: inline-grid; place-items: center; width: 22px; height: 22px; border-radius: 50%; background: var(--brand, #F5A01B); color: #fff; margin-right: 6px; font-size: 11px; }
  @media print {
    @page { size: A4; margin: 12mm; }
    .sidebar, .topbar, .app-footer, .no-print { display: none !important; }
    .main-wrap { margin: 0 !important; padding: 0 !important; }
    .content { padding: 0 !important; }
    .qr-poster { border-width: 2px; margin-top: 10mm; max-width: 150mm; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const url = <?= json_encode($applyUrl) ?>;
  new QRCode(document.getElementById('qrCode'), { text: url, width: 520, height: 520, colorDark: '#211F23', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.H });

  document.getElementById('qrCopy').addEventListener('click', function () {
    navigator.clipboard.writeText(url).then(() => window.oceanToast ? oceanToast('success', 'Apply link copied') : alert('Copied'));
  });
  document.getElementById('qrDownload').addEventListener('click', function () {
    const canvas = document.querySelector('#qrCode canvas');
    const a = document.createElement('a');
    a.href = canvas.toDataURL('image/png');
    a.download = 'career-apply-qr.png';
    a.click();
  });
});
</script>
