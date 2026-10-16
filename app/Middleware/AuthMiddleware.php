<?php

namespace App\Middleware;

use App\Core\Auth;

class AuthMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            $app = require BASE_PATH . '/app/Config/app.php';
            header('Location: ' . rtrim($app['url'], '/') . '/login');
            exit;
        }

        // Session timeout: 2 hours
        $last = $_SESSION['last_activity'] ?? time();
        if ((time() - $last) > 7200) {
            Auth::logout();
            $app = require BASE_PATH . '/app/Config/app.php';
            header('Location: ' . rtrim($app['url'], '/') . '/login?timeout=1');
            exit;
        }
        $_SESSION['last_activity'] = time();

        if (!empty($_SESSION['user']['must_change_password'])) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (!str_contains($uri, 'change-password') && !str_contains($uri, 'logout')) {
                $app = require BASE_PATH . '/app/Config/app.php';
                header('Location: ' . rtrim($app['url'], '/') . '/change-password');
                exit;
            }
        }
    }
}
