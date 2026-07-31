# CAPÍTULO V. ANÁLISIS DE RESULTADOS

## 5.1 Resultados obtenidos con el sistema

El desarrollo del sistema gestor de archivos de audio permitió obtener una plataforma web funcional para centralizar, organizar, consultar y proteger los recursos sonoros de Arca de Salvación Radio 95.3 FM. La solución integra en una misma interfaz la autenticación, la administración de usuarios, la clasificación por categorías, el registro de archivos, la gestión de metadatos, la búsqueda y recuperación de audios y el control de acceso basado en roles y permisos.

Los resultados se determinaron mediante la revisión funcional de los módulos, la comprobación de las reglas de negocio y la ejecución de pruebas en un entorno local compuesto por Apache, MySQL y PHP 8.3.1. La ejecución integral documentada el 21 de julio de 2026 confirmó que la aplicación y sus principales rutas respondían correctamente. Además, las pruebas base fueron ejecutadas nuevamente el 30 de julio de 2026, con resultado satisfactorio después de cargar las extensiones requeridas por la aplicación: `fileinfo`, `mbstring` y `pdo_mysql`.

La importación inicial de la base de datos produjo diez tablas relacionadas, dos roles y cinco categorías predeterminadas. Asimismo, el análisis de los archivos PHP no detectó errores de sintaxis. Las pruebas de integración comprobaron el inicio de sesión, el acceso al panel principal y a los módulos de audios, categorías, usuarios, roles, métricas y perfil. También se verificaron la carga de un archivo WAV, la persistencia de sus datos, la reproducción mediante solicitudes parciales, el registro del evento de reproducción y el rechazo de archivos duplicados.

**Tabla 5.1. Síntesis de los resultados de las pruebas del sistema**

| Comprobación realizada | Resultado obtenido |
|---|---|
| Análisis sintáctico de los archivos PHP | Cero errores |
| Pruebas base de contraseñas, hash, escape y extensiones | Satisfactorias |
| Creación de la estructura de datos | 10 tablas, 2 roles y 5 categorías iniciales |
| Inicio de sesión con CSRF y creación de sesión | Acceso correcto y redirección al panel |
| Acceso a los módulos principales | Respuesta HTTP 200 |
| Registro de un archivo WAV válido | Archivo almacenado y datos persistidos |
| Solicitud parcial de audio | Respuesta HTTP 206 |
| Registro de una reproducción | Evento almacenado en el historial |
| Carga del mismo contenido con otro nombre | Rechazada mediante comparación SHA-256 |
| Limpieza del archivo utilizado en la prueba | Archivo físico y registros temporales eliminados |

Estos resultados indican que los componentes principales funcionan de forma integrada. En particular, la plataforma no se limita a almacenar archivos, sino que relaciona cada recurso con su categoría, metadatos, usuario responsable y eventos de reproducción. De esta manera, se obtuvo una biblioteca digital centralizada y trazable que responde a las necesidades operativas identificadas para la emisora.

Debe precisarse que las pruebas realizadas demuestran el cumplimiento funcional en el entorno evaluado. No se efectuó un estudio cronometrado con usuarios ni una prueba de carga concurrente; por tanto, no corresponde afirmar un porcentaje específico de reducción del tiempo de búsqueda o una capacidad máxima de usuarios simultáneos. Esos indicadores podrán medirse posteriormente durante una prueba piloto o la puesta en producción.

### 5.1.1 Módulo de autenticación

El módulo de autenticación permitió restringir el acceso a la plataforma a usuarios registrados y activos. El ingreso puede realizarse mediante el nombre de usuario o el correo electrónico, junto con la contraseña correspondiente. Durante la prueba de credenciales válidas, el servidor procesó la solicitud, creó la sesión y redirigió al usuario hacia el panel principal. Cuando las credenciales no fueron válidas, el sistema mantuvo restringido el acceso y presentó un mensaje genérico, evitando revelar si el nombre de usuario, el correo o la contraseña era el dato incorrecto.

Las contraseñas no se almacenan como texto legible, sino mediante funciones de hash seguras. Al producirse una autenticación satisfactoria, el identificador de la sesión se regenera, lo que disminuye el riesgo de fijación de sesión. El cierre de sesión elimina los datos asociados y la cookie correspondiente. También se comprobó el uso de tokens CSRF en las solicitudes que modifican información, incluido el inicio y el cierre de sesión.

Otro resultado relevante fue la incorporación de un control de intentos fallidos. El sistema registra de forma no reversible el identificador suministrado y la dirección IP mediante SHA-256. Al alcanzar el máximo configurable de intentos dentro del período establecido, se impide temporalmente continuar con nuevas pruebas de acceso. Esta medida reduce la exposición ante intentos automatizados de adivinación de contraseñas.

El análisis del módulo evidencia que la autenticación cumple dos funciones: verificar la identidad del usuario y establecer el contexto de autorización utilizado por los demás módulos. Además, una cuenta o un rol desactivado pierde el acceso, aun cuando la contraseña introducida sea correcta. Por consiguiente, el resultado obtenido fue un mecanismo de ingreso centralizado que combina validación de credenciales, control de sesión, protección contra solicitudes no autorizadas y limitación de intentos.

### 5.1.2 Gestión de usuarios

La gestión de usuarios proporcionó al administrador las operaciones necesarias para crear, consultar, localizar, editar, activar y desactivar cuentas. Cada cuenta se vincula con un rol y con un perfil que puede contener el nombre completo, el teléfono y la fotografía del usuario. El listado permite buscar por nombre de usuario, correo electrónico o nombre completo, lo que facilita la administración cuando aumenta el número de cuentas registradas.

Durante la creación y actualización se validan los datos obligatorios y se aplican restricciones de unicidad al nombre de usuario y al correo electrónico. Estas restricciones se encuentran respaldadas tanto por la lógica de la aplicación como por la estructura de la base de datos, lo cual evita el registro de identidades duplicadas. Las contraseñas deben poseer al menos ocho caracteres y se almacenan mediante hash. El administrador también dispone de una función para restablecer la contraseña de otra cuenta.

La desactivación se implementó de forma lógica por medio del campo de estado. Como resultado, la cuenta permanece en la base de datos para conservar las relaciones históricas, pero ya no puede iniciar sesión. El sistema impide que el administrador desactive su propia cuenta desde la sesión activa, previniendo la pérdida accidental del acceso administrativo.

Las pruebas de navegación confirmaron la respuesta correcta del módulo y su integración con roles y perfiles. En términos operativos, el resultado fue un registro centralizado de las personas autorizadas, con trazabilidad sobre las cuentas que cargan archivos y reproducen contenidos. Este enfoque supera el manejo informal de credenciales compartidas, debido a que cada acción puede asociarse con un usuario individual.

### 5.1.3 Gestión de categorías

El módulo de categorías permitió organizar los audios de acuerdo con la naturaleza de los contenidos de la emisora. La configuración inicial incorporó las categorías Canciones, Anuncios, Cuñas, Promocionales y Programas grabados. El administrador puede crear nuevas categorías, modificar su nombre o descripción y cambiar su estado conforme evolucionen las necesidades de clasificación.

Como regla de integridad, el nombre de cada categoría debe ser único, obligatorio y no exceder los cien caracteres. Cuando se intenta registrar un nombre ya existente, la operación es rechazada y se informa al usuario sin alterar los datos almacenados. Solamente las categorías activas aparecen disponibles al registrar o editar un archivo de audio.

La desactivación se realiza de manera lógica. Esta decisión permite retirar una categoría de las operaciones futuras sin borrar los registros históricos ni romper su relación con archivos previamente clasificados. Las claves foráneas de la base de datos aseguran que cada audio esté asociado con una categoría válida.

El resultado obtenido fue una clasificación uniforme de la biblioteca. La categoría funciona como criterio de organización, como filtro de recuperación y como dimensión para presentar estadísticas. Por lo tanto, este módulo no solo facilita el registro, sino que mejora la consulta posterior y permite conocer la distribución de los recursos almacenados.

### 5.1.4 Registro de archivos

El módulo de registro permitió cargar archivos de audio en formatos MP3 y WAV junto con la información necesaria para identificarlos. Antes de almacenar un archivo, el sistema valida que la transferencia haya finalizado correctamente, comprueba el tamaño máximo configurado y determina el tipo MIME a partir del contenido real. Además, exige correspondencia entre el contenido detectado y la extensión del nombre original. Este procedimiento evita aceptar como audio un archivo de otro tipo que haya sido renombrado.

Una vez validado el contenido, se calcula su huella SHA-256 y se compara con las huellas existentes. La prueba realizada con el mismo contenido y un nombre diferente fue rechazada correctamente. Esto demuestra que la detección de duplicados no depende del nombre del archivo, sino de su contenido binario. La medida evita almacenar copias innecesarias y ayuda a mantener un inventario consistente.

Los archivos aceptados reciben un nombre interno aleatorio y se guardan en un directorio protegido, mientras que la base de datos conserva el nombre original, el nombre interno, la ruta, el tipo MIME, la extensión, el tamaño, la duración, la huella digital, la categoría y el usuario responsable. Esta separación reduce la exposición directa de los archivos y evita colisiones entre nombres iguales.

El registro físico del archivo y la creación de sus datos descriptivos se coordinan mediante una transacción. Si ocurre un error al insertar el registro principal o los metadatos, la transacción se revierte y el archivo trasladado se elimina. Por tanto, no quedan archivos huérfanos ni registros incompletos. En la prueba de carga WAV, tanto el archivo como los registros asociados fueron persistidos satisfactoriamente.

También se implementó la eliminación lógica de los audios. Al eliminar un recurso, su estado cambia a inactivo y deja de mostrarse en la biblioteca, pero se conserva la información necesaria para el historial y las métricas. El resultado general fue un proceso de registro controlado, consistente y trazable desde la carga inicial hasta la consulta posterior.

### 5.1.5 Gestión de metadatos

La gestión de metadatos permitió describir cada audio mediante título, artista, locutor, cliente, palabras clave, fecha de producción y descripción. El título se definió como obligatorio, mientras que los demás campos pueden completarse según el tipo de contenido. También se almacena la duración en segundos como parte de la información técnica del recurso.

La relación entre el archivo y sus metadatos es de uno a uno. Esto garantiza que cada audio posea un único conjunto de datos descriptivos y evita registros ambiguos. Durante la carga, ambos componentes se guardan dentro de la misma operación transaccional; durante la edición, la categoría, la duración y los metadatos se actualizan de forma coordinada.

Los campos responden al contexto de una emisora. Por ejemplo, artista resulta útil para contenidos musicales; locutor, para programas y grabaciones; cliente, para anuncios y cuñas; y palabras clave, para incorporar términos alternativos de localización. De esta forma, el sistema no depende exclusivamente del nombre físico del archivo, que muchas veces no describe adecuadamente el contenido.

El resultado obtenido fue una descripción estructurada y consistente de los recursos sonoros. Los metadatos mejoran la identificación visual, alimentan el detalle de cada audio y constituyen la base de la búsqueda por contenido. Su utilización aporta mayor valor documental al archivo, debido a que convierte un conjunto de ficheros aislados en registros organizados y recuperables.

### 5.1.6 Búsqueda y recuperación

El módulo de búsqueda permitió consultar los audios activos mediante una expresión de texto y un filtro por categoría. La expresión suministrada se compara con el título, artista, locutor, cliente y palabras clave. Ambos criterios pueden emplearse de forma independiente o combinada, y la opción de limpiar filtros permite regresar al inventario completo.

Los resultados se presentan en orden descendente por fecha de registro y se distribuyen en páginas de quince elementos. Cada fila muestra el título, el nombre original, la categoría, un dato descriptivo relevante, el tamaño, el formato y la fecha de registro. Si no existen coincidencias, la interfaz comunica que no se encontraron audios con los criterios indicados.

La matriz de pruebas contempló búsquedas por título y categoría, además de la comprobación de la paginación. Las consultas utilizan sentencias preparadas, lo cual mantiene separados los datos introducidos por el usuario y las instrucciones SQL. Asimismo, únicamente se recuperan registros con estado activo, de modo que los audios eliminados lógicamente no vuelven a aparecer en la biblioteca.

Una vez localizado un recurso, el usuario con permiso puede consultar su detalle y reproducirlo en el navegador. La entrega del audio admite solicitudes HTTP Range; la prueba `bytes=0-99` produjo la respuesta HTTP 206 esperada. Esto permite recuperar y reproducir segmentos del archivo sin transferirlo completamente desde el inicio. El primer evento de reproducción fue registrado satisfactoriamente en el historial con el audio, el usuario, la fecha y una representación hash de la dirección IP.

En consecuencia, el resultado obtenido fue un mecanismo de recuperación que combina información descriptiva, clasificación y acceso protegido al contenido. Aunque la evaluación funcional confirmó la exactitud de los filtros y la reproducción, la cuantificación del ahorro de tiempo frente al procedimiento anterior requerirá una medición con usuarios y un conjunto representativo de archivos reales.

### 5.1.7 Control de permisos

El control de acceso se implementó mediante un modelo basado en roles y permisos. La base de datos relaciona usuarios con roles y roles con capacidades específicas. La configuración inicial incluye los roles Administrador y Usuario autorizado. El primero posee acceso completo, mientras que el segundo comienza con permiso para consultar y reproducir audios; sus capacidades pueden ampliarse según las responsabilidades asignadas.

Los permisos definidos son acceso completo, consulta de audios, registro de audios, edición de audios, eliminación de audios y consulta de métricas. Antes de ejecutar una acción protegida, el sistema verifica que exista una sesión válida y que el usuario posea el permiso requerido. Si la comprobación falla, se devuelve una respuesta HTTP 403 y no se ejecuta la operación. La visibilidad de botones y opciones de navegación también se ajusta a los permisos, aunque la verificación decisiva se realiza en el servidor.

La administración de usuarios, roles y categorías se reservó al perfil con acceso completo. Para proteger la continuidad administrativa, el rol Administrador no puede desactivarse y conserva el permiso global. De igual manera, la desactivación de un rol impide el acceso de sus usuarios asociados, aun si las cuentas individuales permanecen activas.

Las pruebas funcionales confirmaron el control de permisos y el acceso a los módulos según la autorización disponible. Todas las operaciones que modifican información exigen, además, un token CSRF válido. Esta combinación evita que la seguridad dependa únicamente de ocultar enlaces en la interfaz.

El resultado fue una separación efectiva de responsabilidades. Un usuario puede consultar y reproducir sin recibir automáticamente facultades para cargar, modificar o eliminar contenidos, y el administrador puede adaptar los roles sin reprogramar la aplicación. Así, el control de permisos contribuye a la confidencialidad, integridad y trazabilidad de la biblioteca digital.

En síntesis, los resultados obtenidos muestran que el sistema satisface las funciones esenciales previstas para la gestión de archivos de audio: controla el ingreso, identifica a los responsables, estructura el contenido, evita duplicados, conserva metadatos, facilita la recuperación y limita las acciones según el rol. Las pruebas efectuadas respaldan su funcionamiento en el entorno local evaluado y proporcionan una base verificable para el despliegue piloto. Como trabajo posterior, resulta conveniente recopilar evidencias visuales con datos reales, medir tiempos de búsqueda antes y después de la implantación, evaluar la satisfacción de los usuarios y ejecutar pruebas de concurrencia, rendimiento, respaldo y restauración en el entorno definitivo.

## 5.2 Validación funcional

La validación funcional tuvo como propósito comprobar que el sistema desarrollado responde a los requerimientos definidos para la gestión de archivos de audio de Arca de Salvación Radio 95.3 FM. Este proceso se concentró en verificar el comportamiento observable de la aplicación: los datos recibidos, las reglas aplicadas, las respuestas generadas y la persistencia de la información. Por consiguiente, la validación permitió determinar si cada función entrega el resultado esperado desde la perspectiva operativa, independientemente de los detalles internos de programación.

Para realizarla se utilizó un entorno local con Apache, MySQL y PHP 8.3.1. La base de datos fue creada nuevamente a partir de los archivos de estructura y datos iniciales, con el objetivo de comprobar que la instalación fuera reproducible. Posteriormente, se ejecutaron pruebas base y recorridos funcionales sobre los módulos principales. Las pruebas utilizaron datos controlados, incluida una cuenta administrativa y un audio WAV sintético, el cual fue eliminado al finalizar para no contaminar el inventario.

La validación combinó cuatro mecanismos:

1. **Pruebas base automatizadas:** comprobaron la generación y verificación de contraseñas, el comportamiento determinista de SHA-256, el escape de contenido HTML, la disponibilidad de las extensiones requeridas y la existencia de los directorios fundamentales.
2. **Pruebas de integración:** recorrieron el inicio de sesión, la navegación por los módulos, la carga de audio, la persistencia de registros, la reproducción y el registro de eventos.
3. **Pruebas negativas:** evaluaron la reacción del sistema ante credenciales incorrectas, ausencia de permisos, contenido duplicado, tipos de archivo no admitidos y entradas potencialmente maliciosas.
4. **Revisión de integridad:** contrastó el estado de la base de datos y del almacenamiento antes y después de cada operación para confirmar que no quedaran registros incompletos o archivos huérfanos.

El criterio general de aceptación estableció que una función se consideraría satisfactoria cuando produjera la respuesta prevista, conservara la integridad de los datos y no permitiera ejecutar acciones fuera de los permisos asignados. En las operaciones negativas, el resultado esperado consistió en rechazar la solicitud sin modificar la información existente.

### 5.2.1 Casos de validación funcional

La matriz de validación se organizó a partir de los procesos esenciales del sistema. Cada caso relacionó una acción del usuario con un resultado verificable en la interfaz, en la respuesta HTTP, en la base de datos o en el almacenamiento.

**Tabla 5.2. Matriz de validación funcional**

| Código | Función evaluada | Procedimiento de validación | Resultado esperado |
|---|---|---|---|
| VF-01 | Autenticación válida | Introducir las credenciales correctas de una cuenta activa | Crear la sesión y redirigir al panel principal |
| VF-02 | Autenticación inválida | Introducir un usuario, correo o contraseña incorrectos | Denegar el acceso y mostrar un mensaje genérico |
| VF-03 | Control de permisos | Enviar una operación de registro con un usuario sin autorización | Responder con HTTP 403 y no insertar datos |
| VF-04 | Navegación por módulos | Acceder al panel, audios, categorías, usuarios, roles, métricas y perfil | Mostrar cada módulo sin errores del servidor |
| VF-05 | Registro de audio válido | Cargar un archivo WAV o MP3 admitido con categoría, título y metadatos | Guardar el archivo y sus registros relacionados |
| VF-06 | Prevención de duplicados | Cargar nuevamente el mismo contenido con un nombre distinto | Rechazarlo mediante la coincidencia SHA-256 |
| VF-07 | Validación del tipo de archivo | Cargar un archivo renombrado o con MIME no admitido | Rechazarlo antes de trasladarlo al almacenamiento |
| VF-08 | Búsqueda y filtrado | Buscar por datos descriptivos y seleccionar una categoría | Mostrar únicamente las coincidencias y conservar la paginación |
| VF-09 | Reproducción protegida | Reproducir un audio activo mediante una solicitud parcial | Entregar el segmento solicitado con respuesta HTTP 206 |
| VF-10 | Historial de reproducción | Iniciar la reproducción de un audio válido | Insertar un evento asociado con audio, usuario y fecha |
| VF-11 | Gestión del perfil | Cargar una imagen válida y actualizar los datos personales | Guardar los cambios y mostrar la imagen mediante una ruta protegida |
| VF-12 | Cambio de contraseña | Confirmar la contraseña actual y establecer una nueva | Aceptar la nueva contraseña e invalidar la anterior |
| VF-13 | Exactitud del panel | Comparar los indicadores con consultas directas a la base de datos | Presentar conteos equivalentes |
| VF-14 | Protección de entradas | Introducir expresiones SQL y etiquetas de script en campos de texto | No alterar las consultas ni ejecutar código en el navegador |
| VF-15 | Eliminación lógica | Eliminar un audio registrado | Cambiar su estado, ocultarlo del listado y conservar el historial |
| VF-16 | Adaptabilidad visual | Utilizar la aplicación en anchos de 360, 768 y 1440 píxeles | Mantener navegación y contenidos utilizables |

Los casos anteriores cubren los flujos normales, las situaciones de error y las condiciones de seguridad relacionadas con las funciones. La inclusión de pruebas negativas fue importante porque una operación no puede considerarse validada únicamente cuando acepta datos correctos; también debe responder de manera controlada ante datos inválidos o acciones no autorizadas.

### 5.2.2 Resultados de la validación

La ejecución documentada el 21 de julio de 2026 confirmó la creación reproducible de diez tablas, dos roles y cinco categorías iniciales. El análisis sintáctico de los archivos PHP finalizó sin errores y las pruebas base de contraseñas, hash, escape y extensiones obtuvieron un resultado satisfactorio. Estas últimas fueron ejecutadas nuevamente el 30 de julio de 2026 y produjeron el mensaje **“Pruebas base: OK”** al utilizar el intérprete con las extensiones `fileinfo`, `mbstring` y `pdo_mysql`.

El inicio de sesión válido generó una respuesta satisfactoria y la redirección al panel principal. Después de establecer la sesión, las rutas del panel, biblioteca de audios, categorías, usuarios, roles, métricas y perfil respondieron con HTTP 200. Esto confirmó que los controladores, las vistas, la conexión con la base de datos y la sesión funcionaban de manera conjunta.

En el proceso de carga, un archivo WAV válido fue aceptado y se comprobaron tanto su almacenamiento físico como la inserción de los registros correspondientes al audio y sus metadatos. Al intentar cargar el mismo contenido con otro nombre, la aplicación detectó la coincidencia de la huella SHA-256 y rechazó la operación. Este resultado validó la regla de unicidad del contenido y demostró que el nombre externo no se utiliza como único criterio para identificar duplicados.

La recuperación del audio mediante el encabezado `Range: bytes=0-99` devolvió el código HTTP 206, correspondiente a contenido parcial. A continuación, el evento de reproducción produjo una respuesta satisfactoria y una nueva fila en el historial. De esta forma se comprobó la relación entre la consulta del recurso, la entrega protegida del archivo y la generación de información para las métricas.

Al finalizar el procedimiento, el audio sintético y sus registros temporales fueron eliminados, mientras que la cuenta administrativa y los datos iniciales permanecieron intactos. Esta limpieza demostró que los datos de prueba podían retirarse sin afectar la configuración principal.

**Tabla 5.3. Resumen de evidencias obtenidas**

| Área validada | Evidencia observada | Evaluación |
|---|---|---|
| Configuración e instalación | Estructura creada con 10 tablas, 2 roles y 5 categorías | Satisfactoria |
| Código PHP | Cero errores de sintaxis | Satisfactoria |
| Funciones fundamentales | Pruebas base finalizadas correctamente | Satisfactoria |
| Autenticación y sesión | Acceso correcto y redirección al panel | Satisfactoria |
| Integración de módulos | Respuesta HTTP 200 en los módulos principales | Satisfactoria |
| Registro de archivos | WAV almacenado y registros persistidos | Satisfactoria |
| Control de duplicados | Segundo contenido rechazado por SHA-256 | Satisfactoria |
| Recuperación del audio | Respuesta HTTP 206 ante solicitud parcial | Satisfactoria |
| Trazabilidad | Evento almacenado en el historial de reproducciones | Satisfactoria |
| Limpieza de datos de prueba | Archivo y registros temporales retirados | Satisfactoria |
| Adaptabilidad y aceptación de usuarios | Requiere evidencia final con usuarios y dispositivos del entorno objetivo | Pendiente de validación en producción |

### 5.2.3 Análisis de cumplimiento funcional

Los resultados obtenidos permiten considerar funcionalmente aceptados los procesos esenciales en el entorno de prueba. El sistema autentica al usuario antes de permitir el acceso, aplica las autorizaciones correspondientes, registra archivos válidos de manera consistente, evita duplicados, conserva los metadatos, recupera los contenidos y registra las reproducciones. La correcta interacción entre estos procesos demuestra que los módulos no funcionan de forma aislada, sino como partes de un mismo flujo de gestión documental.

La validación también mostró que la integridad se mantiene ante operaciones incompletas o no permitidas. El uso de transacciones coordina el registro del archivo y sus metadatos; la validación MIME impide aceptar contenido incompatible; la eliminación lógica preserva la trazabilidad; y las consultas preparadas junto con el escape HTML reducen los riesgos asociados con entradas manipuladas. Desde el punto de vista funcional, estas medidas aseguran que el sistema no solo realice las operaciones solicitadas, sino que las ejecute respetando las reglas establecidas.

No obstante, la aceptación obtenida corresponde al entorno local controlado. Antes de declarar la validación definitiva en producción, deben incorporarse capturas de pantalla y registros con datos reales, probar la interfaz en los dispositivos utilizados por el personal y solicitar a los usuarios responsables que ejecuten los procesos habituales de la emisora. También resulta recomendable documentar el nombre o rol de cada participante, la fecha de ejecución, el resultado de cada caso, las incidencias encontradas y la firma de aceptación.

La prueba con usuarios finales puede formalizarse mediante un acta que contenga los siguientes criterios:

- El usuario puede iniciar y cerrar sesión sin asistencia técnica.
- El administrador puede crear y desactivar cuentas, categorías y roles.
- Un usuario autorizado solo observa y ejecuta las acciones permitidas.
- El operador puede registrar un audio válido junto con sus metadatos.
- Los contenidos duplicados o incompatibles son rechazados con un mensaje comprensible.
- El usuario puede localizar un audio por sus datos descriptivos o categoría.
- El archivo encontrado puede reproducirse desde el navegador.
- Los datos mostrados en el panel coinciden con los registros almacenados.
- La navegación es utilizable desde computadoras, tabletas y teléfonos previstos.

En conclusión, la validación funcional evidenció que el sistema cumple los requerimientos esenciales definidos para la centralización y administración de los recursos de audio. Los resultados satisfactorios respaldan su paso a una prueba piloto. La aprobación final deberá completarse con la validación de aceptación por parte de los usuarios de la emisora y con evidencias obtenidas en el servidor y los dispositivos que se emplearán durante la operación real.

## 5.3 Experiencias tecnológicas previas

El desarrollo del sistema se apoyó en experiencias y conocimientos tecnológicos previos relacionados con aplicaciones web, bases de datos relacionales y administración de archivos digitales. El manejo de HTML, CSS, JavaScript, PHP y MySQL proporcionó una base para abordar la construcción de formularios, sesiones, operaciones de registro y consulta, así como la presentación de información en el navegador. No obstante, la integración de estas tecnologías en una solución orientada específicamente a archivos de audio planteó retos que superaron el uso aislado de cada herramienta.

Las experiencias anteriores con sistemas de información permitieron reconocer la importancia de separar la presentación, la lógica del negocio y el acceso a los datos. A partir de ese conocimiento se adoptó una arquitectura Modelo-Vista-Controlador ligera. Las vistas se encargan de la interfaz; los controladores reciben y validan las solicitudes; los servicios concentran las operaciones especializadas de almacenamiento; y MySQL mantiene la información persistente. Esta separación facilitó la organización del código y evitó que las consultas a la base de datos se mezclaran directamente con la presentación.

La elección de PHP y MySQL respondió tanto a la experiencia disponible como a las características del entorno objetivo. Son tecnologías ampliamente compatibles con servidores web y servicios de alojamiento compartido. El uso de PHP 8.3 permitió trabajar con tipado estricto, sesiones, carga de archivos, generación de hash y validación del contenido. MySQL, mediante el motor InnoDB, aportó claves foráneas, restricciones únicas, índices y transacciones. La experiencia previa con consultas básicas evolucionó hacia el uso sistemático de PDO y sentencias preparadas, necesarias para mantener separados los parámetros proporcionados por el usuario y las instrucciones SQL.

En la capa de presentación, los conocimientos de HTML5 y CSS3 sirvieron para estructurar formularios, tablas, paneles y vistas de detalle. Bootstrap 5.3 permitió ampliar esa experiencia mediante un sistema de cuadrícula, componentes de navegación, formularios, alertas y utilidades adaptables. JavaScript se utilizó de forma complementaria para el comportamiento del menú lateral, la vista previa de fotografías y el registro del inicio de una reproducción. La decisión de mantener una interfaz basada principalmente en tecnologías web estándar redujo la complejidad y favoreció el acceso desde computadoras, tabletas y teléfonos sin desarrollar aplicaciones nativas independientes.

### 5.3.1 Aprendizajes derivados del tratamiento de archivos

Uno de los principales aprendizajes fue comprender que la gestión de archivos no debe limitarse a recibir un nombre y mover el contenido hacia una carpeta. Un archivo enviado por el navegador puede contener datos distintos de los sugeridos por su extensión. Por esa razón, la experiencia adquirida durante el proyecto condujo a validar el error de carga, el tamaño, el tipo MIME real y la correspondencia con las extensiones MP3 o WAV.

También se comprobó que el nombre original no constituye un identificador confiable. Dos archivos diferentes pueden tener el mismo nombre y un mismo contenido puede presentarse con nombres distintos. Para resolver estas situaciones, el sistema conserva el nombre original únicamente como información descriptiva, genera un nombre interno aleatorio para el almacenamiento y calcula una huella SHA-256 para identificar el contenido. Esta combinación evita colisiones, dificulta la predicción de rutas físicas y permite detectar duplicados independientemente del nombre utilizado por el usuario.

Otro aprendizaje consistió en coordinar el almacenamiento físico con la persistencia en la base de datos. Guardar primero el archivo y luego insertar los datos sin un mecanismo de recuperación puede producir archivos huérfanos; insertar los datos sin confirmar el archivo puede generar registros que apuntan a recursos inexistentes. La solución aplicada utiliza transacciones y elimina el archivo trasladado si la operación de base de datos falla. Esta experiencia reforzó la necesidad de considerar la consistencia entre recursos físicos y registros lógicos como parte de una misma operación.

La reproducción desde el navegador también requirió ampliar los conocimientos previos sobre entrega de archivos. En lugar de exponer directamente las carpetas de almacenamiento, el audio se sirve mediante un controlador autenticado. El soporte de solicitudes HTTP Range permite al navegador solicitar segmentos específicos y comenzar la reproducción sin descargar previamente el archivo completo. El resultado de la prueba HTTP 206 confirmó la aplicación correcta de este mecanismo.

### 5.3.2 Aprendizajes sobre seguridad y control de acceso

El proyecto permitió aplicar principios de seguridad que no siempre están presentes en aplicaciones de práctica o prototipos iniciales. La autenticación no se redujo a comparar una contraseña con un valor almacenado: se utilizaron funciones seguras de hash, regeneración del identificador de sesión, cierre de sesión, cookies protegidas, mensajes genéricos y limitación de intentos fallidos.

De igual manera, se estableció una diferencia entre autenticación y autorización. La primera identifica al usuario, mientras que la segunda determina las acciones que puede realizar. La implementación de un control basado en roles y permisos mostró que ocultar un botón no es suficiente; cada controlador debe repetir la comprobación en el servidor antes de procesar la solicitud. Esta experiencia produjo una estructura RBAC persistida en las tablas de roles, permisos y sus relaciones.

La protección CSRF aplicada a las solicitudes que modifican información y el escape centralizado de salidas HTML reforzaron el tratamiento seguro de los datos. Las consultas preparadas ayudaron a prevenir la manipulación de instrucciones SQL. En conjunto, estas medidas demostraron que la seguridad no corresponde a una única función, sino a controles distribuidos a lo largo del inicio de sesión, los formularios, los controladores, la base de datos y el almacenamiento.

### 5.3.3 Aprendizajes sobre diseño de datos y trazabilidad

La experiencia previa con bases de datos se amplió al diseñar relaciones que conservaran la trazabilidad. El sistema no guarda únicamente la ubicación de un audio: relaciona el recurso con su categoría, el usuario que lo registró, sus metadatos y los eventos de reproducción. Las restricciones únicas sobre usuarios, correos, categorías y huellas SHA-256 evitan inconsistencias, mientras que las claves foráneas mantienen las relaciones entre las diez tablas.

Se aprendió además que la eliminación física inmediata no siempre es adecuada para un sistema de información. La eliminación lógica permite ocultar un audio o desactivar una cuenta sin perder las referencias históricas utilizadas por el seguimiento y las métricas. Este criterio también se aplicó a categorías y roles mediante estados activos e inactivos.

El registro de reproducciones proporcionó una base verificable para el panel y las métricas. En vez de utilizar valores simulados, los indicadores se calculan a partir de eventos almacenados. Esta decisión mejoró la confiabilidad de los resultados y mostró la importancia de diseñar los datos operativos pensando también en las necesidades posteriores de análisis.

### 5.3.4 Adaptación al entorno tecnológico

El uso de MAMP facilitó la configuración de un ambiente local semejante al servidor PHP/MySQL previsto. Sin embargo, las pruebas mostraron que no basta con disponer de una versión adecuada de PHP; también deben estar habilitadas extensiones como `fileinfo`, `mbstring` y `pdo_mysql`. Al ejecutar las pruebas sin estas extensiones, el sistema de comprobación informó su ausencia. Después de cargarlas, las pruebas base finalizaron correctamente. Esta experiencia evidenció la importancia de validar tanto el código como la configuración del entorno.

También se tomaron decisiones para mantener la compatibilidad con alojamientos compartidos. Se implementó un MVC ligero sin dependencias de Composer, la duración del audio se dejó editable porque no puede garantizarse la disponibilidad de FFmpeg y se incorporó una comprobación de firmas MP3/WAV como respaldo local. Para producción se mantiene como requisito habilitar `fileinfo`, utilizar HTTPS, proteger las cookies de sesión y configurar límites de carga coherentes con el tamaño máximo admitido.

En síntesis, las experiencias tecnológicas previas facilitaron la selección de herramientas conocidas, pero el proyecto permitió integrarlas con criterios de arquitectura, seguridad, integridad y trazabilidad. El aprendizaje más significativo consistió en transformar conocimientos separados de programación web y bases de datos en una solución completa, en la que la interfaz, las reglas del negocio, el almacenamiento y la seguridad funcionan de manera coordinada.

## 5.4 Cumplimiento de los objetivos

El cumplimiento de los objetivos se evaluó mediante la comparación entre los propósitos formulados para el proyecto y las funciones implementadas y comprobadas. Para este análisis se utilizaron como evidencias la estructura del sistema, los módulos disponibles, las reglas de negocio, los resultados de las pruebas funcionales y los registros producidos durante la validación.

### 5.4.1 Cumplimiento del objetivo general

El objetivo general consistió en proporcionar una aplicación web alojable en un entorno PHP/MySQL que permitiera gestionar de forma organizada y segura los recursos de audio de la emisora.

Este objetivo se considera **cumplido en el entorno de desarrollo y validación**, debido a que se obtuvo una aplicación web operativa con autenticación, usuarios, perfiles, roles, permisos, categorías, archivos, metadatos, búsqueda, reproducción, historial y métricas. La instalación reproducible de la base de datos, la ausencia de errores de sintaxis, el acceso satisfactorio a los módulos y la prueba integral de carga y reproducción demuestran que los componentes principales funcionan de manera conjunta.

La organización se evidencia en la centralización de los archivos y sus datos descriptivos, así como en su clasificación por categorías. La seguridad se sustenta en el hash de contraseñas, las sesiones, los permisos, los tokens CSRF, las consultas preparadas, la validación MIME, la detección de duplicados y el almacenamiento protegido. La posibilidad de configurar la aplicación mediante variables de entorno y de operar sin un framework o dependencias externas de PHP favorece su instalación en el alojamiento previsto.

La consideración anterior no sustituye la validación final en producción. Para completar el ciclo de implantación deben configurarse HTTPS, cookies seguras, límites de carga, permisos de directorios, copias de seguridad y pruebas de aceptación con los usuarios de la emisora. Estas actividades corresponden al despliegue definitivo y no invalidan el cumplimiento funcional alcanzado.

### 5.4.2 Cumplimiento de los objetivos específicos

**Tabla 5.4. Matriz de cumplimiento de los objetivos**

| Objetivo específico | Resultado alcanzado | Evidencia | Nivel de cumplimiento |
|---|---|---|---|
| Centralizar archivos y metadatos en una sola plataforma | Se integraron los audios, datos técnicos y descripciones en una biblioteca común | Tablas `archivos_audio` y `metadatos_audio`, registro transaccional y biblioteca web | Cumplido |
| Reducir el tiempo de localización mediante búsqueda y filtros | Se implementó búsqueda por título, artista, locutor, cliente y palabras clave, además de filtro por categoría y paginación | Módulo de biblioteca y prueba funcional de búsqueda | Cumplido funcionalmente; pendiente de cuantificación |
| Clasificar los recursos mediante categorías adaptadas a la operación radial | Se crearon categorías configurables y cinco clasificaciones iniciales | Canciones, Anuncios, Cuñas, Promocionales y Programas grabados | Cumplido |
| Evitar contenido duplicado mediante SHA-256 | La aplicación calcula la huella del archivo y rechaza coincidencias existentes | Prueba del mismo contenido con un nombre distinto | Cumplido |
| Controlar el acceso mediante roles y permisos configurables | Se implementó RBAC con verificación en el servidor y administración de roles | Roles, permisos, relación `rol_permisos` y respuesta HTTP 403 | Cumplido |
| Reproducir audio directamente en el navegador | Se incorporó un reproductor con entrega protegida y soporte HTTP Range | Respuesta HTTP 206 ante `Range: bytes=0-99` | Cumplido |
| Producir métricas verificables basadas en eventos reales | Cada reproducción genera un registro utilizado por el panel y las métricas | Fila en `historial_reproducciones`, tendencias y clasificaciones por uso | Cumplido |
| Permitir que cada usuario administre su perfil y contraseña | Se habilitó la edición de datos, fotografía y cambio de contraseña con verificación de la clave actual | Módulo de perfil y rutas protegidas | Cumplido |

El primer objetivo específico se alcanzó al sustituir la concepción del audio como un archivo aislado por un registro integrado. Cada contenido queda asociado con información técnica, metadatos, categoría y usuario responsable. Esta estructura proporciona un único punto de consulta y evita depender de carpetas o nombres externos como forma principal de organización.

El segundo objetivo se cumplió desde el punto de vista funcional. La biblioteca permite localizar contenidos mediante diferentes campos y restringir los resultados por categoría. La paginación mantiene manejable la presentación cuando aumenta el volumen de registros. Sin embargo, para afirmar una reducción expresada en minutos o porcentajes será necesario comparar el procedimiento anterior con el sistema mediante una prueba cronometrada y una muestra representativa de usuarios y archivos.

El tercer objetivo se evidenció en la administración de categorías y en su aplicación obligatoria durante el registro. La configuración inicial responde a los tipos de contenidos habituales de una emisora, pero puede ampliarse sin modificar el código. Además de ordenar la biblioteca, las categorías funcionan como filtros y como dimensión para las métricas.

El cuarto objetivo fue validado directamente. El sistema calcula una huella SHA-256 antes de completar el registro y consulta si existe una coincidencia. La prueba con el mismo contenido bajo otro nombre fue rechazada, confirmando que la prevención no depende de la denominación externa del archivo.

El quinto objetivo se alcanzó mediante el control de acceso basado en roles. El Administrador posee acceso completo y el Usuario autorizado recibe inicialmente capacidad de consulta y reproducción. Los permisos de creación, edición, eliminación y métricas pueden asignarse de acuerdo con las funciones del personal. La comprobación se realiza en el servidor antes de ejecutar cada operación protegida.

El sexto objetivo se comprobó mediante la reproducción en el navegador. Los archivos permanecen en almacenamiento protegido y son entregados por un controlador que verifica la autorización. El soporte HTTP Range permite comenzar, pausar y desplazar la reproducción sin exponer directamente la ruta física.

El séptimo objetivo se cumplió al registrar eventos reales de reproducción. A partir de estos datos, el módulo de métricas presenta el total dentro de un rango, los audios más reproducidos, las categorías con mayor actividad, la tendencia diaria y las cargas mensuales. Esto permite que los indicadores sean verificables mediante consultas a la base de datos.

El octavo objetivo se materializó en el módulo de perfil. Cada usuario puede actualizar su información personal, cargar una fotografía validada y cambiar su contraseña después de confirmar la actual. La gestión de la cuenta personal queda así separada de las funciones administrativas de creación, asignación de roles y restablecimiento de contraseñas.

### 5.4.3 Valoración general del cumplimiento

La comparación realizada muestra que el objetivo general y los ocho objetivos específicos cuentan con una respuesta funcional dentro del sistema. Siete objetivos específicos poseen evidencia directa de cumplimiento y el objetivo relacionado con la reducción del tiempo de localización dispone de la funcionalidad necesaria, aunque su impacto cuantitativo deberá medirse durante la prueba piloto.

El alcance se mantuvo concentrado en la gestión de recursos de audio. No se incorporaron funciones ajenas a los objetivos, como transmisión radial en vivo, edición o mezcla de audio, automatización de cabina, facturación o una aplicación móvil nativa. Esta delimitación permitió orientar los recursos del proyecto hacia los procesos prioritarios de organización, seguridad y recuperación.

Por tanto, el grado de cumplimiento puede considerarse satisfactorio para la fase de desarrollo. La solución obtenida responde a los requerimientos planteados y dispone de evidencias técnicas y funcionales que sustentan su operación. La siguiente etapa consiste en completar la implantación controlada, recoger la aceptación formal de los usuarios y medir los indicadores de tiempo y satisfacción que permitan evaluar el impacto del sistema en condiciones reales.

## 5.5 Análisis de implementación

El análisis de implementación permitió valorar la manera en que las decisiones técnicas contribuyeron al funcionamiento, la seguridad y la posibilidad de mantenimiento del sistema. La solución fue desarrollada como una aplicación web con PHP 8.3, MySQL, HTML5, CSS3, Bootstrap y JavaScript. Se adoptó una arquitectura MVC ligera para separar las vistas, el enrutamiento, los controladores, los servicios y el acceso a la base de datos.

Esta organización favoreció la asignación de responsabilidades. Las vistas presentan la información, pero no ejecutan consultas directamente; los controladores verifican la sesión, los permisos y las reglas del negocio; los servicios procesan el almacenamiento de audios e imágenes; y la capa de conexión utiliza PDO para comunicarse con MySQL. Como resultado, las modificaciones de una capa pueden realizarse con menor impacto sobre las demás.

La implementación también separó los datos estructurados de los archivos binarios. MySQL conserva las credenciales, categorías, metadatos, rutas, huellas y eventos, mientras que los audios permanecen en el sistema de archivos. Esta decisión evita que la base de datos aumente innecesariamente por el almacenamiento de objetos binarios de gran tamaño y permite que el servidor web entregue el contenido por fragmentos.

Las reglas críticas se ejecutan en el servidor. La interfaz puede ocultar opciones según el rol, pero los controladores vuelven a comprobar cada permiso antes de efectuar la acción. De la misma manera, el navegador puede indicar el formato de un archivo, pero el servidor examina el contenido real. Este criterio impide que la implementación dependa de controles que un usuario podría omitir o modificar desde el cliente.

La utilización de variables de entorno permitió separar la configuración del código. La dirección de la aplicación, la conexión con MySQL, las rutas de almacenamiento, los límites de carga y el uso de cookies seguras pueden ajustarse sin alterar los módulos. Esta característica facilita el traslado desde MAMP hacia un servidor remoto compatible con PHP y MySQL.

Las pruebas demostraron que la implementación principal funciona en el entorno local. Sin embargo, el análisis también identificó aspectos que dependen del servidor definitivo: HTTPS, capacidad de almacenamiento, velocidad de disco, límites de PHP, ancho de banda, número de usuarios concurrentes, programación de respaldos y disponibilidad de las extensiones necesarias. Por ello, la evaluación de la implementación distingue entre el funcionamiento comprobado localmente y el rendimiento que todavía debe medirse en el alojamiento remoto.

### 5.5.1 Eficiencia en la prevención de duplicidad de archivos

La prevención de duplicidad se implementó mediante una huella SHA-256 calculada a partir del contenido binario de cada archivo. Después de validar el tipo MIME, la extensión y el tamaño, el sistema procesa el archivo temporal y obtiene una cadena de 64 caracteres hexadecimales. Antes de trasladar definitivamente el contenido, consulta si esa huella ya está asociada con un audio registrado.

Este mecanismo es más eficaz que comparar únicamente el nombre, el tamaño o la fecha. Los nombres pueden cambiar, dos archivos distintos pueden compartir una denominación y el tamaño por sí solo no permite demostrar que el contenido sea igual. En cambio, si un usuario intenta cargar nuevamente el mismo audio bajo otro nombre, el contenido produce la misma huella y la aplicación identifica la coincidencia.

La prueba funcional confirmó este comportamiento: se cargó un archivo válido y posteriormente se intentó registrar el mismo contenido con un nombre diferente. La segunda operación fue rechazada y se indicó la existencia del registro previo. Por consiguiente, la prevención de duplicados demostró ser independiente del nombre suministrado y evitó la creación de una segunda copia tanto en la base de datos como en el almacenamiento.

Desde el punto de vista computacional, el cálculo de SHA-256 requiere leer el archivo completo una vez. Su costo crece de manera lineal con el tamaño del contenido, representado como **O(n)**, donde *n* corresponde al número de bytes. Esta lectura es necesaria para comparar el contenido real. La función utilizada procesa el archivo de manera progresiva, por lo que no necesita cargar el audio completo en la memoria del servidor. En consecuencia, el principal costo se relaciona con el tiempo de lectura del disco y el procesamiento del archivo, no con un consumo de memoria equivalente a su tamaño total.

Después del cálculo, la búsqueda se realiza sobre la columna `hash_sha256`, definida como única. El índice asociado evita recorrer todos los registros para localizar una coincidencia. Si *m* representa la cantidad de audios almacenados, la búsqueda mediante un índice de árbol posee normalmente un costo aproximado de **O(log m)**. Por ello, el aumento del inventario afecta mucho menos la comprobación en la base de datos que el tamaño individual del archivo afecta el cálculo de la huella.

La restricción única proporciona una segunda capa de protección. Aunque dos solicitudes simultáneas consultaran la misma huella antes de que una de ellas finalizara, MySQL impediría insertar dos valores iguales. La consulta previa ofrece un mensaje comprensible al usuario y la restricción protege la integridad ante condiciones de concurrencia.

El proceso completo puede resumirse de la siguiente manera:

1. Se recibe el archivo en el área temporal de PHP.
2. Se comprueban el error de carga, el tamaño, el MIME y la extensión.
3. Se calcula la huella SHA-256 del contenido.
4. Se consulta el índice de huellas existentes.
5. Si existe una coincidencia, se cancela el registro.
6. Si no existe, se almacena el archivo con un nombre interno aleatorio.
7. Se insertan los datos físicos y los metadatos dentro de una transacción.
8. La restricción única confirma que la huella no pueda repetirse.

**Tabla 5.5. Evaluación del mecanismo de prevención de duplicados**

| Criterio | Mecanismo implementado | Resultado |
|---|---|---|
| Independencia del nombre | Comparación de SHA-256 sobre el contenido | Detecta el mismo audio aunque sea renombrado |
| Precisión | Huella de 256 bits | Probabilidad de colisión accidental extremadamente baja |
| Velocidad de consulta | Índice único en `hash_sha256` | Evita una búsqueda secuencial en la tabla |
| Uso de memoria | Procesamiento progresivo del archivo | No requiere cargar el audio completo en memoria |
| Integridad concurrente | Restricción única en MySQL | Impide dos huellas iguales aun ante solicitudes simultáneas |
| Consistencia | Transacción y limpieza ante errores | Evita registros parciales y archivos huérfanos |
| Evidencia funcional | Reintento con el mismo contenido y otro nombre | Operación rechazada correctamente |

La huella se compara incluso con registros inactivos. Esto significa que un audio eliminado lógicamente continúa impidiendo que el mismo contenido se cargue como un registro nuevo. El comportamiento conserva la unicidad histórica y la trazabilidad; si fuera necesario recuperar ese material, resulta más adecuado reactivar el registro existente que crear otro. Esta regla debe comunicarse al administrador para evitar interpretar el rechazo como una falla.

Aunque SHA-256 hace extremadamente improbable una colisión accidental, ninguna función de hash permite afirmar una imposibilidad matemática absoluta. Para el alcance y volumen esperado de una biblioteca radial, el nivel de confiabilidad es adecuado. Si en el futuro se necesitara una verificación adicional, una coincidencia de hash podría complementarse comparando el tamaño y, excepcionalmente, el contenido byte por byte. En el escenario actual, esa comparación adicional aumentaría el tiempo de procesamiento sin proporcionar un beneficio operativo significativo.

Como posible mejora, una colisión provocada por dos cargas simultáneas podría capturarse de manera específica para mostrar el mismo mensaje descriptivo utilizado en la consulta previa. Actualmente, la restricción conservaría la integridad, pero el segundo usuario podría recibir un mensaje general de error. Esta observación no afecta la prevención de duplicidad, pero permitiría mejorar la experiencia ante una condición de concurrencia poco frecuente.

En conclusión, el mecanismo resultó eficiente porque combina una lectura lineal inevitable del contenido con una búsqueda indexada y una restricción de unicidad. La prueba realizada demuestra su eficacia funcional, mientras que una medición de archivos de diferentes tamaños en el servidor remoto permitirá establecer el tiempo promedio consumido por el cálculo en condiciones reales.

### 5.5.2 Rendimiento de la base de datos y servidor remoto

El rendimiento de la base de datos fue considerado desde el diseño. Las tablas utilizan InnoDB, claves primarias, claves foráneas, restricciones únicas e índices en campos utilizados con frecuencia. Entre ellos se encuentran el estado del usuario, la categoría y el usuario asociado con cada audio, la combinación de estado y fecha de registro y la fecha del historial de reproducciones. Estas estructuras favorecen las operaciones de autenticación, listado, detalle, trazabilidad y generación de métricas.

La aplicación establece una conexión PDO reutilizable durante cada solicitud y utiliza consultas preparadas nativas. Los listados principales recuperan mediante uniones los datos relacionados que requiere la interfaz, evitando realizar una consulta separada por cada fila. La biblioteca cuenta primero las coincidencias y recupera únicamente la página solicitada, con un máximo de quince registros. Esta paginación reduce la cantidad de datos transferidos y representados en una sola respuesta.

El almacenamiento de los audios fuera de MySQL constituye otra decisión favorable. La base de datos conserva únicamente la ruta y los atributos necesarios, por lo que las búsquedas y operaciones administrativas no transportan el contenido binario. Cuando un usuario reproduce un audio, el controlador lo lee en bloques y admite solicitudes parciales. De esta manera, el consumo de memoria se mantiene controlado y el navegador recibe solamente el intervalo solicitado.

Las pruebas locales confirmaron que los módulos principales respondieron correctamente y que la entrega parcial produjo HTTP 206. Sin embargo, estas evidencias validan la funcionalidad y no constituyen por sí solas una prueba de rendimiento remoto. No se dispone todavía de mediciones documentadas de latencia, consultas por segundo, usuarios concurrentes, uso de CPU, memoria, velocidad de disco o ancho de banda del hosting definitivo. Por esa razón, no resulta metodológicamente válido asignar tiempos de respuesta o capacidades máximas al servidor remoto sin ejecutar pruebas en ese entorno.

El análisis del código permite identificar los factores que influirán en el rendimiento:

- La autenticación consulta usuarios, roles y permisos al reconstruir la sesión.
- El panel realiza varias consultas de conteo, distribución y actividad reciente.
- La biblioteca ejecuta un conteo y una consulta paginada con uniones.
- La búsqueda textual utiliza coincidencias parciales en cinco campos.
- Las métricas agrupan el historial por fecha, audio y categoría.
- La carga depende del tamaño del archivo y de la velocidad de escritura del disco.
- La reproducción depende principalmente de la lectura del almacenamiento y del ancho de banda.

La búsqueda utiliza expresiones `LIKE` con comodines al inicio y al final. Este enfoque es apropiado para una biblioteca pequeña o mediana y permite localizar coincidencias parciales, pero los índices convencionales no pueden aprovecharse plenamente cuando el término comienza con un comodín. Si el inventario creciera de forma considerable, se recomienda evaluar índices `FULLTEXT`, una tabla de búsqueda normalizada o un motor especializado. Esa decisión deberá basarse en mediciones reales, pues añadir complejidad antes de identificar un problema no garantiza una mejora.

La paginación actual utiliza `LIMIT` y `OFFSET`. Su comportamiento es adecuado para el volumen inicial, pero las páginas muy avanzadas pueden requerir que MySQL descarte una cantidad creciente de filas. Para inventarios de gran tamaño podría adoptarse paginación por cursor, utilizando la fecha y el identificador del último registro mostrado.

El historial de reproducciones puede convertirse en la tabla de mayor crecimiento. El índice compuesto por fecha y audio favorece los informes por período, aunque las métricas acumuladas exigirán más trabajo a medida que aumenten los eventos. Entre las mejoras futuras se encuentran crear índices después de analizar los planes de ejecución, almacenar resúmenes diarios o mensuales, aplicar caché de corta duración al panel y archivar eventos antiguos conforme a una política institucional.

Para evaluar el servidor remoto debe aplicarse una prueba controlada con un conjunto representativo de datos y archivos. Los resultados deben registrarse en lugar de basarse solamente en apreciaciones visuales.

**Tabla 5.6. Indicadores propuestos para evaluar el servidor remoto**

| Indicador | Forma de medición | Finalidad |
|---|---|---|
| Tiempo de autenticación | Desde el envío del formulario hasta la carga del panel | Evaluar sesión y consultas iniciales |
| Tiempo de carga de la biblioteca | Medición del tiempo total de respuesta con distintas cantidades de registros | Evaluar uniones, conteo y paginación |
| Tiempo de búsqueda | Consultas por términos frecuentes y poco frecuentes | Detectar necesidad de optimización textual |
| Tiempo de registro | Desde el inicio de la carga hasta la confirmación | Evaluar red, hash, disco y transacción |
| Inicio de reproducción | Desde la acción del usuario hasta la recepción del primer segmento | Evaluar almacenamiento y ancho de banda |
| Rendimiento concurrente | Pruebas progresivas con varias sesiones simultáneas | Identificar capacidad y degradación |
| Uso de recursos | CPU, memoria, disco y conexiones durante las pruebas | Localizar el recurso limitante |
| Tasa de errores | Respuestas 4xx/5xx y operaciones fallidas durante la carga | Verificar estabilidad |
| Crecimiento de la base de datos | Tamaño de tablas e índices por período | Planificar almacenamiento y mantenimiento |

Antes de la prueba deben acordarse los umbrales de aceptación con la institución y el proveedor del alojamiento. Como referencia de trabajo, pueden establecerse tiempos máximos para las páginas administrativas, la búsqueda y el inicio de reproducción, además de una cantidad esperada de usuarios simultáneos. Estos valores deben figurar como criterios aprobados antes de ejecutar la medición y no como resultados ya alcanzados.

El rendimiento remoto también dependerá de una configuración correcta. Será necesario habilitar las extensiones de PHP, ajustar `upload_max_filesize` y `post_max_size`, establecer un tiempo de ejecución compatible con el tamaño máximo de audio, activar OPcache si el proveedor lo permite y verificar permisos de lectura y escritura. La base de datos debe utilizar un usuario con privilegios mínimos y encontrarse preferiblemente en la misma red o centro de datos que la aplicación para reducir la latencia.

La capacidad de operación no debe evaluarse sin considerar la continuidad. Se recomienda supervisar el espacio disponible, revisar los registros de errores, respaldar diariamente la base de datos y los directorios de almacenamiento y probar periódicamente la restauración. Una aplicación rápida pero sin recuperación ante fallos no satisface por completo las necesidades institucionales.

En síntesis, la estructura implementada incorpora decisiones adecuadas para un volumen inicial: índices, paginación, consultas preparadas, archivos fuera de la base de datos y transmisión por bloques. No obstante, el rendimiento del servidor remoto permanece pendiente de una medición formal en el alojamiento seleccionado. Esta conclusión evita confundir el éxito funcional local con una capacidad de producción todavía no cuantificada.

## 5.6 Análisis de resultados

El análisis conjunto de los resultados muestra que el proyecto produjo una solución funcional para organizar y controlar los recursos sonoros de la emisora. La aplicación integra procesos que anteriormente podían ejecutarse de manera separada: identificación de usuarios, clasificación, carga, descripción, búsqueda, reproducción y seguimiento. El valor principal no reside únicamente en digitalizar el almacenamiento, sino en relacionar cada archivo con información que facilita su administración y recuperación.

Desde la dimensión funcional, los módulos principales respondieron satisfactoriamente. La autenticación creó una sesión válida y redirigió al panel; las rutas administrativas y operativas respondieron sin errores; un archivo WAV fue almacenado con sus metadatos; la biblioteca permitió recuperar contenidos; la reproducción parcial generó HTTP 206; y el evento correspondiente quedó registrado. Estos resultados demuestran la integración entre interfaz, controladores, base de datos y almacenamiento.

En relación con la integridad, la estructura de diez tablas mantiene las relaciones entre usuarios, roles, permisos, categorías, audios, metadatos e historial. Las transacciones evitan registros parciales y la eliminación lógica conserva la trazabilidad. La prueba de contenido duplicado confirmó que una modificación del nombre no permite evadir la comparación SHA-256. Por tanto, el sistema ofrece controles que reducen inconsistencias frecuentes en una colección administrada únicamente mediante carpetas.

Desde la dimensión de seguridad, la solución combina contraseñas protegidas, sesiones regeneradas, control de intentos, permisos en el servidor, tokens CSRF, consultas preparadas, escape HTML y validación real de archivos. Ningún control aislado elimina todos los riesgos, pero su aplicación en diferentes capas establece una defensa coherente con el alcance del proyecto. Para producción deben añadirse las condiciones propias del entorno: HTTPS, cookies seguras, permisos mínimos, protección de la configuración y mantenimiento de respaldos.

El análisis de eficiencia presenta dos resultados diferentes. La prevención de duplicados cuenta con evidencia directa y una estructura favorable: cálculo lineal de la huella, consulta indexada y restricción única. En cambio, el rendimiento remoto todavía no dispone de valores empíricos. Los mecanismos de paginación, indexación y entrega parcial proporcionan una base apropiada, pero la velocidad final dependerá del proveedor, el volumen de datos, los tamaños de archivos y la concurrencia.

**Tabla 5.7. Síntesis del análisis de resultados**

| Dimensión | Resultado principal | Interpretación |
|---|---|---|
| Funcionalidad | Módulos integrados y operaciones principales satisfactorias | El sistema responde a los procesos definidos |
| Organización | Archivos, categorías y metadatos centralizados | Mejora la estructura documental de la biblioteca |
| Recuperación | Búsqueda multicampo, filtro y reproducción web | Facilita localizar y utilizar los recursos |
| Integridad | Transacciones, claves foráneas y eliminación lógica | Reduce registros incompletos y conserva trazabilidad |
| Duplicidad | Archivo repetido rechazado mediante SHA-256 | Evita copias aunque cambie el nombre |
| Seguridad | Autenticación, RBAC, CSRF, PDO y validación de contenido | Restringe acciones y protege las operaciones |
| Métricas | Eventos de reproducción persistidos | Los indicadores se basan en actividad verificable |
| Rendimiento local | Navegación y pruebas funcionales satisfactorias | Adecuado para la validación realizada |
| Rendimiento remoto | Sin mediciones formales en el hosting definitivo | Requiere prueba de carga y monitoreo |
| Aceptación de usuarios | Criterios definidos, evidencia final pendiente | Debe completarse durante el piloto |

Al contrastar los resultados con los objetivos, se observó cumplimiento funcional del objetivo general y de los ocho objetivos específicos. La única reserva cuantitativa corresponde a la reducción del tiempo de localización. El sistema proporciona las herramientas destinadas a lograrla, pero su impacto debe medirse mediante una comparación antes y después con usuarios reales. Esta distinción fortalece el análisis, debido a que separa la existencia de una función del efecto operativo que se espera obtener.

### Limitaciones del análisis

Los resultados deben interpretarse considerando las siguientes limitaciones:

- Las pruebas se realizaron principalmente en un entorno local controlado.
- No se documentó todavía una prueba de carga con usuarios concurrentes.
- No existen mediciones del rendimiento del hosting definitivo.
- La cantidad de archivos utilizada no representa aún el crecimiento de varios años.
- La adaptabilidad visual requiere evidencia final en los dispositivos institucionales.
- La reducción del tiempo de búsqueda y la satisfacción de los usuarios no fueron cuantificadas.
- La política de respaldo y restauración está definida, pero debe validarse periódicamente en producción.

Estas limitaciones no contradicen los resultados funcionales; delimitan el alcance de las conclusiones que pueden formularse. La aplicación puede considerarse funcionalmente satisfactoria en desarrollo, pero su comportamiento a gran escala y su impacto organizacional requieren observación durante la implantación.

### Valoración final

Los resultados permiten concluir que el sistema ofrece una respuesta coherente al problema planteado. La centralización evita que la información descriptiva permanezca separada de los archivos; las categorías y metadatos proporcionan criterios uniformes; la búsqueda facilita la recuperación; SHA-256 reduce la duplicidad; y el control de permisos limita las acciones según las responsabilidades del usuario. La reproducción y el historial convierten la biblioteca en una herramienta operativa y generan información para las métricas.

La implementación utiliza tecnologías compatibles con el entorno previsto y mantiene una estructura que puede ampliarse. Los aspectos pendientes corresponden principalmente a la transición desde el ambiente local hacia la operación real: configuración segura, medición de rendimiento, validación con usuarios, monitoreo y respaldo.

En consecuencia, el sistema se encuentra en condiciones de pasar a una prueba piloto controlada. Durante esa etapa deben recopilarse tiempos de respuesta, resultados de concurrencia, mediciones de búsqueda, observaciones de los usuarios e incidencias operativas. Con esas evidencias será posible completar la validación en producción y determinar cuantitativamente el aporte del sistema a la gestión de los recursos sonoros de Arca de Salvación Radio 95.3 FM.
