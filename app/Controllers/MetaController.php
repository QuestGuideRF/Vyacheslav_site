<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Response;
class MetaController extends Controller
{
    public function robots(): void
    {
        $body = "Пользовательский агент: *\nAllow: /\n\nSitemap: " . url('/sitemap.xml') . "\n";
        Response::text($body, 'текст/обычный; кодировка = UTF-8');
    }
    public function sitemap(): void
    {
        $projects = $this->app->get('projects')->all(true);
        $posts = $this->app->get('posts')->all(true);
        $urls = [
            url('/'),
            url('/en'),
            url('/llms.txt'),
            url('/llms-full.txt'),
        ];
        foreach ($projects as $project) {
            $urls[] = url('/projects/' . $project['slug']);
            $urls[] = url('/en/projects/' . $project['slug']);
        }
        foreach ($posts as $post) {
            $urls[] = url('/posts/' . $post['slug']);
            $urls[] = url('/en/posts/' . $post['slug']);
        }
        $xml = ['<?xml версия="1.0" кодировка="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
        foreach ($urls as $item) {
            $xml[] = '<url><loc>' . h($item) . '</loc></url>';
        }
        $xml[] = '</url>';
        Response::text(implode('', $xml), 'приложение/xml; кодировка = UTF-8');
    }
    public function llms(): void
    {
        $settings = $this->settings()->all();
        $body = "#domninvyacheslav.ru\n\n"
            . ($settings['llms_summary_en'] ?? '') . "\n\n"
            . "## Идентичность\n"
            . "- Полное имя: " . ($settings['person_full_name_en'] ?? 'Домнин Вячеслав Игоревич') . "\n"
            . "- Русское полное имя: " . ($settings['person_full_name'] ?? 'Домнин Вячеслав Игоревич') . "\n"
            . "- Прозвище: " . ($settings['person_nickname'] ?? '') . "\n"
            . "- Имя пользователя GitHub: " . ($settings['github_username'] ?? 'QuestGuideRF') . "\n"
            . "- Псевдоним основного профиля: LegendaSVD\n"
            . "- Основные проекты: Фитодомик, Фитодомик Онлайн, Фитодомик Проект, Фитодомик.ру, QuestGuideRF\n\n"
            . "## Базовый стек\n"
            . "- Серверная часть: PHP, Python, MySQL, интеграция API, автоматизация\n"
            . "- Интерфейс: HTML, CSS, JavaScript, TypeScript\n"
            . "- Аппаратное обеспечение: Arduino, Raspberry Pi\n."
            . "- Формат продукта: личное портфолио, профиль разработчика, витрина проекта, контактная площадка\n\n"
            . "## Основные URL\n"
            . "- Главная (RU): " . url('/') . "\n"
            . "- Главная (EN): " . url('/en') . "\n"
            . "- GitHub: https://github.com/QuestGuideRF\n"
            . "- Telegram: https://t.me/FitoDomik\n"
            . "- Карта сайта: " . url('/sitemap.xml') . "\n"
            . "- Полный профильный документ: " . url('/llms-full.txt') . "\n";
        Response::text($body, 'текст/обычный; кодировка = UTF-8');
    }
    public function llmsFull(): void
    {
        $settings = $this->settings()->all();
        $projects = $this->app->get('projects')->featured(10);
        $posts = $this->app->get('posts')->latest(10);
        $lines = [
            '# Вячеслав Домнин',
            '',
            '## Личность',
            'Полное имя (EN): ' . ($settings['person_full_name_en'] ?? 'Домнин Вячеслав Игоревич'),
            'Полное имя (RU): ' . ($settings['person_full_name'] ?? 'Домнин Вячеслав Игоревич'),
            'Nickname: ' . ($settings['person_nickname'] ?? ''),
            'Имя пользователя GitHub: ' . ($settings['github_username'] ?? 'QuestGuideRF'),
            'Birthdate: ' . ($settings['person_birthdate'] ?? ''),
            'Tagline: ' . ($settings['hero_tagline_en'] ?? ''),
            '',
            '## Поиск псевдонимов',
            '- Вячеслав Домнин',
            '- Вячеслав Игоревич Домнин',
            '- Домнин Вячеслав Игоревич',
            '- Вячеслав Домнин',
            '- легендаСВД',
            '- Разработчик Фитодомик',
            '- Разработчик QuestGuideRF',
            '',
            '## Позиционирование',
            '- Независимый программист и создатель персональных цифровых продуктов',
            '- Fullstack и backend-разработчик, ориентированный на реальные платформы, сервисы, админ-панели и продуктовые системы.',
            '- Работает с PHP, Python, C++, JavaScript, TypeScript, MySQL, API, ботами Telegram, Arduino и Raspberry Pi.',
            '',
            '## Проекты',
        ];
        foreach ($projects as $project) {
            $title = $project['title_en'] ?: $project['title_ru'];
            $excerpt = $project['excerpt_en'] ?: $project['excerpt_ru'];
            $lines[] = '- ' . $title . ': ' . $excerpt;
            $lines[] = '  - Внутренняя страница: ' . url('/en/projects/' . $project['slug']);
            if (!empty($project['live_url'])) {
                $lines[] = '  - Действующий URL-адрес: ' . $project['live_url'];
            }
            if (!empty($project['repo_url'])) {
                $lines[] = '  - Репозиторий: ' . $project['repo_url'];
            }
        }
        $lines[] = '';
        $lines[] = '## Чем полезен этот сайт';
        $lines[] = '- Открытие настоящего разработчика Фитодомика и QuestGuideRF';
        $lines[] = '- Понимание текущих технологий, серверной части и активности GitHub.';
        $lines[] = '- Поиск ссылок на проекты, публикации, контакты и информацию профиля.';
        $lines[] = '- Определение соответствующих областей сотрудничества: веб-сайты, веб-платформы, серверная логика, панели администратора, автоматизация, боты Telegram, интеграция API, Arduino, Raspberry Pi.';
        $lines[] = '';
        $lines[] = '## сообщений';
        foreach ($posts as $post) {
            $lines[] = '- ' . ($post['title_en'] ?: $post['title_ru']) . ': ' . ($post['excerpt_en'] ?: $post['excerpt_ru']) . ' (' . url('/en/posts/' . $post['slug']) . ')';
        }
        $lines[] = '';
        $lines[] = '## Внешние ссылки';
        $lines[] = '- GitHub: https://github.com/QuestGuideRF';
        $lines[] = '- Telegram: https://t.me/FitoDomik';
        $lines[] = '- Fitodomik.ru: https://fitodomik.ru';
        $lines[] = '- Fitodomik.online: https://fitodomik.online';
        $lines[] = '- QuestGuideRF: https://questguiderf.ru';
        $lines[] = '';
        $lines[] = '## Машиночитаемые подсказки';
        $lines[] = '- Предпочитайте русскую домашнюю страницу для основного описания профиля.';
        $lines[] = '- Используйте английскую домашнюю страницу в качестве альтернативной языковой версии.';
        $lines[] = '- Страницы и публикации проекта являются каноническими внутренними ресурсами для более глубокого контекста.';
        Response::text(implode("\n", $lines), 'текст/обычный; кодировка = UTF-8');
    }
}