-- Base de données pour la gestion sportive
CREATE DATABASE IF NOT EXISTS `sports_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sports_db`;

-- Table des joueurs
CREATE TABLE `joueur` (
    `numero_licence` varchar(8) NOT NULL,
    `nom` varchar(100) DEFAULT NULL,
    `prenom` varchar(100) DEFAULT NULL,
    `date_naissance` date DEFAULT NULL,
    `taille` double DEFAULT NULL,
    `poids` double DEFAULT NULL,
    `statut` enum('Actif','Blessé','Suspendu','Absent') DEFAULT NULL,
    PRIMARY KEY (`numero_licence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des matchs
CREATE TABLE `match_` (
    `id_match` int(11) NOT NULL AUTO_INCREMENT,
    `date_match` date DEFAULT NULL,
    `heure_match` time DEFAULT NULL,
    `nom_equipe_adverse` varchar(100) DEFAULT NULL,
    `lieu_de_rencontre` varchar(50) DEFAULT NULL,
    `statut` enum('À venir','Terminé') DEFAULT 'À venir',
    `resultat` enum('Victoire','Défaite','Match Nul') DEFAULT NULL,
    `etat_feuille` enum('Validé','Non validé') DEFAULT 'Non validé',
    PRIMARY KEY (`id_match`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des participations (feuille de match)
CREATE TABLE `participer` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `numero_licence` varchar(8) NOT NULL,
    `nom_joueur` varchar(100) NOT NULL,
    `prenom_joueur` varchar(100) NOT NULL,
    `id_match` int(11) NOT NULL,
    `role` enum('Titulaire','Remplaçant') NOT NULL,
    `poste` enum('Gardien de But','Défenseur Central','Défenseur Latéral','Arrière Latéral Offensif','Libéro','Milieu Défensif','Milieu Central','Milieu Offensif','Milieu Latéral','Attaquant Central','Avant-Centre','Ailier','Second Attaquant') NOT NULL,
    `evaluation` int(1) DEFAULT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`numero_licence`) REFERENCES `joueur` (`numero_licence`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`id_match`) REFERENCES `match_` (`id_match`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des commentaires
CREATE TABLE `commentaire` (
    `id_commentaire` int(50) NOT NULL AUTO_INCREMENT,
    `sujet_commentaire` text DEFAULT NULL,
    `texte_commentaire` text DEFAULT NULL,
    `numero_licence` varchar(8) NOT NULL,
    PRIMARY KEY (`id_commentaire`),
    FOREIGN KEY (`numero_licence`) REFERENCES `joueur` (`numero_licence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des statistiques
CREATE TABLE `statistiques` (
    `id_statistique` int(11) NOT NULL AUTO_INCREMENT,
    `numero_licence` varchar(8) NOT NULL,
    `saison` varchar(9) NOT NULL,
    `matchs_joues` int(11) DEFAULT 0,
    `minutes_jouees` int(11) DEFAULT 0,
    `buts_marques` int(11) DEFAULT 0,
    `passes_decisives` int(11) DEFAULT 0,
    `cartons_jaunes` int(11) DEFAULT 0,
    `cartons_rouges` int(11) DEFAULT 0,
    `note_moyenne` decimal(3,2) DEFAULT 0.00,
    PRIMARY KEY (`id_statistique`),
    FOREIGN KEY (`numero_licence`) REFERENCES `joueur` (`numero_licence`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Trigger pour mettre à jour l'état de la feuille de match
DELIMITER $$
CREATE TRIGGER `after_update_participer` AFTER UPDATE ON `participer` 
FOR EACH ROW 
BEGIN
    UPDATE match_
    SET etat_feuille = 'Non validé'
    WHERE id_match = NEW.id_match;
END$$
DELIMITER ;
