<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use PDOException;

final class CategoryController
{
    public function index(): void
    {
        Auth::requirePermission('*');

        $categories = Database::connection()
            ->query('SELECT * FROM categorias ORDER BY nombre')
            ->fetchAll();

        View::render('categories/index', [
            'categories' => $categories,
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('*');

        View::render('categories/form', [
            'category' => null,
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
             FROM categorias
             WHERE id_categoria = ?'
        );
        $statement->execute([(int) $id]);
        $category = $statement->fetch();

        if (!$category) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        View::render('categories/form', [
            'category' => $category,
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

        Database::connection()
            ->prepare(
                'UPDATE categorias
                 SET estado = 1 - estado
                 WHERE id_categoria = ?'
            )
            ->execute([(int) $id]);

        flash('success', 'Estado de la categoría actualizado.');
        redirect('/categorias');
    }

    private function save(?int $id = null): void
    {
        $name = trim((string) ($_POST['nombre'] ?? ''));
        $description = trim((string) ($_POST['descripcion'] ?? ''));

        if ($name === '' || mb_strlen($name) > 100) {
            flash(
                'error',
                'El nombre es obligatorio y admite hasta 100 caracteres.'
            );
            remember_input($_POST);
            redirect($id ? "/categorias/{$id}/editar" : '/categorias/crear');
        }

        try {
            $database = Database::connection();

            if ($id) {
                $database
                    ->prepare(
                        'UPDATE categorias
                         SET nombre = ?, descripcion = ?
                         WHERE id_categoria = ?'
                    )
                    ->execute([$name, $description ?: null, $id]);
            } else {
                $database
                    ->prepare(
                        'INSERT INTO categorias (nombre, descripcion)
                         VALUES (?, ?)'
                    )
                    ->execute([$name, $description ?: null]);
            }

            clear_old();
            flash('success', 'Categoría guardada correctamente.');
            redirect('/categorias');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                flash('error', 'Ya existe una categoría con ese nombre.');
                remember_input($_POST);
                redirect($id ? "/categorias/{$id}/editar" : '/categorias/crear');
            }

            throw $exception;
        }
    }
}
