<?php
$general = $grouped['general'] ?? [];
$seo = $grouped['seo'] ?? [];
ob_start();
?>
<form class="admin-form" method="post" action="/admin/settings" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <section class="admin-card">
        <div class="admin-card__head">
            <div>
                <h2>Основные настройки</h2>
                <p>Личная карточка, hero, GitHub, Telegram, LLMS и общие параметры сайта.</p>
            </div>
            <button type="submit" name="translate_general" value="1">Сохранить и перевести RU -> EN</button>
        </div>
        <div class="admin-form-grid">
            <label><span>Имя RU</span><input type="text" name="person_full_name" value="<?= h($general['person_full_name'] ?? '') ?>"></label>
            <label><span>Имя EN</span><input type="text" name="person_full_name_en" value="<?= h($general['person_full_name_en'] ?? '') ?>"></label>
            <label><span>Никнейм</span><input type="text" name="person_nickname" value="<?= h($general['person_nickname'] ?? '') ?>"></label>
            <label><span>Дата рождения</span><input type="date" name="person_birthdate" value="<?= h($general['person_birthdate'] ?? '') ?>"></label>
            <label><span>Hero tagline RU</span><textarea name="hero_tagline_ru" rows="3"><?= h($general['hero_tagline_ru'] ?? '') ?></textarea></label>
            <label><span>Hero tagline EN</span><textarea name="hero_tagline_en" rows="3"><?= h($general['hero_tagline_en'] ?? '') ?></textarea></label>
            <label><span>Hero intro RU</span><textarea name="hero_intro_ru" rows="5"><?= h($general['hero_intro_ru'] ?? '') ?></textarea></label>
            <label><span>Hero intro EN</span><textarea name="hero_intro_en" rows="5"><?= h($general['hero_intro_en'] ?? '') ?></textarea></label>
            <label><span>Биография RU</span><textarea name="person_biography_ru" rows="9"><?= h($general['person_biography_ru'] ?? '') ?></textarea></label>
            <label><span>Biography EN</span><textarea name="person_biography_en" rows="9"><?= h($general['person_biography_en'] ?? '') ?></textarea></label>
            <label><span>Роли RU</span><textarea name="card_roles_ru" rows="3"><?= h($general['card_roles_ru'] ?? '') ?></textarea></label>
            <label><span>Roles EN</span><textarea name="card_roles_en" rows="3"><?= h($general['card_roles_en'] ?? '') ?></textarea></label>
            <label><span>Статус RU</span><input type="text" name="card_status_ru" value="<?= h($general['card_status_ru'] ?? '') ?>"></label>
            <label><span>Status EN</span><input type="text" name="card_status_en" value="<?= h($general['card_status_en'] ?? '') ?>"></label>
            <label><span>Локация RU</span><input type="text" name="card_location_ru" value="<?= h($general['card_location_ru'] ?? '') ?>"></label>
            <label><span>Location EN</span><input type="text" name="card_location_en" value="<?= h($general['card_location_en'] ?? '') ?>"></label>
            <label><span>GitHub username</span><input type="text" name="github_username" value="<?= h($general['github_username'] ?? '') ?>"></label>
            <label><span>GitHub token</span><input type="password" name="github_token" value="<?= h($general['github_token'] ?? '') ?>"></label>
            <label><span>Telegram bot token</span><input type="password" name="telegram_bot_token" value="<?= h($general['telegram_bot_token'] ?? '') ?>"></label>
            <label><span>Telegram chat ID</span><input type="text" name="telegram_chat_id" value="<?= h($general['telegram_chat_id'] ?? '') ?>"></label>
            <label><span>Email</span><input type="email" name="contact_email" value="<?= h($general['contact_email'] ?? '') ?>"></label>
            <label><span>Тема по умолчанию</span>
                <select name="default_theme">
                    <option value="dark" <?= ($general['default_theme'] ?? 'dark') === 'dark' ? 'selected' : '' ?>>Dark</option>
                    <option value="light" <?= ($general['default_theme'] ?? '') === 'light' ? 'selected' : '' ?>>Light</option>
                </select>
            </label>
            <label><span>Title RU</span><input type="text" name="site_title_ru" value="<?= h($general['site_title_ru'] ?? '') ?>"></label>
            <label><span>Title EN</span><input type="text" name="site_title_en" value="<?= h($general['site_title_en'] ?? '') ?>"></label>
            <label><span>Description RU</span><textarea name="site_description_ru" rows="4"><?= h($general['site_description_ru'] ?? '') ?></textarea></label>
            <label><span>Description EN</span><textarea name="site_description_en" rows="4"><?= h($general['site_description_en'] ?? '') ?></textarea></label>
            <label><span>LLMS summary RU</span><textarea name="llms_summary_ru" rows="4"><?= h($general['llms_summary_ru'] ?? '') ?></textarea></label>
            <label><span>LLMS summary EN</span><textarea name="llms_summary_en" rows="4"><?= h($general['llms_summary_en'] ?? '') ?></textarea></label>
            <label class="admin-form-grid__full"><span>Аватар</span><input type="file" name="avatar_file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg"></label>
        </div>
    </section>
    <section class="admin-card">
        <div class="admin-card__head">
            <div>
                <h2>SEO</h2>
                <p>Метаданные, title, description и индексация для главной страницы.</p>
            </div>
            <button type="submit" name="translate_seo" value="1">Сохранить и перевести SEO</button>
        </div>
        <div class="admin-form-grid">
            <label><span>SEO title RU</span><input type="text" name="seo_home_title_ru" value="<?= h($seo['seo_home_title_ru'] ?? '') ?>"></label>
            <label><span>SEO title EN</span><input type="text" name="seo_home_title_en" value="<?= h($seo['seo_home_title_en'] ?? '') ?>"></label>
            <label><span>SEO description RU</span><textarea name="seo_home_description_ru" rows="4"><?= h($seo['seo_home_description_ru'] ?? '') ?></textarea></label>
            <label><span>SEO description EN</span><textarea name="seo_home_description_en" rows="4"><?= h($seo['seo_home_description_en'] ?? '') ?></textarea></label>
            <label><span>SEO keywords RU</span><textarea name="seo_home_keywords_ru" rows="5"><?= h($seo['seo_home_keywords_ru'] ?? '') ?></textarea></label>
            <label><span>SEO keywords EN</span><textarea name="seo_home_keywords_en" rows="5"><?= h($seo['seo_home_keywords_en'] ?? '') ?></textarea></label>
        </div>
    </section>
    <div class="admin-submit-row">
        <button type="submit">Сохранить все настройки</button>
    </div>
</form>
<?php $slot = ob_get_clean(); require __DIR__ . '/partials-shell.php'; ?>