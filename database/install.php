<?php

/**
 * One-time installer: creates DB schema + seeds demo data.
 * Visit: http://localhost/Ocean_HRMS/database/install.php
 * Delete this file after install in production.
 */

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbName = 'ocean_hrms';

header('Content-Type: text/html; charset=utf-8');

try {
    $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schema);

    $seed = file_get_contents(__DIR__ . '/seed.sql');
    // Replace placeholder password with Admin@123
    // Use callback so bcrypt "$2y$..." is not treated as preg backreferences.
    $hash = password_hash('Admin@123', PASSWORD_DEFAULT);
    $quoted = $pdo->quote($hash);
    $seed = preg_replace_callback(
        "/SET @pwd = '.*?';/",
        static fn() => "SET @pwd = {$quoted};",
        $seed
    );
    $pdo->exec($seed);
    $pdo = null;

    // Recruitment tables, permission & letterhead defaults
    ob_start();
    require __DIR__ . '/migrate_recruitment.php';
    ob_end_clean();

    echo '<h2 style="font-family:sans-serif;color:#211F23">Ocean HRMS installed successfully.</h2>';
    echo '<p style="font-family:sans-serif">Database: <b>ocean_hrms</b></p>';
    echo '<p style="font-family:sans-serif">Admin login: <b>admin@oceaninfotech.com</b> / <b>Admin@123</b></p>';
    echo '<p style="font-family:sans-serif">Employee login: <b>OI20260001</b> / <b>Admin@123</b></p>';
    echo '<p style="font-family:sans-serif"><a href="../public/login" style="color:#F5A01B">Go to Login</a></p>';
    echo '<p style="font-family:sans-serif;color:#b91c1c">Delete database/install.php after setup.</p>';
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h2 style="font-family:sans-serif;color:#b91c1c">Install failed</h2>';
    echo '<pre style="font-family:monospace">' . htmlspecialchars($e->getMessage()) . '</pre>';
}
