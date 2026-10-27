-- =============================================
-- Uber-Cueillette - Base de données
-- Institut Supérieur de Gestion de Tunis
-- =============================================

CREATE DATABASE IF NOT EXISTS `uber-cueillette` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `uber-cueillette`;

-- Table des types de fruits
CREATE TABLE `type_fruit` (
  `id_type_fruit` INT AUTO_INCREMENT PRIMARY KEY,
  `libelle` VARCHAR(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `type_fruit` (`libelle`) VALUES
  ('Olives'),
  ('Agrumes'),
  ('Tomates'),
  ('Dattes'),
  ('Grenades'),
  ('Figues');

-- Table des gouvernorats
CREATE TABLE `gouvernorat` (
  `id_gouvernorat` INT AUTO_INCREMENT PRIMARY KEY,
  `libelle` VARCHAR(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `gouvernorat` (`libelle`) VALUES
  ('Tunis'),('Ariana'),('Ben Arous'),('Manouba'),
  ('Nabeul'),('Zaghouan'),('Bizerte'),('Béja'),
  ('Jendouba'),('Le Kef'),('Siliana'),('Sousse'),
  ('Monastir'),('Mahdia'),('Sfax'),('Kairouan'),
  ('Kasserine'),('Sidi Bouzid'),('Gabès'),('Médenine'),
  ('Tataouine'),('Gafsa'),('Tozeur'),('Kébili');

-- Table des agriculteurs
CREATE TABLE `agriculteur` (
  `id_agriculteur` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(30) NOT NULL,
  `prenom` VARCHAR(30) NOT NULL,
  `CIN` VARCHAR(10) NOT NULL UNIQUE,
  `email` VARCHAR(50) NOT NULL UNIQUE,
  `adresse` VARCHAR(50),
  `pseudo` VARCHAR(30) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des ouvriers
CREATE TABLE `ouvrier` (
  `id_ouvrier` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(30) NOT NULL,
  `prenom` VARCHAR(30) NOT NULL,
  `CIN` VARCHAR(30) NOT NULL UNIQUE,
  `email` VARCHAR(50) NOT NULL UNIQUE,
  `photo` LONGBLOB,
  `description` VARCHAR(100),
  `pseudo` VARCHAR(30) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des offres
CREATE TABLE `offre` (
  `id_offre` INT AUTO_INCREMENT PRIMARY KEY,
  `id_type_fruit` INT NOT NULL,
  `id_gouvernorat` INT NOT NULL,
  `adresse` VARCHAR(50),
  `date_debut` DATE NOT NULL,
  `date_fin` DATE NOT NULL,
  `nombre_ouvriers` INT NOT NULL,
  `prix_journee` FLOAT NOT NULL,
  `date_limite` DATE NOT NULL,
  `id_agriculteur` INT NOT NULL,
  FOREIGN KEY (`id_type_fruit`) REFERENCES `type_fruit`(`id_type_fruit`),
  FOREIGN KEY (`id_gouvernorat`) REFERENCES `gouvernorat`(`id_gouvernorat`),
  FOREIGN KEY (`id_agriculteur`) REFERENCES `agriculteur`(`id_agriculteur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des candidatures
CREATE TABLE `candidature` (
  `id_candidature` INT AUTO_INCREMENT PRIMARY KEY,
  `id_offre` INT NOT NULL,
  `id_ouvrier` INT NOT NULL,
  `decision` VARCHAR(10) DEFAULT 'encours',
  `date_candidature` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `note` INT DEFAULT NULL,
  `commentaire` VARCHAR(50) DEFAULT NULL,
  `remuneration` FLOAT DEFAULT NULL,
  FOREIGN KEY (`id_offre`) REFERENCES `offre`(`id_offre`) ON DELETE CASCADE,
  FOREIGN KEY (`id_ouvrier`) REFERENCES `ouvrier`(`id_ouvrier`) ON DELETE CASCADE,
  UNIQUE KEY `unique_candidature` (`id_offre`, `id_ouvrier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
