<?php
$fmtDate = static fn(?string $d) => $d ? date('d M Y', strtotime($d)) : '—';
$hideContact = !empty($isEmp);
$renderNode = function (array $node, bool $leader = false, string $badge = '') use (&$renderNode, $fmtDate, $hideContact): void {
    $photo = !empty($node['profile_photo']) ? asset($node['profile_photo']) : null;
    $desig = $badge !== '' ? $badge : ($node['designation_name'] ?: 'Team Member');
    $dept = $node['department_name'] ?? '';
    $isCustomLeader = !empty($node['is_leader']);
    $childCount = count($node['children'] ?? []);
    $canProfile = !$isCustomLeader && !$hideContact && (can('employee.view') || can('*')) && !empty($node['id']);
    $profileUrl = $canProfile ? url('employees/' . $node['id']) : '';

    $popup = [
        'id' => (int) ($node['id'] ?? 0),
        'name' => $node['full_name'] ?? '',
        'initials' => $node['initials'] ?? '?',
        'photo' => $photo ?: '',
        'code' => $node['employee_code'] ?? '',
        'role' => $desig,
        'department' => $dept,
        'email' => $hideContact ? '' : ($node['official_email'] ?? ''),
        'personal_email' => $hideContact ? '' : ($node['personal_email'] ?? ''),
        'mobile' => $hideContact ? '' : ($node['mobile'] ?? ''),
        'type' => !empty($node['employment_type']) ? ucwords(str_replace('_', ' ', $node['employment_type'])) : '',
        'joining' => !empty($node['joining_date']) ? $fmtDate($node['joining_date']) : '',
        'location' => $node['work_location'] ?? '',
        'status' => !empty($node['status']) ? ucfirst((string) $node['status']) : '',
        'reports_to' => $node['reports_to'] ?? '',
        'reports' => $childCount,
        'profile' => $profileUrl,
        'is_leader' => $isCustomLeader ? 1 : 0,
        'hide_contact' => $hideContact ? 1 : 0,
    ];
    ?>
    <li>
      <div class="oc-card <?= $leader || $isCustomLeader ? 'oc-leader' : '' ?> js-oc-card"
           role="button" tabindex="0"
           data-oc="<?= htmlspecialchars(json_encode($popup, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
           title="View <?= e($node['full_name']) ?>">
        <div class="oc-photo <?= $photo ? 'has-photo' : '' ?>">
          <?php if ($photo): ?>
            <img src="<?= e($photo) ?>" alt="<?= e($node['full_name']) ?>">
          <?php else: ?>
            <span><?= e($node['initials']) ?></span>
          <?php endif; ?>
        </div>
        <div class="oc-meta">
          <b><?= e($node['full_name']) ?></b>
          <small class="oc-role"><?= e($desig) ?></small>
          <?php if ($dept && $badge === ''): ?><small class="oc-dept"><?= e($dept) ?></small><?php endif; ?>
          <?php if (!empty($node['employee_code'])): ?>
            <small class="oc-code"><?= e($node['employee_code']) ?></small>
          <?php endif; ?>
          <?php if ($childCount > 0): ?>
            <small class="oc-reports"><?= $childCount ?> direct report<?= $childCount === 1 ? '' : 's' ?></small>
          <?php endif; ?>
        </div>
      </div>
      <?php if (!empty($node['children'])): ?>
        <ul>
          <?php foreach ($node['children'] as $child) {
              $renderNode($child);
          } ?>
        </ul>
      <?php endif; ?>
    </li>
    <?php
};
$hasLeaders = $founder || $coFounder;
$hasTree = $hasLeaders || !empty($teamRoots);
?>

<div class="page-head">
  <div>
    <h1>Organization Chart</h1>
    <p><?= !empty($isEmp)
        ? 'Click any person for photo, post &amp; details'
        : 'Click any person for photo, office email, post &amp; details' ?> · <?= (int) ($empCount ?? 0) ?> active</p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <?php if (empty($isEmp)): ?>
      <a href="<?= url('org-chart/print') ?>" class="btn btn-sm btn-outline-ink" target="_blank">
        <i class="fa-solid fa-print me-1"></i>Print on Letterhead
      </a>
    <?php endif; ?>
    <?php if ($isAdmin): ?>
      <button type="button" class="btn btn-sm btn-brand" data-bs-toggle="modal" data-bs-target="#ocLeadersModal">
        <i class="fa-solid fa-user-tie me-1"></i>Add Founder / Co-Founder
      </button>
    <?php endif; ?>
  </div>
</div>

<?php if (!$hasLeaders): ?>
  <div class="alert alert-warning">
    <i class="fa-solid fa-circle-info me-1"></i>
    Founder / Co-Founder not added yet — chart shows employees only.
    <?php if ($isAdmin): ?> Click <b>Add Founder / Co-Founder</b> to set Direct Owners.
    <?php else: ?> Ask HR to add leaders.<?php endif; ?>
  </div>
<?php endif; ?>

<div class="oc-wrap">
  <div class="oc-toolbar no-print">
    <div class="oc-zoom">
      <button type="button" class="btn btn-sm btn-outline-ink" id="ocZoomOut" title="Zoom out"><i class="fa-solid fa-minus"></i></button>
      <span class="oc-zoom-label" id="ocZoomLabel">100%</span>
      <button type="button" class="btn btn-sm btn-outline-ink" id="ocZoomIn" title="Zoom in"><i class="fa-solid fa-plus"></i></button>
      <button type="button" class="btn btn-sm btn-outline-ink" id="ocZoomReset" title="Reset">Reset</button>
    </div>
    <?php if (empty($isEmp)): ?>
      <a href="<?= url('org-chart/print') ?>" class="btn btn-sm btn-outline-ink" target="_blank"><i class="fa-solid fa-print me-1"></i>Letterhead Print</a>
    <?php endif; ?>
  </div>

  <div class="oc-stage" id="ocStage">
    <div class="oc-scale" id="ocScale">
      <?php if ($hasLeaders): ?>
        <div class="oc-roots">
          <ul class="oc-tree oc-tree-roots">
            <?php if ($founder) {
                $renderNode($founder, true, 'Founder / Direct Owner');
            } ?>
            <?php if ($coFounder) {
                $renderNode($coFounder, true, 'Co-Founder / Direct Owner');
            } ?>
          </ul>
        </div>
      <?php elseif ($teamRoots): ?>
        <div class="oc-section">
          <h3><i class="fa-solid fa-sitemap me-1"></i> Team structure</h3>
          <ul class="oc-tree">
            <?php foreach ($teamRoots as $n) {
                $renderNode($n);
            } ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if (!$hasTree): ?>
        <div class="oc-empty">
          <i class="fa-solid fa-sitemap"></i>
          <b>No chart data yet</b>
          <p>Add Founder / Co-Founder and set Reporting Person on employees.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Employee / Leader detail popup -->
<div class="modal fade" id="ocPersonModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content oc-person-modal">
      <button type="button" class="btn-close oc-person-close" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="oc-person-hero">
        <div class="oc-person-photo" id="ocPmPhoto"><span id="ocPmInitials">?</span></div>
        <div>
          <h4 id="ocPmName">—</h4>
          <div class="oc-person-role" id="ocPmRole">—</div>
          <div class="oc-person-code" id="ocPmCode"></div>
        </div>
      </div>
      <div class="oc-person-body">
        <div class="oc-person-grid">
          <div><span>Department</span><b id="ocPmDept">—</b></div>
          <div><span>Post / Designation</span><b id="ocPmPost">—</b></div>
          <?php if (empty($isEmp)): ?>
          <div class="full oc-contact-row" id="ocPmEmailRow"><span>Office Email</span><b id="ocPmEmail">—</b></div>
          <div class="full oc-contact-row" id="ocPmPersonalRow"><span>Personal Email</span><b id="ocPmPersonal">—</b></div>
          <div class="oc-contact-row" id="ocPmMobileRow"><span>Mobile</span><b id="ocPmMobile">—</b></div>
          <?php endif; ?>
          <div><span>Employment Type</span><b id="ocPmType">—</b></div>
          <div><span>Joining Date</span><b id="ocPmJoin">—</b></div>
          <div><span>Work Location</span><b id="ocPmLoc">—</b></div>
          <div><span>Status</span><b id="ocPmStatus">—</b></div>
          <div><span>Reports To</span><b id="ocPmReportsTo">—</b></div>
          <div class="full"><span>Direct Reports</span><b id="ocPmReports">—</b></div>
        </div>
      </div>
      <div class="oc-person-foot" id="ocPmFoot">
        <a href="#" class="btn btn-sm btn-brand" id="ocPmProfile" target="_blank"><i class="fa-solid fa-id-card me-1"></i>Open Full Profile</a>
        <button type="button" class="btn btn-sm btn-outline-ink" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php if ($isAdmin): ?>
<div class="modal fade" id="ocLeadersModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="<?= url('org-chart/leaders') ?>" class="modal-content js-loading" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title">Add Founder &amp; Co-Founder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted mb-3">These names appear as <b>Direct Owner</b> on the chart. On employee form, Reporting Person can be Founder, Co-Founder, or <b>Both Direct Owners</b> (shows under both).</p>

        <div class="mb-3">
          <label class="form-label">Founder name</label>
          <input type="text" name="founder_name" class="form-control" maxlength="120"
                 value="<?= e($founderName) ?>" placeholder="e.g. Ramesh Patel">
        </div>
        <div class="mb-3">
          <label class="form-label">Founder photo (optional)</label>
          <input type="file" name="founder_photo" class="form-control" accept="image/jpeg,image/png,image/webp">
          <?php if ($founderPhoto): ?>
            <label class="form-check mt-2 mb-0">
              <input class="form-check-input" type="checkbox" name="remove_founder_photo" value="1">
              <span class="form-check-label">Remove current photo</span>
            </label>
          <?php endif; ?>
        </div>

        <hr class="my-3">

        <div class="mb-3">
          <label class="form-label">Co-Founder name</label>
          <input type="text" name="co_founder_name" class="form-control" maxlength="120"
                 value="<?= e($coFounderName) ?>" placeholder="e.g. Suresh Patel">
        </div>
        <div class="mb-0">
          <label class="form-label">Co-Founder photo (optional)</label>
          <input type="file" name="co_founder_photo" class="form-control" accept="image/jpeg,image/png,image/webp">
          <?php if ($coFounderPhoto): ?>
            <label class="form-check mt-2 mb-0">
              <input class="form-check-input" type="checkbox" name="remove_co_founder_photo" value="1">
              <span class="form-check-label">Remove current photo</span>
            </label>
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-ink" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-brand"><i class="fa-solid fa-check me-1"></i>Save</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<link rel="stylesheet" href="<?= asset('assets/css/org-chart.css') ?>?v=<?= @filemtime(BASE_PATH . '/public/assets/css/org-chart.css') ?>">
<script>
document.addEventListener('DOMContentLoaded', function () {
  let z = 1;
  const scale = document.getElementById('ocScale');
  const label = document.getElementById('ocZoomLabel');
  const apply = () => {
    if (scale) scale.style.transform = 'scale(' + z + ')';
    if (label) label.textContent = Math.round(z * 100) + '%';
  };
  document.getElementById('ocZoomIn')?.addEventListener('click', () => { z = Math.min(1.5, +(z + 0.1).toFixed(2)); apply(); });
  document.getElementById('ocZoomOut')?.addEventListener('click', () => { z = Math.max(0.5, +(z - 0.1).toFixed(2)); apply(); });
  document.getElementById('ocZoomReset')?.addEventListener('click', () => { z = 1; apply(); });

  const modalEl = document.getElementById('ocPersonModal');
  const set = (id, val, isHtml) => {
    const el = document.getElementById(id);
    if (!el) return;
    const v = (val && String(val).trim()) ? String(val).trim() : '—';
    if (isHtml) el.innerHTML = v; else el.textContent = v;
  };
  const openPerson = (raw) => {
    let d;
    try { d = typeof raw === 'string' ? JSON.parse(raw) : raw; } catch (err) { console.warn('org-chart popup', err); return; }
    const photoBox = document.getElementById('ocPmPhoto');
    if (d.photo) {
      photoBox.innerHTML = '<img src="' + d.photo.replace(/"/g, '&quot;') + '" alt="">';
      photoBox.classList.add('has-photo');
    } else {
      photoBox.innerHTML = '<span id="ocPmInitials"></span>';
      photoBox.classList.remove('has-photo');
      document.getElementById('ocPmInitials').textContent = d.initials || '?';
    }
    set('ocPmName', d.name);
    set('ocPmRole', d.role);
    set('ocPmCode', d.code ? d.code : '');
    set('ocPmDept', d.department);
    set('ocPmPost', d.role);
    if (!d.hide_contact) {
      if (d.email) {
        const a = document.createElement('a');
        a.href = 'mailto:' + d.email;
        a.textContent = d.email;
        const emailEl = document.getElementById('ocPmEmail');
        if (emailEl) { emailEl.textContent = ''; emailEl.appendChild(a); }
      } else set('ocPmEmail', '—');
      set('ocPmPersonal', d.personal_email);
      set('ocPmMobile', d.mobile);
    }
    set('ocPmType', d.type);
    set('ocPmJoin', d.joining);
    set('ocPmLoc', d.location);
    set('ocPmStatus', d.status);
    set('ocPmReportsTo', d.reports_to);
    set('ocPmReports', d.reports > 0 ? (d.reports + ' person' + (d.reports === 1 ? '' : 's')) : 'None');
    const foot = document.getElementById('ocPmFoot');
    const link = document.getElementById('ocPmProfile');
    if (d.profile) {
      link.href = d.profile;
      link.classList.remove('d-none');
      foot.classList.remove('leader-only');
    } else {
      link.classList.add('d-none');
      foot.classList.add('leader-only');
    }
    if (window.bootstrap && bootstrap.Modal) {
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } else if (window.jQuery) {
      jQuery(modalEl).modal('show');
    } else {
      modalEl.classList.add('show');
      modalEl.style.display = 'block';
    }
  };

  document.addEventListener('click', function (e) {
    const card = e.target.closest('.js-oc-card');
    if (!card) return;
    e.preventDefault();
    openPerson(card.getAttribute('data-oc'));
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter' && e.key !== ' ') return;
    const card = e.target.closest('.js-oc-card');
    if (!card) return;
    e.preventDefault();
    openPerson(card.getAttribute('data-oc'));
  });
});
</script>
