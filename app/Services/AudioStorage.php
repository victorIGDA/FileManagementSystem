<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Env;

final class AudioStorage
{
    private const TYPES = ['audio/mpeg' => 'mp3', 'audio/mp3' => 'mp3', 'audio/wav' => 'wav', 'audio/x-wav' => 'wav', 'audio/wave' => 'wav'];

    public function validate(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException($this->uploadError((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE)));
        }
        $max = max(1, (int) Env::get('MAX_AUDIO_MB', 100)) * 1024 * 1024;
        if ((int) $file['size'] > $max) throw new \InvalidArgumentException('El audio supera el límite de ' . Env::get('MAX_AUDIO_MB', 100) . ' MB.');
        $mime = $this->detectMime($file['tmp_name']);
        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!isset(self::TYPES[$mime]) || self::TYPES[$mime] !== $extension) {
            throw new \InvalidArgumentException('Solo se permiten archivos MP3 o WAV válidos.');
        }
        return ['mime' => $mime, 'extension' => $extension, 'hash' => hash_file('sha256', $file['tmp_name'])];
    }

    public function store(array $file, string $extension): array
    {
        $dir = (string) Env::get('UPLOAD_DIR', '') ?: APP_ROOT . '/storage/audio';
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) throw new \RuntimeException('No se pudo crear el directorio de audio.');
        $name = bin2hex(random_bytes(20)) . '.' . $extension;
        $destination = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $name;
        if (!move_uploaded_file($file['tmp_name'], $destination)) throw new \RuntimeException('No se pudo almacenar el archivo.');
        return ['name' => $name, 'path' => $destination];
    }

    public function remove(string $path): void { if (is_file($path)) @unlink($path); }

    private function detectMime(string $path): string
    {
        if (class_exists(\finfo::class)) {
            return (string) (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        }
        $header = file_get_contents($path, false, null, 0, 12) ?: '';
        if (strlen($header) >= 12 && substr($header, 0, 4) === 'RIFF' && substr($header, 8, 4) === 'WAVE') return 'audio/x-wav';
        if (str_starts_with($header, 'ID3') || (strlen($header) >= 2 && ord($header[0]) === 0xff && (ord($header[1]) & 0xe0) === 0xe0)) return 'audio/mpeg';
        return 'application/octet-stream';
    }

    private function uploadError(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el límite permitido por el servidor.',
            UPLOAD_ERR_NO_FILE => 'Debes seleccionar un archivo de audio.',
            default => 'La carga del archivo no pudo completarse.'
        };
    }
}
