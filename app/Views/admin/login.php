<main class="login-shell">
    <section class="login-card">
        <div class="login-copy">
            <div class="admin-kicker">domninvyacheslav.ru / admin</div>
            <h1>Вход в админ-панель</h1>
            <p>Управление двуязычным контентом, проектами, переводами, сообщениями, GitHub и SEO-настройками.</p>
        </div>
        <form class="login-form" method="post" action="/admin/login">
            <?= csrf_field() ?>
            <label>
                <span>Логин</span>
                <input type="text" name="username" value="<?= h((string) old('username')) ?>" required>
            </label>
            <label>
                <span>Пароль</span>
                <input type="password" name="password" required>
            </label>
            <button type="submit">Войти</button>
        </form>
    </section>
</main>
