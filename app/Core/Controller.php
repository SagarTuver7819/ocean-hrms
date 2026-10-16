<?php

namespace App\Core;

use PDO;

abstract class Controller
{
    protected function db(): PDO
    {
        return Database::connect();
    }

    /** Stops the request with a flash error when the posted CSRF token is invalid. */
    protected function requireCsrf(string $redirectTo, string $message = 'Invalid token.'): void
    {
        if (!CSRF::validate($this->input('_csrf'))) {
            $this->flash('error', $message);
            $this->redirect($redirectTo);
        }
    }

    protected function view(string $template, array $data = [], ?string $layout = 'layouts/app'): void
    {
        View::render($template, $data, $layout);
    }

    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . View::url($path));
        exit;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}
