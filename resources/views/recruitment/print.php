<?php
use App\Models\Candidate;

$c = $candidate;
$lh = $letterhead;
$v = fn($x) => ($x === null || $x === '') ? '—' : e((string) $x);
$inr = fn($x) => $x !== null && $x !== '' ? '₹ ' . number_format((float) $x) . ' / yr' : '—';
$month = fn($m) => $m ? date('M Y', strtotime($m . '-01')) : '—';
$types = ['full_time' => 'Full Time', 'intern' => 'Internship', 'contract' => 'Contract', 'part_time' => 'Part Time'];
$age = $c['date_of_birth'] ? (new DateTime($c['date_of_birth']))->diff(new DateTime())->y . ' yrs' : null;

$photo = null;
if ($c['photo_path'] && is_file(BASE_PATH . '/' . $c['photo_path'])) {
    $file = BASE_PATH . '/' . $c['photo_path'];
    $photo = 'data:' . mime_content_type($file) . ';base64,' . base64_encode(file_get_contents($file));
}
$rounds = $c['interviews'] ?: array_map(fn($r) => ['round' => $r], ['technical', 'machine_test', 'hr']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($c['application_no'] . ' · ' . $c['full_name']) ?></title>
  <?php \App\Core\View::partial('partials/letterhead-css', ['letterhead' => $lh]); ?>
  <style>
    .doc-head { display: flex; gap: 12px; align-items: stretch; margin: 0 0 4mm; }
    .doc-title { flex: 1; }
    .doc-title h1 { margin: 0 calc(-30mm - 12px) 5mm 0; text-align: center; font-size: 20px; font-weight: 900; color: #211F23; }
    .doc-title h1 span { display: inline-block; padding-bottom: 4px; border-bottom: 3px solid #F5A01B; }
    .doc-title h1 { letter-spacing: .06em; text-transform: uppercase; }
    .doc-title .meta { margin-top: 4px; color: #4b5563; }
    .doc-title .meta b { color: var(--ink); }
    .doc-title .pos { margin-top: 8px; display: inline-block; background: var(--soft); border: 1px solid #f6d398; color: #8a5200; font-weight: 800; padding: 5px 10px; border-radius: 6px; font-size: 12px; }
    .photo { width: 30mm; height: 36mm; border: 1px solid var(--line); display: grid; place-items: center; color: #9ca3af; font-size: 9px; text-align: center; overflow: hidden; background: #fafafa; }
    .photo img { width: 100%; height: 100%; object-fit: cover; }

    .sec { margin-bottom: 3.5mm; page-break-inside: avoid; }
    .sec h2 { margin: 0; padding: 5px 8px; background: var(--ink); color: #fff; font-size: 10.5px; letter-spacing: .08em; text-transform: uppercase; border-left: 5px solid var(--brand); }
    table.grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
    table.grid td, table.grid th { border: 1px solid var(--line); padding: 4px 7px; vertical-align: top; word-wrap: break-word; }
    table.grid th { background: #f6f6f8; text-align: left; font-size: 9.5px; text-transform: uppercase; letter-spacing: .04em; color: #4b5563; }
    .kv td small { display: block; font-size: 8.5px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; font-weight: 700; }
    .kv td span { font-weight: 700; }
    .text-block { border: 1px solid var(--line); border-top: 0; padding: 6px 8px; white-space: pre-line; min-height: 10mm; }
    .decl { border: 1px solid var(--line); border-top: 0; padding: 7px 8px; }
    .sign-row { display: flex; justify-content: space-between; gap: 10mm; margin-top: 10mm; }
    .sign-row div { flex: 1; border-top: 1px solid var(--ink); padding-top: 3px; text-align: center; font-weight: 700; font-size: 10px; }
    .office { page-break-before: auto; }
    .office .tick { display: inline-block; width: 11px; height: 11px; border: 1px solid var(--ink); margin: 0 4px -2px 10px; position: relative; }
    .office .tick.on { background: var(--ink); }
    .office .tick.on::after { content: "✓"; position: absolute; inset: -3px 0 0 1px; color: #fff; font-size: 10px; font-weight: 800; }
    .muted { color: #6b7280; }
  </style>
</head>
<body>

<div class="toolbar">
  <span><b><?= e($c['application_no']) ?></b> · Job Application Form (A4)</span>
  <div class="tb-actions">
    <span class="tb-pages" id="pageCount">Preparing A4…</span>
    <?php if ($hrCopy): ?><a class="tb-btn ghost" href="<?= url('recruitment/' . $c['id']) ?>">← Back</a><?php endif; ?>
    <button type="button" onclick="window.print()">Print / Save PDF</button>
  </div>
</div>

<template id="lhTpl"><?php \App\Core\View::partial('partials/letterhead-layer', ['letterhead' => $lh, 'footerNote' => 'Confidential · Candidate application record']); ?></template>
<div id="pages" data-mode="<?= e($lh['mode']) ?>" data-top="<?= (int) $lh['top_mm'] ?>" data-bottom="<?= (int) $lh['bottom_mm'] ?>" data-side="<?= (int) $lh['side_mm'] ?>"></div>

<div class="flow" id="flow">

      <div class="doc-head">
        <div class="doc-title">
          <h1><span>Job Application Form</span></h1>
          <div class="meta">Application No: <b><?= e($c['application_no']) ?></b> &nbsp;|&nbsp; Date: <b><?= format_date($c['created_at'], 'd M Y, h:i A') ?></b>
            <?php if ($hrCopy): ?>&nbsp;|&nbsp; Status: <b><?= e(Candidate::statusLabel($c['status'])) ?></b><?php endif; ?>
          </div>
          <div class="pos">Applied for: <?= e($c['position']) ?></div>
        </div>
        <div class="photo"><?php if ($photo): ?><img src="<?= $photo ?>" alt=""><?php else: ?>Affix<br>passport<br>photo<?php endif; ?></div>
      </div>

      <div class="sec">
        <h2>Position Details</h2>
        <table class="grid kv"><tr>
          <td colspan="2"><small>Position</small><span><?= $v($c['position']) ?></span></td>
          <td colspan="2"><small>Employment Type</small><span><?= $v($types[$c['employment_type']] ?? $c['employment_type']) ?></span></td>
        </tr><tr>
          <td colspan="2"><small>Source</small><span><?= $v($c['source']) ?></span></td>
          <td colspan="2"><small>Referred By</small><span><?= $v($c['referred_by']) ?></span></td>
        </tr></table>
      </div>

      <div class="sec">
        <h2>Personal Details</h2>
        <table class="grid kv"><tr>
          <td colspan="2"><small>Full Name</small><span><?= e($c['full_name']) ?></span></td>
          <td><small>Mobile</small><span><?= $v($c['mobile']) ?></span></td>
          <td><small>Parents Contact No.</small><span><?= $v($c['parent_mobile'] ?? $c['alt_mobile']) ?></span></td>
        </tr><tr>
          <td colspan="2"><small>Email</small><span><?= $v($c['email']) ?></span></td>
          <td><small>Date of Birth / Age</small><span><?= $c['date_of_birth'] ? format_date($c['date_of_birth'], 'd-m-Y') . ' / ' . $age : '—' ?></span></td>
          <td><small>Gender / Marital</small><span><?= $v(trim(ucfirst((string) $c['gender']) . ($c['marital_status'] ? ' / ' . $c['marital_status'] : ''), ' /')) ?></span></td>
        </tr><tr>
          <td colspan="2"><small>Address</small><span><?= $v($c['address']) ?></span></td>
          <td><small>City / State</small><span><?= $v(trim($c['city'] . ', ' . $c['state'], ', ')) ?></span></td>
          <td><small>Pincode</small><span><?= $v($c['pincode']) ?></span></td>
        </tr><tr>
          <td colspan="2"><small>Aadhaar No.</small><span><?= $v($c['aadhaar_number']) ?></span></td>
          <td colspan="2"><small>PAN No.</small><span><?= $v($c['pan_number']) ?></span></td>
        </tr></table>
      </div>

      <div class="sec">
        <h2>Educational Details</h2>
        <table class="grid">
          <tr><th style="width:22%">Degree / Course</th><th>College / University</th><th style="width:24%">Specialization</th><th style="width:10%">Year</th><th style="width:12%">% / CGPA</th></tr>
          <?php foreach ($c['education'] ?: [[]] as $ed): ?>
            <tr><td><?= $v($ed['degree'] ?? null) ?></td><td><?= $v($ed['institute'] ?? null) ?></td><td><?= $v($ed['specialization'] ?? null) ?></td><td><?= $v($ed['passing_year'] ?? null) ?></td><td><?= $v($ed['percentage_grade'] ?? null) ?></td></tr>
          <?php endforeach; ?>
        </table>
      </div>

      <div class="sec">
        <h2>Experience Details · <?= e(Candidate::experienceLabel((int) $c['total_experience_months'], (bool) $c['is_fresher'])) ?></h2>
        <?php if ($c['is_fresher'] || !$c['experience']): ?>
          <div class="text-block muted">Fresher — no prior work experience.</div>
        <?php else: ?>
          <table class="grid">
            <tr><th style="width:22%">Company</th><th style="width:18%">Designation</th><th style="width:10%">From</th><th style="width:10%">To</th><th style="width:12%">Duration</th><th>Technologies / Responsibilities</th></tr>
            <?php foreach ($c['experience'] as $ex): ?>
              <tr>
                <td><?= $v($ex['company']) ?></td><td><?= $v($ex['designation']) ?></td>
                <td><?= $month($ex['from_month']) ?></td><td><?= $ex['is_current'] ? 'Present' : $month($ex['to_month']) ?></td>
                <td><?= e(Candidate::experienceLabel(Candidate::monthsBetween($ex['from_month'], $ex['is_current'] ? null : $ex['to_month']))) ?></td>
                <td><?= $ex['tech_stack'] ? '<b>' . e($ex['tech_stack']) . '</b><br>' : '' ?><?= nl2br(e((string) $ex['responsibilities'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </table>
        <?php endif; ?>
      </div>

      <div class="sec">
        <h2>Compensation &amp; Availability</h2>
        <table class="grid kv"><tr>
          <td><small>Current CTC</small><span><?= $c['is_fresher'] ? 'Fresher' : $inr($c['current_ctc']) ?></span></td>
          <td><small>Expected CTC</small><span><?= $inr($c['expected_ctc']) ?></span></td>
          <td><small>Notice Period</small><span><?= $v($c['notice_period']) ?></span></td>
          <td><small>Current Location / Relocate</small><span><?= $v($c['current_location']) ?> / <?= $c['ready_to_relocate'] ? 'Yes' : 'No' ?></span></td>
        </tr></table>
      </div>

      <div class="sec">
        <h2>Technical Skills &amp; Profile</h2>
        <table class="grid kv"><tr>
          <td colspan="2"><small>Primary Skills</small><span><?= $v($c['primary_skills']) ?></span></td>
          <td colspan="2"><small>Other Skills / Tools</small><span><?= $v($c['secondary_skills']) ?></span></td>
        </tr><tr>
          <td colspan="4"><small>Certifications</small><span><?= $v($c['certifications']) ?></span></td>
        </tr><tr>
          <td colspan="2"><small>GitHub</small><span><?= $v($c['github_url']) ?></span></td>
          <td colspan="2"><small>LinkedIn / Portfolio</small><span><?= $v(trim($c['linkedin_url'] . ' ' . $c['portfolio_url'])) ?></span></td>
        </tr></table>
        <?php if ($c['best_project']): ?><div class="text-block"><b>Best Project:</b> <?= e($c['best_project']) ?></div><?php endif; ?>
        <?php if ($c['why_join']): ?><div class="text-block"><b>Why join us:</b> <?= e($c['why_join']) ?></div><?php endif; ?>
      </div>

      <div class="sec">
        <h2>Declaration</h2>
        <div class="decl">
          I hereby declare that the information provided above is true and complete to the best of my knowledge. I understand that any false information may lead to rejection of my application or termination of employment.
          <div class="sign-row"><div>Date &amp; Place</div><div>Candidate Signature</div></div>
        </div>
      </div>

      <div class="sec office">
        <h2>For Office Use · Interview Assessment <span style="font-weight:600;letter-spacing:0;text-transform:none;opacity:.8">(scores out of 5)</span></h2>
        <table class="grid">
          <tr><th style="width:17%">Round</th><th style="width:15%">Date</th><th style="width:16%">Interviewer</th><th>Tech</th><th>Comm</th><th>Logic</th><th>Attitude</th><th style="width:10%">Result</th><th style="width:12%">Sign</th></tr>
          <?php foreach ($rounds as $r): ?>
            <tr style="height:9mm">
              <td><b><?= e(Candidate::ROUNDS[$r['round']] ?? $r['round']) ?></b></td>
              <td><?= !empty($r['interview_at']) ? format_date($r['interview_at'], 'd-m-Y') : '' ?></td>
              <td><?= e($r['interviewer'] ?? '') ?></td>
              <td><?= e((string) ($r['technical_score'] ?? '')) ?></td>
              <td><?= e((string) ($r['communication_score'] ?? '')) ?></td>
              <td><?= e((string) ($r['problem_solving_score'] ?? '')) ?></td>
              <td><?= e((string) ($r['attitude_score'] ?? '')) ?></td>
              <td><?= isset($r['result']) && $r['result'] !== 'pending' ? e(ucfirst($r['result'])) : '' ?></td>
              <td></td>
            </tr>
          <?php endforeach; ?>
        </table>
        <div class="decl">
          <b>Final Decision:</b>
          <span class="tick <?= $hrCopy && $c['status'] === 'selected' ? 'on' : '' ?>"></span>Selected
          <span class="tick <?= $hrCopy && $c['status'] === 'rejected' ? 'on' : '' ?>"></span>Not Selected
<?php $offered = $hrCopy && (float) ($c['offered_salary'] ?? 0) > 0; ?>
          &nbsp;&nbsp; <b>Final Salary:</b> <?= $offered ? '₹' . number_format((float) $c['offered_salary']) . ' / month' : '____________' ?>
          &nbsp; <b>Joining Date:</b> <?= $offered && $c['joining_date'] ? format_date($c['joining_date'], 'd-m-Y') : '____________' ?>
          <?php if ($hrCopy && $c['hr_remarks']): ?><div style="margin-top:5px"><b>HR Remarks:</b> <?= e($c['hr_remarks']) ?></div><?php endif; ?>
          <div class="sign-row"><div>Interviewer</div><div>HR Manager</div><div>Approved By</div></div>
        </div>
      </div>

</div>

<script src="<?= asset('assets/js/paged.js') ?>?v=<?= @filemtime(BASE_PATH . '/public/assets/js/paged.js') ?>"></script>
<?php if (isset($_GET['print'])): ?><script>document.addEventListener('paged:ready', () => setTimeout(() => window.print(), 300));</script><?php endif; ?>
</body>
</html>
