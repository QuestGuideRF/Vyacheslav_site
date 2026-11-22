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
        <a class="button button-secondary" href="<?= h($lang === 'ru' ? '/' : '/en') ?>#posts"><?= $lang === 'ru' ? 'Назад к постам' : 'Back to posts' ?></a>
        <a class="lang-switch" href="<?= h($lang === 'ru' ? '/en/posts/' . $post['slug'] : '/posts/' . $post['slug']) ?>"><?= $lang === 'ru' ? 'EN' : 'RU' ?></a>
    </div>
</header>
<main class="detail-page">
    <article class="detail-card reveal">
        <div class="section-kicker"><?= h((string) trans_value($post, 'category', $lang)) ?></div>
        <h1><?= h((string) trans_value($post, 'title', $lang)) ?></h1>
        <p class="detail-lead"><?= h((string) trans_value($post, 'excerpt', $lang)) ?></p>
        <div class="detail-meta"><?= h(format_date($post['published_at'] ?? '', $lang)) ?></div>
        <div class="detail-body rich-text"><?= markdown_to_html((string) trans_value($post, 'content', $lang)) ?></div>
    </article>
</main>
