<?php ob_start(); ?>
<section class="admin-card">
    <div class="admin-card__head">
        <div>
            <h2>Блоки публичной страницы</h2>
            <p>Редактирование крупных смысловых секций: About, Projects, Posts, Plans, Contacts.</p>
        </div>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Секция</th><th>Заголовок RU</th><th>Порядок</th><th>Статус</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($blocks as $block): ?>
                    <tr>
                        <td><?= h($block['section_key']) ?></td>
                        <td><?= h($block['title_ru']) ?></td>
                        <td><?= h((string) $block['sort_order']) ?></td>
                        <td><?= !empty($block['enabled']) ? 'Включён' : 'Скрыт' ?></td>
                        <td><a href="/admin/blocks/<?= h((string) $block['id']) ?>/edit">Редактировать</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php $slot = ob_get_clean(); require __DIR__ . '/../partials-shell.php'; ?>