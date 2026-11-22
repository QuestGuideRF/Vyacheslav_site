<?php
namespace App\Core;
class Application
{
    private array $bindings = [];
    private array $instances = [];
    private array $config;
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->instances['config'] = $config;
    }
    public function bind(string $key, callable $resolver): void
    {
        $this->bindings[$key] = $resolver;
    }
    public function instance(string $key, mixed $value): void
    {
        $this->instances[$key] = $value;
    }
    public function get(string $key): mixed
    {
        if (array_key_exists($key, $this->instances)) {
            return $this->instances[$key];
        }
        if (!array_key_exists($key, $this->bindings)) {
            throw new \RuntimeException("Service [{$key}] is not registered.");
        }
        $this->instances[$key] = ($this->bindings[$key])($this);
        return $this->instances[$key];
    }
    public function config(): array
    {
        return $this->config;
    }
}