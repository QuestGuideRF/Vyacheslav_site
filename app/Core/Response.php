<?php
namespace App\Core;
class Response
{
    public static function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
    public static function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Тип контента: приложение/json; кодировка = UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    public static function text(string $body, string $contentType = 'текст/обычный; кодировка = UTF-8', int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: ' . $contentType);
        echo $body;
        exit;
    }
}