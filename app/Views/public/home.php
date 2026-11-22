<?php
$nav = [
    'ru' => [
        ['hero', 'Главная'],
        ['about', 'Обо мне'],
        ['journey', 'Мой путь'],
        ['projects', 'Проекты'],
        ['services', 'Чем полезен'],
        ['github', 'GitHub'],
        ['stack', 'Стек'],
        ['posts', 'Посты'],
        ['plans', 'Планы'],
        ['contacts', 'Контакты'],
    ],
    'en' => [
        ['hero', 'Home'],
        ['about', 'About'],
        ['journey', 'Journey'],
        ['projects', 'Projects'],
        ['services', 'Services'],
        ['github', 'GitHub'],
        ['stack', 'Stack'],
        ['posts', 'Posts'],
        ['plans', 'Plans'],
        ['contacts', 'Contact'],
    ],
];
$labels = [
    'ru' => [
        'live' => 'Онлайн',
        'repo' => 'GitHub',
        'projects' => 'Смотреть проекты',
        'contact' => 'Связаться',
        'allPosts' => 'Все обновления в админке',
        'send' => 'Отправить сообщение',
        'theme' => 'Тема',
        'language' => 'EN',
        'followers' => 'подписчиков',
        'repos' => 'репозиториев',
        'stars' => 'звёзд',
        'active' => 'активных направлений',
    ],
    'en' => [
        'live' => 'Live',
        'repo' => 'GitHub',
        'projects' => 'Посмотреть проекты',
        'contact' => 'Свяжитесь со мной',
        'allPosts' => 'Больше обновлений в админке',
        'send' => 'Отправить сообщение',
        'theme' => 'Theme',
        'language' => 'RU',
        'followers' => 'followers',
        'repos' => 'repositories',
        'stars' => 'stars',
        'active' => 'активные направления',
    ],
];
$copy = $labels[$lang];
$about = $blocks['about'] ?? [];
$journeyBlock = $blocks['journey'] ?? [];
$projectsBlock = $blocks['projects'] ?? [];
$seoBlock = $blocks['seo_profile'] ?? [];
$servicesBlock = $blocks['services'] ?? [];
$postsBlock = $blocks['posts'] ?? [];
$stackBlock = $blocks['techstack'] ?? [];
$plansBlock = $blocks['plans'] ?? [];
$contactsBlock = $blocks['contacts'] ?? [];
$roles = array_filter(array_map('trim', explode(',', (string) ($lang === 'ru' ? ($settings['card_roles_ru'] ?? '') : ($settings['card_roles_en'] ?? '')))));
$fullName = $lang === 'ru' ? ($settings['person_full_name'] ?? '') : ($settings['person_full_name_en'] ?? '');
$heroTagline = $lang === 'ru' ? ($settings['hero_tagline_ru'] ?? '') : ($settings['hero_tagline_en'] ?? '');
$heroIntro = $lang === 'ru' ? ($settings['hero_intro_ru'] ?? '') : ($settings['hero_intro_en'] ?? '');
$biography = $lang === 'ru' ? ($settings['person_biography_ru'] ?? '') : ($settings['person_biography_en'] ?? '');
$contactIntro = trans_value($contactsBlock, 'content', $lang);
$journeyItems = block_extra_items($journeyBlock, $lang, 'items');
$seoCards = block_extra_items($seoBlock, $lang, 'cards');
$serviceItems = block_extra_items($servicesBlock, $lang, 'items');
$stackGroups = block_extra_items($stackBlock, $lang, 'groups');
$birthDate = format_date($settings['person_birthdate'] ?? '', $lang);
$stats = $github['stats'] ?? [];
$profile = $github['profile'] ?? [];
$githubLogin = $profile['login'] ?? ($settings['github_username'] ?? 'QuestGuideRF');
$githubSignature = 'Код | Электроника | Полный стек | Питон/С++ | Ардуино и малина Пи';
$githubDisplayName = (string) ($settings['person_nickname'] ?? '') !== '' ? (string) ($settings['person_nickname'] ?? '') : $fullName;
$avatar = !empty($settings['avatar_path'])
    ? avatar_sources($settings['avatar_path'], 84, 168)
    : [
        'src' => $profile['avatar_url'] ?? asset('img/placeholder.svg'),
        'srcset' => '',
    ];
$seoExternalLinks = [
    [
        'label' => 'Легенда GitHubSVD',
        'url' => 'https://github.com/QuestGuideRF',
    ],
];
foreach ($projects as $project) {
    if (empty($project['live_url'])) {
        continue;
    }
    $seoExternalLinks[] = [
        'label' => (string) trans_value($project, 'title', $lang),
        'url' => (string) $project['live_url'],
    ];
}
?>
<header class="site-header">
    <div class="site-header__inner">
        <a class="brand" href="<?= $lang === 'ru' ? '/' : '/en' ?>">
            <span class="brand-mark"></span>
            <span class="brand-copy">
                <strong><?= h($fullName) ?></strong>
                <span><?= h($settings['person_nickname'] ?? '') ?></span>
            </span>
        </a>
        <nav class="top-nav">
            <?php foreach ($nav[$lang] as [$anchor, $text]): ?>
                <a href="#<?= h($anchor) ?>" data-nav-link><?= h($text) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="header-actions">
            <button class="theme-toggle" type="button" aria-label="<?= h($copy['theme']) ?>">
                <span></span>
            </button>
            <a class="lang-switch" href="<?= h(active_locale_path($lang === 'ru' ? 'en' : 'ru')) ?>"><?= h($copy['language']) ?></a>
        </div>
    </div>
    <div class="scroll-progress"><span></span></div>
</header>
<main class="page page-home">
    <section class="hero" id="hero">
        <div class="hero__inner">
            <div class="hero-copy reveal is-visible" data-delay="0">
                <div class="section-kicker"><?= $lang === 'ru' ? 'Личный сайт разработчика' : 'Платформа портфолио разработчиков' ?></div>
                <h1><?= h($fullName) ?></h1>
                <div class="hero-nickname"><?= h($settings['person_nickname'] ?? '') ?></div>
                <p class="hero-tagline"><?= h($heroTagline) ?></p>
                <p class="hero-intro"><?= h($heroIntro) ?></p>
                <div class="hero-actions">
                    <a class="button button-primary magnetic" href="#projects"><?= h($copy['projects']) ?></a>
                    <a class="button button-secondary magnetic" href="#contacts"><?= h($copy['contact']) ?></a>
                </div>
                <div class="stack-marquee">
                    <div class="stack-marquee__track">
                        <span>Code</span><span>Python</span><span>C++</span><span>JavaScript</span><span>Electronics</span><span>Fullstack</span><span>Arduino</span><span>Raspberry Pi</span>
                        <span>Code</span><span>Python</span><span>C++</span><span>JavaScript</span><span>Electronics</span><span>Fullstack</span><span>Arduino</span><span>Raspberry Pi</span>
                    </div>
                </div>
            </div>
            <div class="hero-side">
                <div class="profile-card reveal" data-delay="120">
                    <div class="profile-card__header">
                        <div class="avatar-wrap">
                            <?php if (!empty($avatar['src'])): ?>
                                <img
                                    src="<?= h($avatar['src']) ?>"
                                    <?php if (!empty($avatar['srcset'])): ?>srcset="<?= h($avatar['srcset']) ?>"<?php endif; ?>
                                    sizes="(max-width: 420px) 72px, 84px"
                                    width="84"
                                    height="84"
                                    decoding="async"
                                    alt="<?= h($fullName) ?>"
                                >
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <span><?= h(mb_substr($fullName, 0, 1, 'UTF-8')) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <strong><?= h($fullName) ?></strong>
                            <span><?= h($settings['person_nickname'] ?? '') ?></span>
                        </div>
                    </div>
                    <div class="profile-card__grid">
                        <div><span><?= $lang === 'ru' ? 'Дата рождения' : 'Birthdate' ?></span><strong><?= h($birthDate) ?></strong></div>
                        <div><span><?= $lang === 'ru' ? 'Статус' : 'Status' ?></span><strong><?= h($lang === 'ru' ? ($settings['card_status_ru'] ?? '') : ($settings['card_status_en'] ?? '')) ?></strong></div>
                        <div><span><?= $lang === 'ru' ? 'Формат' : 'Mode' ?></span><strong><?= h($lang === 'ru' ? ($settings['card_location_ru'] ?? '') : ($settings['card_location_en'] ?? '')) ?></strong></div>
                        <div><span>GitHub</span><strong><?= h($settings['github_username'] ?? 'QuestGuideRF') ?></strong></div>
                    </div>
                    <div class="role-cloud">
                        <?php foreach ($roles as $role): ?>
                            <span><?= h($role) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="quick-links">
                        <a href="https://github.com/QuestGuideRF" target="_blank" rel="noreferrer">GitHub</a>
                        <a href="https://t.me/FitoDomik" target="_blank" rel="noreferrer">Telegram</a>
                        <a href="#projects"><?= $lang === 'ru' ? 'Проекты' : 'Projects' ?></a>
                        <a href="#contacts"><?= $lang === 'ru' ? 'Связаться' : 'Contact' ?></a>
                    </div>
                </div>
                <div class="code-window reveal is-visible" data-delay="220">
                    <div class="code-window__bar">
                        <span></span><span></span><span></span>
                    </div>
                    <pre id="heroCode"><code>
const builder = {
  name: "<?= h($fullName) ?>",
  focus: ["PHP", "JS", "Python", "C++"],
  mission: "отгружать настоящие продукты",
  github: "<?= h($settings['github_username'] ?? 'QuestGuideRF') ?>"
};</code></pre>
                </div>
            </div>
        </div>
    </section>
    <section class="section section-about" id="about">
        <div class="section-head reveal">
            <div class="section-kicker"><?= h($about['label'] ?? ($lang === 'ru' ? 'Обо мне' : 'About')) ?></div>
            <h2><?= h((string) trans_value($about, 'title', $lang)) ?></h2>
            <p><?= h((string) trans_value($about, 'lead', $lang)) ?></p>
        </div>
        <div class="about-grid">
            <article class="glass-card reveal">
                <div class="rich-text"><?= markdown_to_html((string) trans_value($about, 'content', $lang)) ?></div>
            </article>
            <aside class="focus-panel reveal tilt-card" data-delay="120">
                <div class="focus-panel__label"><?= $lang === 'ru' ? 'Направления' : 'Направления деятельности' ?></div>
                <div class="focus-list">
                    <div><strong><?= $lang === 'ru' ? 'Код' : 'Code' ?></strong><span><?= $lang === 'ru' ? 'Backend, продуктовая логика и архитектура систем.' : 'Бэкэнд, логика продукта и архитектура.' ?></span></div>
                    <div><strong><?= $lang === 'ru' ? 'Электроника' : 'Electronics' ?></strong><span><?= $lang === 'ru' ? 'Arduino, Raspberry Pi и интерфейсы, связанные с железом.' : 'Arduino, Raspberry Pi и аппаратные интерфейсы.' ?></span></div>
                    <div><strong>Fullstack</strong><span><?= $lang === 'ru' ? 'PHP, JS, MySQL, интерфейсы и собственные админ-панели.' : 'PHP, JS, MySQL, системы интерфейса и пользовательские панели администратора.' ?></span></div>
                    <div><strong><?= $lang === 'ru' ? 'Подход' : 'Mindset' ?></strong><span><?= $lang === 'ru' ? 'Создавать собственные проекты, а не только писать код.' : 'Создавайте реальные продукты, а не только фрагменты кода.' ?></span></div>
                </div>
            </aside>
        </div>
        <?php if (trim((string) $biography) !== ''): ?>
            <article class="biography-card glass-card reveal" data-delay="160">
                <div class="section-kicker"><?= $lang === 'ru' ? 'Биография' : 'Biography' ?></div>
                <div class="rich-text"><?= markdown_to_html((string) $biography) ?></div>
            </article>
        <?php endif; ?>
    </section>
    <?php if ($journeyBlock !== []): ?>
        <section class="section section-journey" id="journey">
            <div class="section-head reveal">
                <div class="section-kicker"><?= h($journeyBlock['label'] ?? ($lang === 'ru' ? 'Мой путь' : 'Journey')) ?></div>
                <h2><?= h((string) trans_value($journeyBlock, 'title', $lang)) ?></h2>
                <p><?= h((string) trans_value($journeyBlock, 'lead', $lang)) ?></p>
            </div>
            <div class="journey-timeline">
                <?php foreach ($journeyItems as $index => $item): ?>
                    <article class="timeline-card reveal" data-delay="<?= 60 * $index ?>">
                        <span class="timeline-card__eyebrow"><?= h((string) ($item['year'] ?? '')) ?></span>
                        <h3><?= h((string) ($item['title'] ?? '')) ?></h3>
                        <p><?= h((string) ($item['text'] ?? '')) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
    <section class="section section-projects" id="projects">
        <div class="section-head reveal">
            <div class="section-kicker"><?= h($projectsBlock['label'] ?? ($lang === 'ru' ? 'Проекты' : 'Projects')) ?></div>
            <h2><?= h((string) trans_value($projectsBlock, 'title', $lang)) ?></h2>
            <p><?= h((string) trans_value($projectsBlock, 'content', $lang)) ?></p>
        </div>
        <div class="project-grid">
            <?php foreach ($projects as $index => $project): ?>
                <article class="project-card reveal tilt-card" data-delay="<?= 60 * $index ?>">
                    <div class="project-card__top">
                        <div class="project-orbit"></div>
                        <span class="project-status"><?= h((string) trans_value($project, 'status', $lang)) ?></span>
                        <h3><?= h((string) trans_value($project, 'title', $lang)) ?></h3>
                        <p><?= h((string) trans_value($project, 'excerpt', $lang)) ?></p>
                    </div>
                    <div class="project-stack">
                        <?php foreach (array_filter(array_map('trim', explode(',', (string) ($project['stack_text'] ?? '')))) as $stack): ?>
                            <span><?= h($stack) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="project-links">
                        <a href="<?= h($lang === 'ru' ? '/projects/' . $project['slug'] : '/en/projects/' . $project['slug']) ?>"><?= $lang === 'ru' ? 'Подробнее' : 'Details' ?></a>
                        <?php if (!empty($project['live_url'])): ?><a href="<?= h($project['live_url']) ?>" target="_blank" rel="noreferrer"><?= h($copy['live']) ?></a><?php endif; ?>
                        <?php if (!empty($project['repo_url'])): ?><a href="<?= h($project['repo_url']) ?>" target="_blank" rel="noreferrer"><?= h($copy['repo']) ?></a><?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php if ($seoBlock !== []): ?>
        <section class="section section-seo" id="search-profile">
            <div class="section-head reveal">
                <div class="section-kicker"><?= h($seoBlock['label'] ?? ($lang === 'ru' ? 'Позиционирование' : 'Positioning')) ?></div>
                <h2><?= h((string) trans_value($seoBlock, 'title', $lang)) ?></h2>
                <p><?= h((string) trans_value($seoBlock, 'lead', $lang)) ?></p>
            </div>
            <div class="seo-layout">
                <article class="seo-copy glass-card reveal">
                    <div class="rich-text"><?= markdown_to_html((string) trans_value($seoBlock, 'content', $lang)) ?></div>
                    <div class="seo-links">
                        <?php foreach ($seoExternalLinks as $link): ?>
                            <a href="<?= h($link['url']) ?>" target="_blank" rel="noreferrer"><?= h($link['label']) ?></a>
                        <?php endforeach; ?>
                    </div>
                </article>
                <div class="seo-cards">
                    <?php foreach ($seoCards as $index => $item): ?>
                        <article class="seo-card reveal" data-delay="<?= 55 * $index ?>">
                            <h3><?= h((string) ($item['title'] ?? '')) ?></h3>
                            <p><?= h((string) ($item['text'] ?? '')) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
    <?php if ($servicesBlock !== []): ?>
        <section class="section section-services" id="services">
            <div class="section-head reveal">
                <div class="section-kicker"><?= h($servicesBlock['label'] ?? ($lang === 'ru' ? 'Чем я могу быть полезен' : 'Services')) ?></div>
                <h2><?= h((string) trans_value($servicesBlock, 'title', $lang)) ?></h2>
                <p><?= h((string) trans_value($servicesBlock, 'lead', $lang)) ?></p>
            </div>
            <div class="service-grid">
                <?php foreach ($serviceItems as $index => $item): ?>
                    <article class="service-card reveal" data-delay="<?= 55 * $index ?>">
                        <h3><?= h((string) ($item['title'] ?? '')) ?></h3>
                        <p><?= h((string) ($item['text'] ?? '')) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
    <section class="section section-github" id="github">
        <div class="section-head reveal">
            <div class="section-kicker">GitHub / API / Live</div>
            <h2><?= $lang === 'ru' ? 'Живая статистика аккаунта и репозиториев' : 'Статистика реального аккаунта и репозитория' ?></h2>
            <p><?= $lang === 'ru' ? 'Раздел собирается по полному сканированию публичных owned-репозиториев аккаунта через GitHub API и показывает чистую сводку без лишних виджетов.' : 'This section is built from a full scan of public owned repositories via the GitHub API and keeps the presentation intentionally minimal.' ?></p>
        </div>
        <div class="github-overview github-overview--minimal">
            <div class="github-main reveal">
                <div class="github-identity">
                    <div class="github-profile__meta">
                        <strong><?= h($githubDisplayName) ?></strong>
                        <span>@<?= h($githubLogin) ?></span>
                    </div>
                    <p class="github-identity__stack"><?= h($githubSignature) ?></p>
                </div>
                <div class="github-stats">
                    <div class="stat-card"><span><?= h($copy['repos']) ?></span><strong data-counter><?= h((string) ($stats['public_repos'] ?? 0)) ?></strong></div>
                    <div class="stat-card"><span><?= h($copy['stars']) ?></span><strong data-counter><?= h((string) ($stats['stars'] ?? 0)) ?></strong></div>
                    <div class="stat-card"><span><?= h($copy['followers']) ?></span><strong data-counter><?= h((string) ($stats['followers'] ?? 0)) ?></strong></div>
                    <div class="stat-card"><span><?= h($copy['active']) ?></span><strong data-counter><?= h((string) ($stats['active_projects'] ?? 0)) ?></strong></div>
                </div>
            </div>
            <div class="github-languages reveal" data-delay="80">
                <h3><?= $lang === 'ru' ? 'Языки по GitHub API' : 'Языки из GitHub API' ?></h3>
                <?php if (!empty($github['languages'])): ?>
                    <?php foreach (($github['languages'] ?? []) as $language): ?>
                        <div class="language-bar">
                            <div class="language-bar__head"><span><?= h($language['name']) ?></span><strong><?= h((string) $language['percent']) ?>%</strong></div>
                            <div class="language-bar__track"><span style="width: <?= h((string) $language['percent']) ?>%"></span></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="github-empty"><?= $lang === 'ru' ? 'GitHub пока не вернул языки для отображения.' : 'GitHub еще не вернул данные о языке.' ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php if ($stackBlock !== []): ?>
        <section class="section section-stack" id="stack">
            <div class="section-head reveal">
                <div class="section-kicker"><?= h($stackBlock['label'] ?? ($lang === 'ru' ? 'Технологии и стек' : 'Stack')) ?></div>
                <h2><?= h((string) trans_value($stackBlock, 'title', $lang)) ?></h2>
                <p><?= h((string) trans_value($stackBlock, 'lead', $lang)) ?></p>
            </div>
            <div class="stack-grid">
                <?php foreach ($stackGroups as $index => $group): ?>
                    <article class="stack-card reveal" data-delay="<?= 60 * $index ?>">
                        <div class="stack-card__head">
                            <h3><?= h((string) ($group['title'] ?? '')) ?></h3>
                            <p><?= h((string) ($group['text'] ?? '')) ?></p>
                        </div>
                        <div class="stack-card__chips">
                            <?php foreach (($group['items'] ?? []) as $item): ?>
                                <span><?= h((string) $item) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
    <section class="section section-posts" id="posts">
        <div class="section-head reveal">
            <div class="section-kicker"><?= h($postsBlock['label'] ?? ($lang === 'ru' ? 'Посты' : 'Posts')) ?></div>
            <h2><?= h((string) trans_value($postsBlock, 'title', $lang)) ?></h2>
            <p><?= h((string) trans_value($postsBlock, 'content', $lang)) ?></p>
        </div>
        <div class="posts-grid">
            <?php foreach ($posts as $index => $post): ?>
                <article class="post-card reveal" data-delay="<?= 70 * $index ?>">
                    <div class="post-card__meta">
                        <span><?= h((string) trans_value($post, 'category', $lang)) ?></span>
                        <strong><?= h(format_date($post['published_at'] ?? '', $lang)) ?></strong>
                    </div>
                    <h3><?= h((string) trans_value($post, 'title', $lang)) ?></h3>
                    <p><?= h((string) trans_value($post, 'excerpt', $lang)) ?></p>
                    <a href="<?= h($lang === 'ru' ? '/posts/' . $post['slug'] : '/en/posts/' . $post['slug']) ?>"><?= $lang === 'ru' ? 'Читать' : 'Read' ?></a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="section section-plans" id="plans">
        <div class="section-head reveal">
            <div class="section-kicker"><?= h($plansBlock['label'] ?? ($lang === 'ru' ? 'Планы' : 'Plans')) ?></div>
            <h2><?= h((string) trans_value($plansBlock, 'title', $lang)) ?></h2>
            <p><?= h((string) trans_value($plansBlock, 'lead', $lang)) ?></p>
        </div>
        <div class="plans-layout">
            <article class="glass-card reveal">
                <div class="rich-text"><?= markdown_to_html((string) trans_value($plansBlock, 'content', $lang)) ?></div>
            </article>
            <div class="trajectory reveal" data-delay="120">
                <div class="trajectory-step"><span>01</span><strong><?= $lang === 'ru' ? 'Сильные собственные продукты' : 'Более сильные личные продукты' ?></strong><p><?= $lang === 'ru' ? 'Делать проекты так, чтобы они выглядели как полноценные платформы с ростом, логикой и своим лицом.' : 'Создавайте проекты как полноценные платформы с ростом, структурой и четкой идентичностью.' ?></p></div>
                <div class="trajectory-step"><span>02</span><strong><?= $lang === 'ru' ? 'Техническая мощность' : 'Техническая мощность' ?></strong><p><?= $lang === 'ru' ? 'Усиливать backend, API, автоматизацию, инженерный подход и глубину в Python, PHP и C++.' : 'Улучшите серверную часть, API, автоматизацию, инженерное мышление и углубленное изучение Python, PHP и C++.' ?></p></div>
                <div class="trajectory-step"><span>03</span><strong><?= $lang === 'ru' ? 'Публичное позиционирование' : 'Публичное позиционирование' ?></strong><p><?= $lang === 'ru' ? 'Показывать свой реальный путь через GitHub, сайт, релизы и живые проекты, за которыми стоит настоящая работа.' : 'Show the real path through GitHub, the website, releases, and live projects backed by real work.' ?></p></div>
            </div>
        </div>
    </section>
    <section class="section section-contacts" id="contacts">
        <div class="section-head reveal">
            <div class="section-kicker"><?= h($contactsBlock['label'] ?? ($lang === 'ru' ? 'Контакты' : 'Contact')) ?></div>
            <h2><?= h((string) trans_value($contactsBlock, 'title', $lang)) ?></h2>
            <p><?= h($contactIntro) ?></p>
        </div>
        <div class="contact-layout">
            <div class="contact-list reveal">
                <?php foreach ($contacts as $contact): ?>
                    <a class="contact-item magnetic" href="<?= h($contact['link_url'] ?: '#') ?>" <?= !empty($contact['link_url']) ? 'target="_blank" rel="noreferrer"' : '' ?>>
                        <span><?= h((string) trans_value($contact, 'label', $lang)) ?></span>
                        <strong><?= h($contact['value_text']) ?></strong>
                    </a>
                <?php endforeach; ?>
            </div>
            <form class="contact-form reveal" data-delay="120" action="/contact" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="page_url" value="<?= h($lang === 'ru' ? url('/'): url('/en')) ?>">
                <input type="hidden" name="locale" value="<?= h($lang) ?>">
                <label>
                    <span><?= $lang === 'ru' ? 'Имя' : 'Name' ?></span>
                    <input type="text" name="name" value="<?= h((string) old('name')) ?>" required>
                </label>
                <label>
                    <span>Email</span>
                    <input type="email" name="email" value="<?= h((string) old('email')) ?>">
                </label>
                <label>
                    <span>Telegram</span>
                    <input type="text" name="telegram" value="<?= h((string) old('telegram')) ?>" placeholder="@username">
                </label>
                <label>
                    <span><?= $lang === 'ru' ? 'Сообщение' : 'Message' ?></span>
                    <textarea name="message" rows="6" required><?= h((string) old('message')) ?></textarea>
                </label>
                <button class="button button-primary magnetic" type="submit"><?= h($copy['send']) ?></button>
            </form>
        </div>
    </section>
</main>
<footer class="site-footer">
    <div>
        <strong><?= h($fullName) ?></strong>
        <span><?= h($heroTagline) ?></span>
    </div>
    <div>
        <a href="https://github.com/QuestGuideRF" target="_blank" rel="noreferrer">GitHub</a>
        <a href="https://t.me/FitoDomik" target="_blank" rel="noreferrer">Telegram</a>
        <a href="mailto:<?= h($settings['contact_email'] ?? 'vdomninsg@mail.ru') ?>">Email</a>
    </div>
</footer>
