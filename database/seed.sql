INSERT IGNORE INTO roles (id_rol,nombre,descripcion) VALUES
(1,'Administrador','Acceso completo al sistema'),
(2,'Usuario autorizado','Acceso operativo según permisos asignados');

INSERT IGNORE INTO permisos (id_permiso,codigo,nombre) VALUES
(1,'*','Acceso completo'),(2,'audios.ver','Consultar y reproducir audios'),
(3,'audios.crear','Registrar audios'),(4,'audios.editar','Editar audios'),
(5,'audios.eliminar','Eliminar audios'),(6,'metricas.ver','Consultar métricas completas');

INSERT IGNORE INTO rol_permisos (id_rol,id_permiso) VALUES (1,1),(2,2);

INSERT IGNORE INTO categorias (nombre,descripcion) VALUES
('Canciones','Música y contenido musical'),('Anuncios','Anuncios comerciales'),
('Cuñas','Cuñas radiales'),('Promocionales','Contenido promocional'),
('Programas grabados','Grabaciones de programas emitidos');

