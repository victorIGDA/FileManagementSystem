<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Env;
use App\Core\View;

define('APP_ROOT', __DIR__);

spl_autoload_register(
    function (string $class): void {
        $prefix = 'App\\';

        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relativeClass = substr(
            $class,
            strlen($prefix)
        );
        $file = APP_ROOT
            . '/app/'
            . str_replace('\\', '/', $relativeClass)
            . '.php';

        if (is_file($file)) {
            require $file;
        }
    }
);

require APP_ROOT . '/app/helpers.php';

Env::load(APP_ROOT . '/.env');
date_default_timezone_set(
    (string) Env::get(
        'APP_TIMEZONE',
        'America/Santo_Domingo'
    )
);

$isWebRequest = PHP_SAPI !== 'cli';
$sessionInactive = session_status() !== PHP_SESSION_ACTIVE;

if ($isWebRequest && $sessionInactive) {
    session_name('arca_audio_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => Env::bool('APP_SECURE_COOKIE', false),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    $lastActivity = (int) ($_SESSION['last_activity'] ?? time());
    $sessionExpired = time() - $lastActivity > 7200;

    if ($sessionExpired) {
        Auth::logout();
        session_start();
    }

    $_SESSION['last_activity'] = time();
}

set_exception_handler(
    function (Throwable $exception): void {
        error_log($exception->__toString());
        http_response_code(500);

        if (Env::get('APP_ENV', 'production') === 'development') {
            echo '<pre>' . e($exception->__toString()) . '</pre>';
            return;
        }

        if (PHP_SAPI !== 'cli') {
            View::render('errors/500');
            return;
        }

        fwrite(STDERR, "Error interno.\n");
    }
);
