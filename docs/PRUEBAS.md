# Matriz de pruebas y evidencia

| ID | Prueba | Evidencia esperada |
|---|---|---|
| CP-01/02 | Login válido e inválido | Redirección al panel / mensaje genérico |
| CP-03 | POST de audio sin permiso | HTTP 403 y sin inserción |
| CP-04 | MP3 válido | Archivo y dos registros confirmados en transacción |
| CP-05 | Mismo contenido con otro nombre | Rechazo por SHA-256 y referencia al registro existente |
| CP-06 | Archivo renombrado o MIME inválido | Rechazo antes de moverlo |
| CP-07 | Título y categoría | Solo coincidencias y paginación correcta |
| CP-08 | Primer evento play | Audio por Range y fila en historial |
| CP-09 | JPG/PNG/WEBP válido | Imagen nueva visible mediante ruta protegida |
| CP-10 | Cambio de clave | Verifica clave actual; la anterior deja de autenticar |
| CP-11 | Dashboard | Conteos iguales a consultas SQL |
| CP-12 | Responsive | Navegación usable a 360, 768 y 1440 px |
| CP-13 | Entrada `' OR 1=1 --` | Sin alteración por consultas preparadas |
| CP-14 | Texto `<script>` | Se muestra escapado, no se ejecuta |
| CP-15 | Eliminación | `estado=0`, oculto en listados e historial intacto |

Antes de entregar en hosting deben capturarse evidencias visuales con datos reales del entorno objetivo. No se incluyen capturas simuladas.

