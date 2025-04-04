-- Base de données d'authentification
CREATE DATABASE IF NOT EXISTS `auth_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `auth_db`;

-- Table des utilisateurs avec plus de détails et sécurité
CREATE TABLE `utilisateur` (
    `id_utilisateur` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom_utilisateur` varchar(50) NOT NULL,
    `mot_de_passe` varchar(255) NOT NULL,
    `email` varchar(100) NOT NULL UNIQUE,
    `role` ENUM('entraineur', 'directeur', 'admin') NOT NULL,
    `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `derniere_connexion` TIMESTAMP NULL,
    PRIMARY KEY (`id_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des tokens JWT
CREATE TABLE `tokens` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_utilisateur` int(10) UNSIGNED NOT NULL,
    `token` varchar(500) NOT NULL,
    `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `date_expiration` TIMESTAMP NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
