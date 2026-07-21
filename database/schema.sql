SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS roles (
  id_rol INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE,
  descripcion VARCHAR(255) NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permisos (
  id_permiso INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(80) NOT NULL UNIQUE,
  nombre VARCHAR(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rol_permisos (
  id_rol INT UNSIGNED NOT NULL,
  id_permiso INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_rol,id_permiso),
  CONSTRAINT fk_rp_rol FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE CASCADE,
  CONSTRAINT fk_rp_permiso FOREIGN KEY (id_permiso) REFERENCES permisos(id_permiso) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS usuarios (
  id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_rol INT UNSIGNED NOT NULL,
  nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
  correo VARCHAR(150) NOT NULL UNIQUE,
  contrasena_hash VARCHAR(255) NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ultima_actualizacion DATETIME NULL,
  CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) REFERENCES roles(id_rol),
  INDEX idx_usuario_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS perfiles_usuarios (
  id_perfil INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT UNSIGNED NOT NULL UNIQUE,
  nombre_completo VARCHAR(150) NOT NULL,
  foto_perfil VARCHAR(255) NULL,
  telefono VARCHAR(25) NULL,
  fecha_actualizacion DATETIME NULL,
  CONSTRAINT fk_perfil_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
  id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  descripcion VARCHAR(255) NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS archivos_audio (
  id_audio BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_categoria INT UNSIGNED NOT NULL,
  id_usuario INT UNSIGNED NOT NULL,
  nombre_original VARCHAR(255) NOT NULL,
  nombre_almacenado VARCHAR(255) NOT NULL UNIQUE,
  ruta_archivo VARCHAR(500) NOT NULL,
  mime_type VARCHAR(100) NOT NULL,
  extension VARCHAR(10) NOT NULL,
  tamano_bytes BIGINT UNSIGNED NOT NULL,
  duracion_segundos INT UNSIGNED NULL,
  hash_sha256 CHAR(64) NOT NULL UNIQUE,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL,
  CONSTRAINT fk_audio_categoria FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria),
  CONSTRAINT fk_audio_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
  INDEX idx_audio_categoria (id_categoria), INDEX idx_audio_usuario (id_usuario),
  INDEX idx_audio_fecha_estado (estado,fecha_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS metadatos_audio (
  id_metadato BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_audio BIGINT UNSIGNED NOT NULL UNIQUE,
  titulo VARCHAR(200) NOT NULL,
  artista VARCHAR(150) NULL,
  locutor VARCHAR(150) NULL,
  cliente VARCHAR(150) NULL,
  palabras_clave TEXT NULL,
  fecha_produccion DATE NULL,
  descripcion TEXT NULL,
  CONSTRAINT fk_meta_audio FOREIGN KEY (id_audio) REFERENCES archivos_audio(id_audio) ON DELETE CASCADE,
  INDEX idx_meta_titulo (titulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS historial_reproducciones (
  id_reproduccion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_audio BIGINT UNSIGNED NOT NULL,
  id_usuario INT UNSIGNED NULL,
  fecha_reproduccion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ip_hash CHAR(64) NULL,
  CONSTRAINT fk_rep_audio FOREIGN KEY (id_audio) REFERENCES archivos_audio(id_audio),
  CONSTRAINT fk_rep_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
  INDEX idx_rep_fecha_audio (fecha_reproduccion,id_audio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS intentos_login (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  identificador_hash CHAR(64) NOT NULL,
  ip_hash CHAR(64) NOT NULL,
  exitoso TINYINT(1) NOT NULL DEFAULT 0,
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_intentos (identificador_hash,ip_hash,fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS=1;

