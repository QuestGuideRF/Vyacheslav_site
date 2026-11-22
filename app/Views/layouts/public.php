<?php
$meta = $meta ?? [];
$lang = $lang ?? locale();
$settings = $settings ?? [];
$theme = $settings['default_theme'] ?? 'dark';
$personName = $lang === 'ru'
    ? ($settings['person_full_name'] ?? 'Домнин Вячеслав Игоревич')
    : ($settings['person_full_name_en'] ?? 'Вячеслав Домнин');
$description = $meta['description'] ?? ($lang === 'ru' ? ($settings['site_description_ru'] ?? '') : ($settings['site_description_en'] ?? ''));
$title = $meta['title'] ?? ($lang === 'ru' ? ($settings['site_title_ru'] ?? '') : ($settings['site_title_en'] ?? ''));
$keywords = trim((string) ($meta['keywords'] ?? ($lang === 'ru' ? ($settings['seo_home_keywords_ru'] ?? '') : ($settings['seo_home_keywords_en'] ?? ''))));
$flash = pull_flash();
$personUrlRu = url('/');
$personUrlEn = url('/en');
$canonicalUrl = $meta['canonical'] ?? ($lang === 'ru' ? $personUrlRu : $personUrlEn);
$avatarImage = $meta['image'] ?? (!empty($settings['avatar_path']) ? upload_url($settings['avatar_path']) : null);
$fontHref = 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=JetBrains+Mono:wght@500;700&family=Unbounded:wght@400;600&display=swap';
$appCssPath = dirname(__DIR__, 3) . '/assets/css/app.css';
$inlineAppCss = is_file($appCssPath) ? file_get_contents($appCssPath) : '';
$knowsAbout = [
    'Fitodomik',
    'Фитодомика онлайн',
    'Проект Фитодомик',
    'Fitodomik.ru',
    'QuestGuideRF',
    'PHP',
    'Python',
    'C++',
    'JavaScript',
    'TypeScript',
    'MySQL',
    'API-интеграция',
    'Телеграм-боты',
    'Arduino',
    'Малиновый Пи',
    'серверная разработка',
    'полная разработка',
];
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    '@id' => url('/#person'),
    'name' => $personName,
    'alternateName' => array_values(array_filter([
        $settings['person_nickname'] ?? '',
        'legendaSVD',
    ])),
    'url' => $canonicalUrl,
    'description' => $description,
    'birthDate' => $settings['person_birthdate'] ?? '',
    'jobTitle' => $lang === 'ru' ? ($settings['hero_tagline_ru'] ?? '') : ($settings['hero_tagline_en'] ?? ''),
    'knowsAbout' => $knowsAbout,
    'knowsLanguage' => ['ru', 'en'],
    'sameAs' => [
        'https://github.com/QuestGuideRF',
        'https://t.me/FitoDomik',
    ],
];
if ($avatarImage) {
    $schema['image'] = $avatarImage;
}
$profilePageSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'ProfilePage',
    '@id' => $canonicalUrl . '#profile',
    'url' => $canonicalUrl,
    'name' => $title,
    'description' => $description,
    'inLanguage' => $lang,
    'isPartOf' => ['@id' => url('/#website')],
    'about' => ['@id' => url('/#person')],
    'mainEntity' => ['@id' => url('/#person')],
];
$websiteSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    '@id' => url('/#website'),
    'name' => $lang === 'ru' ? ($settings['site_title_ru'] ?? 'domninvyacheslav.ru') : ($settings['site_title_en'] ?? 'domninvyacheslav.ru'),
    'url' => $lang === 'ru' ? $personUrlRu : $personUrlEn,
    'inLanguage' => $lang,
    'description' => $description,
    'publisher' => ['@id' => url('/#person')],
    'about' => ['@id' => url('/#person')],
];
?>
<!doctype html>
<html lang="<?= h($lang) ?>" data-theme="<?= h($theme) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?></title>
    <meta name="description" content="<?= h($description) ?>">
    <?php if ($keywords !== ''): ?><meta name="keywords" content="<?= h($keywords) ?>"><?php endif; ?>
    <meta name="author" content="<?= h($personName) ?>">
    <meta name="creator" content="<?= h($personName) ?>">
    <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
    <meta name="theme-color" content="#0b1020">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?= h($lang === 'ru' ? 'ru_RU' : 'en_US') ?>">
    <meta property="og:site_name" content="domninvyacheslav.ru">
    <meta property="og:title" content="<?= h($title) ?>">
    <meta property="og:description" content="<?= h($description) ?>">
    <meta property="og:url" content="<?= h($canonicalUrl) ?>">
    <?php if ($avatarImage): ?>
        <meta property="og:image" content="<?= h($avatarImage) ?>">
        <meta property="og:image:alt" content="<?= h($personName) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="<?= $avatarImage ? 'summary_large_image': 'summary' ?>">
    <meta name="twitter:title" content="<?= h($title) ?>">
    <meta name="twitter:description" content="<?= h($description) ?>">
    <meta name="twitter:site" content="@legendaSVD">
    <?php if ($avatarImage): ?><meta name="twitter:image" content="<?= h($avatarImage) ?>"><?php endif; ?>
    <link rel="canonical" href="<?= h($canonicalUrl) ?>">
    <link rel="alternate" hreflang="ru" href="<?= h($meta['alternate_ru'] ?? url('/')) ?>">
    <link rel="alternate" hreflang="en" href="<?= h($meta['alternate_en'] ?? url('/en')) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= h(url('/')) ?>">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= h($fontHref) ?>" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="<?= h($fontHref) ?>"></noscript>
    <?php if ($inlineAppCss !== ''): ?>
        <style><?= $inlineAppCss ?></style>
    <?php else: ?>
        <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <?php endif; ?>
    <script>
        try {
            const savedTheme = localStorage.getItem('domnin-theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            }
        } catch (error) {}
    </script>
    <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <script type="application/ld+json"><?= json_encode($profilePageSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <script type="application/ld+json"><?= json_encode($websiteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
</head>
<body>
    <div class="site-shell">
        <div class="site-noise"></div>
        <div class="site-grid"></div>
        <canvas class="hero-canvas" id="heroCanvas" aria-hidden="true"></canvas>
        <?php if ($flash): ?>
            <div class="flash-stack">
                <?php foreach ($flash as $type => $messages): ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="flash flash-<?= h($type) ?>"><?= h($message) ?></div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?= $content ?>
    </div>
    <script>
        window.APP_CONFIG = <?= json_encode([
            'lang' => $lang,
            'theme' => $theme,
            'routes' => [
                'ru' => active_locale_path('ru'),
                'en' => active_locale_path('en'),
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
