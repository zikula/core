-- MySQL dump 10.13  Distrib 5.5.42, for osx10.6 (i386)
--
-- Host: localhost    Database: upgrading14
-- ------------------------------------------------------
-- Server version	5.5.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_category`
--

DROP TABLE IF EXISTS `admin_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_category` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sortorder` int(11) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_category`
--

LOCK TABLES `admin_category` WRITE;
/*!40000 ALTER TABLE `admin_category` DISABLE KEYS */;
INSERT INTO `admin_category` VALUES (1,'System','Core modules at the heart of operation of the site.',0),(2,'Layout','Layout modules for controlling the site\'s look and feel.',0),(3,'Users','Modules for controlling user membership, access rights and profiles.',0),(4,'Content','Modules for providing content to your users.',0),(5,'Uncategorised','Newly-installed or uncategorized modules.',0),(6,'Security','Modules for managing the site\'s security.',0);
/*!40000 ALTER TABLE `admin_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_module`
--

DROP TABLE IF EXISTS `admin_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_module` (
  `amid` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `sortorder` int(11) NOT NULL,
  PRIMARY KEY (`amid`),
  KEY `mid_cid` (`mid`,`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_module`
--

LOCK TABLES `admin_module` WRITE;
/*!40000 ALTER TABLE `admin_module` DISABLE KEYS */;
INSERT INTO `admin_module` VALUES (1,1,1,0),(2,11,1,1),(3,12,2,0),(4,2,1,2),(5,8,3,0),(6,5,3,1),(7,3,2,1),(8,13,3,2),(9,10,6,0),(10,4,4,0),(11,6,1,3),(12,9,4,1),(13,14,1,4);
/*!40000 ALTER TABLE `admin_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `block_placements`
--

DROP TABLE IF EXISTS `block_placements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `block_placements` (
  `pid` int(11) NOT NULL,
  `bid` int(11) NOT NULL,
  `sortorder` int(11) NOT NULL,
  PRIMARY KEY (`pid`,`bid`),
  KEY `bid_pid_idx` (`bid`,`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `block_placements`
--

LOCK TABLES `block_placements` WRITE;
/*!40000 ALTER TABLE `block_placements` DISABLE KEYS */;
INSERT INTO `block_placements` VALUES (1,1,0),(2,4,0),(3,3,0),(4,2,0),(7,5,0);
/*!40000 ALTER TABLE `block_placements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `block_positions`
--

DROP TABLE IF EXISTS `block_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `block_positions` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`pid`),
  KEY `name_idx` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `block_positions`
--

LOCK TABLES `block_positions` WRITE;
/*!40000 ALTER TABLE `block_positions` DISABLE KEYS */;
INSERT INTO `block_positions` VALUES (1,'left','Left blocks'),(2,'right','Right blocks'),(3,'center','Center blocks'),(4,'search','Search block'),(5,'header','Header block'),(6,'footer','Footer block'),(7,'topnav','Top navigation block'),(8,'bottomnav','Bottom navigation block');
/*!40000 ALTER TABLE `block_positions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blocks`
--

DROP TABLE IF EXISTS `blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocks` (
  `bid` int(11) NOT NULL AUTO_INCREMENT,
  `bkey` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `url` longtext COLLATE utf8_unicode_ci NOT NULL,
  `mid` int(11) NOT NULL,
  `filter` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `active` int(11) NOT NULL,
  `collapsable` int(11) NOT NULL,
  `defaultstate` int(11) NOT NULL,
  `refresh` int(11) NOT NULL,
  `last_update` datetime NOT NULL,
  `language` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`bid`),
  KEY `active_idx` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blocks`
--

LOCK TABLES `blocks` WRITE;
/*!40000 ALTER TABLE `blocks` DISABLE KEYS */;
INSERT INTO `blocks` VALUES (1,'Extmenu','Main menu','Main menu','a:5:{s:14:\"displaymodules\";s:1:\"0\";s:10:\"stylesheet\";s:11:\"extmenu.css\";s:8:\"template\";s:25:\"Block/Extmenu/extmenu.tpl\";s:11:\"blocktitles\";a:1:{s:2:\"en\";s:9:\"Main menu\";}s:5:\"links\";a:1:{s:2:\"en\";a:5:{i:0;a:7:{s:4:\"name\";s:4:\"Home\";s:3:\"url\";s:10:\"{homepage}\";s:5:\"title\";s:19:\"Go to the home page\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:1;a:7:{s:4:\"name\";s:14:\"Administration\";s:3:\"url\";s:24:\"{Admin:admin:adminpanel}\";s:5:\"title\";s:29:\"Go to the site administration\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:2;a:7:{s:4:\"name\";s:10:\"My Account\";s:3:\"url\";s:19:\"{ZikulaUsersModule}\";s:5:\"title\";s:24:\"Go to your account panel\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:3;a:7:{s:4:\"name\";s:7:\"Log out\";s:3:\"url\";s:31:\"{ZikulaUsersModule:user:logout}\";s:5:\"title\";s:20:\"Log out of this site\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:4;a:7:{s:4:\"name\";s:11:\"Site search\";s:3:\"url\";s:20:\"{ZikulaSearchModule}\";s:5:\"title\";s:16:\"Search this site\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}}}}','',3,'a:0:{}',1,1,1,3600,'2016-08-11 19:10:35',''),(2,'Search','Search box','Search block','a:2:{s:16:\"displaySearchBtn\";i:1;s:6:\"active\";a:1:{s:17:\"ZikulaUsersModule\";i:1;}}','',9,'a:0:{}',1,1,1,3600,'2016-08-11 19:10:35',''),(3,'Html','This site is powered by Zikula!','HTML block','<p><a href=\"http://zikula.org/\">Zikula</a> is a content management system (CMS) and application framework. It is secure and stable, and is a good choice for sites with a large volume of traffic.</p><p>With Zikula:</p><ul><li>you can customise all aspects of the site\'s appearance through themes, with support for CSS style sheets, JavaScript, Flash and all other modern web development technologies;</li><li>you can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation and internationalisation of your site;</li><li>you can be sure that your pages will display properly in all browsers, thanks to Zikula\'s full compliance with W3C HTML standards;</li><li>you get a standard application-programming interface (API) that lets you easily augment your site\'s functionality through modules, blocks and other extensions;</li><li>you can get help and support from the Zikula community of webmasters and developers at <a href=\"http://www.zikula.org\">zikula.org</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>','',3,'a:0:{}',1,1,1,3600,'2016-08-11 19:10:35',''),(4,'Login','User log-in','Login block','','',13,'a:0:{}',1,1,1,3600,'2016-08-11 19:10:35',''),(5,'Extmenu','Top navigation','Theme navigation','a:5:{s:14:\"displaymodules\";s:1:\"0\";s:10:\"stylesheet\";s:11:\"extmenu.css\";s:8:\"template\";s:24:\"Block/Extmenu/topnav.tpl\";s:11:\"blocktitles\";a:1:{s:2:\"en\";s:14:\"Top navigation\";}s:5:\"links\";a:1:{s:2:\"en\";a:3:{i:0;a:7:{s:4:\"name\";s:4:\"Home\";s:3:\"url\";s:10:\"{homepage}\";s:5:\"title\";s:26:\"Go to the site\'s home page\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:1;a:7:{s:4:\"name\";s:10:\"My Account\";s:3:\"url\";s:19:\"{ZikulaUsersModule}\";s:5:\"title\";s:24:\"Go to your account panel\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:2;a:7:{s:4:\"name\";s:11:\"Site search\";s:3:\"url\";s:20:\"{ZikulaSearchModule}\";s:5:\"title\";s:16:\"Search this site\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}}}}','',3,'a:0:{}',1,1,1,3600,'2016-08-11 19:10:35','');
/*!40000 ALTER TABLE `blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bundles`
--

DROP TABLE IF EXISTS `bundles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bundles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bundlename` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `autoload` varchar(384) COLLATE utf8_unicode_ci NOT NULL,
  `bundleclass` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `bundletype` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `bundlestate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D8A73A9867B776D8` (`bundlename`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bundles`
--

LOCK TABLES `bundles` WRITE;
/*!40000 ALTER TABLE `bundles` DISABLE KEYS */;
INSERT INTO `bundles` VALUES (1,'zikula/legal-module','a:1:{s:5:\"psr-4\";a:1:{s:19:\"Zikula\\LegalModule\\\";s:27:\"modules/zikula/legal-module\";}}','Zikula\\LegalModule\\ZikulaLegalModule','M',3),(2,'zikula/profile-module','a:1:{s:5:\"psr-4\";a:1:{s:21:\"Zikula\\ProfileModule\\\";s:29:\"modules/zikula/profile-module\";}}','Zikula\\ProfileModule\\ZikulaProfileModule','M',3),(3,'zikula/seabreeze-theme','a:1:{s:5:\"psr-4\";a:1:{s:22:\"Zikula\\SeaBreezeTheme\\\";s:21:\"themes/SeaBreezeTheme\";}}','Zikula\\SeaBreezeTheme\\ZikulaSeaBreezeTheme','T',3),(4,'zikula/andreas08-theme','a:1:{s:5:\"psr-0\";a:1:{s:28:\"Zikula\\Theme\\Andreas08Theme\\\";s:6:\"themes\";}}','Zikula\\Theme\\Andreas08Theme\\ZikulaAndreas08Theme','T',3),(5,'zikula/atom-theme','a:1:{s:5:\"psr-0\";a:1:{s:23:\"Zikula\\Theme\\AtomTheme\\\";s:6:\"themes\";}}','Zikula\\Theme\\AtomTheme\\ZikulaAtomTheme','T',3),(6,'zikula/bootstrap-theme','a:1:{s:5:\"psr-0\";a:1:{s:28:\"Zikula\\Theme\\BootstrapTheme\\\";s:6:\"themes\";}}','Zikula\\Theme\\BootstrapTheme\\ZikulaBootstrapTheme','T',3),(7,'zikula/printer-theme','a:1:{s:5:\"psr-0\";a:1:{s:26:\"Zikula\\Theme\\PrinterTheme\\\";s:6:\"themes\";}}','Zikula\\Theme\\PrinterTheme\\ZikulaPrinterTheme','T',3),(8,'zikula/rss-theme','a:1:{s:5:\"psr-0\";a:1:{s:22:\"Zikula\\Theme\\RssTheme\\\";s:6:\"themes\";}}','Zikula\\Theme\\RssTheme\\ZikulaRssTheme','T',3);
/*!40000 ALTER TABLE `bundles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories_attributes`
--

DROP TABLE IF EXISTS `categories_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories_attributes` (
  `category_id` int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`category_id`,`name`),
  KEY `IDX_9015DE7812469DE2` (`category_id`),
  CONSTRAINT `FK_9015DE7812469DE2` FOREIGN KEY (`category_id`) REFERENCES `categories_category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories_attributes`
--

LOCK TABLES `categories_attributes` WRITE;
/*!40000 ALTER TABLE `categories_attributes` DISABLE KEYS */;
INSERT INTO `categories_attributes` VALUES (5,'Y','code'),(6,'N','code'),(11,'P','code'),(12,'C','code'),(13,'A','code'),(14,'O','code'),(15,'R','code'),(17,'M','code'),(18,'F','code'),(26,'A','code'),(27,'I','code'),(29,'P','code'),(30,'A','code');
/*!40000 ALTER TABLE `categories_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories_category`
--

DROP TABLE IF EXISTS `categories_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL,
  `is_leaf` tinyint(1) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sort_value` int(11) NOT NULL,
  `display_name` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `display_desc` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `path` longtext COLLATE utf8_unicode_ci NOT NULL,
  `ipath` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `cr_date` datetime NOT NULL,
  `lu_date` datetime NOT NULL,
  `obj_status` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `cr_uid` int(11) DEFAULT NULL,
  `lu_uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D0B2B0F8727ACA70` (`parent_id`),
  KEY `IDX_D0B2B0F88304AF18` (`cr_uid`),
  KEY `IDX_D0B2B0F8C072C1DD` (`lu_uid`),
  KEY `idx_categories_is_leaf` (`is_leaf`),
  KEY `idx_categories_name` (`name`),
  KEY `idx_categories_ipath` (`ipath`,`is_leaf`,`status`),
  KEY `idx_categories_status` (`status`),
  KEY `idx_categories_ipath_status` (`ipath`,`status`),
  CONSTRAINT `FK_D0B2B0F8727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `categories_category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories_category`
--

LOCK TABLES `categories_category` WRITE;
/*!40000 ALTER TABLE `categories_category` DISABLE KEYS */;
INSERT INTO `categories_category` VALUES (1,NULL,1,0,'__SYSTEM__','',1,'s:0:\"\";','s:0:\"\";','/__SYSTEM__','/1','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(2,1,0,0,'Modules','',2,'a:1:{s:2:\"en\";s:7:\"Modules\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules','/1/2','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(3,1,0,0,'General','',3,'a:1:{s:2:\"en\";s:7:\"General\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General','/1/3','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(4,3,0,0,'YesNo','',4,'a:1:{s:2:\"en\";s:6:\"Yes/No\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/YesNo','/1/3/4','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(5,4,0,1,'1 - Yes','Y',5,'s:0:\"\";','s:0:\"\";','/__SYSTEM__/General/YesNo/1 - Yes','/1/3/4/5','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(6,4,0,1,'2 - No','N',6,'s:0:\"\";','s:0:\"\";','/__SYSTEM__/General/YesNo/2 - No','/1/3/4/6','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(10,3,0,0,'Publication Status (extended)','',10,'a:1:{s:2:\"en\";s:29:\"Publication status (extended)\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended','/1/3/10','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(11,10,0,1,'Pending','P',11,'a:1:{s:2:\"en\";s:7:\"Pending\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Pending','/1/3/10/11','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(12,10,0,1,'Checked','C',12,'a:1:{s:2:\"en\";s:7:\"Checked\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Checked','/1/3/10/12','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(13,10,0,1,'Approved','A',13,'a:1:{s:2:\"en\";s:8:\"Approved\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Approved','/1/3/10/13','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(14,10,0,1,'On-line','O',14,'a:1:{s:2:\"en\";s:7:\"On-line\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Online','/1/3/10/14','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(15,10,0,1,'Rejected','R',15,'a:1:{s:2:\"en\";s:8:\"Rejected\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Rejected','/1/3/10/15','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(16,3,0,0,'Gender','',16,'a:1:{s:2:\"en\";s:6:\"Gender\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Gender','/1/3/16','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(17,16,0,1,'Male','M',17,'a:1:{s:2:\"en\";s:4:\"Male\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Gender/Male','/1/3/16/17','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(18,16,0,1,'Female','F',18,'a:1:{s:2:\"en\";s:6:\"Female\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Gender/Female','/1/3/16/18','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(19,3,0,0,'Title','',19,'a:1:{s:2:\"en\";s:5:\"Title\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title','/1/3/19','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(20,19,0,1,'Mr','Mr',20,'a:1:{s:2:\"en\";s:3:\"Mr.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Mr','/1/3/19/20','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(21,19,0,1,'Mrs','Mrs',21,'a:1:{s:2:\"en\";s:4:\"Mrs.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Mrs','/1/3/19/21','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(22,19,0,1,'Ms','Ms',22,'a:1:{s:2:\"en\";s:3:\"Ms.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Ms','/1/3/19/22','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(23,19,0,1,'Miss','Miss',23,'a:1:{s:2:\"en\";s:4:\"Miss\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Miss','/1/3/19/23','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(24,19,0,1,'Dr','Dr',24,'a:1:{s:2:\"en\";s:3:\"Dr.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Dr','/1/3/19/24','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(25,3,0,0,'ActiveStatus','',25,'a:1:{s:2:\"en\";s:15:\"Activity status\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus','/1/3/25','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(26,25,0,1,'Active','A',26,'a:1:{s:2:\"en\";s:6:\"Active\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus/Active','/1/3/25/26','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(27,25,0,1,'Inactive','I',27,'a:1:{s:2:\"en\";s:8:\"Inactive\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus/Inactive','/1/3/25/27','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(28,3,0,0,'Publication status (basic)','',28,'a:1:{s:2:\"en\";s:26:\"Publication status (basic)\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic','/1/3/28','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(29,28,0,1,'Pending','P',29,'a:1:{s:2:\"en\";s:7:\"Pending\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic/Pending','/1/3/28/29','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(30,28,0,1,'Approved','A',30,'a:1:{s:2:\"en\";s:8:\"Approved\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic/Approved','/1/3/28/30','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(31,1,0,0,'ZikulaUsersModule','',31,'a:1:{s:2:\"en\";s:5:\"Users\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Users','/1/31','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(32,2,0,0,'Global','',32,'a:1:{s:2:\"en\";s:6:\"Global\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global','/1/2/32','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(33,32,0,1,'Blogging','',33,'a:1:{s:2:\"en\";s:8:\"Blogging\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/Blogging','/1/2/32/33','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(34,32,0,1,'Music and audio','',34,'a:1:{s:2:\"en\";s:15:\"Music and audio\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/MusicAndAudio','/1/2/32/34','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(35,32,0,1,'Art and photography','',35,'a:1:{s:2:\"en\";s:19:\"Art and photography\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ArtAndPhotography','/1/2/32/35','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(36,32,0,1,'Writing and thinking','',36,'a:1:{s:2:\"en\";s:20:\"Writing and thinking\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/WritingAndThinking','/1/2/32/36','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(37,32,0,1,'Communications and media','',37,'a:1:{s:2:\"en\";s:24:\"Communications and media\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/CommunicationsAndMedia','/1/2/32/37','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(38,32,0,1,'Travel and culture','',38,'a:1:{s:2:\"en\";s:18:\"Travel and culture\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/TravelAndCulture','/1/2/32/38','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(39,32,0,1,'Science and technology','',39,'a:1:{s:2:\"en\";s:22:\"Science and technology\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ScienceAndTechnology','/1/2/32/39','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(40,32,0,1,'Sport and activities','',40,'a:1:{s:2:\"en\";s:20:\"Sport and activities\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/SportAndActivities','/1/2/32/40','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2),(41,32,0,1,'Business and work','',41,'a:1:{s:2:\"en\";s:17:\"Business and work\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/BusinessAndWork','/1/2/32/41','A','2016-08-11 19:10:26','2016-08-11 19:10:26','A',2,2);
/*!40000 ALTER TABLE `categories_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories_mapobj`
--

DROP TABLE IF EXISTS `categories_mapobj`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories_mapobj` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `tablename` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `obj_id` int(11) NOT NULL,
  `obj_idcolumn` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `reg_id` int(11) NOT NULL,
  `reg_property` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(11) NOT NULL,
  `obj_status` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `cr_date` datetime NOT NULL,
  `cr_uid` int(11) NOT NULL,
  `lu_date` datetime NOT NULL,
  `lu_uid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories_mapobj`
--

LOCK TABLES `categories_mapobj` WRITE;
/*!40000 ALTER TABLE `categories_mapobj` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories_mapobj` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories_registry`
--

DROP TABLE IF EXISTS `categories_registry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories_registry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `entityname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `property` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(11) NOT NULL,
  `cr_date` datetime NOT NULL,
  `lu_date` datetime NOT NULL,
  `obj_status` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `cr_uid` int(11) DEFAULT NULL,
  `lu_uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1B56B4338304AF18` (`cr_uid`),
  KEY `IDX_1B56B433C072C1DD` (`lu_uid`),
  KEY `idx_categories_registry` (`modname`,`entityname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories_registry`
--

LOCK TABLES `categories_registry` WRITE;
/*!40000 ALTER TABLE `categories_registry` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories_registry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_applications`
--

DROP TABLE IF EXISTS `group_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_applications` (
  `app_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `application` longtext COLLATE utf8_unicode_ci NOT NULL,
  `status` smallint(6) NOT NULL,
  PRIMARY KEY (`app_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_applications`
--

LOCK TABLES `group_applications` WRITE;
/*!40000 ALTER TABLE `group_applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_membership`
--

DROP TABLE IF EXISTS `group_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_membership` (
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`gid`,`uid`),
  KEY `gid_uid` (`uid`,`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_membership`
--

LOCK TABLES `group_membership` WRITE;
/*!40000 ALTER TABLE `group_membership` DISABLE KEYS */;
INSERT INTO `group_membership` VALUES (1,1),(1,2),(2,2);
/*!40000 ALTER TABLE `group_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_perms`
--

DROP TABLE IF EXISTS `group_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_perms` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `sequence` int(11) NOT NULL,
  `realm` int(11) NOT NULL,
  `component` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `instance` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `level` int(11) NOT NULL,
  `bond` int(11) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_perms`
--

LOCK TABLES `group_perms` WRITE;
/*!40000 ALTER TABLE `group_perms` DISABLE KEYS */;
INSERT INTO `group_perms` VALUES (1,2,1,0,'.*','.*',800,0),(2,-1,2,0,'ExtendedMenublock::','1:1:',0,0),(3,1,3,0,'.*','.*',300,0),(4,0,4,0,'ExtendedMenublock::','1:(1|2|3):',0,0),(5,0,5,0,'.*','.*',200,0);
/*!40000 ALTER TABLE `group_perms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `gtype` smallint(6) NOT NULL,
  `description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `prefix` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `state` smallint(6) NOT NULL,
  `nbuser` int(11) NOT NULL,
  `nbumax` int(11) NOT NULL,
  `link` int(11) NOT NULL,
  `uidmaster` int(11) NOT NULL,
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'Users',0,'By default, all users are made members of this group.','usr',0,0,0,0,0),(2,'Administrators',0,'Group of administrators of this site.','adm',0,0,0,0,0);
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hook_area`
--

DROP TABLE IF EXISTS `hook_area`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hook_area` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subowner` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `areatype` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `category` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `areaname` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `areaidx` (`areaname`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hook_area`
--

LOCK TABLES `hook_area` WRITE;
/*!40000 ALTER TABLE `hook_area` DISABLE KEYS */;
INSERT INTO `hook_area` VALUES (1,'ZikulaBlocksModule',NULL,'s','ui_hooks','subscriber.blocks.ui_hooks.htmlblock.content'),(2,'ZikulaUsersModule',NULL,'s','ui_hooks','subscriber.users.ui_hooks.user'),(3,'ZikulaUsersModule',NULL,'s','ui_hooks','subscriber.users.ui_hooks.registration'),(4,'ZikulaUsersModule',NULL,'s','ui_hooks','subscriber.users.ui_hooks.login_screen'),(5,'ZikulaUsersModule',NULL,'s','ui_hooks','subscriber.users.ui_hooks.login_block'),(6,'ZikulaMailerModule',NULL,'s','ui_hooks','subscriber.mailer.ui_hooks.htmlmail'),(7,'ZikulaRoutesModule',NULL,'s','ui_hooks','subscriber.zikularoutesmodule.ui_hooks.routes'),(8,'ZikulaRoutesModule',NULL,'s','filter_hooks','subscriber.zikularoutesmodule.filter_hooks.routes');
/*!40000 ALTER TABLE `hook_area` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hook_binding`
--

DROP TABLE IF EXISTS `hook_binding`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hook_binding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sowner` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subsowner` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `powner` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subpowner` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sareaid` int(11) NOT NULL,
  `pareaid` int(11) NOT NULL,
  `category` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `sortorder` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hook_binding`
--

LOCK TABLES `hook_binding` WRITE;
/*!40000 ALTER TABLE `hook_binding` DISABLE KEYS */;
/*!40000 ALTER TABLE `hook_binding` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hook_provider`
--

DROP TABLE IF EXISTS `hook_provider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hook_provider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subowner` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pareaid` int(11) NOT NULL,
  `hooktype` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `category` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `classname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `serviceid` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hook_provider`
--

LOCK TABLES `hook_provider` WRITE;
/*!40000 ALTER TABLE `hook_provider` DISABLE KEYS */;
/*!40000 ALTER TABLE `hook_provider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hook_runtime`
--

DROP TABLE IF EXISTS `hook_runtime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hook_runtime` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sowner` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subsowner` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `powner` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subpowner` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sareaid` int(11) NOT NULL,
  `pareaid` int(11) NOT NULL,
  `eventname` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `classname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `serviceid` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `priority` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hook_runtime`
--

LOCK TABLES `hook_runtime` WRITE;
/*!40000 ALTER TABLE `hook_runtime` DISABLE KEYS */;
/*!40000 ALTER TABLE `hook_runtime` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hook_subscriber`
--

DROP TABLE IF EXISTS `hook_subscriber`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hook_subscriber` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subowner` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sareaid` int(11) NOT NULL,
  `hooktype` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `category` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `eventname` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hook_subscriber`
--

LOCK TABLES `hook_subscriber` WRITE;
/*!40000 ALTER TABLE `hook_subscriber` DISABLE KEYS */;
INSERT INTO `hook_subscriber` VALUES (1,'ZikulaBlocksModule',NULL,1,'form_edit','ui_hooks','blocks.ui_hooks.htmlblock.content.form_edit'),(2,'ZikulaUsersModule',NULL,2,'display_view','ui_hooks','users.ui_hooks.user.display_view'),(3,'ZikulaUsersModule',NULL,2,'form_edit','ui_hooks','users.ui_hooks.user.form_edit'),(4,'ZikulaUsersModule',NULL,2,'validate_edit','ui_hooks','users.ui_hooks.user.validate_edit'),(5,'ZikulaUsersModule',NULL,2,'process_edit','ui_hooks','users.ui_hooks.user.process_edit'),(6,'ZikulaUsersModule',NULL,2,'form_delete','ui_hooks','users.ui_hooks.user.form_delete'),(7,'ZikulaUsersModule',NULL,2,'validate_delete','ui_hooks','users.ui_hooks.user.validate_delete'),(8,'ZikulaUsersModule',NULL,2,'process_delete','ui_hooks','users.ui_hooks.user.process_delete'),(9,'ZikulaUsersModule',NULL,3,'display_view','ui_hooks','users.ui_hooks.registration.display_view'),(10,'ZikulaUsersModule',NULL,3,'form_edit','ui_hooks','users.ui_hooks.registration.form_edit'),(11,'ZikulaUsersModule',NULL,3,'validate_edit','ui_hooks','users.ui_hooks.registration.validate_edit'),(12,'ZikulaUsersModule',NULL,3,'process_edit','ui_hooks','users.ui_hooks.registration.process_edit'),(13,'ZikulaUsersModule',NULL,3,'form_delete','ui_hooks','users.ui_hooks.registration.form_delete'),(14,'ZikulaUsersModule',NULL,3,'validate_delete','ui_hooks','users.ui_hooks.registration.validate_delete'),(15,'ZikulaUsersModule',NULL,3,'process_delete','ui_hooks','users.ui_hooks.registration.process_delete'),(16,'ZikulaUsersModule',NULL,4,'form_edit','ui_hooks','users.ui_hooks.login_screen.form_edit'),(17,'ZikulaUsersModule',NULL,4,'validate_edit','ui_hooks','users.ui_hooks.login_screen.validate_edit'),(18,'ZikulaUsersModule',NULL,4,'process_edit','ui_hooks','users.ui_hooks.login_screen.process_edit'),(19,'ZikulaUsersModule',NULL,5,'form_edit','ui_hooks','users.ui_hooks.login_block.form_edit'),(20,'ZikulaUsersModule',NULL,5,'validate_edit','ui_hooks','users.ui_hooks.login_block.validate_edit'),(21,'ZikulaUsersModule',NULL,5,'process_edit','ui_hooks','users.ui_hooks.login_block.process_edit'),(22,'ZikulaMailerModule',NULL,6,'form_edit','ui_hooks','mailer.ui_hooks.htmlmail.form_edit'),(23,'ZikulaRoutesModule',NULL,7,'display_view','ui_hooks','zikularoutesmodule.ui_hooks.routes.display_view'),(24,'ZikulaRoutesModule',NULL,7,'form_edit','ui_hooks','zikularoutesmodule.ui_hooks.routes.form_edit'),(25,'ZikulaRoutesModule',NULL,7,'form_delete','ui_hooks','zikularoutesmodule.ui_hooks.routes.form_delete'),(26,'ZikulaRoutesModule',NULL,7,'validate_edit','ui_hooks','zikularoutesmodule.ui_hooks.routes.validate_edit'),(27,'ZikulaRoutesModule',NULL,7,'validate_delete','ui_hooks','zikularoutesmodule.ui_hooks.routes.validate_delete'),(28,'ZikulaRoutesModule',NULL,7,'process_edit','ui_hooks','zikularoutesmodule.ui_hooks.routes.process_edit'),(29,'ZikulaRoutesModule',NULL,7,'process_delete','ui_hooks','zikularoutesmodule.ui_hooks.routes.process_delete'),(30,'ZikulaRoutesModule',NULL,8,'filter','filter_hooks','zikularoutesmodule.filter_hooks.routes.filter');
/*!40000 ALTER TABLE `hook_subscriber` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_deps`
--

DROP TABLE IF EXISTS `module_deps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_deps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modid` int(11) NOT NULL,
  `modname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `minversion` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `maxversion` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_deps`
--

LOCK TABLES `module_deps` WRITE;
/*!40000 ALTER TABLE `module_deps` DISABLE KEYS */;
INSERT INTO `module_deps` VALUES (1,3,'Scribite','5.0.0','',2);
/*!40000 ALTER TABLE `module_deps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_vars`
--

DROP TABLE IF EXISTS `module_vars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_vars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_vars`
--

LOCK TABLES `module_vars` WRITE;
/*!40000 ALTER TABLE `module_vars` DISABLE KEYS */;
INSERT INTO `module_vars` VALUES (1,'ZikulaExtensionsModule','itemsperpage','i:25;'),(2,'ZConfig','debug','s:1:\"0\";'),(3,'ZConfig','startdate','s:7:\"08/2016\";'),(4,'ZConfig','adminmail','s:17:\"admin@example.com\";'),(5,'ZConfig','Default_Theme','s:20:\"ZikulaAndreas08Theme\";'),(6,'ZConfig','timezone_offset','s:1:\"0\";'),(7,'ZConfig','timezone_server','s:1:\"0\";'),(8,'ZConfig','funtext','s:1:\"1\";'),(9,'ZConfig','reportlevel','s:1:\"0\";'),(10,'ZConfig','startpage','s:0:\"\";'),(11,'ZConfig','Version_Num','s:5:\"1.4.0\";'),(12,'ZConfig','Version_ID','s:6:\"Zikula\";'),(13,'ZConfig','Version_Sub','s:8:\"Overture\";'),(14,'ZConfig','debug_sql','s:1:\"0\";'),(15,'ZConfig','multilingual','s:1:\"1\";'),(16,'ZConfig','useflags','s:1:\"0\";'),(17,'ZConfig','theme_change','s:1:\"0\";'),(18,'ZConfig','UseCompression','s:1:\"0\";'),(19,'ZConfig','siteoff','i:0;'),(20,'ZConfig','siteoffreason','s:0:\"\";'),(21,'ZConfig','starttype','s:0:\"\";'),(22,'ZConfig','startfunc','s:0:\"\";'),(23,'ZConfig','startargs','s:0:\"\";'),(24,'ZConfig','entrypoint','s:9:\"index.php\";'),(25,'ZConfig','language_detect','i:0;'),(26,'ZConfig','shorturls','b:0;'),(27,'ZConfig','shorturlstype','s:1:\"0\";'),(28,'ZConfig','shorturlsseparator','s:1:\"-\";'),(29,'ZConfig','sitename_en','s:9:\"Site name\";'),(30,'ZConfig','slogan_en','s:16:\"Site description\";'),(31,'ZConfig','metakeywords_en','s:115:\"zikula, portal, open source, web site, website, weblog, blog, content management system, cms, application framework\";'),(32,'ZConfig','defaultpagetitle_en','s:9:\"Site name\";'),(33,'ZConfig','defaultmetadescription_en','s:16:\"Site description\";'),(34,'ZConfig','shorturlsstripentrypoint','b:1;'),(35,'ZConfig','shorturlsdefaultmodule','s:0:\"\";'),(36,'ZConfig','profilemodule','s:0:\"\";'),(37,'ZConfig','messagemodule','s:0:\"\";'),(38,'ZConfig','languageurl','i:0;'),(39,'ZConfig','ajaxtimeout','i:5000;'),(40,'ZConfig','permasearch','s:161:\"À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü\";'),(41,'ZConfig','permareplace','s:114:\"A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,ss,ae,Ae,oe,Oe,ue,Ue\";'),(42,'ZConfig','language','s:3:\"eng\";'),(43,'ZConfig','locale','s:2:\"en\";'),(44,'ZConfig','language_i18n','s:2:\"en\";'),(45,'ZConfig','idnnames','i:1;'),(46,'ZikulaThemeModule','modulesnocache','s:0:\"\";'),(47,'ZikulaThemeModule','enablecache','b:0;'),(48,'ZikulaThemeModule','compile_check','b:1;'),(49,'ZikulaThemeModule','cache_lifetime','i:1800;'),(50,'ZikulaThemeModule','cache_lifetime_mods','i:1800;'),(51,'ZikulaThemeModule','force_compile','b:0;'),(52,'ZikulaThemeModule','trimwhitespace','b:0;'),(53,'ZikulaThemeModule','maxsizeforlinks','i:30;'),(54,'ZikulaThemeModule','itemsperpage','i:25;'),(55,'ZikulaThemeModule','cssjscombine','b:0;'),(56,'ZikulaThemeModule','cssjscompress','b:0;'),(57,'ZikulaThemeModule','cssjsminify','b:0;'),(58,'ZikulaThemeModule','cssjscombine_lifetime','i:3600;'),(59,'ZikulaThemeModule','render_compile_check','b:1;'),(60,'ZikulaThemeModule','render_force_compile','b:0;'),(61,'ZikulaThemeModule','render_cache','b:0;'),(62,'ZikulaThemeModule','render_expose_template','b:0;'),(63,'ZikulaThemeModule','render_lifetime','i:3600;'),(64,'ZikulaAdminModule','modulesperrow','i:3;'),(65,'ZikulaAdminModule','itemsperpage','i:15;'),(66,'ZikulaAdminModule','defaultcategory','i:5;'),(67,'ZikulaAdminModule','admingraphic','i:1;'),(68,'ZikulaAdminModule','startcategory','i:1;'),(69,'ZikulaAdminModule','ignoreinstallercheck','i:0;'),(70,'ZikulaAdminModule','admintheme','s:0:\"\";'),(71,'ZikulaAdminModule','displaynametype','i:1;'),(72,'ZikulaPermissionsModule','filter','i:1;'),(73,'ZikulaPermissionsModule','warnbar','i:1;'),(74,'ZikulaPermissionsModule','rowview','i:20;'),(75,'ZikulaPermissionsModule','rowedit','i:20;'),(76,'ZikulaPermissionsModule','lockadmin','i:1;'),(77,'ZikulaPermissionsModule','adminid','i:1;'),(78,'ZikulaGroupsModule','itemsperpage','i:25;'),(79,'ZikulaGroupsModule','defaultgroup','i:1;'),(80,'ZikulaGroupsModule','mailwarning','i:0;'),(81,'ZikulaGroupsModule','hideclosed','i:0;'),(82,'ZikulaGroupsModule','primaryadmingroup','i:2;'),(83,'ZikulaBlocksModule','collapseable','i:0;'),(84,'ZikulaUsersModule','accountdisplaygraphics','b:1;'),(85,'ZikulaUsersModule','accountitemsperpage','i:25;'),(86,'ZikulaUsersModule','accountitemsperrow','i:5;'),(87,'ZikulaUsersModule','userimg','s:11:\"images/menu\";'),(88,'ZikulaUsersModule','anonymous','s:5:\"Guest\";'),(89,'ZikulaUsersModule','avatarpath','s:13:\"images/avatar\";'),(90,'ZikulaUsersModule','chgemail_expiredays','i:0;'),(91,'ZikulaUsersModule','chgpass_expiredays','i:0;'),(92,'ZikulaUsersModule','reg_expiredays','i:0;'),(93,'ZikulaUsersModule','allowgravatars','b:1;'),(94,'ZikulaUsersModule','gravatarimage','s:12:\"gravatar.jpg\";'),(95,'ZikulaUsersModule','hash_method','s:6:\"sha256\";'),(96,'ZikulaUsersModule','itemsperpage','i:25;'),(97,'ZikulaUsersModule','login_displayapproval','b:0;'),(98,'ZikulaUsersModule','login_displaydelete','b:0;'),(99,'ZikulaUsersModule','login_displayinactive','b:0;'),(100,'ZikulaUsersModule','login_displayverify','b:0;'),(101,'ZikulaUsersModule','loginviaoption','i:0;'),(102,'ZikulaUsersModule','login_redirect','b:1;'),(103,'ZikulaUsersModule','changeemail','b:1;'),(104,'ZikulaUsersModule','minpass','i:5;'),(105,'ZikulaUsersModule','use_password_strength_meter','b:0;'),(106,'ZikulaUsersModule','password_reminder_enabled','b:0;'),(107,'ZikulaUsersModule','password_reminder_mandatory','b:1;'),(108,'ZikulaUsersModule','reg_notifyemail','s:0:\"\";'),(109,'ZikulaUsersModule','reg_question','s:0:\"\";'),(110,'ZikulaUsersModule','reg_answer','s:0:\"\";'),(111,'ZikulaUsersModule','moderation','b:0;'),(112,'ZikulaUsersModule','moderation_order','i:0;'),(113,'ZikulaUsersModule','reg_autologin','b:0;'),(114,'ZikulaUsersModule','reg_noregreasons','s:51:\"Sorry! New user registration is currently disabled.\";'),(115,'ZikulaUsersModule','reg_allowreg','b:1;'),(116,'ZikulaUsersModule','reg_Illegaluseragents','s:0:\"\";'),(117,'ZikulaUsersModule','reg_Illegaldomains','s:0:\"\";'),(118,'ZikulaUsersModule','reg_Illegalusername','s:66:\"root, webmaster, admin, administrator, nobody, anonymous, username\";'),(119,'ZikulaUsersModule','reg_verifyemail','i:2;'),(120,'ZikulaUsersModule','reg_uniemail','b:1;'),(121,'ZikulaSecurityCenterModule','itemsperpage','i:10;'),(122,'ZConfig','updatecheck','i:1;'),(123,'ZConfig','updatefrequency','i:7;'),(124,'ZConfig','updatelastchecked','i:1470957141;'),(125,'ZConfig','updateversion','s:5:\"1.4.2\";'),(126,'ZConfig','keyexpiry','i:0;'),(127,'ZConfig','sessionauthkeyua','b:0;'),(128,'ZConfig','secure_domain','s:0:\"\";'),(129,'ZConfig','signcookies','i:1;'),(130,'ZConfig','signingkey','s:40:\"3cdb7e2ffd374206628850d1071798fd38459d18\";'),(131,'ZConfig','seclevel','s:6:\"Medium\";'),(132,'ZConfig','secmeddays','i:7;'),(133,'ZConfig','secinactivemins','i:20;'),(134,'ZConfig','sessionstoretofile','i:0;'),(135,'ZConfig','sessionsavepath','s:0:\"\";'),(136,'ZConfig','gc_probability','i:100;'),(137,'ZConfig','anonymoussessions','i:1;'),(138,'ZConfig','sessionrandregenerate','b:1;'),(139,'ZConfig','sessionregenerate','b:1;'),(140,'ZConfig','sessionregeneratefreq','i:10;'),(141,'ZConfig','sessionipcheck','i:0;'),(142,'ZConfig','sessionname','s:5:\"_zsid\";'),(143,'ZConfig','sessioncsrftokenonetime','i:1;'),(144,'ZConfig','filtergetvars','i:1;'),(145,'ZConfig','filterpostvars','i:1;'),(146,'ZConfig','filtercookievars','i:1;'),(147,'ZConfig','outputfilter','i:1;'),(148,'ZConfig','htmlpurifierlocation','s:92:\"/Applications/MAMP/htdocs/140/system/Zikula/Module/SecurityCenterModule/vendor/htmlpurifier/\";'),(149,'ZikulaSecurityCenterModule','htmlpurifierConfig','s:4303:\"a:10:{s:4:\"Attr\";a:15:{s:14:\"AllowedClasses\";N;s:19:\"AllowedFrameTargets\";a:0:{}s:10:\"AllowedRel\";a:3:{s:8:\"nofollow\";b:1;s:11:\"imageviewer\";b:1;s:8:\"lightbox\";b:1;}s:10:\"AllowedRev\";a:0:{}s:13:\"ClassUseCDATA\";N;s:15:\"DefaultImageAlt\";N;s:19:\"DefaultInvalidImage\";s:0:\"\";s:22:\"DefaultInvalidImageAlt\";s:13:\"Invalid image\";s:14:\"DefaultTextDir\";s:3:\"ltr\";s:8:\"EnableID\";b:0;s:16:\"ForbiddenClasses\";a:0:{}s:11:\"IDBlacklist\";a:0:{}s:17:\"IDBlacklistRegexp\";N;s:8:\"IDPrefix\";s:0:\"\";s:13:\"IDPrefixLocal\";s:0:\"\";}s:10:\"AutoFormat\";a:11:{s:13:\"AutoParagraph\";b:0;s:6:\"Custom\";a:0:{}s:14:\"DisplayLinkURI\";b:0;s:7:\"Linkify\";b:0;s:22:\"PurifierLinkify.DocURL\";s:3:\"#%s\";s:15:\"PurifierLinkify\";b:0;s:21:\"RemoveEmpty.Predicate\";a:4:{s:8:\"colgroup\";a:0:{}s:2:\"th\";a:0:{}s:2:\"td\";a:0:{}s:6:\"iframe\";a:1:{i:0;s:3:\"src\";}}s:33:\"RemoveEmpty.RemoveNbsp.Exceptions\";a:2:{s:2:\"td\";b:1;s:2:\"th\";b:1;}s:22:\"RemoveEmpty.RemoveNbsp\";b:0;s:11:\"RemoveEmpty\";b:0;s:28:\"RemoveSpansWithoutAttributes\";b:0;}s:3:\"CSS\";a:9:{s:14:\"AllowImportant\";b:0;s:11:\"AllowTricky\";b:0;s:12:\"AllowedFonts\";N;s:17:\"AllowedProperties\";N;s:13:\"DefinitionRev\";i:1;s:19:\"ForbiddenProperties\";a:0:{}s:12:\"MaxImgLength\";s:6:\"1200px\";s:11:\"Proprietary\";b:0;s:7:\"Trusted\";b:0;}s:5:\"Cache\";a:3:{s:14:\"DefinitionImpl\";s:10:\"Serializer\";s:14:\"SerializerPath\";N;s:21:\"SerializerPermissions\";i:493;}s:4:\"Core\";a:20:{s:17:\"AggressivelyFixLt\";b:1;s:23:\"AllowHostnameUnderscore\";b:0;s:13:\"CollectErrors\";b:0;s:13:\"ColorKeywords\";a:17:{s:6:\"maroon\";s:7:\"#800000\";s:3:\"red\";s:7:\"#FF0000\";s:6:\"orange\";s:7:\"#FFA500\";s:6:\"yellow\";s:7:\"#FFFF00\";s:5:\"olive\";s:7:\"#808000\";s:6:\"purple\";s:7:\"#800080\";s:7:\"fuchsia\";s:7:\"#FF00FF\";s:5:\"white\";s:7:\"#FFFFFF\";s:4:\"lime\";s:7:\"#00FF00\";s:5:\"green\";s:7:\"#008000\";s:4:\"navy\";s:7:\"#000080\";s:4:\"blue\";s:7:\"#0000FF\";s:4:\"aqua\";s:7:\"#00FFFF\";s:4:\"teal\";s:7:\"#008080\";s:5:\"black\";s:7:\"#000000\";s:6:\"silver\";s:7:\"#C0C0C0\";s:4:\"gray\";s:7:\"#808080\";}s:25:\"ConvertDocumentToFragment\";b:1;s:31:\"DirectLexLineNumberSyncInterval\";i:0;s:15:\"DisableExcludes\";b:0;s:10:\"EnableIDNA\";b:0;s:8:\"Encoding\";s:5:\"utf-8\";s:21:\"EscapeInvalidChildren\";b:0;s:17:\"EscapeInvalidTags\";b:0;s:24:\"EscapeNonASCIICharacters\";b:0;s:14:\"HiddenElements\";a:2:{s:6:\"script\";b:1;s:5:\"style\";b:1;}s:8:\"Language\";s:2:\"en\";s:9:\"LexerImpl\";N;s:19:\"MaintainLineNumbers\";N;s:17:\"NormalizeNewlines\";b:1;s:16:\"RemoveInvalidImg\";b:1;s:28:\"RemoveProcessingInstructions\";b:0;s:20:\"RemoveScriptContents\";N;}s:6:\"Filter\";a:6:{s:6:\"Custom\";a:0:{}s:27:\"ExtractStyleBlocks.Escaping\";b:1;s:24:\"ExtractStyleBlocks.Scope\";N;s:27:\"ExtractStyleBlocks.TidyImpl\";N;s:18:\"ExtractStyleBlocks\";b:0;s:7:\"YouTube\";b:0;}s:4:\"HTML\";a:31:{s:7:\"Allowed\";N;s:17:\"AllowedAttributes\";N;s:15:\"AllowedComments\";a:0:{}s:21:\"AllowedCommentsRegexp\";N;s:15:\"AllowedElements\";N;s:14:\"AllowedModules\";N;s:18:\"Attr.Name.UseCDATA\";b:0;s:12:\"BlockWrapper\";s:1:\"p\";s:11:\"CoreModules\";a:7:{s:9:\"Structure\";b:1;s:4:\"Text\";b:1;s:9:\"Hypertext\";b:1;s:4:\"List\";b:1;s:22:\"NonXMLCommonAttributes\";b:1;s:19:\"XMLCommonAttributes\";b:1;s:16:\"CommonAttributes\";b:1;}s:13:\"CustomDoctype\";N;s:12:\"DefinitionID\";N;s:13:\"DefinitionRev\";i:1;s:7:\"Doctype\";s:22:\"HTML 4.01 Transitional\";s:20:\"FlashAllowFullScreen\";b:0;s:19:\"ForbiddenAttributes\";a:0:{}s:17:\"ForbiddenElements\";a:0:{}s:12:\"MaxImgLength\";i:1200;s:8:\"Nofollow\";b:0;s:6:\"Parent\";s:3:\"div\";s:11:\"Proprietary\";b:0;s:9:\"SafeEmbed\";b:1;s:10:\"SafeIframe\";b:0;s:10:\"SafeObject\";b:1;s:13:\"SafeScripting\";a:0:{}s:6:\"Strict\";b:0;s:11:\"TargetBlank\";b:0;s:7:\"TidyAdd\";a:0:{}s:9:\"TidyLevel\";s:6:\"medium\";s:10:\"TidyRemove\";a:0:{}s:7:\"Trusted\";b:0;s:5:\"XHTML\";b:1;}s:6:\"Output\";a:6:{s:21:\"CommentScriptContents\";b:1;s:12:\"FixInnerHTML\";b:1;s:11:\"FlashCompat\";b:1;s:7:\"Newline\";N;s:8:\"SortAttr\";b:0;s:10:\"TidyFormat\";b:0;}s:4:\"Test\";a:1:{s:12:\"ForceNoIconv\";b:0;}s:3:\"URI\";a:17:{s:14:\"AllowedSchemes\";a:6:{s:4:\"http\";b:1;s:5:\"https\";b:1;s:6:\"mailto\";b:1;s:3:\"ftp\";b:1;s:4:\"nntp\";b:1;s:4:\"news\";b:1;}s:4:\"Base\";N;s:13:\"DefaultScheme\";s:4:\"http\";s:12:\"DefinitionID\";N;s:13:\"DefinitionRev\";i:1;s:7:\"Disable\";b:0;s:15:\"DisableExternal\";b:0;s:24:\"DisableExternalResources\";b:0;s:16:\"DisableResources\";b:0;s:4:\"Host\";N;s:13:\"HostBlacklist\";a:0:{}s:12:\"MakeAbsolute\";b:0;s:5:\"Munge\";N;s:14:\"MungeResources\";b:0;s:14:\"MungeSecretKey\";N;s:22:\"OverrideAllowedSchemes\";b:1;s:16:\"SafeIframeRegexp\";N;}}\";'),(150,'ZConfig','useids','i:0;'),(151,'ZConfig','idsmail','i:0;'),(152,'ZConfig','idsrulepath','s:114:\"/Applications/MAMP/htdocs/140/system/Zikula/Module/SecurityCenterModule/Resources/config/phpids_zikula_default.xml\";'),(153,'ZConfig','idssoftblock','i:1;'),(154,'ZConfig','idsfilter','s:3:\"xml\";'),(155,'ZConfig','idsimpactthresholdone','i:1;'),(156,'ZConfig','idsimpactthresholdtwo','i:10;'),(157,'ZConfig','idsimpactthresholdthree','i:25;'),(158,'ZConfig','idsimpactthresholdfour','i:75;'),(159,'ZConfig','idsimpactmode','i:1;'),(160,'ZConfig','idshtmlfields','a:1:{i:0;s:14:\"POST.__wysiwyg\";}'),(161,'ZConfig','idsjsonfields','a:1:{i:0;s:15:\"POST.__jsondata\";}'),(162,'ZConfig','idsexceptions','a:12:{i:0;s:10:\"GET.__utmz\";i:1;s:10:\"GET.__utmc\";i:2;s:18:\"REQUEST.linksorder\";i:3;s:15:\"POST.linksorder\";i:4;s:19:\"REQUEST.fullcontent\";i:5;s:16:\"POST.fullcontent\";i:6;s:22:\"REQUEST.summarycontent\";i:7;s:19:\"POST.summarycontent\";i:8;s:19:\"REQUEST.filter.page\";i:9;s:16:\"POST.filter.page\";i:10;s:20:\"REQUEST.filter.value\";i:11;s:17:\"POST.filter.value\";}'),(163,'ZConfig','htmlentities','s:1:\"1\";'),(164,'ZConfig','AllowableHTML','a:110:{s:3:\"!--\";i:2;s:1:\"a\";i:2;s:4:\"abbr\";i:1;s:7:\"acronym\";i:1;s:7:\"address\";i:1;s:6:\"applet\";i:0;s:4:\"area\";i:0;s:7:\"article\";i:1;s:5:\"aside\";i:1;s:5:\"audio\";i:0;s:1:\"b\";i:1;s:4:\"base\";i:0;s:8:\"basefont\";i:0;s:3:\"bdo\";i:0;s:3:\"big\";i:0;s:10:\"blockquote\";i:2;s:2:\"br\";i:2;s:6:\"button\";i:0;s:6:\"canvas\";i:0;s:7:\"caption\";i:1;s:6:\"center\";i:2;s:4:\"cite\";i:1;s:4:\"code\";i:0;s:3:\"col\";i:1;s:8:\"colgroup\";i:1;s:7:\"command\";i:0;s:8:\"datalist\";i:0;s:2:\"dd\";i:1;s:3:\"del\";i:0;s:7:\"details\";i:1;s:3:\"dfn\";i:0;s:3:\"dir\";i:0;s:3:\"div\";i:2;s:2:\"dl\";i:1;s:2:\"dt\";i:1;s:2:\"em\";i:2;s:5:\"embed\";i:0;s:8:\"fieldset\";i:1;s:10:\"figcaption\";i:0;s:6:\"figure\";i:0;s:6:\"footer\";i:0;s:4:\"font\";i:0;s:4:\"form\";i:0;s:2:\"h1\";i:1;s:2:\"h2\";i:1;s:2:\"h3\";i:1;s:2:\"h4\";i:1;s:2:\"h5\";i:1;s:2:\"h6\";i:1;s:6:\"header\";i:0;s:6:\"hgroup\";i:0;s:2:\"hr\";i:2;s:1:\"i\";i:1;s:6:\"iframe\";i:0;s:3:\"img\";i:2;s:5:\"input\";i:0;s:3:\"ins\";i:0;s:6:\"keygen\";i:0;s:3:\"kbd\";i:0;s:5:\"label\";i:1;s:6:\"legend\";i:1;s:2:\"li\";i:2;s:3:\"map\";i:0;s:4:\"mark\";i:0;s:4:\"menu\";i:0;s:7:\"marquee\";i:0;s:5:\"meter\";i:0;s:3:\"nav\";i:0;s:4:\"nobr\";i:0;s:6:\"object\";i:0;s:2:\"ol\";i:2;s:8:\"optgroup\";i:0;s:6:\"option\";i:0;s:6:\"output\";i:0;s:1:\"p\";i:2;s:5:\"param\";i:0;s:3:\"pre\";i:2;s:8:\"progress\";i:0;s:1:\"q\";i:0;s:2:\"rp\";i:0;s:2:\"rt\";i:0;s:4:\"ruby\";i:0;s:1:\"s\";i:0;s:4:\"samp\";i:0;s:6:\"script\";i:0;s:7:\"section\";i:0;s:6:\"select\";i:0;s:5:\"small\";i:0;s:6:\"source\";i:0;s:4:\"span\";i:2;s:6:\"strike\";i:0;s:6:\"strong\";i:2;s:3:\"sub\";i:1;s:7:\"summary\";i:1;s:3:\"sup\";i:0;s:5:\"table\";i:2;s:5:\"tbody\";i:1;s:2:\"td\";i:2;s:8:\"textarea\";i:0;s:5:\"tfoot\";i:1;s:2:\"th\";i:2;s:5:\"thead\";i:0;s:4:\"time\";i:0;s:2:\"tr\";i:2;s:2:\"tt\";i:2;s:1:\"u\";i:0;s:2:\"ul\";i:2;s:3:\"var\";i:0;s:5:\"video\";i:0;s:3:\"wbr\";i:0;}'),(165,'ZikulaCategoriesModule','userrootcat','s:17:\"/__SYSTEM__/Users\";'),(166,'ZikulaCategoriesModule','allowusercatedit','i:0;'),(167,'ZikulaCategoriesModule','autocreateusercat','i:0;'),(168,'ZikulaCategoriesModule','autocreateuserdefaultcat','i:0;'),(169,'ZikulaCategoriesModule','userdefaultcatname','s:7:\"Default\";'),(170,'ZikulaMailerModule','charset','s:5:\"utf-8\";'),(171,'ZikulaMailerModule','encoding','s:4:\"8bit\";'),(172,'ZikulaMailerModule','html','b:0;'),(173,'ZikulaMailerModule','wordwrap','i:50;'),(174,'ZikulaMailerModule','enableLogging','b:0;'),(175,'ZikulaSearchModule','itemsperpage','i:10;'),(176,'ZikulaSearchModule','limitsummary','i:255;'),(177,'ZikulaSearchModule','opensearch_enabled','b:1;'),(178,'ZikulaSearchModule','opensearch_adult_content','b:0;'),(179,'ZConfig','system_identifier','s:32:\"978716568757ad05ed5b039355194245\";'),(180,'systemplugin.imagine','version','s:5:\"0.6.2\";'),(181,'systemplugin.imagine','thumb_dir','s:20:\"systemplugin.imagine\";'),(182,'systemplugin.imagine','thumb_auto_cleanup','b:0;'),(183,'systemplugin.imagine','thumb_auto_cleanup_period','s:3:\"P1D\";'),(184,'systemplugin.imagine','presets','a:1:{s:7:\"default\";C:27:\"SystemPlugin_Imagine_Preset\":266:{x:i:2;a:8:{s:5:\"width\";i:100;s:6:\"height\";i:100;s:4:\"mode\";s:5:\"inset\";s:9:\"extension\";N;s:7:\"options\";a:2:{s:12:\"jpeg_quality\";i:75;s:21:\"png_compression_level\";i:7;}s:8:\"__module\";N;s:9:\"__imagine\";N;s:16:\"__transformation\";N;};m:a:1:{s:7:\"\0*\0name\";s:7:\"default\";}}}');
/*!40000 ALTER TABLE `module_vars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `displayname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `directory` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `version` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `capabilities` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `state` int(11) NOT NULL,
  `securityschema` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `core_min` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `core_max` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,'ZikulaExtensionsModule',3,'Extensions','extensions','Manage your modules and plugins.','Zikula/Module/ExtensionsModule','3.7.12','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:24:\"ZikulaExtensionsModule::\";s:2:\"::\";}','1.4.0',''),(2,'ZikulaAdminModule',3,'Administration panel','adminpanel','Backend administration interface.','Zikula/Module/AdminModule','1.9.1','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:19:\"ZikulaAdminModule::\";s:38:\"Admin Category name::Admin Category ID\";}','1.4.0',''),(3,'ZikulaBlocksModule',3,'Blocks','blocks','Block administration module.','Zikula/Module/BlocksModule','3.9.1','a:3:{s:15:\"hook_subscriber\";a:1:{s:7:\"enabled\";b:1;}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:11:{s:20:\"ZikulaBlocksModule::\";s:30:\"Block key:Block title:Block ID\";s:28:\"ZikulaBlocksModule::position\";s:26:\"Position name::Position ID\";s:23:\"Menutree:menutreeblock:\";s:26:\"Block ID:Link Name:Link ID\";s:19:\"ExtendedMenublock::\";s:17:\"Block ID:Link ID:\";s:15:\"fincludeblock::\";s:13:\"Block title::\";s:11:\"HTMLblock::\";s:13:\"Block title::\";s:15:\"Languageblock::\";s:13:\"Block title::\";s:11:\"Menublock::\";s:22:\"Block title:Link name:\";s:16:\"PendingContent::\";s:13:\"Block title::\";s:11:\"Textblock::\";s:13:\"Block title::\";s:11:\"xsltblock::\";s:13:\"Block title::\";}','1.4.0',''),(4,'ZikulaCategoriesModule',3,'Categories','categories','Category administration.','Zikula/Module/CategoriesModule','1.2.2','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:2:{s:24:\"ZikulaCategoriesModule::\";s:2:\"::\";s:32:\"ZikulaCategoriesModule::Category\";s:40:\"Category ID:Category Path:Category IPath\";}','1.4.0',''),(5,'ZikulaGroupsModule',3,'Groups','groups','User group administration module.','Zikula/Module/GroupsModule','2.3.2','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:20:\"ZikulaGroupsModule::\";s:10:\"Group ID::\";}','1.4.0',''),(6,'ZikulaMailerModule',3,'Mailer Module','mailer','Mailer module, provides mail API and mail setting administration.','Zikula/Module/MailerModule','1.4.2','a:2:{s:15:\"hook_subscriber\";a:1:{s:7:\"enabled\";b:1;}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:20:\"ZikulaMailerModule::\";s:2:\"::\";}','1.4.0',''),(7,'ZikulaPageLockModule',3,'Page lock','pagelock','Provides the ability to lock pages when they are in use, for content and access control.','Zikula/Module/PageLockModule','1.1.1','a:0:{}',1,'a:1:{s:22:\"ZikulaPageLockModule::\";s:2:\"::\";}','1.4.0',''),(8,'ZikulaPermissionsModule',3,'Permissions','permissions','User permissions manager.','Zikula/Module/PermissionsModule','1.1.1','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:25:\"ZikulaPermissionsModule::\";s:2:\"::\";}','1.4.0',''),(9,'ZikulaSearchModule',3,'Site search','search','Site search module.','Zikula/Module/SearchModule','1.5.4','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:20:\"ZikulaSearchModule::\";s:13:\"Module name::\";}','1.4.0',''),(10,'ZikulaSecurityCenterModule',3,'Security Center','securitycenter','Manage site security and settings.','Zikula/Module/SecurityCenterModule','1.4.4','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:28:\"ZikulaSecurityCenterModule::\";s:2:\"::\";}','1.4.0',''),(11,'ZikulaSettingsModule',3,'General settings','settings','General site configuration interface.','Zikula/Module/SettingsModule','2.9.10','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:22:\"ZikulaSettingsModule::\";s:2:\"::\";}','1.4.0',''),(12,'ZikulaThemeModule',3,'Themes','theme','Themes module to manage site layout, render and cache settings.','Zikula/Module/ThemeModule','3.4.3','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:2:{s:19:\"ZikulaThemeModule::\";s:12:\"Theme name::\";s:30:\"ZikulaThemeModule::ThemeChange\";s:2:\"::\";}','1.4.0',''),(13,'ZikulaUsersModule',3,'Users','users','Provides an interface for configuring and administering registered user accounts. Incorporates all needed functionality, but can work in close unison with the third party profile module configured in the general settings of the site.','Zikula/Module/UsersModule','2.2.5','a:5:{s:14:\"authentication\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:15:\"hook_subscriber\";a:1:{s:7:\"enabled\";b:1;}s:10:\"searchable\";a:1:{s:5:\"class\";s:45:\"Zikula\\Module\\UsersModule\\Helper\\SearchHelper\";}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:2:{s:19:\"ZikulaUsersModule::\";s:14:\"Uname::User ID\";s:28:\"ZikulaUsersModule::MailUsers\";s:2:\"::\";}','1.4.0',''),(14,'ZikulaRoutesModule',3,'Routes','routes','Routes module generated by ModuleStudio 0.7.0.','ZikulaRoutesModule','1.0.0','a:2:{s:15:\"hook_subscriber\";a:1:{s:7:\"enabled\";b:1;}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:3:{s:20:\"ZikulaRoutesModule::\";s:2:\"::\";s:24:\"ZikulaRoutesModule::Ajax\";s:2:\"::\";s:25:\"ZikulaRoutesModule:Route:\";s:10:\"Route ID::\";}','1.4.0','1.4.99'),(15,'ZikulaLegalModule',2,'Legal','legal','Provides an interface for managing the site\'s legal documents.','zikula/legal-module','2.1.0','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',1,'a:8:{s:19:\"ZikulaLegalModule::\";s:2:\"::\";s:30:\"ZikulaLegalModule::legalnotice\";s:2:\"::\";s:29:\"ZikulaLegalModule::termsofuse\";s:2:\"::\";s:32:\"ZikulaLegalModule::privacypolicy\";s:2:\"::\";s:28:\"ZikulaLegalModule::agepolicy\";s:2:\"::\";s:41:\"ZikulaLegalModule::accessibilitystatement\";s:2:\"::\";s:42:\"ZikulaLegalModule::cancellationrightpolicy\";s:2:\"::\";s:34:\"ZikulaLegalModule::tradeconditions\";s:2:\"::\";}','1.4.0','1.4.99'),(16,'ZikulaProfileModule',2,'Profile','profile','Provides a personal account control panel for each registered user, an interface to administer the personal information items displayed within it, and a registered users list functionality. Works in close unison with the \'Users\' module.','zikula/profile-module','2.0.0','a:3:{s:7:\"profile\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',1,'a:6:{s:21:\"ZikulaProfileModule::\";s:2:\"::\";s:25:\"ZikulaProfileModule::view\";s:2:\"::\";s:25:\"ZikulaProfileModule::item\";s:56:\"DynamicUserData PropertyName::DynamicUserData PropertyID\";s:28:\"ZikulaProfileModule:Members:\";s:2:\"::\";s:34:\"ZikulaProfileModule:Members:recent\";s:2:\"::\";s:34:\"ZikulaProfileModule:Members:online\";s:2:\"::\";}','1.4.0','1.4.99');
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objectdata_attributes`
--

DROP TABLE IF EXISTS `objectdata_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objectdata_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `object_id` int(11) NOT NULL,
  `object_type` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  `obj_status` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `cr_date` datetime NOT NULL,
  `cr_uid` int(11) NOT NULL,
  `lu_date` datetime NOT NULL,
  `lu_uid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `object_type` (`object_type`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objectdata_attributes`
--

LOCK TABLES `objectdata_attributes` WRITE;
/*!40000 ALTER TABLE `objectdata_attributes` DISABLE KEYS */;
/*!40000 ALTER TABLE `objectdata_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objectdata_log`
--

DROP TABLE IF EXISTS `objectdata_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objectdata_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object_type` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `object_id` int(11) NOT NULL,
  `op` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `diff` longtext COLLATE utf8_unicode_ci,
  `obj_status` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `cr_date` datetime NOT NULL,
  `cr_uid` int(11) NOT NULL,
  `lu_date` datetime NOT NULL,
  `lu_uid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objectdata_log`
--

LOCK TABLES `objectdata_log` WRITE;
/*!40000 ALTER TABLE `objectdata_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `objectdata_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objectdata_meta`
--

DROP TABLE IF EXISTS `objectdata_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objectdata_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `tablename` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `idcolumn` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `obj_id` int(11) NOT NULL,
  `permissions` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_title` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_author` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_keywords` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_publisher` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_contributor` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_startdate` datetime DEFAULT NULL,
  `dc_enddate` datetime DEFAULT NULL,
  `dc_type` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_format` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_uri` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_source` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_language` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_relation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_coverage` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_entity` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dc_extra` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `obj_status` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `cr_date` datetime NOT NULL,
  `cr_uid` int(11) NOT NULL,
  `lu_date` datetime NOT NULL,
  `lu_uid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objectdata_meta`
--

LOCK TABLES `objectdata_meta` WRITE;
/*!40000 ALTER TABLE `objectdata_meta` DISABLE KEYS */;
/*!40000 ALTER TABLE `objectdata_meta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_intrusion`
--

DROP TABLE IF EXISTS `sc_intrusion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_intrusion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `tag` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  `page` longtext COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `impact` int(11) NOT NULL,
  `filters` longtext COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8595CE46539B0606` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_intrusion`
--

LOCK TABLES `sc_intrusion` WRITE;
/*!40000 ALTER TABLE `sc_intrusion` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_intrusion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_result`
--

DROP TABLE IF EXISTS `search_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `text` longtext COLLATE utf8_unicode_ci,
  `module` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `extra` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `found` datetime DEFAULT NULL,
  `sesid` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:object)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_result`
--

LOCK TABLES `search_result` WRITE;
/*!40000 ALTER TABLE `search_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `search_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_stat`
--

DROP TABLE IF EXISTS `search_stat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_stat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `search` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `scount` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_stat`
--

LOCK TABLES `search_stat` WRITE;
/*!40000 ALTER TABLE `search_stat` DISABLE KEYS */;
/*!40000 ALTER TABLE `search_stat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session_info`
--

DROP TABLE IF EXISTS `session_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_info` (
  `sessid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ipaddr` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `lastused` datetime NOT NULL,
  `uid` int(11) NOT NULL,
  `remember` smallint(6) NOT NULL,
  `vars` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`sessid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session_info`
--

LOCK TABLES `session_info` WRITE;
/*!40000 ALTER TABLE `session_info` DISABLE KEYS */;
INSERT INTO `session_info` VALUES ('90cdcb836d37bcefb41ddd051d991eb5','localhost','2016-08-11 19:11:38',0,0,'_sf2_attributes|a:2:{s:7:\"_tokens\";a:1:{s:23:\"57ad062acd10d4.39984164\";a:2:{s:5:\"token\";s:92:\"NTdhZDA2MmFjZDEwZDQuMzk5ODQxNjQ6ZjdhNDI4M2E3N2I3YzBkYjJjMjM3ZmY0ZjYwMDliZTI6MTQ3MDk1NzA5OA==\";s:4:\"time\";i:1470957098;}}s:18:\"sessioncsrftokenid\";s:23:\"57ad062acd10d4.39984164\";}_sf2_flashes|a:1:{s:7:\"success\";a:1:{i:0;s:56:\"Congratulations! Zikula has been successfully installed.\";}}_sf2_meta|a:3:{s:1:\"u\";i:1470957099;s:1:\"c\";i:1470957096;s:1:\"l\";s:1:\"0\";}'),('a534159917f59cfb0a9259e9ed9959af','localhost','2016-08-11 19:12:36',0,0,'_sf2_attributes|a:0:{}_sf2_flashes|a:0:{}_sf2_meta|a:3:{s:1:\"u\";i:1470957157;s:1:\"c\";i:1470957100;s:1:\"l\";s:1:\"0\";}'),('fe53025bfeaf58be7b1486f639795306','localhost','2016-08-11 19:12:35',0,0,'_sf2_attributes|a:2:{s:7:\"_tokens\";a:1:{s:23:\"57ad0662eb4306.30076868\";a:2:{s:5:\"token\";s:92:\"NTdhZDA2NjJlYjQzMDYuMzAwNzY4Njg6MGIxNTJmMjE5YmFhNzdkNDdlNDgwYzExYmJiNDc3MzI6MTQ3MDk1NzE1NA==\";s:4:\"time\";i:1470957154;}}s:18:\"sessioncsrftokenid\";s:23:\"57ad0662eb4306.30076868\";}_sf2_flashes|a:0:{}_sf2_meta|a:3:{s:1:\"u\";i:1470957155;s:1:\"c\";i:1470957154;s:1:\"l\";s:1:\"0\";}');
/*!40000 ALTER TABLE `session_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `themes`
--

DROP TABLE IF EXISTS `themes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `type` smallint(6) NOT NULL,
  `displayname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `directory` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `version` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `contact` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin` smallint(6) NOT NULL,
  `user` smallint(6) NOT NULL,
  `system` smallint(6) NOT NULL,
  `state` smallint(6) NOT NULL,
  `xhtml` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `themes`
--

LOCK TABLES `themes` WRITE;
/*!40000 ALTER TABLE `themes` DISABLE KEYS */;
INSERT INTO `themes` VALUES (1,'ZikulaSeaBreezeTheme',3,'SeaBreeze','The SeaBreeze theme is a browser-oriented theme.','SeaBreezeTheme','3.2.0','3',0,1,0,1,1),(2,'ZikulaAndreas08Theme',3,'Andreas08','Based on the theme Andreas08 by Andreas Viklund and extended for Zikula with the CSS Framework \'fluid960gs\'.','Zikula/Theme/Andreas08Theme','2.0.0','3',1,1,0,1,1),(3,'ZikulaAtomTheme',3,'Atom','The Atom theme is an auxiliary theme specially designed for rendering pages in Atom mark-up.','Zikula/Theme/AtomTheme','1.0.0','3',0,0,1,1,1),(4,'ZikulaBootstrapTheme',3,'Bootstrap','Bootstrap testing version. Based on Andreas 08.','Zikula/Theme/BootstrapTheme','0.0.1','3',1,1,0,1,1),(5,'ZikulaPrinterTheme',3,'Printer','The Printer theme is an auxiliary theme designed specially for outputting pages in a printer-friendly format.','Zikula/Theme/PrinterTheme','2.0.0','3',0,0,1,1,1),(6,'ZikulaRssTheme',3,'RSS','The RSS theme is an auxiliary theme designed specially for outputting pages as an RSS feed.','Zikula/Theme/RssTheme','0','3',0,0,1,1,1);
/*!40000 ALTER TABLE `themes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userblocks`
--

DROP TABLE IF EXISTS `userblocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userblocks` (
  `uid` int(11) NOT NULL,
  `bid` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `last_update` datetime NOT NULL,
  PRIMARY KEY (`uid`,`bid`),
  KEY `uid_bid_idx` (`uid`,`bid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userblocks`
--

LOCK TABLES `userblocks` WRITE;
/*!40000 ALTER TABLE `userblocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `userblocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `uname` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `pass` varchar(138) COLLATE utf8_unicode_ci NOT NULL,
  `passreminder` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `activated` smallint(6) NOT NULL,
  `approved_date` datetime NOT NULL,
  `approved_by` int(11) NOT NULL,
  `user_regdate` datetime NOT NULL,
  `lastlogin` datetime NOT NULL,
  `theme` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ublockon` smallint(6) NOT NULL,
  `ublock` longtext COLLATE utf8_unicode_ci NOT NULL,
  `tz` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `locale` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `uname` (`uname`),
  KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'guest','','','',1,'1970-01-01 00:00:00',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','',0,'','',''),(2,'admin','admin@example.com','8$WJFCa$6f5bd640baa9721e1485d1c8f0299521f7cea5ed16acf9e952676d4d31f76a73','',1,'2016-08-11 23:10:24',2,'2016-08-11 23:10:36','2016-08-11 23:12:21','',0,'','','');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_attributes`
--

DROP TABLE IF EXISTS `users_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_attributes` (
  `user_id` int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`user_id`,`name`),
  KEY `IDX_E6F031E4A76ED395` (`user_id`),
  CONSTRAINT `FK_E6F031E4A76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_attributes`
--

LOCK TABLES `users_attributes` WRITE;
/*!40000 ALTER TABLE `users_attributes` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_verifychg`
--

DROP TABLE IF EXISTS `users_verifychg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_verifychg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `changetype` smallint(6) NOT NULL,
  `uid` int(11) NOT NULL,
  `newemail` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `verifycode` varchar(138) COLLATE utf8_unicode_ci NOT NULL,
  `created_dt` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_verifychg`
--

LOCK TABLES `users_verifychg` WRITE;
/*!40000 ALTER TABLE `users_verifychg` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_verifychg` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflows`
--

DROP TABLE IF EXISTS `workflows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metaid` int(11) NOT NULL,
  `module` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `schemaname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` smallint(6) NOT NULL,
  `obj_table` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `obj_idcolumn` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `obj_id` int(11) NOT NULL,
  `busy` int(11) NOT NULL,
  `debug` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflows`
--

LOCK TABLES `workflows` WRITE;
/*!40000 ALTER TABLE `workflows` DISABLE KEYS */;
INSERT INTO `workflows` VALUES (1,0,'ZikulaRoutesModule','none','approved',1,'route','id',1,0,NULL),(2,0,'ZikulaRoutesModule','none','approved',1,'route','id',2,0,NULL),(3,0,'ZikulaRoutesModule','none','approved',1,'route','id',3,0,NULL),(4,0,'ZikulaRoutesModule','none','approved',1,'route','id',4,0,NULL),(5,0,'ZikulaRoutesModule','none','approved',1,'route','id',5,0,NULL),(6,0,'ZikulaRoutesModule','none','approved',1,'route','id',6,0,NULL),(7,0,'ZikulaRoutesModule','none','approved',1,'route','id',7,0,NULL),(8,0,'ZikulaRoutesModule','none','approved',1,'route','id',8,0,NULL),(9,0,'ZikulaRoutesModule','none','approved',1,'route','id',9,0,NULL),(10,0,'ZikulaRoutesModule','none','approved',1,'route','id',10,0,NULL),(11,0,'ZikulaRoutesModule','none','approved',1,'route','id',11,0,NULL),(12,0,'ZikulaRoutesModule','none','approved',1,'route','id',12,0,NULL),(13,0,'ZikulaRoutesModule','none','approved',1,'route','id',13,0,NULL),(14,0,'ZikulaRoutesModule','none','approved',1,'route','id',14,0,NULL),(15,0,'ZikulaRoutesModule','none','approved',1,'route','id',15,0,NULL),(16,0,'ZikulaRoutesModule','none','approved',1,'route','id',16,0,NULL),(17,0,'ZikulaRoutesModule','none','approved',1,'route','id',17,0,NULL),(18,0,'ZikulaRoutesModule','none','approved',1,'route','id',18,0,NULL),(19,0,'ZikulaRoutesModule','none','approved',1,'route','id',19,0,NULL),(20,0,'ZikulaRoutesModule','none','approved',1,'route','id',20,0,NULL),(21,0,'ZikulaRoutesModule','none','approved',1,'route','id',21,0,NULL),(22,0,'ZikulaRoutesModule','none','approved',1,'route','id',22,0,NULL),(23,0,'ZikulaRoutesModule','none','approved',1,'route','id',23,0,NULL),(24,0,'ZikulaRoutesModule','none','approved',1,'route','id',24,0,NULL),(25,0,'ZikulaRoutesModule','none','approved',1,'route','id',25,0,NULL),(26,0,'ZikulaRoutesModule','none','approved',1,'route','id',26,0,NULL),(27,0,'ZikulaRoutesModule','none','approved',1,'route','id',27,0,NULL),(28,0,'ZikulaRoutesModule','none','approved',1,'route','id',28,0,NULL),(29,0,'ZikulaRoutesModule','none','approved',1,'route','id',29,0,NULL),(30,0,'ZikulaRoutesModule','none','approved',1,'route','id',30,0,NULL),(31,0,'ZikulaRoutesModule','none','approved',1,'route','id',31,0,NULL),(32,0,'ZikulaRoutesModule','none','approved',1,'route','id',32,0,NULL),(33,0,'ZikulaRoutesModule','none','approved',1,'route','id',33,0,NULL),(34,0,'ZikulaRoutesModule','none','approved',1,'route','id',34,0,NULL),(35,0,'ZikulaRoutesModule','none','approved',1,'route','id',35,0,NULL),(36,0,'ZikulaRoutesModule','none','approved',1,'route','id',36,0,NULL),(37,0,'ZikulaRoutesModule','none','approved',1,'route','id',37,0,NULL),(38,0,'ZikulaRoutesModule','none','approved',1,'route','id',38,0,NULL),(39,0,'ZikulaRoutesModule','none','approved',1,'route','id',39,0,NULL),(40,0,'ZikulaRoutesModule','none','approved',1,'route','id',40,0,NULL),(41,0,'ZikulaRoutesModule','none','approved',1,'route','id',41,0,NULL),(42,0,'ZikulaRoutesModule','none','approved',1,'route','id',42,0,NULL),(43,0,'ZikulaRoutesModule','none','approved',1,'route','id',43,0,NULL),(44,0,'ZikulaRoutesModule','none','approved',1,'route','id',44,0,NULL),(45,0,'ZikulaRoutesModule','none','approved',1,'route','id',45,0,NULL),(46,0,'ZikulaRoutesModule','none','approved',1,'route','id',46,0,NULL),(47,0,'ZikulaRoutesModule','none','approved',1,'route','id',47,0,NULL),(48,0,'ZikulaRoutesModule','none','approved',1,'route','id',48,0,NULL),(49,0,'ZikulaRoutesModule','none','approved',1,'route','id',49,0,NULL),(50,0,'ZikulaRoutesModule','none','approved',1,'route','id',50,0,NULL),(51,0,'ZikulaRoutesModule','none','approved',1,'route','id',51,0,NULL),(52,0,'ZikulaRoutesModule','none','approved',1,'route','id',52,0,NULL),(53,0,'ZikulaRoutesModule','none','approved',1,'route','id',53,0,NULL),(54,0,'ZikulaRoutesModule','none','approved',1,'route','id',54,0,NULL),(55,0,'ZikulaRoutesModule','none','approved',1,'route','id',55,0,NULL),(56,0,'ZikulaRoutesModule','none','approved',1,'route','id',56,0,NULL),(57,0,'ZikulaRoutesModule','none','approved',1,'route','id',57,0,NULL),(58,0,'ZikulaRoutesModule','none','approved',1,'route','id',58,0,NULL),(59,0,'ZikulaRoutesModule','none','approved',1,'route','id',59,0,NULL),(60,0,'ZikulaRoutesModule','none','approved',1,'route','id',60,0,NULL),(61,0,'ZikulaRoutesModule','none','approved',1,'route','id',61,0,NULL),(62,0,'ZikulaRoutesModule','none','approved',1,'route','id',62,0,NULL),(63,0,'ZikulaRoutesModule','none','approved',1,'route','id',63,0,NULL),(64,0,'ZikulaRoutesModule','none','approved',1,'route','id',64,0,NULL),(65,0,'ZikulaRoutesModule','none','approved',1,'route','id',65,0,NULL),(66,0,'ZikulaRoutesModule','none','approved',1,'route','id',66,0,NULL),(67,0,'ZikulaRoutesModule','none','approved',1,'route','id',67,0,NULL),(68,0,'ZikulaRoutesModule','none','approved',1,'route','id',68,0,NULL),(69,0,'ZikulaRoutesModule','none','approved',1,'route','id',69,0,NULL),(70,0,'ZikulaRoutesModule','none','approved',1,'route','id',70,0,NULL),(71,0,'ZikulaRoutesModule','none','approved',1,'route','id',71,0,NULL),(72,0,'ZikulaRoutesModule','none','approved',1,'route','id',72,0,NULL),(73,0,'ZikulaRoutesModule','none','approved',1,'route','id',73,0,NULL),(74,0,'ZikulaRoutesModule','none','approved',1,'route','id',74,0,NULL),(75,0,'ZikulaRoutesModule','none','approved',1,'route','id',75,0,NULL),(76,0,'ZikulaRoutesModule','none','approved',1,'route','id',76,0,NULL),(77,0,'ZikulaRoutesModule','none','approved',1,'route','id',77,0,NULL),(78,0,'ZikulaRoutesModule','none','approved',1,'route','id',78,0,NULL),(79,0,'ZikulaRoutesModule','none','approved',1,'route','id',79,0,NULL),(80,0,'ZikulaRoutesModule','none','approved',1,'route','id',80,0,NULL),(81,0,'ZikulaRoutesModule','none','approved',1,'route','id',81,0,NULL),(82,0,'ZikulaRoutesModule','none','approved',1,'route','id',82,0,NULL),(83,0,'ZikulaRoutesModule','none','approved',1,'route','id',83,0,NULL),(84,0,'ZikulaRoutesModule','none','approved',1,'route','id',84,0,NULL),(85,0,'ZikulaRoutesModule','none','approved',1,'route','id',85,0,NULL),(86,0,'ZikulaRoutesModule','none','approved',1,'route','id',86,0,NULL),(87,0,'ZikulaRoutesModule','none','approved',1,'route','id',87,0,NULL),(88,0,'ZikulaRoutesModule','none','approved',1,'route','id',88,0,NULL),(89,0,'ZikulaRoutesModule','none','approved',1,'route','id',89,0,NULL),(90,0,'ZikulaRoutesModule','none','approved',1,'route','id',90,0,NULL),(91,0,'ZikulaRoutesModule','none','approved',1,'route','id',91,0,NULL),(92,0,'ZikulaRoutesModule','none','approved',1,'route','id',92,0,NULL),(93,0,'ZikulaRoutesModule','none','approved',1,'route','id',93,0,NULL),(94,0,'ZikulaRoutesModule','none','approved',1,'route','id',94,0,NULL),(95,0,'ZikulaRoutesModule','none','approved',1,'route','id',95,0,NULL),(96,0,'ZikulaRoutesModule','none','approved',1,'route','id',96,0,NULL),(97,0,'ZikulaRoutesModule','none','approved',1,'route','id',97,0,NULL),(98,0,'ZikulaRoutesModule','none','approved',1,'route','id',98,0,NULL),(99,0,'ZikulaRoutesModule','none','approved',1,'route','id',99,0,NULL),(100,0,'ZikulaRoutesModule','none','approved',1,'route','id',100,0,NULL),(101,0,'ZikulaRoutesModule','none','approved',1,'route','id',101,0,NULL),(102,0,'ZikulaRoutesModule','none','approved',1,'route','id',102,0,NULL),(103,0,'ZikulaRoutesModule','none','approved',1,'route','id',103,0,NULL),(104,0,'ZikulaRoutesModule','none','approved',1,'route','id',104,0,NULL),(105,0,'ZikulaRoutesModule','none','approved',1,'route','id',105,0,NULL),(106,0,'ZikulaRoutesModule','none','approved',1,'route','id',106,0,NULL),(107,0,'ZikulaRoutesModule','none','approved',1,'route','id',107,0,NULL),(108,0,'ZikulaRoutesModule','none','approved',1,'route','id',108,0,NULL),(109,0,'ZikulaRoutesModule','none','approved',1,'route','id',109,0,NULL),(110,0,'ZikulaRoutesModule','none','approved',1,'route','id',110,0,NULL),(111,0,'ZikulaRoutesModule','none','approved',1,'route','id',111,0,NULL),(112,0,'ZikulaRoutesModule','none','approved',1,'route','id',112,0,NULL),(113,0,'ZikulaRoutesModule','none','approved',1,'route','id',113,0,NULL),(114,0,'ZikulaRoutesModule','none','approved',1,'route','id',114,0,NULL),(115,0,'ZikulaRoutesModule','none','approved',1,'route','id',115,0,NULL),(116,0,'ZikulaRoutesModule','none','approved',1,'route','id',116,0,NULL),(117,0,'ZikulaRoutesModule','none','approved',1,'route','id',117,0,NULL),(118,0,'ZikulaRoutesModule','none','approved',1,'route','id',118,0,NULL),(119,0,'ZikulaRoutesModule','none','approved',1,'route','id',119,0,NULL),(120,0,'ZikulaRoutesModule','none','approved',1,'route','id',120,0,NULL),(121,0,'ZikulaRoutesModule','none','approved',1,'route','id',121,0,NULL),(122,0,'ZikulaRoutesModule','none','approved',1,'route','id',122,0,NULL),(123,0,'ZikulaRoutesModule','none','approved',1,'route','id',123,0,NULL),(124,0,'ZikulaRoutesModule','none','approved',1,'route','id',124,0,NULL),(125,0,'ZikulaRoutesModule','none','approved',1,'route','id',125,0,NULL),(126,0,'ZikulaRoutesModule','none','approved',1,'route','id',126,0,NULL),(127,0,'ZikulaRoutesModule','none','approved',1,'route','id',127,0,NULL),(128,0,'ZikulaRoutesModule','none','approved',1,'route','id',128,0,NULL),(129,0,'ZikulaRoutesModule','none','approved',1,'route','id',129,0,NULL),(130,0,'ZikulaRoutesModule','none','approved',1,'route','id',130,0,NULL),(131,0,'ZikulaRoutesModule','none','approved',1,'route','id',131,0,NULL),(132,0,'ZikulaRoutesModule','none','approved',1,'route','id',132,0,NULL),(133,0,'ZikulaRoutesModule','none','approved',1,'route','id',133,0,NULL),(134,0,'ZikulaRoutesModule','none','approved',1,'route','id',134,0,NULL),(135,0,'ZikulaRoutesModule','none','approved',1,'route','id',135,0,NULL),(136,0,'ZikulaRoutesModule','none','approved',1,'route','id',136,0,NULL),(137,0,'ZikulaRoutesModule','none','approved',1,'route','id',137,0,NULL),(138,0,'ZikulaRoutesModule','none','approved',1,'route','id',138,0,NULL),(139,0,'ZikulaRoutesModule','none','approved',1,'route','id',139,0,NULL),(140,0,'ZikulaRoutesModule','none','approved',1,'route','id',140,0,NULL),(141,0,'ZikulaRoutesModule','none','approved',1,'route','id',141,0,NULL),(142,0,'ZikulaRoutesModule','none','approved',1,'route','id',142,0,NULL),(143,0,'ZikulaRoutesModule','none','approved',1,'route','id',143,0,NULL),(144,0,'ZikulaRoutesModule','none','approved',1,'route','id',144,0,NULL),(145,0,'ZikulaRoutesModule','none','approved',1,'route','id',145,0,NULL),(146,0,'ZikulaRoutesModule','none','approved',1,'route','id',146,0,NULL),(147,0,'ZikulaRoutesModule','none','approved',1,'route','id',147,0,NULL),(148,0,'ZikulaRoutesModule','none','approved',1,'route','id',148,0,NULL),(149,0,'ZikulaRoutesModule','none','approved',1,'route','id',149,0,NULL),(150,0,'ZikulaRoutesModule','none','approved',1,'route','id',150,0,NULL),(151,0,'ZikulaRoutesModule','none','approved',1,'route','id',151,0,NULL),(152,0,'ZikulaRoutesModule','none','approved',1,'route','id',152,0,NULL),(153,0,'ZikulaRoutesModule','none','approved',1,'route','id',153,0,NULL),(154,0,'ZikulaRoutesModule','none','approved',1,'route','id',154,0,NULL),(155,0,'ZikulaRoutesModule','none','approved',1,'route','id',155,0,NULL),(156,0,'ZikulaRoutesModule','none','approved',1,'route','id',156,0,NULL),(157,0,'ZikulaRoutesModule','none','approved',1,'route','id',157,0,NULL),(158,0,'ZikulaRoutesModule','none','approved',1,'route','id',158,0,NULL),(159,0,'ZikulaRoutesModule','none','approved',1,'route','id',159,0,NULL),(160,0,'ZikulaRoutesModule','none','approved',1,'route','id',160,0,NULL),(161,0,'ZikulaRoutesModule','none','approved',1,'route','id',161,0,NULL),(162,0,'ZikulaRoutesModule','none','approved',1,'route','id',162,0,NULL),(163,0,'ZikulaRoutesModule','none','approved',1,'route','id',163,0,NULL),(164,0,'ZikulaRoutesModule','none','approved',1,'route','id',164,0,NULL),(165,0,'ZikulaRoutesModule','none','approved',1,'route','id',165,0,NULL),(166,0,'ZikulaRoutesModule','none','approved',1,'route','id',166,0,NULL),(167,0,'ZikulaRoutesModule','none','approved',1,'route','id',167,0,NULL),(168,0,'ZikulaRoutesModule','none','approved',1,'route','id',168,0,NULL),(169,0,'ZikulaRoutesModule','none','approved',1,'route','id',169,0,NULL),(170,0,'ZikulaRoutesModule','none','approved',1,'route','id',170,0,NULL),(171,0,'ZikulaRoutesModule','none','approved',1,'route','id',171,0,NULL),(172,0,'ZikulaRoutesModule','none','approved',1,'route','id',172,0,NULL),(173,0,'ZikulaRoutesModule','none','approved',1,'route','id',173,0,NULL),(174,0,'ZikulaRoutesModule','none','approved',1,'route','id',174,0,NULL),(175,0,'ZikulaRoutesModule','none','approved',1,'route','id',175,0,NULL),(176,0,'ZikulaRoutesModule','none','approved',1,'route','id',176,0,NULL),(177,0,'ZikulaRoutesModule','none','approved',1,'route','id',177,0,NULL),(178,0,'ZikulaRoutesModule','none','approved',1,'route','id',178,0,NULL),(179,0,'ZikulaRoutesModule','none','approved',1,'route','id',179,0,NULL),(180,0,'ZikulaRoutesModule','none','approved',1,'route','id',180,0,NULL),(181,0,'ZikulaRoutesModule','none','approved',1,'route','id',181,0,NULL),(182,0,'ZikulaRoutesModule','none','approved',1,'route','id',182,0,NULL),(183,0,'ZikulaRoutesModule','none','approved',1,'route','id',183,0,NULL),(184,0,'ZikulaRoutesModule','none','approved',1,'route','id',184,0,NULL),(185,0,'ZikulaRoutesModule','none','approved',1,'route','id',185,0,NULL),(186,0,'ZikulaRoutesModule','none','approved',1,'route','id',186,0,NULL),(187,0,'ZikulaRoutesModule','none','approved',1,'route','id',187,0,NULL),(188,0,'ZikulaRoutesModule','none','approved',1,'route','id',188,0,NULL),(189,0,'ZikulaRoutesModule','none','approved',1,'route','id',189,0,NULL),(190,0,'ZikulaRoutesModule','none','approved',1,'route','id',190,0,NULL),(191,0,'ZikulaRoutesModule','none','approved',1,'route','id',191,0,NULL),(192,0,'ZikulaRoutesModule','none','approved',1,'route','id',192,0,NULL),(193,0,'ZikulaRoutesModule','none','approved',1,'route','id',193,0,NULL),(194,0,'ZikulaRoutesModule','none','approved',1,'route','id',194,0,NULL),(195,0,'ZikulaRoutesModule','none','approved',1,'route','id',195,0,NULL),(196,0,'ZikulaRoutesModule','none','approved',1,'route','id',196,0,NULL),(197,0,'ZikulaRoutesModule','none','approved',1,'route','id',197,0,NULL),(198,0,'ZikulaRoutesModule','none','approved',1,'route','id',198,0,NULL),(199,0,'ZikulaRoutesModule','none','approved',1,'route','id',199,0,NULL),(200,0,'ZikulaRoutesModule','none','approved',1,'route','id',200,0,NULL),(201,0,'ZikulaRoutesModule','none','approved',1,'route','id',201,0,NULL),(202,0,'ZikulaRoutesModule','none','approved',1,'route','id',202,0,NULL),(203,0,'ZikulaRoutesModule','none','approved',1,'route','id',203,0,NULL),(204,0,'ZikulaRoutesModule','none','approved',1,'route','id',204,0,NULL),(205,0,'ZikulaRoutesModule','none','approved',1,'route','id',205,0,NULL),(206,0,'ZikulaRoutesModule','none','approved',1,'route','id',206,0,NULL),(207,0,'ZikulaRoutesModule','none','approved',1,'route','id',207,0,NULL),(208,0,'ZikulaRoutesModule','none','approved',1,'route','id',208,0,NULL),(209,0,'ZikulaRoutesModule','none','approved',1,'route','id',209,0,NULL),(210,0,'ZikulaRoutesModule','none','approved',1,'route','id',210,0,NULL),(211,0,'ZikulaRoutesModule','none','approved',1,'route','id',211,0,NULL),(212,0,'ZikulaRoutesModule','none','approved',1,'route','id',212,0,NULL),(213,0,'ZikulaRoutesModule','none','approved',1,'route','id',213,0,NULL),(214,0,'ZikulaRoutesModule','none','approved',1,'route','id',214,0,NULL),(215,0,'ZikulaRoutesModule','none','approved',1,'route','id',215,0,NULL),(216,0,'ZikulaRoutesModule','none','approved',1,'route','id',216,0,NULL),(217,0,'ZikulaRoutesModule','none','approved',1,'route','id',217,0,NULL),(218,0,'ZikulaRoutesModule','none','approved',1,'route','id',218,0,NULL),(219,0,'ZikulaRoutesModule','none','approved',1,'route','id',219,0,NULL),(220,0,'ZikulaRoutesModule','none','approved',1,'route','id',220,0,NULL),(221,0,'ZikulaRoutesModule','none','approved',1,'route','id',221,0,NULL),(222,0,'ZikulaRoutesModule','none','approved',1,'route','id',222,0,NULL),(223,0,'ZikulaRoutesModule','none','approved',1,'route','id',223,0,NULL),(224,0,'ZikulaRoutesModule','none','approved',1,'route','id',224,0,NULL),(225,0,'ZikulaRoutesModule','none','approved',1,'route','id',225,0,NULL),(226,0,'ZikulaRoutesModule','none','approved',1,'route','id',226,0,NULL),(227,0,'ZikulaRoutesModule','none','approved',1,'route','id',227,0,NULL),(228,0,'ZikulaRoutesModule','none','approved',1,'route','id',228,0,NULL),(229,0,'ZikulaRoutesModule','none','approved',1,'route','id',229,0,NULL),(230,0,'ZikulaRoutesModule','none','approved',1,'route','id',230,0,NULL),(231,0,'ZikulaRoutesModule','none','approved',1,'route','id',231,0,NULL),(232,0,'ZikulaRoutesModule','none','approved',1,'route','id',232,0,NULL),(233,0,'ZikulaRoutesModule','none','approved',1,'route','id',233,0,NULL),(234,0,'ZikulaRoutesModule','none','approved',1,'route','id',234,0,NULL),(235,0,'ZikulaRoutesModule','none','approved',1,'route','id',235,0,NULL),(236,0,'ZikulaRoutesModule','none','approved',1,'route','id',236,0,NULL),(237,0,'ZikulaRoutesModule','none','approved',1,'route','id',237,0,NULL),(238,0,'ZikulaRoutesModule','none','approved',1,'route','id',238,0,NULL),(239,0,'ZikulaRoutesModule','none','approved',1,'route','id',239,0,NULL),(240,0,'ZikulaRoutesModule','none','approved',1,'route','id',240,0,NULL),(241,0,'ZikulaRoutesModule','none','approved',1,'route','id',241,0,NULL),(242,0,'ZikulaRoutesModule','none','approved',1,'route','id',242,0,NULL),(243,0,'ZikulaRoutesModule','none','approved',1,'route','id',243,0,NULL),(244,0,'ZikulaRoutesModule','none','approved',1,'route','id',244,0,NULL),(245,0,'ZikulaRoutesModule','none','approved',1,'route','id',245,0,NULL),(246,0,'ZikulaRoutesModule','none','approved',1,'route','id',246,0,NULL),(247,0,'ZikulaRoutesModule','none','approved',1,'route','id',247,0,NULL),(248,0,'ZikulaRoutesModule','none','approved',1,'route','id',248,0,NULL),(249,0,'ZikulaRoutesModule','none','approved',1,'route','id',249,0,NULL),(250,0,'ZikulaRoutesModule','none','approved',1,'route','id',250,0,NULL),(251,0,'ZikulaRoutesModule','none','approved',1,'route','id',251,0,NULL),(252,0,'ZikulaRoutesModule','none','approved',1,'route','id',252,0,NULL),(253,0,'ZikulaRoutesModule','none','approved',1,'route','id',253,0,NULL),(254,0,'ZikulaRoutesModule','none','approved',1,'route','id',254,0,NULL),(255,0,'ZikulaRoutesModule','none','approved',1,'route','id',255,0,NULL);
/*!40000 ALTER TABLE `workflows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zikula_routes_route`
--

DROP TABLE IF EXISTS `zikula_routes_route`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zikula_routes_route` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflowState` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `route_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bundle` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `controller` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `route_action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `route_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `host` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `schemes` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `methods` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `route_defaults` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `requirements` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `options` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `route_condition` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `userRoute` tinyint(1) NOT NULL,
  `sort` bigint(20) NOT NULL,
  `sort_group` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdUserId` int(11) NOT NULL,
  `updatedUserId` int(11) NOT NULL,
  `createdDate` datetime NOT NULL,
  `updatedDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_516F4628BF396750` (`id`),
  KEY `workflowstateindex` (`workflowState`)
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zikula_routes_route`
--

LOCK TABLES `zikula_routes_route` WRITE;
/*!40000 ALTER TABLE `zikula_routes_route` DISABLE KEYS */;
INSERT INTO `zikula_routes_route` VALUES (1,'approved','zikulaadminmodule_admin_index','ZikulaAdminModule','admin','index','/','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:65:\"Zikula\\Module\\AdminModule\\Controller\\AdminController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,0,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(2,'approved','zikulaadminmodule_admin_view','ZikulaAdminModule','admin','view','/categories/{startnum}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:8:\"startnum\";i:0;s:11:\"_controller\";s:64:\"Zikula\\Module\\AdminModule\\Controller\\AdminController::viewAction\";}','a:2:{s:8:\"startnum\";s:3:\"\\d+\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,1,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(3,'approved','zikulaadminmodule_admin_newcat','ZikulaAdminModule','admin','newcat','/newcategory','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\AdminModule\\Controller\\AdminController::newcatAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,2,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(4,'approved','zikulaadminmodule_admin_create','ZikulaAdminModule','admin','create','/newcategory','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\AdminModule\\Controller\\AdminController::createAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,3,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(5,'approved','zikulaadminmodule_admin_modify','ZikulaAdminModule','admin','modify','/modifycategory/{cid}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\AdminModule\\Controller\\AdminController::modifyAction\";}','a:2:{s:3:\"cid\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,4,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(6,'approved','zikulaadminmodule_admin_update','ZikulaAdminModule','admin','update','/modifycategory','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\AdminModule\\Controller\\AdminController::updateAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,5,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(7,'approved','zikulaadminmodule_admin_delete','ZikulaAdminModule','admin','delete','/deletecategory','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\AdminModule\\Controller\\AdminController::deleteAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,6,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(8,'approved','zikulaadminmodule_admin_adminpanel','ZikulaAdminModule','admin','adminpanel','/panel/{acid}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:4:\"acid\";N;s:11:\"_controller\";s:70:\"Zikula\\Module\\AdminModule\\Controller\\AdminController::adminpanelAction\";}','a:2:{s:4:\"acid\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,7,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(9,'approved','zikulaadminmodule_admin_modifyconfig','ZikulaAdminModule','admin','modifyconfig','/config','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:72:\"Zikula\\Module\\AdminModule\\Controller\\AdminController::modifyconfigAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,8,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(10,'approved','zikulaadminmodule_admin_updateconfig','ZikulaAdminModule','admin','updateconfig','/config','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:72:\"Zikula\\Module\\AdminModule\\Controller\\AdminController::updateconfigAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,9,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(11,'approved','zikulaadminmodule_admin_categorymenu','ZikulaAdminModule','admin','categorymenu','/categorymenu/{acid}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:4:\"acid\";N;s:11:\"_controller\";s:72:\"Zikula\\Module\\AdminModule\\Controller\\AdminController::categorymenuAction\";}','a:2:{s:4:\"acid\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,10,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(12,'approved','zikulaadminmodule_admin_adminheader','ZikulaAdminModule','admin','adminheader','/header','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\AdminModule\\Controller\\AdminController::adminheaderAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,11,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(13,'approved','zikulaadminmodule_admin_adminfooter','ZikulaAdminModule','admin','adminfooter','/footer','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\AdminModule\\Controller\\AdminController::adminfooterAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,12,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(14,'approved','zikulaadminmodule_admin_help','ZikulaAdminModule','admin','help','/help','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:64:\"Zikula\\Module\\AdminModule\\Controller\\AdminController::helpAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,13,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(15,'approved','zikulaadminmodule_ajax_changemodulecategory','ZikulaAdminModule','ajax','changemodulecategory','/ajax/assigncategory','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:79:\"Zikula\\Module\\AdminModule\\Controller\\AjaxController::changeModuleCategoryAction\";}','a:0:{}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,14,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(16,'approved','zikulaadminmodule_ajax_addcategory','ZikulaAdminModule','ajax','addcategory','/ajax/newcategory','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\Module\\AdminModule\\Controller\\AjaxController::addCategoryAction\";}','a:0:{}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,15,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(17,'approved','zikulaadminmodule_ajax_deletecategory','ZikulaAdminModule','ajax','deletecategory','/ajax/deletecategory','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\AdminModule\\Controller\\AjaxController::deleteCategoryAction\";}','a:0:{}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,16,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(18,'approved','zikulaadminmodule_ajax_editcategory','ZikulaAdminModule','ajax','editcategory','/ajax/editcategory','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\AdminModule\\Controller\\AjaxController::editCategoryAction\";}','a:0:{}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,17,'5',0,0,'2016-08-11 19:10:41','2016-08-11 19:11:28'),(19,'approved','zikulaadminmodule_ajax_defaultcategory','ZikulaAdminModule','ajax','defaultcategory','/ajax/makedefault','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:74:\"Zikula\\Module\\AdminModule\\Controller\\AjaxController::defaultCategoryAction\";}','a:0:{}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,18,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(20,'approved','zikulaadminmodule_ajax_sortcategories','ZikulaAdminModule','ajax','sortcategories','/ajax/sortcategories','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\AdminModule\\Controller\\AjaxController::sortCategoriesAction\";}','a:0:{}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,19,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(21,'approved','zikulaadminmodule_ajax_sortmodules','ZikulaAdminModule','ajax','sortmodules','/ajax/sortmodules','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\Module\\AdminModule\\Controller\\AjaxController::sortModulesAction\";}','a:0:{}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,20,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(22,'approved','zikulablocksmodule_admin_index','ZikulaBlocksModule','admin','index','/admin','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,21,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(23,'approved','zikulablocksmodule_admin_view','ZikulaBlocksModule','admin','view','/admin/view','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:65:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::viewAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,22,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(24,'approved','zikulablocksmodule_admin_deactivate','ZikulaBlocksModule','admin','deactivate','/admin/deactivate/{bid}/{csrftoken}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::deactivateAction\";}','a:2:{s:3:\"bid\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,23,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(25,'approved','zikulablocksmodule_admin_activate','ZikulaBlocksModule','admin','activate','/admin/activate/{bid}/{csrftoken}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:69:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::activateAction\";}','a:2:{s:3:\"bid\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,24,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(26,'approved','zikulablocksmodule_admin_modify','ZikulaBlocksModule','admin','modify','/admin/modify/{bid}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:67:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::modifyAction\";}','a:2:{s:3:\"bid\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,25,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(27,'approved','zikulablocksmodule_admin_update','ZikulaBlocksModule','admin','update','/admin/modify','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:67:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::updateAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,26,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(28,'approved','zikulablocksmodule_admin_newblock','ZikulaBlocksModule','admin','newblock','/admin/new','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:69:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::newblockAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,27,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(29,'approved','zikulablocksmodule_admin_create','ZikulaBlocksModule','admin','create','/admin/new','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:67:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::createAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,28,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(30,'approved','zikulablocksmodule_admin_delete','ZikulaBlocksModule','admin','delete','/admin/delete','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:67:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::deleteAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,29,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(31,'approved','zikulablocksmodule_admin_newposition','ZikulaBlocksModule','admin','newposition','/admin/newposition/{name}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:4:\"name\";s:0:\"\";s:11:\"_controller\";s:72:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::newpositionAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,30,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(32,'approved','zikulablocksmodule_admin_createposition','ZikulaBlocksModule','admin','createposition','/admin/newposition','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::createpositionAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,31,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(33,'approved','zikulablocksmodule_admin_modifyposition','ZikulaBlocksModule','admin','modifyposition','/admin/modifyposition/{pid}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::modifypositionAction\";}','a:2:{s:3:\"pid\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,32,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(34,'approved','zikulablocksmodule_admin_updateposition','ZikulaBlocksModule','admin','updateposition','/admin/modifyposition','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::updatepositionAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,33,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(35,'approved','zikulablocksmodule_admin_deleteposition','ZikulaBlocksModule','admin','deleteposition','/admin/deleteposition','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::deletepositionAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,34,'5',0,0,'2016-08-11 19:10:42','2016-08-11 19:11:28'),(36,'approved','zikulablocksmodule_admin_modifyconfig','ZikulaBlocksModule','admin','modifyconfig','/admin/config','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::modifyconfigAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,35,'5',0,0,'2016-08-11 19:10:43','2016-08-11 19:11:28'),(37,'approved','zikulablocksmodule_admin_updateconfig','ZikulaBlocksModule','admin','updateconfig','/admin/config','','a:0:{}','a:1:{i:0;s:5:\"/POST\";}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\BlocksModule\\Controller\\AdminController::updateconfigAction\";}','a:1:{s:7:\"_method\";s:5:\"/POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,36,'5',0,0,'2016-08-11 19:10:43','2016-08-11 19:11:28'),(38,'approved','zikulablocksmodule_ajax_changeblockorder','ZikulaBlocksModule','ajax','changeblockorder','/ajax/changeorder','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:76:\"Zikula\\Module\\BlocksModule\\Controller\\AjaxController::changeblockorderAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,37,'5',0,0,'2016-08-11 19:10:43','2016-08-11 19:11:28'),(39,'approved','zikulablocksmodule_ajax_toggleblock','ZikulaBlocksModule','ajax','toggleblock','/ajax/toggle','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\BlocksModule\\Controller\\AjaxController::toggleblockAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,38,'5',0,0,'2016-08-11 19:10:43','2016-08-11 19:11:28'),(40,'approved','zikulablocksmodule_user_index','ZikulaBlocksModule','user','index','/','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:65:\"Zikula\\Module\\BlocksModule\\Controller\\UserController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,39,'5',0,0,'2016-08-11 19:10:43','2016-08-11 19:11:28'),(41,'approved','zikulablocksmodule_user_display','ZikulaBlocksModule','user','display','/display','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:67:\"Zikula\\Module\\BlocksModule\\Controller\\UserController::displayAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,40,'5',0,0,'2016-08-11 19:10:43','2016-08-11 19:11:28'),(42,'approved','zikulablocksmodule_user_changestatus','ZikulaBlocksModule','user','changestatus','/changestatus/{bid}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:72:\"Zikula\\Module\\BlocksModule\\Controller\\UserController::changestatusAction\";}','a:2:{s:3:\"bid\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,41,'5',0,0,'2016-08-11 19:10:43','2016-08-11 19:11:28'),(43,'approved','zikulacategoriesmodule_admin_index','ZikulaCategoriesModule','admin','index','/admin','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,42,'5',0,0,'2016-08-11 19:10:43','2016-08-11 19:11:28'),(44,'approved','zikulacategoriesmodule_admin_view','ZikulaCategoriesModule','admin','view','/admin/view','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:69:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminController::viewAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,43,'5',0,0,'2016-08-11 19:10:43','2016-08-11 19:11:28'),(45,'approved','zikulacategoriesmodule_admin_config','ZikulaCategoriesModule','admin','config','/admin/config','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminController::configAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,44,'5',0,0,'2016-08-11 19:10:43','2016-08-11 19:11:28'),(46,'approved','zikulacategoriesmodule_admin_edit','ZikulaCategoriesModule','admin','edit','/admin/edit/{cid}/{dr}/{mode}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:4:{s:3:\"cid\";i:0;s:2:\"dr\";i:1;s:4:\"mode\";s:3:\"new\";s:11:\"_controller\";s:69:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminController::editAction\";}','a:4:{s:3:\"cid\";s:8:\"[1-9]\\d*\";s:2:\"dr\";s:8:\"[1-9]\\d*\";s:4:\"mode\";s:8:\"edit|new\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,45,'5',0,0,'2016-08-11 19:10:43','2016-08-11 19:11:28'),(47,'approved','zikulacategoriesmodule_admin_editregistry','ZikulaCategoriesModule','admin','editregistry','/admin/editregistry','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:77:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminController::editregistryAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,46,'5',0,0,'2016-08-11 19:10:43','2016-08-11 19:11:28'),(48,'approved','zikulacategoriesmodule_admin_deleteregistry','ZikulaCategoriesModule','admin','deleteregistry','/admin/deleteregistry','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:79:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminController::deleteregistryAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,47,'5',0,0,'2016-08-11 19:10:43','2016-08-11 19:11:28'),(49,'approved','zikulacategoriesmodule_admin_newcat','ZikulaCategoriesModule','admin','newcat','/admin/new','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminController::newcatAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,48,'5',0,0,'2016-08-11 19:10:44','2016-08-11 19:11:28'),(50,'approved','zikulacategoriesmodule_admin_op','ZikulaCategoriesModule','admin','op','/admin/op','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:67:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminController::opAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,49,'5',0,0,'2016-08-11 19:10:44','2016-08-11 19:11:28'),(51,'approved','zikulacategoriesmodule_admin_preferences','ZikulaCategoriesModule','admin','preferences','/admin/preferences','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:76:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminController::preferencesAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,50,'5',0,0,'2016-08-11 19:10:44','2016-08-11 19:11:28'),(52,'approved','zikulacategoriesmodule_adminform_edit','ZikulaCategoriesModule','adminform','edit','/admin/edit','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminformController::editAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,51,'5',0,0,'2016-08-11 19:10:44','2016-08-11 19:11:28'),(53,'approved','zikulacategoriesmodule_adminform_newcat','ZikulaCategoriesModule','adminform','newcat','/admin/new','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminformController::newcatAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,52,'5',0,0,'2016-08-11 19:10:44','2016-08-11 19:11:28'),(54,'approved','zikulacategoriesmodule_adminform_delete','ZikulaCategoriesModule','adminform','delete','/admin/delete','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminformController::deleteAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,53,'5',0,0,'2016-08-11 19:10:44','2016-08-11 19:11:28'),(55,'approved','zikulacategoriesmodule_adminform_copy','ZikulaCategoriesModule','adminform','copy','/admin/copy','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminformController::copyAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,54,'5',0,0,'2016-08-11 19:10:44','2016-08-11 19:11:28'),(56,'approved','zikulacategoriesmodule_adminform_move','ZikulaCategoriesModule','adminform','move','/admin/move','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminformController::moveAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,55,'5',0,0,'2016-08-11 19:10:44','2016-08-11 19:11:28'),(57,'approved','zikulacategoriesmodule_adminform_rebuildpaths','ZikulaCategoriesModule','adminform','rebuildpaths','/admin/rebuild','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:81:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminformController::rebuildPathsAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,56,'5',0,0,'2016-08-11 19:10:44','2016-08-11 19:11:28'),(58,'approved','zikulacategoriesmodule_adminform_editregistry','ZikulaCategoriesModule','adminform','editregistry','/admin/editregistry','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:81:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminformController::editregistryAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,57,'5',0,0,'2016-08-11 19:10:44','2016-08-11 19:11:28'),(59,'approved','zikulacategoriesmodule_adminform_preferences','ZikulaCategoriesModule','adminform','preferences','/admin/preferences','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:80:\"Zikula\\Module\\CategoriesModule\\Controller\\AdminformController::preferencesAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,58,'5',0,0,'2016-08-11 19:10:44','2016-08-11 19:11:28'),(60,'approved','zikulacategoriesmodule_ajax_resequence','ZikulaCategoriesModule','ajax','resequence','/ajax/resequence','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:74:\"Zikula\\Module\\CategoriesModule\\Controller\\AjaxController::resequenceAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,59,'5',0,0,'2016-08-11 19:10:44','2016-08-11 19:11:28'),(61,'approved','zikulacategoriesmodule_ajax_edit','ZikulaCategoriesModule','ajax','edit','/ajax/edit','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:68:\"Zikula\\Module\\CategoriesModule\\Controller\\AjaxController::editAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,60,'5',0,0,'2016-08-11 19:10:44','2016-08-11 19:11:28'),(62,'approved','zikulacategoriesmodule_ajax_copy','ZikulaCategoriesModule','ajax','copy','/ajax/copy','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:68:\"Zikula\\Module\\CategoriesModule\\Controller\\AjaxController::copyAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,61,'5',0,0,'2016-08-11 19:10:45','2016-08-11 19:11:28'),(63,'approved','zikulacategoriesmodule_ajax_delete','ZikulaCategoriesModule','ajax','delete','/ajax/delete','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\Module\\CategoriesModule\\Controller\\AjaxController::deleteAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,62,'5',0,0,'2016-08-11 19:10:45','2016-08-11 19:11:28'),(64,'approved','zikulacategoriesmodule_ajax_deleteandmovesubs','ZikulaCategoriesModule','ajax','deleteandmovesubs','/ajax/deleteandmove','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:81:\"Zikula\\Module\\CategoriesModule\\Controller\\AjaxController::deleteandmovesubsAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,63,'5',0,0,'2016-08-11 19:10:45','2016-08-11 19:11:28'),(65,'approved','zikulacategoriesmodule_ajax_deletedialog','ZikulaCategoriesModule','ajax','deletedialog','/ajax/deletedialog','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:76:\"Zikula\\Module\\CategoriesModule\\Controller\\AjaxController::deletedialogAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,64,'5',0,0,'2016-08-11 19:10:45','2016-08-11 19:11:28'),(66,'approved','zikulacategoriesmodule_ajax_activate','ZikulaCategoriesModule','ajax','activate','/ajax/activate','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:72:\"Zikula\\Module\\CategoriesModule\\Controller\\AjaxController::activateAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,65,'5',0,0,'2016-08-11 19:10:45','2016-08-11 19:11:28'),(67,'approved','zikulacategoriesmodule_ajax_deactivate','ZikulaCategoriesModule','ajax','deactivate','/ajax/deactivate','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:74:\"Zikula\\Module\\CategoriesModule\\Controller\\AjaxController::deactivateAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,66,'5',0,0,'2016-08-11 19:10:45','2016-08-11 19:11:28'),(68,'approved','zikulacategoriesmodule_ajax_save','ZikulaCategoriesModule','ajax','save','/ajax/save','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:68:\"Zikula\\Module\\CategoriesModule\\Controller\\AjaxController::saveAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,67,'5',0,0,'2016-08-11 19:10:45','2016-08-11 19:11:28'),(69,'approved','zikulacategoriesmodule_user_index','ZikulaCategoriesModule','user','index','/','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:69:\"Zikula\\Module\\CategoriesModule\\Controller\\UserController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,68,'5',0,0,'2016-08-11 19:10:45','2016-08-11 19:11:28'),(70,'approved','zikulacategoriesmodule_user_edit','ZikulaCategoriesModule','user','edit','/edit','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:68:\"Zikula\\Module\\CategoriesModule\\Controller\\UserController::editAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,69,'5',0,0,'2016-08-11 19:10:45','2016-08-11 19:11:28'),(71,'approved','zikulacategoriesmodule_user_edituser','ZikulaCategoriesModule','user','edituser','/edituser','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:72:\"Zikula\\Module\\CategoriesModule\\Controller\\UserController::edituserAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,70,'5',0,0,'2016-08-11 19:10:45','2016-08-11 19:11:28'),(72,'approved','zikulacategoriesmodule_user_referback','ZikulaCategoriesModule','user','referback','/refer','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\CategoriesModule\\Controller\\UserController::referBackAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,71,'5',0,0,'2016-08-11 19:10:45','2016-08-11 19:11:28'),(73,'approved','zikulacategoriesmodule_user_getusercategories','ZikulaCategoriesModule','user','getusercategories','/usercategories','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:81:\"Zikula\\Module\\CategoriesModule\\Controller\\UserController::getusercategoriesAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,72,'5',0,0,'2016-08-11 19:10:45','2016-08-11 19:11:28'),(74,'approved','zikulacategoriesmodule_user_getusercategoryname','ZikulaCategoriesModule','user','getusercategoryname','/usercategoryname','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:83:\"Zikula\\Module\\CategoriesModule\\Controller\\UserController::getusercategorynameAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,73,'5',0,0,'2016-08-11 19:10:46','2016-08-11 19:11:28'),(75,'approved','zikulacategoriesmodule_userform_delete','ZikulaCategoriesModule','userform','delete','/delete','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:74:\"Zikula\\Module\\CategoriesModule\\Controller\\UserformController::deleteAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,74,'5',0,0,'2016-08-11 19:10:46','2016-08-11 19:11:28'),(76,'approved','zikulacategoriesmodule_userform_edit','ZikulaCategoriesModule','userform','edit','/update','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:72:\"Zikula\\Module\\CategoriesModule\\Controller\\UserformController::editAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,75,'5',0,0,'2016-08-11 19:10:46','2016-08-11 19:11:28'),(77,'approved','zikulacategoriesmodule_userform_movefield','ZikulaCategoriesModule','userform','movefield','/move/{cid}/{dr}/{direction}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:9:\"direction\";N;s:11:\"_controller\";s:77:\"Zikula\\Module\\CategoriesModule\\Controller\\UserformController::moveFieldAction\";}','a:4:{s:3:\"cid\";s:8:\"[1-9]\\d*\";s:2:\"dr\";s:8:\"[1-9]\\d*\";s:9:\"direction\";s:7:\"up|down\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,76,'5',0,0,'2016-08-11 19:10:46','2016-08-11 19:11:28'),(78,'approved','zikulacategoriesmodule_userform_newcat','ZikulaCategoriesModule','userform','newcat','/new','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:74:\"Zikula\\Module\\CategoriesModule\\Controller\\UserformController::newcatAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,77,'5',0,0,'2016-08-11 19:10:46','2016-08-11 19:11:28'),(79,'approved','zikulacategoriesmodule_userform_resequence','ZikulaCategoriesModule','userform','resequence','/resequence/{dr}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:78:\"Zikula\\Module\\CategoriesModule\\Controller\\UserformController::resequenceAction\";}','a:2:{s:2:\"dr\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,78,'5',0,0,'2016-08-11 19:10:46','2016-08-11 19:11:28'),(80,'approved','zikulaextensionsmodule_admin_index','ZikulaExtensionsModule','admin','index','/','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,79,'5',0,0,'2016-08-11 19:10:46','2016-08-11 19:11:28'),(81,'approved','zikulaextensionsmodule_admin_modify','ZikulaExtensionsModule','admin','modify','/modules/modify/{id}/{restore}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:7:\"restore\";b:0;s:11:\"_controller\";s:71:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::modifyAction\";}','a:3:{s:2:\"id\";s:8:\"[1-9]\\d*\";s:7:\"restore\";s:3:\"0|1\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,80,'5',0,0,'2016-08-11 19:10:46','2016-08-11 19:11:28'),(82,'approved','zikulaextensionsmodule_admin_update','ZikulaExtensionsModule','admin','update','/modules/modify','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::updateAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,81,'5',0,0,'2016-08-11 19:10:46','2016-08-11 19:11:28'),(83,'approved','zikulaextensionsmodule_admin_view','ZikulaExtensionsModule','admin','view','/modules','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:69:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::viewAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,82,'5',0,0,'2016-08-11 19:10:46','2016-08-11 19:11:28'),(84,'approved','zikulaextensionsmodule_admin_initialise','ZikulaExtensionsModule','admin','initialise','/modules/initialize','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::initialiseAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,83,'5',0,0,'2016-08-11 19:10:47','2016-08-11 19:11:28'),(85,'approved','zikulaextensionsmodule_admin_activate','ZikulaExtensionsModule','admin','activate','/modules/activate/{id}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::activateAction\";}','a:2:{s:2:\"id\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,84,'5',0,0,'2016-08-11 19:10:47','2016-08-11 19:11:28'),(86,'approved','zikulaextensionsmodule_admin_upgrade','ZikulaExtensionsModule','admin','upgrade','/modules/upgrade/{id}','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:72:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::upgradeAction\";}','a:1:{s:2:\"id\";s:8:\"[1-9]\\d*\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,85,'5',0,0,'2016-08-11 19:10:47','2016-08-11 19:11:28'),(87,'approved','zikulaextensionsmodule_admin_deactivate','ZikulaExtensionsModule','admin','deactivate','/modules/deactivate/{id}','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::deactivateAction\";}','a:1:{s:2:\"id\";s:8:\"[1-9]\\d*\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,86,'5',0,0,'2016-08-11 19:10:47','2016-08-11 19:11:28'),(88,'approved','zikulaextensionsmodule_admin_remove','ZikulaExtensionsModule','admin','remove','/modules/remove','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::removeAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,87,'5',0,0,'2016-08-11 19:10:47','2016-08-11 19:11:28'),(89,'approved','zikulaextensionsmodule_admin_modifyconfig','ZikulaExtensionsModule','admin','modifyconfig','/config','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:77:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::modifyconfigAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,88,'5',0,0,'2016-08-11 19:10:47','2016-08-11 19:11:28'),(90,'approved','zikulaextensionsmodule_admin_updateconfig','ZikulaExtensionsModule','admin','updateconfig','/config','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:77:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::updateconfigAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,89,'5',0,0,'2016-08-11 19:10:47','2016-08-11 19:11:28'),(91,'approved','zikulaextensionsmodule_admin_compinfo','ZikulaExtensionsModule','admin','compinfo','/modules/compatibility/{id}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::compinfoAction\";}','a:2:{s:2:\"id\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,90,'5',0,0,'2016-08-11 19:10:47','2016-08-11 19:11:28'),(92,'approved','zikulaextensionsmodule_admin_viewplugins','ZikulaExtensionsModule','admin','viewplugins','/plugins','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:76:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::viewPluginsAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,91,'5',0,0,'2016-08-11 19:10:47','2016-08-11 19:11:28'),(93,'approved','zikulaextensionsmodule_admin_initialiseplugin','ZikulaExtensionsModule','admin','initialiseplugin','/plugins/initialize/{plugin}','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:81:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::initialisePluginAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,92,'5',0,0,'2016-08-11 19:10:48','2016-08-11 19:11:28'),(94,'approved','zikulaextensionsmodule_admin_deactivateplugin','ZikulaExtensionsModule','admin','deactivateplugin','/plugins/deactivate/{plugin}','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:81:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::deactivatePluginAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,93,'5',0,0,'2016-08-11 19:10:48','2016-08-11 19:11:28'),(95,'approved','zikulaextensionsmodule_admin_activateplugin','ZikulaExtensionsModule','admin','activateplugin','/plugins/activate/{plugin}','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:79:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::activatePluginAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,94,'5',0,0,'2016-08-11 19:10:48','2016-08-11 19:11:28'),(96,'approved','zikulaextensionsmodule_admin_removeplugin','ZikulaExtensionsModule','admin','removeplugin','/plugins/remove/{plugin}','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:77:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::removePluginAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,95,'5',0,0,'2016-08-11 19:10:48','2016-08-11 19:11:28'),(97,'approved','zikulaextensionsmodule_admin_upgradeplugin','ZikulaExtensionsModule','admin','upgradeplugin','/plugins/upgrade/{plugin}','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:78:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::upgradePluginAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,96,'5',0,0,'2016-08-11 19:10:48','2016-08-11 19:11:28'),(98,'approved','zikulaextensionsmodule_admin_upgradeall','ZikulaExtensionsModule','admin','upgradeall','/modules/upgradeall','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::upgradeallAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,97,'5',0,0,'2016-08-11 19:10:48','2016-08-11 19:11:28'),(99,'approved','zikulaextensionsmodule_admin_hooks','ZikulaExtensionsModule','admin','hooks','/hooks/{moduleName}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::hooksAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:16:\"zkNoBundlePrefix\";i:1;}','','',0,98,'5',0,0,'2016-08-11 19:10:48','2016-08-11 19:11:28'),(100,'approved','zikulaextensionsmodule_admin_moduleservices','ZikulaExtensionsModule','admin','moduleservices','/moduleservices/{moduleName}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:79:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminController::moduleServicesAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:16:\"zkNoBundlePrefix\";i:1;}','','',0,99,'5',0,0,'2016-08-11 19:10:48','2016-08-11 19:11:28'),(101,'approved','zikulaextensionsmodule_adminplugin_dispatch','ZikulaExtensionsModule','adminplugin','dispatch','/adminplugin/dispatch','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:79:\"Zikula\\Module\\ExtensionsModule\\Controller\\AdminpluginController::dispatchAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,100,'5',0,0,'2016-08-11 19:10:48','2016-08-11 19:11:28'),(102,'approved','zikulaextensionsmodule_ajax_togglesubscriberareastatus','ZikulaExtensionsModule','ajax','togglesubscriberareastatus','/ajax/togglestatus','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:90:\"Zikula\\Module\\ExtensionsModule\\Controller\\AjaxController::togglesubscriberareastatusAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,101,'5',0,0,'2016-08-11 19:10:48','2016-08-11 19:11:28'),(103,'approved','zikulaextensionsmodule_ajax_changeproviderareaorder','ZikulaExtensionsModule','ajax','changeproviderareaorder','/ajax/changeorder','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:87:\"Zikula\\Module\\ExtensionsModule\\Controller\\AjaxController::changeproviderareaorderAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,102,'5',0,0,'2016-08-11 19:10:49','2016-08-11 19:11:28'),(104,'approved','zikulagroupsmodule_admin_index','ZikulaGroupsModule','admin','index','/admin','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\GroupsModule\\Controller\\AdminController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,103,'5',0,0,'2016-08-11 19:10:49','2016-08-11 19:11:28'),(105,'approved','zikulagroupsmodule_admin_view','ZikulaGroupsModule','admin','view','/admin/view/{startnum}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:8:\"startnum\";i:0;s:11:\"_controller\";s:65:\"Zikula\\Module\\GroupsModule\\Controller\\AdminController::viewAction\";}','a:2:{s:8:\"startnum\";s:3:\"\\d+\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,104,'5',0,0,'2016-08-11 19:10:49','2016-08-11 19:11:28'),(106,'approved','zikulagroupsmodule_admin_newgroup','ZikulaGroupsModule','admin','newgroup','/admin/new','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:69:\"Zikula\\Module\\GroupsModule\\Controller\\AdminController::newgroupAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,105,'5',0,0,'2016-08-11 19:10:49','2016-08-11 19:11:28'),(107,'approved','zikulagroupsmodule_admin_create','ZikulaGroupsModule','admin','create','/admin/new','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:67:\"Zikula\\Module\\GroupsModule\\Controller\\AdminController::createAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,106,'5',0,0,'2016-08-11 19:10:49','2016-08-11 19:11:28'),(108,'approved','zikulagroupsmodule_admin_modify','ZikulaGroupsModule','admin','modify','/admin/modify/{gid}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:3:\"gid\";i:0;s:11:\"_controller\";s:67:\"Zikula\\Module\\GroupsModule\\Controller\\AdminController::modifyAction\";}','a:2:{s:3:\"gid\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,107,'5',0,0,'2016-08-11 19:10:49','2016-08-11 19:11:28'),(109,'approved','zikulagroupsmodule_admin_update','ZikulaGroupsModule','admin','update','/admin/modify','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:67:\"Zikula\\Module\\GroupsModule\\Controller\\AdminController::updateAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,108,'5',0,0,'2016-08-11 19:10:49','2016-08-11 19:11:28'),(110,'approved','zikulagroupsmodule_admin_delete','ZikulaGroupsModule','admin','delete','/admin/delete','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:67:\"Zikula\\Module\\GroupsModule\\Controller\\AdminController::deleteAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,109,'5',0,0,'2016-08-11 19:10:50','2016-08-11 19:11:28'),(111,'approved','zikulagroupsmodule_admin_groupmembership','ZikulaGroupsModule','admin','groupmembership','/admin/membership/{gid}/{letter}/{startnum}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:4:{s:3:\"gid\";i:0;s:6:\"letter\";s:1:\"*\";s:8:\"startnum\";i:0;s:11:\"_controller\";s:76:\"Zikula\\Module\\GroupsModule\\Controller\\AdminController::groupmembershipAction\";}','a:4:{s:3:\"gid\";s:8:\"[1-9]\\d*\";s:6:\"letter\";s:11:\"[a-zA-Z]|\\*\";s:8:\"startnum\";s:3:\"\\d+\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,110,'5',0,0,'2016-08-11 19:10:50','2016-08-11 19:11:28'),(112,'approved','zikulagroupsmodule_admin_adduser','ZikulaGroupsModule','admin','adduser','/admin/adduser','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:68:\"Zikula\\Module\\GroupsModule\\Controller\\AdminController::adduserAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,111,'5',0,0,'2016-08-11 19:10:50','2016-08-11 19:11:28'),(113,'approved','zikulagroupsmodule_admin_removeuser','ZikulaGroupsModule','admin','removeuser','/admin/removeuser','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\GroupsModule\\Controller\\AdminController::removeuserAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,112,'5',0,0,'2016-08-11 19:10:50','2016-08-11 19:11:28'),(114,'approved','zikulagroupsmodule_admin_userpending','ZikulaGroupsModule','admin','userpending','/admin/pendingusers/{action}/{userid}/{gid}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:4:{s:6:\"action\";s:6:\"accept\";s:6:\"userid\";i:0;s:3:\"gid\";i:0;s:11:\"_controller\";s:72:\"Zikula\\Module\\GroupsModule\\Controller\\AdminController::userpendingAction\";}','a:4:{s:6:\"action\";s:11:\"deny|accept\";s:6:\"userid\";s:8:\"[1-9]\\d*\";s:3:\"gid\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,113,'5',0,0,'2016-08-11 19:10:50','2016-08-11 19:11:28'),(115,'approved','zikulagroupsmodule_admin_userupdate','ZikulaGroupsModule','admin','userupdate','/admin/pendingusers','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\GroupsModule\\Controller\\AdminController::userupdateAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,114,'5',0,0,'2016-08-11 19:10:50','2016-08-11 19:11:28'),(116,'approved','zikulagroupsmodule_admin_modifyconfig','ZikulaGroupsModule','admin','modifyconfig','/admin/config','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\GroupsModule\\Controller\\AdminController::modifyconfigAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,115,'5',0,0,'2016-08-11 19:10:50','2016-08-11 19:11:28'),(117,'approved','zikulagroupsmodule_admin_updateconfig','ZikulaGroupsModule','admin','updateconfig','/admin/config','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\GroupsModule\\Controller\\AdminController::updateconfigAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,116,'5',0,0,'2016-08-11 19:10:50','2016-08-11 19:11:28'),(118,'approved','zikulagroupsmodule_ajax_updategroup','ZikulaGroupsModule','ajax','updategroup','/ajax/update','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\GroupsModule\\Controller\\AjaxController::updategroupAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,117,'5',0,0,'2016-08-11 19:10:51','2016-08-11 19:11:28'),(119,'approved','zikulagroupsmodule_ajax_creategroup','ZikulaGroupsModule','ajax','creategroup','/ajax/create','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\GroupsModule\\Controller\\AjaxController::creategroupAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,118,'5',0,0,'2016-08-11 19:10:51','2016-08-11 19:11:28'),(120,'approved','zikulagroupsmodule_ajax_deletegroup','ZikulaGroupsModule','ajax','deletegroup','/ajax/delete','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\GroupsModule\\Controller\\AjaxController::deletegroupAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,119,'5',0,0,'2016-08-11 19:10:51','2016-08-11 19:11:28'),(121,'approved','zikulagroupsmodule_ajax_removeuser','ZikulaGroupsModule','ajax','removeuser','/ajax/removeuser','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\Module\\GroupsModule\\Controller\\AjaxController::removeuserAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,120,'5',0,0,'2016-08-11 19:10:51','2016-08-11 19:11:28'),(122,'approved','zikulagroupsmodule_user_index','ZikulaGroupsModule','user','index','/','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:65:\"Zikula\\Module\\GroupsModule\\Controller\\UserController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,121,'5',0,0,'2016-08-11 19:10:51','2016-08-11 19:11:28'),(123,'approved','zikulagroupsmodule_user_view','ZikulaGroupsModule','user','view','/view/{startnum}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:8:\"startnum\";i:0;s:11:\"_controller\";s:64:\"Zikula\\Module\\GroupsModule\\Controller\\UserController::viewAction\";}','a:2:{s:8:\"startnum\";s:3:\"\\d+\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,122,'5',0,0,'2016-08-11 19:10:51','2016-08-11 19:11:28'),(124,'approved','zikulagroupsmodule_user_membership','ZikulaGroupsModule','user','membership','/membership/{action}/{gid}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:3:{s:6:\"action\";s:6:\"cancel\";s:3:\"gid\";i:0;s:11:\"_controller\";s:70:\"Zikula\\Module\\GroupsModule\\Controller\\UserController::membershipAction\";}','a:3:{s:6:\"action\";s:28:\"subscribe|unsubscribe|cancel\";s:3:\"gid\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,123,'5',0,0,'2016-08-11 19:10:51','2016-08-11 19:11:28'),(125,'approved','zikulagroupsmodule_user_userupdate','ZikulaGroupsModule','user','userupdate','/update','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\Module\\GroupsModule\\Controller\\UserController::userupdateAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,124,'5',0,0,'2016-08-11 19:10:51','2016-08-11 19:11:28'),(126,'approved','zikulagroupsmodule_user_memberslist','ZikulaGroupsModule','user','memberslist','/memberlist/{gid}/{startnum}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:3:{s:3:\"gid\";i:0;s:8:\"startnum\";i:0;s:11:\"_controller\";s:71:\"Zikula\\Module\\GroupsModule\\Controller\\UserController::memberslistAction\";}','a:3:{s:3:\"gid\";s:8:\"[1-9]\\d*\";s:8:\"startnum\";s:3:\"\\d+\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,125,'5',0,0,'2016-08-11 19:10:52','2016-08-11 19:11:28'),(127,'approved','zikulamailermodule_admin_index','ZikulaMailerModule','admin','index','/admin','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\MailerModule\\Controller\\AdminController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,126,'5',0,0,'2016-08-11 19:10:52','2016-08-11 19:11:28'),(128,'approved','zikulamailermodule_admin_modifyconfig','ZikulaMailerModule','admin','modifyconfig','/admin/config','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\MailerModule\\Controller\\AdminController::modifyconfigAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,127,'5',0,0,'2016-08-11 19:10:52','2016-08-11 19:11:28'),(129,'approved','zikulamailermodule_admin_testconfig','ZikulaMailerModule','admin','testconfig','/admin/test','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\MailerModule\\Controller\\AdminController::testconfigAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,128,'5',0,0,'2016-08-11 19:10:52','2016-08-11 19:11:28'),(130,'approved','zikulapagelockmodule_ajax_refreshpagelock','ZikulaPageLockModule','ajax','refreshpagelock','/ajax/refresh','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:77:\"Zikula\\Module\\PageLockModule\\Controller\\AjaxController::refreshpagelockAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,129,'5',0,0,'2016-08-11 19:10:52','2016-08-11 19:11:28'),(131,'approved','zikulapagelockmodule_ajax_checkpagelock','ZikulaPageLockModule','ajax','checkpagelock','/ajax/check','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\PageLockModule\\Controller\\AjaxController::checkpagelockAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,130,'5',0,0,'2016-08-11 19:10:52','2016-08-11 19:11:28'),(132,'approved','zikulapermissionsmodule_admin_index','ZikulaPermissionsModule','admin','index','/','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\PermissionsModule\\Controller\\AdminController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,131,'5',0,0,'2016-08-11 19:10:53','2016-08-11 19:11:28'),(133,'approved','zikulapermissionsmodule_admin_view','ZikulaPermissionsModule','admin','view','/view','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\Module\\PermissionsModule\\Controller\\AdminController::viewAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,132,'5',0,0,'2016-08-11 19:10:53','2016-08-11 19:11:28'),(134,'approved','zikulapermissionsmodule_admin_inc','ZikulaPermissionsModule','admin','inc','/inc/{pid}/{permgrp}','','a:0:{}','a:0:{}','a:2:{s:7:\"permgrp\";N;s:11:\"_controller\";s:69:\"Zikula\\Module\\PermissionsModule\\Controller\\AdminController::incAction\";}','a:2:{s:3:\"pid\";s:3:\"\\d+\";s:7:\"permgrp\";s:3:\"\\d+\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,133,'5',0,0,'2016-08-11 19:10:53','2016-08-11 19:11:28'),(135,'approved','zikulapermissionsmodule_admin_dec','ZikulaPermissionsModule','admin','dec','/dec/{pid}/{permgrp}','','a:0:{}','a:0:{}','a:2:{s:7:\"permgrp\";N;s:11:\"_controller\";s:69:\"Zikula\\Module\\PermissionsModule\\Controller\\AdminController::decAction\";}','a:2:{s:3:\"pid\";s:3:\"\\d+\";s:7:\"permgrp\";s:3:\"\\d+\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,134,'5',0,0,'2016-08-11 19:10:53','2016-08-11 19:11:28'),(136,'approved','zikulapermissionsmodule_admin_listedit','ZikulaPermissionsModule','admin','listedit','/edit/{action}/{chgpid}','','a:0:{}','a:0:{}','a:3:{s:6:\"action\";s:3:\"add\";s:6:\"chgpid\";N;s:11:\"_controller\";s:74:\"Zikula\\Module\\PermissionsModule\\Controller\\AdminController::listeditAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,135,'5',0,0,'2016-08-11 19:10:53','2016-08-11 19:11:28'),(137,'approved','zikulapermissionsmodule_admin_update','ZikulaPermissionsModule','admin','update','/update','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:72:\"Zikula\\Module\\PermissionsModule\\Controller\\AdminController::updateAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,136,'5',0,0,'2016-08-11 19:10:53','2016-08-11 19:11:28'),(138,'approved','zikulapermissionsmodule_admin_create','ZikulaPermissionsModule','admin','create','/create','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:72:\"Zikula\\Module\\PermissionsModule\\Controller\\AdminController::createAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,137,'5',0,0,'2016-08-11 19:10:53','2016-08-11 19:11:28'),(139,'approved','zikulapermissionsmodule_admin_delete','ZikulaPermissionsModule','admin','delete','/delete/{pid}/{permgrp}','','a:0:{}','a:0:{}','a:2:{s:7:\"permgrp\";N;s:11:\"_controller\";s:72:\"Zikula\\Module\\PermissionsModule\\Controller\\AdminController::deleteAction\";}','a:2:{s:3:\"pid\";s:3:\"\\d+\";s:7:\"permgrp\";s:3:\"\\d+\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,138,'5',0,0,'2016-08-11 19:10:54','2016-08-11 19:11:28'),(140,'approved','zikulapermissionsmodule_admin_viewinstanceinfo','ZikulaPermissionsModule','admin','viewinstanceinfo','/instance-info','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:82:\"Zikula\\Module\\PermissionsModule\\Controller\\AdminController::viewinstanceinfoAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,139,'5',0,0,'2016-08-11 19:10:54','2016-08-11 19:11:28'),(141,'approved','zikulapermissionsmodule_admin_modifyconfig','ZikulaPermissionsModule','admin','modifyconfig','/config','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:78:\"Zikula\\Module\\PermissionsModule\\Controller\\AdminController::modifyconfigAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,140,'5',0,0,'2016-08-11 19:10:54','2016-08-11 19:11:28'),(142,'approved','zikulapermissionsmodule_admin_updateconfig','ZikulaPermissionsModule','admin','updateconfig','/config','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:78:\"Zikula\\Module\\PermissionsModule\\Controller\\AdminController::updateconfigAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,141,'5',0,0,'2016-08-11 19:10:54','2016-08-11 19:11:28'),(143,'approved','zikulapermissionsmodule_ajax_update','ZikulaPermissionsModule','ajax','update','/ajax/update','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\PermissionsModule\\Controller\\AjaxController::updateAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,142,'5',0,0,'2016-08-11 19:10:54','2016-08-11 19:11:28'),(144,'approved','zikulapermissionsmodule_ajax_changeorder','ZikulaPermissionsModule','ajax','changeorder','/ajax/change-order','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:76:\"Zikula\\Module\\PermissionsModule\\Controller\\AjaxController::changeorderAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,143,'5',0,0,'2016-08-11 19:10:54','2016-08-11 19:11:28'),(145,'approved','zikulapermissionsmodule_ajax_create','ZikulaPermissionsModule','ajax','create','/ajax/create','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\PermissionsModule\\Controller\\AjaxController::createAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,144,'5',0,0,'2016-08-11 19:10:54','2016-08-11 19:11:28'),(146,'approved','zikulapermissionsmodule_ajax_delete','ZikulaPermissionsModule','ajax','delete','/ajax/delete','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\PermissionsModule\\Controller\\AjaxController::deleteAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,145,'5',0,0,'2016-08-11 19:10:55','2016-08-11 19:11:28'),(147,'approved','zikulapermissionsmodule_ajax_test','ZikulaPermissionsModule','ajax','test','/ajax/test','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:69:\"Zikula\\Module\\PermissionsModule\\Controller\\AjaxController::testAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,146,'5',0,0,'2016-08-11 19:10:55','2016-08-11 19:11:28'),(148,'approved','zikulasearchmodule_admin_index','ZikulaSearchModule','admin','index','/admin','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\SearchModule\\Controller\\AdminController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,147,'5',0,0,'2016-08-11 19:10:55','2016-08-11 19:11:28'),(149,'approved','zikulasearchmodule_admin_modifyconfig','ZikulaSearchModule','admin','modifyconfig','/admin/config','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\SearchModule\\Controller\\AdminController::modifyconfigAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,148,'5',0,0,'2016-08-11 19:10:55','2016-08-11 19:11:28'),(150,'approved','zikulasearchmodule_admin_updateconfig','ZikulaSearchModule','admin','updateconfig','/admin/config','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\SearchModule\\Controller\\AdminController::updateconfigAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,149,'5',0,0,'2016-08-11 19:10:55','2016-08-11 19:11:28'),(151,'approved','zikulasearchmodule_user_form','ZikulaSearchModule','user','form','/','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:64:\"Zikula\\Module\\SearchModule\\Controller\\UserController::formAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,150,'5',0,0,'2016-08-11 19:10:55','2016-08-11 19:11:28'),(152,'approved','zikulasearchmodule_user_search','ZikulaSearchModule','user','search','/results/{page}','','a:0:{}','a:0:{}','a:2:{s:4:\"page\";i:-1;s:11:\"_controller\";s:66:\"Zikula\\Module\\SearchModule\\Controller\\UserController::searchAction\";}','a:1:{s:4:\"page\";s:3:\"\\d+\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,151,'5',0,0,'2016-08-11 19:10:56','2016-08-11 19:11:28'),(153,'approved','zikulasearchmodule_user_recent','ZikulaSearchModule','user','recent','/recent-searches','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\SearchModule\\Controller\\UserController::recentAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,152,'5',0,0,'2016-08-11 19:10:56','2016-08-11 19:11:28'),(154,'approved','zikulasearchmodule_user_opensearch','ZikulaSearchModule','user','opensearch','/opensearch','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\Module\\SearchModule\\Controller\\UserController::opensearchAction\";}','a:0:{}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:4:\"i18n\";b:0;}','','',0,153,'5',0,0,'2016-08-11 19:10:56','2016-08-11 19:11:28'),(155,'approved','zikulasecuritycentermodule_admin_index','ZikulaSecurityCenterModule','admin','index','/admin','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:74:\"Zikula\\Module\\SecurityCenterModule\\Controller\\AdminController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,154,'5',0,0,'2016-08-11 19:10:56','2016-08-11 19:11:28'),(156,'approved','zikulasecuritycentermodule_admin_modifyconfig','ZikulaSecurityCenterModule','admin','modifyconfig','/admin/config','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:81:\"Zikula\\Module\\SecurityCenterModule\\Controller\\AdminController::modifyconfigAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,155,'5',0,0,'2016-08-11 19:10:56','2016-08-11 19:11:28'),(157,'approved','zikulasecuritycentermodule_admin_updateconfig','ZikulaSecurityCenterModule','admin','updateconfig','/admin/config','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:81:\"Zikula\\Module\\SecurityCenterModule\\Controller\\AdminController::updateconfigAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,156,'5',0,0,'2016-08-11 19:10:56','2016-08-11 19:11:28'),(158,'approved','zikulasecuritycentermodule_admin_purifierconfig','ZikulaSecurityCenterModule','admin','purifierconfig','/admin/purifierconfig/{reset}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:5:\"reset\";N;s:11:\"_controller\";s:83:\"Zikula\\Module\\SecurityCenterModule\\Controller\\AdminController::purifierconfigAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,157,'5',0,0,'2016-08-11 19:10:57','2016-08-11 19:11:28'),(159,'approved','zikulasecuritycentermodule_admin_updatepurifierconfig','ZikulaSecurityCenterModule','admin','updatepurifierconfig','/admin/purifierconfig','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:89:\"Zikula\\Module\\SecurityCenterModule\\Controller\\AdminController::updatepurifierconfigAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,158,'5',0,0,'2016-08-11 19:10:57','2016-08-11 19:11:28'),(160,'approved','zikulasecuritycentermodule_admin_viewidslog','ZikulaSecurityCenterModule','admin','viewidslog','/admin/idslog','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:79:\"Zikula\\Module\\SecurityCenterModule\\Controller\\AdminController::viewidslogAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,159,'5',0,0,'2016-08-11 19:10:57','2016-08-11 19:11:28'),(161,'approved','zikulasecuritycentermodule_admin_exportidslog','ZikulaSecurityCenterModule','admin','exportidslog','/admin/exportidslog','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:81:\"Zikula\\Module\\SecurityCenterModule\\Controller\\AdminController::exportidslogAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,160,'5',0,0,'2016-08-11 19:10:57','2016-08-11 19:11:28'),(162,'approved','zikulasecuritycentermodule_admin_purgeidslog','ZikulaSecurityCenterModule','admin','purgeidslog','/admin/purgeidslog','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:80:\"Zikula\\Module\\SecurityCenterModule\\Controller\\AdminController::purgeidslogAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,161,'5',0,0,'2016-08-11 19:10:57','2016-08-11 19:11:28'),(163,'approved','zikulasecuritycentermodule_admin_allowedhtml','ZikulaSecurityCenterModule','admin','allowedhtml','/admin/allowedhtml','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:80:\"Zikula\\Module\\SecurityCenterModule\\Controller\\AdminController::allowedhtmlAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,162,'5',0,0,'2016-08-11 19:10:57','2016-08-11 19:11:28'),(164,'approved','zikulasecuritycentermodule_admin_updateallowedhtml','ZikulaSecurityCenterModule','admin','updateallowedhtml','/admin/allowedhtml','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:86:\"Zikula\\Module\\SecurityCenterModule\\Controller\\AdminController::updateallowedhtmlAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,163,'5',0,0,'2016-08-11 19:10:58','2016-08-11 19:11:28'),(165,'approved','zikulasecuritycentermodule_adminform_deleteidsentry','ZikulaSecurityCenterModule','adminform','deleteidsentry','/adminform/deleteidsentry','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:87:\"Zikula\\Module\\SecurityCenterModule\\Controller\\AdminformController::deleteidsentryAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,164,'5',0,0,'2016-08-11 19:10:58','2016-08-11 19:11:28'),(166,'approved','zikulasettingsmodule_admin_index','ZikulaSettingsModule','admin','index','/admin','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:68:\"Zikula\\Module\\SettingsModule\\Controller\\AdminController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,165,'5',0,0,'2016-08-11 19:10:58','2016-08-11 19:11:28'),(167,'approved','zikulasettingsmodule_admin_modifyconfig','ZikulaSettingsModule','admin','modifyconfig','/admin/config','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\SettingsModule\\Controller\\AdminController::modifyconfigAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,166,'5',0,0,'2016-08-11 19:10:58','2016-08-11 19:11:28'),(168,'approved','zikulasettingsmodule_admin_updateconfig','ZikulaSettingsModule','admin','updateconfig','/admin/config','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\SettingsModule\\Controller\\AdminController::updateconfigAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,167,'5',0,0,'2016-08-11 19:10:58','2016-08-11 19:11:28'),(169,'approved','zikulasettingsmodule_admin_multilingual','ZikulaSettingsModule','admin','multilingual','/admin/ml','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\SettingsModule\\Controller\\AdminController::multilingualAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:4:\"i18n\";b:0;}','','',0,168,'5',0,0,'2016-08-11 19:10:58','2016-08-11 19:11:28'),(170,'approved','zikulasettingsmodule_admin_updatemultilingual','ZikulaSettingsModule','admin','updatemultilingual','/admin/ml','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:81:\"Zikula\\Module\\SettingsModule\\Controller\\AdminController::updatemultilingualAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,169,'5',0,0,'2016-08-11 19:10:59','2016-08-11 19:11:28'),(171,'approved','zikulasettingsmodule_admin_phpinfo','ZikulaSettingsModule','admin','phpinfo','/admin/phpinfo','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\Module\\SettingsModule\\Controller\\AdminController::phpinfoAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,170,'5',0,0,'2016-08-11 19:10:59','2016-08-11 19:11:28'),(172,'approved','zikulathememodule_admin_index','ZikulaThemeModule','admin','index','/admin','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:65:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,171,'5',0,0,'2016-08-11 19:10:59','2016-08-11 19:11:28'),(173,'approved','zikulathememodule_admin_view','ZikulaThemeModule','admin','view','/admin/view/{startnum}/{startlet}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:3:{s:8:\"startnum\";i:1;s:8:\"startlet\";N;s:11:\"_controller\";s:64:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::viewAction\";}','a:3:{s:8:\"startnum\";s:3:\"\\d+\";s:8:\"startlet\";s:11:\"[a-zA-Z]|\\*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,172,'5',0,0,'2016-08-11 19:10:59','2016-08-11 19:11:28'),(174,'approved','zikulathememodule_admin_modify','ZikulaThemeModule','admin','modify','/admin/modify/{themename}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::modifyAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,173,'5',0,0,'2016-08-11 19:10:59','2016-08-11 19:11:28'),(175,'approved','zikulathememodule_admin_updatesettings','ZikulaThemeModule','admin','updatesettings','/admin/modify','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:74:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::updatesettingsAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,174,'5',0,0,'2016-08-11 19:11:00','2016-08-11 19:11:28'),(176,'approved','zikulathememodule_admin_variables','ZikulaThemeModule','admin','variables','/admin/variables/{themename}/{filename}','','a:0:{}','a:0:{}','a:2:{s:8:\"filename\";N;s:11:\"_controller\";s:69:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::variablesAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,175,'5',0,0,'2016-08-11 19:11:00','2016-08-11 19:11:28'),(177,'approved','zikulathememodule_admin_updatevariables','ZikulaThemeModule','admin','updatevariables','/admin/variables','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::updatevariablesAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,176,'5',0,0,'2016-08-11 19:11:00','2016-08-11 19:11:28'),(178,'approved','zikulathememodule_admin_palettes','ZikulaThemeModule','admin','palettes','/admin/palettes/{themename}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:68:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::palettesAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,177,'5',0,0,'2016-08-11 19:11:00','2016-08-11 19:11:28'),(179,'approved','zikulathememodule_admin_updatepalettes','ZikulaThemeModule','admin','updatepalettes','/admin/palettes','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:74:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::updatepalettesAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,178,'5',0,0,'2016-08-11 19:11:00','2016-08-11 19:11:28'),(180,'approved','zikulathememodule_admin_pageconfigurations','ZikulaThemeModule','admin','pageconfigurations','/admin/pageconfig/{themename}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:78:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::pageconfigurationsAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,179,'5',0,0,'2016-08-11 19:11:01','2016-08-11 19:11:28'),(181,'approved','zikulathememodule_admin_modifypageconfigtemplates','ZikulaThemeModule','admin','modifypageconfigtemplates','/admin/modifypageconfigtemplates/{themename}/{filename}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:8:\"filename\";N;s:11:\"_controller\";s:85:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::modifypageconfigtemplatesAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,180,'5',0,0,'2016-08-11 19:11:01','2016-08-11 19:11:28'),(182,'approved','zikulathememodule_admin_updatepageconfigtemplates','ZikulaThemeModule','admin','updatepageconfigtemplates','/admin/modifypageconfigtemplates','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:85:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::updatepageconfigtemplatesAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,181,'5',0,0,'2016-08-11 19:11:01','2016-08-11 19:11:28'),(183,'approved','zikulathememodule_admin_modifypageconfigurationassignment','ZikulaThemeModule','admin','modifypageconfigurationassignment','/admin/modifypageconfigurationassignment/{themename}/{pcname}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:6:\"pcname\";N;s:11:\"_controller\";s:93:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::modifypageconfigurationassignmentAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,182,'5',0,0,'2016-08-11 19:11:01','2016-08-11 19:11:28'),(184,'approved','zikulathememodule_admin_updatepageconfigurationassignment','ZikulaThemeModule','admin','updatepageconfigurationassignment','/admin/pageconfig','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:93:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::updatepageconfigurationassignmentAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,183,'5',0,0,'2016-08-11 19:11:01','2016-08-11 19:11:28'),(185,'approved','zikulathememodule_admin_deletepageconfigurationassignment','ZikulaThemeModule','admin','deletepageconfigurationassignment','/admin/deletepageconfigurationassignment','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:93:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::deletepageconfigurationassignmentAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,184,'5',0,0,'2016-08-11 19:11:01','2016-08-11 19:11:28'),(186,'approved','zikulathememodule_admin_credits','ZikulaThemeModule','admin','credits','/admin/credits/{themename}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:67:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::creditsAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,185,'5',0,0,'2016-08-11 19:11:02','2016-08-11 19:11:28'),(187,'approved','zikulathememodule_admin_setasdefault','ZikulaThemeModule','admin','setasdefault','/admin/makedefault','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:72:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::setasdefaultAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,186,'5',0,0,'2016-08-11 19:11:02','2016-08-11 19:11:28'),(188,'approved','zikulathememodule_admin_delete','ZikulaThemeModule','admin','delete','/admin/delete','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::deleteAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,187,'5',0,0,'2016-08-11 19:11:02','2016-08-11 19:11:28'),(189,'approved','zikulathememodule_admin_modifyconfig','ZikulaThemeModule','admin','modifyconfig','/admin/config','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:72:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::modifyconfigAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,188,'5',0,0,'2016-08-11 19:11:02','2016-08-11 19:11:28'),(190,'approved','zikulathememodule_admin_updateconfig','ZikulaThemeModule','admin','updateconfig','/admin/config','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:72:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::updateconfigAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,189,'5',0,0,'2016-08-11 19:11:02','2016-08-11 19:11:28'),(191,'approved','zikulathememodule_admin_clearcompiled','ZikulaThemeModule','admin','clearcompiled','/admin/clearcompiled','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::clearCompiledAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,190,'5',0,0,'2016-08-11 19:11:03','2016-08-11 19:11:28'),(192,'approved','zikulathememodule_admin_clearcache','ZikulaThemeModule','admin','clearcache','/admin/clearcache','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::clearCacheAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,191,'5',0,0,'2016-08-11 19:11:03','2016-08-11 19:11:28'),(193,'approved','zikulathememodule_admin_clearcssjscombinecache','ZikulaThemeModule','admin','clearcssjscombinecache','/admin/clearcombo','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:82:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::clearCssjscombinecacheAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,192,'5',0,0,'2016-08-11 19:11:03','2016-08-11 19:11:28'),(194,'approved','zikulathememodule_admin_clearconfig','ZikulaThemeModule','admin','clearconfig','/admin/clearconfig','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::clearConfigAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,193,'5',0,0,'2016-08-11 19:11:03','2016-08-11 19:11:28'),(195,'approved','zikulathememodule_admin_renderclearcompiled','ZikulaThemeModule','admin','renderclearcompiled','/admin/renderclearcompiled','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:79:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::renderClearCompiledAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,194,'5',0,0,'2016-08-11 19:11:03','2016-08-11 19:11:28'),(196,'approved','zikulathememodule_admin_renderclearcache','ZikulaThemeModule','admin','renderclearcache','/admin/renderclearcache','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:76:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::renderClearCacheAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,195,'5',0,0,'2016-08-11 19:11:04','2016-08-11 19:11:28'),(197,'approved','zikulathememodule_admin_clearallcompiledcaches','ZikulaThemeModule','admin','clearallcompiledcaches','/admin/clearall','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:82:\"Zikula\\Module\\ThemeModule\\Controller\\AdminController::clearallcompiledcachesAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,196,'5',0,0,'2016-08-11 19:11:04','2016-08-11 19:11:28'),(198,'approved','zikulathememodule_ajax_dispatch','ZikulaThemeModule','ajax','dispatch','/ajax/dispatch','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:67:\"Zikula\\Module\\ThemeModule\\Controller\\AjaxController::dispatchAction\";}','a:0:{}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,197,'5',0,0,'2016-08-11 19:11:04','2016-08-11 19:11:28'),(199,'approved','zikulathememodule_user_index','ZikulaThemeModule','user','index','/','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:64:\"Zikula\\Module\\ThemeModule\\Controller\\UserController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,198,'5',0,0,'2016-08-11 19:11:04','2016-08-11 19:11:28'),(200,'approved','zikulathememodule_user_resettodefault','ZikulaThemeModule','user','resettodefault','/reset','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\ThemeModule\\Controller\\UserController::resettodefaultAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,199,'5',0,0,'2016-08-11 19:11:04','2016-08-11 19:11:28'),(201,'approved','zikulausersmodule_admin_index','ZikulaUsersModule','admin','index','/admin','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:65:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::indexAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,200,'5',0,0,'2016-08-11 19:11:05','2016-08-11 19:11:28'),(202,'approved','zikulausersmodule_admin_view','ZikulaUsersModule','admin','view','/admin/view','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:64:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::viewAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,201,'5',0,0,'2016-08-11 19:11:05','2016-08-11 19:11:28'),(203,'approved','zikulausersmodule_admin_newuser','ZikulaUsersModule','admin','newuser','/admin/newuser','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:67:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::newUserAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,202,'5',0,0,'2016-08-11 19:11:05','2016-08-11 19:11:28'),(204,'approved','zikulausersmodule_admin_search','ZikulaUsersModule','admin','search','/admin/search','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::searchAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,203,'5',0,0,'2016-08-11 19:11:06','2016-08-11 19:11:28'),(205,'approved','zikulausersmodule_admin_mailusers','ZikulaUsersModule','admin','mailusers','/admin/mailusers','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:69:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::mailUsersAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,204,'5',0,0,'2016-08-11 19:11:06','2016-08-11 19:11:28'),(206,'approved','zikulausersmodule_admin_modify','ZikulaUsersModule','admin','modify','/admin/modify','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::modifyAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,205,'5',0,0,'2016-08-11 19:11:06','2016-08-11 19:11:28'),(207,'approved','zikulausersmodule_admin_lostusername','ZikulaUsersModule','admin','lostusername','/admin/lostusername','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:72:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::lostUsernameAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,206,'5',0,0,'2016-08-11 19:11:06','2016-08-11 19:11:28'),(208,'approved','zikulausersmodule_admin_lostpassword','ZikulaUsersModule','admin','lostpassword','/admin/lostpassword/{userid}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:72:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::lostPasswordAction\";}','a:2:{s:6:\"userid\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,207,'5',0,0,'2016-08-11 19:11:06','2016-08-11 19:11:28'),(209,'approved','zikulausersmodule_admin_deleteusers','ZikulaUsersModule','admin','deleteusers','/admin/deleteusers','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::deleteUsersAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,208,'5',0,0,'2016-08-11 19:11:07','2016-08-11 19:11:28'),(210,'approved','zikulausersmodule_admin_viewregistrations','ZikulaUsersModule','admin','viewregistrations','/admin/viewregistrations','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:77:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::viewRegistrationsAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,209,'5',0,0,'2016-08-11 19:11:07','2016-08-11 19:11:28'),(211,'approved','zikulausersmodule_admin_displayregistration','ZikulaUsersModule','admin','displayregistration','/admin/displayregistration/{uid}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:79:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::displayRegistrationAction\";}','a:2:{s:3:\"uid\";s:8:\"[1-9]\\d*\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,210,'5',0,0,'2016-08-11 19:11:07','2016-08-11 19:11:28'),(212,'approved','zikulausersmodule_admin_modifyregistration','ZikulaUsersModule','admin','modifyregistration','/admin/modifyregistration','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:78:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::modifyRegistrationAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,211,'5',0,0,'2016-08-11 19:11:07','2016-08-11 19:11:28'),(213,'approved','zikulausersmodule_admin_verifyregistration','ZikulaUsersModule','admin','verifyregistration','/admin/verifyregistration','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:78:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::verifyRegistrationAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,212,'5',0,0,'2016-08-11 19:11:07','2016-08-11 19:11:28'),(214,'approved','zikulausersmodule_admin_approveregistration','ZikulaUsersModule','admin','approveregistration','/admin/approveregistration','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:79:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::approveRegistrationAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,213,'5',0,0,'2016-08-11 19:11:08','2016-08-11 19:11:28'),(215,'approved','zikulausersmodule_admin_denyregistration','ZikulaUsersModule','admin','denyregistration','/admin/denyregistration','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:76:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::denyRegistrationAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,214,'5',0,0,'2016-08-11 19:11:08','2016-08-11 19:11:28'),(216,'approved','zikulausersmodule_admin_config','ZikulaUsersModule','admin','config','/admin/config','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::configAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,215,'5',0,0,'2016-08-11 19:11:08','2016-08-11 19:11:28'),(217,'approved','zikulausersmodule_admin_import','ZikulaUsersModule','admin','import','/admin/import','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:66:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::importAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,216,'5',0,0,'2016-08-11 19:11:08','2016-08-11 19:11:28'),(218,'approved','zikulausersmodule_admin_exporter','ZikulaUsersModule','admin','exporter','/admin/export','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:68:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::exporterAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,217,'5',0,0,'2016-08-11 19:11:08','2016-08-11 19:11:28'),(219,'approved','zikulausersmodule_admin_toggleforcedpasswordchange','ZikulaUsersModule','admin','toggleforcedpasswordchange','/admin/forcepasswordchange','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:86:\"Zikula\\Module\\UsersModule\\Controller\\AdminController::toggleForcedPasswordChangeAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,218,'5',0,0,'2016-08-11 19:11:09','2016-08-11 19:11:28'),(220,'approved','zikulausersmodule_ajax_getusers','ZikulaUsersModule','ajax','getusers','/ajax/getusers','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:67:\"Zikula\\Module\\UsersModule\\Controller\\AjaxController::getUsersAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,219,'5',0,0,'2016-08-11 19:11:09','2016-08-11 19:11:28'),(221,'approved','zikulausersmodule_ajax_getusersastable','ZikulaUsersModule','ajax','getusersastable','/ajax/getusersastable','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:74:\"Zikula\\Module\\UsersModule\\Controller\\AjaxController::getUsersAsTableAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,220,'5',0,0,'2016-08-11 19:11:09','2016-08-11 19:11:28'),(222,'approved','zikulausersmodule_ajax_getregistrationerrors','ZikulaUsersModule','ajax','getregistrationerrors','/ajax/getregistrationerrors','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:80:\"Zikula\\Module\\UsersModule\\Controller\\AjaxController::getRegistrationErrorsAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,221,'5',0,0,'2016-08-11 19:11:09','2016-08-11 19:11:28'),(223,'approved','zikulausersmodule_ajax_getloginformfields','ZikulaUsersModule','ajax','getloginformfields','/ajax/getloginformfields','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:77:\"Zikula\\Module\\UsersModule\\Controller\\AjaxController::getLoginFormFieldsAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,222,'5',0,0,'2016-08-11 19:11:09','2016-08-11 19:11:28'),(224,'approved','zikulausersmodule_user_index','ZikulaUsersModule','user','index','/useraccount','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:64:\"Zikula\\Module\\UsersModule\\Controller\\UserController::indexAction\";}','a:0:{}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:16:\"zkNoBundlePrefix\";i:1;}','','',0,223,'5',0,0,'2016-08-11 19:11:10','2016-08-11 19:11:28'),(225,'approved','zikulausersmodule_user_view','ZikulaUsersModule','user','view','/view','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:63:\"Zikula\\Module\\UsersModule\\Controller\\UserController::viewAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,224,'5',0,0,'2016-08-11 19:11:10','2016-08-11 19:11:28'),(226,'approved','zikulausersmodule_user_register','ZikulaUsersModule','user','register','/register','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:67:\"Zikula\\Module\\UsersModule\\Controller\\UserController::registerAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:16:\"zkNoBundlePrefix\";i:1;}','','',0,225,'5',0,0,'2016-08-11 19:11:10','2016-08-11 19:11:28'),(227,'approved','zikulausersmodule_user_lostpwduname','ZikulaUsersModule','user','lostpwduname','/lost-account-details','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\UsersModule\\Controller\\UserController::lostPwdUnameAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,226,'5',0,0,'2016-08-11 19:11:10','2016-08-11 19:11:28'),(228,'approved','zikulausersmodule_user_lostuname','ZikulaUsersModule','user','lostuname','/lost-username','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:68:\"Zikula\\Module\\UsersModule\\Controller\\UserController::lostUnameAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,227,'5',0,0,'2016-08-11 19:11:11','2016-08-11 19:11:28'),(229,'approved','zikulausersmodule_user_lostpassword','ZikulaUsersModule','user','lostpassword','/lost-password','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:71:\"Zikula\\Module\\UsersModule\\Controller\\UserController::lostPasswordAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,228,'5',0,0,'2016-08-11 19:11:11','2016-08-11 19:11:28'),(230,'approved','zikulausersmodule_user_lostpasswordcode','ZikulaUsersModule','user','lostpasswordcode','/lost-password/code','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\UsersModule\\Controller\\UserController::lostPasswordCodeAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,229,'5',0,0,'2016-08-11 19:11:11','2016-08-11 19:11:28'),(231,'approved','zikulausersmodule_user_login','ZikulaUsersModule','user','login','/login','','a:0:{}','a:1:{i:0;s:8:\"GET|POST\";}','a:1:{s:11:\"_controller\";s:64:\"Zikula\\Module\\UsersModule\\Controller\\UserController::loginAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:16:\"zkNoBundlePrefix\";i:1;}','','',0,230,'5',0,0,'2016-08-11 19:11:11','2016-08-11 19:11:28'),(232,'approved','zikulausersmodule_user_logout','ZikulaUsersModule','user','logout','/logout','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:65:\"Zikula\\Module\\UsersModule\\Controller\\UserController::logoutAction\";}','a:0:{}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:16:\"zkNoBundlePrefix\";i:1;}','','',0,231,'5',0,0,'2016-08-11 19:11:11','2016-08-11 19:11:28'),(233,'approved','zikulausersmodule_user_verifyregistration','ZikulaUsersModule','user','verifyregistration','/verify-registration','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:77:\"Zikula\\Module\\UsersModule\\Controller\\UserController::verifyRegistrationAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,232,'5',0,0,'2016-08-11 19:11:12','2016-08-11 19:11:28'),(234,'approved','zikulausersmodule_user_usersblock','ZikulaUsersModule','user','usersblock','/usersblock','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:69:\"Zikula\\Module\\UsersModule\\Controller\\UserController::usersBlockAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,233,'5',0,0,'2016-08-11 19:11:12','2016-08-11 19:11:28'),(235,'approved','zikulausersmodule_user_updateusersblock','ZikulaUsersModule','user','updateusersblock','/updateusersblock','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\Module\\UsersModule\\Controller\\UserController::updateUsersBlockAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,234,'5',0,0,'2016-08-11 19:11:12','2016-08-11 19:11:28'),(236,'approved','zikulausersmodule_user_changepassword','ZikulaUsersModule','user','changepassword','/password','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\UsersModule\\Controller\\UserController::changePasswordAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,235,'5',0,0,'2016-08-11 19:11:12','2016-08-11 19:11:28'),(237,'approved','zikulausersmodule_user_updatepassword','ZikulaUsersModule','user','updatepassword','/password/update','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:73:\"Zikula\\Module\\UsersModule\\Controller\\UserController::updatePasswordAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,236,'5',0,0,'2016-08-11 19:11:13','2016-08-11 19:11:28'),(238,'approved','zikulausersmodule_user_changeemail','ZikulaUsersModule','user','changeemail','/email','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\Module\\UsersModule\\Controller\\UserController::changeEmailAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,237,'5',0,0,'2016-08-11 19:11:13','2016-08-11 19:11:28'),(239,'approved','zikulausersmodule_user_updateemail','ZikulaUsersModule','user','updateemail','/email/update','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\Module\\UsersModule\\Controller\\UserController::updateEmailAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,238,'5',0,0,'2016-08-11 19:11:13','2016-08-11 19:11:28'),(240,'approved','zikulausersmodule_user_changelang','ZikulaUsersModule','user','changelang','/lang','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:69:\"Zikula\\Module\\UsersModule\\Controller\\UserController::changeLangAction\";}','a:0:{}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,239,'5',0,0,'2016-08-11 19:11:13','2016-08-11 19:11:28'),(241,'approved','zikulausersmodule_user_confirmchemail','ZikulaUsersModule','user','confirmchemail','/email/confirm/{confirmcode}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:11:\"confirmcode\";N;s:11:\"_controller\";s:73:\"Zikula\\Module\\UsersModule\\Controller\\UserController::confirmChEmailAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,240,'5',0,0,'2016-08-11 19:11:13','2016-08-11 19:11:28'),(242,'approved','zikularoutesmodule_ajax_getitemlistfinder','ZikulaRoutesModule','ajax','getitemlistfinder','/%zikularoutesmodule.routing.ajax%/getItemListFinder','','a:0:{}','a:0:{}','a:1:{s:11:\"_controller\";s:70:\"Zikula\\RoutesModule\\Controller\\AjaxController::getItemListFinderAction\";}','a:0:{}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,241,'5',0,0,'2016-08-11 19:11:14','2016-08-11 19:11:28'),(243,'approved','zikularoutesmodule_external_display','ZikulaRoutesModule','external','display','/%zikularoutesmodule.routing.external%/display/{ot}/{id}/{source}/{displayMode}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:3:{s:6:\"source\";s:11:\"contentType\";s:11:\"contentType\";s:5:\"embed\";s:11:\"_controller\";s:64:\"Zikula\\RoutesModule\\Controller\\ExternalController::displayAction\";}','a:4:{s:2:\"id\";s:3:\"\\d+\";s:6:\"source\";s:20:\"contentType|scribite\";s:11:\"displayMode\";s:10:\"link|embed\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,242,'5',0,0,'2016-08-11 19:11:14','2016-08-11 19:11:28'),(244,'approved','zikularoutesmodule_external_finder','ZikulaRoutesModule','external','finder','/%zikularoutesmodule.routing.external%/finder/{objectType}/{editor}/{sort}/{sortdir}/{pos}/{num}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:5:{s:4:\"sort\";s:0:\"\";s:7:\"sortdir\";s:3:\"asc\";s:3:\"pos\";i:1;s:3:\"num\";i:0;s:11:\"_controller\";s:63:\"Zikula\\RoutesModule\\Controller\\ExternalController::finderAction\";}','a:5:{s:6:\"editor\";s:22:\"xinha|tinymce|ckeditor\";s:7:\"sortdir\";s:8:\"asc|desc\";s:3:\"pos\";s:3:\"\\d+\";s:3:\"num\";s:3:\"\\d+\";s:7:\"_method\";s:3:\"GET\";}','a:2:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:6:\"expose\";b:1;}','','',0,243,'5',0,0,'2016-08-11 19:11:14','2016-08-11 19:11:28'),(245,'approved','zikularoutesmodule_route_index','ZikulaRoutesModule','route','index','/%zikularoutesmodule.routing.route.plural%','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:59:\"Zikula\\RoutesModule\\Controller\\RouteController::indexAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,244,'5',0,0,'2016-08-11 19:11:15','2016-08-11 19:11:28'),(246,'approved','zikularoutesmodule_route_view','ZikulaRoutesModule','route','view','/%zikularoutesmodule.routing.route.plural%/%zikularoutesmodule.routing.view.suffix%/{sort}/{sortdir}/{pos}/{num}.{_format}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:6:{s:4:\"sort\";s:0:\"\";s:7:\"sortdir\";s:3:\"asc\";s:3:\"pos\";i:1;s:3:\"num\";i:0;s:7:\"_format\";s:4:\"html\";s:11:\"_controller\";s:58:\"Zikula\\RoutesModule\\Controller\\RouteController::viewAction\";}','a:5:{s:7:\"sortdir\";s:17:\"asc|desc|ASC|DESC\";s:3:\"pos\";s:3:\"\\d+\";s:3:\"num\";s:3:\"\\d+\";s:7:\"_format\";s:41:\"%zikularoutesmodule.routing.formats.view%\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,245,'5',0,0,'2016-08-11 19:11:15','2016-08-11 19:11:28'),(247,'approved','zikularoutesmodule_route_edit','ZikulaRoutesModule','route','edit','/%zikularoutesmodule.routing.route.singular%/edit/{id}.{_format}','','a:0:{}','a:2:{i:0;s:3:\"GET\";i:1;s:4:\"POST\";}','a:3:{s:2:\"id\";s:1:\"0\";s:7:\"_format\";s:4:\"html\";s:11:\"_controller\";s:58:\"Zikula\\RoutesModule\\Controller\\RouteController::editAction\";}','a:3:{s:2:\"id\";s:3:\"\\d+\";s:7:\"_format\";s:4:\"html\";s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,246,'5',0,0,'2016-08-11 19:11:15','2016-08-11 19:11:28'),(248,'approved','zikularoutesmodule_route_display','ZikulaRoutesModule','route','display','/%zikularoutesmodule.routing.route.singular%/{id}.{_format}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:7:\"_format\";s:4:\"html\";s:11:\"_controller\";s:61:\"Zikula\\RoutesModule\\Controller\\RouteController::displayAction\";}','a:3:{s:2:\"id\";s:3:\"\\d+\";s:7:\"_format\";s:44:\"%zikularoutesmodule.routing.formats.display%\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,247,'5',0,0,'2016-08-11 19:11:15','2016-08-11 19:11:28'),(249,'approved','zikularoutesmodule_route_delete','ZikulaRoutesModule','route','delete','/%zikularoutesmodule.routing.route.singular%/delete/{id}.{_format}','','a:0:{}','a:2:{i:0;s:3:\"GET\";i:1;s:4:\"POST\";}','a:2:{s:7:\"_format\";s:4:\"html\";s:11:\"_controller\";s:60:\"Zikula\\RoutesModule\\Controller\\RouteController::deleteAction\";}','a:3:{s:2:\"id\";s:3:\"\\d+\";s:7:\"_format\";s:4:\"html\";s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,248,'5',0,0,'2016-08-11 19:11:15','2016-08-11 19:11:28'),(250,'approved','zikularoutesmodule_route_reload','ZikulaRoutesModule','route','reload','/%zikularoutesmodule.routing.route.plural%/reload/{stage}/{module}','','a:0:{}','a:2:{i:0;s:3:\"GET\";i:1;s:4:\"POST\";}','a:3:{s:5:\"stage\";i:0;s:6:\"module\";N;s:11:\"_controller\";s:60:\"Zikula\\RoutesModule\\Controller\\RouteController::reloadAction\";}','a:1:{s:7:\"_method\";s:8:\"GET|POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,249,'5',0,0,'2016-08-11 19:11:16','2016-08-11 19:11:28'),(251,'approved','zikularoutesmodule_route_renew','ZikulaRoutesModule','route','renew','/%zikularoutesmodule.routing.route.plural%/renew','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:59:\"Zikula\\RoutesModule\\Controller\\RouteController::renewAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,250,'5',0,0,'2016-08-11 19:11:16','2016-08-11 19:11:28'),(252,'approved','zikularoutesmodule_route_dumpjsroutes','ZikulaRoutesModule','route','dumpjsroutes','/%zikularoutesmodule.routing.route.plural%/dump/{lang}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:2:{s:4:\"lang\";N;s:11:\"_controller\";s:66:\"Zikula\\RoutesModule\\Controller\\RouteController::dumpJsRoutesAction\";}','a:1:{s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,251,'5',0,0,'2016-08-11 19:11:16','2016-08-11 19:11:28'),(253,'approved','zikularoutesmodule_route_handleselectedentries','ZikulaRoutesModule','route','handleSelectedEntries','/%zikularoutesmodule.routing.route.plural%/handleSelectedEntries','','a:0:{}','a:1:{i:0;s:4:\"POST\";}','a:1:{s:11:\"_controller\";s:75:\"Zikula\\RoutesModule\\Controller\\RouteController::handleSelectedEntriesAction\";}','a:1:{s:7:\"_method\";s:4:\"POST\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,252,'5',0,0,'2016-08-11 19:11:16','2016-08-11 19:11:28'),(254,'approved','zikularoutesmodule_route_handleinlineredirect','ZikulaRoutesModule','route','handleInlineRedirect','/%zikularoutesmodule.routing.route.singular%/handleInlineRedirect/{idPrefix}/{commandName}/{id}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:3:{s:11:\"commandName\";s:0:\"\";s:2:\"id\";i:0;s:11:\"_controller\";s:74:\"Zikula\\RoutesModule\\Controller\\RouteController::handleInlineRedirectAction\";}','a:2:{s:2:\"id\";s:3:\"\\d+\";s:7:\"_method\";s:3:\"GET\";}','a:1:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";}','','',0,253,'5',0,0,'2016-08-11 19:11:17','2016-08-11 19:11:28'),(255,'approved','zikularoutesmodule_redirectingcontroller_removetrailingslash','ZikulaRoutesModule','redirectingcontroller','removetrailingslash','/{url}','','a:0:{}','a:1:{i:0;s:3:\"GET\";}','a:1:{s:11:\"_controller\";s:79:\"Zikula\\RoutesModule\\Controller\\RedirectingController::removeTrailingSlashAction\";}','a:2:{s:3:\"url\";s:3:\".*/\";s:7:\"_method\";s:3:\"GET\";}','a:5:{s:14:\"compiler_class\";s:39:\"Symfony\\Component\\Routing\\RouteCompiler\";s:13:\"zkDescription\";s:142:\"The goal of this route is to redirect URLs with a trailing slash to the same URL without a trailing slash (for example /en/blog/ to /en/blog).\";s:16:\"zkNoBundlePrefix\";b:1;s:10:\"zkPosition\";s:6:\"bottom\";s:4:\"i18n\";b:0;}','','The goal of this route is to redirect URLs with a trailing slash to the same URL without a trailing slash (for example /en/blog/ to /en/blog).',0,0,'7',0,0,'2016-08-11 19:11:17','2016-08-11 19:11:28');
/*!40000 ALTER TABLE `zikula_routes_route` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-08-11 19:14:39
