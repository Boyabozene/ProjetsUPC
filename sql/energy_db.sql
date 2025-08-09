-- =====================================================
-- SCRIPT DE CRÉATION DE LA BASE DE DONNÉES ENERGY+
-- =====================================================
-- Version: 2.0
-- Auteur: Boybabozene Buyingo
-- Date: Juillet 2025
-- Description: Structure complète de la base de données
--              pour l'application de gestion de consommation électrique
-- =====================================================

-- Configuration de l'environnement MySQL
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Configuration de l'encodage des caractères
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- =====================================================
-- CRÉATION DE LA BASE DE DONNÉES
-- =====================================================

--
-- Base de données : `energy_db`
-- Encodage: utf8mb4 pour support Unicode complet
--

-- =====================================================
-- TABLE: users
-- =====================================================
-- Description: Stocke les informations des utilisateurs/clients
-- Rôle: Gestion de l'authentification et profils utilisateurs
-- =====================================================

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Nom complet de l\'utilisateur',
  `email` varchar(100) NOT NULL UNIQUE COMMENT 'Adresse email (identifiant de connexion)',
  `password` varchar(255) NOT NULL COMMENT 'Mot de passe hashé avec PHP password_hash()',
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  
  -- Index pour optimiser les recherches par email
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Table des utilisateurs/clients Energy+';

-- =====================================================
-- TABLE: consumption
-- =====================================================
-- Description: Enregistre la consommation électrique en temps réel
-- Rôle: Tracking de la consommation par utilisateur avec horodatage
-- =====================================================

CREATE TABLE `consumption` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'Référence vers l\'utilisateur',
  `date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Horodatage de l\'enregistrement',
  `kwh` int(11) NOT NULL COMMENT 'Consommation en kWh pour cet intervalle',
  
  PRIMARY KEY (`id`),
  
  -- Clé étrangère vers la table users
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  
  -- Index composé pour optimiser les requêtes par utilisateur et date
  KEY `idx_user_date` (`user_id`, `date`),
  KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Enregistrements de consommation électrique';

-- =====================================================
-- TABLE: invoices
-- =====================================================
-- Description: Gestion des factures électriques
-- Rôle: Facturation basée sur la consommation avec suivi des paiements
-- =====================================================

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'Référence vers l\'utilisateur facturé',
  `amount` int(11) NOT NULL COMMENT 'Montant de la facture en Francs Congolais (FC)',
  `status` enum('unpaid','paid') DEFAULT 'unpaid' COMMENT 'Statut de paiement de la facture',
  `issued_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Date d\'émission de la facture',
  `paid_at` datetime DEFAULT NULL COMMENT 'Date de paiement (NULL si impayée)',
  
  PRIMARY KEY (`id`),
  
  -- Clé étrangère vers la table users
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  
  -- Index pour optimiser les requêtes de facturation
  KEY `idx_user_status` (`user_id`, `status`),
  KEY `idx_issued_date` (`issued_at`),
  KEY `idx_paid_date` (`paid_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Factures électriques et suivi des paiements';

-- =====================================================
-- DONNÉES DE TEST
-- =====================================================
-- Utilisateurs de démonstration avec mots de passe hashés
-- Tous les mots de passe suivent le format: [nom]123
-- =====================================================

INSERT INTO `users` (`id`, `name`, `email`, `password`) VALUES
-- Utilisateur de démonstration principal
-- Email: demo@example.com, Mot de passe: demo123
(1, 'Demo User', 'demo@example.com', '$2y$10$nt/QGZNsd64Anm3HEuAIqOwP7SZD4L7UPfkV/d06GAstiCgZ75C8W'),

-- Utilisatrice Alice
-- Email: alice@example.com, Mot de passe: alice123
(2, 'Alice Makamba', 'alice@example.com', '$2y$10$KTge/Hf/ynopZP/sa29Qve3jgbabsVrcbjnKIlC2A1irLcrsTDTVW'),

-- Utilisateur Bob
-- Email: bob@example.com, Mot de passe: bob123
(3, 'Bob Tshisekedi', 'bob@example.com', '$2y$10$wrJUscGmdPSUygT3xk4bO.IzrnfyAwvPQNhN7xlF2XU4I9KwH2x3G');

-- =====================================================
-- CONFIGURATION DES AUTO_INCREMENT
-- =====================================================
-- Définir les valeurs de départ pour les clés primaires
-- =====================================================

ALTER TABLE `consumption`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- =====================================================
-- FINALISATION
-- =====================================================

COMMIT;

-- Restauration de la configuration d'encodage précédente
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =====================================================
-- NOTES D'UTILISATION
-- =====================================================
-- 1. Tarif électrique: 100 FC par kWh
-- 2. Les sessions utilisateur expirent après 1 heure d'inactivité
-- 3. Les factures incluent automatiquement:
--    - Frais de service: 1,500 FC
--    - TVA: 16%
-- 4. Génération PDF disponible uniquement pour factures payées
-- =====================================================
