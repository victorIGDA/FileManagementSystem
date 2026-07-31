<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Env;

function e(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function url(string $path = ''): string
{
    return rtrim(
        (string) Env::get('APP_URL', ''),
        '/'
    ) . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    $destination = str_starts_with($path, 'http')
        ? $path
        : url($path);

    header('Location: ' . $destination);
    exit;
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="'
        . e(Csrf::token())
        . '">';
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return $value;
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function remember_input(array $input): void
{
    unset(
        $input['contrasena'],
        $input['password'],
        $input['_csrf']
    );

    $_SESSION['_old'] = $input;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function require_csrf(): void
{
    if (!Csrf::validate($_POST['_csrf'] ?? null)) {
        http_response_code(419);
        exit(
            'La sesión del formulario expiró. '
            . 'Regresa e inténtalo de nuevo.'
        );
    }
}
