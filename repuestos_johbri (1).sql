-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2025 at 03:37 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `repuestos_johbri`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrador`
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
-- Dumping data for table `administrador`
--

INSERT INTO `administrador` (`id_administrador`, `nombre_administrador`, `correo`, `contrasena`, `cargo`, `intentos`) VALUES
(1, 'Johbri Angulo', 'johbrirepuestos@gmail.com', 'Repuesto_123', 'Dueño', 0);

-- --------------------------------------------------------

--
-- Table structure for table `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carrito`
--

INSERT INTO `carrito` (`id`, `cliente_id`, `producto_id`, `cantidad`) VALUES
(19, 13, 32, 3),
(20, 13, 31, 2);

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
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
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id`, `nombre_empresa`, `rif`, `telefono_empresa`, `direccion`, `nombre_encargado`, `cedula_encargado`, `telefono_encargado`, `correo`, `contrasena`, `estado_cliente`, `intentos`) VALUES
(2, 'Diesel Y Turbos Venezuela', 'J-50081587-5', '04246263179', '40012', 'Rafael Chirinos', 'V-30605170', '04166626450', 'chirinos@gmail.com', 'Chirinos.123', 'Activo', 0),
(3, 'Repuestos la rotaria', 'J-95869555-8', '04166626450', '4006', 'Manuel Melean', 'V-13004028', '04166626450', 'melean@gmail.com', 'Melean.123', 'Activo', 0),
(13, 'Universo Diesel', 'J-12345678-9', '04246263179', '4001', 'Jesus Santos', 'V-26456380', '04246263179', 'santos@gmail.com', 'Santos.123', 'Activo', 0);

-- --------------------------------------------------------

--
-- Table structure for table `detalle_orden`
--

CREATE TABLE `detalle_orden` (
  `id_detalle` int(11) NOT NULL,
  `id_orden` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detalle_orden`
--

INSERT INTO `detalle_orden` (`id_detalle`, `id_orden`, `id_producto`, `cantidad`, `precio_unitario`) VALUES
(2, 2, 31, 1, 20.00),
(3, 3, 31, 10, 20.00),
(4, 3, 32, 20, 20.00),
(5, 4, 31, 10, 20.00),
(6, 5, 31, 20, 20.00),
(7, 6, 31, 10, 20.00),
(8, 6, 32, 5, 20.00),
(9, 7, 31, 5, 20.00),
(10, 7, 32, 20, 20.00),
(11, 8, 32, 80, 20.00),
(12, 9, 32, 2, 20.00),
(13, 10, 31, 2, 20.00);

-- --------------------------------------------------------

--
-- Table structure for table `foto_productos`
--

CREATE TABLE `foto_productos` (
  `id_foto` int(11) NOT NULL,
  `ruta_foto` varchar(200) NOT NULL,
  `num_foto` int(11) DEFAULT NULL,
  `id_producto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `foto_productos`
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
-- Table structure for table `ordenes`
--

CREATE TABLE `ordenes` (
  `id_orden` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','aceptada','rechazada') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ordenes`
--

INSERT INTO `ordenes` (`id_orden`, `cliente_id`, `fecha_creacion`, `estado`) VALUES
(2, 13, '2025-03-17 00:41:09', 'aceptada'),
(3, 13, '2025-03-17 02:51:38', 'aceptada'),
(4, 13, '2025-03-17 02:59:14', 'aceptada'),
(5, 13, '2025-03-17 03:01:49', 'aceptada'),
(6, 13, '2025-03-17 03:05:13', 'aceptada'),
(7, 13, '2025-03-17 03:07:44', 'aceptada'),
(8, 13, '2025-03-17 03:19:52', 'aceptada'),
(9, 13, '2025-03-20 15:35:57', 'aceptada'),
(10, 3, '2025-03-27 23:37:34', 'aceptada');

-- --------------------------------------------------------

--
-- Table structure for table `productos`
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
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`id_producto`, `numero_de_parte`, `nombre_producto`, `categoria_producto`, `marca_producto`, `precio_producto`, `stock_producto`, `descripcion_producto`, `fecha_creacion`, `hora_creacion`) VALUES
(31, '88863336', 'Refrigerante Dex Cool Importado', 'Frenos', 'Chevrolet', 20.00, 98, '', '2025-03-16', '20:40:06'),
(32, '481H1012010', 'Filtro de Aceite de Motor 484 (Varios modelos)', 'Frenos', 'Toyota', 20.00, 210, 'Descripción:\r\nRepuesto Original CHERY\r\n\r\nNo. de Parte: 481H1012010\r\n\r\nCaracterísticas:\r\n\r\nDimensiones: 9 x 9 x 10 cm3\r\nPeso: 0,10 Kg.\r\nModelos de Aplicación:\r\n\r\nCHERY | ORINOCO A/T | ORINOCO M/T | H5 | TIGGO | TIGGO 5 | X5', '2025-03-16', '22:50:21'),
(33, '0414', 'Catalizador', 'Inyección', 'Mitsubishi', 105.55, 1, 'For All Mitsubishi Cars- Catalytic Converter High Quality For Cleaner Drives. \r\n\r\nUnas palabras tecnicas Ahi.', '2025-03-25', '17:43:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id_administrador`),
  ADD UNIQUE KEY `correo_administrador` (`correo`);

--
-- Indexes for table `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rif` (`rif`),
  ADD UNIQUE KEY `cedula_encargado` (`cedula_encargado`),
  ADD UNIQUE KEY `correo_empresa` (`correo`);

--
-- Indexes for table `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_orden` (`id_orden`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indexes for table `foto_productos`
--
ALTER TABLE `foto_productos`
  ADD PRIMARY KEY (`id_foto`),
  ADD KEY `id_num_parte` (`id_producto`);

--
-- Indexes for table `ordenes`
--
ALTER TABLE `ordenes`
  ADD PRIMARY KEY (`id_orden`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `numero_de_parte` (`numero_de_parte`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrador`
--
ALTER TABLE `administrador`
  MODIFY `id_administrador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `detalle_orden`
--
ALTER TABLE `detalle_orden`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `foto_productos`
--
ALTER TABLE `foto_productos`
  MODIFY `id_foto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `ordenes`
--
ALTER TABLE `ordenes`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Constraints for table `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD CONSTRAINT `detalle_orden_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `ordenes` (`id_orden`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_orden_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Constraints for table `foto_productos`
--
ALTER TABLE `foto_productos`
  ADD CONSTRAINT `foto_productos_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE;

--
-- Constraints for table `ordenes`
--
ALTER TABLE `ordenes`
  ADD CONSTRAINT `ordenes_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
