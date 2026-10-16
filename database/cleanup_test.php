<?php

require dirname(__DIR__) . '/app/bootstrap.php';

$db = App\Core\Database::connect();
$row = $db->query("SELECT id, position_other, designation_id, resume_path FROM candidates WHERE email = 't@example.com'")->fetch();
echo 'saved position: ' . ($row['position_other'] ?? 'NONE') . "\n";
if ($row) {
    if ($row['resume_path'] && is_file(BASE_PATH . '/' . $row['resume_path'])) unlink(BASE_PATH . '/' . $row['resume_path']);
    $db->prepare('DELETE FROM candidates WHERE id = ?')->execute([$row['id']]);
    $db->exec("DELETE FROM notifications WHERE title = 'New job application'");
    $db->exec('ALTER TABLE candidates AUTO_INCREMENT = 1');
}
echo "cleaned\n";
