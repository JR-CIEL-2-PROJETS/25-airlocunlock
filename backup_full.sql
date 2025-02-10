-- MySQL dump 10.13  Distrib 8.4.4, for Linux (x86_64)
--
-- Host: localhost    Database: airlockunlock
-- ------------------------------------------------------
-- Server version	8.4.4

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
-- Table structure for table `inscription_clients`
--

DROP TABLE IF EXISTS `inscription_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inscription_clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inscription_clients`
--

LOCK TABLES `inscription_clients` WRITE;
/*!40000 ALTER TABLE `inscription_clients` DISABLE KEYS */;
INSERT INTO `inscription_clients` VALUES (1,'toto','toto@toto.fr','$2y$10$PCSpKBuNn7sPwwKs7T0r6OmHJvvt0adHHdk8r0ztyJsDtL3Rt50oi','010203040506'),(2,'amine','amine.elmir@lycee-jeanrostand.fr','$2y$10$f/PvusOwZwXP.NfRfhoiHOV6VcZXFcOdKzBwtAxB3oyUpjFBdlzPW','01020304050607');
/*!40000 ALTER TABLE `inscription_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inscription_propriétaire`
--

DROP TABLE IF EXISTS `inscription_propriétaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inscription_propriétaire` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inscription_propriétaire`
--

LOCK TABLES `inscription_propriétaire` WRITE;
/*!40000 ALTER TABLE `inscription_propriétaire` DISABLE KEYS */;
/*!40000 ALTER TABLE `inscription_propriétaire` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publier_bien`
--

DROP TABLE IF EXISTS `publier_bien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `publier_bien` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `rooms` int NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `publier_bien`
--

LOCK TABLES `publier_bien` WRITE;
/*!40000 ALTER TABLE `publier_bien` DISABLE KEYS */;
INSERT INTO `publier_bien` VALUES (3,'Appartement Parisien','Paris ','Un superbe appartement au cœur de Paris, idéal pour vos séjours.',150.00,2,'photo-bien/tour-eiffel.jpg');
/*!40000 ALTER TABLE `publier_bien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation_bien`
--

DROP TABLE IF EXISTS `reservation_bien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation_bien` (
  `reservation_id` int NOT NULL AUTO_INCREMENT,
  `bien_id` int NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `nombre_personnes` int NOT NULL,
  `date_reservation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reservation_id`),
  KEY `bien_id` (`bien_id`),
  CONSTRAINT `reservation_bien_ibfk_1` FOREIGN KEY (`bien_id`) REFERENCES `publier_bien` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation_bien`
--

LOCK TABLES `reservation_bien` WRITE;
/*!40000 ALTER TABLE `reservation_bien` DISABLE KEYS */;
INSERT INTO `reservation_bien` VALUES (1,3,'2025-02-10','2025-02-17',3,'2025-02-10 10:08:00'),(2,3,'2025-02-12','2025-02-19',4,'2025-02-10 10:17:03'),(3,3,'2025-02-13','2025-02-26',5,'2025-02-10 10:19:11');
/*!40000 ALTER TABLE `reservation_bien` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-02-10 10:20:48
