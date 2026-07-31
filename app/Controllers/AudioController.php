<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Services\AudioStorage;
use InvalidArgumentException;
use Throwable;

final class AudioController
{
    private const PAGE_SIZE = 15;

    public function index(): void
    {
        Auth::requirePermission('audios.ver');

        $database = Database::connection();
        $query = trim((string) ($_GET['q'] ?? ''));
        $category = (int) ($_GET['categoria'] ?? 0);
        $page = max(1, (int) ($_GET['pagina'] ?? 1));
        $offset = ($page - 1) * self::PAGE_SIZE;
        $conditions = ['a.estado = 1'];
        $parameters = [];

        if ($query !== '') {
            $conditions[] = '(
                m.titulo LIKE ?
                OR m.artista LIKE ?
                OR m.locutor LIKE ?
                OR m.cliente LIKE ?
                OR m.palabras_clave LIKE ?
            )';

            $like = "%{$query}%";
            $parameters = array_fill(0, 5, $like);
        }

        if ($category) {
            $conditions[] = 'a.id_categoria = ?';
            $parameters[] = $category;
        }

        $conditionSql = implode(' AND ', $conditions);

        $statement = $database->prepare(
            "SELECT COUNT(*)
             FROM archivos_audio a
             JOIN metadatos_audio m ON m.id_audio = a.id_audio
             WHERE {$conditionSql}"
        );
        $statement->execute($parameters);
        $total = (int) $statement->fetchColumn();

        $statement = $database->prepare(
            "SELECT a.*,
                    m.*,
                    c.nombre AS categoria,
                    p.nombre_completo AS usuario
             FROM archivos_audio a
             JOIN metadatos_audio m ON m.id_audio = a.id_audio
             JOIN categorias c ON c.id_categoria = a.id_categoria
             LEFT JOIN perfiles_usuarios p ON p.id_usuario = a.id_usuario
             WHERE {$conditionSql}
             ORDER BY a.fecha_registro DESC
             LIMIT " . self::PAGE_SIZE . " OFFSET {$offset}"
        );
        $statement->execute($parameters);
        $audios = $statement->fetchAll();

        $categories = $database
            ->query(
                'SELECT id_categoria, nombre
                 FROM categorias
                 WHERE estado = 1
                 ORDER BY nombre'
            )
            ->fetchAll();

        $pages = max(1, (int) ceil($total / self::PAGE_SIZE));

        View::render('audios/index', [
            'audios' => $audios,
            'categories' => $categories,
            'q' => $query,
            'category' => $category,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('audios.crear');

        View::render('audios/form', [
            'audio' => null,
            'categories' => $this->categories(),
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('audios.crear');
        require_csrf();
        $this->validateMetadata();

        $storage = new AudioStorage();
        $database = Database::connection();
        $storedFile = null;

        try {
            $fileInformation = $storage->validate(
                $_FILES['archivo'] ?? []
            );

            $statement = $database->prepare(
                'SELECT a.id_audio, m.titulo
                 FROM archivos_audio a
                 JOIN metadatos_audio m ON m.id_audio = a.id_audio
                 WHERE a.hash_sha256 = ?
                 LIMIT 1'
            );
            $statement->execute([$fileInformation['hash']]);
            $existingAudio = $statement->fetch();

            if ($existingAudio) {
                throw new InvalidArgumentException(
                    'Este contenido ya existe como «'
                    . $existingAudio['titulo']
                    . '» (registro #'
                    . $existingAudio['id_audio']
                    . ').'
                );
            }

            $categoryId = (int) $_POST['id_categoria'];
            $this->ensureCategory($categoryId);

            $storedFile = $storage->store(
                $_FILES['archivo'],
                $fileInformation['extension']
            );

            $database->beginTransaction();
            $database
                ->prepare(
                    'INSERT INTO archivos_audio (
                        id_categoria,
                        id_usuario,
                        nombre_original,
                        nombre_almacenado,
                        ruta_archivo,
                        mime_type,
                        extension,
                        tamano_bytes,
                        duracion_segundos,
                        hash_sha256
                     ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                )
                ->execute([
                    $categoryId,
                    Auth::user()['id_usuario'],
                    basename((string) $_FILES['archivo']['name']),
                    $storedFile['name'],
                    $storedFile['path'],
                    $fileInformation['mime'],
                    $fileInformation['extension'],
                    (int) $_FILES['archivo']['size'],
                    $this->duration(),
                    $fileInformation['hash'],
                ]);

            $audioId = (int) $database->lastInsertId();
            $this->insertMetadata($audioId);

            $database->commit();
            clear_old();
            flash('success', 'Audio cargado correctamente.');
            redirect('/audios');
        } catch (InvalidArgumentException $exception) {
            if ($database->inTransaction()) {
                $database->rollBack();
            }

            if ($storedFile) {
                $storage->remove($storedFile['path']);
            }

            flash('error', $exception->getMessage());
            remember_input($_POST);
            redirect('/audios/crear');
        } catch (Throwable $exception) {
            if ($database->inTransaction()) {
                $database->rollBack();
            }

            if ($storedFile) {
                $storage->remove($storedFile['path']);
            }

            error_log($exception->getMessage());
            flash('error', 'No fue posible guardar el audio.');
            remember_input($_POST);
            redirect('/audios/crear');
        }
    }

    public function show(string $id): void
    {
        Auth::requirePermission('audios.ver');

        $audio = $this->find((int) $id);

        if (!$audio) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        View::render('audios/show', [
            'audio' => $audio,
        ]);
    }

    public function edit(string $id): void
    {
        Auth::requirePermission('audios.editar');

        $audio = $this->find((int) $id);

        if (!$audio) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        View::render('audios/form', [
            'audio' => $audio,
            'categories' => $this->categories(),
        ]);
    }

    public function update(string $id): void
    {
        Auth::requirePermission('audios.editar');
        require_csrf();

        $audio = $this->find((int) $id);

        if (!$audio) {
            http_response_code(404);
            return;
        }

        $this->validateMetadata();

        $categoryId = (int) $_POST['id_categoria'];
        $this->ensureCategory($categoryId);

        $database = Database::connection();
        $database->beginTransaction();

        try {
            $database
                ->prepare(
                    'UPDATE archivos_audio
                     SET id_categoria = ?,
                         duracion_segundos = ?,
                         fecha_actualizacion = NOW()
                     WHERE id_audio = ?'
                )
                ->execute([
                    $categoryId,
                    $this->duration(),
                    (int) $id,
                ]);

            $database
                ->prepare(
                    'UPDATE metadatos_audio
                     SET titulo = ?,
                         artista = ?,
                         locutor = ?,
                         cliente = ?,
                         palabras_clave = ?,
                         fecha_produccion = ?,
                         descripcion = ?
                     WHERE id_audio = ?'
                )
                ->execute($this->metadataParams((int) $id));

            $database->commit();
            flash('success', 'Audio actualizado correctamente.');
            redirect('/audios/' . $id);
        } catch (Throwable $exception) {
            $database->rollBack();
            error_log($exception->getMessage());
            flash('error', 'No fue posible actualizar el audio.');
            remember_input($_POST);
            redirect('/audios/' . $id . '/editar');
        }
    }

    public function delete(string $id): void
    {
        Auth::requirePermission('audios.eliminar');
        require_csrf();

        Database::connection()
            ->prepare(
                'UPDATE archivos_audio
                 SET estado = 0, fecha_actualizacion = NOW()
                 WHERE id_audio = ?'
            )
            ->execute([(int) $id]);

        flash('success', 'El audio fue eliminado lógicamente.');
        redirect('/audios');
    }

    public function stream(string $id): void
    {
        Auth::requirePermission('audios.ver');

        $statement = Database::connection()->prepare(
            'SELECT *
             FROM archivos_audio
             WHERE id_audio = ? AND estado = 1'
        );
        $statement->execute([(int) $id]);
        $audio = $statement->fetch();

        if (!$audio || !is_file($audio['ruta_archivo'])) {
            http_response_code(404);
            exit;
        }

        $file = $audio['ruta_archivo'];
        $size = filesize($file);
        $start = 0;
        $end = $size - 1;

        header('Content-Type: ' . $audio['mime_type']);
        header('Accept-Ranges: bytes');
        header(
            'Content-Disposition: inline; filename="audio.'
            . $audio['extension']
            . '"'
        );

        $hasRange = isset($_SERVER['HTTP_RANGE'])
            && preg_match(
                '/bytes=(\d*)-(\d*)/',
                $_SERVER['HTTP_RANGE'],
                $matches
            );

        if ($hasRange) {
            $start = $matches[1] !== '' ? (int) $matches[1] : 0;
            $end = $matches[2] !== '' ? (int) $matches[2] : $end;

            if ($start > $end || $end >= $size) {
                http_response_code(416);
                header("Content-Range: bytes */{$size}");
                exit;
            }

            http_response_code(206);
            header("Content-Range: bytes {$start}-{$end}/{$size}");
        }

        $length = $end - $start + 1;

        header('Content-Length: ' . $length);

        $handle = fopen($file, 'rb');
        fseek($handle, $start);
        $remaining = $length;

        while ($remaining > 0 && !feof($handle)) {
            $chunk = fread($handle, min(8192, $remaining));
            echo $chunk;
            $remaining -= strlen($chunk);
            flush();
        }

        fclose($handle);
        exit;
    }

    public function recordPlay(string $id): void
    {
        Auth::requirePermission('audios.ver');
        require_csrf();

        $statement = Database::connection()->prepare(
            'SELECT id_audio
             FROM archivos_audio
             WHERE id_audio = ? AND estado = 1'
        );
        $statement->execute([(int) $id]);

        if (!$statement->fetch()) {
            http_response_code(404);
            return;
        }

        $ipHash = hash(
            'sha256',
            (string) ($_SERVER['REMOTE_ADDR'] ?? '')
        );

        Database::connection()
            ->prepare(
                'INSERT INTO historial_reproducciones (
                    id_audio,
                    id_usuario,
                    ip_hash
                 ) VALUES (?, ?, ?)'
            )
            ->execute([
                (int) $id,
                Auth::user()['id_usuario'],
                $ipHash,
            ]);

        header('Content-Type: application/json');
        echo '{"ok":true}';
    }

    private function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT a.*,
                    m.*,
                    c.nombre AS categoria,
                    p.nombre_completo AS usuario
             FROM archivos_audio a
             JOIN metadatos_audio m ON m.id_audio = a.id_audio
             JOIN categorias c ON c.id_categoria = a.id_categoria
             LEFT JOIN perfiles_usuarios p ON p.id_usuario = a.id_usuario
             WHERE a.id_audio = ? AND a.estado = 1'
        );
        $statement->execute([$id]);

        return $statement->fetch() ?: null;
    }

    private function categories(): array
    {
        $categories = Database::connection()
            ->query(
                'SELECT id_categoria, nombre
                 FROM categorias
                 WHERE estado = 1
                 ORDER BY nombre'
            )
            ->fetchAll();

        foreach ($categories as &$category) {
            $category['id_categoria'] = (int) $category['id_categoria'];
        }

        return $categories;
    }

    private function ensureCategory(int $id): void
    {
        $statement = Database::connection()->prepare(
            'SELECT 1
             FROM categorias
             WHERE id_categoria = ? AND estado = 1'
        );
        $statement->execute([$id]);

        if (!$statement->fetchColumn()) {
            throw new InvalidArgumentException(
                'Selecciona una categoría activa.'
            );
        }
    }

    private function validateMetadata(): void
    {
        $title = trim((string) ($_POST['titulo'] ?? ''));

        if ($title === '' || mb_strlen($title) > 200) {
            flash(
                'error',
                'El título es obligatorio y admite hasta 200 caracteres.'
            );
            remember_input($_POST);

            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            $fallback = str_contains($requestUri, 'editar')
                ? ($requestUri ?: '/audios')
                : '/audios/crear';

            redirect($fallback);
        }
    }

    private function duration(): ?int
    {
        $value = trim((string) ($_POST['duracion_segundos'] ?? ''));

        return $value === ''
            ? null
            : max(0, (int) $value);
    }

    private function metadataParams(int $audioId): array
    {
        return [
            trim((string) $_POST['titulo']),
            trim((string) ($_POST['artista'] ?? '')) ?: null,
            trim((string) ($_POST['locutor'] ?? '')) ?: null,
            trim((string) ($_POST['cliente'] ?? '')) ?: null,
            trim((string) ($_POST['palabras_clave'] ?? '')) ?: null,
            ($_POST['fecha_produccion'] ?? '') ?: null,
            trim((string) ($_POST['descripcion'] ?? '')) ?: null,
            $audioId,
        ];
    }

    private function insertMetadata(int $audioId): void
    {
        Database::connection()
            ->prepare(
                'INSERT INTO metadatos_audio (
                    titulo,
                    artista,
                    locutor,
                    cliente,
                    palabras_clave,
                    fecha_produccion,
                    descripcion,
                    id_audio
                 ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            )
            ->execute($this->metadataParams($audioId));
    }
}
