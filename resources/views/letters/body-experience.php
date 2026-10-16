<?php
$separated = in_array($employee['status'] ?? '', ['inactive', 'resigned'], true);
$pronoun = ($p['gender'] ?? '') === 'female' ? ['she', 'her'] : ['he', 'his'];
?>
<p>
  This is to certify that <b><?= e($fullName) ?></b> (Employee Code: <b><?= e($p['code']) ?></b>)
  <?= $separated ? 'was' : 'has been' ?> working with <b><?= e($company) ?></b> as
  <b><?= e($p['designation']) ?></b><?= $p['department'] ? ' in the <b>' . e($p['department']) . '</b> department' : '' ?>
  from <b><?= $d($joiningDate) ?></b> <?= $separated ? 'to <b>' . $d($lastDay) . '</b>' : 'till date' ?>.
</p>

<p>
  During <?= $pronoun[1] ?> tenure with us, <?= $pronoun[0] ?> has shown sincerity, technical competence and a positive attitude towards work.
  <?= ucfirst($pronoun[1]) ?> conduct and performance have been found to be good.
</p>

<p>This certificate is issued on <?= $pronoun[1] ?> request for whatever purpose it may serve.</p>

<p>We wish <?= $pronoun[1] === 'her' ? 'her' : 'him' ?> all the best for future endeavours.</p>
