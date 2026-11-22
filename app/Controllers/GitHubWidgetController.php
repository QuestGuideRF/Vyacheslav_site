<?php
namespace App\Controllers;
use App\Core\Controller;
class GitHubWidgetController extends Controller
{
    public function show(string $type, string $theme): void
    {
        $allowedTypes = ['stats', 'languages', 'streak', 'activity', 'trophy'];
        if (!in_array($type, $allowedTypes, true)) {
            http_response_code(404);
            header('Тип контента: изображение/svg+xml; кодировка = UTF-8');
            echo $this->app->get('github')->renderWidget('missing', 'dark');
            exit;
        }
        $theme = $theme === 'light' ? 'light' : 'dark';
        $svg = $this->app->get('github')->renderWidget($type, $theme);
        http_response_code(200);
        header('Тип контента: изображение/svg+xml; кодировка = UTF-8');
        header('Cache-Control: общедоступный, максимальный возраст = 900');
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: ' . strlen($svg));
        echo $svg;
        exit;
    }
}