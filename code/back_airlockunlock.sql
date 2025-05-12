-- MySQL dump 10.13  Distrib 8.4.5, for Linux (x86_64)
--
-- Host: localhost    Database: airlockunlock
-- ------------------------------------------------------
-- Server version	8.4.5

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `biens`
--

DROP TABLE IF EXISTS `biens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `biens` (
  `id_bien` int NOT NULL AUTO_INCREMENT,
  `id_proprietaire` int DEFAULT NULL,
  `type_bien` enum('Appartement','Bureau','Maison') DEFAULT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `prix_par_nuit` decimal(10,2) DEFAULT NULL,
  `description` text,
  `surface` int DEFAULT NULL,
  `nombre_pieces` int DEFAULT NULL,
  `capacite` int DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `photos` text,
  `wifi` tinyint(1) DEFAULT '0',
  `parking` tinyint(1) DEFAULT '0',
  `cuisine` tinyint(1) DEFAULT '0',
  `tv` tinyint(1) DEFAULT '0',
  `climatisation` tinyint(1) DEFAULT '0',
  `chauffage` tinyint(1) DEFAULT '0',
  `serrure_electronique` tinyint(1) DEFAULT '0',
  `numero_serie_tapkey` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_bien`),
  KEY `id_proprietaire` (`id_proprietaire`),
  CONSTRAINT `biens_ibfk_1` FOREIGN KEY (`id_proprietaire`) REFERENCES `proprietaires` (`id_proprietaire`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `biens`
--

LOCK TABLES `biens` WRITE;
/*!40000 ALTER TABLE `biens` DISABLE KEYS */;
INSERT INTO `biens` VALUES (1,1,'Maison','Villa en bord de mer',120.00,'Superbe villa avec vue sur la mer.',150,5,6,'12 Rue des Vagues, Marseille','test1.jpg\r\n',1,1,1,1,1,1,1,'TK-001-ADMIN'),(2,2,'Appartement','Studio cosy centre-ville',60.00,'Studio moderne proche des commodit??s.',35,1,2,'88 Avenue Victor Hugo, Paris','studio1.jpg',1,0,1,1,0,1,0,NULL),(3,3,'Bureau','Bureau ??quip?? ?? louer',80.00,'Espace de travail calme et lumineux.',50,2,4,'45 Boulevard Haussmann, Paris','bureau1.jpg',1,1,1,1,1,1,1,'TK-002-XYZ'),(4,2,'Maison','Villa moderne avec piscine',250.00,'Magnifique villa moderne avec piscine privée et jardin paysager.',200,6,10,'789 Avenue du Soleil, 06400 Cannes','villa1.jpg',1,1,1,1,1,1,1,'TK-002-XYZ');
/*!40000 ALTER TABLE `biens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id_client` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `date_inscription` datetime DEFAULT CURRENT_TIMESTAMP,
  `telephone` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id_client`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'Client Admin','client@admin.com','$2y$10$GEq6aEZHERl/tHB2b0fBr.nqfQ9SxvtFybmgWWbU2nyReZ0Qw5Fme','2025-04-08 09:27:45','0614589324'),(2,'Bilal Taoufik','bilal@gmail.com','$2y$10$Noi/ZpDGHQM/VPpOcBJW1eKhocXGg7yMeUgEgnPMZnUwbjBjUXyZm','2025-04-08 09:31:07','0626266252'),(3,'Adel Aichi','adel@gmail.com','$2y$10$1VZPPJ9t3feNhFQE3bPVGOGt7lYaKlNm24CxDbPj.HQ9UEvuWuBq2','2025-04-08 09:31:07','0624589357'),(5,'toto','toto@lycee-jeanrostand.fr','$2y$10$vGYrKL5l2U0Uf1vmLgWXZ.35PK2BNvMQBaqNRoMNxX/gcXkXhCc2G','2025-04-10 13:36:07','0102030405');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proprietaires`
--

DROP TABLE IF EXISTS `proprietaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proprietaires` (
  `id_proprietaire` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `date_inscription` date DEFAULT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id_proprietaire`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proprietaires`
--

LOCK TABLES `proprietaires` WRITE;
/*!40000 ALTER TABLE `proprietaires` DISABLE KEYS */;
INSERT INTO `proprietaires` VALUES (1,'Proprietaire Admin','proprietaire@admin.com','$2y$10$Cud4vOQAPZP4E43E3THQ2eB907V1DQuFW4sKAeTN9TCxWAXLuBw/a','2025-04-08','0698412368'),(2,'Lakshan Sangaralingam','lakshan@gmail.com','$2y$10$WdYcM8ObNmpj1hLd/ZwWHu4vYb56GLnZGP45OdJNu3If7iaQ21ajy','2025-04-08','0698478687'),(3,'Amine El mir','amine@gmail.com','$2y$10$vxPj544S8kvPsTD2cT4ds.j8k3q7nCscZeZZ4Otu/b3oDOr4VOC1y','2025-04-08','0658598747');
/*!40000 ALTER TABLE `proprietaires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `id_reservation` int NOT NULL AUTO_INCREMENT,
  `id_client` int DEFAULT NULL,
  `id_bien` int DEFAULT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `date_arrivee` date DEFAULT NULL,
  `date_depart` date DEFAULT NULL,
  `nombre_personnes` int DEFAULT NULL,
  `date_reservation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statut` enum('en attente','confirmée','annulée') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'en attente',
  PRIMARY KEY (`id_reservation`),
  KEY `id_client` (`id_client`),
  KEY `id_bien` (`id_bien`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `clients` (`id_client`),
  CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`id_bien`) REFERENCES `biens` (`id_bien`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (1,1,1,'Client Admin','2025-04-15','2025-04-20',4,'2025-04-08 09:35:08','confirmée'),(2,3,3,'Adel Aichi','2025-04-10','2025-04-12',1,'2025-04-08 09:35:08','confirmée'),(3,2,2,'Bilal Taoufik','2025-04-13','2025-04-26',2,'2025-04-08 09:35:08','confirmée'),(4,2,4,'Bilal Taoufik','2025-04-28','2025-06-14',10,'2025-04-27 22:23:04','confirmée');
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-12  7:55:41
