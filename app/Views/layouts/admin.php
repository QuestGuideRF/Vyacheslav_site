<?php
$meta = $meta ?? [];
$flash = pull_flash();
$isLoginPage = str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/login');
$fontHref = 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500&family=Unbounded:wght@400;500;700&display=swap';
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($meta['title'] ?? 'Админ-панель') ?></title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="<?= h($fontHref) ?>" as="style" crossorigin>
    <link rel="stylesheet" href="<?= h($fontHref) ?>" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="<?= h($fontHref) ?>"></noscript>
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="<?= $isLoginPage ? 'admin-login-page' : 'admin-page' ?>">
    <?php if ($flash): ?>
        <div class="admin-flash-stack">
            <?php foreach ($flash as $type => $messages): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="admin-flash admin-flash-<?= h($type) ?>"><?= h($message) ?></div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?= $content ?>
    <script src="<?= asset('js/admin.js') ?>"></script>
</body>
</html>
