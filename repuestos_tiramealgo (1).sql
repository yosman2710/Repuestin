-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-11-2025 a las 01:30:40
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
(2, 'Diesel Y Turbos Venezuela', 'J-50081587-5', '04246263179', '40012', 'Jesus jimenez', 'V-28145124', '04146839084', 'jimenez@gmail.com', 'Jimenez.123', 'Activo', 0),
(14, 'Prueba tirame algo', 'J-50081587-7', '04246587917', 'urbe', 'admins de tirame algo', 'V-12345678', '04146454545', 'tiramealgocliente@gmail.com', 'Cliente.123', 'Activo', 0);

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
(48, '../assets/foto-repuestos/17801-0Y040 - 1.png', 1, 36),
(49, '../assets/foto-repuestos/16620-31012 - 1.jpg', 1, 37),
(50, '../assets/foto-repuestos/16100-39545 - 1.jpg', 1, 38),
(51, '../assets/foto-repuestos/04152-YZZA5 - 1.jpeg', 1, 39),
(52, '../assets/foto-repuestos/15601-97202 - 1.png', 1, 40),
(53, '../assets/foto-repuestos/23300-21010 - 1.png', 1, 41),
(54, '../assets/foto-repuestos/90048-51196 - 1.png', 1, 42),
(55, '../assets/foto-repuestos/17801-87402 - 1.png', 1, 43),
(56, '../assets/foto-repuestos/90369-T0003 - 1.png', 1, 44);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_chat`
--

CREATE TABLE `historial_chat` (
  `id_conversacion` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `rol` enum('usuario','bot','','') NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_chat`
--

INSERT INTO `historial_chat` (`id_conversacion`, `cliente_id`, `rol`, `mensaje`, `fecha_creacion`) VALUES
(1, 2, 'usuario', 'hola', '2025-11-13 19:17:53'),
(2, 2, 'bot', '¡Qué molleja, mi gente! Soy Repuestín, el que te resuelve la vida con los repuestos, ¿oíste? ¡Bienvenido, Jesús Jiménez de Diesel Y Turbos Venezuela!\n\nDime, ¿qué repuesto andas buscando hoy? ¡Aquí lo tenemos todo, papá!', '2025-11-13 19:17:57'),
(3, 2, 'usuario', 'Quien eres', '2025-11-13 19:18:00'),
(4, 2, 'bot', '¡Qué molleja, mi pana Jesus Jimenez de Diesel Y Turbos Venezuela!\n\nAquí Repuestín, tu pana de confianza para todos los repuestos automotrices. ¡Dime qué estás buscando, que aquí lo tenemos todo!', '2025-11-13 19:18:03'),
(5, 2, 'usuario', 'hola', '2025-11-13 19:20:30'),
(6, 2, 'bot', '¡Qué molleja, mi pana Jesus Jimenez! ¡Un placer tenerte por aquí! Soy Repuestín, tu pana de confianza en repuestos automotrices. ¿En qué te puedo ayudar hoy, pues? ¡Dime qué necesitas y te lo consigo!', '2025-11-13 19:20:33'),
(7, 14, 'usuario', 'hola', '2025-11-13 19:26:10'),
(8, 14, 'bot', '¡Qué molleja, mi gente! Aquí Repuestín, a la orden de los admins de tirame algo. ¿En qué te puedo ayudar hoy, verga? ¡Dime qué repuesto buscas para tu nave!', '2025-11-13 19:26:15');

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

--
-- Volcado de datos para la tabla `ordenes`
--

INSERT INTO `ordenes` (`id_orden`, `cliente_id`, `fecha_creacion`, `estado`) VALUES
(11, 2, '2025-11-11 01:36:35', 'aceptada');

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
(36, '17801-0Y040', 'FILTRO DE AIRE YARIS 12-22', 'Filtros', 'Toyota', 35.00, 8, 'Filtro de aire Toyota yaris.\r\n', '2025-11-13', '20:15:28'),
(37, '16620-31012', 'TENSOR DE CORREA 4RUNNER - TACOMA 4.0 03-16', 'Otros', 'Toyota', 234.99, 5, '', '2025-11-13', '20:22:08'),
(38, '16100-39545', 'BOMBA DE AGUA 4RUNNER - TUNDRA 10-21', 'Otros', 'Toyota', 195.00, 8, '', '2025-11-13', '20:23:00'),
(39, '04152-YZZA5', 'FILTRO ELEMENTO ACEITE 4RUNNER 10-17 TUNDRA 10-14', 'Filtros', 'Toyota', 12.00, 43, '', '2025-11-13', '20:24:05'),
(40, '15601-97202', 'FILTRO ACEITE TERIOS', 'Filtros', 'Toyota', 8.00, 22, '', '2025-11-13', '20:25:12'),
(41, '23300-21010', 'FILTRO DE GASOLINA COROLLA NEW SENSATION', 'Filtros', 'Toyota', 20.00, 15, '', '2025-11-13', '20:25:55'),
(42, '90048-51196', 'BUJIAS TERIOS BEGO 08-12', 'Motor', 'Toyota', 17.00, 39, '', '2025-11-13', '20:28:23'),
(43, '17801-87402', 'FILTRO DE AIRE TERIOS 02-07', 'Filtros', 'Toyota', 16.00, 18, '', '2025-11-13', '20:29:05'),
(44, '90369-T0003', 'RODAMIENTO ROLINERA DELANTERA HILUX 2.7 FORTUNER KAVAK 4.0', 'Otros', 'Toyota', 54.00, 62, '', '2025-11-13', '20:30:04');

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
-- Indices de la tabla `historial_chat`
--
ALTER TABLE `historial_chat`
  ADD PRIMARY KEY (`id_conversacion`),
  ADD KEY `Claveforanea` (`cliente_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `foto_productos`
--
ALTER TABLE `foto_productos`
  MODIFY `id_foto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `historial_chat`
--
ALTER TABLE `historial_chat`
  MODIFY `id_conversacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

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
-- Filtros para la tabla `historial_chat`
--
ALTER TABLE `historial_chat`
  ADD CONSTRAINT `Claveforanea` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD CONSTRAINT `ordenes_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
