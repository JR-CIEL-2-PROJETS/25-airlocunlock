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
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `biens`
--

LOCK TABLES `biens` WRITE;
/*!40000 ALTER TABLE `biens` DISABLE KEYS */;
INSERT INTO `biens` VALUES (1,1,'Appartement','Studio moderne au centre-ville',85.00,'Studio lumineux avec toutes commodités.',30,1,2,'13 Rue Lafayette, 75009 Paris','photo1.jpg',0,0,0,0,0,0,1,'TK-001-XYZ'),(2,1,'Maison','Maison avec jardin à Lyon',120.00,'Maison familiale avec grand jardin, parfaite pour les vacances.',120,5,6,'8 Chemin des Prés, 69008 Lyon','photo2.jpg',1,1,1,1,1,1,1,'TK-001-XYZ'),(3,1,'Bureau','Espace de coworking à Bordeaux',60.00,'Bureau moderne idéal pour freelances et startups.',50,2,4,'22 Rue Sainte-Catherine, 33000 Bordeaux','photo3.jpg',1,0,1,1,0,1,1,'TK-001-XYZ'),(4,1,'Appartement','Appartement cosy à Marseille',75.00,'Appartement avec vue mer à deux pas du Vieux-Port.',45,2,3,'5 Quai du Port, 13002 Marseille','photo4.jpg',1,0,1,1,1,1,0,NULL),(5,1,'Maison','Villa en Provence avec piscine',200.00,'Villa avec piscine privée dans un cadre paisible.',180,6,8,'99 Route des Vignes, 84110 Vaison-la-Romaine','photo5.jpg',1,1,1,1,1,1,0,NULL),(6,1,'Appartement','Loft design à Lille',95.00,'Grand loft avec mezzanine et espace détente.',70,3,4,'18 Rue de la Monnaie, 59000 Lille','photo6.jpg',1,1,1,1,1,1,0,NULL);
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
INSERT INTO `clients` VALUES (1,'Bilal Taoufik','bll.taoufik@gmail.com','$2y$10$ZdVUg9Bb92unjfpitayeyu6wUaSEZTG1PSVs2yU5GPZSK1wosuXrm','2025-04-08 09:31:07','0626266252'),(2,'Adel Aichi','adel@gmail.com','$2y$10$1VZPPJ9t3feNhFQE3bPVGOGt7lYaKlNm24CxDbPj.HQ9UEvuWuBq2','2025-04-08 09:31:07','0624589357');
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
INSERT INTO `proprietaires` VALUES (1,'Lakshan Sangaralingam','lakshan@gmail.com','$2y$10$.Ye/vpSYuVYlIpPd6kUIvuXChSAwgXlkZARfD1W7v6UnywZ3QB0Xa','2025-04-08','0698478687');
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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (1,1,1,'Bilal Taoufik','2025-05-04','2025-05-10',2,'2025-05-22 00:06:28','confirmée'),(2,1,4,'Bilal Taoufik','2025-06-22','2025-06-24',3,'2025-05-21 22:42:27','confirmée'),(3,1,2,'Bilal Taoufik','2025-05-18','2025-06-14',6,'2025-05-21 22:43:11','confirmée'),(4,1,6,'Bilal Taoufik','2024-12-01','2024-12-07',4,'2025-05-22 00:11:10','confirmée');
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

-- Dump completed on 2025-05-27  9:27:07
