-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 09-05-2022 a las 15:47:30
-- Versión del servidor: 8.0.18
-- Versión de PHP: 7.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tuit`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `messages`
--

CREATE TABLE `messages` (
  `id_messages` int(11) NOT NULL,
  `Sender_user_id` int(11) NOT NULL,
  `Recepter_user_id` int(11) NOT NULL,
  `message_users` varchar(5000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `File_url` varchar(500) NOT NULL,
  `date_message` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `messages`
--

INSERT INTO `messages` (`id_messages`, `Sender_user_id`, `Recepter_user_id`, `message_users`, `File_url`, `date_message`) VALUES
(14, 29, 30, 'Hola maria ¿Cómo estás?', '61fa467a3b3e022209e4d65a41d2fe59.jpg', '2022-05-08 15:49:32'),
(15, 30, 29, 'holis pepe', 'Sin archivo adjunto', '2022-05-08 16:56:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tweets`
--

CREATE TABLE `tweets` (
  `id_tweets` int(11) NOT NULL,
  `user_tweet_id` int(11) NOT NULL,
  `Tweet` varchar(500) NOT NULL,
  `Create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Is_public` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id_users` int(11) NOT NULL,
  `First_name` varchar(50) NOT NULL,
  `Last_name` varchar(50) NOT NULL,
  `Email` varchar(70) NOT NULL,
  `Address_house` varchar(50) NOT NULL,
  `Childs` tinyint(4) NOT NULL,
  `Marital_Status` varchar(20) NOT NULL,
  `Photo_url` varchar(100) NOT NULL,
  `User` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Password_user` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id_users`, `First_name`, `Last_name`, `Email`, `Address_house`, `Childs`, `Marital_Status`, `Photo_url`, `User`, `Password_user`) VALUES
(29, 'pepe', 'pepardo', 'pepe@pepe.com', 'pepe456', 5, 'casado', 'd0fc4f448a9f250be0eb03b5ab9004de.jpg', 'pepe', '$2y$10$oktOn1V1mlhYJntdlO.JoeoRAhgcveFMKVuxPxFrMNyZ34p/bBuDG'),
(30, 'maria', 'mariela', 'maria@maria.com', 'maria456', 0, 'casado', '294e0d0028890db3a6161100e75bd852.jpg', 'maria', '$2y$10$6knuc8pCMuR/EfF5sRO7T.A3VHsKxDl4GcGkxWefGXnetW0XIPeTe');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id_messages`),
  ADD KEY `Sender_user_id` (`Sender_user_id`),
  ADD KEY `Recepter_user_id` (`Recepter_user_id`);

--
-- Indices de la tabla `tweets`
--
ALTER TABLE `tweets`
  ADD PRIMARY KEY (`id_tweets`),
  ADD KEY `User_id` (`user_tweet_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_users`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `messages`
--
ALTER TABLE `messages`
  MODIFY `id_messages` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `tweets`
--
ALTER TABLE `tweets`
  MODIFY `id_tweets` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id_users` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`Sender_user_id`) REFERENCES `users` (`id_users`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`Recepter_user_id`) REFERENCES `users` (`id_users`);

--
-- Filtros para la tabla `tweets`
--
ALTER TABLE `tweets`
  ADD CONSTRAINT `tweets_ibfk_1` FOREIGN KEY (`user_tweet_id`) REFERENCES `users` (`id_users`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
