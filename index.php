<?php
$app = require __DIR__ . '/app/bootstrap.php';
$request = App\Core\Request::capture();
$router = $app->get('router');
require __DIR__ . '/app/routes.php';
$router->dispatch($request, $app);