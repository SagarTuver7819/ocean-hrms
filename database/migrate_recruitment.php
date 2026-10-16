<?php

/**
 * Recruitment module migration (safe to re-run).
 * Run: php database/migrate_recruitment.php
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$db = Database::connect();

$sql = file_get_contents(__DIR__ . '/recruitment.sql');
foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
    $db->exec($statement);
}

$hasParentMobile = $db->query("SHOW COLUMNS FROM candidates LIKE 'parent_mobile'")->fetch();
if (!$hasParentMobile) {
    $db->exec('ALTER TABLE candidates ADD COLUMN parent_mobile VARCHAR(20) NULL AFTER alt_mobile');
}

$db->prepare(
    "INSERT IGNORE INTO permissions (name, slug, module, created_at) VALUES ('Manage Recruitment', 'recruitment.manage', 'recruitment', NOW())"
)->execute();
$permId = (int) $db->query("SELECT id FROM permissions WHERE slug = 'recruitment.manage'")->fetchColumn();
foreach (['hr_head', 'hr_executive'] as $roleSlug) {
    $db->prepare(
        'INSERT IGNORE INTO role_permissions (role_id, permission_id) SELECT id, ? FROM roles WHERE slug = ?'
    )->execute([$permId, $roleSlug]);
}

$defaults = [
    'careers_positions'    => "Web Developer\nWeb Designer\nDesigning\nReceptionist\nSales Executive",
    'letterhead_mode'      => 'auto',
    'letterhead_header'    => '',
    'letterhead_footer'    => '',
    'letterhead_full'      => '',
    'letterhead_top_mm'    => '38',
    'letterhead_bottom_mm' => '22',
    'letterhead_side_mm'   => '12',
    'careers_intro'        => 'Ocean Infotech is a software development company building web, mobile and cloud solutions for clients across India and abroad. We are always looking for passionate developers, designers, testers and IT professionals to grow with us.',
];
$stmt = $db->prepare('INSERT IGNORE INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW())');
foreach ($defaults as $key => $value) {
    $stmt->execute([$key, $value]);
}

foreach (['storage/uploads/recruitment', 'public/assets/uploads/letterhead'] as $dir) {
    $path = BASE_PATH . '/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

echo "Recruitment module migrated successfully.\n";
