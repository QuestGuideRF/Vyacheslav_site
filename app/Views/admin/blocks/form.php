<?php ob_start(); ?>
<form class="admin-form" method="post" action="/admin/blocks/<?= h((string) $block['id']) ?>/edit">
    <?= csrf_field() ?>
    <section class="admin-card">
        <div class="admin-card__head">
            <div>
                <h2>Редактирование блока: <?= h($block['section_key']) ?></h2>
                <p>Кнопка перевода сразу сохранит английскую версию на основе русского текста.</p>
            </div>
            <button type="submit" name="translate_block" value="1">Сохранить и перевести</button>
        </div>
        <div class="admin-form-grid">
            <label><span>Label</span><input type="text" name="label" value="<?= h($block['label']) ?>"></label>
            <label><span>Порядок</span><input type="number" name="sort_order" value="<?= h((string) $block['sort_order']) ?>"></label>
            <label><span>Title RU</span><textarea name="title_ru" rows="3"><?= h($block['title_ru']) ?></textarea></label>
            <label><span>Title EN</span><textarea name="title_en" rows="3"><?= h($block['title_en']) ?></textarea></label>
            <label><span>Lead RU</span><textarea name="lead_ru" rows="4"><?= h($block['lead_ru']) ?></textarea></label>
            <label><span>Lead EN</span><textarea name="lead_en" rows="4"><?= h($block['lead_en']) ?></textarea></label>
            <label><span>Content RU</span><textarea name="content_ru" rows="12"><?= h($block['content_ru']) ?></textarea></label>
            <label><span>Content EN</span><textarea name="content_en" rows="12"><?= h($block['content_en']) ?></textarea></label>
            <label class="admin-form-grid__full"><span>Extra JSON</span><textarea name="extra_json" rows="5"><?= h((string) $block['extra_json']) ?></textarea></label>
            <label class="checkbox"><input type="checkbox" name="enabled" value="1" <?= !empty($block['enabled']) ? 'checked' : '' ?>><span>Блок включён</span></label>
        </div>
    </section>
    <div class="admin-submit-row">
        <button type="submit">Сохранить блок</button>
    </div>
</form>
<?php $slot = ob_get_clean(); require __DIR__ . '/../partials-shell.php'; ?>
