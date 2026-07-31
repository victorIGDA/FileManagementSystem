<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

final class DashboardController
{
    public function index(): void
    {
        Auth::requireLogin();

        $database = Database::connection();
        $stats = [
            'audios' => (int) $database
                ->query(
                    'SELECT COUNT(*)
                     FROM archivos_audio
                     WHERE estado = 1'
                )
                ->fetchColumn(),
            'categorias' => (int) $database
                ->query(
                    'SELECT COUNT(*)
                     FROM categorias
                     WHERE estado = 1'
                )
                ->fetchColumn(),
            'usuarios' => (int) $database
                ->query(
                    'SELECT COUNT(*)
                     FROM usuarios
                     WHERE estado = 1'
                )
                ->fetchColumn(),
            'reproducciones' => (int) $database
                ->query(
                    'SELECT COUNT(*)
                     FROM historial_reproducciones
                     WHERE fecha_reproduccion >= DATE_SUB(NOW(), INTERVAL 30 DAY)'
                )
                ->fetchColumn(),
        ];

        $categories = $database
            ->query(
                'SELECT c.nombre,
                        COUNT(a.id_audio) AS total
                 FROM categorias c
                 LEFT JOIN archivos_audio a
                    ON a.id_categoria = c.id_categoria
                   AND a.estado = 1
                 WHERE c.estado = 1
                 GROUP BY c.id_categoria
                 ORDER BY total DESC'
            )
            ->fetchAll();

        $recent = $database
            ->query(
                'SELECT a.id_audio,
                        m.titulo,
                        c.nombre AS categoria,
                        a.fecha_registro
                 FROM archivos_audio a
                 JOIN metadatos_audio m ON m.id_audio = a.id_audio
                 JOIN categorias c ON c.id_categoria = a.id_categoria
                 WHERE a.estado = 1
                 ORDER BY a.fecha_registro DESC
                 LIMIT 6'
            )
            ->fetchAll();

        View::render('dashboard', compact(
            'stats',
            'categories',
            'recent'
        ));
    }
}
