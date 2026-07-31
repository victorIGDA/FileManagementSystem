<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Database;

$username = $argv[1] ?? '';
$email = $argv[2] ?? '';
$name = $argv[3] ?? '';
$password = $argv[4] ?? '';

$invalidData = $username === ''
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || $name === ''
    || strlen($password) < 12;

if ($invalidData) {
    fwrite(
        STDERR,
        "Uso: php scripts/create_admin.php usuario correo nombre \"contraseña-de-12+\"\n"
    );
    exit(1);
}

$database = Database::connection();
$database->beginTransaction();

try {
    $database
        ->prepare(
            'INSERT INTO usuarios (
                id_rol,
                nombre_usuario,
                correo,
                contrasena_hash
             ) VALUES (1, ?, ?, ?)'
        )
        ->execute([
            $username,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
        ]);

    $userId = (int) $database->lastInsertId();

    $database
        ->prepare(
            'INSERT INTO perfiles_usuarios (
                id_usuario,
                nombre_completo
             ) VALUES (?, ?)'
        )
        ->execute([$userId, $name]);

    $database->commit();
    echo "Administrador creado con ID {$userId}.\n";
} catch (Throwable $exception) {
    if ($database->inTransaction()) {
        $database->rollBack();
    }

    fwrite(
        STDERR,
        "No se creó el administrador: {$exception->getMessage()}\n"
    );
    exit(1);
}
