<?php ob_start(); ?>
<section class="admin-panel-grid">
    <article class="metric-card">
        <span>Проекты</span>
        <strong><?= h((string) ($stats['projects'] ?? 0)) ?></strong>
    </article>
    <article class="metric-card">
        <span>Посты</span>
        <strong><?= h((string) ($stats['posts'] ?? 0)) ?></strong>
    </article>
    <article class="metric-card">
        <span>Контакты</span>
        <strong><?= h((string) ($stats['contacts'] ?? 0)) ?></strong>
    </article>
    <article class="metric-card">
        <span>Сообщения</span>
        <strong><?= h((string) ($stats['messages'] ?? 0)) ?></strong>
    </article>
</section>
<section class="admin-card">
    <div class="admin-card__head">
        <div>
            <h2>GitHub-сводка</h2>
            <p>Данные для публичного блока GitHub-статистики.</p>
        </div>
        <form method="post" action="/admin/github/refresh">
            <?= csrf_field() ?>
            <button type="submit">Обновить GitHub</button>
        </form>
    </div>
    <div class="github-admin-grid">
        <div>
            <strong><?= h($github['profile']['name'] ?? 'GitHub') ?></strong>
            <p>@<?= h($github['profile']['login'] ?? '') ?></p>
            <small><?= h($github['updated_at'] ?? '') ?></small>
        </div>
        <div>
            <strong><?= h((string) ($github['stats']['public_repos'] ?? 0)) ?></strong>
            <p>Репозитории</p>
        </div>
        <div>
            <strong><?= h((string) ($github['stats']['stars'] ?? 0)) ?></strong>
            <p>Звёзды</p>
        </div>
        <div>
            <strong><?= h((string) ($github['stats']['followers'] ?? 0)) ?></strong>
            <p>Подписчики</p>
        </div>
    </div>
</section>
<?php $slot = ob_get_clean(); require __DIR__ . '/partials-shell.php'; ?>