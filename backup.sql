-- MySQL dump 10.13  Distrib 8.0.43, for Linux (x86_64)
--
-- Host: localhost    Database: plataforma_videojocs
-- ------------------------------------------------------
-- Server version	8.0.43-0ubuntu0.24.04.2

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
-- Table structure for table `jocs`
--

DROP TABLE IF EXISTS `jocs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jocs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_joc` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcio` text COLLATE utf8mb4_unicode_ci,
  `imatge_joc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `puntuacio_maxima` int DEFAULT '0',
  `nivells_totals` int DEFAULT '1',
  `actiu` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jocs`
--

LOCK TABLES `jocs` WRITE;
/*!40000 ALTER TABLE `jocs` DISABLE KEYS */;
INSERT INTO `jocs` VALUES (1,'EsquivaManía','Un juego de reflejos donde debes esquivar obstáculos que caen cada vez más rápido. Gana puntos por cada segundo que sobrevivas y alcanza la mayor puntuación posible.','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRDfIY5bqtYQdc0UsGX5x3ov8qmWxTpVTPAPg&s',1000,3,1),(2,'Mono Pensante','Ayuda al mono a resolver acertijos y recolectar plátanos usando su ingenio. Cada nivel presenta un desafío más difícil: puzles, trampas y enigmas selváticos.','https://media1.tenor.com/m/eNd8EyoH7AYAAAAC/monkey-thinking.gif',0,7,1),(3,'Adivina el Anime','Pon a prueba tus conocimientos de anime: observa una imagen y escribe el nombre correcto. Cuantos más aciertes seguidos, mayor será tu puntuación. ¡Demuestra que eres un verdadero otaku!','https://wompimages.ampify.care/fetchimage?siteId=7575&v=2&jpgQuality=100&width=700&url=https%3A%2F%2Fi.kym-cdn.com%2Fphotos%2Fimages%2Fnewsfeed%2F001%2F661%2F461%2F910.jpg',0,10,1),(4,'Pong Clásico','El clásico juego de tenis de dos jugadores. Mueve tu raqueta arriba y abajo para golpear la pelota y evitar que tu oponente anote. ¡Rápido, simple y adictivo!','https://i1.sndcdn.com/artworks-3qEdsFaGIz5nEzyg-P0dmNw-t1080x1080.jpg',0,3,1),(5,'Cliquero','Haz clics en los cubitos.','https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/363970/f5666007a5af2cb6a4ce772c8b4ff73f924af650/capsule_616x353.jpg?t=1757711723',123876,10,1),(6,'Space Blaster','Space Blasters!','https://play-lh.googleusercontent.com/0goocG7RJZDZ41ShfBPl-h7ctwHKHjqzn4nSImyL8_RWyXqeYNKw-CdGAKhgPGZG5Es',10000,5,1),(7,'Fruit Catcher','El jugador mueve una cesta para atrapar frutas que caen desde la parte superior.','https://ih1.redbubble.net/image.4949594644.8751/raf,360x360,075,t,fafafa:ca443f4786.jpg',10000,1,1),(8,'Pixel Runner',' juego de plataformas estilo retro pero con gráficos más modernos','https://cdn-images.dzcdn.net/images/cover/1216def200fac24252dfe7d531a39bd8/500x500.jpg',10000,1,1),(9,'Neon Snake','Una versión cyberpunk y ultra estilizada del clásico Snake','https://i.pinimg.com/736x/0c/ab/e8/0cabe8036fe675e5bbfe1145c8f51e65.jpg',10000,1,1);
/*!40000 ALTER TABLE `jocs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nivells_joc`
--

DROP TABLE IF EXISTS `nivells_joc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nivells_joc` (
  `id` int NOT NULL AUTO_INCREMENT,
  `joc_id` int NOT NULL,
  `nivell` int NOT NULL,
  `nom_nivell` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `configuracio_json` json NOT NULL,
  `puntuacio_minima` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `joc_id` (`joc_id`),
  CONSTRAINT `nivells_joc_ibfk_1` FOREIGN KEY (`joc_id`) REFERENCES `jocs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nivells_joc`
--

LOCK TABLES `nivells_joc` WRITE;
/*!40000 ALTER TABLE `nivells_joc` DISABLE KEYS */;
INSERT INTO `nivells_joc` VALUES (2,5,1,'Fácil','{\"ttl\": 10, \"color\": \"234111\", \"sizePx\": 200, \"sizeVariationPx\": 20}',0),(3,5,2,'Mediofácil','{\"ttl\": 5, \"color\": \"45f5f4\", \"sizePx\": 125, \"sizeVariationPx\": 20}',200),(4,5,3,'Medio','{\"ttl\": 4, \"color\": \"fd35f4\", \"sizePx\": 75, \"sizeVariationPx\": 15}',350),(5,5,4,'Mediodifícil','{\"ttl\": 3, \"color\": \"54673\", \"sizePx\": 30, \"sizeVariationPx\": 10}',550),(6,5,5,'Difícil','{\"ttl\": 1, \"color\": \"32456\", \"sizePx\": 10, \"sizeVariationPx\": 5}',1000),(7,4,1,'Fácil','{\"aiSpeed\": 3, \"paddleH\": 120, \"ballSpeed\": 4, \"winPoints\": 7}',0),(8,4,2,'Mediofácil','{\"aiSpeed\": 4, \"paddleH\": 110, \"ballSpeed\": 5, \"winPoints\": 7}',5),(9,4,3,'Medio','{\"aiSpeed\": 6, \"paddleH\": 90, \"ballSpeed\": 8, \"winPoints\": 10}',15),(10,4,4,'Mediodifícil','{\"aiSpeed\": 6, \"paddleH\": 70, \"ballSpeed\": 8, \"winPoints\": 10}',20),(11,4,5,'Extremo','{\"aiSpeed\": 9, \"paddleH\": 40, \"ballSpeed\": 15, \"winPoints\": 20}',50),(22,6,1,'Entrenamiento','{\"enemyCount\": 5, \"enemySpeed\": 2, \"bulletSpeed\": 6, \"playerSpeed\": 5}',0),(23,6,2,'Patrulla hostil','{\"enemyCount\": 8, \"enemySpeed\": 2.5, \"bulletSpeed\": 6.5, \"playerSpeed\": 5}',100),(24,6,3,'Invasión','{\"enemyCount\": 10, \"enemySpeed\": 3, \"bulletSpeed\": 7, \"playerSpeed\": 5.5}',200),(25,6,4,'Asalto espacial','{\"enemyCount\": 12, \"enemySpeed\": 3.4, \"bulletSpeed\": 7.5, \"playerSpeed\": 6}',300),(26,6,5,'Apocalipsis galáctico','{\"enemyCount\": 15, \"enemySpeed\": 4, \"bulletSpeed\": 8, \"playerSpeed\": 6.2}',500),(27,7,1,'Nivel 1','{\"fruitCount\": 10, \"fruitSpeed\": 1, \"basketSpeed\": 16}',0),(28,8,1,'Básico','{\"gravity\": 0.5, \"coinCount\": 10, \"jumpPower\": 12, \"playerSpeed\": 5, \"obstacleCount\": 5, \"platformCount\": 8}',0),(29,9,1,'Fácil','{\"speed\": 150, \"targetScore\": 100, \"obstacleCount\": 5}',0);
/*!40000 ALTER TABLE `nivells_joc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partides`
--

DROP TABLE IF EXISTS `partides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `partides` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuari_id` int NOT NULL,
  `joc_id` int NOT NULL,
  `nivell_jugat` int NOT NULL,
  `puntuacio_obtinguda` int NOT NULL,
  `data_partida` datetime DEFAULT CURRENT_TIMESTAMP,
  `durada_segons` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `usuari_id` (`usuari_id`),
  KEY `joc_id` (`joc_id`),
  CONSTRAINT `partides_ibfk_1` FOREIGN KEY (`usuari_id`) REFERENCES `usuaris` (`id`) ON DELETE CASCADE,
  CONSTRAINT `partides_ibfk_2` FOREIGN KEY (`joc_id`) REFERENCES `jocs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `partides`
--

LOCK TABLES `partides` WRITE;
/*!40000 ALTER TABLE `partides` DISABLE KEYS */;
INSERT INTO `partides` VALUES (1,1,4,5,0,'2025-10-24 14:50:47',10),(2,1,4,5,0,'2025-10-24 14:54:43',13),(3,1,4,1,0,'2025-10-28 15:54:54',48),(4,1,4,5,6,'2025-10-28 15:59:01',40),(5,1,4,3,10,'2025-10-28 16:06:09',413),(6,1,6,1,0,'2025-10-28 16:18:44',0),(7,1,6,2,0,'2025-10-28 16:18:52',0),(8,1,6,6,0,'2025-10-28 16:23:02',0),(9,1,6,6,0,'2025-10-28 16:23:05',0),(10,1,7,1,0,'2025-10-28 16:26:03',5),(11,1,6,1,0,'2025-10-28 16:26:21',0),(12,1,7,1,0,'2025-10-28 16:27:31',4),(13,1,7,1,0,'2025-10-28 16:30:27',5),(14,1,7,1,0,'2025-10-28 16:30:35',5),(15,1,7,1,0,'2025-10-28 16:32:19',5),(16,1,7,1,0,'2025-10-28 16:34:19',5),(17,1,7,1,0,'2025-10-28 16:36:08',5),(18,1,7,1,0,'2025-10-28 16:36:55',5),(19,1,7,1,10,'2025-10-28 16:38:58',5),(20,1,7,1,40,'2025-10-28 16:40:38',5),(21,1,7,1,10,'2025-10-28 16:41:21',4),(22,1,7,1,0,'2025-10-28 16:42:00',10),(23,1,6,1,50,'2025-10-28 16:43:45',12),(24,1,6,5,100,'2025-10-28 16:44:00',8),(25,1,6,5,150,'2025-10-28 16:44:12',10),(26,1,8,1,150,'2025-10-28 16:50:43',4),(27,1,8,1,250,'2025-10-28 16:50:54',9),(28,1,8,1,500,'2025-10-28 16:51:12',16),(29,1,8,1,0,'2025-10-28 17:03:18',3),(30,1,8,1,500,'2025-10-28 17:03:40',20);
/*!40000 ALTER TABLE `partides` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progres_usuari`
--

DROP TABLE IF EXISTS `progres_usuari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `progres_usuari` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuari_id` int NOT NULL,
  `joc_id` int NOT NULL,
  `nivell_actual` int DEFAULT '1',
  `puntuacio_maxima` int DEFAULT '0',
  `partides_jugades` int DEFAULT '0',
  `ultima_partida` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuari_id` (`usuari_id`),
  KEY `joc_id` (`joc_id`),
  CONSTRAINT `progres_usuari_ibfk_1` FOREIGN KEY (`usuari_id`) REFERENCES `usuaris` (`id`) ON DELETE CASCADE,
  CONSTRAINT `progres_usuari_ibfk_2` FOREIGN KEY (`joc_id`) REFERENCES `jocs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progres_usuari`
--

LOCK TABLES `progres_usuari` WRITE;
/*!40000 ALTER TABLE `progres_usuari` DISABLE KEYS */;
INSERT INTO `progres_usuari` VALUES (1,1,4,1,10,5,'2025-10-28 16:06:09'),(2,2,4,1,0,0,'2025-10-14 15:38:42'),(3,2,5,1,1070,0,'2025-10-17 13:53:24'),(4,1,5,1,1100,0,'2025-10-17 13:53:40'),(5,3,5,1,450,0,'2025-10-28 15:36:10'),(6,1,6,2,150,8,'2025-10-28 16:44:12'),(7,1,7,1,40,12,'2025-10-28 16:42:00'),(8,1,8,1,500,5,'2025-10-28 17:03:40');
/*!40000 ALTER TABLE `progres_usuari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuaris`
--

DROP TABLE IF EXISTS `usuaris`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuaris` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_usuari` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_complet` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_registre` datetime DEFAULT CURRENT_TIMESTAMP,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_usuari` (`nom_usuari`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuaris`
--

LOCK TABLES `usuaris` WRITE;
/*!40000 ALTER TABLE `usuaris` DISABLE KEYS */;
INSERT INTO `usuaris` VALUES (1,'alex','alex@alex.com','$2y$10$5imO.71gNKXGl51WI08yMuwxutKiFkY3wkYZSFr3ZaYrA1Z7ADpQ2','alex','2025-10-10 14:17:27','/uploads/avatars/avatar_1.gif'),(2,'dani','dani@dani.com','$2y$10$JVDlmnstEUs.OdFeIzmeUu8AT2KEeGdX6iCDDay/H6DTU4p793UK2','Daniel Coca','2025-10-10 14:24:16','/uploads/avatars/avatar_2.jpeg'),(3,'tomi','tomi@tomi.es','$2y$10$C5M9RzkX7HfzSlsqeH2EFuX8RBOEl7HVTW4ez6s5VBjC8P15Ivs1e','Tomasete','2025-10-28 15:35:15','/uploads/avatars/avatar_3.jpeg');
/*!40000 ALTER TABLE `usuaris` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-28 17:26:58
