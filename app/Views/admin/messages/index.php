<?php ob_start(); ?>
<section class="admin-card">
    <div class="admin-card__head">
        <div>
            <h2>Сообщения с формы</h2>
            <p>Все отправки сохраняются в базе и могут дублироваться в Telegram при заполненных настройках бота.</p>
        </div>
    </div>
    <div class="message-list">
        <?php foreach ($messages as $message): ?>
            <article class="message-item <?= empty($message['is_read']) ? 'is-unread' : '' ?>">
                <div class="message-item__top">
                    <div>
                        <strong><?= h($message['name']) ?></strong>
                        <span><?= h($message['email'] ?: $message['telegram'] ?: 'Без контакта') ?></span>
                    </div>
                    <div class="message-actions">
                        <small><?= h((string) $message['created_at']) ?></small>
                        <?php if (empty($message['is_read'])): ?>
                            <form method="post" action="/admin/messages/<?= h((string) $message['id']) ?>/read">
                                <?= csrf_field() ?>
                                <button type="submit">Прочитано</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <p><?= nl2br(h($message['message_text'])) ?></p>
                <div class="message-meta">
                    <span>Locale: <?= h($message['locale']) ?></span>
                    <span>IP: <?= h((string) $message['ip_address']) ?></span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php $slot = ob_get_clean(); require __DIR__ . '/../partials-shell.php'; ?>
