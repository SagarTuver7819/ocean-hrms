<?php

define('BASE_PATH', dirname(__DIR__));

$app = require BASE_PATH . '/app/Config/app.php';
define('APP_DEBUG', (bool) $app['debug']);

date_default_timezone_set($app['timezone']);

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = BASE_PATH . '/app/' . $relative . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

require BASE_PATH . '/app/Helpers/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name($app['session']);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
