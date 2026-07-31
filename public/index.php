<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

$router = new App\Core\Router();

require APP_ROOT . '/routes/web.php';

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);
