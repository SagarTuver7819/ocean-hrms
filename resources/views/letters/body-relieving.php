<?php $pronoun = ($p['gender'] ?? '') === 'female' ? 'her' : 'his'; ?>
<p>Dear <?= e($firstName) ?>,</p>

<p>
  This is with reference to your resignation from the position of <b><?= e($p['designation']) ?></b>.
  We would like to inform you that your resignation has been accepted and you are relieved from your duties with
  <b><?= e($company) ?></b> at the close of business hours on <b><?= $d($lastDay) ?></b>.
</p>

<div class="lt-hl">
  <table>
    <tr><td>Employee Code</td><td><?= e($p['code']) ?></td></tr>
    <tr><td>Designation</td><td><?= e($p['designation']) ?></td></tr>
    <?php if ($p['department']): ?><tr><td>Department</td><td><?= e($p['department']) ?></td></tr><?php endif; ?>
    <tr><td>Date of Joining</td><td><?= $d($joiningDate) ?></td></tr>
    <tr><td>Last Working Day</td><td><?= $d($lastDay) ?></td></tr>
  </table>
</div>

<p>
  We confirm that you have handed over all work, project documents, source code access and company property in your possession.
  Your full and final settlement will be processed as per company policy.
</p>

<p>
  Please note that the confidentiality and intellectual property obligations mentioned in your appointment letter continue to apply after your separation.
</p>

<p>We thank you for your contribution to <?= e($company) ?> and wish you success in your future endeavours.</p>
