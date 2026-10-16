<?php

namespace App\Middleware;

use App\Core\Auth;

class PermissionMiddleware
{
    private string $permission;

    public function __construct(string $permission = '')
    {
        $this->permission = $permission;
    }

    public function handle(): void
    {
        // Used via route closure pattern; primary check is in Controller helpers
        if (!Auth::check()) {
            $app = require BASE_PATH . '/app/Config/app.php';
            header('Location: ' . rtrim($app['url'], '/') . '/login');
            exit;
        }
    }
}
