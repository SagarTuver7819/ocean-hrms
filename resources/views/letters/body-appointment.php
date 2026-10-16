<p>Dear <?= e($firstName) ?>,</p>

<p>
  We are pleased to appoint you as <b><?= e($p['designation']) ?></b> with <b><?= e($company) ?></b> on the following terms and conditions:
</p>

<h4><span>1.</span>Commencement of Employment</h4>
<p>Your employment will be effective from <b><?= $d($joiningDate) ?></b>.</p>

<h4><span>2.</span>Job Title &amp; Reporting</h4>
<p>
  Your job title will be <b><?= e($p['designation']) ?></b><?= $p['department'] ? ' in the <b>' . e($p['department']) . '</b> department' : '' ?>
  and you will report to <b><?= e(!empty($employee['manager_name']) ? $employee['manager_name'] : 'your Reporting Manager / Team Lead') ?></b>.
</p>

<h4><span>3.</span>Salary</h4>
<?php if ($monthlySalary): ?>
<p>
  Your fixed monthly salary will be <b><?= $rupee($monthlySalary) ?></b> (<?= e(amount_in_words($monthlySalary)) ?>) per month,
  i.e. <b><?= $rupee($monthlySalary * 12) ?></b> per annum, paid monthly by bank transfer subject to applicable statutory deductions, if any.
  Compensation details are strictly confidential.
</p>
<?php else: ?>
<p>Your salary will be communicated to you separately in writing.</p>
<?php endif; ?>

<h4><span>4.</span>Place of Posting</h4>
<p>
  You will be posted at <b><?= e($employee['work_location'] ?? '' ?: ($employee['branch_name'] ?? '' ?: $letter['jurisdiction'])) ?></b>.
  You may be required to work at client locations or any other office of the company, or remotely, as per project requirements.
</p>

<h4><span>5.</span>Probation Period</h4>
<p>
  Your probation period will be <b><?= (int) $probation ?> month<?= (int) $probation === 1 ? '' : 's' ?></b>. During probation either party may end the employment without notice.
  If performance needs improvement, the company may extend the probation period.
</p>

<h4><span>6.</span>Working Hours &amp; Leave</h4>
<p>The normal working hours are from <b><?= e($shift) ?></b>. You may be required to work beyond these hours to meet project deadlines.</p>
<ul>
  <li>Week off will be on <b><?= e($letter['weekOff']) ?></b>.</li>
  <li>Paid leave will be as per the company leave policy.</li>
  <li>The company will publish the list of declared holidays at the beginning of each year.</li>
  <li>Leave must be applied for and approved in advance through the HRMS portal.</li>
</ul>

<h4><span>7.</span>Duties</h4>
<p>You will perform to the best of your ability all duties inherent in your role, follow project timelines, coding standards and processes, and take up additional responsibilities assigned from time to time.</p>

<h4><span>8.</span>Company Property</h4>
<p>You will maintain in good condition all company property entrusted to you, including laptop, accessories, ID card and access credentials, and return them on or before your last working day.</p>

<h4><span>9.</span>Confidentiality &amp; Intellectual Property</h4>
<p>
  You shall keep confidential all source code, designs, client data, business information and credentials you have access to.
  All work products, including source code, documentation and designs created during your employment, shall be the exclusive property of the company.
  You shall not remove, copy or share any confidential information without written permission. This obligation continues after your employment ends.
</p>

<h4><span>10.</span>Termination &amp; Notice</h4>
<ul>
  <li>After confirmation, either party may terminate the employment by giving <b><?= e($letter['notice']) ?></b> notice or salary in lieu of notice.</li>
  <li>The company may terminate employment without notice in case of misconduct, breach of confidentiality or unsatisfactory performance.</li>
  <li>On separation you shall complete the hand-over of all work, code repositories and company property.</li>
</ul>

<h4><span>11.</span>Jurisdiction</h4>
<p>Your employment is subject to Indian laws. All disputes shall be subject to the jurisdiction of <?= e($letter['jurisdiction']) ?> only.</p>

<p>Please confirm your acceptance by signing and returning the duplicate copy of this letter. We welcome you to the <?= e($company) ?> family.</p>
