<?php

$env = is_file(__DIR__ . '/env.php') ? require __DIR__ . '/env.php' : [];

return [
    'name'       => 'Ocean HRMS',
    'company'    => 'Ocean Infotech',
    'tagline'    => 'Be seen, get connected',
    'url'        => $env['url'] ?? 'http://localhost/Ocean_HRMS/public',
    'public_url' => $env['public_url'] ?? 'https://hrms.oceanhub.co.in', // used in the career QR code so printed posters always open the live site
    'timezone'   => 'Asia/Kolkata',
    'debug'      => $env['debug'] ?? true,
    'key'        => $env['key'] ?? 'b3aa032a99d08cc35413c39b12937cffb85527af4708e435', // encrypts stored employee login passwords; changing it invalidates them
    'session'    => 'ocean_hrms_session',
    'upload_max' => 5 * 1024 * 1024, // 5MB
    'allowed_mimes' => [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ],
    'brand' => [
        'primary'     => '#F5A01B',
        'primary_deep'=> '#d4890f',
        'primary_soft'=> '#fff4e5',
        'ink'         => '#211F23',
        'logo'        => 'assets/uploads/logo/ocean-infotech-logo.png',
    ],
];
