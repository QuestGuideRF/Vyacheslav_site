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
        'database' => 'u3512632_domnin_portfolio',
        'username' => 'u3512632_domnin_portfolio',
        'password' => 'u3512632_domnin_portfolio',
        'charset' => 'utf8mb4',
    ],
    'setup' => [
        'admin_username' => 'Slavik10',
        'admin_password' => 'Slavik10',
        'admin_name' => 'Slavik10',
    ],
];