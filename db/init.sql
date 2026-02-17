-- Inicialización de base de datos Affinity

CREATE DATABASE IF NOT EXISTS `Affinity`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

USE `Affinity`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- TABLAS ------------------------------------------------------------

CREATE TABLE `config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `estado` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `contextos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `contextos` (`id`, `nombre`) VALUES
  (1, 'publicidad'),
  (2, 'todos los servicios'),
  (3, 'tension arterial'),
  (4, 'menu seleccion'),
  (5, 'pago'),
  (6, 'reposo'),
  (7, 'clima'),
  (8, 'peso altura')
ON DUPLICATE KEY UPDATE
  `nombre` = VALUES(`nombre`);

CREATE TABLE `historial_servicios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_servicio` int NOT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `servicio_historial` (`id_servicio`),
  CONSTRAINT `servicio_historial`
    FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `imagenes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `nombre_original` varchar(255) DEFAULT NULL,
  `duracion` double NOT NULL,
  `id_programa` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FKY_programa` (`id_programa`),
  CONSTRAINT `FKY_programa`
    FOREIGN KEY (`id_programa`) REFERENCES `programas` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `programas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `contexto` int NOT NULL,
  `texto` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FKY_contexto` (`contexto`),
  CONSTRAINT `FKY_contexto`
    FOREIGN KEY (`contexto`) REFERENCES `contextos` (`id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `programa_paquetes` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `id_programa` int NOT NULL,
  `version` int NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `contexto` varchar(32) NOT NULL,
  `category` varchar(64) NOT NULL,
  `zip_name` varchar(255) NOT NULL,
  `zip_size_bytes` int unsigned NOT NULL,
  `zip_sha256` char(64) NOT NULL,
  `zip_blob` longblob NOT NULL,
  `manifest_json` longtext DEFAULT NULL,
  `missing_files_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_programa_version` (`id_programa`,`version`),
  KEY `idx_programa_created` (`id_programa`,`created_at`),
  CONSTRAINT `fk_paquete_programa`
    FOREIGN KEY (`id_programa`) REFERENCES `programas` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `promociones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `nombre_producto` varchar(255) NOT NULL,
  `dir_imagen` varchar(255) NOT NULL,
  `frecuencia_cliente` double NOT NULL,
  `frecuencia_no_cliente` double NOT NULL,
  `estado` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `servicios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `precio` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `ticket` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `cl_1` text NOT NULL,
  `cl_2` text NOT NULL,
  `cl_3` text NOT NULL,
  `cl_4` text NOT NULL,
  `cl_5` text NOT NULL,
  `pl_1` text NOT NULL,
  `pl_2` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `video` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `nombre_original` varchar(255) DEFAULT NULL,
  `id_programa` int NOT NULL,
  `duracion` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FKY_programa_video` (`id_programa`),
  CONSTRAINT `FKY_programa_video`
    FOREIGN KEY (`id_programa`) REFERENCES `programas` (`id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
