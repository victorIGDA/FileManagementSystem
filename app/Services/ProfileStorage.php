<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Env;

final class ProfileStorage
{
    private const TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public function store(array $file): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new \InvalidArgumentException('No fue posible cargar la fotografía.');
        if ((int) $file['size'] > (int) Env::get('MAX_PROFILE_MB', 3) * 1024 * 1024) throw new \InvalidArgumentException('La fotografía supera el límite permitido.');
        $image = @getimagesize($file['tmp_name']);
        $mime = class_exists(\finfo::class) ? (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : ($image['mime'] ?? '');
        if (!isset(self::TYPES[$mime]) || $image === false) throw new \InvalidArgumentException('La fotografía debe ser JPG, PNG o WEBP válida.');
        $dir = (string) Env::get('PROFILE_DIR', '') ?: APP_ROOT . '/storage/profiles';
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) throw new \RuntimeException('No se pudo crear el directorio de perfiles.');
        $name = bin2hex(random_bytes(20)) . '.' . self::TYPES[$mime];
        if (!move_uploaded_file($file['tmp_name'], rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $name)) throw new \RuntimeException('No se pudo guardar la fotografía.');
        return $name;
    }
}
