<?php
namespace App\Core;
class View
{
    public static function render(string $template, array $data = [], string $layout = 'public'): void
    {
        $viewFile = dirname(__DIR__) . '/Views/' . $template . '.php';
        $layoutFile = dirname(__DIR__) . '/Views/layouts/' . $layout . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException("View [{$template}] not found.");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        require $layoutFile;
    }
}