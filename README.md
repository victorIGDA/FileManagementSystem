# Sistema gestor de archivos de audio

Aplicación web para Arca de Salvación Radio 95.3 FM. Centraliza archivos MP3/WAV, metadatos, usuarios, roles, reproducciones y métricas según el SRS versión 1.0.

## Requisitos

- PHP 8.1 o superior con PDO MySQL, fileinfo y mbstring.
- MySQL 8.x o MariaDB compatible, Apache con `mod_rewrite` y HTTPS en producción.
- Límites `upload_max_filesize` y `post_max_size` iguales o superiores a `MAX_AUDIO_MB`.

## Instalación local

1. Copia `.env.example` como `.env` y configura `APP_URL` y MySQL.
2. Crea una base con `utf8mb4` e importa, en orden, `database/schema.sql` y `database/seed.sql`.
3. Crea el administrador inicial desde terminal (la clave no queda en el repositorio):

   ```bash
   php scripts/create_admin.php admin admin@ejemplo.com "Administrador" "UnaClaveSegura123!"
   ```

4. Da escritura al proceso web sobre `storage/audio`, `storage/profiles` y `storage/logs`.
5. Abre la URL configurada, normalmente `http://localhost/FileManagementSystem/public`.

En MAMP para Windows puede usarse `C:\MAMP\bin\php\php8.3.1\php.exe` en lugar de `php`.

## Módulos incluidos

- Login con mensajes genéricos, sesiones seguras y limitación de intentos.
- Usuarios, roles, permisos y activación/desactivación lógica.
- Categorías con nombre único y estado.
- Audios con metadatos, búsqueda, filtros, paginación, MIME real, SHA-256 y transacciones.
- Reproductor protegido con Range y registro de reproducciones.
- Dashboard, métricas por rango, archivos recientes y carga mensual.
- Perfil, fotografía validada y cambio de contraseña con verificación actual.
- CSRF, PDO preparado, escape XSS y almacenamiento protegido.

## Comprobaciones

```bash
php tests/smoke.php
php -l public/index.php
```

La matriz manual está en [docs/PRUEBAS.md](docs/PRUEBAS.md). Las decisiones y cualquier adaptación están en [docs/DECISIONES_TECNICAS.md](docs/DECISIONES_TECNICAS.md).

El reporte técnico-funcional integral está disponible en [docs/REPORTE_COMPLETO_SISTEMA.pdf](docs/REPORTE_COMPLETO_SISTEMA.pdf), con una versión HTML editable en el mismo directorio.

## Producción

Configura el document root hacia `public`, coloca `storage` fuera de la ruta pública si el hosting lo permite, usa un usuario MySQL con privilegios mínimos y activa `APP_ENV=production`, `APP_SECURE_COOKIE=true` y HTTPS. Nunca publiques `.env`.

Consulta [docs/RESPALDO_Y_RESTAURACION.md](docs/RESPALDO_Y_RESTAURACION.md) antes del despliegue.
# FileManagementSystem
