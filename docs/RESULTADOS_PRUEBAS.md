# Resultados de pruebas

Ejecución local: 21 de julio de 2026, MAMP con PHP 8.3.1, Apache y MySQL.

| Comprobación | Resultado |
|---|---|
| Análisis de todos los archivos PHP | 0 errores |
| Pruebas base de hash, contraseñas, escape y extensiones | OK |
| Importación reproducible | 10 tablas, 2 roles y 5 categorías |
| Login real con CSRF y sesión | HTTP 200, redirección al dashboard |
| Dashboard, audios, categorías, usuarios, roles, métricas y perfil | HTTP 200 en cada módulo |
| Carga WAV válida | HTTP 200 y registros persistidos |
| Entrega parcial de audio | HTTP 206 ante `Range: bytes=0-99` |
| Evento de reproducción | HTTP 200 y una fila de historial |
| Segundo archivo con el mismo contenido | Rechazado por SHA-256 |
| Limpieza de fixture | Archivo físico y filas temporales eliminados |

La cuenta administrativa y los datos iniciales permanecen disponibles. El audio sintético usado por la prueba fue eliminado para no contaminar el inventario.

