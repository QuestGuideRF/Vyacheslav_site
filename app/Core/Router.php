<?php
namespace App\Core;
class Router
{
    private array $routes = [];
    public function get(string $pattern, callable|array $handler): void
    {
        $this->add(['GET'], $pattern, $handler);
    }
    public function post(string $pattern, callable|array $handler): void
    {
        $this->add(['POST'], $pattern, $handler);
    }
    public function match(array $methods, string $pattern, callable|array $handler): void
    {
        $this->add($methods, $pattern, $handler);
    }
    private function add(array $methods, string $pattern, callable|array $handler): void
    {
        $this->routes[] = [
            'methods' => array_map('strtoupper', $methods),
            'pattern' => rtrim($pattern, '/') ?: '/',
            'handler' => $handler,
        ];
    }
    public function dispatch(Request $request, Application $app): mixed
    {
        $path = $request->path();
        $method = $request->method();
        foreach ($this->routes as $route) {
            if (!in_array($method, $route['methods'], true)) {
                continue;
            }
            $paramNames = [];
            $pattern = preg_replace_callback(
                '#\\\\\{([a-zA-Z_][a-zA-Z0-9_-]*)\\\\\}#',
                static function (array $matches) use (&$paramNames): string {
                    $paramNames[] = $matches[1];
                    return '([^/]+)';
                },
                preg_quote($route['pattern'], '#')
            );
            $pattern = '#^' . $pattern . '$#';
            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }
            array_shift($matches);
            $params = [];
            foreach ($paramNames as $index => $name) {
                $params[$name] = urldecode($matches[$index] ?? '');
            }
            $handler = $route['handler'];
            if (is_array($handler)) {
                [$class, $action] = $handler;
                $controller = new $class($app, $request);
                return $controller->{$action}(...array_values($params));
            }
            return $handler($request, $app, $params);
        }
        $lang = str_starts_with($path, '/en') ? 'en' : 'ru';
        render_not_found($lang);
    }
}