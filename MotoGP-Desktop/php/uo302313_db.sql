-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-12-2025 a las 12:01:18
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `UO302313_DB`
--
CREATE DATABASE IF NOT EXISTS `UO302313_DB` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci;
USE `UO302313_DB`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `observaciones_facilitador`
--

CREATE TABLE `observaciones_facilitador` (
  `id_observacion` int(11) NOT NULL,
  `id_prueba` int(11) NOT NULL,
  `comentarios_facilitador` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pruebas_usabilidad`
--

CREATE TABLE `pruebas_usabilidad` (
  `id_prueba` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `dispositivo` enum('ordenador','tableta','telefono') NOT NULL,
  `tiempo_segundos` int(10) UNSIGNED NOT NULL,
  `completada` tinyint(1) NOT NULL,
  `comentarios_usuario` text DEFAULT NULL,
  `propuestas_mejora` text DEFAULT NULL,
  `valoracion` tinyint(3) UNSIGNED NOT NULL CHECK (`valoracion` between 0 and 10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `profesion` varchar(100) NOT NULL,
  `edad` tinyint(3) UNSIGNED NOT NULL,
  `genero` varchar(20) NOT NULL,
  `pericia_informatica` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas_prueba`
--

CREATE TABLE `respuestas_prueba` (
  `id_prueba` int(11) NOT NULL,
  `num_pregunta` tinyint(3) UNSIGNED NOT NULL,
  `respuesta` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `observaciones_facilitador`
--
ALTER TABLE `observaciones_facilitador`
  ADD PRIMARY KEY (`id_observacion`),
  ADD KEY `fk_obs_prueba` (`id_prueba`);

--
-- Indices de la tabla `pruebas_usabilidad`
--
ALTER TABLE `pruebas_usabilidad`
  ADD PRIMARY KEY (`id_prueba`),
  ADD KEY `fk_prueba_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Indices de la tabla `respuestas_prueba`
--
ALTER TABLE `respuestas_prueba`
  ADD PRIMARY KEY (`id_prueba`,`num_pregunta`),
  ADD KEY `fk_resp_prueba` (`id_prueba`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `observaciones_facilitador`
--
ALTER TABLE `observaciones_facilitador`
  MODIFY `id_observacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pruebas_usabilidad`
--
ALTER TABLE `pruebas_usabilidad`
  MODIFY `id_prueba` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `observaciones_facilitador`
--
ALTER TABLE `observaciones_facilitador`
  ADD CONSTRAINT `fk_obs_prueba` FOREIGN KEY (`id_prueba`) REFERENCES `pruebas_usabilidad` (`id_prueba`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pruebas_usabilidad`
--
ALTER TABLE `pruebas_usabilidad`
  ADD CONSTRAINT `fk_prueba_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `respuestas_prueba`
--
ALTER TABLE `respuestas_prueba`
  ADD CONSTRAINT `fk_resp_prueba` FOREIGN KEY (`id_prueba`) REFERENCES `pruebas_usabilidad` (`id_prueba`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
