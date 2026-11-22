<?php ob_start(); ?>
<section class="admin-card">
    <div class="admin-card__head">
        <div>
            <h2>Проекты</h2>
            <p>Карточки портфолио, ссылки на сайты и репозитории, статусы, порядок и публикация.</p>
        </div>
        <a class="admin-link-button" href="/admin/projects/create">Новый проект</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Название</th><th>Slug</th><th>Статус</th><th>Featured</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?= h($project['title_ru']) ?></td>
                        <td><?= h($project['slug']) ?></td>
                        <td><?= !empty($project['published']) ? 'Опубликован' : 'Черновик' ?></td>
                        <td><?= !empty($project['featured']) ? 'Да' : 'Нет' ?></td>
                        <td class="table-actions">
                            <a href="/admin/projects/<?= h((string) $project['id']) ?>/edit">Редактировать</a>
                            <form method="post" action="/admin/projects/<?= h((string) $project['id']) ?>/delete">
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