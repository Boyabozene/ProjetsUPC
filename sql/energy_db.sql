-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3309
-- Généré le : mer. 16 juil. 2025 à 15:48
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `energy_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `consumption`
--

CREATE TABLE `consumption` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `kwh` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `consumption`
--

INSERT INTO `consumption` (`id`, `user_id`, `date`, `kwh`) VALUES
(1, 1, '2025-07-16 00:00:00', 5),
(2, 1, '2025-07-16 00:00:00', 19),
(3, 1, '2025-07-16 00:00:00', 17),
(4, 1, '2025-07-16 00:00:00', 5),
(5, 1, '2025-07-16 00:00:00', 10),
(6, 1, '2025-07-16 00:00:00', 7),
(7, 1, '2025-07-16 00:00:00', 15),
(8, 1, '2025-07-16 00:00:00', 7),
(9, 1, '2025-07-16 00:00:00', 18),
(10, 1, '2025-07-16 00:00:00', 16),
(11, 1, '2025-07-16 14:46:56', 18),
(12, 1, '2025-07-16 14:46:58', 17),
(13, 1, '2025-07-16 14:46:59', 6),
(14, 1, '2025-07-16 14:47:00', 5),
(15, 1, '2025-07-16 14:47:00', 13),
(16, 1, '2025-07-16 14:47:00', 17),
(17, 1, '2025-07-16 14:47:00', 19),
(18, 1, '2025-07-16 14:47:01', 16),
(19, 1, '2025-07-16 14:47:01', 11),
(20, 1, '2025-07-16 14:47:01', 11),
(21, 1, '2025-07-16 14:47:01', 9),
(22, 1, '2025-07-16 14:47:01', 20),
(23, 1, '2025-07-16 14:47:02', 10),
(24, 1, '2025-07-16 14:47:02', 19),
(25, 1, '2025-07-16 14:47:02', 6),
(26, 1, '2025-07-16 14:47:02', 12),
(27, 1, '2025-07-16 14:47:02', 13),
(28, 1, '2025-07-16 14:47:03', 10),
(29, 1, '2025-07-16 14:47:03', 18),
(30, 1, '2025-07-16 14:47:03', 15),
(31, 1, '2025-07-16 14:47:03', 19),
(32, 1, '2025-07-16 14:47:04', 8),
(33, 1, '2025-07-16 14:47:04', 10),
(34, 1, '2025-07-16 14:47:04', 13),
(35, 1, '2025-07-16 14:47:04', 19),
(36, 1, '2025-07-16 14:47:04', 14),
(37, 1, '2025-07-16 14:47:05', 5),
(38, 1, '2025-07-16 14:47:05', 13),
(39, 1, '2025-07-16 14:47:05', 6),
(40, 1, '2025-07-16 14:47:05', 15),
(41, 1, '2025-07-16 14:47:06', 19),
(42, 1, '2025-07-16 14:47:06', 9),
(43, 1, '2025-07-16 14:47:06', 13),
(44, 1, '2025-07-16 14:47:06', 17);

-- --------------------------------------------------------

--
-- Structure de la table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `status` enum('unpaid','paid') DEFAULT 'unpaid',
  `issued_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `invoices`
--

INSERT INTO `invoices` (`id`, `user_id`, `amount`, `status`, `issued_at`, `paid_at`) VALUES
(1, 1, 0, 'paid', '2025-07-16 14:37:51', '2025-07-16 14:37:56'),
(2, 1, 500, 'paid', '2025-07-16 14:38:16', '2025-07-16 14:38:20'),
(3, 1, 10300, 'paid', '2025-07-16 14:38:52', '2025-07-16 14:38:58'),
(4, 1, 11900, 'unpaid', '2025-07-16 14:46:51', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`) VALUES
(1, 'Demo User', 'demo@example.com', '$2y$10$nt/QGZNsd64Anm3HEuAIqOwP7SZD4L7UPfkV/d06GAstiCgZ75C8W'),
(2, 'Alice', 'alice@example.com', '$2y$10$KTge/Hf/ynopZP/sa29Qve3jgbabsVrcbjnKIlC2A1irLcrsTDTVW'),
(3, 'Bob', 'bob@example.com', '$2y$10$wrJUscGmdPSUygT3xk4bO.IzrnfyAwvPQNhN7xlF2XU4I9KwH2x3G');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `consumption`
--
ALTER TABLE `consumption`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `consumption`
--
ALTER TABLE `consumption`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT pour la table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `consumption`
--
ALTER TABLE `consumption`
  ADD CONSTRAINT `consumption_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
