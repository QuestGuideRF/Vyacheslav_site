<?php
$fullName = $lang === 'ru' ? ($settings['person_full_name'] ?? '') : ($settings['person_full_name_en'] ?? '');
?>
<header class="simple-header">
    <a class="brand" href="<?= $lang === 'ru' ? '/' : '/en' ?>">
        <span class="brand-mark"></span>
        <span class="brand-copy">
            <strong><?= h($fullName) ?></strong>
            <span><?= h($settings['person_nickname'] ?? '') ?></span>
        </span>
    </a>
    <div class="header-actions">
        <a class="button button-secondary" href="<?= h($lang === 'ru' ? '/' : '/en') ?>#projects"><?= $lang === 'ru' ? 'Назад к проектам' : 'Back to projects' ?></a>
        <a class="lang-switch" href="<?= h($lang === 'ru' ? '/en/projects/' . $project['slug'] : '/projects/' . $project['slug']) ?>"><?= $lang === 'ru' ? 'EN' : 'RU' ?></a>
    </div>
</header>
<main class="detail-page">
    <article class="detail-card reveal">
        <div class="section-kicker"><?= h((string) trans_value($project, 'status', $lang)) ?></div>
        <h1><?= h((string) trans_value($project, 'title', $lang)) ?></h1>
        <p class="detail-lead"><?= h((string) trans_value($project, 'excerpt', $lang)) ?></p>
        <div class="detail-actions">
            <?php if (!empty($project['live_url'])): ?><a class="button button-primary" href="<?= h($project['live_url']) ?>" target="_blank" rel="noreferrer"><?= $lang === 'ru' ? 'Открыть сайт' : 'Open site' ?></a><?php endif; ?>
            <?php if (!empty($project['repo_url'])): ?><a class="button button-secondary" href="<?= h($project['repo_url']) ?>" target="_blank" rel="noreferrer">GitHub</a><?php endif; ?>
        </div>
        <div class="project-stack">
            <?php foreach (array_filter(array_map('trim', explode(',', (string) ($project['stack_text'] ?? '')))) as $stack): ?>
                <span><?= h($stack) ?></span>
            <?php endforeach; ?>
        </div>
        <div class="detail-body rich-text"><?= markdown_to_html((string) trans_value($project, 'content', $lang)) ?></div>
    </article>
</main>
