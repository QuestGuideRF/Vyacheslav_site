<?php
return [
    'app' => [
        'name' => 'domninvyacheslav.ru',
        'base_url' => 'https://domninvyacheslav.ru',
        'timezone' => 'Europe/Moscow',
        'locale' => 'ru',
        'fallback_locale' => 'en',
        'session_name' => 'domnin_portfolio',
        'cache_dir' => dirname(__DIR__, 2) . '/storage/cache',
        'log_dir' => dirname(__DIR__, 2) . '/storage/logs',
        'uploads_dir' => dirname(__DIR__, 2) . '/uploads',
        'install_lock' => dirname(__DIR__, 2) . '/storage/install/installed.lock',
    ],
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'domnin_portfolio',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'setup' => [
        'admin_username' => 'admin',
        'admin_password' => 'admin12345',
        'admin_name' => 'Administrator',
    ],
];