<?php
use App\Core\Application;
use App\Core\Database;
use App\Core\Router;
use App\Repositories\BlockRepository;
use App\Repositories\ContactRepository;
use App\Repositories\MessageRepository;
use App\Repositories\PostRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\SettingsRepository;
use App\Services\AuthService;
use App\Services\GitHubService;
use App\Services\InstallerService;
use App\Services\TelegramService;
use App\Services\TranslationService;
use App\Services\UploadService;
spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $relative = substr(str_replace('\\', '/', $class), 4) . '.php';
    $path = __DIR__ . '/' . $relative;
    if (is_file($path)) {
        require $path;
    }
});
require __DIR__ . '/Support/helpers.php';
$configFile = __DIR__ . '/Config/config.php';
if (!is_file($configFile)) {
    throw new RuntimeException('Конфигурация не найдена. Проверьте файл app/Config/config.php');
}
$config = require $configFile;
date_default_timezone_set($config['app']['timezone'] ?? 'Europe/Moscow');
if (session_status() === PHP_SESSION_NONE) {
    session_name($config['app']['session_name'] ?? 'domnin_portfolio');
    session_start();
}
$app = new Application($config);
$GLOBALS['app_instance'] = $app;
$db = new Database($config['db']);
(new InstallerService())->ensureInstalled($db, $config);
$app->instance('db', $db);
$app->bind('router', static fn (): Router => new Router());
$app->bind('settings', static fn (Application $app): SettingsRepository => new SettingsRepository($app->get('db')));
$app->bind('blocks', static fn (Application $app): BlockRepository => new BlockRepository($app->get('db')));
$app->bind('projects', static fn (Application $app): ProjectRepository => new ProjectRepository($app->get('db')));
$app->bind('posts', static fn (Application $app): PostRepository => new PostRepository($app->get('db')));
$app->bind('contacts', static fn (Application $app): ContactRepository => new ContactRepository($app->get('db')));
$app->bind('messages', static fn (Application $app): MessageRepository => new MessageRepository($app->get('db')));
$app->bind('auth', static fn (Application $app): AuthService => new AuthService($app->get('db')));
$app->bind('translate', static fn (): TranslationService => new TranslationService());
$app->bind('upload', static fn (Application $app): UploadService => new UploadService($app->get('config')['app']['uploads_dir']));
$app->bind('telegram', static fn (Application $app): TelegramService => new TelegramService($app->get('settings')));
$app->bind('github', static fn (Application $app): GitHubService => new GitHubService($app->get('settings'), $app->get('projects'), $app->get('config')['app']['cache_dir']));
return $app;