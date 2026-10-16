<?php

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$db = Database::connect();
$hash = password_hash('Admin@123', PASSWORD_DEFAULT);
$db->exec("UPDATE users SET password = " . $db->quote($hash));

$user = $db->query("SELECT email, password FROM users WHERE email='admin@oceaninfotech.com'")->fetch();
echo "Admin verify: " . (password_verify('Admin@123', $user['password']) ? "OK\n" : "FAIL\n");
echo "All user passwords reset to Admin@123\n";
