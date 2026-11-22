<?php
namespace App\Core;
use App\Repositories\SettingsRepository;
use App\Services\AuthService;
abstract class Controller
{
    protected Application $app;
    protected Request $request;
    public function __construct(
        Application $app,
        Request $request
    ) {
        $this->app = $app;
        $this->request = $request;
    }
    protected function view(string $template, array $data = [], string $layout = 'public'): void
    {
        View::render($template, $data, $layout);
    }
    protected function db(): Database
    {
        return $this->app->get('db');
    }
    protected function settings(): SettingsRepository
    {
        return $this->app->get('settings');
    }
    protected function auth(): AuthService
    {
        return $this->app->get('auth');
    }
    protected function requireAuth(): void
    {
        if (!$this->auth()->check()) {
            flash('error', 'Необходимо войти в админ-панель.');
            Response::redirect('/admin/login');
        }
    }
    protected function ensureCsrf(): void
    {
        if (!verify_csrf($this->request->input('_csrf'))) {
            flash('error', 'Неверный CSRF-токен.');
            $this->back();
        }
    }
    protected function back(string $fallback = '/admin'): void
    {
        $target = $_SERVER['HTTP_REFERER'] ?? $fallback;
        Response::redirect($target);
    }
}