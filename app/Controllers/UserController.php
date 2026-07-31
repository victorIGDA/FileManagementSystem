<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use PDOException;
use Throwable;

final class UserController
{
    public function index(): void
    {
        Auth::requirePermission('*');

        $query = trim((string) ($_GET['q'] ?? ''));
        $like = "%{$query}%";

        $statement = Database::connection()->prepare(
            'SELECT u.*, r.nombre AS rol, p.nombre_completo
             FROM usuarios u
             JOIN roles r ON r.id_rol = u.id_rol
             LEFT JOIN perfiles_usuarios p ON p.id_usuario = u.id_usuario
             WHERE u.nombre_usuario LIKE ?
                OR u.correo LIKE ?
                OR p.nombre_completo LIKE ?
             ORDER BY u.fecha_registro DESC'
        );
        $statement->execute([$like, $like, $like]);

        View::render('users/index', [
            'users' => $statement->fetchAll(),
            'q' => $query,
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('*');

        View::render('users/form', [
            'account' => null,
            'roles' => $this->roles(),
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('*');
        require_csrf();

        $this->save();
    }

    public function edit(string $id): void
    {
        Auth::requirePermission('*');

        $account = $this->find((int) $id);

        if (!$account) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        View::render('users/form', [
            'account' => $account,
            'roles' => $this->roles(),
        ]);
    }

    public function update(string $id): void
    {
        Auth::requirePermission('*');
        require_csrf();

        $this->save((int) $id);
    }

    public function toggle(string $id): void
    {
        Auth::requirePermission('*');
        require_csrf();

        if ((int) $id === (int) Auth::user()['id_usuario']) {
            flash('error', 'No puedes desactivar tu propia cuenta.');
            redirect('/usuarios');
        }

        Database::connection()
            ->prepare(
                'UPDATE usuarios
                 SET estado = 1 - estado, ultima_actualizacion = NOW()
                 WHERE id_usuario = ?'
            )
            ->execute([(int) $id]);

        flash('success', 'Estado de la cuenta actualizado.');
        redirect('/usuarios');
    }

    public function resetPassword(string $id): void
    {
        Auth::requirePermission('*');
        require_csrf();

        $password = (string) ($_POST['nueva_contrasena'] ?? '');

        if (strlen($password) < 8) {
            flash('error', 'La contraseña temporal debe tener al menos 8 caracteres.');
            redirect('/usuarios/' . $id . '/editar');
        }

        Database::connection()
            ->prepare(
                'UPDATE usuarios
                 SET contrasena_hash = ?, ultima_actualizacion = NOW()
                 WHERE id_usuario = ?'
            )
            ->execute([
                password_hash($password, PASSWORD_DEFAULT),
                (int) $id,
            ]);

        flash('success', 'Contraseña restablecida correctamente.');
        redirect('/usuarios/' . $id . '/editar');
    }

    private function save(?int $id = null): void
    {
        $username = trim((string) ($_POST['nombre_usuario'] ?? ''));
        $email = mb_strtolower(trim((string) ($_POST['correo'] ?? '')));
        $name = trim((string) ($_POST['nombre_completo'] ?? ''));
        $phone = trim((string) ($_POST['telefono'] ?? ''));
        $role = (int) ($_POST['id_rol'] ?? 0);
        $password = (string) ($_POST['contrasena'] ?? '');

        $invalidData = $username === ''
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || $name === ''
            || !$role
            || (!$id && strlen($password) < 8);

        if ($invalidData) {
            flash(
                'error',
                'Completa los campos correctamente; la contraseña inicial requiere 8 caracteres.'
            );
            remember_input($_POST);
            redirect($id ? "/usuarios/{$id}/editar" : '/usuarios/crear');
        }

        $database = Database::connection();

        try {
            $database->beginTransaction();

            if ($id) {
                $database
                    ->prepare(
                        'UPDATE usuarios
                         SET id_rol = ?,
                             nombre_usuario = ?,
                             correo = ?,
                             ultima_actualizacion = NOW()
                         WHERE id_usuario = ?'
                    )
                    ->execute([$role, $username, $email, $id]);

                $database
                    ->prepare(
                        'UPDATE perfiles_usuarios
                         SET nombre_completo = ?,
                             telefono = ?,
                             fecha_actualizacion = NOW()
                         WHERE id_usuario = ?'
                    )
                    ->execute([$name, $phone ?: null, $id]);
            } else {
                $database
                    ->prepare(
                        'INSERT INTO usuarios (
                            id_rol,
                            nombre_usuario,
                            correo,
                            contrasena_hash
                         ) VALUES (?, ?, ?, ?)'
                    )
                    ->execute([
                        $role,
                        $username,
                        $email,
                        password_hash($password, PASSWORD_DEFAULT),
                    ]);

                $newUserId = (int) $database->lastInsertId();

                $database
                    ->prepare(
                        'INSERT INTO perfiles_usuarios (
                            id_usuario,
                            nombre_completo,
                            telefono
                         ) VALUES (?, ?, ?)'
                    )
                    ->execute([$newUserId, $name, $phone ?: null]);
            }

            $database->commit();
            clear_old();
            flash('success', 'Usuario guardado correctamente.');
            redirect('/usuarios');
        } catch (PDOException $exception) {
            if ($database->inTransaction()) {
                $database->rollBack();
            }

            if ($exception->getCode() === '23000') {
                flash('error', 'El usuario o correo ya está registrado.');
                remember_input($_POST);
                redirect($id ? "/usuarios/{$id}/editar" : '/usuarios/crear');
            }

            throw $exception;
        } catch (Throwable $exception) {
            if ($database->inTransaction()) {
                $database->rollBack();
            }

            throw $exception;
        }
    }

    private function roles(): array
    {
        $roles = Database::connection()
            ->query(
                'SELECT id_rol, nombre
                 FROM roles
                 WHERE estado = 1
                 ORDER BY nombre'
            )
            ->fetchAll();

        foreach ($roles as &$role) {
            $role['id_rol'] = (int) $role['id_rol'];
        }

        return $roles;
    }

    private function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT u.*, p.nombre_completo, p.telefono
             FROM usuarios u
             LEFT JOIN perfiles_usuarios p ON p.id_usuario = u.id_usuario
             WHERE u.id_usuario = ?'
        );
        $statement->execute([$id]);

        return $statement->fetch() ?: null;
    }
}
