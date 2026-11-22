<?php
namespace App\Services;
use App\Repositories\SettingsRepository;
class TelegramService
{
    private SettingsRepository $settings;
    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }
    public function sendContactMessage(array $message): bool
    {
        $token = trim((string) $this->settings->get('telegram_bot_token', ''));
        $chatId = trim((string) $this->settings->get('telegram_chat_id', ''));
        if ($token === '' || $chatId === '') {
            return false;
        }
        $text = "<b>Новое сообщение с сайта</b>\n"
            . '<b>Имя:</b> ' . htmlspecialchars($message['name'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n"
            . '<b>Email:</b> ' . htmlspecialchars($message['email'] ?? '-', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n"
            . '<b>Telegram:</b> ' . htmlspecialchars($message['telegram'] ?? '-', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n"
            . '<b>Язык:</b> ' . htmlspecialchars($message['locale'] ?? 'ru', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n\n"
            . htmlspecialchars($message['message_text'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];
        return $this->post($url, $payload);
    }
    private function post(string $url, array $payload): bool
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
            ]);
            $result = curl_exec($ch);
            curl_close($ch);
            return is_string($result) && $result !== '';
        }
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Тип контента: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($payload),
                'timeout' => 15,
            ],
        ]);
        $result = @file_get_contents($url, false, $context);
        return is_string($result) && $result !== '';
    }
}