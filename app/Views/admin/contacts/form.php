<?php
$contact = $contact ?? [
    'id' => null,
    'label_ru' => '',
    'label_en' => '',
    'value_text' => '',
    'link_url' => '',
    'type' => 'link',
    'icon_name' => '',
    'sort_order' => 0,
    'enabled' => 1,
];
ob_start();
?>
<form class="admin-form" method="post" action="<?= $contact['id'] ? '/admin/contacts/' . $contact['id'] . '/edit': '/admin/contacts/create' ?>">
    <?= csrf_field() ?>
    <section class="admin-card">
        <div class="admin-card__head">
            <div>
                <h2><?= $contact['id'] ? 'Редактирование контакта' : 'Новый контакт' ?></h2>
                <p>Контакты используются в блоке связи и в футере сайта.</p>
            </div>
            <button type="submit" name="translate_contact" value="1">Сохранить и перевести</button>
        </div>
        <div class="admin-form-grid">
            <label><span>Label RU</span><input type="text" name="label_ru" value="<?= h($contact['label_ru']) ?>"></label>
            <label><span>Label EN</span><input type="text" name="label_en" value="<?= h((string) $contact['label_en']) ?>"></label>
            <label><span>Value</span><input type="text" name="value_text" value="<?= h($contact['value_text']) ?>"></label>
            <label><span>URL</span><input type="text" name="link_url" value="<?= h((string) $contact['link_url']) ?>"></label>
            <label><span>Type</span><input type="text" name="type" value="<?= h((string) $contact['type']) ?>" placeholder="telegram/github/email/link"></label>
            <label><span>Icon</span><input type="text" name="icon_name" value="<?= h((string) $contact['icon_name']) ?>"></label>
            <label><span>Порядок</span><input type="number" name="sort_order" value="<?= h((string) $contact['sort_order']) ?>"></label>
            <label class="checkbox"><input type="checkbox" name="enabled" value="1" <?= !empty($contact['enabled']) ? 'checked' : '' ?>><span>Показывать на сайте</span></label>
        </div>
    </section>
    <div class="admin-submit-row">
        <button type="submit"><?= $contact['id'] ? 'Сохранить контакт' : 'Создать контакт' ?></button>
    </div>
</form>
<?php $slot = ob_get_clean(); require __DIR__ . '/../partials-shell.php'; ?>
