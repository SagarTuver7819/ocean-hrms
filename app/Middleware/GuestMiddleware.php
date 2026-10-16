<?php

namespace App\Middleware;

use App\Core\Auth;

class GuestMiddleware
{
    public function handle(): void
    {
        if (Auth::check()) {
            $app = require BASE_PATH . '/app/Config/app.php';
            $dest = Auth::isEmployeePortal() ? '/employee/dashboard' : '/dashboard';
            header('Location: ' . rtrim($app['url'], '/') . $dest);
            exit;
        }
    }
}
