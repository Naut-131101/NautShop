<?php

declare(strict_types=1);

namespace Core;

class Router
{
    protected array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $uri, array|callable $action): void
    {
        $this->routes['GET'][$this->normalizeUri($uri)] = $action;
    }

    public function post(string $uri, array|callable $action): void
    {
        $this->routes['POST'][$this->normalizeUri($uri)] = $action;
    }

    public function dispatch(Request $request, Response $response): void
    {
        $method = strtoupper($request->method());
        $uri = $this->normalizeUri($request->path());

        $action = $this->routes[$method][$uri] ?? null;

        if (!$action) {
            $response->setStatusCode(404);
            echo '404 Not Found';
            return;
        }

        if (is_callable($action)) {
            call_user_func($action, $request, $response);
            return;
        }

        [$controller, $method] = $action;

        if (!class_exists($controller)) {
            throw new \RuntimeException("Controller [$controller] not found.");
        }

        $instance = new $controller();

        if (!method_exists($instance, $method)) {
            throw new \RuntimeException("Method [$method] not found in controller [$controller].");
        }

        call_user_func([$instance, $method], $request, $response);
    }

    protected function normalizeUri(string $uri): string
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';
        $uri = rtrim($uri, '/');

        return $uri === '' ? '/' : $uri;
    }
}