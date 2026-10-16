<?php $pay = (float) ($monthlySalary ?? 0); $hasPay = $pay > 0; ?>
<p>Dear <?= e($firstName) ?>,</p>

<p>
  With reference to your application and the subsequent interviews held with us, we are pleased to offer you the position of
  <b><?= e($p['designation'] ?: '—') ?></b><?= $p['department'] ? ' in our <b>' . e($p['department']) . '</b> team' : '' ?> at
  <b><?= e($company) ?></b>. We were impressed by your skills and believe you will be a valuable addition to our team.
</p>

<?php if (!$hasPay): ?>
  <div class="lt-hl" style="border-left-color:#dc2626;background:#fef2f2">
    <p style="margin:0;font-weight:700;color:#b91c1c">Monthly salary is not set for this employee. Finalise salary first, then re-open this offer letter.</p>
  </div>
<?php endif; ?>

<div class="lt-hl">
  <table>
    <tr><td>Designation</td><td><?= e($p['designation'] ?: '—') ?></td></tr>
    <?php if ($p['department']): ?><tr><td>Department</td><td><?= e($p['department']) ?></td></tr><?php endif; ?>
    <tr><td>Date of Joining</td><td><?= $d($joiningDate) ?></td></tr>
    <tr><td>Monthly Salary</td><td><?= $hasPay ? $rupee($pay) . ' per month (fixed)' : '— (not set)' ?></td></tr>
    <tr><td>Probation Period</td><td><?= (int) $probation ?> month<?= (int) $probation === 1 ? '' : 's' ?></td></tr>
    <tr><td>Working Hours</td><td><?= e($shift ?? '09:30 AM to 06:30 PM') ?> · <?= e($letter['weekOff']) ?> off</td></tr>
    <tr><td>Place of Work</td><td><?= e($company) ?>, <?= e($letter['jurisdiction']) ?></td></tr>
  </table>
</div>

<h4><span>1.</span>Compensation</h4>
<p>
  <?php if ($hasPay): ?>
  Your fixed monthly salary will be <b><?= $rupee($pay) ?></b> (<?= e(amount_in_words($pay)) ?>) per month,
  i.e. <b><?= $rupee($pay * 12) ?></b> per annum. The salary will be paid monthly by bank transfer, subject to applicable statutory deductions, if any.
  Compensation details are strictly confidential.
  <?php else: ?>
  Your fixed monthly salary will be confirmed in writing before joining. The salary will be paid monthly by bank transfer, subject to applicable statutory deductions, if any.
  <?php endif; ?>
</p>

<h4><span>2.</span>Probation</h4>
<p>You will be on probation for <?= (int) $probation ?> months from your date of joining. On successful completion, your employment will be confirmed in writing. The company may extend the probation period based on performance.</p>

<h4><span>3.</span>Documents Required on Joining</h4>
<ul>
  <li>Copies of educational certificates and mark sheets</li>
  <li>Aadhaar card, PAN card and 2 passport-size photographs</li>
  <li>Relieving / experience letter and last 3 salary slips from previous employer (if applicable)</li>
  <li>Bank account details (cancelled cheque or passbook copy)</li>
</ul>

<h4><span>4.</span>Validity</h4>
<p>
  This offer is valid till <b><?= $d($validTill) ?></b>. Please sign and return a copy of this letter as a token of your acceptance.
  This offer is subject to verification of your documents and the information provided by you. A detailed appointment letter will be issued on joining.
</p>

<p>We look forward to welcoming you to <?= e($company) ?> and wish you a rewarding career with us.</p>
