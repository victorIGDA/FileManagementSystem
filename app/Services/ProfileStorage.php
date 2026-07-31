<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use finfo;
use InvalidArgumentException;
use RuntimeException;

final class ProfileStorage
{
    private const TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function store(array $file): string
    {
        $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($uploadError !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException(
                'No fue posible cargar la fotografía.'
            );
        }

        $maximumBytes = (int) Env::get('MAX_PROFILE_MB', 3)
            * 1024
            * 1024;

        if ((int) $file['size'] > $maximumBytes) {
            throw new InvalidArgumentException(
                'La fotografía supera el límite permitido.'
            );
        }

        $imageInformation = @getimagesize($file['tmp_name']);
        $mime = class_exists(finfo::class)
            ? (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name'])
            : ($imageInformation['mime'] ?? '');

        if (!isset(self::TYPES[$mime]) || $imageInformation === false) {
            throw new InvalidArgumentException(
                'La fotografía debe ser JPG, PNG o WEBP válida.'
            );
        }

        $directory = (string) Env::get('PROFILE_DIR', '')
            ?: APP_ROOT . '/storage/profiles';
        $directoryCreated = is_dir($directory)
            || mkdir($directory, 0750, true);

        if (!$directoryCreated || !is_dir($directory)) {
            throw new RuntimeException(
                'No se pudo crear el directorio de perfiles.'
            );
        }

        $name = bin2hex(random_bytes(20))
            . '.'
            . self::TYPES[$mime];
        $destination = rtrim($directory, '/\\')
            . DIRECTORY_SEPARATOR
            . $name;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException(
                'No se pudo guardar la fotografía.'
            );
        }

        return $name;
    }
}
