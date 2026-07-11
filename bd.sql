-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 11-07-2026 a las 00:49:37
-- Versión del servidor: 8.0.43
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `empresa_poocrud`
--
CREATE DATABASE IF NOT EXISTS `empresa_poocrud` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `empresa_poocrud`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

DROP TABLE IF EXISTS `departamentos`;
CREATE TABLE IF NOT EXISTS `departamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `gerente_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `fk_departamento_gerente` (`gerente_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `departamentos`
--

INSERT INTO `departamentos` (`id`, `nombre`, `gerente_id`) VALUES
(9, 'finanzas', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

DROP TABLE IF EXISTS `empleados`;
CREATE TABLE IF NOT EXISTS `empleados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `direccion` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `correo` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_contrato` date NOT NULL,
  `salario` int NOT NULL,
  `departamento_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_empleados_nombre` (`nombre`),
  KEY `fk_empleado_departamento` (`departamento_id`)
) ;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `nombre`, `direccion`, `telefono`, `correo`, `fecha_contrato`, `salario`, `departamento_id`) VALUES
(6, '111111', '1111', '1111111', 'fdf@er.nl', '2020-09-09', 1212121, NULL),
(7, 'marta', 'los alelies 1550', '98745632', 'maria@gmail.com', '2025-01-02', 500000, NULL),
(8, 'df', 'fdf', '88888888', 'd@gmail.com', '2025-01-01', 50, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos`
--

DROP TABLE IF EXISTS `proyectos`;
CREATE TABLE IF NOT EXISTS `proyectos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `director_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `idx_proyectos_nombre` (`nombre`),
  KEY `fk_proyecto_director` (`director_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proyectos`
--

INSERT INTO `proyectos` (`id`, `nombre`, `descripcion`, `fecha_inicio`, `director_id`) VALUES
(6, '222222', '22222', '2023-08-08', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto_participantes`
--

DROP TABLE IF EXISTS `proyecto_participantes`;
CREATE TABLE IF NOT EXISTS `proyecto_participantes` (
  `proyecto_id` int NOT NULL,
  `empleado_id` int NOT NULL,
  PRIMARY KEY (`proyecto_id`,`empleado_id`),
  KEY `fk_pp_empleado` (`empleado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proyecto_participantes`
--

INSERT INTO `proyecto_participantes` (`proyecto_id`, `empleado_id`) VALUES
(6, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `hash_contrasenia` varbinary(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `hash_contrasenia`) VALUES
('', '', 0x24326224313224574774386154796b68626956696a614230616649352e4b71614e5a655a3233487577556f2e4450444552766762483741686f39666d),
('1111', 'ana', 0x243262243132245750505a58644c6e4c366a364f3277616a4c30655765533748326178373969764f6567796f734235564a63353269413232314d6979),
('2', 'jose', 0x2432622431322449786c34725236737832736762672e6a346d484d794f5670454a475243426a5443546a79594763366f3079704948477266526f3571),
('3', 'hola', 0x24326224313224792e3538356a6e73356f75486f73365a2e614c41412e6c51324b6d4858764d5846306b66716547782e6963314c444e6b6e552f4543),
('56', 'jajaja', 0x243262243132246e77696552657162346474643862332f58526f66414f4437336a6e496549524c5466545774487034564b794f34562f6e7958444f32),
('admin', 'Administrador', 0x243262243132245a4b7550724f5472336b563737746f69616a4754524f376a744e306973755234782f643946444479774d462f447856692e58374247);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD CONSTRAINT `fk_departamento_gerente` FOREIGN KEY (`gerente_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `fk_empleado_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD CONSTRAINT `fk_proyecto_director` FOREIGN KEY (`director_id`) REFERENCES `empleados` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `proyecto_participantes`
--
ALTER TABLE `proyecto_participantes`
  ADD CONSTRAINT `fk_pp_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pp_proyecto` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Base de datos: `inmobiliaria_pnk`
--
CREATE DATABASE IF NOT EXISTS `inmobiliaria_pnk` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `inmobiliaria_pnk`;
--
-- Base de datos: `pnk_inmobiliaria`
--
CREATE DATABASE IF NOT EXISTS `pnk_inmobiliaria` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pnk_inmobiliaria`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `galeria_propiedad`
--

DROP TABLE IF EXISTS `galeria_propiedad`;
CREATE TABLE IF NOT EXISTS `galeria_propiedad` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_propiedad` int NOT NULL,
  `ruta_imagen` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT '0',
  `orden` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_propiedad` (`id_propiedad`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `galeria_propiedad`
--

INSERT INTO `galeria_propiedad` (`id`, `id_propiedad`, `ruta_imagen`, `es_principal`, `orden`) VALUES
(15, 5, 'img/Captura%20de%20pantalla%202026-05-30%20220818.png', 1, 0),
(16, 6, 'img/Captura%20de%20pantalla%202026-05-30%20221540.png', 1, 0),
(17, 7, 'img/Captura%20de%20pantalla%202026-05-30%20221921.png', 1, 0),
(18, 8, 'img/propiedad4-01.png', 1, 0),
(19, 9, 'img/propiedad5-01.png', 1, 0),
(20, 10, 'img/propiedad6-01.png', 1, 0),
(21, 11, 'img/propiedad7-01.png', 1, 0),
(22, 12, 'img/propiedad8-01.png', 1, 0),
(23, 13, 'img/propiedad9-01.png', 1, 0),
(24, 14, 'img/propiedad10-01.png', 1, 0),
(25, 15, 'img/propiedades/15/foto_1_1782927138.jpg', 1, 0),
(26, 15, 'img/propiedades/15/foto_2_1782927138.jpg', 0, 1),
(27, 15, 'img/propiedades/15/foto_3_1782927138.jpg', 0, 2),
(28, 15, 'img/propiedades/15/foto_4_1782927138.webp', 0, 3),
(29, 15, 'img/propiedades/15/foto_5_1782927138.webp', 0, 4),
(30, 15, 'img/propiedades/15/foto_6_1782927138.jpg', 0, 5),
(31, 15, 'img/propiedades/15/foto_7_1782927138.jpg', 0, 6),
(32, 15, 'img/propiedades/15/foto_8_1782927138.jpg', 0, 7),
(33, 15, 'img/propiedades/15/foto_9_1782927138.png', 0, 8),
(34, 15, 'img/propiedades/15/foto_10_1782927138.webp', 0, 9),
(65, 19, 'img/propiedades/19/foto_1_1783309029.jpg', 0, 0),
(66, 19, 'img/propiedades/19/foto_2_1783309029.jpg', 0, 1),
(67, 19, 'img/propiedades/19/foto_3_1783309029.jpg', 0, 2),
(68, 19, 'img/propiedades/19/foto_4_1783309029.jpg', 0, 3),
(69, 19, 'img/propiedades/19/foto_5_1783309029.jpg', 1, 4),
(70, 19, 'img/propiedades/19/foto_6_1783309029.jpg', 0, 5),
(71, 19, 'img/propiedades/19/foto_7_1783309029.jpg', 0, 6),
(72, 19, 'img/propiedades/19/foto_8_1783309029.jpg', 0, 7),
(73, 19, 'img/propiedades/19/foto_9_1783309029.jpg', 0, 8),
(74, 19, 'img/propiedades/19/foto_10_1783309029.jpg', 0, 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gestores`
--

DROP TABLE IF EXISTS `gestores`;
CREATE TABLE IF NOT EXISTS `gestores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rut` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `correo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sexo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `certificado_pdf` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('pendiente','aprobado','rechazado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `fecha_postulacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rut` (`rut`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `gestores`
--

INSERT INTO `gestores` (`id`, `rut`, `nombre`, `fecha_nacimiento`, `correo`, `password`, `sexo`, `telefono`, `certificado_pdf`, `estado`, `fecha_postulacion`) VALUES
(3, '11.111.111-1', 'jose perez', '2026-06-24', 'jose.perez@gmail.com', '$2y$10$UifPhAqvG7ekwZyyo9Jn3OWedImr91oA8oj45jGFrXIxC0BGM0zDq', 'Masculino', '999999999', 'uploads/gestores/certificado_111111111_1781931935.pdf', 'aprobado', '2026-06-20 01:05:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propiedades`
--

DROP TABLE IF EXISTS `propiedades`;
CREATE TABLE IF NOT EXISTS `propiedades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` enum('Casa','Departamento','Terreno') COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_publicacion` date NOT NULL,
  `provincia` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comuna` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sector` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dormitorios` int NOT NULL DEFAULT '0',
  `banos` int NOT NULL DEFAULT '0',
  `area_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `area_construida` decimal(10,2) NOT NULL DEFAULT '0.00',
  `precio_clp` decimal(14,2) NOT NULL DEFAULT '0.00',
  `precio_uf` decimal(10,2) NOT NULL DEFAULT '0.00',
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `visita` enum('si','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'si',
  `bodega` enum('si','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `estacionamiento` int NOT NULL DEFAULT '0',
  `logia` int NOT NULL DEFAULT '0',
  `cocina_amoblada` int NOT NULL DEFAULT '0',
  `antejardin` int NOT NULL DEFAULT '0',
  `patio_trasero` int NOT NULL DEFAULT '0',
  `piscina` int NOT NULL DEFAULT '0',
  `id_gestor` int DEFAULT NULL,
  `estado_gestion` enum('sin_asignar','asignada','en_gestion','publicada','pausada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sin_asignar',
  `fecha_asignacion` datetime DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('pendiente','activo','inactivo','aprobado','rechazado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `id_propietario` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `propiedades`
--

INSERT INTO `propiedades` (`id`, `tipo`, `fecha_publicacion`, `provincia`, `comuna`, `sector`, `dormitorios`, `banos`, `area_total`, `area_construida`, `precio_clp`, `precio_uf`, `descripcion`, `visita`, `bodega`, `estacionamiento`, `logia`, `cocina_amoblada`, `antejardin`, `patio_trasero`, `piscina`, `id_gestor`, `estado_gestion`, `fecha_asignacion`, `fecha_creacion`, `estado`, `id_propietario`) VALUES
(5, 'Casa', '2026-05-30', 'Elqui', 'Coquimbo', 'Peñuelas', 5, 4, 300.00, 180.00, 560000000.00, 14358.00, 'Casa amplia y moderna en Peñuelas, Coquimbo.', 'si', 'si', 2, 1, 1, 1, 1, 0, NULL, 'sin_asignar', NULL, '2026-06-27 22:21:23', 'activo', NULL),
(6, 'Casa', '2026-05-30', 'Elqui', 'La Serena', 'San Joaquín', 4, 3, 372.50, 140.00, 385640810.00, 9500.00, 'Casa en venta en San Joaquín, La Serena.', 'si', 'no', 2, 1, 1, 1, 1, 0, NULL, 'sin_asignar', NULL, '2026-06-27 22:21:23', 'activo', NULL),
(7, 'Casa', '2026-05-30', 'Elqui', 'La Serena', 'Algarrobito', 9, 6, 5000.00, 650.00, 253020965.00, 6233.00, 'Casa turística en Algarrobito, La Serena.', 'si', 'si', 4, 1, 1, 1, 1, 0, NULL, 'sin_asignar', NULL, '2026-06-27 22:21:23', 'activo', NULL),
(8, 'Casa', '2026-05-30', 'Elqui', 'La Serena', 'Serena Oriente', 3, 2, 252.32, 95.00, 223265732.00, 5500.00, 'Casa en venta en Serena Oriente, La Serena.', 'si', 'no', 2, 1, 1, 1, 1, 0, NULL, 'sin_asignar', NULL, '2026-06-27 22:21:23', 'activo', NULL),
(9, 'Departamento', '2026-05-30', 'Elqui', 'La Serena', 'Av. Pacífico', 2, 2, 78.00, 78.00, 182671963.00, 4500.00, 'Departamento con hermosa vista al mar, La Serena.', 'si', 'no', 1, 1, 1, 0, 0, 0, NULL, 'sin_asignar', NULL, '2026-06-27 22:21:23', 'activo', NULL),
(10, 'Departamento', '2026-05-30', 'Elqui', 'La Serena', 'Laguna del Mar', 3, 2, 96.34, 96.34, 207028225.00, 5100.00, 'Departamento en Laguna del Mar, La Serena.', 'si', 'no', 1, 1, 1, 0, 0, 0, NULL, 'sin_asignar', NULL, '2026-06-27 22:21:23', 'activo', NULL),
(11, 'Terreno', '2026-05-30', 'Limarí', 'Ovalle', 'Flor del Norte', 0, 0, 5000.00, 0.00, 811875400.00, 20000.00, 'Terreno en Talhuén, Ovalle.', 'si', 'no', 0, 0, 0, 0, 0, 0, NULL, 'sin_asignar', NULL, '2026-06-27 22:21:23', 'activo', NULL),
(12, 'Casa', '2026-05-30', 'Elqui', 'Coquimbo', 'Nova Hacienda', 3, 2, 144.00, 90.00, 138018818.00, 3400.00, 'Casa en Nova Hacienda, Coquimbo.', 'si', 'no', 1, 1, 1, 1, 1, 0, NULL, 'sin_asignar', NULL, '2026-06-27 22:21:23', 'activo', NULL),
(13, 'Casa', '2026-05-30', 'Elqui', 'Coquimbo', 'San Juan', 3, 1, 180.00, 90.00, 78548944.00, 1935.00, 'Casa Olivar Bajo, Coquimbo.', 'si', 'no', 1, 0, 1, 1, 1, 0, NULL, 'sin_asignar', NULL, '2026-06-27 22:21:23', 'activo', NULL),
(14, 'Terreno', '2026-05-30', 'Limarí', 'Ovalle', 'Mineral de Talca', 0, 0, 5000.00, 0.00, 7900000.00, 194.61, 'Parcela 5000 m² en Ovalle.', 'si', 'no', 0, 0, 0, 0, 0, 0, NULL, 'sin_asignar', NULL, '2026-06-27 22:21:23', 'activo', NULL),
(15, 'Terreno', '2026-07-01', 'Elqui', 'Coquimbo', 'Puerto Aldea', 0, 0, 5000.00, 1.00, 30000000.00, 734.88, 'Se vende parcela de 5.000 m2 ubicada en el sector de Puerto Aldea, comuna de Coquimbo. La propiedad se encuentra totalmente cercada, con portón de acceso particular, dentro de un loteo con acceso controlado y portón eléctrico que garantiza seguridad y privacidad.\r\n\r\nCuenta con agua y energía eléctrica, y se emplaza en un entorno natural único, a pocos minutos del mar. Posee accesos tanto desde el sur por el Fundo El Tangue, como desde el norte por el camino costero proveniente de Tongoy, lo que facilita su conectividad y acceso durante todo el año.\r\n\r\nUna excelente oportunidad para quienes buscan tranquilidad, naturaleza y una inversión segura en la costa de la Región de Coquimbo', 'si', 'no', 0, 0, 0, 0, 0, 0, 3, 'asignada', '2026-07-05 22:21:31', '2026-07-01 13:32:18', 'activo', NULL),
(19, 'Departamento', '2026-07-05', 'Elqui', 'Coquimbo', 'Villa Dominante', 1, 1, 0.00, 0.00, 98000000.00, 2400.00, 'Ubicación: Edificio Verne, Coquimbo\r\nExcelente oportunidad en el corazón de Coquimbo. Departamento ubicado en sector céntrico, con acceso inmediato a hospital, estadio, colegios, supermercados, farmacias y más. Conectividad privilegiada a través de Ruta 5 Norte y cercanía a transporte público.\r\nCaracterísticas del Departamento:\r\nLiving-comedor amplio y luminoso\r\nCocina americana amoblada y equipada\r\n1 habitación Sala comedor\r\n1 baño completo\r\n1 estacionamiento\r\nCaracterísticas del Condominio:\r\nPiscina\r\nQuincho\r\nJuegos infantiles\r\nAcceso controlado las 24 horas\r\nIdeal para profesionales, inversionistas o quienes buscan una excelente ubicación con servicios a la mano. Para más información o agendar visita, contáctanos.', 'si', 'no', 1, 0, 0, 0, 0, 1, NULL, 'sin_asignar', NULL, '2026-07-05 23:37:09', 'activo', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propietarios`
--

DROP TABLE IF EXISTS `propietarios`;
CREATE TABLE IF NOT EXISTS `propietarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rut` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `correo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sexo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_propiedad` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('pendiente','activo','rechazado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rut` (`rut`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `propietarios`
--

INSERT INTO `propietarios` (`id`, `rut`, `nombre`, `fecha_nacimiento`, `correo`, `password`, `sexo`, `telefono`, `numero_propiedad`, `estado`, `fecha_registro`) VALUES
(2, '17.982.315-2', 'maricel videla', '2005-10-12', 'mvidela@gmail.com', '$2y$10$83XP53H9zGayNru1TxTiY.m8qk7vIiZMo1OC/d4lTskqjOorBH/pC', 'Femenino', '645656565', '8855-555-5665', 'activo', '2026-07-01 13:23:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_visita`
--

DROP TABLE IF EXISTS `solicitudes_visita`;
CREATE TABLE IF NOT EXISTS `solicitudes_visita` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_propiedad` int DEFAULT NULL,
  `codigo_propiedad` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titulo_propiedad` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_interesado` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo_interesado` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono_interesado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci,
  `estado` enum('pendiente','asignada','contactado','coordinada','cerrada','rechazada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `id_gestor` int DEFAULT NULL,
  `fecha_asignacion` datetime DEFAULT NULL,
  `fecha_solicitud` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_correo_interesado` (`correo_interesado`),
  KEY `idx_propiedad` (`id_propiedad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('administrador','propietario','gestor') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'propietario',
  `estado` enum('activo','pendiente','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `password`, `rol`, `estado`, `fecha_creacion`) VALUES
(1, 'Administrador', 'Adm@inmobiliria.cl', '$2y$10$R/qigIfeodQs.SFxzf0bGeOMpLTwztR40LZdyQDUw4M1zVbRxJoD2', 'administrador', 'activo', '2026-06-27 21:47:39');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `galeria_propiedad`
--
ALTER TABLE `galeria_propiedad`
  ADD CONSTRAINT `galeria_propiedad_ibfk_1` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
