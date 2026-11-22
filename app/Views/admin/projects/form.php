<?php
$project = $project ?? [
    'id' => null,
    'slug' => '',
    'title_ru' => '',
    'title_en' => '',
    'excerpt_ru' => '',
    'excerpt_en' => '',
    'content_ru' => '',
    'content_en' => '',
    'status_ru' => '',
    'status_en' => '',
    'stack_text' => '',
    'live_url' => '',
    'repo_url' => '',
    'demo_url' => '',
    'image_path' => '',
    'featured' => 1,
    'sort_order' => 0,
    'published' => 1,
];
ob_start();
?>
<form class="admin-form" method="post" action="<?= $project['id'] ? '/admin/projects/' . $project['id'] . '/edit': '/admin/projects/create' ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <section class="admin-card">
        <div class="admin-card__head">
            <div>
                <h2><?= $project['id'] ? 'Редактирование проекта' : 'Новый проект' ?></h2>
                <p>Кнопка перевода сохранит английские поля на основе русской версии.</p>
            </div>
            <button type="submit" name="translate_project" value="1">Сохранить и перевести</button>
        </div>
        <div class="admin-form-grid">
            <label><span>Title RU</span><input type="text" name="title_ru" value="<?= h($project['title_ru']) ?>" required></label>
            <label><span>Title EN</span><input type="text" name="title_en" value="<?= h((string) $project['title_en']) ?>"></label>
            <label><span>Slug</span><input type="text" name="slug" value="<?= h($project['slug']) ?>"></label>
            <label><span>Stack</span><input type="text" name="stack_text" value="<?= h((string) $project['stack_text']) ?>" placeholder="PHP, JS, MySQL"></label>
            <label><span>Excerpt RU</span><textarea name="excerpt_ru" rows="4"><?= h((string) $project['excerpt_ru']) ?></textarea></label>
            <label><span>Excerpt EN</span><textarea name="excerpt_en" rows="4"><?= h((string) $project['excerpt_en']) ?></textarea></label>
            <label><span>Status RU</span><input type="text" name="status_ru" value="<?= h((string) $project['status_ru']) ?>"></label>
            <label><span>Status EN</span><input type="text" name="status_en" value="<?= h((string) $project['status_en']) ?>"></label>
            <label class="admin-form-grid__full"><span>Content RU</span><textarea name="content_ru" rows="10"><?= h((string) $project['content_ru']) ?></textarea></label>
            <label class="admin-form-grid__full"><span>Content EN</span><textarea name="content_en" rows="10"><?= h((string) $project['content_en']) ?></textarea></label>
            <label><span>Live URL</span><input type="url" name="live_url" value="<?= h((string) $project['live_url']) ?>"></label>
            <label><span>Repo URL</span><input type="url" name="repo_url" value="<?= h((string) $project['repo_url']) ?>"></label>
            <label><span>Demo URL</span><input type="url" name="demo_url" value="<?= h((string) $project['demo_url']) ?>"></label>
            <label><span>Порядок</span><input type="number" name="sort_order" value="<?= h((string) $project['sort_order']) ?>"></label>
            <label><span>Изображение</span><input type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg"></label>
            <input type="hidden" name="existing_image" value="<?= h((string) $project['image_path']) ?>">
            <label class="checkbox"><input type="checkbox" name="featured" value="1" <?= !empty($project['featured']) ? 'checked' : '' ?>><span>Показывать как featured</span></label>
            <label class="checkbox"><input type="checkbox" name="published" value="1" <?= !empty($project['published']) ? 'checked' : '' ?>><span>Опубликован</span></label>
        </div>
    </section>
    <div class="admin-submit-row">
        <button type="submit"><?= $project['id'] ? 'Сохранить проект' : 'Создать проект' ?></button>
    </div>
</form>
<?php $slot = ob_get_clean(); require __DIR__ . '/../partials-shell.php'; ?>
