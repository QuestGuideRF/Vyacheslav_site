<?php ob_start(); ?>
<section class="admin-card">
    <div class="admin-card__head">
        <div>
            <h2>Контакты</h2>
            <p>Telegram, GitHub, email и любые дополнительные ссылки для блока контактов.</p>
        </div>
        <a class="admin-link-button" href="/admin/contacts/create">Новый контакт</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Label</th><th>Value</th><th>Type</th><th>Статус</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td><?= h($contact['label_ru']) ?></td>
                        <td><?= h($contact['value_text']) ?></td>
                        <td><?= h($contact['type']) ?></td>
                        <td><?= !empty($contact['enabled']) ? 'Включён' : 'Скрыт' ?></td>
                        <td class="table-actions">
                            <a href="/admin/contacts/<?= h((string) $contact['id']) ?>/edit">Редактировать</a>
                            <form method="post" action="/admin/contacts/<?= h((string) $contact['id']) ?>/delete">
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