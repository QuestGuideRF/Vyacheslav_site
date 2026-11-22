<?php
use App\Core\Application;
use App\Core\View;
function app(?string $key = null): mixed
{
    $app = $GLOBALS['app_instance'] ?? null;
    if ($app === null) {
        return null;
    }
    return $key === null ? $app : $app->get($key);
}
function config(string $path, mixed $default = null): mixed
{
    $config = app('config') ?? [];
    $segments = explode('.', $path);
    $value = $config;
    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}
function base_url(): string
{
    return rtrim((string) config('app.base_url', ''), '/');
}
function url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    return base_url() . ($path === '/' ? '' : $path);
}
function asset(string $path): string
{
    return url('/assets/' . ltrim($path, '/'));
}
function upload_url(?string $path): string
{
    if (!$path) {
        return asset('img/placeholder.svg');
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return url('/' . ltrim($path, '/'));
}
function avatar_sources(?string $path, int $baseSize = 84, int $retinaSize = 168): array
{
    $fallback = upload_url($path);
    $service = app('upload');
    if (!is_object($service) || !method_exists($service, 'avatarSources')) {
        return [
            'src' => $fallback,
            'srcset' => '',
        ];
    }
    try {
        $sources = $service->avatarSources($path, $baseSize, $retinaSize);
        return is_array($sources) ? $sources : [
            'src' => $fallback,
            'srcset' => '',
        ];
    } catch (\Throwable) {
        return [
            'src' => $fallback,
            'srcset' => '',
        ];
    }
}
function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function flash(string $type, string $message): void
{
    $_SESSION['_flash'][$type][] = $message;
}
function pull_flash(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $messages;
}
function set_old(array $input): void
{
    $_SESSION['_old'] = $input;
}
function clear_old(): void
{
    unset($_SESSION['_old']);
}
function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}
function locale(): string
{
    return app('locale') ?? config('app.locale', 'ru');
}
function trans_value(array $item, string $field, ?string $lang = null, mixed $fallback = ''): mixed
{
    $lang ??= locale();
    $primary = $item[$field . '_' . $lang] ?? null;
    if ($primary !== null && $primary !== '') {
        return $primary;
    }
    $fallbackLocale = $lang === 'ru' ? 'en' : 'ru';
    $secondary = $item[$field . '_' . $fallbackLocale] ?? null;
    if ($secondary !== null && $secondary !== '') {
        return $secondary;
    }
    return $item[$field] ?? $fallback;
}
function block_extra_array(array $block): array
{
    $raw = $block['extra_json'] ?? null;
    if (!is_string($raw) || trim($raw) === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}
function block_extra_items(array $block, string $lang, string $key): array
{
    $extra = block_extra_array($block);
    $items = $extra[$key] ?? [];
    if (!is_array($items)) {
        return [];
    }
    $normalized = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $resolved = [];
        foreach ($item as $itemKey => $value) {
            if (preg_match('/^(.*)_(ru|en)$/', (string) $itemKey, $matches)) {
                if ($matches[2] === $lang) {
                    $resolved[$matches[1]] = $value;
                }
                continue;
            }
            $resolved[$itemKey] = $value;
        }
        if ($resolved !== []) {
            $normalized[] = $resolved;
        }
    }
    return $normalized;
}
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . h(csrf_token()) . '">';
}
function verify_csrf(?string $token): bool
{
    return hash_equals($_SESSION['_csrf'] ?? '', (string) $token);
}
function active_locale_path(string $lang): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = rtrim($path, '/') ?: '/';
    if ($lang === 'en') {
        if ($path === '/') {
            return '/en';
        }
        if (!str_starts_with($path, '/en')) {
            return '/en' . ($path === '/' ? '' : $path);
        }
        return $path;
    }
    if ($path === '/en') {
        return '/';
    }
    if (str_starts_with($path, '/en/')) {
        return substr($path, 3) ?: '/';
    }
    return $path;
}
function format_date(?string $date, ?string $lang = null): string
{
    if (!$date) {
        return '';
    }
    $lang ??= locale();
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }
    $months = [
        'ru' => ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'],
        'en' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    ];
    $list = $months[$lang] ?? $months['en'];
    return date('d', $timestamp) . ' ' . $list[(int) date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp);
}
function markdown_to_html(?string $text): string
{
    $text = trim((string) $text);
    if ($text === '') {
        return '';
    }
    $lines = preg_split("/\r\n|\n|\r/", $text) ?: [];
    $html = [];
    $inList = false;
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            if ($inList) {
                $html[] = '</ul>';
                $inList = false;
            }
            continue;
        }
        if (str_starts_with($line, '- ')) {
            if (!$inList) {
                $html[] = '<ul>';
                $inList = true;
            }
            $html[] = '<li>' . h(substr($line, 2)) . '</li>';
            continue;
        }
        if ($inList) {
            $html[] = '</ul>';
            $inList = false;
        }
        if (str_starts_with($line, '### ')) {
            $html[] = '<h3>' . h(substr($line, 4)) . '</h3>';
        } elseif (str_starts_with($line, '## ')) {
            $html[] = '<h2>' . h(substr($line, 3)) . '</h2>';
        } else {
            $html[] = '<p>' . nl2br(h($line)) . '</p>';
        }
    }
    if ($inList) {
        $html[] = '</ul>';
    }
    return implode("\n", $html);
}
function slugify(string $text): string
{
    $map = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh',
        'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
        'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
        'я' => 'ya',
    ];
    $text = mb_strtolower(trim($text), 'UTF-8');
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text) ?? '';
    $text = trim($text, '-');
    return $text !== '' ? $text : 'item-' . time();
}
function str_limit(string $text, int $limit = 160): string
{
    $text = trim($text);
    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $limit, 'UTF-8')) . '...';
}
function render_not_found(string $lang = 'ru', ?string $hint = null): never
{
    $lang = $lang === 'en' ? 'en' : 'ru';
    $app = app();
    if ($app instanceof Application) {
        $app->instance('locale', $lang);
    }
    $settingsRepo = app('settings');
    $settings = is_object($settingsRepo) && method_exists($settingsRepo, 'all')
        ? $settingsRepo->all()
        : [];
    $meta = [
        'title' => $lang === 'ru'
            ? 'Страница не найдена — ' . ($settings['site_title_ru'] ?? 'domninvyacheslav.ru')
            : 'Страница не найдена — ' . ($settings['site_title_en'] ?? 'domninvyacheslav.ru'),
        'description' => $lang === 'ru'
            ? 'Запрошенная страница не найдена. Вернись на главную или перейди к проектам и контактам.'
            : 'The requested page could not be found. Return to the homepage or open projects and contacts.',
        'canonical' => $lang === 'ru' ? url('/') : url('/en'),
        'alternate_ru' => url('/'),
        'alternate_en' => url('/en'),
    ];
    http_response_code(404);
    View::render('public/404', compact('lang', 'settings', 'meta', 'hint'));
    exit;
}
