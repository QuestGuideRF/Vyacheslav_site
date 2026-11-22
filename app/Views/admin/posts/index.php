<?php ob_start(); ?>
<section class="admin-card">
    <div class="admin-card__head">
        <div>
            <h2>Посты и обновления</h2>
            <p>Новости, заметки, changelog, новые релизы и статус разработки.</p>
        </div>
        <a class="admin-link-button" href="/admin/posts/create">Новый пост</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Название</th><th>Категория</th><th>Дата</th><th>Статус</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= h($post['title_ru']) ?></td>
                        <td><?= h((string) $post['category_ru']) ?></td>
                        <td><?= h((string) $post['published_at']) ?></td>
                        <td><?= !empty($post['published']) ? 'Опубликован' : 'Черновик' ?></td>
                        <td class="table-actions">
                            <a href="/admin/posts/<?= h((string) $post['id']) ?>/edit">Редактировать</a>
                            <form method="post" action="/admin/posts/<?= h((string) $post['id']) ?>/delete">
                                <?= csrf_field() ?>
                                <button type="submit" class="link-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php $slot = ob_get_clean(); require __DIR__ . '/../partials-shell.php'; ?>