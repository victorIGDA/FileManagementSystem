# Decisiones técnicas y conformidad con el SRS

## Decisiones

- Se implementó MVC ligero sin framework para maximizar la compatibilidad con hosting PHP 8/MySQL y evitar dependencias de Composer.
- El control de acceso usa RBAC persistido en `permisos` y `rol_permisos`. El permiso `*` representa acceso administrativo completo; los demás pueden asignarse desde la interfaz.
- Los audios y fotos permanecen en `storage`, protegido por `.htaccess`, y se entregan mediante controladores autenticados. `UPLOAD_DIR` y `PROFILE_DIR` permiten ubicarlos fuera del web root en producción.
- La reproducción soporta solicitudes HTTP Range y el evento estadístico se registra al primer evento `play` de cada carga de página.
- La duración se deja editable porque el hosting objetivo no garantiza FFmpeg ni una biblioteca de análisis de audio.
- La validación usa `finfo` cuando está disponible. Incluye una validación estricta de firmas MP3/WAV como respaldo para instalaciones locales de MAMP sin `fileinfo`; producción debe habilitar la extensión.
- Bootstrap se carga desde CDN. Para una instalación sin Internet debe descargarse Bootstrap 5.3 y reemplazarse la referencia del layout.

## Desviaciones

No se cambió el alcance ni el modelo principal solicitado. Se agregaron tablas auxiliares para RBAC y limitación de intentos de acceso, ambas exigidas por los requisitos de extensibilidad y seguridad.

## Pendiente del entorno de producción

- Activar HTTPS y `APP_SECURE_COOKIE=true`.
- Establecer límites PHP coherentes con `MAX_AUDIO_MB`.
- Programar la copia de seguridad de MySQL y `storage`.
- Verificar SMTP no aplica: el SRS no solicita recuperación de contraseña por correo.
