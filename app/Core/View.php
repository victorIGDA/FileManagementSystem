<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], bool $layout = true): void
    {
        $path = APP_ROOT . '/app/Views/' . $view . '.php';
        if (!is_file($path)) {
            throw new \RuntimeException("Vista no encontrada: {$view}");
        }
        extract($data, EXTR_SKIP);
        if ($layout) require APP_ROOT . '/app/Views/layouts/header.php';
        require $path;
        if ($layout) require APP_ROOT . '/app/Views/layouts/footer.php';
    }
}

