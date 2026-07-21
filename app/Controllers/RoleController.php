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
        $roles = Database::connection()->query(
            'SELECT r.*,COUNT(DISTINCT u.id_usuario) usuarios,COUNT(DISTINCT rp.id_permiso) permisos
             FROM roles r LEFT JOIN usuarios u ON u.id_rol=r.id_rol
             LEFT JOIN rol_permisos rp ON rp.id_rol=r.id_rol
             GROUP BY r.id_rol ORDER BY r.nombre'
        )->fetchAll();
        View::render('roles/index', compact('roles'));
    }

    public function create(): void
    {
        Auth::requirePermission('*');
        View::render('roles/form', ['role' => null, 'selected' => [], 'permissions' => $this->permissions()]);
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
        $stmt = Database::connection()->prepare('SELECT * FROM roles WHERE id_rol=?');
        $stmt->execute([(int) $id]);
        $role = $stmt->fetch();
        if (!$role) { http_response_code(404); View::render('errors/404'); return; }
        $stmt = Database::connection()->prepare('SELECT id_permiso FROM rol_permisos WHERE id_rol=?');
        $stmt->execute([(int) $id]);
        $selected = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        $permissions = $this->permissions();
        View::render('roles/form', compact('role', 'selected', 'permissions'));
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
        if ((int) $id === 1) { flash('error', 'El rol Administrador no puede desactivarse.'); redirect('/roles'); }
        Database::connection()->prepare('UPDATE roles SET estado=1-estado WHERE id_rol=?')->execute([(int) $id]);
        flash('success', 'Estado del rol actualizado.');
        redirect('/roles');
    }

    private function save(?int $id = null): void
    {
        $name = trim((string) ($_POST['nombre'] ?? ''));
        $permissions = array_values(array_unique(array_map('intval', (array) ($_POST['permisos'] ?? []))));
        if ($id === 1) { $name = 'Administrador'; $permissions = [1]; }
        if ($name === '' || mb_strlen($name) > 50) {
            flash('error', 'El nombre del rol es obligatorio.');
            redirect($id ? "/roles/{$id}/editar" : '/roles/crear');
        }
        $db = Database::connection();
        try {
            $db->beginTransaction();
            if ($id) {
                $db->prepare('UPDATE roles SET nombre=?,descripcion=? WHERE id_rol=?')->execute([$name, trim((string) ($_POST['descripcion'] ?? '')) ?: null, $id]);
                $db->prepare('DELETE FROM rol_permisos WHERE id_rol=?')->execute([$id]);
                $roleId = $id;
            } else {
                $db->prepare('INSERT INTO roles(nombre,descripcion) VALUES(?,?)')->execute([$name, trim((string) ($_POST['descripcion'] ?? '')) ?: null]);
                $roleId = (int) $db->lastInsertId();
            }
            $insert = $db->prepare('INSERT INTO rol_permisos(id_rol,id_permiso) VALUES(?,?)');
            foreach ($permissions as $permission) $insert->execute([$roleId, $permission]);
            $db->commit();
            flash('success', 'Rol guardado correctamente.');
            redirect('/roles');
        } catch (PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            if ($e->getCode() === '23000') {
                flash('error', 'El nombre del rol ya existe.');
                redirect($id ? "/roles/{$id}/editar" : '/roles/crear');
            }
            throw $e;
        }
    }

    private function permissions(): array
    {
        return Database::connection()->query('SELECT * FROM permisos ORDER BY id_permiso')->fetchAll();
    }
}

