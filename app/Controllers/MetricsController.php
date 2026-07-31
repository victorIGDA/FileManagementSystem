<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use DateTime;

final class MetricsController
{
    public function index(): void
    {
        Auth::requirePermission('metricas.ver');

        $from = $this->date(
            $_GET['desde'] ?? null,
            date('Y-m-d', strtotime('-30 days'))
        );
        $to = $this->date(
            $_GET['hasta'] ?? null,
            date('Y-m-d')
        );

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $database = Database::connection();
        $range = [
            $from . ' 00:00:00',
            $to . ' 23:59:59',
        ];

        $statement = $database->prepare(
            'SELECT COUNT(*)
             FROM historial_reproducciones
             WHERE fecha_reproduccion BETWEEN ? AND ?'
        );
        $statement->execute($range);
        $total = (int) $statement->fetchColumn();

        $statement = $database->prepare(
            'SELECT a.id_audio,
                    m.titulo,
                    COUNT(h.id_reproduccion) AS total
             FROM historial_reproducciones h
             JOIN archivos_audio a ON a.id_audio = h.id_audio
             JOIN metadatos_audio m ON m.id_audio = a.id_audio
             WHERE h.fecha_reproduccion BETWEEN ? AND ?
             GROUP BY a.id_audio, m.titulo
             ORDER BY total DESC
             LIMIT 10'
        );
        $statement->execute($range);
        $top = $statement->fetchAll();

        $statement = $database->prepare(
            'SELECT c.nombre,
                    COUNT(h.id_reproduccion) AS total
             FROM historial_reproducciones h
             JOIN archivos_audio a ON a.id_audio = h.id_audio
             JOIN categorias c ON c.id_categoria = a.id_categoria
             WHERE h.fecha_reproduccion BETWEEN ? AND ?
             GROUP BY c.id_categoria, c.nombre
             ORDER BY total DESC'
        );
        $statement->execute($range);
        $categories = $statement->fetchAll();

        $statement = $database->prepare(
            'SELECT DATE(fecha_reproduccion) AS fecha,
                    COUNT(*) AS total
             FROM historial_reproducciones
             WHERE fecha_reproduccion BETWEEN ? AND ?
             GROUP BY DATE(fecha_reproduccion)
             ORDER BY fecha'
        );
        $statement->execute($range);
        $trend = $statement->fetchAll();

        $uploads = $database
            ->query(
                "SELECT DATE_FORMAT(fecha_registro, '%Y-%m') AS mes,
                        COUNT(*) AS total
                 FROM archivos_audio
                 GROUP BY DATE_FORMAT(fecha_registro, '%Y-%m')
                 ORDER BY mes DESC
                 LIMIT 12"
            )
            ->fetchAll();

        View::render('metrics/index', compact(
            'from',
            'to',
            'total',
            'top',
            'categories',
            'trend',
            'uploads'
        ));
    }

    private function date(mixed $value, string $fallback): string
    {
        $date = DateTime::createFromFormat('Y-m-d', (string) $value);

        return $date && $date->format('Y-m-d') === $value
            ? $date->format('Y-m-d')
            : $fallback;
    }
}
