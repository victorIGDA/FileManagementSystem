-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 31-07-2026 a las 03:36:32
-- Versión del servidor: 5.7.24
-- Versión de PHP: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gestor_audio`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos_audio`
--

CREATE TABLE `archivos_audio` (
  `id_audio` bigint(20) UNSIGNED NOT NULL,
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `nombre_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_almacenado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta_archivo` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extension` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tamano_bytes` bigint(20) UNSIGNED NOT NULL,
  `duracion_segundos` int(10) UNSIGNED DEFAULT NULL,
  `hash_sha256` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `archivos_audio`
--

INSERT INTO `archivos_audio` (`id_audio`, `id_categoria`, `id_usuario`, `nombre_original`, `nombre_almacenado`, `ruta_archivo`, `mime_type`, `extension`, `tamano_bytes`, `duracion_segundos`, `hash_sha256`, `estado`, `fecha_registro`, `fecha_actualizacion`) VALUES
(1, 5, 1, 'Momento Interactivo con el Pastor Edwin Santana. - Arca de Salvacion Radio.mp3', '7c8d9add4e001a3e72767150d554b77991c5debf.mp3', 'C:\\MAMP\\htdocs\\FileManagementSystem/storage/audio\\7c8d9add4e001a3e72767150d554b77991c5debf.mp3', 'audio/mpeg', 'mp3', 34337035, NULL, 'fad45844f7ae9a56da28864a5a74634b830404de5ed6449b902f4f0e1a560312', 1, '2026-07-25 00:24:59', '2026-07-25 00:25:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`, `estado`, `fecha_registro`) VALUES
(1, 'Canciones', 'Musica y contenido musical', 1, '2026-07-21 16:04:35'),
(2, 'Anuncios', 'Anuncios comerciales', 1, '2026-07-21 16:04:35'),
(3, 'Cuñas', 'Cuñas radiales', 1, '2026-07-21 16:04:35'),
(4, 'Promocionales', 'Contenido promocional', 1, '2026-07-21 16:04:35'),
(5, 'Programas grabados', 'Grabaciones de programas emitidos', 1, '2026-07-21 16:04:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_reproducciones`
--

CREATE TABLE `historial_reproducciones` (
  `id_reproduccion` bigint(20) UNSIGNED NOT NULL,
  `id_audio` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `fecha_reproduccion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_hash` char(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `historial_reproducciones`
--

INSERT INTO `historial_reproducciones` (`id_reproduccion`, `id_audio`, `id_usuario`, `fecha_reproduccion`, `ip_hash`) VALUES
(1, 1, 1, '2026-07-25 00:25:53', 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_login`
--

CREATE TABLE `intentos_login` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `identificador_hash` char(64) NOT NULL,
  `ip_hash` char(64) NOT NULL,
  `exitoso` tinyint(1) NOT NULL DEFAULT '0',
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `intentos_login`
--

INSERT INTO `intentos_login` (`id`, `identificador_hash`, `ip_hash`, `exitoso`, `fecha`) VALUES
(1, '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', 1, '2026-07-21 16:05:11'),
(2, '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', 1, '2026-07-21 16:06:24'),
(3, '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', 1, '2026-07-21 16:06:50'),
(4, '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', 1, '2026-07-21 16:09:24'),
(5, '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', 1, '2026-07-21 16:25:54'),
(6, '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', 1, '2026-07-21 16:27:13'),
(7, '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', 1, '2026-07-21 16:58:56'),
(8, '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', 1, '2026-07-21 17:08:23'),
(9, '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', 1, '2026-07-25 00:05:47'),
(10, '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', 0, '2026-07-25 01:06:08'),
(11, '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', 0, '2026-07-25 08:16:44'),
(12, '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', 1, '2026-07-25 08:16:51'),
(13, '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', 1, '2026-07-30 21:00:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metadatos_audio`
--

CREATE TABLE `metadatos_audio` (
  `id_metadato` bigint(20) UNSIGNED NOT NULL,
  `id_audio` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `artista` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locutor` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cliente` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `palabras_clave` text COLLATE utf8mb4_unicode_ci,
  `fecha_produccion` date DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `metadatos_audio`
--

INSERT INTO `metadatos_audio` (`id_metadato`, `id_audio`, `titulo`, `artista`, `locutor`, `cliente`, `palabras_clave`, `fecha_produccion`, `descripcion`) VALUES
(1, 1, 'Momento Interactivo con el Pastor', NULL, 'Edwin', NULL, 'Pastor', '2026-07-24', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfiles_usuarios`
--

CREATE TABLE `perfiles_usuarios` (
  `id_perfil` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `nombre_completo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto_perfil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `perfiles_usuarios`
--

INSERT INTO `perfiles_usuarios` (`id_perfil`, `id_usuario`, `nombre_completo`, `foto_perfil`, `telefono`, `fecha_actualizacion`) VALUES
(1, 1, 'Administrador del sistema', 'f429ba995c91f247143cecaffcaad3dfe7284036.jpg', NULL, '2026-07-25 00:46:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id_permiso` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id_permiso`, `codigo`, `nombre`) VALUES
(1, '*', 'Acceso completo'),
(2, 'audios.ver', 'Consultar y reproducir audios'),
(3, 'audios.crear', 'Registrar audios'),
(4, 'audios.editar', 'Editar audios'),
(5, 'audios.eliminar', 'Eliminar audios'),
(6, 'metricas.ver', 'Consultar m├®tricas completas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`, `estado`, `fecha_creacion`) VALUES
(1, 'Administrador', 'Acceso completo al sistema', 1, '2026-07-21 16:04:35'),
(2, 'Usuario autorizado', 'Acceso operativo seg├║n permisos asignados', 1, '2026-07-21 16:04:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permisos`
--

CREATE TABLE `rol_permisos` (
  `id_rol` int(10) UNSIGNED NOT NULL,
  `id_permiso` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `rol_permisos`
--

INSERT INTO `rol_permisos` (`id_rol`, `id_permiso`) VALUES
(1, 1),
(2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `id_rol` int(10) UNSIGNED NOT NULL,
  `nombre_usuario` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasena_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ultima_actualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `nombre_usuario`, `correo`, `contrasena_hash`, `estado`, `fecha_registro`, `ultima_actualizacion`) VALUES
(1, 1, 'admin', 'admin@arcadesalvacion.local', '$2y$10$KhYWOXeZXP874D7YmiaWw.8iNiXexHP9FQVz0tOEzn8HlJlzSAZ3a', 1, '2026-07-21 16:04:47', '2026-07-25 00:06:49');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `archivos_audio`
--
ALTER TABLE `archivos_audio`
  ADD PRIMARY KEY (`id_audio`),
  ADD UNIQUE KEY `nombre_almacenado` (`nombre_almacenado`),
  ADD UNIQUE KEY `hash_sha256` (`hash_sha256`),
  ADD KEY `idx_audio_categoria` (`id_categoria`),
  ADD KEY `idx_audio_usuario` (`id_usuario`),
  ADD KEY `idx_audio_fecha_estado` (`estado`,`fecha_registro`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `historial_reproducciones`
--
ALTER TABLE `historial_reproducciones`
  ADD PRIMARY KEY (`id_reproduccion`),
  ADD KEY `fk_rep_audio` (`id_audio`),
  ADD KEY `fk_rep_usuario` (`id_usuario`),
  ADD KEY `idx_rep_fecha_audio` (`fecha_reproduccion`,`id_audio`);

--
-- Indices de la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_intentos` (`identificador_hash`,`ip_hash`,`fecha`);

--
-- Indices de la tabla `metadatos_audio`
--
ALTER TABLE `metadatos_audio`
  ADD PRIMARY KEY (`id_metadato`),
  ADD UNIQUE KEY `id_audio` (`id_audio`),
  ADD KEY `idx_meta_titulo` (`titulo`);

--
-- Indices de la tabla `perfiles_usuarios`
--
ALTER TABLE `perfiles_usuarios`
  ADD PRIMARY KEY (`id_perfil`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id_permiso`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD PRIMARY KEY (`id_rol`,`id_permiso`),
  ADD KEY `fk_rp_permiso` (`id_permiso`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `fk_usuario_rol` (`id_rol`),
  ADD KEY `idx_usuario_estado` (`estado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `archivos_audio`
--
ALTER TABLE `archivos_audio`
  MODIFY `id_audio` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `historial_reproducciones`
--
ALTER TABLE `historial_reproducciones`
  MODIFY `id_reproduccion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `metadatos_audio`
--
ALTER TABLE `metadatos_audio`
  MODIFY `id_metadato` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `perfiles_usuarios`
--
ALTER TABLE `perfiles_usuarios`
  MODIFY `id_perfil` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `archivos_audio`
--
ALTER TABLE `archivos_audio`
  ADD CONSTRAINT `fk_audio_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`),
  ADD CONSTRAINT `fk_audio_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `historial_reproducciones`
--
ALTER TABLE `historial_reproducciones`
  ADD CONSTRAINT `fk_rep_audio` FOREIGN KEY (`id_audio`) REFERENCES `archivos_audio` (`id_audio`),
  ADD CONSTRAINT `fk_rep_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `metadatos_audio`
--
ALTER TABLE `metadatos_audio`
  ADD CONSTRAINT `fk_meta_audio` FOREIGN KEY (`id_audio`) REFERENCES `archivos_audio` (`id_audio`) ON DELETE CASCADE;

--
-- Filtros para la tabla `perfiles_usuarios`
--
ALTER TABLE `perfiles_usuarios`
  ADD CONSTRAINT `fk_perfil_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD CONSTRAINT `fk_rp_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rp_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
