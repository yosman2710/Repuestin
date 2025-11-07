-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-11-2025 a las 01:39:52
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `repuestos_tiramealgo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrador`
--

CREATE TABLE `administrador` (
  `id_administrador` int(11) NOT NULL,
  `nombre_administrador` varchar(45) NOT NULL,
  `correo` varchar(60) NOT NULL,
  `contrasena` varchar(16) NOT NULL,
  `cargo` varchar(15) NOT NULL,
  `intentos` int(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administrador`
--

INSERT INTO `administrador` (`id_administrador`, `nombre_administrador`, `correo`, `contrasena`, `cargo`, `intentos`) VALUES
(1, 'Respuestos TirameAlgo', 'tiramealgo_admin@gmail.com', 'admin', 'Dueño', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre_empresa` varchar(60) NOT NULL,
  `rif` varchar(15) NOT NULL,
  `telefono_empresa` varchar(13) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `nombre_encargado` varchar(60) NOT NULL,
  `cedula_encargado` varchar(13) NOT NULL,
  `telefono_encargado` varchar(13) NOT NULL,
  `correo` varchar(60) DEFAULT NULL,
  `contrasena` varchar(16) NOT NULL,
  `estado_cliente` enum('Activo','Inactivo') DEFAULT 'Activo',
  `intentos` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre_empresa`, `rif`, `telefono_empresa`, `direccion`, `nombre_encargado`, `cedula_encargado`, `telefono_encargado`, `correo`, `contrasena`, `estado_cliente`, `intentos`) VALUES
(2, 'Diesel Y Turbos Venezuela', 'J-50081587-5', '04246263179', '40012', 'Jesus jimenez', 'V-30605170', '04166626450', 'jimenez@gmail.com', 'Jimenez.123', 'Activo', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden`
--

CREATE TABLE `detalle_orden` (
  `id_detalle` int(11) NOT NULL,
  `id_orden` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foto_productos`
--

CREATE TABLE `foto_productos` (
  `id_foto` int(11) NOT NULL,
  `ruta_foto` varchar(200) NOT NULL,
  `num_foto` int(11) DEFAULT NULL,
  `id_producto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `foto_productos`
--

INSERT INTO `foto_productos` (`id_foto`, `ruta_foto`, `num_foto`, `id_producto`) VALUES
(40, '../assets/foto-repuestos/88863336 - 1.webp', 1, 31),
(41, '../assets/foto-repuestos/88863336 - 2.webp', 2, 31),
(42, '../assets/foto-repuestos/88863336 - 3.webp', 3, 31),
(43, '../assets/foto-repuestos/481H1012010 - 1.webp', 1, 32),
(44, '../assets/foto-repuestos/481H1012010 - 2.webp', 2, 32),
(45, '../assets/foto-repuestos/481H1012010 - 3.webp', 3, 32),
(46, '../assets/foto-repuestos/481H1012010 - 4.webp', 4, 32),
(47, '../assets/foto-repuestos/0414 - 1.jpg', 1, 33);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes`
--

CREATE TABLE `ordenes` (
  `id_orden` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','aceptada','rechazada') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `numero_de_parte` varchar(20) NOT NULL,
  `nombre_producto` varchar(80) NOT NULL,
  `categoria_producto` varchar(20) DEFAULT NULL,
  `marca_producto` varchar(20) DEFAULT NULL,
  `precio_producto` decimal(10,2) DEFAULT NULL,
  `stock_producto` int(11) DEFAULT NULL,
  `descripcion_producto` text DEFAULT NULL,
  `fecha_creacion` date DEFAULT NULL,
  `hora_creacion` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `numero_de_parte`, `nombre_producto`, `categoria_producto`, `marca_producto`, `precio_producto`, `stock_producto`, `descripcion_producto`, `fecha_creacion`, `hora_creacion`) VALUES
(31, '88863336', 'Refrigerante Dex Cool Importado', 'Frenos', 'Chevrolet', 20.00, 98, '', '2025-03-16', '20:40:06'),
(32, '481H1012010', 'Filtro de Aceite de Motor 484 (Varios modelos)', 'Frenos', 'Toyota', 20.00, 210, 'Descripción:\r\nRepuesto Original CHERY\r\n\r\nNo. de Parte: 481H1012010\r\n\r\nCaracterísticas:\r\n\r\nDimensiones: 9 x 9 x 10 cm3\r\nPeso: 0,10 Kg.\r\nModelos de Aplicación:\r\n\r\nCHERY | ORINOCO A/T | ORINOCO M/T | H5 | TIGGO | TIGGO 5 | X5', '2025-03-16', '22:50:21'),
(33, '0414', 'Catalizador', 'Inyección', 'Mitsubishi', 105.55, 1, 'For All Mitsubishi Cars- Catalytic Converter High Quality For Cleaner Drives. \r\n\r\nUnas palabras tecnicas Ahi.', '2025-03-25', '17:43:46'),
(35, '17801-0Y040', 'FILTRO DE AIRE YARIS 12-22', 'Filtros', 'Toyota', 35.00, 15, '', '2025-11-06', '20:22:16');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id_administrador`),
  ADD UNIQUE KEY `correo_administrador` (`correo`);

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rif` (`rif`),
  ADD UNIQUE KEY `cedula_encargado` (`cedula_encargado`),
  ADD UNIQUE KEY `correo_empresa` (`correo`);

--
-- Indices de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_orden` (`id_orden`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `foto_productos`
--
ALTER TABLE `foto_productos`
  ADD PRIMARY KEY (`id_foto`),
  ADD KEY `id_num_parte` (`id_producto`);

--
-- Indices de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD PRIMARY KEY (`id_orden`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `numero_de_parte` (`numero_de_parte`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administrador`
--
ALTER TABLE `administrador`
  MODIFY `id_administrador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `foto_productos`
--
ALTER TABLE `foto_productos`
  MODIFY `id_foto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD CONSTRAINT `detalle_orden_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `ordenes` (`id_orden`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_orden_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `foto_productos`
--
ALTER TABLE `foto_productos`
  ADD CONSTRAINT `foto_productos_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD CONSTRAINT `ordenes_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
