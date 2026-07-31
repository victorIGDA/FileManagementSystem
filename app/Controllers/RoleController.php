<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use PDO;
use PDOException;

final class RoleController
{
    public function index(): void
    {
        Auth::requirePermission('*');

        $roles = Database::connection()
            ->query(
                'SELECT r.*,
                        COUNT(DISTINCT u.id_usuario) AS usuarios,
                        COUNT(DISTINCT rp.id_permiso) AS permisos
                 FROM roles r
                 LEFT JOIN usuarios u ON u.id_rol = r.id_rol
                 LEFT JOIN rol_permisos rp ON rp.id_rol = r.id_rol
                 GROUP BY r.id_rol
                 ORDER BY r.nombre'
            )
            ->fetchAll();

        View::render('roles/index', [
            'roles' => $roles,
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('*');

        View::render('roles/form', [
            'role' => null,
            'selected' => [],
            'permissions' => $this->permissions(),
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

        $statement = Database::connection()->prepare(
            'SELECT *
             FROM roles
             WHERE id_rol = ?'
        );
        $statement->execute([(int) $id]);
        $role = $statement->fetch();

        if (!$role) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        $statement = Database::connection()->prepare(
            'SELECT id_permiso
             FROM rol_permisos
             WHERE id_rol = ?'
        );
        $statement->execute([(int) $id]);

        $selected = array_map(
            'intval',
            $statement->fetchAll(PDO::FETCH_COLUMN)
        );

        View::render('roles/form', [
            'role' => $role,
            'selected' => $selected,
            'permissions' => $this->permissions(),
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

        if ((int) $id === 1) {
            flash(
                'error',
                'El rol Administrador no puede desactivarse.'
            );
            redirect('/roles');
        }

        Database::connection()
            ->prepare(
                'UPDATE roles
                 SET estado = 1 - estado
                 WHERE id_rol = ?'
            )
            ->execute([(int) $id]);

        flash('success', 'Estado del rol actualizado.');
        redirect('/roles');
    }

    private function save(?int $id = null): void
    {
        $name = trim((string) ($_POST['nombre'] ?? ''));
        $description = trim(
            (string) ($_POST['descripcion'] ?? '')
        );
        $permissions = array_values(
            array_unique(
                array_map(
                    'intval',
                    (array) ($_POST['permisos'] ?? [])
                )
            )
        );

        if ($id === 1) {
            $name = 'Administrador';
            $permissions = [1];
        }

        if ($name === '' || mb_strlen($name) > 50) {
            flash('error', 'El nombre del rol es obligatorio.');
            redirect($id ? "/roles/{$id}/editar" : '/roles/crear');
        }

        $database = Database::connection();

        try {
            $database->beginTransaction();

            if ($id) {
                $database
                    ->prepare(
                        'UPDATE roles
                         SET nombre = ?, descripcion = ?
                         WHERE id_rol = ?'
                    )
                    ->execute([
                        $name,
                        $description ?: null,
                        $id,
                    ]);

                $database
                    ->prepare(
                        'DELETE FROM rol_permisos
                         WHERE id_rol = ?'
                    )
                    ->execute([$id]);

                $roleId = $id;
            } else {
                $database
                    ->prepare(
                        'INSERT INTO roles (nombre, descripcion)
                         VALUES (?, ?)'
                    )
                    ->execute([
                        $name,
                        $description ?: null,
                    ]);

                $roleId = (int) $database->lastInsertId();
            }

            $insertPermission = $database->prepare(
                'INSERT INTO rol_permisos (id_rol, id_permiso)
                 VALUES (?, ?)'
            );

            foreach ($permissions as $permission) {
                $insertPermission->execute([
                    $roleId,
                    $permission,
                ]);
            }

            $database->commit();
            flash('success', 'Rol guardado correctamente.');
            redirect('/roles');
        } catch (PDOException $exception) {
            if ($database->inTransaction()) {
                $database->rollBack();
            }

            if ($exception->getCode() === '23000') {
                flash('error', 'El nombre del rol ya existe.');
                redirect(
                    $id
                        ? "/roles/{$id}/editar"
                        : '/roles/crear'
                );
            }

            throw $exception;
        }
    }

    private function permissions(): array
    {
        return Database::connection()
            ->query(
                'SELECT *
                 FROM permisos
                 ORDER BY id_permiso'
            )
            ->fetchAll();
    }
}
