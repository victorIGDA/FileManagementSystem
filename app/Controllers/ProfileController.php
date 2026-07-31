<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Env;
use App\Core\View;
use App\Services\ProfileStorage;
use InvalidArgumentException;
use Throwable;

final class ProfileController
{
    public function show(): void
    {
        Auth::requireLogin();

        View::render('profile/index', [
            'user' => Auth::user(),
        ]);
    }

    public function update(): void
    {
        Auth::requireLogin();
        require_csrf();

        $name = trim((string) ($_POST['nombre_completo'] ?? ''));
        $phone = trim((string) ($_POST['telefono'] ?? ''));

        if ($name === '' || mb_strlen($name) > 150 || mb_strlen($phone) > 25) {
            flash('error', 'Revisa el nombre y el teléfono.');
            redirect('/perfil');
        }

        $database = Database::connection();
        $photo = Auth::user()['foto_perfil'];

        try {
            $hasNewPhoto = isset($_FILES['foto'])
                && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

            if ($hasNewPhoto) {
                $photo = (new ProfileStorage())->store($_FILES['foto']);
            }

            $database
                ->prepare(
                    'UPDATE perfiles_usuarios
                     SET nombre_completo = ?,
                         telefono = ?,
                         foto_perfil = ?,
                         fecha_actualizacion = NOW()
                     WHERE id_usuario = ?'
                )
                ->execute([
                    $name,
                    $phone ?: null,
                    $photo,
                    Auth::user()['id_usuario'],
                ]);

            flash('success', 'Perfil actualizado.');
            redirect('/perfil');
        } catch (InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());
            redirect('/perfil');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            flash('error', 'No fue posible actualizar el perfil.');
            redirect('/perfil');
        }
    }

    public function password(): void
    {
        Auth::requireLogin();
        require_csrf();

        $currentPassword = (string) ($_POST['contrasena_actual'] ?? '');
        $newPassword = (string) ($_POST['nueva_contrasena'] ?? '');
        $confirmation = (string) ($_POST['confirmar_contrasena'] ?? '');

        $statement = Database::connection()->prepare(
            'SELECT contrasena_hash
             FROM usuarios
             WHERE id_usuario = ?'
        );
        $statement->execute([Auth::user()['id_usuario']]);
        $currentHash = (string) $statement->fetchColumn();

        if (!password_verify($currentPassword, $currentHash)) {
            flash('error', 'La contraseña actual no es correcta.');
            redirect('/perfil');
        }

        if (strlen($newPassword) < 8 || $newPassword !== $confirmation) {
            flash(
                'error',
                'La nueva contraseña debe tener 8 caracteres y coincidir con la confirmación.'
            );
            redirect('/perfil');
        }

        Database::connection()
            ->prepare(
                'UPDATE usuarios
                 SET contrasena_hash = ?, ultima_actualizacion = NOW()
                 WHERE id_usuario = ?'
            )
            ->execute([
                password_hash($newPassword, PASSWORD_DEFAULT),
                Auth::user()['id_usuario'],
            ]);

        session_regenerate_id(true);
        flash('success', 'Contraseña actualizada correctamente.');
        redirect('/perfil');
    }

    public function photo(string $name): void
    {
        Auth::requireLogin();

        if (!preg_match('/^[a-f0-9]{40}\.(jpg|png|webp)$/', $name)) {
            http_response_code(404);
            return;
        }

        $directory = (string) Env::get('PROFILE_DIR', '')
            ?: APP_ROOT . '/storage/profiles';
        $file = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $name;

        if (!is_file($file)) {
            http_response_code(404);
            return;
        }

        $mime = class_exists(\finfo::class)
            ? (new \finfo(FILEINFO_MIME_TYPE))->file($file)
            : (@getimagesize($file)['mime'] ?? 'application/octet-stream');

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: private, max-age=3600');

        readfile($file);
        exit;
    }
}
