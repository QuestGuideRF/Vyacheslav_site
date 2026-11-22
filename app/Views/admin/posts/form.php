<?php
$post = $post ?? [
    'id' => null,
    'slug' => '',
    'title_ru' => '',
    'title_en' => '',
    'excerpt_ru' => '',
    'excerpt_en' => '',
    'content_ru' => '',
    'content_en' => '',
    'category_ru' => '',
    'category_en' => '',
    'image_path' => '',
    'published' => 1,
    'published_at' => date('Y-m-d\TH:i'),
];
ob_start();
?>
<form class="admin-form" method="post" action="<?= $post['id'] ? '/admin/posts/' . $post['id'] . '/edit': '/admin/posts/create' ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <section class="admin-card">
        <div class="admin-card__head">
            <div>
                <h2><?= $post['id'] ? 'Редактирование поста' : 'Новый пост' ?></h2>
                <p>Можно быстро перевести русский текст и сразу сохранить английскую версию.</p>
            </div>
            <button type="submit" name="translate_post" value="1">Сохранить и перевести</button>
        </div>
        <div class="admin-form-grid">
            <label><span>Title RU</span><input type="text" name="title_ru" value="<?= h($post['title_ru']) ?>" required></label>
            <label><span>Title EN</span><input type="text" name="title_en" value="<?= h((string) $post['title_en']) ?>"></label>
            <label><span>Slug</span><input type="text" name="slug" value="<?= h($post['slug']) ?>"></label>
            <label><span>Дата публикации</span><input type="datetime-local" name="published_at" value="<?= h(date('Y-m-d\TH:i', strtotime((string) $post['published_at']))) ?>"></label>
            <label><span>Категория RU</span><input type="text" name="category_ru" value="<?= h((string) $post['category_ru']) ?>"></label>
            <label><span>Category EN</span><input type="text" name="category_en" value="<?= h((string) $post['category_en']) ?>"></label>
            <label><span>Excerpt RU</span><textarea name="excerpt_ru" rows="4"><?= h((string) $post['excerpt_ru']) ?></textarea></label>
            <label><span>Excerpt EN</span><textarea name="excerpt_en" rows="4"><?= h((string) $post['excerpt_en']) ?></textarea></label>
            <label class="admin-form-grid__full"><span>Content RU</span><textarea name="content_ru" rows="12"><?= h((string) $post['content_ru']) ?></textarea></label>
            <label class="admin-form-grid__full"><span>Content EN</span><textarea name="content_en" rows="12"><?= h((string) $post['content_en']) ?></textarea></label>
            <label><span>Изображение</span><input type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg"></label>
            <input type="hidden" name="existing_image" value="<?= h((string) $post['image_path']) ?>">
            <label class="checkbox"><input type="checkbox" name="published" value="1" <?= !empty($post['published']) ? 'checked' : '' ?>><span>Опубликован</span></label>
        </div>
    </section>
    <div class="admin-submit-row">
        <button type="submit"><?= $post['id'] ? 'Сохранить пост' : 'Создать пост' ?></button>
    </div>
</form>
<?php $slot = ob_get_clean(); require __DIR__ . '/../partials-shell.php'; ?>
