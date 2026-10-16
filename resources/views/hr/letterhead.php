<?php
use App\Services\Letterhead;

$mode = $settings['letterhead_mode'] ?? 'auto';
$img = fn($k) => !empty($settings[$k]) ? asset($settings[$k]) : '';
$slots = [
    'letterhead_header' => ['Header Image', 'Top strip of your letterhead · PNG/JPG · width ~2480px'],
    'letterhead_footer' => ['Footer Image', 'Bottom strip of your letterhead · PNG/JPG'],
    'letterhead_full'   => ['Full-page Letterhead', 'Complete A4 design (2480 × 3508 px at 300 DPI)'],
];
?>
<div class="page-head">
  <div>
    <h1>Letterhead Setup</h1>
    <p>Dynamic letterhead used when printing candidate applications &amp; HR documents</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('settings') ?>" class="btn btn-sm btn-outline-ink"><i class="fa-solid fa-building me-1"></i>Company Settings</a>
  </div>
</div>

<form method="post" action="<?= url('settings/letterhead') ?>" enctype="multipart/form-data" class="js-loading" id="lhForm">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-xl-7">
      <div class="panel mb-3">
        <div class="panel-header"><h3>Letterhead Type</h3></div>
        <div class="panel-body">
          <div class="row g-2">
            <?php foreach (Letterhead::MODES as $key => $label): ?>
              <div class="col-md-4">
                <label class="lh-mode <?= $mode === $key ? 'active' : '' ?>">
                  <input type="radio" name="letterhead_mode" value="<?= $key ?>" <?= $mode === $key ? 'checked' : '' ?>>
                  <i class="fa-solid <?= ['auto' => 'fa-wand-magic-sparkles', 'header_footer' => 'fa-table-rows', 'full' => 'fa-file-image'][$key] ?>"></i>
                  <b><?= e($label) ?></b>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
          <p class="small text-muted mt-2 mb-0"><i class="fa-solid fa-circle-info me-1"></i>
            <b>Auto</b> uses the logo and company details from Company Settings. Upload your own letterhead as images (export the PDF/Word letterhead as PNG).</p>
        </div>
      </div>

      <div class="panel mb-3">
        <div class="panel-header"><h3>Letterhead Images</h3></div>
        <div class="panel-body">
          <?php foreach ($slots as $key => [$label, $hint]): ?>
            <div class="lh-slot" data-slot="<?= $key ?>">
              <div class="lh-thumb"><?php if ($img($key)): ?><img src="<?= $img($key) ?>" alt=""><?php else: ?><i class="fa-regular fa-image"></i><?php endif; ?></div>
              <div class="flex-grow-1">
                <label class="form-label mb-1"><?= e($label) ?></label>
                <input type="file" class="form-control form-control-sm js-lh-file" name="<?= $key ?>" accept="image/png,image/jpeg,image/webp">
                <div class="d-flex justify-content-between mt-1">
                  <small class="text-muted"><?= e($hint) ?></small>
                  <?php if ($img($key)): ?>
                    <label class="small text-danger"><input type="checkbox" name="remove_<?= $key ?>" value="1" class="form-check-input me-1">Remove</label>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="panel">
        <div class="panel-header"><h3>Content Margins (mm)</h3></div>
        <div class="panel-body">
          <div class="row g-3">
            <div class="col-4"><label class="form-label">Top space</label><input type="number" class="form-control js-lh-num" name="letterhead_top_mm" min="0" max="120" value="<?= (int) $lh['top_mm'] ?>"></div>
            <div class="col-4"><label class="form-label">Bottom space</label><input type="number" class="form-control js-lh-num" name="letterhead_bottom_mm" min="0" max="80" value="<?= (int) $lh['bottom_mm'] ?>"></div>
            <div class="col-4"><label class="form-label">Side margin</label><input type="number" class="form-control js-lh-num" name="letterhead_side_mm" min="5" max="30" value="<?= (int) $lh['side_mm'] ?>"></div>
          </div>
          <p class="small text-muted mt-2 mb-0">Set top/bottom space equal to your header and footer height so text never overlaps the design. These repeat on every printed page.</p>
        </div>
      </div>

      <div class="mt-3"><button class="btn btn-brand"><i class="fa-solid fa-check me-1"></i>Save Letterhead</button></div>
    </div>

    <div class="col-xl-5">
      <div class="panel position-sticky" style="top:84px">
        <div class="panel-header"><h3>Live Preview (A4)</h3></div>
        <div class="panel-body">
          <div class="lh-preview" id="lhPreview"
               data-header="<?= $img('letterhead_header') ?>" data-footer="<?= $img('letterhead_footer') ?>" data-full="<?= $img('letterhead_full') ?>">
            <img class="pv-full" alt="">
            <img class="pv-header" alt="">
            <img class="pv-footer" alt="">
            <div class="pv-auto-head">
              <img src="<?= e($lh['logo']) ?>" alt="">
              <div><b><?= e($lh['company']) ?></b><small><?= e($lh['address'] ?: $lh['tagline']) ?></small></div>
            </div>
            <div class="pv-auto-foot"></div>
            <div class="pv-body">
              <div class="pv-title">JOB APPLICATION FORM</div>
              <?php for ($i = 0; $i < 7; $i++): ?><div class="pv-bar"></div><div class="pv-line"></div><div class="pv-line short"></div><?php endfor; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<style>
  .lh-mode { display:flex; flex-direction:column; gap:6px; height:100%; padding:14px; border:1.5px solid #e5e7eb; border-radius:12px; cursor:pointer; transition:.15s; }
  .lh-mode input { display:none; }
  .lh-mode i { font-size:20px; color:var(--brand, #F5A01B); }
  .lh-mode b { font-size:13px; }
  .lh-mode.active { border-color:var(--brand, #F5A01B); background:#fff7e8; }
  .lh-slot { display:flex; gap:14px; align-items:center; padding:12px 0; border-bottom:1px dashed #eee; }
  .lh-slot:last-child { border-bottom:0; }
  .lh-thumb { width:110px; height:70px; flex-shrink:0; border:1px solid #eee; border-radius:10px; display:grid; place-items:center; background:#fafafa; overflow:hidden; color:#c4c4c4; font-size:22px; }
  .lh-thumb img { max-width:100%; max-height:100%; object-fit:contain; }
  .lh-preview { --s: 1.6px; position:relative; width:calc(210 * var(--s)); height:calc(297 * var(--s)); margin:0 auto; background:#fff; box-shadow:0 6px 24px rgba(0,0,0,.14); overflow:hidden; }
  .lh-preview > img { position:absolute; left:0; width:100%; display:none; }
  .lh-preview .pv-full { top:0; height:100%; object-fit:fill; }
  .lh-preview .pv-header { top:0; } .lh-preview .pv-footer { bottom:0; }
  .pv-auto-head { position:absolute; top:0; left:0; right:0; display:none; align-items:center; gap:8px; padding:0 calc(var(--side,12) * var(--s)); border-bottom:3px solid #F5A01B; }
  .pv-auto-head img { height:32px; } .pv-auto-head b { display:block; font-size:12px; } .pv-auto-head small { display:block; font-size:8px; color:#6b7280; }
  .pv-auto-foot { position:absolute; bottom:0; left:0; right:0; display:none; border-top:1px solid #ddd; background:linear-gradient(90deg,#211F23 0 30%,#F5A01B 30%) bottom/100% 4px no-repeat; }
  .pv-body { position:absolute; left:0; right:0; overflow:hidden; outline:1px dashed rgba(245,160,27,.7); outline-offset:-1px; }
  .pv-title { font-weight:800; font-size:10px; letter-spacing:.08em; margin:6px 0; }
  .pv-bar { height:7px; background:#211F23; border-left:4px solid #F5A01B; margin-top:7px; }
  .pv-line { height:5px; background:#eee; margin-top:4px; } .pv-line.short { width:60%; }
  @media (max-width: 400px) { .lh-preview { --s: 1.3px; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('lhForm');
  const pv = document.getElementById('lhPreview');
  const src = { header: pv.dataset.header, footer: pv.dataset.footer, full: pv.dataset.full };
  const q = s => pv.querySelector(s);

  function render() {
    const mode = form.querySelector('[name="letterhead_mode"]:checked').value;
    const s = parseFloat(getComputedStyle(pv).getPropertyValue('--s'));
    const top = +form.letterhead_top_mm.value || 0, bottom = +form.letterhead_bottom_mm.value || 0, side = +form.letterhead_side_mm.value || 0;
    form.querySelectorAll('.lh-mode').forEach(l => l.classList.toggle('active', l.querySelector('input').checked));

    const show = (el, on, url) => { el.style.display = on ? 'block' : 'none'; if (url) el.src = url; };
    show(q('.pv-full'), mode === 'full' && src.full, src.full);
    show(q('.pv-header'), mode === 'header_footer' && src.header, src.header);
    show(q('.pv-footer'), mode === 'header_footer' && src.footer, src.footer);
    const auto = mode === 'auto' || (mode === 'full' && !src.full) || (mode === 'header_footer' && !src.header && !src.footer);
    q('.pv-auto-head').style.display = auto ? 'flex' : 'none';
    q('.pv-auto-head').style.height = (top * s) + 'px';
    q('.pv-auto-foot').style.display = auto ? 'block' : 'none';
    q('.pv-auto-foot').style.height = (bottom * s) + 'px';
    Object.assign(q('.pv-body').style, { top: (top * s) + 'px', bottom: (bottom * s) + 'px', padding: '0 ' + (side * s) + 'px' });
  }

  form.querySelectorAll('[name="letterhead_mode"], .js-lh-num').forEach(el => el.addEventListener('input', render));
  form.querySelectorAll('.js-lh-file').forEach(input => input.addEventListener('change', function () {
    const f = this.files[0];
    if (!f) return;
    const url = URL.createObjectURL(f);
    const key = this.name.replace('letterhead_', '');
    src[key] = url;
    this.closest('.lh-slot').querySelector('.lh-thumb').innerHTML = '<img src="' + url + '" alt="">';
    const radio = form.querySelector('[name="letterhead_mode"][value="' + (key === 'full' ? 'full' : 'header_footer') + '"]');
    radio.checked = true;
    render();
  }));
  render();
});
</script>
