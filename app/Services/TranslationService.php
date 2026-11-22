<?php
namespace App\Services;
class TranslationService
{
    public function translateFields(array $fields, string $from = 'ru', string $to = 'en'): array
    {
        $translated = [];
        foreach ($fields as $key => $value) {
            $translated[$key] = $this->translateText((string) $value, $from, $to);
        }
        return $translated;
    }
    public function translateText(string $text, string $from = 'ru', string $to = 'en'): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }
        $chunks = $this->chunkText($text, 420);
        $translated = [];
        foreach ($chunks as $chunk) {
            $url = 'https://api.mymemory.translated.net/get?q=' . rawurlencode($chunk) . '&langpair=' . rawurlencode($from . '|' . $to);
            $response = $this->requestJson($url);
            $translated[] = trim((string) ($response['responseData']['translatedText'] ?? $chunk));
        }
        return trim(implode("\n\n", $translated));
    }
    private function requestJson(string $url): array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_USERAGENT => 'domninvyacheslav-portfolio/1.0',
            ]);
            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            $result = @file_get_contents($url);
        }
        if (!is_string($result) || $result === '') {
            return [];
        }
        $decoded = json_decode($result, true);
        return is_array($decoded) ? $decoded : [];
    }
    private function chunkText(string $text, int $limit = 420): array
    {
        $paragraphs = preg_split("/\n{2,}/", $text) ?: [$text];
        $chunks = [];
        $buffer = '';
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }
            $candidate = $buffer === '' ? $paragraph : $buffer . "\n\n" . $paragraph;
            if (mb_strlen($candidate, 'UTF-8') <= $limit) {
                $buffer = $candidate;
                continue;
            }
            if ($buffer !== '') {
                $chunks[] = $buffer;
                $buffer = '';
            }
            while (mb_strlen($paragraph, 'UTF-8') > $limit) {
                $chunks[] = mb_substr($paragraph, 0, $limit, 'UTF-8');
                $paragraph = mb_substr($paragraph, $limit, null, 'UTF-8');
            }
            $buffer = $paragraph;
        }
        if ($buffer !== '') {
            $chunks[] = $buffer;
        }
        return $chunks ?: [$text];
    }
}