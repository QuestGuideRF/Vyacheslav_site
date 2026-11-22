<?php
namespace App\Core;
class Request
{
    private array $get;
    private array $post;
    private array $files;
    private array $server;
    public function __construct(
        array $get,
        array $post,
        array $files,
        array $server
    ) {
        $this->get = $get;
        $this->post = $post;
        $this->files = $files;
        $this->server = $server;
    }
    public static function capture(): self
    {
        return new self($_GET, $_POST, $_FILES, $_SERVER);
    }
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }
    public function path(): string
    {
        $path = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        return rtrim($path, '/') ?: '/';
    }
    public function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->method() === 'POST' ? $this->post : $this->get;
        }
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }
    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;
        return is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE ? $file : null;
    }
    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}