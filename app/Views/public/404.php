<?php
$fullName = $lang === 'ru' ? ($settings['person_full_name'] ?? '') : ($settings['person_full_name_en'] ?? '');
$nickname = $settings['person_nickname'] ?? '';
$homeUrl = $lang === 'ru' ? '/' : '/en';
$projectsUrl = $lang === 'ru' ? '/#projects' : '/en#projects';
$contactsUrl = $lang === 'ru' ? '/#contacts' : '/en#contacts';
?>
<header class="simple-header">
    <a class="brand" href="<?= h($homeUrl) ?>">
        <span class="brand-mark"></span>
        <span class="brand-copy">
            <strong><?= h($fullName) ?></strong>
            <span><?= h($nickname) ?></span>
        </span>
    </a>
    <div class="header-actions">
        <a class="button button-secondary" href="<?= h($projectsUrl) ?>"><?= $lang === 'ru' ? 'К проектам' : 'Projects' ?></a>
        <a class="lang-switch" href="<?= h($lang === 'ru' ? '/en' : '/') ?>"><?= $lang === 'ru' ? 'EN' : 'RU' ?></a>
    </div>
</header>
<main class="detail-page detail-page--404">
    <article class="detail-card detail-card--404 reveal">
        <div class="section-kicker">404 / Lost Route</div>
        <h1><?= $lang === 'ru' ? 'Страница не найдена' : 'Страница не найдена' ?></h1>
        <p class="detail-lead">
            <?= $lang === 'ru'
                ? 'Похоже, такой страницы здесь нет. Можно вернуться на главную, посмотреть проекты или написать мне напрямую.'
                : 'Похоже, этой страницы не существует. Вы можете вернуться на главную страницу, открыть раздел проектов или связаться со мной напрямую.' ?>
        </p>
        <?php if (!empty($hint)): ?>
            <div class="detail-meta"><?= h($hint) ?></div>
        <?php endif; ?>
        <div class="detail-actions">
            <a class="button button-primary" href="<?= h($homeUrl) ?>"><?= $lang === 'ru' ? 'На главную' : 'Home' ?></a>
            <a class="button button-secondary" href="<?= h($contactsUrl) ?>"><?= $lang === 'ru' ? 'Связаться' : 'Contact' ?></a>
        </div>
        <div class="code-window code-window--404">
            <div class="code-window__bar">
                <span></span><span></span><span></span>
            </div>
            <pre><code>router.match("/requested/page", () => {
  throw new NotFoundError(404);
});
redirect("<?= h($homeUrl) ?>");</code></pre>
        </div>
    </article>
</main>
