<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?: '/admin';
$menu = [
    ['/admin', 'Обзор'],
    ['/admin/settings', 'Настройки'],
    ['/admin/blocks', 'Блоки'],
    ['/admin/projects', 'Проекты'],
    ['/admin/posts', 'Посты'],
    ['/admin/contacts', 'Контакты'],
    ['/admin/messages', 'Сообщения'],
];
?>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="/admin">
            <span class="brand-mark"></span>
            <div>
                <strong>Vyacheslav CMS</strong>
                <span>PHP / MySQL / RU-EN</span>
            </div>
        </a>
        <nav class="admin-nav">
            <?php foreach ($menu as [$href, $label]): ?>
                <a href="<?= h($href) ?>" class="<?= $path === $href ? 'is-active' : '' ?>"><?= h($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar__footer">
            <div>
                <strong><?= h($adminUser['display_name'] ?? 'Admin') ?></strong>
                <span>@<?= h($adminUser['username'] ?? 'admin') ?></span>
            </div>
            <a href="/admin/logout">Выйти</a>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <strong>Админ-панель</strong>
                <span>Непрочитанных сообщений: <?= h((string) ($unreadMessages ?? 0)) ?></span>
            </div>
            <a href="/" target="_blank" rel="noreferrer">Открыть сайт</a>
        </header>
        <div class="admin-content">
            <?= $slot ?? '' ?>
        </div>
    </div>
</div>
