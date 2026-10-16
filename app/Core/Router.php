<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, array $handler, array $middleware): void
    {
        $this->routes[] = compact('method', 'path', 'handler', 'middleware');
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
        foreach ([$base, preg_replace('#/public$#', '', $base)] as $prefix) {
            if ($prefix && str_starts_with($uri . '/', $prefix . '/')) {
                $uri = substr($uri, strlen($prefix)) ?: '/';
                break;
            }
        }
        $uri = '/' . trim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes as $route) {
            $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] !== $method || !preg_match($pattern, $uri, $matches)) {
                continue;
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            foreach ($route['middleware'] as $mw) {
                $mwClass = "App\\Middleware\\{$mw}";
                if (class_exists($mwClass)) {
                    (new $mwClass())->handle();
                }
            }

            [$controller, $action] = $route['handler'];
            $controllerClass = "App\\Controllers\\{$controller}";
            if (!class_exists($controllerClass)) {
                http_response_code(500);
                exit("Controller not found: {$controller}");
            }

            $instance = new $controllerClass();
            call_user_func_array([$instance, $action], $params);
            return;
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Page Not Found']);
    }
}
