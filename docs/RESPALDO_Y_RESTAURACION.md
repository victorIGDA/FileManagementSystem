# Respaldo y restauración

## Respaldo

Ejecutar diariamente, desde una cuenta con acceso mínimo:

```bash
mysqldump --single-transaction --routines --triggers -u USUARIO -p gestor_audio > gestor_audio_YYYY-MM-DD.sql
tar -czf storage_YYYY-MM-DD.tar.gz storage/audio storage/profiles
```

Guardar ambos archivos juntos, cifrados, fuera del hosting. Conservar al menos 7 copias diarias, 4 semanales y 6 mensuales. Comprobar mensualmente que los respaldos puedan abrirse.

## Restauración

1. Poner la aplicación en mantenimiento y crear una base vacía con `utf8mb4`.
2. Importar: `mysql -u USUARIO -p gestor_audio < gestor_audio_FECHA.sql`.
3. Restaurar `storage/audio` y `storage/profiles` preservando los nombres.
4. Corregir `UPLOAD_DIR`, `PROFILE_DIR` y permisos de lectura/escritura.
5. Probar login, búsqueda, reproducción y carga de un audio de prueba.
6. Retirar el modo de mantenimiento después de revisar los logs.

