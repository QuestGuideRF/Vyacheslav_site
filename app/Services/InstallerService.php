<?php
namespace App\Services;
use App\Core\Database;
class InstallerService
{
    private const CONTENT_VERSION = 4;
    public function writeConfig(array $payload): void
    {
        $configFile = dirname(__DIR__) . '/Config/config.php';
        $config = require dirname(__DIR__) . '/Config/config.example.php';
        $config['app']['base_url'] = rtrim($payload['base_url'], '/');
        $config['app']['name'] = parse_url($payload['base_url'], PHP_URL_HOST) ?: $payload['base_url'];
        $config['db']['host'] = $payload['db_host'];
        $config['db']['port'] = (int) $payload['db_port'];
        $config['db']['database'] = $payload['db_name'];
        $config['db']['username'] = $payload['db_user'];
        $config['db']['password'] = $payload['db_pass'];
        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        file_put_contents($configFile, $content);
    }
    public function createStorageDirs(array $config): void
    {
        foreach ([
            $config['app']['cache_dir'],
            $config['app']['log_dir'],
            $config['app']['uploads_dir'],
            dirname($config['app']['install_lock']),
        ] as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0775, true);
            }
        }
    }
    public function seed(Database $db, array $payload): void
    {
        $schema = file_get_contents(dirname(__DIR__) . '/Config/schema.sql');
        if ($schema === false) {
            throw new \RuntimeException('Не удалось прочитать файл schema.sql');
        }
        $db->pdo()->exec($schema);
        $db->transaction(function (Database $database) use ($payload) {
            $database->execute('DELETE FROM admins');
            $database->execute('DELETE FROM settings');
            $database->execute('DELETE FROM content_blocks');
            $database->execute('DELETE FROM projects');
            $database->execute('DELETE FROM posts');
            $database->execute('DELETE FROM contacts');
            $this->seedAdmins($database, $payload);
            $this->seedSettings($database);
            $this->seedBlocks($database);
            $this->seedProjects($database);
            $this->seedPosts($database);
            $this->seedContacts($database);
            $this->applyContentUpgrade($database);
        });
    }
    public function ensureInstalled(Database $db, array $config): void
    {
        $this->createStorageDirs($config);
        $schema = file_get_contents(dirname(__DIR__) . '/Config/schema.sql');
        if ($schema === false) {
            throw new \RuntimeException('Не удалось прочитать файл schema.sql');
        }
        $db->pdo()->exec($schema);
        $setup = $config['setup'] ?? [];
        $payload = [
            'admin_username' => $setup['admin_username'] ?? 'admin',
            'admin_password' => $setup['admin_password'] ?? 'admin12345',
            'admin_name' => $setup['admin_name'] ?? 'Administrator',
        ];
        if (!$db->fetch('SELECT id FROM admins LIMIT 1')) {
            $this->seedAdmins($db, $payload);
        }
        if (!$db->fetch('SELECT id FROM settings LIMIT 1')) {
            $this->seedSettings($db);
        }
        if (!$db->fetch('SELECT id FROM content_blocks LIMIT 1')) {
            $this->seedBlocks($db);
        }
        if (!$db->fetch('SELECT id FROM projects LIMIT 1')) {
            $this->seedProjects($db);
        }
        if (!$db->fetch('SELECT id FROM posts LIMIT 1')) {
            $this->seedPosts($db);
        }
        if (!$db->fetch('SELECT id FROM contacts LIMIT 1')) {
            $this->seedContacts($db);
        }
        $this->applyContentUpgrade($db);
        if (!is_file($config['app']['install_lock'])) {
            $this->markInstalled($config);
        }
    }
    public function markInstalled(array $config): void
    {
        file_put_contents($config['app']['install_lock'], date('c'));
    }
    private function seedSettings(Database $db): void
    {
        $this->saveMany($db, 'general', [
            'site_title_ru' => 'Домнин Вячеслав Игоревич — программист и создатель цифровых проектов',
            'site_title_en' => 'Вячеслав Домнин — программист и создатель цифровых продуктов',
            'site_description_ru' => 'Персональный двуязычный сайт-портфолио программиста Домнина Вячеслава Игоревича: проекты, GitHub-активность, посты, контакты и планы развития.',
            'site_description_en' => 'Двуязычное личное портфолио Вячеслава Домнина: проекты, активность на GitHub, посты, контакты и текущие цели.',
            'person_full_name' => 'Домнин Вячеслав Игоревич',
            'person_full_name_en' => 'Домнин Вячеслав Игоревич',
            'person_nickname' => '༺Leͥgeͣnͫda༻ᴳᵒᵈ',
            'person_birthdate' => '2008-07-12',
            'hero_tagline_ru' => 'Программист. Разработчик проектов. Python / C++ / JavaScript.',
            'hero_tagline_en' => 'Программист. Строитель проекта. Питон/С++/JavaScript.',
            'hero_intro_ru' => 'Делаю собственные цифровые продукты: от логики и базы данных до интерфейсов, интеграций и живой поддержки проекта после запуска.',
            'hero_intro_en' => 'Я создаю свои собственные цифровые продукты от начала до конца: серверную логику, интерфейсы, интеграцию и развитие продукта после запуска.',
            'card_roles_ru' => 'Код, электроника, Fullstack, Python/C++, Arduino и Raspberry Pi',
            'card_roles_en' => 'Код, электроника, Fullstack, Python/C++, Arduino и Raspberry Pi',
            'card_status_ru' => 'Открыт к новым проектам и сотрудничеству',
            'card_status_en' => 'Открыт для новых проектов и сотрудничества',
            'card_location_ru' => 'Россия / онлайн',
            'card_location_en' => 'Россия / удаленно',
            'avatar_path' => '',
            'github_username' => 'QuestGuideRF',
            'github_token' => '',
            'telegram_bot_token' => '',
            'telegram_chat_id' => '',
            'contact_email' => 'vdomninsg@mail.ru',
            'default_theme' => 'dark',
            'person_biography_ru' => $this->defaultBiographyRu(),
            'person_biography_en' => $this->defaultBiographyEn(),
        ]);
        $this->saveMany($db, 'seo', [
            'seo_home_title_ru' => 'Домнин Вячеслав Игоревич 2008 — программист, проект, GitHub, контакт',
            'seo_home_title_en' => 'Вячеслав Домнин 2008 — программист, проекты, GitHub, контакты',
            'seo_home_description_ru' => 'Современный двуязычный сайт-портфолио Домнины Вячеслава Игоревича: проект Фитодомик и QuestGuideRF, GitHub-статистика, посты, контакты и админ-панель.',
            'seo_home_description_en' => 'Современный двуязычный сайт-портфолио Вячеслава Домнина с проектами, статистикой GitHub, постами, контактами и админ-панелью.',
            'llms_summary_ru' => 'Персональный сайт-портфолио Домнина Вячеслава Игоревича, программиста и автора собственных цифровых проектов.',
            'llms_summary_en' => 'Сайт личного портфолио Вячеслава Домнина, программиста и создателя цифровых продуктов.',
        ]);
        $this->saveMany($db, 'general', $this->defaultSeoGeneralSettings(), true);
        $this->saveMany($db, 'seo', $this->defaultHomeSeoSettings(), true);
    }
    private function seedBlocks(Database $db): void
    {
        foreach ($this->defaultBlocks() as $block) {
            $this->upsertBlock($db, $block);
        }
    }
    private function seedProjects(Database $db): void
    {
        $projects = [
            [
                'slug' => 'fitodomik-online',
                'title_ru' => 'Фитодомика онлайн',
                'title_en' => 'Фитодомика онлайн',
                'excerpt_ru' => 'Онлайн-направление экосистемы Fitodomik: современная веб-платформа и развитие цифрового присутствия.',
                'excerpt_en' => 'Онлайн-направление экосистемы Фитодомик: современная веб-платформа и более сильное цифровое присутствие.',
                'content_ru' => "Проект ориентирован на развитие современного онлайн-присутствия и цифровых инструментов бренда Fitodomik.\n\nЗдесь важны структура, удобство, визуальная подача и постоянное развитие продукта.",
                'content_en' => "Проект ориентирован на создание современного онлайн-присутствия и цифровых инструментов для бренда «Фитодомик».\n\nЗдесь важны структура, удобство использования, презентация и стабильный рост продукта.",
                'status_ru' => 'В активной разработке',
                'status_en' => 'Активно развивается',
                'stack_text' => 'PHP, JavaScript, MySQL, HTML, CSS',
                'live_url' => 'https://fitodomik.online',
                'repo_url' => 'https://github.com/QuestGuideRF/fitodomik',
                'featured' => 1,
                'sort_order' => 10,
            ],
            [
                'slug' => 'fitodomik-project',
                'title_ru' => 'Проект Фитодомик',
                'title_en' => 'Проект Фитодомик',
                'excerpt_ru' => 'Более широкая проектная основа Fitodomik: архитектура, структура и функциональное развитие.',
                'excerpt_en' => 'Более широкий фундамент Фитодомика: архитектура, структура и развитие функций.',
                'content_ru' => "Это часть более крупной системы, где важно продумывать логику продукта, масштабирование и удобство для пользователей.\n\nПроект помогает мне развивать fullstack-навыки на практике.",
                'content_en' => "This is part of a larger system where product logic, scalability, and user experience all matter.\n\nIt helps me grow real fullstack development skills in practice.",
                'status_ru' => 'Развивается',
                'status_en' => 'Growing',
                'stack_text' => 'PHP, JS, MySQL, UX',
                'repo_url' => 'https://github.com/QuestGuideRF/fitodomik',
                'featured' => 1,
                'sort_order' => 20,
            ],
            [
                'slug' => 'fitodomik-ru',
                'title_ru' => 'Fitodomik.ru',
                'title_en' => 'Fitodomik.ru',
                'excerpt_ru' => 'Публичная веб-площадка с акцентом на презентацию, структуру и развитие бренда.',
                'excerpt_en' => 'Публичная веб-платформа, ориентированная на презентацию, структуру и рост бренда.',
                'content_ru' => "Проект сочетает визуальную подачу, работу с контентом и развитие веб-продукта.\n\nЭто часть экосистемы, которая помогает выстраивать узнаваемость и полезный пользовательский опыт.",
                'content_en' => "Проект сочетает в себе презентацию, работу с контентом и эволюцию веб-продуктов.\n\nЭто часть экосистемы, нацеленной на наглядность и полезный пользовательский опыт.",
                'status_ru' => 'Публичный проект',
                'status_en' => 'Общественный проект',
                'stack_text' => 'HTML, CSS, JavaScript, контент',
                'live_url' => 'https://fitodomik.ru',
                'repo_url' => 'https://github.com/QuestGuideRF/fitodomik',
                'featured' => 1,
                'sort_order' => 30,
            ],
            [
                'slug' => 'questguiderf',
                'title_ru' => 'QuestGuideRF',
                'title_en' => 'QuestGuideRF',
                'excerpt_ru' => 'Отдельный проект с собственной тематикой, логикой и направлением развития.',
                'excerpt_en' => 'Отдельный проект со своей темой, логикой и направлением.',
                'content_ru' => "QuestGuideRF показывает, что мне интересно делать разные типы цифровых проектов и развивать их как самостоятельные продукты.\n\nЭто не просто репозиторий, а идея, которую можно усиливать через функциональность и контент.",
                'content_en' => "QuestGuideRF показывает, что мне нравится создавать различные типы цифровых продуктов и развивать их как независимые платформы.\n\nЭто не просто репозиторий, а идея, которая может постоянно расширяться за счет функций и контента.",
                'status_ru' => 'Отдельное направление',
                'status_en' => 'Независимое направление',
                'stack_text' => 'PHP, JS, Дизайн продукта',
                'live_url' => 'https://questguiderf.ru',
                'repo_url' => 'https://github.com/QuestGuideRF/Quest-Guide-rf',
                'featured' => 1,
                'sort_order' => 40,
            ],
        ];
        foreach ($projects as $project) {
            $project = array_merge([
                'live_url' => null,
                'repo_url' => null,
            ], $project);
            $db->execute(
                'INSERT INTO projects (slug, title_ru, title_en, excerpt_ru, excerpt_en, content_ru, content_en, status_ru, status_en, stack_text, live_url, repo_url, featured, sort_order, published)
                 VALUES (:slug, :title_ru, :title_en, :excerpt_ru, :excerpt_en, :content_ru, :content_en, :status_ru, :status_en, :stack_text, :live_url, :repo_url, :featured, :sort_order, 1)',
                $project
            );
        }
    }
    private function seedPosts(Database $db): void
    {
        $post = [
            'slug' => 'portfolio-launch',
            'title_ru' => 'Запуск личного сайта-портфолио',
            'title_en' => 'Запускаем сайт личного портфолио',
            'excerpt_ru' => 'Первый публичный релиз сайта с личной карточкой, проектами, GitHub-статистикой и админ-панелью.',
            'excerpt_en' => 'Первый публичный релиз сайта с карточкой профиля, проектами, статистикой GitHub и админ-панелью.',
            'content_ru' => "Этот сайт задуман не как простая страница обо мне, а как рабочая платформа, где видны мои проекты, активность и развитие как разработчика.\n\nДальше здесь будут появляться новые заметки, обновления проектов и технические изменения.",
            'content_en' => "Этот веб-сайт задуман не как простая страница «О нас», а как рабочая платформа, показывающая мои проекты, деятельность и рост как разработчика.\n\nНовые заметки, обновления проектов и технические улучшения будут появляться здесь со временем.",
            'category_ru' => 'Обновление сайта',
            'category_en' => 'Обновление сайта',
        ];
        $db->execute(
            'INSERT INTO posts (slug, title_ru, title_en, excerpt_ru, excerpt_en, content_ru, content_en, category_ru, category_en, published, published_at)
             VALUES (:slug, :title_ru, :title_en, :excerpt_ru, :excerpt_en, :content_ru, :content_en, :category_ru, :category_en, 1, NOW())',
            $post
        );
    }
    private function seedContacts(Database $db): void
    {
        $contacts = [
            ['Telegram', 'Telegram', '@ФитоДомик', 'https://t.me/FitoDomik', 'telegram', 'telegram', 10],
            ['GitHub', 'GitHub', 'QuestGuideRF', 'https://github.com/QuestGuideRF', 'github', 'github', 20],
            ['Email', 'Email', 'vdomninsg@mail.ru', 'почта: vdomninsg@mail.ru', 'email', 'mail', 30],
        ];
        foreach ($contacts as [$labelRu, $labelEn, $value, $url, $type, $icon, $sort]) {
            $db->execute(
                'INSERT INTO contacts (label_ru, label_en, value_text, link_url, type, icon_name, sort_order, enabled)
                 VALUES (:label_ru, :label_en, :value_text, :link_url, :type, :icon_name, :sort_order, 1)',
                [
                    'label_ru' => $labelRu,
                    'label_en' => $labelEn,
                    'value_text' => $value,
                    'link_url' => $url,
                    'type' => $type,
                    'icon_name' => $icon,
                    'sort_order' => $sort,
                ]
            );
        }
    }
    private function seedAdmins(Database $db, array $payload): void
    {
        $db->execute(
            'INSERT INTO admins (username, password_hash, display_name) VALUES (:username, :password_hash, :display_name)',
            [
                'username' => $payload['admin_username'],
                'password_hash' => password_hash($payload['admin_password'], PASSWORD_DEFAULT),
                'display_name' => $payload['admin_name'],
            ]
        );
    }
    private function applyContentUpgrade(Database $db): void
    {
        $currentVersion = (int) ($db->fetch(
            'SELECT value_long FROM settings WHERE `key` = :key LIMIT 1',
            ['key' => 'content_seed_version']
        )['value_long'] ?? 0);
        if ($currentVersion < self::CONTENT_VERSION) {
            $this->saveMany($db, 'general', [
                'person_biography_ru' => $this->defaultBiographyRu(),
                'person_biography_en' => $this->defaultBiographyEn(),
            ], true);
            $this->saveMany($db, 'general', $this->defaultSeoGeneralSettings(), true);
            $this->saveMany($db, 'seo', $this->defaultHomeSeoSettings(), true);
            foreach ($this->defaultBlocks() as $block) {
                $overwrite = in_array($block['section_key'], ['journey', 'plans', 'seo_profile'], true);
                $this->upsertBlock($db, $block, $overwrite);
            }
            $this->saveSetting($db, 'system', 'content_seed_version', (string) self::CONTENT_VERSION, true);
            return;
        }
        $this->saveMany($db, 'general', [
            'person_biography_ru' => $this->defaultBiographyRu(),
            'person_biography_en' => $this->defaultBiographyEn(),
        ]);
        $this->saveMany($db, 'general', $this->defaultSeoGeneralSettings());
        $this->saveMany($db, 'seo', $this->defaultHomeSeoSettings());
        foreach ($this->defaultBlocks() as $block) {
            if (!in_array($block['section_key'], ['journey', 'services', 'techstack', 'seo_profile'], true)) {
                continue;
            }
            $this->upsertBlock($db, $block);
        }
    }
    private function saveMany(Database $db, string $group, array $values, bool $overwrite = false): void
    {
        foreach ($values as $key => $value) {
            $this->saveSetting($db, $group, $key, $value, $overwrite);
        }
    }
    private function saveSetting(Database $db, string $group, string $key, string $value, bool $overwrite = false): void
    {
        $exists = $db->fetch('SELECT id FROM settings WHERE `key` = :key LIMIT 1', ['key' => $key]);
        if ($exists) {
            if ($overwrite) {
                $db->execute(
                    'UPDATE settings SET `group` = :group_name, value_long = :value WHERE `key` = :key',
                    ['group_name' => $group, 'value' => $value, 'key' => $key]
                );
            }
            return;
        }
        $db->execute(
            'INSERT INTO settings (`group`, `key`, value_long) VALUES (:group_name, :key, :value)',
            ['group_name' => $group, 'key' => $key, 'value' => $value]
        );
    }
    private function upsertBlock(Database $db, array $block, bool $overwrite = false): void
    {
        $payload = [
            'section_key' => $block['section_key'],
            'label' => $block['label'],
            'title_ru' => $block['title_ru'] ?? null,
            'title_en' => $block['title_en'] ?? null,
            'lead_ru' => $block['lead_ru'] ?? null,
            'lead_en' => $block['lead_en'] ?? null,
            'content_ru' => $block['content_ru'] ?? null,
            'content_en' => $block['content_en'] ?? null,
            'extra_json' => !empty($block['extra_json'])
                ? json_encode($block['extra_json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null,
            'sort_order' => $block['sort_order'] ?? 0,
        ];
        $exists = $db->fetch(
            'SELECT id FROM content_blocks WHERE section_key = :section_key LIMIT 1',
            ['section_key' => $payload['section_key']]
        );
        if ($exists) {
            if ($overwrite) {
                $updatePayload = $payload;
                $updatePayload['id'] = $exists['id'];
                unset($updatePayload['section_key']);
                $db->execute(
                    'UPDATE content_blocks
                     SET label = :label,
                         title_ru = :title_ru,
                         title_en = :title_en,
                         lead_ru = :lead_ru,
                         lead_en = :lead_en,
                         content_ru = :content_ru,
                         content_en = :content_en,
                         extra_json = :extra_json,
                         sort_order = :sort_order,
                         enabled = 1
                     WHERE id = :id',
                    $updatePayload
                );
            }
            return;
        }
        $db->execute(
            'INSERT INTO content_blocks (section_key, label, title_ru, title_en, lead_ru, lead_en, content_ru, content_en, extra_json, sort_order, enabled)
             VALUES (:section_key, :label, :title_ru, :title_en, :lead_ru, :lead_en, :content_ru, :content_en, :extra_json, :sort_order, 1)',
            $payload
        );
    }
    private function defaultBlocks(): array
    {
        return [
            [
                'section_key' => 'about',
                'label' => 'Обо мне',
                'title_ru' => 'Мне интересен не код ради кода, а проекты, которые реально работают.',
                'title_en' => 'Меня меньше волнует код ради кода, а больше — проекты, которые действительно работают.',
                'lead_ru' => 'Я развиваюсь как разработчик через собственные продукты, практику и доведение идей до результата.',
                'lead_en' => 'Я расту как разработчик благодаря реальным продуктам, практической работе и доведению идей до готового состояния.',
                'content_ru' => $this->defaultAboutContentRu(),
                'content_en' => $this->defaultAboutContentEn(),
                'sort_order' => 10,
            ],
            [
                'section_key' => 'journey',
                'label' => 'Мой путь',
                'title_ru' => 'Как всё начиналось и как интерес к технологиям превратился в серьёзное направление',
                'title_en' => 'Как все начиналось и как любопытство к технологиям перешло в серьезное направление',
                'lead_ru' => 'От первого сайта для себя до продуктового мышления, собственных платформ и уверенного технического роста.',
                'lead_en' => 'От первого проекта, созданного для меня, до продуктового мышления, личных платформ и устойчивого технического роста.',
                'content_ru' => 'Это путь не про случайный интерес, а про постоянную практику, быстрое развитие и привычку доводить идеи до рабочего результата.',
                'content_en' => 'This is not a story about random curiosity, but about consistent practice, fast growth, and a habit of turning ideas into working results.',
                'sort_order' => 15,
                'extra_json' => [
                    'items' => [
                        [
                            'year_ru' => '10 лет',
                            'year_en' => 'Возраст 10 лет',
                            'title_ru' => 'Первый сайт для подсчёта оценок',
                            'title_en' => 'Мой первый сайт для отслеживания оценок',
                            'text_ru' => 'Начал с простой, но полезной для себя задачи: сделал сайт на HTML и CSS, чтобы считать оценки. Тогда и пришло главное понимание — код нужен не ради кода, а ради результата, который реально работает.',
                            'text_en' => 'I started with a simple but useful task for myself: I built an HTML/CSS website to track grades. That was the key moment when I understood that code matters because it creates a real result, not because it simply exists.',
                        ],
                        [
                            'year_ru' => 'Следующий этап',
                            'year_en' => 'Then',
                            'title_ru' => 'Первые программы, логика и автоматизация',
                            'title_en' => 'Первые программы, логика и автоматизация',
                            'text_ru' => 'После сайта быстро начались эксперименты с программами, интерфейсами, логикой и автоматизацией. Я всё чаще переходил от идеи сразу к прототипу и учился не просто писать код, а строить работающие решения.',
                            'text_en' => 'After the website came experiments with programs, interfaces, logic, and automation. I got used to moving quickly from an idea to a prototype and learned not just to write code, but to build working solutions.',
                        ],
                        [
                            'year_ru' => 'Рост глубины',
                            'year_en' => 'Следующий шаг',
                            'title_ru' => 'Сильный интерес к backend, Python, C++ и электронике',
                            'title_en' => 'Сильная тяга к серверной части, Python, C++ и электронике.',
                            'text_ru' => 'Постепенно основной фокус сместился в сторону backend, API, Python, C++, админ-панелей, Arduino и Raspberry Pi. Меня особенно зацепили направления, где важны архитектура, системность, производительность и инженерное мышление.',
                            'text_en' => 'Со временем мое основное внимание переместилось на серверную часть, API, Python, C++, панели администратора, Arduino и Raspberry Pi. Меня особенно привлекли направления, где важны архитектура, системное мышление, производительность и инженерное мышление.',
                        ],
                        [
                            'year_ru' => 'Продуктовый этап',
                            'year_en' => 'Later',
                            'title_ru' => 'Свои проекты, платформы и продуктовый подход',
                            'title_en' => 'Мои собственные проекты, платформы и продуктовое мышление',
                            'text_ru' => 'Дальше появились уже не просто эксперименты, а собственные проекты и платформы: Fitodomik, QuestGuideRF и другие направления. Здесь я начал мыслить шире — не только как разработчик, но и как человек, который собирает продукт целиком.',
                            'text_en' => 'Then came not just experiments, but my own projects and platforms: Fitodomik, QuestGuideRF, and other directions. This is where I started thinking more broadly — not only as a developer, but also as someone building a full product.',
                        ],
                        [
                            'year_ru' => 'Сейчас',
                            'year_en' => 'Now',
                            'title_ru' => 'Уверенный рост как разработчика и автора своих систем',
                            'title_en' => 'Стабильный рост как разработчика и создателя собственных систем',
                            'text_ru' => 'Сейчас мой путь уже выглядит как осознанное развитие сильного разработчика: я усиливаю свои сервисы, работаю с GitHub-портфолио, развиваю backend, интеграции, AI-направление и всё больше выстраиваю вокруг себя настоящий технологический фундамент.',
                            'text_en' => 'Сейчас путь уже выглядит как целенаправленный рост сильного разработчика: я продолжаю укреплять свои сервисы, расширять портфолио GitHub, углублять бэкенд-работу, интеграцию и направления, связанные с ИИ, одновременно выстраивая вокруг своей работы реальную технологическую основу.',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'services',
                'label' => 'Чем я могу быть полезен',
                'title_ru' => 'Что я могу сделать как разработчик',
                'title_en' => 'Чем я могу помочь как разработчик',
                'lead_ru' => 'Не просто написать код, а собрать понятный, рабочий и поддерживаемый продукт.',
                'lead_en' => 'Не просто писать код, а создавать понятный, работающий и поддерживаемый продукт.',
                'content_ru' => 'Эти направления напрямую связаны с тем, что я уже делаю в своих проектах и на GitHub.',
                'content_en' => 'Эти направления основаны непосредственно на том, что я уже использую в своих проектах и ​​на GitHub.',
                'sort_order' => 18,
                'extra_json' => [
                    'items' => [
                        [
                            'title_ru' => 'Создание сайтов и веб-платформ',
                            'title_en' => 'Веб-сайты и веб-платформы',
                            'text_ru' => 'Лендинги, персональные сайты, сервисные страницы и полноценные продуктовые интерфейсы с понятной структурой и админкой.',
                            'text_en' => 'Целевые страницы, персональные сайты, страницы услуг и полные интерфейсы продуктов с четкой структурой и поддержкой администратора.',
                        ],
                        [
                            'title_ru' => 'Backend-логика и база данных',
                            'title_en' => 'Бэкэнд-логика и базы данных',
                            'text_ru' => 'PHP, Python, MySQL, логика кабинетов, авторизация, формы, хранение данных, API и серверная архитектура.',
                            'text_en' => 'PHP, Python, MySQL, логика кабинета, аутентификация, формы, хранилище данных, API и серверная архитектура.',
                        ],
                        [
                            'title_ru' => 'Автоматизация и Telegram-боты',
                            'title_en' => 'Автоматизация и Telegram-боты',
                            'text_ru' => 'Боты, уведомления, автосообщения, обработка заявок, интеграции с внешними сервисами и рутинные сценарии без ручной работы.',
                            'text_en' => 'Боты, уведомления, автоматические сообщения, обработка потенциальных клиентов, внешние интеграции и рутинные процессы без ручной работы.',
                        ],
                        [
                            'title_ru' => 'Админ-панели и внутренние интерфейсы',
                            'title_en' => 'Панели администратора и внутренние инструменты',
                            'text_ru' => 'Системы управления контентом, проекты с редактированием блоков, новостей, контактов, SEO-полей и внутренней логикой продукта.',
                            'text_en' => 'Системы управления контентом и дашборды проектов для блоков, новостей, контактов, SEO-полей и внутренней логики продукта.',
                        ],
                        [
                            'title_ru' => 'Интеграции API и GitHub-связки',
                            'title_en' => 'Интеграция API и функции на базе GitHub',
                            'text_ru' => 'Подключение внешних API, живая статистика, работа с данными аккаунтов и автоматическое обновление информации без ручного редактирования.',
                            'text_en' => 'Интеграция внешних API, статистика в реальном времени, обработка данных учетной записи и автоматические обновления без ручного редактирования.',
                        ],
                        [
                            'title_ru' => 'Проекты с Arduino и Raspberry Pi',
                            'title_en' => 'Проекты Arduino и Raspberry Pi',
                            'text_ru' => 'Когда нужен не только сайт, но и связка с железом: датчики, логика, интерфейсы, автоматизация и инженерный подход.',
                            'text_en' => 'Когда для задачи нужно нечто большее, чем просто сайт: датчики, логика, интерфейсы, автоматизация и инженерный склад ума.',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'projects',
                'label' => 'Проекты',
                'title_ru' => 'Собственные продукты и платформы',
                'title_en' => 'Персональные продукты и платформы',
                'lead_ru' => 'Здесь собраны проекты, которые формируют мой портфель.',
                'lead_en' => 'Именно эти проекты составляют мое портфолио.',
                'content_ru' => 'В центре внимания — полезные сервисы, рабочие сайты и платформы, которые можно развивать как реальные цифровые продукты.',
                'content_en' => 'Основное внимание уделяется полезным сервисам, работающим веб-сайтам и платформам, которые могут развиваться как настоящие цифровые продукты.',
                'sort_order' => 20,
            ],
            [
                'section_key' => 'posts',
                'label' => 'Посты',
                'title_ru' => 'Что нового',
                'title_en' => 'Что нового',
                'lead_ru' => 'Обновления, заметки и этапы развития проекта.',
                'lead_en' => 'Обновления, заметки и снимки прогресса.',
                'content_ru' => 'Этот раздел наполняется через админ-панель и помогает держать сайт живым и регулярно обновляемым.',
                'content_en' => 'Этот раздел управляется из панели администратора и поддерживает активность и регулярное обновление сайта.',
                'sort_order' => 30,
            ],
            [
                'section_key' => 'techstack',
                'label' => 'Технологии и стек',
                'title_ru' => 'Технологии, с которыми я работаю',
                'title_en' => 'Технологии, с которыми я работаю',
                'lead_ru' => 'Стек по направлениям, чтобы было понятно, что я использую на практике.',
                'lead_en' => 'Мой стек по направлениям, поэтому понятно, что я собственно использую на практике.',
                'content_ru' => 'Это не просто список языков, а те инструменты, из которых я собираю реальные проекты.',
                'content_en' => 'Это не просто список языков, а инструментов, которые я использую для сборки реальных проектов.',
                'sort_order' => 35,
                'extra_json' => [
                    'groups' => [
                        [
                            'title_ru' => 'Backend',
                            'title_en' => 'Backend',
                            'text_ru' => 'Серверная логика, API, базы данных, кабинеты, формы, интеграции и рабочая архитектура продукта.',
                            'text_en' => 'Серверная логика, API, базы данных, информационные панели, формы, интеграция и архитектура продукта.',
                            'items' => ['Python', 'PHP', 'API', 'MySQL'],
                        ],
                        [
                            'title_ru' => 'Frontend',
                            'title_en' => 'Frontend',
                            'text_ru' => 'Интерфейсы, адаптивность, визуальная подача и клиентская логика для удобных современных сайтов.',
                            'text_en' => 'Интерфейсы, отзывчивость, визуальное представление и клиентская логика для современных удобных для пользователя сайтов.',
                            'items' => ['HTML', 'CSS', 'JavaScript', 'TypeScript'],
                        ],
                        [
                            'title_ru' => 'Tools',
                            'title_en' => 'Tools',
                            'text_ru' => 'Контроль версий, работа с кодовой базой, Linux-окружение, релизы и публичная GitHub-история.',
                            'text_en' => 'Контроль версий, рабочий процесс репозитория, среды Linux, выпуски и общедоступная история GitHub.',
                            'items' => ['Git', 'GitHub', 'Linux'],
                        ],
                        [
                            'title_ru' => 'Hardware',
                            'title_en' => 'Hardware',
                            'text_ru' => 'Проекты на стыке кода и железа, где важны скорость, логика и понимание того, как всё работает вживую.',
                            'text_en' => 'Проекты на стыке кода и оборудования, где важны скорость, логика и практическое понимание.',
                            'items' => ['Arduino', 'Малиновый Пи', 'C++'],
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'plans',
                'label' => 'Планы',
                'title_ru' => 'Чем занимаюсь сейчас и куда двигаюсь дальше',
                'title_en' => 'На чем я сосредоточен сейчас и куда направляюсь дальше',
                'lead_ru' => 'Сейчас мой фокус — не просто учиться, а набирать настоящую техническую глубину, усиливать свои проекты и превращать опыт в сильный личный уровень.',
                'lead_en' => 'Right now my focus is not just learning, but building real technical depth, strengthening my projects, and turning experience into a strong personal level.',
                'content_ru' => "Сейчас я развиваюсь в очень рабочем темпе и подхожу к этому уже не как к хобби, а как к серьёзному направлению.\n\n- Развиваю свои проекты и платформы так, чтобы они выглядели не как набор репозиториев, а как полноценные цифровые продукты с логикой, структурой, админ-панелями, контентом и понятным ростом.\n- Усиливаю backend-направление: архитектура, API, работа с MySQL, серверная логика, формы, кабинеты, интеграции и внутренняя организация системы.\n- Продолжаю наращивать практику в Python, PHP, JavaScript и C++, чтобы уверенно чувствовать себя и в вебе, и в более инженерных задачах, где важны скорость и контроль.\n- Развиваю направления автоматизации, Telegram-ботов, GitHub API, AI-инструментов и связок между сервисами — мне особенно интересны системы, которые умеют работать живо, полезно и без лишней ручной рутины.\n- Усиливаю публичную сторону своей работы: GitHub, сайт-портфолио, релизы, визуальную подачу проектов и общее позиционирование как разработчика, у которого есть не только код, но и реальные продукты.\n\nВ перспективе я хочу выйти на ещё более высокий уровень: делать сильные собственные платформы, углубляться в backend и инженерную часть, усиливать связку кода и железа, работать с AI и запускать проекты, которые можно развивать вдолгую. Мне близок спортивный подход к росту: дисциплина, темп, постоянная практика и движение вперёд без остановки.",
                'content_en' => "Right now I am growing at a very practical pace and I already treat this not as a hobby, but as a serious direction.\n\n- I keep developing my projects and platforms so they feel like full digital products rather than just a set of repositories, with logic, structure, admin panels, content, and visible growth.\n- I am strengthening my backend direction: architecture, APIs, MySQL, server-side logic, forms, dashboards, integrations, and the internal organization of a system.\n- I continue building confidence in Python, PHP, JavaScript, and C++ so I feel strong both in web development and in more engineering-oriented tasks where speed and control matter.\n- I am developing automation, Telegram bots, GitHub API integrations, AI tools, and connections between services — I am especially interested in systems that work in a live, useful, and low-friction way.\n- I am also strengthening the public side of my work: GitHub, the portfolio site, releases, project presentation, and my overall positioning as a developer with not only code, but real products behind it.\n\nLong term, I want to reach an even stronger level: build powerful personal platforms, go deeper into backend and engineering, strengthen the connection between code and hardware, work with AI, and launch projects that can keep growing for years. I relate to growth the same way I relate to sport: discipline, pace, constant practice, and steady forward movement.",
                'sort_order' => 50,
            ],
            [
                'section_key' => 'seo_profile',
                'label' => 'Позиционирование',
                'title_ru' => 'Домнин Вячеслав Игоревич — разработчик Fitodomik, QuestGuideRF и собственных цифровых проектов',
                'title_en' => 'Вячеслав Домнин — разработчик Фитодомик, QuestGuideRF и независимых цифровых продуктов',
                'lead_ru' => 'Этот блок усиливает публичное позиционирование: имя, никнейм, проекты, стек, GitHub-профиль legendaSVD и направления, по которым со мной можно связаться.',
                'lead_en' => 'Этот блок усиливает публичное позиционирование с помощью основного имени, ника, проектов, стека, профиля LegendaSVD на GitHub и направлений сотрудничества.',
                'content_ru' => $this->defaultSeoProfileContentRu(),
                'content_en' => $this->defaultSeoProfileContentEn(),
                'sort_order' => 55,
                'extra_json' => [
                    'cards' => $this->defaultSeoProfileCards(),
                ],
            ],
            [
                'section_key' => 'contacts',
                'label' => 'Контакты',
                'title_ru' => 'Связь, идеи, сотрудничество',
                'title_en' => 'Контакты, идеи и сотрудничество',
                'lead_ru' => 'Можно написать напрямую или отправить сообщение через форму.',
                'lead_en' => 'Вы можете связаться напрямую или отправить сообщение с помощью формы.',
                'content_ru' => 'Сообщения сохраняются в системе и при подключении Telegram-бота автоматически приходят в Telegram.',
                'content_en' => 'Сообщения хранятся в системе и могут быть перенаправлены в Telegram при настройке бота.',
                'sort_order' => 60,
            ],
        ];
    }
    private function defaultSeoGeneralSettings(): array
    {
        return [
            'site_title_ru' => 'Домнин Вячеслав Игоревич — разработчик Fitodomik, QuestGuideRF и цифровых проектов',
            'site_title_en' => 'Вячеслав Домнин — разработчик Фитодомик, QuestGuideRF и цифровых продуктов',
            'site_description_ru' => 'Домнин Вячеслав Игоревич — программист, fullstack и backend разработчик, создатель Fitodomik, Fitodomik Online, Fitodomik Project, Fitodomik.ru и QuestGuideRF. PHP, Python, C++, JavaScript, MySQL, API, Telegram-боты, Arduino, Raspberry Pi и GitHub legendaSVD.',
            'site_description_en' => 'Вячеслав Домнин — программист и создатель Фитодомик, Фитодомик Онлайн, Фитодомик Проект, Фитодомик.ру и QuestGuideRF. Fullstack, серверная часть, PHP, Python, C++, JavaScript, MySQL, API, боты Telegram, Arduino, Raspberry Pi и GitHub LegendaSVD.',
            'llms_summary_ru' => 'Домнин Вячеслав Игоревич — программист, backend и fullstack разработчик, создатель проектов Fitodomik, Fitodomik Online, Fitodomik Project, Fitodomik.ru и QuestGuideRF. Сайт содержит личный профиль, стек технологий, GitHub legendaSVD, проекты, посты, контакты и машинно-читаемые описания для ИИ.',
            'llms_summary_en' => 'Вячеслав Домнин — программист, backend/fullstack разработчик, создатель Fitodomik, Fitodomik Online, Fitodomik Project, Fitodomik.ru и QuestGuideRF. Сайт включает в себя личный профиль, технический стек, учетную запись LegendaSVD на GitHub, проекты, публикации, контакты и описания, читаемые искусственным интеллектом.',
        ];
    }
    private function defaultHomeSeoSettings(): array
    {
        return [
            'seo_home_title_ru' => 'Домнин Вячеслав Игоревич — разработчик Fitodomik, QuestGuideRF | Python, C++, JavaScript, PHP',
            'seo_home_title_en' => 'Вячеслав Домнин — разработчик Фитодомика и QuestGuideRF | Питон, С++, JavaScript, PHP',
            'seo_home_description_ru' => 'Домнин Вячеслав Игоревич — программист и разработчик собственных проектов Fitodomik, Fitodomik Online, Fitodomik Project, Fitodomik.ru и QuestGuideRF. Fullstack, backend, Python, C++, JavaScript, PHP, MySQL, API, Telegram-боты, Arduino, Raspberry Pi, GitHub legendaSVD.',
            'seo_home_description_en' => 'Вячеслав Домнин — разработчик Фитодомик, Фитодомик Онлайн, Фитодомик Проект, Фитодомик.ру и QuestGuideRF. Fullstack, серверная часть, Python, C++, JavaScript, PHP, MySQL, API, боты Telegram, Arduino, Raspberry Pi и GitHub LegendaSVD.',
            'seo_home_keywords_ru' => 'Домнин Вячеслав Игоревич, Вячеслав Домнин, Домнин Вячеслав, legendaSVD, GitHub legendaSVD, программист Вячеслав Домнин, разработчик Fitodomik, создатель Fitodomik, Fitodomik, Fitodomik Online, Fitodomik Project, Fitodomik.ru, разработчик QuestGuideRF, QuestGuideRF, fullstack разработчик, backend разработчик, Python разработчик, C++ разработчик, JavaScript разработчик, PHP разработчик, MySQL разработчик, API интеграции, Telegram-боты, Arduino, Raspberry Pi, разработка сайтов, разработка веб-платформ, создание цифровых продуктов, админ-панели, автоматизация, GitHub портфолио',
            'seo_home_keywords_en' => 'Вячеслав Домнин, Вячеслав Игоревич Домнин, LegendaSVD, LegendaSVD GitHub, Разработчик Фитодомик, Фитодомик Онлайн, Проект Фитодомик, Fitodomik.ru, Разработчик QuestGuideRF, Fullstack разработчик, Backend-разработчик, PHP-разработчик, Python-разработчик, C++-разработчик, JavaScript-разработчик, MySQL-разработчик, API-интеграция, Разработчик Telegram-бота, Программист Arduino, Программист Raspberry Pi, портфолио разработчиков, создатель цифровых продуктов',
        ];
    }
    private function defaultSeoProfileContentRu(): string
    {
        return <<<TEXT
Домнин Вячеслав Игоревич — программист, fullstack и backend разработчик, который создаёт собственные цифровые продукты, веб-сервисы, интерфейсы, админ-панели, автоматизации и проекты на стыке кода и электроники. Основные направления моей работы — PHP, Python, C++, JavaScript, TypeScript, MySQL, API, Telegram-боты, Arduino, Raspberry Pi, backend-логика, базы данных и развитие собственных IT-платформ.
Я являюсь создателем и разработчиком проектов Fitodomik, Fitodomik Online, Fitodomik Project, Fitodomik.ru и QuestGuideRF. Для меня это не просто названия репозиториев на GitHub, а реальные продуктовые направления, в которых я продумываю архитектуру, backend-логику, структуру базы данных, административные панели, интерфейсы и дальнейшее развитие платформ.
Этот сайт работает как личное портфолио, персональная визитка и центральная точка для всех моих проектов. Здесь собраны информация обо мне, реальные направления разработки, стек технологий, публичный GitHub-профиль legendaSVD, ключевые проекты, свежие обновления и контакты для сотрудничества.
Через этот сайт меня можно находить по имени Домнин Вячеслав Игоревич, по никнейму ༺Leͥgeͣnͫda༻ᴳᵒᵈ, по GitHub-аккаунту legendaSVD, а также по проектам Fitodomik и QuestGuideRF. Мне близка разработка сайтов, веб-платформ, backend-сервисов, API-интеграций, Telegram-ботов, админ-панелей, автоматизаций и инженерных решений с Arduino и Raspberry Pi.
TEXT;
    }
    private function defaultSeoProfileContentEn(): string
    {
        return <<<TEXT
Vyacheslav Domnin is a programmer, fullstack developer, and backend-focused builder who creates independent digital products, web services, interfaces, admin panels, automation systems, and projects at the intersection of code and electronics. The main directions include PHP, Python, C++, JavaScript, TypeScript, MySQL, APIs, Telegram bots, Arduino, Raspberry Pi, backend logic, databases, and product platform development.
I am the creator and developer of Fitodomik, Fitodomik Online, Fitodomik Project, Fitodomik.ru, and QuestGuideRF. For me these are not just repository names on GitHub, but real product directions where I shape architecture, backend logic, database structure, administrative interfaces, and the long-term growth of each platform.
This website works as a personal portfolio, online business card, and central profile hub for all of my projects. It brings together the biography, technology stack, public legendaSVD GitHub profile, project list, updates, and contact points for collaboration.
The site should be discoverable by the full name Vyacheslav Domnin, the nickname ༺Leͥgeͣnͫda༻ᴳᵒᵈ, the legendaSVD GitHub account, and the project names Fitodomik and QuestGuideRF. I focus on websites, web platforms, backend services, API integrations, Telegram bots, admin panels, automation, and engineering directions with Arduino and Raspberry Pi.
TEXT;
    }
    private function defaultSeoProfileCards(): array
    {
        return [
            [
                'title_ru' => 'Ключевые проекты',
                'title_en' => 'Основные проекты',
                'text_ru' => 'Fitodomik, Fitodomik Online, Fitodomik Project, Fitodomik.ru и QuestGuideRF — основные публичные направления, по которым меня уже можно узнавать.',
                'text_en' => 'Фитодомик, Фитодомик Онлайн, Фитодомик Проект, Фитодомик.ру и QuestGuideRF — основные публичные направления, уже связанные с моим профилем.',
            ],
            [
                'title_ru' => 'Стек и бэкэнд',
                'title_en' => 'Стек и бэкэнд',
                'text_ru' => 'PHP, Python, C++, JavaScript, TypeScript, MySQL, API, Telegram-боты, админ-панели, базы данных и продуктовая логика.',
                'text_en' => 'PHP, Python, C++, JavaScript, TypeScript, MySQL, API, боты Telegram, панели администратора, базы данных и логика продукта.',
            ],
            [
                'title_ru' => 'GitHub и живая разработка',
                'title_en' => 'GitHub и живая разработка',
                'text_ru' => 'Профиль legendaSVD показывает реальную активность, публичные репозитории, языки, архитектурные эксперименты и рост моих собственных платформ.',
                'text_en' => 'Профиль LegendaSVD на GitHub показывает реальную активность, публичные репозитории, распространение языков, эксперименты с архитектурой и рост моих собственных платформ.',
            ],
            [
                'title_ru' => 'Чем могу быть полезен',
                'title_en' => 'Как я могу помочь',
                'text_ru' => 'Разработка сайтов и веб-платформ, backend-логика, автоматизация, Telegram-боты, API-интеграции, админ-панели и проекты с Arduino / Raspberry Pi.',
                'text_en' => 'Веб-сайты и платформы, серверная логика, автоматизация, боты Telegram, интеграция API, панели администратора и проекты Arduino/Raspberry Pi.',
            ],
        ];
    }
    private function defaultAboutContentRu(): string
    {
        return <<<TEXT
Меня зовут Вячеслав Домнин. Для меня разработка — это не про «написать что-нибудь на языке X», а про собрать рабочую вещь целиком: придумать идею, продумать структуру, написать логику, запустить и потом ещё довести до нормального состояния.
Первые программы я начал делать ещё в 10 лет. Одной из самых ранних вещей был сайт для подсчёта моих оценок на HTML и CSS. С этого момента мне стало по-настоящему интересно программирование: я увидел, что код может быть не абстракцией, а инструментом, который реально решает задачи.
Сейчас мне ближе всего backend, fullstack, Python, C++, JavaScript и всё, что связано с автоматизацией, API, админ-панелями и собственными сервисами. Отдельно нравится направление электроники, потому что там особенно хорошо чувствуется логика системы и связь между кодом и реальным результатом.
Мне важно делать не учебные заготовки, а проекты, которые можно открыть, запустить, показать людям и развивать дальше. Именно поэтому для меня так важны Fitodomik, QuestGuideRF и другие мои направления: это не «репозитории ради количества», а вещи, которые я действительно хочу доводить до сильного уровня.
Я люблю разбираться в новых технологиях, но ещё больше люблю доводить начатое до конца. Если задача сложная, мне как раз интересно в неё влезть, понять, где слабое место, и сделать решение лучше — чище, понятнее и полезнее.
TEXT;
    }
    private function defaultAboutContentEn(): string
    {
        return <<<TEXT
My name is Vyacheslav Domnin. For me, development is not about “writing something in language X”, but about building a working thing from end to end: the idea, the structure, the logic, the launch, and the follow-up improvements.
I started making my first programs at the age of 10. One of the earliest things was a website for tracking my grades with HTML and CSS. That was the moment I understood that code is not an abstraction for me — it is a tool that can solve real problems.
Today I am most drawn to backend, fullstack work, Python, C++, JavaScript, and everything around automation, APIs, admin panels, and personal services. Electronics also interests me a lot because it makes the connection between code and real-world results especially tangible.
I want to build things that are not just demos, but projects you can open, launch, show to people, and keep growing. That is why Fitodomik, QuestGuideRF, and my other directions matter so much to me: they are not repositories for the sake of numbers, but products I genuinely want to push to a strong level.
I enjoy learning new technologies, but I care even more about finishing what I start. When a task is difficult, that usually makes it more interesting for me: to understand the weak spot and turn the solution into something cleaner, clearer, and more useful.
TEXT;
    }
    private function defaultBiographyRu(): string
    {
        return <<<TEXT
Я активный человек, которому важно движение, развитие и ощущение, что жизнь не стоит на месте. Мне нравится пробовать новое, придумывать идеи, влезать в интересные темы и не жить по одному и тому же сценарию. Наверное, поэтому мне близки и технологии, и спорт, и всё, где есть энергия, характер и живой интерес к процессу.
В моей жизни большое место занимает активность. Мне близки велоспорт, волейбол, хоккей, бокс и плавание. Особенно люблю велосипед: мне интересно не только кататься, но и разбираться в устройстве, что-то чинить, настраивать и понимать, как всё работает внутри. Мне вообще нравится практический подход — не просто пользоваться вещью, а чувствовать её глубже.
Меня вдохновляют люди, музыка, природа, спорт и новые идеи. Мне интересно наблюдать за тем, как люди думают, как принимают решения, как ведут себя в разных ситуациях. Я ценю искренность, доверие, нормальное человеческое отношение и людей, с которыми можно быть собой без лишнего напряжения.
По характеру я живой, инициативный и с чувством юмора. Люблю общение, люблю атмосферу, люблю, когда рядом есть люди, с которыми можно что-то придумать, обсудить или даже запустить вместе. При этом мне нравится и спокойствие: моменты, когда можно выдохнуть, собраться с мыслями и идти дальше уже в своём темпе.
Одна из моих личных мечт — стать гонщиком. Меня привлекают скорость, концентрация, техника и то внутреннее состояние, когда ты полностью собран и идёшь вперёд. Наверное, это хорошо совпадает и с тем, какой я сам: мне близки движение, вызов, интерес к жизни и желание не останавливаться на месте.
TEXT;
    }
    private function defaultBiographyEn(): string
    {
        return <<<TEXT
I am an active person who needs movement, growth, and the feeling that life is moving forward. I like trying new things, coming up with ideas, diving into interesting topics, and not living by the same script every day. That is probably why both technology and the energy of sport feel natural to me.
Activity takes a big place in my life. I enjoy cycling, volleyball, hockey, boxing, and swimming. Cycling matters to me in a special way: I like not only riding, but also understanding the mechanics, fixing things, tuning them, and learning how everything works from the inside.
People, music, nature, sport, and new ideas inspire me. I am genuinely interested in how people think, how they make decisions, and how they behave in different situations. I value sincerity, trust, simple human respect, and the kind of connection where you can be yourself without tension.
By character I am energetic, initiative-driven, and easy to talk to. I like communication, atmosphere, and the feeling that there are people around you with whom you can discuss something, invent something, or even start building together. At the same time, I also value calm moments when I can slow down, think clearly, and move further in my own rhythm.
One of my personal dreams is to become a racing driver. I am drawn to speed, concentration, technique, and that specific mindset where you stay fully focused and keep moving forward. In many ways, it reflects my own character: movement, challenge, curiosity, and the desire not to stand still.
TEXT;
    }
}