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
  `id_programa` int NOT NULL,
  `duracion` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FKY_programa_video` (`id_programa`),
  CONSTRAINT `FKY_programa_video`
    FOREIGN KEY (`id_programa`) REFERENCES `programas` (`id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
