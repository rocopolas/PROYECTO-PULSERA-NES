-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 24-06-2025 a las 02:16:52
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `nes`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradorxpulsera`
--

CREATE TABLE `administradorxpulsera` (
  `id_usuario` int(11) NOT NULL,
  `id_pulsera` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administradorxpulsera`
--

INSERT INTO `administradorxpulsera` (`id_usuario`, `id_pulsera`, `fecha_creacion`) VALUES
(1, 1, '2025-06-23 23:57:45'),
(1, 2, '2025-06-24 00:15:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigos_invitacion`
--

CREATE TABLE `codigos_invitacion` (
  `id` int(11) NOT NULL,
  `id_pulsera` int(11) NOT NULL,
  `codigo` varchar(32) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_uso` timestamp NULL DEFAULT NULL,
  `id_usuario_uso` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `codigos_invitacion`
--

INSERT INTO `codigos_invitacion` (`id`, `id_pulsera`, `codigo`, `fecha_creacion`, `fecha_uso`, `id_usuario_uso`) VALUES
(2, 1, '3e6589449be59310e66574ba78bee4a6', '2025-06-23 23:46:01', '2025-06-23 23:50:43', 2),
(3, 1, '046001224b8a61c7308f28c228d9245e', '2025-06-23 23:52:50', '2025-06-23 23:53:19', 2),
(4, 1, '12547c4571fc2aa972f874cbbfdb05b4', '2025-06-23 23:54:02', '2025-06-23 23:54:17', 2),
(5, 1, '36f9c1c2b2bfff8fe692c05a3e6df812', '2025-06-23 23:56:56', '2025-06-23 23:57:09', 2),
(6, 1, 'b8661a460c4779b978f7bb831af53888', '2025-06-23 23:58:04', '2025-06-23 23:58:20', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historialpulseras`
--

CREATE TABLE `historialpulseras` (
  `id` int(11) NOT NULL,
  `id_pulsera` int(11) DEFAULT NULL,
  `estado_pulsera` enum('encendido','apagado','error') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historialpulseras`
--

INSERT INTO `historialpulseras` (`id`, `id_pulsera`, `estado_pulsera`, `timestamp`) VALUES
(1, 1, 'encendido', '2025-06-09 23:40:21'),
(2, 1, 'apagado', '2025-06-09 23:53:24'),
(3, 1, 'encendido', '2025-06-09 23:53:30'),
(4, 1, 'apagado', '2025-06-09 23:53:48'),
(5, 2, 'encendido', '2025-06-09 23:53:53'),
(6, 2, 'apagado', '2025-06-09 23:53:58'),
(7, 1, 'encendido', '2025-06-09 23:56:17'),
(8, 1, 'apagado', '2025-06-09 23:56:28'),
(9, 2, 'encendido', '2025-06-09 23:57:25'),
(10, 2, 'apagado', '2025-06-09 23:57:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pulseras`
--

CREATE TABLE `pulseras` (
  `id` int(11) NOT NULL,
  `fecha_emision` date NOT NULL,
  `version` varchar(20) DEFAULT NULL,
  `funcionamiento` enum('funcionando','averiada','mantenimiento') DEFAULT 'funcionando',
  `alias` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pulseras`
--

INSERT INTO `pulseras` (`id`, `fecha_emision`, `version`, `funcionamiento`, `alias`, `created_at`) VALUES
(1, '2006-08-18', '1.0', 'mantenimiento', 'Marta', '2025-06-09 23:24:10'),
(2, '2007-01-24', '1.0', 'funcionando', 'Pato (bullrich)', '2025-06-09 23:28:00'),
(3, '2006-06-23', '6.6.6', 'funcionando', 'Colucci', '2025-06-23 21:57:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `direccion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `contraseña`, `email`, `direccion`, `created_at`) VALUES
(1, 'roco', '$2y$10$H0te.I6j641jG/r/99d1nu3dzUO0k/vdxUOkN1TdukqBOkyjfM4ua', 'roco@perez.com', 'ortega y gasset 2253', '2025-06-09 23:20:35'),
(2, 'Rudka', '$2y$10$fDe4W32ViPGcFKkYHM.i.OWKmfVuRXTPxZqRaK62lr4NyBitf0ntC', 'rudka@rudka.com', 'lol 123', '2025-06-23 23:24:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuariosxpulseras`
--

CREATE TABLE `usuariosxpulseras` (
  `id_usuario` int(11) NOT NULL,
  `id_pulsera` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuariosxpulseras`
--

INSERT INTO `usuariosxpulseras` (`id_usuario`, `id_pulsera`, `fecha_asignacion`) VALUES
(1, 1, '2025-06-23 23:57:44'),
(1, 2, '2025-06-24 00:15:20'),
(2, 1, '2025-06-23 23:58:20');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `codigos_invitacion`
--
ALTER TABLE `codigos_invitacion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `fk_codigo_pulsera` (`id_pulsera`),
  ADD KEY `fk_codigo_usuario` (`id_usuario_uso`),
  ADD KEY `idx_codigo` (`codigo`);

--
-- Indices de la tabla `historialpulseras`
--
ALTER TABLE `historialpulseras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_historialpulseras_timestamp` (`timestamp`),
  ADD KEY `idx_historialpulseras_pulsera` (`id_pulsera`);

--
-- Indices de la tabla `pulseras`
--
ALTER TABLE `pulseras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `alias` (`alias`),
  ADD KEY `idx_pulseras_alias` (`alias`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_usuarios_email` (`email`);

--
-- Indices de la tabla `usuariosxpulseras`
--
ALTER TABLE `usuariosxpulseras`
  ADD PRIMARY KEY (`id_usuario`,`id_pulsera`),
  ADD KEY `id_pulsera` (`id_pulsera`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `codigos_invitacion`
--
ALTER TABLE `codigos_invitacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `historialpulseras`
--
ALTER TABLE `historialpulseras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `pulseras`
--
ALTER TABLE `pulseras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `codigos_invitacion`
--
ALTER TABLE `codigos_invitacion`
  ADD CONSTRAINT `fk_codigo_pulsera` FOREIGN KEY (`id_pulsera`) REFERENCES `pulseras` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_codigo_usuario` FOREIGN KEY (`id_usuario_uso`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `historialpulseras`
--
ALTER TABLE `historialpulseras`
  ADD CONSTRAINT `historialpulseras_ibfk_1` FOREIGN KEY (`id_pulsera`) REFERENCES `pulseras` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuariosxpulseras`
--
ALTER TABLE `usuariosxpulseras`
  ADD CONSTRAINT `usuariosxpulseras_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuariosxpulseras_ibfk_2` FOREIGN KEY (`id_pulsera`) REFERENCES `pulseras` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
