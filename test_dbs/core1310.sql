-- MySQL dump 10.13  Distrib 5.5.42, for osx10.6 (i386)
--
-- Host: localhost    Database: 1310
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
  `name` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `sortorder` int(11) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
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
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_module`
--

LOCK TABLES `admin_module` WRITE;
/*!40000 ALTER TABLE `admin_module` DISABLE KEYS */;
INSERT INTO `admin_module` VALUES (1,1,1,0),(2,12,1,1),(3,13,2,0),(4,2,1,2),(5,9,3,0),(6,6,3,1),(7,3,2,1),(8,14,3,2),(9,11,6,0),(10,4,4,0),(11,15,4,1),(12,7,1,3),(13,5,1,4),(14,10,4,2);
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `block_placements`
--

LOCK TABLES `block_placements` WRITE;
/*!40000 ALTER TABLE `block_placements` DISABLE KEYS */;
INSERT INTO `block_placements` VALUES (1,1,0),(4,2,0),(3,3,0),(2,4,0),(7,5,0);
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
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`pid`),
  KEY `name_idx` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
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
  `bkey` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `content` longtext NOT NULL,
  `url` longtext NOT NULL,
  `mid` int(11) NOT NULL,
  `filter` longtext NOT NULL COMMENT '(DC2Type:array)',
  `active` int(11) NOT NULL,
  `collapsable` int(11) NOT NULL,
  `defaultstate` int(11) NOT NULL,
  `refresh` int(11) NOT NULL,
  `last_update` datetime NOT NULL,
  `language` varchar(30) NOT NULL,
  PRIMARY KEY (`bid`),
  KEY `active_idx` (`active`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blocks`
--

LOCK TABLES `blocks` WRITE;
/*!40000 ALTER TABLE `blocks` DISABLE KEYS */;
INSERT INTO `blocks` VALUES (1,'Extmenu','Main menu','Main menu','a:5:{s:14:\"displaymodules\";s:1:\"0\";s:10:\"stylesheet\";s:11:\"extmenu.css\";s:8:\"template\";s:24:\"blocks_block_extmenu.tpl\";s:11:\"blocktitles\";a:1:{s:2:\"en\";s:9:\"Main menu\";}s:5:\"links\";a:1:{s:2:\"en\";a:5:{i:0;a:7:{s:4:\"name\";s:4:\"Home\";s:3:\"url\";s:10:\"{homepage}\";s:5:\"title\";s:19:\"Go to the home page\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:1;a:7:{s:4:\"name\";s:14:\"Administration\";s:3:\"url\";s:24:\"{Admin:admin:adminpanel}\";s:5:\"title\";s:29:\"Go to the site administration\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:2;a:7:{s:4:\"name\";s:10:\"My Account\";s:3:\"url\";s:7:\"{Users}\";s:5:\"title\";s:24:\"Go to your account panel\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:3;a:7:{s:4:\"name\";s:7:\"Log out\";s:3:\"url\";s:19:\"{Users:user:logout}\";s:5:\"title\";s:20:\"Log out of this site\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:4;a:7:{s:4:\"name\";s:11:\"Site search\";s:3:\"url\";s:8:\"{Search}\";s:5:\"title\";s:16:\"Search this site\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}}}}','',3,'a:0:{}',1,1,1,3600,'2016-08-13 16:21:53',''),(2,'Search','Search box','Search block','a:2:{s:16:\"displaySearchBtn\";i:1;s:6:\"active\";a:1:{s:5:\"Users\";i:1;}}','',10,'a:0:{}',1,1,1,3600,'2016-08-13 16:21:53',''),(3,'Html','This site is powered by Zikula!','HTML block','<p><a href=\"http://zikula.org/\">Zikula</a> is a content management system (CMS) and application framework. It is secure and stable, and is a good choice for sites with a large volume of traffic.</p><p>With Zikula:</p><ul><li>you can customise all aspects of the site\'s appearance through themes, with support for CSS style sheets, JavaScript, Flash and all other modern web development technologies;</li><li>you can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation and internationalisation of your site;</li><li>you can be sure that your pages will display properly in all browsers, thanks to Zikula\'s full compliance with W3C HTML standards;</li><li>you get a standard application-programming interface (API) that lets you easily augment your site\'s functionality through modules, blocks and other extensions;</li><li>you can get help and support from the Zikula community of webmasters and developers at <a href=\"http://www.zikula.org\">zikula.org</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>','',3,'a:0:{}',1,1,1,3600,'2016-08-13 16:21:53',''),(4,'Login','User log-in','Login block','','',14,'a:0:{}',1,1,1,3600,'2016-08-13 16:21:53',''),(5,'Extmenu','Top navigation','Theme navigation','a:5:{s:14:\"displaymodules\";s:1:\"0\";s:10:\"stylesheet\";s:11:\"extmenu.css\";s:8:\"template\";s:31:\"blocks_block_extmenu_topnav.tpl\";s:11:\"blocktitles\";a:1:{s:2:\"en\";s:14:\"Top navigation\";}s:5:\"links\";a:1:{s:2:\"en\";a:3:{i:0;a:7:{s:4:\"name\";s:4:\"Home\";s:3:\"url\";s:10:\"{homepage}\";s:5:\"title\";s:26:\"Go to the site\'s home page\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:1;a:7:{s:4:\"name\";s:10:\"My Account\";s:3:\"url\";s:7:\"{Users}\";s:5:\"title\";s:24:\"Go to your account panel\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:2;a:7:{s:4:\"name\";s:11:\"Site search\";s:3:\"url\";s:8:\"{Search}\";s:5:\"title\";s:16:\"Search this site\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}}}}','',3,'a:0:{}',1,1,1,3600,'2016-08-13 16:21:53','');
/*!40000 ALTER TABLE `blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories_category`
--

DROP TABLE IF EXISTS `categories_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '1',
  `is_locked` tinyint(4) NOT NULL DEFAULT '0',
  `is_leaf` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `sort_value` int(11) NOT NULL DEFAULT '2147483647',
  `display_name` longtext NOT NULL,
  `display_desc` longtext NOT NULL,
  `path` longtext NOT NULL,
  `ipath` varchar(255) NOT NULL,
  `status` varchar(1) NOT NULL DEFAULT 'A',
  `obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cr_uid` int(11) NOT NULL DEFAULT '0',
  `lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_categories_parent` (`parent_id`),
  KEY `idx_categories_is_leaf` (`is_leaf`),
  KEY `idx_categories_name` (`name`),
  KEY `idx_categories_ipath` (`ipath`,`is_leaf`,`status`),
  KEY `idx_categories_status` (`status`),
  KEY `idx_categories_ipath_status` (`ipath`,`status`)
) ENGINE=MyISAM AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories_category`
--

LOCK TABLES `categories_category` WRITE;
/*!40000 ALTER TABLE `categories_category` DISABLE KEYS */;
INSERT INTO `categories_category` VALUES (1,0,1,0,'__SYSTEM__','',1,'b:0;','b:0;','/__SYSTEM__','/1','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(2,1,0,0,'Modules','',2,'a:1:{s:2:\"en\";s:7:\"Modules\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules','/1/2','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(3,1,0,0,'General','',3,'a:1:{s:2:\"en\";s:7:\"General\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General','/1/3','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(4,3,0,0,'YesNo','',4,'a:1:{s:2:\"en\";s:6:\"Yes/No\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/YesNo','/1/3/4','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(5,4,0,1,'1 - Yes','Y',5,'b:0;','b:0;','/__SYSTEM__/General/YesNo/1 - Yes','/1/3/4/5','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(6,4,0,1,'2 - No','N',6,'b:0;','b:0;','/__SYSTEM__/General/YesNo/2 - No','/1/3/4/6','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(10,3,0,0,'Publication Status (extended)','',10,'a:1:{s:2:\"en\";s:29:\"Publication status (extended)\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended','/1/3/10','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(11,10,0,1,'Pending','P',11,'a:1:{s:2:\"en\";s:7:\"Pending\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Pending','/1/3/10/11','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(12,10,0,1,'Checked','C',12,'a:1:{s:2:\"en\";s:7:\"Checked\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Checked','/1/3/10/12','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(13,10,0,1,'Approved','A',13,'a:1:{s:2:\"en\";s:8:\"Approved\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Approved','/1/3/10/13','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(14,10,0,1,'On-line','O',14,'a:1:{s:2:\"en\";s:7:\"On-line\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Online','/1/3/10/14','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(15,10,0,1,'Rejected','R',15,'a:1:{s:2:\"en\";s:8:\"Rejected\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Rejected','/1/3/10/15','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(16,3,0,0,'Gender','',16,'a:1:{s:2:\"en\";s:6:\"Gender\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Gender','/1/3/16','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(17,16,0,1,'Male','M',17,'a:1:{s:2:\"en\";s:4:\"Male\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Gender/Male','/1/3/16/17','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(18,16,0,1,'Female','F',18,'a:1:{s:2:\"en\";s:6:\"Female\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Gender/Female','/1/3/16/18','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(19,3,0,0,'Title','',19,'a:1:{s:2:\"en\";s:5:\"Title\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title','/1/3/19','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(20,19,0,1,'Mr','Mr',20,'a:1:{s:2:\"en\";s:3:\"Mr.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Mr','/1/3/19/20','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(21,19,0,1,'Mrs','Mrs',21,'a:1:{s:2:\"en\";s:4:\"Mrs.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Mrs','/1/3/19/21','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(22,19,0,1,'Ms','Ms',22,'a:1:{s:2:\"en\";s:3:\"Ms.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Ms','/1/3/19/22','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(23,19,0,1,'Miss','Miss',23,'a:1:{s:2:\"en\";s:4:\"Miss\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Miss','/1/3/19/23','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(24,19,0,1,'Dr','Dr',24,'a:1:{s:2:\"en\";s:3:\"Dr.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Dr','/1/3/19/24','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(25,3,0,0,'ActiveStatus','',25,'a:1:{s:2:\"en\";s:15:\"Activity status\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus','/1/3/25','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(26,25,0,1,'Active','A',26,'a:1:{s:2:\"en\";s:6:\"Active\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus/Active','/1/3/25/26','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(27,25,0,1,'Inactive','I',27,'a:1:{s:2:\"en\";s:8:\"Inactive\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus/Inactive','/1/3/25/27','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(28,3,0,0,'Publication status (basic)','',28,'a:1:{s:2:\"en\";s:26:\"Publication status (basic)\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic','/1/3/28','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(29,28,0,1,'Pending','P',29,'a:1:{s:2:\"en\";s:7:\"Pending\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic/Pending','/1/3/28/29','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(30,28,0,1,'Approved','A',30,'a:1:{s:2:\"en\";s:8:\"Approved\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic/Approved','/1/3/28/30','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(31,1,0,0,'Users','',31,'a:1:{s:2:\"en\";s:5:\"Users\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Users','/1/31','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(32,2,0,0,'Global','',32,'a:1:{s:2:\"en\";s:6:\"Global\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global','/1/2/32','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(33,32,0,1,'Blogging','',33,'a:1:{s:2:\"en\";s:8:\"Blogging\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/Blogging','/1/2/32/33','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(34,32,0,1,'Music and audio','',34,'a:1:{s:2:\"en\";s:15:\"Music and audio\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/MusicAndAudio','/1/2/32/34','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(35,32,0,1,'Art and photography','',35,'a:1:{s:2:\"en\";s:19:\"Art and photography\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ArtAndPhotography','/1/2/32/35','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(36,32,0,1,'Writing and thinking','',36,'a:1:{s:2:\"en\";s:20:\"Writing and thinking\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/WritingAndThinking','/1/2/32/36','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(37,32,0,1,'Communications and media','',37,'a:1:{s:2:\"en\";s:24:\"Communications and media\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/CommunicationsAndMedia','/1/2/32/37','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(38,32,0,1,'Travel and culture','',38,'a:1:{s:2:\"en\";s:18:\"Travel and culture\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/TravelAndCulture','/1/2/32/38','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(39,32,0,1,'Science and technology','',39,'a:1:{s:2:\"en\";s:22:\"Science and technology\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ScienceAndTechnology','/1/2/32/39','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(40,32,0,1,'Sport and activities','',40,'a:1:{s:2:\"en\";s:20:\"Sport and activities\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/SportAndActivities','/1/2/32/40','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(41,32,0,1,'Business and work','',41,'a:1:{s:2:\"en\";s:17:\"Business and work\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/BusinessAndWork','/1/2/32/41','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0);
/*!40000 ALTER TABLE `categories_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories_mapmeta`
--

DROP TABLE IF EXISTS `categories_mapmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories_mapmeta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meta_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cr_uid` int(11) NOT NULL DEFAULT '0',
  `lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_categories_mapmeta` (`meta_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories_mapmeta`
--

LOCK TABLES `categories_mapmeta` WRITE;
/*!40000 ALTER TABLE `categories_mapmeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories_mapmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories_mapobj`
--

DROP TABLE IF EXISTS `categories_mapobj`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories_mapobj` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modname` varchar(60) NOT NULL,
  `tablename` varchar(60) NOT NULL,
  `obj_id` int(11) NOT NULL DEFAULT '0',
  `obj_idcolumn` varchar(60) NOT NULL DEFAULT 'id',
  `reg_id` int(11) NOT NULL DEFAULT '0',
  `reg_property` varchar(60) NOT NULL,
  `category_id` int(11) NOT NULL DEFAULT '0',
  `obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cr_uid` int(11) NOT NULL DEFAULT '0',
  `lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_categories_mapobj` (`modname`,`tablename`,`obj_id`,`obj_idcolumn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `modname` varchar(60) NOT NULL,
  `tablename` varchar(60) NOT NULL,
  `property` varchar(60) NOT NULL,
  `category_id` int(11) NOT NULL DEFAULT '0',
  `obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cr_uid` int(11) NOT NULL DEFAULT '0',
  `lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_categories_registry` (`modname`,`tablename`,`property`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `uid` int(11) NOT NULL DEFAULT '0',
  `gid` int(11) NOT NULL DEFAULT '0',
  `application` longtext NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`app_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `gid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  KEY `gid_uid` (`uid`,`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `gid` int(11) NOT NULL DEFAULT '0',
  `sequence` int(11) NOT NULL DEFAULT '0',
  `realm` int(11) NOT NULL DEFAULT '0',
  `component` varchar(255) NOT NULL,
  `instance` varchar(255) NOT NULL,
  `level` int(11) NOT NULL DEFAULT '0',
  `bond` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
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
  `name` varchar(255) NOT NULL,
  `gtype` tinyint(4) NOT NULL DEFAULT '0',
  `description` varchar(200) NOT NULL,
  `prefix` varchar(25) NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '0',
  `nbuser` int(11) NOT NULL DEFAULT '0',
  `nbumax` int(11) NOT NULL DEFAULT '0',
  `link` int(11) NOT NULL DEFAULT '0',
  `uidmaster` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
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
  `owner` varchar(40) NOT NULL,
  `subowner` varchar(40) DEFAULT NULL,
  `areatype` varchar(1) NOT NULL,
  `category` varchar(20) NOT NULL,
  `areaname` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `areaidx` (`areaname`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hook_area`
--

LOCK TABLES `hook_area` WRITE;
/*!40000 ALTER TABLE `hook_area` DISABLE KEYS */;
INSERT INTO `hook_area` VALUES (1,'Blocks',NULL,'s','ui_hooks','subscriber.blocks.ui_hooks.htmlblock.content'),(2,'Users',NULL,'s','ui_hooks','subscriber.users.ui_hooks.user'),(3,'Users',NULL,'s','ui_hooks','subscriber.users.ui_hooks.registration'),(4,'Users',NULL,'s','ui_hooks','subscriber.users.ui_hooks.login_screen'),(5,'Users',NULL,'s','ui_hooks','subscriber.users.ui_hooks.login_block');
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
  `sowner` varchar(40) NOT NULL,
  `subsowner` varchar(40) DEFAULT NULL,
  `powner` varchar(40) NOT NULL,
  `subpowner` varchar(40) DEFAULT NULL,
  `sareaid` int(11) NOT NULL,
  `pareaid` int(11) NOT NULL,
  `category` varchar(20) NOT NULL,
  `sortorder` smallint(6) NOT NULL DEFAULT '999',
  PRIMARY KEY (`id`),
  KEY `sortidx` (`sareaid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `owner` varchar(40) NOT NULL,
  `subowner` varchar(40) DEFAULT NULL,
  `pareaid` int(11) NOT NULL,
  `hooktype` varchar(20) NOT NULL,
  `category` varchar(20) NOT NULL,
  `classname` varchar(60) NOT NULL,
  `method` varchar(20) NOT NULL,
  `serviceid` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nameidx` (`pareaid`,`hooktype`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `sowner` varchar(40) NOT NULL,
  `subsowner` varchar(40) DEFAULT NULL,
  `powner` varchar(40) NOT NULL,
  `subpowner` varchar(40) DEFAULT NULL,
  `sareaid` int(11) NOT NULL,
  `pareaid` int(11) NOT NULL,
  `eventname` varchar(100) NOT NULL,
  `classname` varchar(60) NOT NULL,
  `method` varchar(20) NOT NULL,
  `serviceid` varchar(60) DEFAULT NULL,
  `priority` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `owner` varchar(40) NOT NULL,
  `subowner` varchar(40) DEFAULT NULL,
  `sareaid` int(11) NOT NULL,
  `hooktype` varchar(20) NOT NULL,
  `category` varchar(20) NOT NULL,
  `eventname` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `myindex` (`eventname`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hook_subscriber`
--

LOCK TABLES `hook_subscriber` WRITE;
/*!40000 ALTER TABLE `hook_subscriber` DISABLE KEYS */;
INSERT INTO `hook_subscriber` VALUES (1,'Blocks',NULL,1,'form_edit','ui_hooks','blocks.ui_hooks.htmlblock.content.form_edit'),(2,'Users',NULL,2,'display_view','ui_hooks','users.ui_hooks.user.display_view'),(3,'Users',NULL,2,'form_edit','ui_hooks','users.ui_hooks.user.form_edit'),(4,'Users',NULL,2,'validate_edit','ui_hooks','users.ui_hooks.user.validate_edit'),(5,'Users',NULL,2,'process_edit','ui_hooks','users.ui_hooks.user.process_edit'),(6,'Users',NULL,2,'form_delete','ui_hooks','users.ui_hooks.user.form_delete'),(7,'Users',NULL,2,'validate_delete','ui_hooks','users.ui_hooks.user.validate_delete'),(8,'Users',NULL,2,'process_delete','ui_hooks','users.ui_hooks.user.process_delete'),(9,'Users',NULL,3,'display_view','ui_hooks','users.ui_hooks.registration.display_view'),(10,'Users',NULL,3,'form_edit','ui_hooks','users.ui_hooks.registration.form_edit'),(11,'Users',NULL,3,'validate_edit','ui_hooks','users.ui_hooks.registration.validate_edit'),(12,'Users',NULL,3,'process_edit','ui_hooks','users.ui_hooks.registration.process_edit'),(13,'Users',NULL,3,'form_delete','ui_hooks','users.ui_hooks.registration.form_delete'),(14,'Users',NULL,3,'validate_delete','ui_hooks','users.ui_hooks.registration.validate_delete'),(15,'Users',NULL,3,'process_delete','ui_hooks','users.ui_hooks.registration.process_delete'),(16,'Users',NULL,4,'form_edit','ui_hooks','users.ui_hooks.login_screen.form_edit'),(17,'Users',NULL,4,'validate_edit','ui_hooks','users.ui_hooks.login_screen.validate_edit'),(18,'Users',NULL,4,'process_edit','ui_hooks','users.ui_hooks.login_screen.process_edit'),(19,'Users',NULL,5,'form_edit','ui_hooks','users.ui_hooks.login_block.form_edit'),(20,'Users',NULL,5,'validate_edit','ui_hooks','users.ui_hooks.login_block.validate_edit'),(21,'Users',NULL,5,'process_edit','ui_hooks','users.ui_hooks.login_block.process_edit');
/*!40000 ALTER TABLE `hook_subscriber` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hooks`
--

DROP TABLE IF EXISTS `hooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(64) NOT NULL,
  `action` varchar(64) NOT NULL,
  `smodule` varchar(64) NOT NULL,
  `stype` varchar(64) NOT NULL,
  `tarea` varchar(64) NOT NULL,
  `tmodule` varchar(64) NOT NULL,
  `ttype` varchar(64) NOT NULL,
  `tfunc` varchar(64) NOT NULL,
  `sequence` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `smodule` (`smodule`),
  KEY `smodule_tmodule` (`smodule`,`tmodule`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hooks`
--

LOCK TABLES `hooks` WRITE;
/*!40000 ALTER TABLE `hooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `hooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_deps`
--

DROP TABLE IF EXISTS `module_deps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_deps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modid` int(11) NOT NULL DEFAULT '0',
  `modname` varchar(64) NOT NULL,
  `minversion` varchar(10) NOT NULL,
  `maxversion` varchar(10) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
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
  `modname` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `value` longtext,
  PRIMARY KEY (`id`),
  KEY `mod_var` (`modname`,`name`)
) ENGINE=MyISAM AUTO_INCREMENT=225 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_vars`
--

LOCK TABLES `module_vars` WRITE;
/*!40000 ALTER TABLE `module_vars` DISABLE KEYS */;
INSERT INTO `module_vars` VALUES (1,'/EventHandlers','Extensions','a:2:{i:0;a:3:{s:9:\"eventname\";s:27:\"controller.method_not_found\";s:8:\"callable\";a:2:{i:0;s:17:\"Extensions_HookUI\";i:1;s:5:\"hooks\";}s:6:\"weight\";i:10;}i:1;a:3:{s:9:\"eventname\";s:27:\"controller.method_not_found\";s:8:\"callable\";a:2:{i:0;s:17:\"Extensions_HookUI\";i:1;s:14:\"moduleservices\";}s:6:\"weight\";i:10;}}'),(2,'Extensions','itemsperpage','i:25;'),(3,'ZConfig','debug','s:1:\"0\";'),(4,'ZConfig','startdate','s:7:\"08/2016\";'),(5,'ZConfig','adminmail','s:17:\"admin@example.com\";'),(6,'ZConfig','Default_Theme','s:9:\"Andreas08\";'),(7,'ZConfig','timezone_offset','s:1:\"0\";'),(8,'ZConfig','timezone_server','s:1:\"0\";'),(9,'ZConfig','funtext','s:1:\"1\";'),(10,'ZConfig','reportlevel','s:1:\"0\";'),(11,'ZConfig','startpage','s:0:\"\";'),(12,'ZConfig','Version_Num','s:6:\"1.3.10\";'),(13,'ZConfig','Version_ID','s:6:\"Zikula\";'),(14,'ZConfig','Version_Sub','s:3:\"vai\";'),(15,'ZConfig','debug_sql','s:1:\"0\";'),(16,'ZConfig','multilingual','s:1:\"1\";'),(17,'ZConfig','useflags','s:1:\"0\";'),(18,'ZConfig','theme_change','s:1:\"0\";'),(19,'ZConfig','UseCompression','s:1:\"0\";'),(20,'ZConfig','siteoff','i:0;'),(21,'ZConfig','siteoffreason','s:0:\"\";'),(22,'ZConfig','starttype','s:0:\"\";'),(23,'ZConfig','startfunc','s:0:\"\";'),(24,'ZConfig','startargs','s:0:\"\";'),(25,'ZConfig','entrypoint','s:9:\"index.php\";'),(26,'ZConfig','language_detect','i:0;'),(27,'ZConfig','shorturls','b:0;'),(28,'ZConfig','shorturlstype','s:1:\"0\";'),(29,'ZConfig','shorturlsseparator','s:1:\"-\";'),(30,'ZConfig','shorturlsstripentrypoint','b:0;'),(31,'ZConfig','shorturlsdefaultmodule','s:0:\"\";'),(32,'ZConfig','profilemodule','s:0:\"\";'),(33,'ZConfig','messagemodule','s:0:\"\";'),(34,'ZConfig','languageurl','i:0;'),(35,'ZConfig','ajaxtimeout','i:5000;'),(36,'ZConfig','permasearch','s:161:\"À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü\";'),(37,'ZConfig','permareplace','s:114:\"A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,ss,ae,Ae,oe,Oe,ue,Ue\";'),(38,'ZConfig','sitename_en','s:9:\"Site name\";'),(39,'ZConfig','slogan_en','s:16:\"Site description\";'),(40,'ZConfig','metakeywords_en','s:115:\"zikula, portal, open source, web site, website, weblog, blog, content management system, cms, application framework\";'),(41,'ZConfig','defaultpagetitle_en','s:9:\"Site name\";'),(42,'ZConfig','defaultmetadescription_en','s:16:\"Site description\";'),(43,'ZConfig','language','s:3:\"eng\";'),(44,'ZConfig','locale','s:2:\"en\";'),(45,'ZConfig','language_i18n','s:2:\"en\";'),(46,'ZConfig','idnnames','i:1;'),(47,'Theme','modulesnocache','s:0:\"\";'),(48,'Theme','enablecache','b:0;'),(49,'Theme','compile_check','b:1;'),(50,'Theme','cache_lifetime','i:1800;'),(51,'Theme','cache_lifetime_mods','i:1800;'),(52,'Theme','force_compile','b:0;'),(53,'Theme','trimwhitespace','b:0;'),(54,'Theme','maxsizeforlinks','i:30;'),(55,'Theme','itemsperpage','i:25;'),(56,'Theme','cssjscombine','b:0;'),(57,'Theme','cssjscompress','b:0;'),(58,'Theme','cssjsminify','b:0;'),(59,'Theme','cssjscombine_lifetime','i:3600;'),(60,'Theme','render_compile_check','b:1;'),(61,'Theme','render_force_compile','b:0;'),(62,'Theme','render_cache','b:1;'),(63,'Theme','render_expose_template','b:0;'),(64,'Theme','render_lifetime','i:3600;'),(65,'Admin','modulesperrow','i:3;'),(66,'Admin','itemsperpage','i:15;'),(67,'Admin','defaultcategory','i:5;'),(68,'Admin','admingraphic','i:1;'),(69,'Admin','startcategory','i:1;'),(70,'Admin','ignoreinstallercheck','i:0;'),(71,'Admin','admintheme','s:0:\"\";'),(72,'Admin','displaynametype','i:1;'),(73,'Permissions','filter','i:1;'),(74,'Permissions','warnbar','i:1;'),(75,'Permissions','rowview','i:20;'),(76,'Permissions','rowedit','i:20;'),(77,'Permissions','lockadmin','i:1;'),(78,'Permissions','adminid','i:1;'),(79,'Groups','itemsperpage','i:25;'),(80,'Groups','defaultgroup','i:1;'),(81,'Groups','mailwarning','i:0;'),(82,'Groups','hideclosed','i:0;'),(83,'Groups','primaryadmingroup','i:2;'),(84,'Blocks','collapseable','i:0;'),(85,'Users','accountdisplaygraphics','b:1;'),(86,'Users','accountitemsperpage','i:25;'),(87,'Users','accountitemsperrow','i:5;'),(88,'Users','userimg','s:11:\"images/menu\";'),(89,'Users','anonymous','s:5:\"Guest\";'),(90,'Users','avatarpath','s:13:\"images/avatar\";'),(91,'Users','chgemail_expiredays','i:0;'),(92,'Users','chgpass_expiredays','i:0;'),(93,'Users','reg_expiredays','i:0;'),(94,'Users','allowgravatars','b:1;'),(95,'Users','gravatarimage','s:12:\"gravatar.gif\";'),(96,'Users','hash_method','s:6:\"sha256\";'),(97,'Users','itemsperpage','i:25;'),(98,'Users','login_displayapproval','b:0;'),(99,'Users','login_displaydelete','b:0;'),(100,'Users','login_displayinactive','b:0;'),(101,'Users','login_displayverify','b:0;'),(102,'Users','loginviaoption','i:0;'),(103,'Users','login_redirect','b:1;'),(104,'Users','changeemail','b:1;'),(105,'Users','minpass','i:5;'),(106,'Users','use_password_strength_meter','b:0;'),(107,'Users','reg_notifyemail','s:0:\"\";'),(108,'Users','reg_question','s:0:\"\";'),(109,'Users','reg_answer','s:0:\"\";'),(110,'Users','moderation','b:0;'),(111,'Users','moderation_order','i:0;'),(112,'Users','reg_autologin','b:0;'),(113,'Users','reg_noregreasons','s:51:\"Sorry! New user registration is currently disabled.\";'),(114,'Users','reg_allowreg','b:1;'),(115,'Users','reg_Illegaluseragents','s:0:\"\";'),(116,'Users','reg_Illegaldomains','s:0:\"\";'),(117,'Users','reg_Illegalusername','s:66:\"root, webmaster, admin, administrator, nobody, anonymous, username\";'),(118,'Users','reg_verifyemail','i:2;'),(119,'Users','reg_uniemail','b:1;'),(120,'/EventHandlers','Users','a:4:{i:0;a:3:{s:9:\"eventname\";s:19:\"get.pending_content\";s:8:\"callable\";a:2:{i:0;s:29:\"Users_Listener_PendingContent\";i:1;s:22:\"pendingContentListener\";}s:6:\"weight\";i:10;}i:1;a:3:{s:9:\"eventname\";s:15:\"user.login.veto\";s:8:\"callable\";a:2:{i:0;s:35:\"Users_Listener_ForcedPasswordChange\";i:1;s:28:\"forcedPasswordChangeListener\";}s:6:\"weight\";i:10;}i:2;a:3:{s:9:\"eventname\";s:21:\"user.logout.succeeded\";s:8:\"callable\";a:2:{i:0;s:34:\"Users_Listener_ClearUsersNamespace\";i:1;s:27:\"clearUsersNamespaceListener\";}s:6:\"weight\";i:10;}i:3;a:3:{s:9:\"eventname\";s:25:\"frontcontroller.exception\";s:8:\"callable\";a:2:{i:0;s:34:\"Users_Listener_ClearUsersNamespace\";i:1;s:27:\"clearUsersNamespaceListener\";}s:6:\"weight\";i:10;}}'),(121,'SecurityCenter','itemsperpage','i:10;'),(122,'ZConfig','updatecheck','i:1;'),(123,'ZConfig','updatefrequency','i:7;'),(124,'ZConfig','updatelastchecked','i:1471119715;'),(125,'ZConfig','updateversion','s:5:\"1.4.2\";'),(126,'ZConfig','keyexpiry','i:0;'),(127,'ZConfig','sessionauthkeyua','b:0;'),(128,'ZConfig','secure_domain','s:0:\"\";'),(129,'ZConfig','signcookies','i:1;'),(130,'ZConfig','signingkey','s:40:\"00a06c68d22650216977a61e0503b5a7c62f8392\";'),(131,'ZConfig','seclevel','s:6:\"Medium\";'),(132,'ZConfig','secmeddays','i:7;'),(133,'ZConfig','secinactivemins','i:20;'),(134,'ZConfig','sessionstoretofile','i:0;'),(135,'ZConfig','sessionsavepath','s:0:\"\";'),(136,'ZConfig','gc_probability','i:100;'),(137,'ZConfig','anonymoussessions','i:1;'),(138,'ZConfig','sessionrandregenerate','b:1;'),(139,'ZConfig','sessionregenerate','b:1;'),(140,'ZConfig','sessionregeneratefreq','i:10;'),(141,'ZConfig','sessionipcheck','i:0;'),(142,'ZConfig','sessionname','s:5:\"_zsid\";'),(143,'ZConfig','sessioncsrftokenonetime','i:0;'),(144,'ZConfig','filtergetvars','i:1;'),(145,'ZConfig','filterpostvars','i:1;'),(146,'ZConfig','filtercookievars','i:1;'),(147,'ZConfig','outputfilter','i:1;'),(148,'ZConfig','htmlpurifierlocation','s:46:\"system/SecurityCenter/lib/vendor/htmlpurifier/\";'),(149,'SecurityCenter','htmlpurifierConfig','s:3942:\"a:10:{s:4:\"Attr\";a:15:{s:14:\"AllowedClasses\";N;s:19:\"AllowedFrameTargets\";a:0:{}s:10:\"AllowedRel\";a:3:{s:8:\"nofollow\";b:1;s:11:\"imageviewer\";b:1;s:8:\"lightbox\";b:1;}s:10:\"AllowedRev\";a:0:{}s:13:\"ClassUseCDATA\";N;s:15:\"DefaultImageAlt\";N;s:19:\"DefaultInvalidImage\";s:0:\"\";s:22:\"DefaultInvalidImageAlt\";s:13:\"Invalid image\";s:14:\"DefaultTextDir\";s:3:\"ltr\";s:8:\"EnableID\";b:0;s:16:\"ForbiddenClasses\";a:0:{}s:11:\"IDBlacklist\";a:0:{}s:17:\"IDBlacklistRegexp\";N;s:8:\"IDPrefix\";s:0:\"\";s:13:\"IDPrefixLocal\";s:0:\"\";}s:10:\"AutoFormat\";a:10:{s:13:\"AutoParagraph\";b:0;s:6:\"Custom\";a:0:{}s:14:\"DisplayLinkURI\";b:0;s:7:\"Linkify\";b:0;s:22:\"PurifierLinkify.DocURL\";s:3:\"#%s\";s:15:\"PurifierLinkify\";b:0;s:33:\"RemoveEmpty.RemoveNbsp.Exceptions\";a:2:{s:2:\"td\";b:1;s:2:\"th\";b:1;}s:22:\"RemoveEmpty.RemoveNbsp\";b:0;s:11:\"RemoveEmpty\";b:0;s:28:\"RemoveSpansWithoutAttributes\";b:0;}s:3:\"CSS\";a:9:{s:14:\"AllowImportant\";b:0;s:11:\"AllowTricky\";b:0;s:12:\"AllowedFonts\";N;s:17:\"AllowedProperties\";N;s:13:\"DefinitionRev\";i:1;s:19:\"ForbiddenProperties\";a:0:{}s:12:\"MaxImgLength\";s:6:\"1200px\";s:11:\"Proprietary\";b:0;s:7:\"Trusted\";b:0;}s:5:\"Cache\";a:3:{s:14:\"DefinitionImpl\";s:10:\"Serializer\";s:14:\"SerializerPath\";N;s:21:\"SerializerPermissions\";i:493;}s:4:\"Core\";a:17:{s:17:\"AggressivelyFixLt\";b:1;s:13:\"CollectErrors\";b:0;s:13:\"ColorKeywords\";a:17:{s:6:\"maroon\";s:7:\"#800000\";s:3:\"red\";s:7:\"#FF0000\";s:6:\"orange\";s:7:\"#FFA500\";s:6:\"yellow\";s:7:\"#FFFF00\";s:5:\"olive\";s:7:\"#808000\";s:6:\"purple\";s:7:\"#800080\";s:7:\"fuchsia\";s:7:\"#FF00FF\";s:5:\"white\";s:7:\"#FFFFFF\";s:4:\"lime\";s:7:\"#00FF00\";s:5:\"green\";s:7:\"#008000\";s:4:\"navy\";s:7:\"#000080\";s:4:\"blue\";s:7:\"#0000FF\";s:4:\"aqua\";s:7:\"#00FFFF\";s:4:\"teal\";s:7:\"#008080\";s:5:\"black\";s:7:\"#000000\";s:6:\"silver\";s:7:\"#C0C0C0\";s:4:\"gray\";s:7:\"#808080\";}s:25:\"ConvertDocumentToFragment\";b:1;s:31:\"DirectLexLineNumberSyncInterval\";i:0;s:8:\"Encoding\";s:5:\"utf-8\";s:21:\"EscapeInvalidChildren\";b:0;s:17:\"EscapeInvalidTags\";b:0;s:24:\"EscapeNonASCIICharacters\";b:0;s:14:\"HiddenElements\";a:2:{s:6:\"script\";b:1;s:5:\"style\";b:1;}s:8:\"Language\";s:2:\"en\";s:9:\"LexerImpl\";N;s:19:\"MaintainLineNumbers\";N;s:17:\"NormalizeNewlines\";b:1;s:16:\"RemoveInvalidImg\";b:1;s:28:\"RemoveProcessingInstructions\";b:0;s:20:\"RemoveScriptContents\";N;}s:6:\"Filter\";a:6:{s:6:\"Custom\";a:0:{}s:27:\"ExtractStyleBlocks.Escaping\";b:1;s:24:\"ExtractStyleBlocks.Scope\";N;s:27:\"ExtractStyleBlocks.TidyImpl\";N;s:18:\"ExtractStyleBlocks\";b:0;s:7:\"YouTube\";b:0;}s:4:\"HTML\";a:26:{s:7:\"Allowed\";N;s:17:\"AllowedAttributes\";N;s:15:\"AllowedElements\";N;s:14:\"AllowedModules\";N;s:18:\"Attr.Name.UseCDATA\";b:0;s:12:\"BlockWrapper\";s:1:\"p\";s:11:\"CoreModules\";a:7:{s:9:\"Structure\";b:1;s:4:\"Text\";b:1;s:9:\"Hypertext\";b:1;s:4:\"List\";b:1;s:22:\"NonXMLCommonAttributes\";b:1;s:19:\"XMLCommonAttributes\";b:1;s:16:\"CommonAttributes\";b:1;}s:13:\"CustomDoctype\";N;s:12:\"DefinitionID\";N;s:13:\"DefinitionRev\";i:1;s:7:\"Doctype\";s:22:\"HTML 4.01 Transitional\";s:20:\"FlashAllowFullScreen\";b:0;s:19:\"ForbiddenAttributes\";a:0:{}s:17:\"ForbiddenElements\";a:0:{}s:12:\"MaxImgLength\";i:1200;s:8:\"Nofollow\";b:0;s:6:\"Parent\";s:3:\"div\";s:11:\"Proprietary\";b:0;s:9:\"SafeEmbed\";b:1;s:10:\"SafeObject\";b:1;s:6:\"Strict\";b:0;s:7:\"TidyAdd\";a:0:{}s:9:\"TidyLevel\";s:6:\"medium\";s:10:\"TidyRemove\";a:0:{}s:7:\"Trusted\";b:0;s:5:\"XHTML\";b:1;}s:6:\"Output\";a:6:{s:21:\"CommentScriptContents\";b:1;s:12:\"FixInnerHTML\";b:1;s:11:\"FlashCompat\";b:1;s:7:\"Newline\";N;s:8:\"SortAttr\";b:0;s:10:\"TidyFormat\";b:0;}s:4:\"Test\";a:1:{s:12:\"ForceNoIconv\";b:0;}s:3:\"URI\";a:16:{s:14:\"AllowedSchemes\";a:6:{s:4:\"http\";b:1;s:5:\"https\";b:1;s:6:\"mailto\";b:1;s:3:\"ftp\";b:1;s:4:\"nntp\";b:1;s:4:\"news\";b:1;}s:4:\"Base\";N;s:13:\"DefaultScheme\";s:4:\"http\";s:12:\"DefinitionID\";N;s:13:\"DefinitionRev\";i:1;s:7:\"Disable\";b:0;s:15:\"DisableExternal\";b:0;s:24:\"DisableExternalResources\";b:0;s:16:\"DisableResources\";b:0;s:4:\"Host\";N;s:13:\"HostBlacklist\";a:0:{}s:12:\"MakeAbsolute\";b:0;s:5:\"Munge\";N;s:14:\"MungeResources\";b:0;s:14:\"MungeSecretKey\";N;s:22:\"OverrideAllowedSchemes\";b:1;}}\";'),(150,'ZConfig','useids','i:0;'),(151,'ZConfig','idsmail','i:0;'),(152,'ZConfig','idsrulepath','s:32:\"config/phpids_zikula_default.xml\";'),(153,'ZConfig','idssoftblock','i:1;'),(154,'ZConfig','idsfilter','s:3:\"xml\";'),(155,'ZConfig','idsimpactthresholdone','i:1;'),(156,'ZConfig','idsimpactthresholdtwo','i:10;'),(157,'ZConfig','idsimpactthresholdthree','i:25;'),(158,'ZConfig','idsimpactthresholdfour','i:75;'),(159,'ZConfig','idsimpactmode','i:1;'),(160,'ZConfig','idshtmlfields','a:1:{i:0;s:14:\"POST.__wysiwyg\";}'),(161,'ZConfig','idsjsonfields','a:1:{i:0;s:15:\"POST.__jsondata\";}'),(162,'ZConfig','idsexceptions','a:12:{i:0;s:10:\"GET.__utmz\";i:1;s:10:\"GET.__utmc\";i:2;s:18:\"REQUEST.linksorder\";i:3;s:15:\"POST.linksorder\";i:4;s:19:\"REQUEST.fullcontent\";i:5;s:16:\"POST.fullcontent\";i:6;s:22:\"REQUEST.summarycontent\";i:7;s:19:\"POST.summarycontent\";i:8;s:19:\"REQUEST.filter.page\";i:9;s:16:\"POST.filter.page\";i:10;s:20:\"REQUEST.filter.value\";i:11;s:17:\"POST.filter.value\";}'),(163,'ZConfig','summarycontent','s:1130:\"For the attention of %sitename% administration staff:\n\nOn %date% at %time%, Zikula detected that somebody tried to interact with the site in a way that may have been intended compromise its security. This is not necessarily the case: it could have been caused by work you were doing on the site, or may have been due to some other reason. In any case, it was detected and blocked. \n\nThe suspicious activity was recognised in \'%filename%\' at line %linenumber%.\n\nType: %type%. \n\nAdditional information: %additionalinfo%.\n\nBelow is logged information that may help you identify what happened and who was responsible.\n\n=====================================\nInformation about the user:\n=====================================\nUser name:  %username%\nUser\'s e-mail address: %useremail%\nUser\'s real name: %userrealname%\n\n=====================================\nIP numbers (if this was a cracker, the IP numbers may not be the true point of origin)\n=====================================\nIP according to HTTP_CLIENT_IP: %httpclientip%\nIP according to REMOTE_ADDR: %remoteaddr%\nIP according to GetHostByName($REMOTE_ADDR): %gethostbyremoteaddr%\n\";'),(164,'ZConfig','fullcontent','s:1289:\"=====================================\nInformation in the $_REQUEST array\n=====================================\n%requestarray%\n\n=====================================\nInformation in the $_GET array\n(variables that may have been in the URL string or in a \'GET\'-type form)\n=====================================\n%getarray%\n\n=====================================\nInformation in the $_POST array\n(visible and invisible form elements)\n=====================================\n%postarray%\n\n=====================================\nBrowser information\n=====================================\n%browserinfo%\n\n=====================================\nInformation in the $_SERVER array\n=====================================\n%serverarray%\n\n=====================================\nInformation in the $_ENV array\n=====================================\n%envarray%\n\n=====================================\nInformation in the $_COOKIE array\n=====================================\n%cookiearray%\n\n=====================================\nInformation in the $_FILES array\n=====================================\n%filearray%\n\n=====================================\nInformation in the $_SESSION array\n(session information -- variables starting with PNSV are Zikula session variables)\n=====================================\n%sessionarray%\n\";'),(165,'ZConfig','usehtaccessbans','i:0;'),(166,'ZConfig','extrapostprotection','i:0;'),(167,'ZConfig','extragetprotection','i:0;'),(168,'ZConfig','checkmultipost','i:0;'),(169,'ZConfig','maxmultipost','i:4;'),(170,'ZConfig','cpuloadmonitor','i:0;'),(171,'ZConfig','cpumaxload','d:10;'),(172,'ZConfig','ccisessionpath','s:0:\"\";'),(173,'ZConfig','htaccessfilelocation','s:9:\".htaccess\";'),(174,'ZConfig','nocookiebanthreshold','i:10;'),(175,'ZConfig','nocookiewarningthreshold','i:2;'),(176,'ZConfig','fastaccessbanthreshold','i:40;'),(177,'ZConfig','fastaccesswarnthreshold','i:10;'),(178,'ZConfig','javababble','i:0;'),(179,'ZConfig','javaencrypt','i:0;'),(180,'ZConfig','preservehead','i:0;'),(181,'ZConfig','filterarrays','i:1;'),(182,'ZConfig','htmlentities','s:1:\"1\";'),(183,'ZConfig','AllowableHTML','a:110:{s:3:\"!--\";i:2;s:1:\"a\";i:2;s:4:\"abbr\";i:1;s:7:\"acronym\";i:1;s:7:\"address\";i:1;s:6:\"applet\";i:0;s:4:\"area\";i:0;s:7:\"article\";i:1;s:5:\"aside\";i:1;s:5:\"audio\";i:0;s:1:\"b\";i:1;s:4:\"base\";i:0;s:8:\"basefont\";i:0;s:3:\"bdo\";i:0;s:3:\"big\";i:0;s:10:\"blockquote\";i:2;s:2:\"br\";i:2;s:6:\"button\";i:0;s:6:\"canvas\";i:0;s:7:\"caption\";i:1;s:6:\"center\";i:2;s:4:\"cite\";i:1;s:4:\"code\";i:0;s:3:\"col\";i:1;s:8:\"colgroup\";i:1;s:7:\"command\";i:0;s:8:\"datalist\";i:0;s:2:\"dd\";i:1;s:3:\"del\";i:0;s:7:\"details\";i:1;s:3:\"dfn\";i:0;s:3:\"dir\";i:0;s:3:\"div\";i:2;s:2:\"dl\";i:1;s:2:\"dt\";i:1;s:2:\"em\";i:2;s:5:\"embed\";i:0;s:8:\"fieldset\";i:1;s:10:\"figcaption\";i:0;s:6:\"figure\";i:0;s:6:\"footer\";i:0;s:4:\"font\";i:0;s:4:\"form\";i:0;s:2:\"h1\";i:1;s:2:\"h2\";i:1;s:2:\"h3\";i:1;s:2:\"h4\";i:1;s:2:\"h5\";i:1;s:2:\"h6\";i:1;s:6:\"header\";i:0;s:6:\"hgroup\";i:0;s:2:\"hr\";i:2;s:1:\"i\";i:1;s:6:\"iframe\";i:0;s:3:\"img\";i:2;s:5:\"input\";i:0;s:3:\"ins\";i:0;s:6:\"keygen\";i:0;s:3:\"kbd\";i:0;s:5:\"label\";i:1;s:6:\"legend\";i:1;s:2:\"li\";i:2;s:3:\"map\";i:0;s:4:\"mark\";i:0;s:4:\"menu\";i:0;s:7:\"marquee\";i:0;s:5:\"meter\";i:0;s:3:\"nav\";i:0;s:4:\"nobr\";i:0;s:6:\"object\";i:0;s:2:\"ol\";i:2;s:8:\"optgroup\";i:0;s:6:\"option\";i:0;s:6:\"output\";i:0;s:1:\"p\";i:2;s:5:\"param\";i:0;s:3:\"pre\";i:2;s:8:\"progress\";i:0;s:1:\"q\";i:0;s:2:\"rp\";i:0;s:2:\"rt\";i:0;s:4:\"ruby\";i:0;s:1:\"s\";i:0;s:4:\"samp\";i:0;s:6:\"script\";i:0;s:7:\"section\";i:0;s:6:\"select\";i:0;s:5:\"small\";i:0;s:6:\"source\";i:0;s:4:\"span\";i:2;s:6:\"strike\";i:0;s:6:\"strong\";i:2;s:3:\"sub\";i:1;s:7:\"summary\";i:1;s:3:\"sup\";i:0;s:5:\"table\";i:2;s:5:\"tbody\";i:1;s:2:\"td\";i:2;s:8:\"textarea\";i:0;s:5:\"tfoot\";i:1;s:2:\"th\";i:2;s:5:\"thead\";i:0;s:4:\"time\";i:0;s:2:\"tr\";i:2;s:2:\"tt\";i:2;s:1:\"u\";i:0;s:2:\"ul\";i:2;s:3:\"var\";i:0;s:5:\"video\";i:0;s:3:\"wbr\";i:0;}'),(184,'Categories','userrootcat','s:17:\"/__SYSTEM__/Users\";'),(185,'Categories','allowusercatedit','i:0;'),(186,'Categories','autocreateusercat','i:0;'),(187,'Categories','autocreateuserdefaultcat','i:0;'),(188,'Categories','userdefaultcatname','s:7:\"Default\";'),(189,'Legal','legalNoticeActive','b:1;'),(190,'Legal','termsOfUseActive','b:1;'),(191,'Legal','privacyPolicyActive','b:1;'),(192,'Legal','accessibilityStatementActive','b:1;'),(193,'Legal','cancellationRightPolicyActive','b:0;'),(194,'Legal','tradeConditionsActive','b:0;'),(195,'Legal','legalNoticeUrl','s:0:\"\";'),(196,'Legal','termsOfUseUrl','s:0:\"\";'),(197,'Legal','privacyPolicyUrl','s:0:\"\";'),(198,'Legal','accessibilityStatementUrl','s:0:\"\";'),(199,'Legal','cancellationRightPolicyUrl','s:0:\"\";'),(200,'Legal','tradeConditionsUrl','s:0:\"\";'),(201,'Legal','minimumAge','i:13;'),(202,'/EventHandlers','Legal','a:2:{i:0;a:3:{s:9:\"eventname\";s:15:\"user.login.veto\";s:8:\"callable\";a:2:{i:0;s:29:\"Legal_Listener_UsersLoginVeto\";i:1;s:22:\"acceptPoliciesListener\";}s:6:\"weight\";i:10;}i:1;a:1:{s:9:\"classname\";s:29:\"Legal_Listener_UsersUiHandler\";}}'),(203,'Mailer','mailertype','i:1;'),(204,'Mailer','charset','s:5:\"utf-8\";'),(205,'Mailer','encoding','s:4:\"8bit\";'),(206,'Mailer','html','b:0;'),(207,'Mailer','wordwrap','i:50;'),(208,'Mailer','msmailheaders','b:0;'),(209,'Mailer','sendmailpath','s:18:\"/usr/sbin/sendmail\";'),(210,'Mailer','smtpauth','b:0;'),(211,'Mailer','smtpserver','s:9:\"localhost\";'),(212,'Mailer','smtpport','i:25;'),(213,'Mailer','smtptimeout','i:10;'),(214,'Mailer','smtpusername','s:0:\"\";'),(215,'Mailer','smtppassword','s:0:\"\";'),(216,'Mailer','smtpsecuremethod','s:3:\"ssl\";'),(217,'Search','itemsperpage','i:10;'),(218,'Search','limitsummary','i:255;'),(219,'/EventHandlers','Search','a:1:{i:0;a:3:{s:9:\"eventname\";s:26:\"installer.module.installed\";s:8:\"callable\";a:2:{i:0;s:20:\"Search_EventHandlers\";i:1;s:13:\"moduleInstall\";}s:6:\"weight\";i:10;}}'),(220,'systemplugin.imagine','version','s:5:\"0.6.2\";'),(221,'systemplugin.imagine','thumb_dir','s:20:\"systemplugin.imagine\";'),(222,'systemplugin.imagine','thumb_auto_cleanup','b:0;'),(223,'systemplugin.imagine','thumb_auto_cleanup_period','s:3:\"P1D\";'),(224,'systemplugin.imagine','presets','a:1:{s:7:\"default\";C:27:\"SystemPlugin_Imagine_Preset\":266:{x:i:2;a:8:{s:5:\"width\";i:100;s:6:\"height\";i:100;s:4:\"mode\";s:5:\"inset\";s:9:\"extension\";N;s:7:\"options\";a:2:{s:12:\"jpeg_quality\";i:75;s:21:\"png_compression_level\";i:7;}s:8:\"__module\";N;s:9:\"__imagine\";N;s:16:\"__transformation\";N;};m:a:1:{s:7:\"\0*\0name\";s:7:\"default\";}}}');
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
  `name` varchar(64) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `displayname` varchar(64) NOT NULL,
  `url` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL,
  `directory` varchar(64) NOT NULL,
  `version` varchar(10) NOT NULL DEFAULT '0',
  `capabilities` longtext NOT NULL,
  `state` smallint(6) NOT NULL DEFAULT '0',
  `securityschema` longtext NOT NULL,
  `core_min` varchar(9) NOT NULL,
  `core_max` varchar(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `mod_state` (`name`,`state`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,'Extensions',3,'Extensions','extensions','Manage your modules and plugins.','Extensions','3.7.10','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:12:\"Extensions::\";s:2:\"::\";}','',''),(2,'Admin',3,'Administration panel','adminpanel','Backend administration interface.','Admin','1.9.1','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:7:\"Admin::\";s:38:\"Admin Category name::Admin Category ID\";}','',''),(3,'Blocks',3,'Blocks','blocks','Block administration module.','Blocks','3.8.2','a:3:{s:15:\"hook_subscriber\";a:1:{s:7:\"enabled\";b:1;}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:4:{s:8:\"Blocks::\";s:30:\"Block key:Block title:Block ID\";s:16:\"Blocks::position\";s:26:\"Position name::Position ID\";s:23:\"Menutree:menutreeblock:\";s:26:\"Block ID:Link Name:Link ID\";s:19:\"ExtendedMenublock::\";s:17:\"Block ID:Link ID:\";}','',''),(4,'Categories',3,'Categories','categories','Category administration.','Categories','1.2.1','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:20:\"Categories::Category\";s:40:\"Category ID:Category Path:Category IPath\";}','',''),(5,'Errors',3,'Errors','errors','Error display module.','Errors','1.1.1','a:1:{s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:8:\"Errors::\";s:2:\"::\";}','',''),(6,'Groups',3,'Groups','groups','User group administration module.','Groups','2.3.2','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:8:\"Groups::\";s:10:\"Group ID::\";}','',''),(7,'Mailer',3,'Mailer','mailer','Mailer module, provides mail API and mail setting administration.','Mailer','1.3.2','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:8:\"Mailer::\";s:2:\"::\";}','',''),(8,'PageLock',3,'Page lock','pagelock','Provides the ability to lock pages when they are in use, for content and access control.','PageLock','1.1.1','a:0:{}',1,'a:1:{s:10:\"PageLock::\";s:2:\"::\";}','',''),(9,'Permissions',3,'Permissions','permissions','User permissions manager.','Permissions','1.1.1','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:13:\"Permissions::\";s:2:\"::\";}','',''),(10,'Search',3,'Site search','search','Site search module.','Search','1.5.2','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:8:\"Search::\";s:13:\"Module name::\";}','',''),(11,'SecurityCenter',3,'Security Center','securitycenter','Manage site security and settings.','SecurityCenter','1.4.4','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:16:\"SecurityCenter::\";s:2:\"::\";}','',''),(12,'Settings',3,'General settings','settings','General site configuration interface.','Settings','2.9.7','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:10:\"Settings::\";s:2:\"::\";}','',''),(13,'Theme',3,'Themes','theme','Themes module to manage site layout, render and cache settings.','Theme','3.4.2','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:7:\"Theme::\";s:12:\"Theme name::\";}','',''),(14,'Users',3,'Users','users','Provides an interface for configuring and administering registered user accounts. Incorporates all needed functionality, but can work in close unison with the third party profile module configured in the general settings of the site.','Users','2.2.0','a:4:{s:14:\"authentication\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:15:\"hook_subscriber\";a:1:{s:7:\"enabled\";b:1;}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:2:{s:7:\"Users::\";s:14:\"Uname::User ID\";s:16:\"Users::MailUsers\";s:2:\"::\";}','1.3.0',''),(15,'Legal',2,'Legal info manager','legalmod','Provides an interface for managing the site\'s legal documents.','Legal','2.0.2','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:8:{s:7:\"Legal::\";s:2:\"::\";s:18:\"Legal::legalnotice\";s:2:\"::\";s:17:\"Legal::termsofuse\";s:2:\"::\";s:20:\"Legal::privacypolicy\";s:2:\"::\";s:16:\"Legal::agepolicy\";s:2:\"::\";s:29:\"Legal::accessibilitystatement\";s:2:\"::\";s:30:\"Legal::cancellationrightpolicy\";s:2:\"::\";s:22:\"Legal::tradeconditions\";s:2:\"::\";}','1.3.0','1.3.99'),(16,'Profile',2,'Profile','profile','Provides a personal account control panel for each registered user, an interface to administer the personal information items displayed within it, and a registered users list functionality. Works in close unison with the \'Users\' module.','Profile','1.6.2','a:3:{s:7:\"profile\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',1,'a:6:{s:9:\"Profile::\";s:2:\"::\";s:13:\"Profile::view\";s:2:\"::\";s:13:\"Profile::item\";s:56:\"DynamicUserData PropertyName::DynamicUserData PropertyID\";s:16:\"Profile:Members:\";s:2:\"::\";s:22:\"Profile:Members:recent\";s:2:\"::\";s:22:\"Profile:Members:online\";s:2:\"::\";}','1.3.0','1.3.99');
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
  `attribute_name` varchar(80) NOT NULL,
  `object_id` int(11) NOT NULL DEFAULT '0',
  `object_type` varchar(80) NOT NULL,
  `value` longtext NOT NULL,
  `obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cr_uid` int(11) NOT NULL DEFAULT '0',
  `lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `object_type` (`object_type`),
  KEY `object_id` (`object_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objectdata_attributes`
--

LOCK TABLES `objectdata_attributes` WRITE;
/*!40000 ALTER TABLE `objectdata_attributes` DISABLE KEYS */;
INSERT INTO `objectdata_attributes` VALUES (1,'code',5,'categories_category','Y','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(2,'code',6,'categories_category','N','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(3,'code',11,'categories_category','P','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(4,'code',12,'categories_category','C','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(5,'code',13,'categories_category','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(6,'code',14,'categories_category','O','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(7,'code',15,'categories_category','R','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(8,'code',17,'categories_category','M','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(9,'code',18,'categories_category','F','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(10,'code',26,'categories_category','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(11,'code',27,'categories_category','I','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(12,'code',29,'categories_category','P','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0),(13,'code',30,'categories_category','A','A','2016-08-13 16:21:53',0,'2016-08-13 16:21:53',0);
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
  `object_type` varchar(80) NOT NULL,
  `object_id` int(11) NOT NULL DEFAULT '0',
  `op` varchar(16) NOT NULL,
  `diff` longtext,
  `obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cr_uid` int(11) NOT NULL DEFAULT '0',
  `lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `module` varchar(40) NOT NULL,
  `tablename` varchar(40) NOT NULL,
  `idcolumn` varchar(40) NOT NULL,
  `obj_id` int(11) NOT NULL DEFAULT '0',
  `permissions` varchar(255) DEFAULT NULL,
  `dc_title` varchar(80) DEFAULT NULL,
  `dc_author` varchar(80) DEFAULT NULL,
  `dc_subject` varchar(255) DEFAULT NULL,
  `dc_keywords` varchar(128) DEFAULT NULL,
  `dc_description` varchar(255) DEFAULT NULL,
  `dc_publisher` varchar(128) DEFAULT NULL,
  `dc_contributor` varchar(128) DEFAULT NULL,
  `dc_startdate` datetime DEFAULT '1970-01-01 00:00:00',
  `dc_enddate` datetime DEFAULT '1970-01-01 00:00:00',
  `dc_type` varchar(128) DEFAULT NULL,
  `dc_format` varchar(128) DEFAULT NULL,
  `dc_uri` varchar(255) DEFAULT NULL,
  `dc_source` varchar(128) DEFAULT NULL,
  `dc_language` varchar(32) DEFAULT NULL,
  `dc_relation` varchar(255) DEFAULT NULL,
  `dc_coverage` varchar(64) DEFAULT NULL,
  `dc_entity` varchar(64) DEFAULT NULL,
  `dc_comment` varchar(255) DEFAULT NULL,
  `dc_extra` varchar(255) DEFAULT NULL,
  `obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cr_uid` int(11) NOT NULL DEFAULT '0',
  `lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `name` varchar(128) NOT NULL,
  `tag` varchar(40) DEFAULT NULL,
  `value` longtext NOT NULL,
  `page` longtext NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `ip` varchar(40) NOT NULL,
  `impact` int(11) NOT NULL DEFAULT '0',
  `filters` longtext NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `title` varchar(255) NOT NULL,
  `text` longtext,
  `module` varchar(100) DEFAULT NULL,
  `extra` varchar(100) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `found` datetime DEFAULT NULL,
  `sesid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `title` (`title`),
  KEY `module` (`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `search` varchar(50) NOT NULL,
  `scount` int(11) NOT NULL DEFAULT '0',
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `sessid` varchar(40) NOT NULL,
  `ipaddr` varchar(32) NOT NULL,
  `lastused` datetime DEFAULT '1970-01-01 00:00:00',
  `uid` int(11) DEFAULT '0',
  `remember` tinyint(4) NOT NULL DEFAULT '0',
  `vars` longtext NOT NULL,
  PRIMARY KEY (`sessid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session_info`
--

LOCK TABLES `session_info` WRITE;
/*!40000 ALTER TABLE `session_info` DISABLE KEYS */;
INSERT INTO `session_info` VALUES ('6d5f3dff86ccf1d85d716517f18832a421a35e4b','837ec5754f503cfaaee0929fd48974e7','2016-08-13 16:22:11',2,0,'/|a:3:{s:4:\"rand\";a:0:{}s:9:\"useragent\";s:40:\"1d17425e9d1cd11d5d857bd3d1b870de6e97430c\";s:3:\"uid\";s:1:\"2\";}_zikula_messages|a:1:{s:6:\"status\";a:0:{}}Zikula_Users|a:1:{s:21:\"authentication_method\";a:2:{s:7:\"modname\";s:5:\"Users\";s:6:\"method\";s:5:\"uname\";}}');
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
  `name` varchar(64) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `displayname` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL,
  `directory` varchar(64) NOT NULL,
  `version` varchar(10) NOT NULL DEFAULT '0',
  `contact` varchar(255) NOT NULL,
  `admin` tinyint(4) NOT NULL DEFAULT '0',
  `user` tinyint(4) NOT NULL DEFAULT '0',
  `system` tinyint(4) NOT NULL DEFAULT '0',
  `state` tinyint(4) NOT NULL DEFAULT '0',
  `xhtml` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `themes`
--

LOCK TABLES `themes` WRITE;
/*!40000 ALTER TABLE `themes` DISABLE KEYS */;
INSERT INTO `themes` VALUES (1,'Andreas08',3,'Andreas08','Based on the theme Andreas08 by Andreas Viklund and extended for Zikula with the CSS Framework \'fluid960gs\'.','Andreas08','2.0','',1,1,0,1,1),(2,'Atom',3,'Atom','The Atom theme is an auxiliary theme specially designed for rendering pages in Atom mark-up.','Atom','1.0','',0,0,1,1,0),(3,'Mobile',3,'Mobile','The mobile theme is an auxiliary theme designed specially for outputting pages in a mobile-friendly format.','Mobile','1.0','',0,0,1,1,1),(4,'Printer',3,'Printer','The Printer theme is an auxiliary theme designed specially for outputting pages in a printer-friendly format.','Printer','2.0','',0,0,1,1,1),(5,'RSS',3,'RSS','The RSS theme is an auxiliary theme designed specially for outputting pages as an RSS feed.','RSS','1.0','',0,0,1,1,0),(6,'SeaBreeze',3,'SeaBreeze','The SeaBreeze theme is a browser-oriented theme, and was updated for the release of Zikula 1.0, with revised colours and new graphics.','SeaBreeze','3.2','',0,1,0,1,1);
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
  PRIMARY KEY (`uid`,`bid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `uname` varchar(25) NOT NULL,
  `email` varchar(60) NOT NULL,
  `pass` varchar(138) NOT NULL,
  `passreminder` varchar(255) NOT NULL,
  `activated` smallint(6) NOT NULL DEFAULT '0',
  `approved_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `approved_by` int(11) NOT NULL DEFAULT '0',
  `user_regdate` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `lastlogin` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `theme` varchar(255) NOT NULL,
  `ublockon` tinyint(4) NOT NULL DEFAULT '0',
  `ublock` longtext NOT NULL,
  `tz` varchar(30) NOT NULL,
  `locale` varchar(5) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `uname` (`uname`),
  KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'guest','','','',1,'1970-01-01 00:00:00',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','',0,'','',''),(2,'admin','admin@example.com','8$CXX1e$adda990787a2bb045c9d99f507342bf7fb9f6dd185c25d2cd34bc8cd80a9c100','',1,'2016-08-13 20:21:52',2,'2016-08-13 20:21:54','2016-08-13 20:21:54','',0,'','','');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_verifychg`
--

DROP TABLE IF EXISTS `users_verifychg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_verifychg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `changetype` tinyint(4) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `newemail` varchar(60) NOT NULL,
  `verifycode` varchar(138) NOT NULL,
  `created_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `metaid` int(11) NOT NULL DEFAULT '0',
  `module` varchar(255) NOT NULL,
  `schemaname` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `type` smallint(6) NOT NULL DEFAULT '1',
  `obj_table` varchar(40) NOT NULL,
  `obj_idcolumn` varchar(40) NOT NULL,
  `obj_id` int(11) NOT NULL DEFAULT '0',
  `busy` int(11) NOT NULL DEFAULT '0',
  `debug` longtext,
  PRIMARY KEY (`id`),
  KEY `obj_table` (`obj_table`),
  KEY `obj_idcolumn` (`obj_idcolumn`),
  KEY `obj_id` (`obj_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflows`
--

LOCK TABLES `workflows` WRITE;
/*!40000 ALTER TABLE `workflows` DISABLE KEYS */;
/*!40000 ALTER TABLE `workflows` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-08-13 16:23:53
