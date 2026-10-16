<?php

namespace App\Core;

class View
{
    public static function render(string $template, array $data = [], ?string $layout = null): void
    {
        extract($data, EXTR_SKIP);
        $app = require BASE_PATH . '/app/Config/app.php';
        $authUser = Auth::user();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $viewFile = BASE_PATH . '/resources/views/' . str_replace('.', '/', $template) . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            exit("View not found: {$template}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout) {
            $layoutFile = BASE_PATH . '/resources/views/' . str_replace('.', '/', $layout) . '.php';
            require $layoutFile;
            return;
        }

        echo $content;
    }

    public static function partial(string $name, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $app = require BASE_PATH . '/app/Config/app.php';
        require BASE_PATH . '/resources/views/' . str_replace('.', '/', $name) . '.php';
    }

    public static function asset(string $path): string
    {
        return self::baseUrl() . '/' . ltrim($path, '/');
    }

    public static function url(string $path = ''): string
    {
        return self::baseUrl() . '/' . ltrim($path, '/');
    }

    /**
     * Configured app URL, but with the host the visitor actually used
     * (so LAN IP / domain access from phones works with the career QR).
     */
    public static function baseUrl(): string
    {
        static $base = null;
        if ($base !== null) {
            return $base;
        }
        $app = require BASE_PATH . '/app/Config/app.php';
        $base = rtrim($app['url'], '/');
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host && preg_match('/^[A-Za-z0-9.\-]+(:\d+)?$/', $host)) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $base = preg_replace('#^https?://[^/]+#', $scheme . '://' . $host, $base);
        }
        return $base;
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
