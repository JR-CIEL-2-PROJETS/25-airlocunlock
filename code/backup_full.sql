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
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inscription_clients`
--

LOCK TABLES `inscription_clients` WRITE;
/*!40000 ALTER TABLE `inscription_clients` DISABLE KEYS */;
INSERT INTO `inscription_clients` VALUES (68,'admin','admin@gmail.com','$2y$10$HrEVAD0pxZtZydfiXDzGV.eeywqJgDop7HjIDGxmP9VV/CzNpJVm.','0751423278','admin'),(69,'tata','tata@gmail.com','$2y$10$64GcBKhI7Q3KKbfxUQAYAuQ4yBfq591A5NQLMl8Icx/73BbGz3Z5e','0695679565','tata');
/*!40000 ALTER TABLE `inscription_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inscription_proprietaire`
--

DROP TABLE IF EXISTS `inscription_proprietaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inscription_proprietaire` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `nom` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inscription_proprietaire`
--

LOCK TABLES `inscription_proprietaire` WRITE;
/*!40000 ALTER TABLE `inscription_proprietaire` DISABLE KEYS */;
INSERT INTO `inscription_proprietaire` VALUES (4,'tata','tata@lycee-jeanrostand.fr','$2y$10$0SBykzG5gS8oDQtmXiGeZ.bhnJQklHnew0AvOFDDQP5aOKer/6SUe','0102030405','tata'),(5,'amine','amine.lycee@lycee-jeanrostand.fr','$2y$10$n2Ds7TUQI5lUs2X46Vj7vuaXuKoTjv31SeNxU5p.bEDecC5.hXuKq','0102030405','amine'),(9,'bilal','qoisdjqopsd@gmail.com','$2y$10$KEVLA6NonaLdPlVjYQ.LcuJmxhh7Pd7eYq6IN5ldJmSfGQ2o2lyCy','0102030405','bilal'),(10,'tre','tre@gmail.com','$2y$10$Gap99lvgTV72xnMLK/MhsOLlBG0UMGJ4gW1ePc2YNxBGATbZGkP.G','0695679565','tre');
/*!40000 ALTER TABLE `inscription_proprietaire` ENABLE KEYS */;
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
  `proprietaire_id` int DEFAULT NULL,
  `cle_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proprietaire_id` (`proprietaire_id`),
  KEY `fk_cle_id` (`cle_id`),
  CONSTRAINT `fk_cle_id` FOREIGN KEY (`cle_id`) REFERENCES `Tapkey`.`cle_tapkey` (`cle_id`),
  CONSTRAINT `publier_bien_ibfk_1` FOREIGN KEY (`proprietaire_id`) REFERENCES `inscription_proprietaire` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `publier_bien`
--

LOCK TABLES `publier_bien` WRITE;
/*!40000 ALTER TABLE `publier_bien` DISABLE KEYS */;
INSERT INTO `publier_bien` VALUES (2,'Appartement Parisien','Paris','Un superbe appartement au cœur de Paris, idéal pour vos séjours.',150.00,2,'photo-bien/tour-eiffel.jpg',NULL,NULL);
/*!40000 ALTER TABLE `publier_bien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation_bien`
--

DROP TABLE IF EXISTS `reservation_bien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation_bien` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bien_id` int NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `nombre_personnes` int NOT NULL,
  `client_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bien_id` (`bien_id`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `reservation_bien_ibfk_1` FOREIGN KEY (`bien_id`) REFERENCES `publier_bien` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_bien_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `inscription_clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation_bien`
--

LOCK TABLES `reservation_bien` WRITE;
/*!40000 ALTER TABLE `reservation_bien` DISABLE KEYS */;
INSERT INTO `reservation_bien` VALUES (2,2,'2025-03-12','2025-03-21',2,NULL);
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

-- Dump completed on 2025-04-02 15:07:28
