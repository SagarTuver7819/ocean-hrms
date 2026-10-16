<?php

$env = is_file(__DIR__ . '/env.php') ? require __DIR__ . '/env.php' : [];

return ($env['db'] ?? []) + [
    'host'    => '127.0.0.1',
    'port'    => '3306',
    'dbname'  => 'ocean_hrms',
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
];
