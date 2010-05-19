-- MySQL dump 10.11
--
-- Host: localhost    Database: zikula130
-- ------------------------------------------------------
-- Server version	5.0.51a-24+lenny3

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
-- Table structure for table `z_admin_category`
--

DROP TABLE IF EXISTS `z_admin_category`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_admin_category` (
  `pn_cid` int(11) NOT NULL auto_increment,
  `pn_name` varchar(32) NOT NULL default '',
  `pn_description` varchar(254) NOT NULL default '',
  PRIMARY KEY  (`pn_cid`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_admin_category`
--

INSERT INTO `z_admin_category` (`pn_cid`, `pn_name`, `pn_description`) VALUES (1,'System','Core modules at the heart of operation of the site.'),(2,'Layout','Layout modules for controlling the site\'s look and feel.'),(3,'Users','Modules for controlling user membership, access rights and profiles.'),(4,'Content','Modules for providing content to your users.'),(5,'3rd-party','3rd-party add-on modules and newly-installed modules.'),(6,'Security','Modules for managing the site\'s security.');

--
-- Table structure for table `z_admin_module`
--

DROP TABLE IF EXISTS `z_admin_module`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_admin_module` (
  `pn_amid` int(11) NOT NULL auto_increment,
  `pn_mid` int(11) NOT NULL default '0',
  `pn_cid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`pn_amid`),
  KEY `mid_cid` (`pn_mid`,`pn_cid`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_admin_module`
--

INSERT INTO `z_admin_module` (`pn_amid`, `pn_mid`, `pn_cid`) VALUES (1,1,1),(2,2,1),(3,10,3),(4,6,3),(5,3,2),(6,8,1),(7,16,3),(8,15,2),(9,13,1),(10,12,6),(11,4,4),(12,7,1),(13,5,1),(14,11,4),(15,17,1),(16,9,1),(17,14,6);

--
-- Table structure for table `z_block_placements`
--

DROP TABLE IF EXISTS `z_block_placements`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_block_placements` (
  `pn_pid` int(11) NOT NULL default '0',
  `pn_bid` int(11) NOT NULL default '0',
  `pn_order` int(11) NOT NULL default '0',
  KEY `bid_pid_idx` (`pn_bid`,`pn_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_block_placements`
--

INSERT INTO `z_block_placements` (`pn_pid`, `pn_bid`, `pn_order`) VALUES (1,1,0),(3,2,0),(2,3,0);

--
-- Table structure for table `z_block_positions`
--

DROP TABLE IF EXISTS `z_block_positions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_block_positions` (
  `pn_pid` int(11) NOT NULL auto_increment,
  `pn_name` varchar(255) NOT NULL default '',
  `pn_description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`pn_pid`),
  KEY `name_idx` (`pn_name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_block_positions`
--

INSERT INTO `z_block_positions` (`pn_pid`, `pn_name`, `pn_description`) VALUES (1,'left','Left blocks'),(2,'right','Right blocks'),(3,'center','Center blocks');

--
-- Table structure for table `z_blocks`
--

DROP TABLE IF EXISTS `z_blocks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_blocks` (
  `pn_bid` int(11) NOT NULL auto_increment,
  `pn_bkey` varchar(255) NOT NULL default '',
  `pn_title` varchar(255) NOT NULL default '',
  `pn_content` longtext NOT NULL,
  `pn_url` longtext NOT NULL,
  `pn_mid` int(11) NOT NULL default '0',
  `pn_filter` longtext NOT NULL,
  `pn_active` smallint(6) NOT NULL default '1',
  `pn_collapsable` int(11) NOT NULL default '1',
  `pn_defaultstate` int(11) NOT NULL default '1',
  `pn_refresh` int(11) NOT NULL default '0',
  `pn_last_update` datetime NOT NULL,
  `pn_language` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`pn_bid`),
  KEY `active_idx` (`pn_active`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_blocks`
--

INSERT INTO `z_blocks` (`pn_bid`, `pn_bkey`, `pn_title`, `pn_content`, `pn_url`, `pn_mid`, `pn_filter`, `pn_active`, `pn_collapsable`, `pn_defaultstate`, `pn_refresh`, `pn_last_update`, `pn_language`) VALUES (1,'extmenu','Main menu','a:5:{s:14:\"displaymodules\";s:1:\"0\";s:10:\"stylesheet\";s:11:\"extmenu.css\";s:8:\"template\";s:24:\"blocks_block_extmenu.htm\";s:11:\"blocktitles\";a:1:{s:2:\"en\";s:9:\"Main menu\";}s:5:\"links\";a:1:{s:2:\"en\";a:4:{i:0;a:7:{s:4:\"name\";s:4:\"Home\";s:3:\"url\";s:10:\"{homepage}\";s:5:\"title\";s:26:\"Go to the site\'s home page\";s:5:\"level\";i:0;s:6:\"parent\";i:0;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:1;a:7:{s:4:\"name\";s:16:\"Site admin panel\";s:3:\"url\";s:24:\"{Admin:adminpanel:admin}\";s:5:\"title\";s:26:\"Go to the site admin panel\";s:5:\"level\";i:0;s:6:\"parent\";i:0;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:2;a:7:{s:4:\"name\";s:18:\"User account panel\";s:3:\"url\";s:7:\"{Users}\";s:5:\"title\";s:29:\"Go to your user account panel\";s:5:\"level\";i:0;s:6:\"parent\";i:0;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:3;a:7:{s:4:\"name\";s:7:\"Log out\";s:3:\"url\";s:14:\"{Users:logout}\";s:5:\"title\";s:28:\"Log out of your user account\";s:5:\"level\";i:0;s:6:\"parent\";i:0;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}}}}','',3,'',1,1,1,3600,'2010-05-19 20:00:04',''),(2,'html','This site is powered by Zikula!','<p><a href=\"http://www.zikula.org\">Zikula</a> is a content management system (CMS) and application framework. It is secure and stable, and is a good choice for sites with a large volume of traffic.</p><p>With Zikula:</p><ul><li>you can customise all aspects of the site\'s appearance through themes, with support for CSS style sheets, JavaScript, Flash and all other modern web development technologies;</li><li>you can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation and internationalisation of your site and pages;</li><li>you can be sure that your pages will display properly in all browsers, thanks to Zikula\'s full compliance with W3C HTML standards;</li><li>you get a standard application-programming interface (API) that lets you easily augment your site\'s functionality through modules, blocks and other extensions;</li><li>you can get help and support from the Zikula community of webmasters and developers at <a href=\"http://www.zikula.org\">zikula.org</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>','',3,'',1,1,1,3600,'2010-05-19 20:00:04',''),(3,'login','User log-in','','',16,'',1,1,1,3600,'2010-05-19 20:00:04','');

--
-- Table structure for table `z_categories_category`
--

DROP TABLE IF EXISTS `z_categories_category`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_categories_category` (
  `cat_id` bigint(20) NOT NULL auto_increment,
  `cat_parent_id` bigint(20) NOT NULL default '1',
  `cat_is_locked` smallint(6) NOT NULL default '0',
  `cat_is_leaf` smallint(6) NOT NULL default '0',
  `cat_name` varchar(255) NOT NULL default '',
  `cat_value` varchar(255) NOT NULL default '',
  `cat_sort_value` bigint(20) NOT NULL default '0',
  `cat_display_name` longtext NOT NULL,
  `cat_display_desc` longtext NOT NULL,
  `cat_path` longtext NOT NULL,
  `cat_ipath` varchar(255) NOT NULL default '',
  `cat_status` varchar(1) NOT NULL default 'A',
  `cat_obj_status` varchar(1) NOT NULL default 'A',
  `cat_cr_date` datetime NOT NULL default '1970-01-01 00:00:00',
  `cat_cr_uid` int(11) NOT NULL default '0',
  `cat_lu_date` datetime NOT NULL default '1970-01-01 00:00:00',
  `cat_lu_uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cat_id`),
  KEY `idx_categories_parent` (`cat_parent_id`),
  KEY `idx_categories_is_leaf` (`cat_is_leaf`),
  KEY `idx_categories_name` (`cat_name`),
  KEY `idx_categories_ipath` (`cat_ipath`,`cat_is_leaf`,`cat_status`),
  KEY `idx_categories_status` (`cat_status`),
  KEY `idx_categories_ipath_status` (`cat_ipath`,`cat_status`)
) ENGINE=MyISAM AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_categories_category`
--

INSERT INTO `z_categories_category` (`cat_id`, `cat_parent_id`, `cat_is_locked`, `cat_is_leaf`, `cat_name`, `cat_value`, `cat_sort_value`, `cat_display_name`, `cat_display_desc`, `cat_path`, `cat_ipath`, `cat_status`, `cat_obj_status`, `cat_cr_date`, `cat_cr_uid`, `cat_lu_date`, `cat_lu_uid`) VALUES (1,0,1,0,'__SYSTEM__','',0,'b:0;','b:0;','/__SYSTEM__','/1','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(2,1,0,0,'Modules','',0,'a:1:{s:2:\"en\";s:7:\"Modules\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules','/1/2','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(3,1,0,0,'General','',0,'a:1:{s:2:\"en\";s:7:\"General\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General','/1/3','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(4,3,0,0,'YesNo','',0,'a:1:{s:2:\"en\";s:6:\"Yes/No\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/YesNo','/1/3/4','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(5,4,0,1,'1 - Yes','',0,'b:0;','b:0;','/__SYSTEM__/General/YesNo/1 - Yes','/1/3/4/5','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(6,4,0,1,'2 - No','',0,'b:0;','b:0;','/__SYSTEM__/General/YesNo/2 - No','/1/3/4/6','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(10,3,0,0,'Publication Status (extended)','',0,'a:1:{s:2:\"en\";s:29:\"Publication status (extended)\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended','/1/3/10','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(11,10,0,1,'Pending','',0,'a:1:{s:2:\"en\";s:7:\"Pending\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Pending','/1/3/10/11','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(12,10,0,1,'Checked','',0,'a:1:{s:2:\"en\";s:7:\"Checked\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Checked','/1/3/10/12','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(13,10,0,1,'Approved','',0,'a:1:{s:2:\"en\";s:8:\"Approved\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Approved','/1/3/10/13','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(14,10,0,1,'On-line','',0,'a:1:{s:2:\"en\";s:7:\"On-line\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Online','/1/3/10/14','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(15,10,0,1,'Rejected','',0,'a:1:{s:2:\"en\";s:8:\"Rejected\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Rejected','/1/3/10/15','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(16,3,0,0,'Gender','',0,'a:1:{s:2:\"en\";s:6:\"Gender\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Gender','/1/3/16','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(17,16,0,1,'Male','',0,'a:1:{s:2:\"en\";s:4:\"Male\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Gender/Male','/1/3/16/17','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(18,16,0,1,'Female','',0,'a:1:{s:2:\"en\";s:6:\"Female\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Gender/Female','/1/3/16/18','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(19,3,0,0,'Title','',0,'a:1:{s:2:\"en\";s:5:\"Title\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Title','/1/3/19','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(20,19,0,1,'Mr','',0,'a:1:{s:2:\"en\";s:3:\"Mr.\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Title/Mr','/1/3/19/20','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(21,19,0,1,'Mrs','',0,'a:1:{s:2:\"en\";s:4:\"Mrs.\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Title/Mrs','/1/3/19/21','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(22,19,0,1,'Ms','',0,'a:1:{s:2:\"en\";s:3:\"Ms.\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Title/Ms','/1/3/19/22','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(23,19,0,1,'Miss','',0,'a:1:{s:2:\"en\";s:4:\"Miss\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Title/Miss','/1/3/19/23','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(24,19,0,1,'Dr','',0,'a:1:{s:2:\"en\";s:3:\"Dr.\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Title/Dr','/1/3/19/24','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(25,3,0,0,'ActiveStatus','',0,'a:1:{s:2:\"en\";s:15:\"Activity status\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus','/1/3/25','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(26,25,0,1,'Active','',0,'a:1:{s:2:\"en\";s:6:\"Active\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus/Active','/1/3/25/26','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(27,25,0,1,'Inactive','',0,'a:1:{s:2:\"en\";s:8:\"Inactive\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus/Inactive','/1/3/25/27','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(28,3,0,0,'Publication status (basic)','',0,'a:1:{s:2:\"en\";s:26:\"Publication status (basic)\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic','/1/3/28','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(29,28,0,1,'Pending','',0,'a:1:{s:2:\"en\";s:7:\"Pending\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic/Pending','/1/3/28/29','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(30,28,0,1,'Approved','',0,'a:1:{s:2:\"en\";s:8:\"Approved\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic/Approved','/1/3/28/30','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(31,1,0,0,'Users','',0,'a:1:{s:2:\"en\";s:5:\"Users\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Users','/1/31','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(32,2,0,0,'Global','',0,'a:1:{s:2:\"en\";s:6:\"Global\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global','/1/2/32','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(33,32,0,1,'Blogging','',0,'a:1:{s:2:\"en\";s:8:\"Blogging\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/Blogging','/1/2/32/33','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(34,32,0,1,'Music and audio','',0,'a:1:{s:2:\"en\";s:15:\"Music and audio\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/MusicAndAudio','/1/2/32/34','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(35,32,0,1,'Art and photography','',0,'a:1:{s:2:\"en\";s:19:\"Art and photography\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ArtAndPhotography','/1/2/32/35','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(36,32,0,1,'Writing and thinking','',0,'a:1:{s:2:\"en\";s:20:\"Writing and thinking\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/WritingAndThinking','/1/2/32/36','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(37,32,0,1,'Communications and media','',0,'a:1:{s:2:\"en\";s:24:\"Communications and media\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/CommunicationsAndMedia','/1/2/32/37','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(38,32,0,1,'Travel and culture','',0,'a:1:{s:2:\"en\";s:18:\"Travel and culture\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/TravelAndCulture','/1/2/32/38','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(39,32,0,1,'Science and technology','',0,'a:1:{s:2:\"en\";s:22:\"Science and technology\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ScienceAndTechnology','/1/2/32/39','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(40,32,0,1,'Sport and activities','',0,'a:1:{s:2:\"en\";s:20:\"Sport and activities\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/SportAndActivities','/1/2/32/40','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(41,32,0,1,'Business and work','',0,'a:1:{s:2:\"en\";s:17:\"Business and work\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/BusinessAndWork','/1/2/32/41','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(42,32,0,1,'Activism and action','',0,'a:1:{s:2:\"en\";s:19:\"Activism and action\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ActivismAndAction','/1/2/32/42','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0);

--
-- Table structure for table `z_categories_mapmeta`
--

DROP TABLE IF EXISTS `z_categories_mapmeta`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_categories_mapmeta` (
  `cmm_id` bigint(20) NOT NULL auto_increment,
  `cmm_meta_id` bigint(20) NOT NULL default '0',
  `cmm_category_id` bigint(20) NOT NULL default '0',
  `cmm_obj_status` varchar(1) NOT NULL default 'A',
  `cmm_cr_date` datetime NOT NULL default '1970-01-01 00:00:00',
  `cmm_cr_uid` int(11) NOT NULL default '0',
  `cmm_lu_date` datetime NOT NULL default '1970-01-01 00:00:00',
  `cmm_lu_uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cmm_id`),
  KEY `idx_categories_mapmeta` (`cmm_meta_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_categories_mapmeta`
--


--
-- Table structure for table `z_categories_mapobj`
--

DROP TABLE IF EXISTS `z_categories_mapobj`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_categories_mapobj` (
  `cmo_id` bigint(20) NOT NULL auto_increment,
  `cmo_modname` varchar(60) NOT NULL default '',
  `cmo_table` varchar(60) NOT NULL,
  `cmo_obj_id` bigint(20) NOT NULL default '0',
  `cmo_obj_idcolumn` varchar(60) NOT NULL default 'id',
  `cmo_reg_id` bigint(20) NOT NULL default '0',
  `cmo_category_id` bigint(20) NOT NULL default '0',
  `cmo_obj_status` varchar(1) NOT NULL default 'A',
  `cmo_cr_date` datetime NOT NULL default '1970-01-01 00:00:00',
  `cmo_cr_uid` int(11) NOT NULL default '0',
  `cmo_lu_date` datetime NOT NULL default '1970-01-01 00:00:00',
  `cmo_lu_uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cmo_id`),
  KEY `idx_categories_mapobj` (`cmo_modname`,`cmo_table`,`cmo_obj_id`,`cmo_obj_idcolumn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_categories_mapobj`
--


--
-- Table structure for table `z_categories_registry`
--

DROP TABLE IF EXISTS `z_categories_registry`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_categories_registry` (
  `crg_id` bigint(20) NOT NULL auto_increment,
  `crg_modname` varchar(60) NOT NULL default '',
  `crg_table` varchar(60) NOT NULL default '',
  `crg_property` varchar(60) NOT NULL default '',
  `crg_category_id` bigint(20) NOT NULL default '0',
  `crg_obj_status` varchar(1) NOT NULL default 'A',
  `crg_cr_date` datetime NOT NULL default '1970-01-01 00:00:00',
  `crg_cr_uid` int(11) NOT NULL default '0',
  `crg_lu_date` datetime NOT NULL default '1970-01-01 00:00:00',
  `crg_lu_uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`crg_id`),
  KEY `idx_categories_registry` (`crg_modname`,`crg_table`,`crg_property`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_categories_registry`
--


--
-- Table structure for table `z_group_applications`
--

DROP TABLE IF EXISTS `z_group_applications`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_group_applications` (
  `pn_app_id` bigint(20) NOT NULL auto_increment,
  `pn_uid` bigint(20) NOT NULL default '0',
  `pn_gid` bigint(20) NOT NULL default '0',
  `pn_application` longblob NOT NULL,
  `pn_status` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`pn_app_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_group_applications`
--


--
-- Table structure for table `z_group_membership`
--

DROP TABLE IF EXISTS `z_group_membership`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_group_membership` (
  `pn_gid` int(11) NOT NULL default '0',
  `pn_uid` int(11) NOT NULL default '0',
  KEY `gid_uid` (`pn_uid`,`pn_gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_group_membership`
--

INSERT INTO `z_group_membership` (`pn_gid`, `pn_uid`) VALUES (1,1),(2,2);

--
-- Table structure for table `z_group_perms`
--

DROP TABLE IF EXISTS `z_group_perms`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_group_perms` (
  `pn_pid` int(11) NOT NULL auto_increment,
  `pn_gid` int(11) NOT NULL default '0',
  `pn_sequence` int(11) NOT NULL default '0',
  `pn_realm` int(11) NOT NULL default '0',
  `pn_component` varchar(255) NOT NULL default '',
  `pn_instance` varchar(255) NOT NULL default '',
  `pn_level` int(11) NOT NULL default '0',
  `pn_bond` int(11) NOT NULL default '0',
  PRIMARY KEY  (`pn_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_group_perms`
--

INSERT INTO `z_group_perms` (`pn_pid`, `pn_gid`, `pn_sequence`, `pn_realm`, `pn_component`, `pn_instance`, `pn_level`, `pn_bond`) VALUES (1,2,1,0,'.*','.*',800,0),(2,-1,2,0,'ExtendedMenublock::','1:1:',0,0),(3,1,3,0,'.*','.*',300,0),(4,0,4,0,'ExtendedMenublock::','1:(1|2|3):',0,0),(5,0,5,0,'.*','.*',200,0);

--
-- Table structure for table `z_groups`
--

DROP TABLE IF EXISTS `z_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_groups` (
  `pn_gid` int(11) NOT NULL auto_increment,
  `pn_name` varchar(255) NOT NULL default '',
  `pn_gtype` smallint(6) NOT NULL default '0',
  `pn_description` varchar(200) NOT NULL default '',
  `pn_prefix` varchar(25) NOT NULL default '',
  `pn_state` smallint(6) NOT NULL default '0',
  `pn_nbuser` bigint(20) NOT NULL default '0',
  `pn_nbumax` bigint(20) NOT NULL default '0',
  `pn_link` bigint(20) NOT NULL default '0',
  `pn_uidmaster` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`pn_gid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_groups`
--

INSERT INTO `z_groups` (`pn_gid`, `pn_name`, `pn_gtype`, `pn_description`, `pn_prefix`, `pn_state`, `pn_nbuser`, `pn_nbumax`, `pn_link`, `pn_uidmaster`) VALUES (1,'Users',0,'By default, all users are made members of this group.','usr',0,0,0,0,0),(2,'Administrators',0,'By default, all administrators are made members of this group.','adm',0,0,0,0,0);

--
-- Table structure for table `z_hooks`
--

DROP TABLE IF EXISTS `z_hooks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_hooks` (
  `pn_id` int(11) NOT NULL auto_increment,
  `pn_object` varchar(64) NOT NULL default '',
  `pn_action` varchar(64) NOT NULL default '',
  `pn_smodule` varchar(64) NOT NULL default '',
  `pn_stype` varchar(64) NOT NULL default '',
  `pn_tarea` varchar(64) NOT NULL default '',
  `pn_tmodule` varchar(64) NOT NULL default '',
  `pn_ttype` varchar(64) NOT NULL default '',
  `pn_tfunc` varchar(64) NOT NULL default '',
  `pn_sequence` int(11) NOT NULL default '0',
  PRIMARY KEY  (`pn_id`),
  KEY `smodule` (`pn_smodule`),
  KEY `smodule_tmodule` (`pn_smodule`,`pn_tmodule`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_hooks`
--


--
-- Table structure for table `z_module_deps`
--

DROP TABLE IF EXISTS `z_module_deps`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_module_deps` (
  `pn_id` bigint(20) NOT NULL auto_increment,
  `pn_modid` int(11) NOT NULL default '0',
  `pn_modname` varchar(64) NOT NULL default '',
  `pn_minversion` varchar(10) NOT NULL default '',
  `pn_maxversion` varchar(10) NOT NULL default '',
  `pn_status` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`pn_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_module_deps`
--


--
-- Table structure for table `z_module_vars`
--

DROP TABLE IF EXISTS `z_module_vars`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_module_vars` (
  `pn_id` int(11) NOT NULL auto_increment,
  `pn_modname` varchar(64) NOT NULL default '',
  `pn_name` varchar(64) NOT NULL default '',
  `pn_value` longtext,
  PRIMARY KEY  (`pn_id`),
  KEY `mod_var` (`pn_modname`,`pn_name`)
) ENGINE=MyISAM AUTO_INCREMENT=200 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_module_vars`
--

INSERT INTO `z_module_vars` (`pn_id`, `pn_modname`, `pn_name`, `pn_value`) VALUES (1,'Modules','itemsperpage','i:25;'),(2,'Admin','modulesperrow','i:3;'),(3,'Admin','itemsperpage','i:15;'),(4,'Admin','defaultcategory','i:5;'),(5,'Admin','modulestylesheet','s:11:\"navtabs.css\";'),(6,'Admin','admingraphic','i:1;'),(7,'Admin','startcategory','i:1;'),(8,'Admin','ignoreinstallercheck','0'),(9,'Admin','admintheme','s:0:\"\";'),(10,'Admin','displaynametype','i:1;'),(11,'Admin','moduledescription','i:1;'),(12,'Permissions','filter','i:1;'),(13,'Permissions','warnbar','i:1;'),(14,'Permissions','rowview','i:20;'),(15,'Permissions','rowedit','i:20;'),(16,'Permissions','lockadmin','i:1;'),(17,'Permissions','adminid','i:1;'),(18,'Groups','itemsperpage','i:25;'),(19,'Groups','defaultgroup','i:1;'),(20,'Groups','mailwarning','0'),(21,'Groups','hideclosed','0'),(22,'Blocks','collapseable','0'),(23,'Users','itemsperpage','i:25;'),(24,'Users','accountdisplaygraphics','i:1;'),(25,'Users','accountitemsperpage','i:25;'),(26,'Users','accountitemsperrow','i:5;'),(27,'Users','changepassword','i:1;'),(28,'Users','changeemail','i:1;'),(29,'Users','reg_allowreg','i:1;'),(30,'Users','reg_verifyemail','i:1;'),(31,'Users','reg_Illegalusername','s:87:\"root adm linux webmaster admin god administrator administrador nobody anonymous anonimo\";'),(32,'Users','reg_Illegaldomains','s:0:\"\";'),(33,'Users','reg_Illegaluseragents','s:0:\"\";'),(34,'Users','reg_noregreasons','s:51:\"Sorry! New user registration is currently disabled.\";'),(35,'Users','reg_uniemail','i:1;'),(36,'Users','reg_notifyemail','s:0:\"\";'),(37,'Users','reg_optitems','0'),(38,'Users','userimg','s:11:\"images/menu\";'),(39,'Users','avatarpath','s:13:\"images/avatar\";'),(40,'Users','allowgravatars','i:1;'),(41,'Users','gravatarimage','s:12:\"gravatar.gif\";'),(42,'Users','minage','i:13;'),(43,'Users','minpass','i:5;'),(44,'Users','anonymous','s:5:\"Guest\";'),(45,'Users','loginviaoption','0'),(46,'Users','lowercaseuname','0'),(47,'Users','moderation','0'),(48,'Users','hash_method','s:6:\"sha256\";'),(49,'Users','login_redirect','i:1;'),(50,'Users','reg_question','s:0:\"\";'),(51,'Users','reg_answer','s:0:\"\";'),(52,'Users','idnnames','i:1;'),(53,'Users','use_password_strength_meter','0'),(54,'Theme','modulesnocache','s:0:\"\";'),(55,'Theme','enablecache','b:0;'),(56,'Theme','compile_check','1'),(57,'Theme','cache_lifetime','i:3600;'),(58,'Theme','force_compile','b:0;'),(59,'Theme','trimwhitespace','b:0;'),(60,'Theme','maxsizeforlinks','i:30;'),(61,'Theme','itemsperpage','i:25;'),(62,'Theme','cssjscombine','b:0;'),(63,'Theme','cssjscompress','b:0;'),(64,'Theme','cssjsminify','b:0;'),(65,'Theme','cssjscombine_lifetime','i:3600;'),(66,'Theme','render_compile_check','1'),(67,'Theme','render_force_compile','1'),(68,'Theme','render_cache','b:0;'),(69,'Theme','render_expose_template','b:0;'),(70,'Theme','render_lifetime','i:3600;'),(71,'/PNConfig','debug','s:1:\"0\";'),(72,'/PNConfig','sitename','s:9:\"Site name\";'),(73,'/PNConfig','slogan','s:16:\"Site description\";'),(74,'/PNConfig','metakeywords','s:253:\"zikula, community, portal, portal web, open source, gpl, web site, website, weblog, blog, content management, content management system, web content management, web content management system, enterprise web content management, cms, application framework\";'),(75,'/PNConfig','startdate','s:7:\"05/2010\";'),(76,'/PNConfig','adminmail','s:19:\"example@example.com\";'),(77,'/PNConfig','Default_Theme','s:9:\"andreas08\";'),(78,'/PNConfig','anonymous','s:5:\"Guest\";'),(79,'/PNConfig','timezone_offset','s:1:\"0\";'),(80,'/PNConfig','timezone_server','s:1:\"0\";'),(81,'/PNConfig','funtext','s:1:\"1\";'),(82,'/PNConfig','reportlevel','s:1:\"0\";'),(83,'/PNConfig','startpage','s:0:\"\";'),(84,'/PNConfig','Version_Num','s:9:\"1.3.0-dev\";'),(85,'/PNConfig','Version_ID','s:6:\"Zikula\";'),(86,'/PNConfig','Version_Sub','s:5:\"cinco\";'),(87,'/PNConfig','debug_sql','s:1:\"0\";'),(88,'/PNConfig','multilingual','s:1:\"1\";'),(89,'/PNConfig','useflags','s:1:\"0\";'),(90,'/PNConfig','theme_change','s:1:\"0\";'),(91,'/PNConfig','UseCompression','s:1:\"0\";'),(92,'/PNConfig','errordisplay','i:1;'),(93,'/PNConfig','errorlog','0'),(94,'/PNConfig','errorlogtype','0'),(95,'/PNConfig','errormailto','s:14:\"me@example.com\";'),(96,'/PNConfig','siteoff','0'),(97,'/PNConfig','siteoffreason','s:0:\"\";'),(98,'/PNConfig','starttype','s:0:\"\";'),(99,'/PNConfig','startfunc','s:0:\"\";'),(100,'/PNConfig','startargs','s:0:\"\";'),(101,'/PNConfig','entrypoint','s:9:\"index.php\";'),(102,'/PNConfig','language_detect','0'),(103,'/PNConfig','shorturls','b:0;'),(104,'/PNConfig','shorturlstype','s:1:\"0\";'),(105,'/PNConfig','shorturlsext','s:4:\"html\";'),(106,'/PNConfig','shorturlsseparator','s:1:\"-\";'),(107,'/PNConfig','shorturlsstripentrypoint','b:0;'),(108,'/PNConfig','shorturlsdefaultmodule','s:0:\"\";'),(109,'/PNConfig','profilemodule','s:0:\"\";'),(110,'/PNConfig','messagemodule','s:0:\"\";'),(111,'/PNConfig','languageurl','0'),(112,'/PNConfig','ajaxtimeout','i:5000;'),(113,'/PNConfig','permasearch','s:161:\"À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü\";'),(114,'/PNConfig','permareplace','s:107:\"A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,s,a,A,o,O,u,U\";'),(115,'/PNConfig','language','s:3:\"eng\";'),(116,'/PNConfig','locale','s:2:\"en\";'),(117,'/PNConfig','language_i18n','s:2:\"en\";'),(118,'/PNConfig','language_bc','i:1;'),(119,'SecurityCenter','itemsperpage','i:10;'),(120,'/PNConfig','enableanticracker','i:1;'),(121,'/PNConfig','emailhackattempt','i:1;'),(122,'/PNConfig','loghackattempttodb','i:1;'),(123,'/PNConfig','onlysendsummarybyemail','i:1;'),(124,'/PNConfig','updatecheck','i:1;'),(125,'/PNConfig','updatefrequency','i:7;'),(126,'/PNConfig','updatelastchecked','i:1274299219;'),(127,'/PNConfig','updateversion','s:5:\"1.2.3\";'),(128,'/PNConfig','keyexpiry','0'),(129,'/PNConfig','sessionauthkeyua','b:0;'),(130,'/PNConfig','secure_domain','s:0:\"\";'),(131,'/PNConfig','signcookies','i:1;'),(132,'/PNConfig','signingkey','s:40:\"4b58289c18b6f916b31d0a42e78808d11fcbbc83\";'),(133,'/PNConfig','seclevel','s:6:\"Medium\";'),(134,'/PNConfig','secmeddays','i:7;'),(135,'/PNConfig','secinactivemins','i:20;'),(136,'/PNConfig','sessionstoretofile','0'),(137,'/PNConfig','sessionsavepath','s:0:\"\";'),(138,'/PNConfig','gc_probability','i:100;'),(139,'/PNConfig','anonymoussessions','i:1;'),(140,'/PNConfig','sessionrandregenerate','1'),(141,'/PNConfig','sessionregenerate','1'),(142,'/PNConfig','sessionregeneratefreq','i:10;'),(143,'/PNConfig','sessionipcheck','0'),(144,'/PNConfig','sessionname','s:4:\"ZSID\";'),(145,'/PNConfig','filtergetvars','i:1;'),(146,'/PNConfig','filterpostvars','i:1;'),(147,'/PNConfig','filtercookievars','i:1;'),(148,'/PNConfig','outputfilter','i:1;'),(149,'/PNConfig','htmlpurifierlocation','s:42:\"system/SecurityCenter/vendor/htmlpurifier/\";'),(150,'/PNConfig','useids','0'),(151,'/PNConfig','idsfilter','s:3:\"xml\";'),(152,'/PNConfig','idsimpactthresholdone','i:1;'),(153,'/PNConfig','idsimpactthresholdtwo','i:10;'),(154,'/PNConfig','idsimpactthresholdthree','i:25;'),(155,'/PNConfig','idsimpactthresholdfour','i:75;'),(156,'/PNConfig','idsimpactmode','i:1;'),(157,'/PNConfig','summarycontent','s:1130:\"For the attention of %sitename% administration staff:\n\nOn %date% at %time%, Zikula detected that somebody tried to interact with the site in a way that may have been intended compromise its security. This is not necessarily the case: it could have been caused by work you were doing on the site, or may have been due to some other reason. In any case, it was detected and blocked. \n\nThe suspicious activity was recognised in \'%filename%\' at line %linenumber%.\n\nType: %type%. \n\nAdditional information: %additionalinfo%.\n\nBelow is logged information that may help you identify what happened and who was responsible.\n\n=====================================\nInformation about the user:\n=====================================\nUser name:  %username%\nUser\'s e-mail address: %useremail%\nUser\'s real name: %userrealname%\n\n=====================================\nIP numbers (if this was a cracker, the IP numbers may not be the true point of origin)\n=====================================\nIP according to HTTP_CLIENT_IP: %httpclientip%\nIP according to REMOTE_ADDR: %remoteaddr%\nIP according to GetHostByName($REMOTE_ADDR): %gethostbyremoteaddr%\n\";'),(158,'/PNConfig','fullcontent','s:1289:\"=====================================\nInformation in the $_REQUEST array\n=====================================\n%requestarray%\n\n=====================================\nInformation in the $_GET array\n(variables that may have been in the URL string or in a \'GET\'-type form)\n=====================================\n%getarray%\n\n=====================================\nInformation in the $_POST array\n(visible and invisible form elements)\n=====================================\n%postarray%\n\n=====================================\nBrowser information\n=====================================\n%browserinfo%\n\n=====================================\nInformation in the $_SERVER array\n=====================================\n%serverarray%\n\n=====================================\nInformation in the $_ENV array\n=====================================\n%envarray%\n\n=====================================\nInformation in the $_COOKIE array\n=====================================\n%cookiearray%\n\n=====================================\nInformation in the $_FILES array\n=====================================\n%filearray%\n\n=====================================\nInformation in the $_SESSION array\n(session information -- variables starting with PNSV are Zikula session variables)\n=====================================\n%sessionarray%\n\";'),(159,'/PNConfig','usehtaccessbans','0'),(160,'/PNConfig','extrapostprotection','0'),(161,'/PNConfig','extragetprotection','0'),(162,'/PNConfig','checkmultipost','0'),(163,'/PNConfig','maxmultipost','i:4;'),(164,'/PNConfig','cpuloadmonitor','0'),(165,'/PNConfig','cpumaxload','d:10;'),(166,'/PNConfig','ccisessionpath','s:0:\"\";'),(167,'/PNConfig','htaccessfilelocation','s:9:\".htaccess\";'),(168,'/PNConfig','nocookiebanthreshold','i:10;'),(169,'/PNConfig','nocookiewarningthreshold','i:2;'),(170,'/PNConfig','fastaccessbanthreshold','i:40;'),(171,'/PNConfig','fastaccesswarnthreshold','i:10;'),(172,'/PNConfig','javababble','0'),(173,'/PNConfig','javaencrypt','0'),(174,'/PNConfig','preservehead','0'),(175,'/PNConfig','filterarrays','i:1;'),(176,'/PNConfig','htmlentities','s:1:\"1\";'),(177,'/PNConfig','AllowableHTML','a:83:{s:3:\"!--\";i:2;s:1:\"a\";i:2;s:4:\"abbr\";i:0;s:7:\"acronym\";i:0;s:7:\"address\";i:0;s:6:\"applet\";i:0;s:4:\"area\";i:0;s:1:\"b\";i:2;s:4:\"base\";i:0;s:8:\"basefont\";i:0;s:3:\"bdo\";i:0;s:3:\"big\";i:0;s:10:\"blockquote\";i:2;s:2:\"br\";i:2;s:6:\"button\";i:0;s:7:\"caption\";i:0;s:6:\"center\";i:2;s:4:\"cite\";i:0;s:4:\"code\";i:0;s:3:\"col\";i:0;s:8:\"colgroup\";i:0;s:3:\"del\";i:0;s:3:\"dfn\";i:0;s:3:\"dir\";i:0;s:3:\"div\";i:2;s:2:\"dl\";i:1;s:2:\"dd\";i:1;s:2:\"dt\";i:1;s:2:\"em\";i:2;s:5:\"embed\";i:0;s:8:\"fieldset\";i:0;s:4:\"font\";i:0;s:4:\"form\";i:0;s:2:\"h1\";i:1;s:2:\"h2\";i:1;s:2:\"h3\";i:1;s:2:\"h4\";i:1;s:2:\"h5\";i:1;s:2:\"h6\";i:1;s:2:\"hr\";i:2;s:1:\"i\";i:2;s:6:\"iframe\";i:0;s:3:\"img\";i:0;s:5:\"input\";i:0;s:3:\"ins\";i:0;s:3:\"kbd\";i:0;s:5:\"label\";i:0;s:6:\"legend\";i:0;s:2:\"li\";i:2;s:3:\"map\";i:0;s:7:\"marquee\";i:0;s:4:\"menu\";i:0;s:4:\"nobr\";i:0;s:6:\"object\";i:0;s:2:\"ol\";i:2;s:8:\"optgroup\";i:0;s:6:\"option\";i:0;s:1:\"p\";i:2;s:5:\"param\";i:0;s:3:\"pre\";i:2;s:1:\"q\";i:0;s:1:\"s\";i:0;s:4:\"samp\";i:0;s:6:\"script\";i:0;s:6:\"select\";i:0;s:5:\"small\";i:0;s:4:\"span\";i:0;s:6:\"strike\";i:0;s:6:\"strong\";i:2;s:3:\"sub\";i:0;s:3:\"sup\";i:0;s:5:\"table\";i:2;s:5:\"tbody\";i:0;s:2:\"td\";i:2;s:8:\"textarea\";i:0;s:5:\"tfoot\";i:0;s:2:\"th\";i:2;s:5:\"thead\";i:0;s:2:\"tr\";i:2;s:2:\"tt\";i:2;s:1:\"u\";i:0;s:2:\"ul\";i:2;s:3:\"var\";i:0;}'),(178,'Categories','userrootcat','s:17:\"/__SYSTEM__/Users\";'),(179,'Categories','allowusercatedit','0'),(180,'Categories','autocreateusercat','0'),(181,'Categories','autocreateuserdefaultcat','0'),(182,'Categories','userdefaultcatname','s:7:\"Default\";'),(183,'Mailer','mailertype','i:1;'),(184,'Mailer','charset','s:5:\"utf-8\";'),(185,'Mailer','encoding','s:4:\"8bit\";'),(186,'Mailer','html','b:0;'),(187,'Mailer','wordwrap','i:50;'),(188,'Mailer','msmailheaders','b:0;'),(189,'Mailer','sendmailpath','s:18:\"/usr/sbin/sendmail\";'),(190,'Mailer','smtpauth','b:0;'),(191,'Mailer','smtpserver','s:9:\"localhost\";'),(192,'Mailer','smtpport','i:25;'),(193,'Mailer','smtptimeout','i:10;'),(194,'Mailer','smtpusername','s:0:\"\";'),(195,'Mailer','smtppassword','s:0:\"\";'),(196,'Search','itemsperpage','i:10;'),(197,'Search','limitsummary','i:255;'),(198,'/PNConfig','log_last_rotate','i:1274299217;'),(199,'/PNConfig','htmlpurifierConfig','s:73:\"a:1:{s:5:\"Cache\";a:1:{s:14:\"SerializerPath\";s:19:\"ztemp/purifierCache\";}}\";');

--
-- Table structure for table `z_modules`
--

DROP TABLE IF EXISTS `z_modules`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_modules` (
  `pn_id` int(11) NOT NULL auto_increment,
  `pn_name` varchar(64) NOT NULL default '',
  `pn_type` smallint(6) NOT NULL default '0',
  `pn_displayname` varchar(64) NOT NULL default '',
  `pn_url` varchar(64) NOT NULL default '',
  `pn_description` varchar(255) NOT NULL default '',
  `pn_regid` int(11) NOT NULL default '0',
  `pn_directory` varchar(64) NOT NULL default '',
  `pn_version` varchar(10) NOT NULL default '0',
  `pn_official` smallint(6) NOT NULL default '0',
  `pn_author` varchar(255) NOT NULL default '',
  `pn_contact` varchar(255) NOT NULL default '',
  `pn_admin_capable` smallint(6) NOT NULL default '0',
  `pn_user_capable` smallint(6) NOT NULL default '0',
  `pn_profile_capable` smallint(6) NOT NULL default '0',
  `pn_message_capable` smallint(6) NOT NULL default '0',
  `pn_state` int(11) NOT NULL default '0',
  `pn_credits` varchar(255) NOT NULL default '',
  `pn_changelog` varchar(255) NOT NULL default '',
  `pn_help` varchar(255) NOT NULL default '',
  `pn_license` varchar(255) NOT NULL default '',
  `pn_securityschema` longtext NOT NULL,
  PRIMARY KEY  (`pn_id`),
  KEY `state` (`pn_state`),
  KEY `mod_state` (`pn_name`,`pn_state`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_modules`
--

INSERT INTO `z_modules` (`pn_id`, `pn_name`, `pn_type`, `pn_displayname`, `pn_url`, `pn_description`, `pn_regid`, `pn_directory`, `pn_version`, `pn_official`, `pn_author`, `pn_contact`, `pn_admin_capable`, `pn_user_capable`, `pn_profile_capable`, `pn_message_capable`, `pn_state`, `pn_credits`, `pn_changelog`, `pn_help`, `pn_license`, `pn_securityschema`) VALUES (1,'Modules',3,'Modules manager','modules','Provides support for modules, and incorporates an interface for adding, removing and administering core system modules and add-on modules.',0,'Modules','3.7.1',1,'Jim McDonald, Mark West','http://www.zikula.org',1,0,0,0,3,'','','','','a:1:{s:9:\"Modules::\";s:2:\"::\";}'),(2,'Admin',3,'Admin panel manager','adminpanel','Provides the site\'s administration panel, and the ability to configure and manage it.',0,'Admin','1.8',1,'Mark West','http://www.markwest.me.uk/',1,0,0,0,3,'','','','','a:1:{s:7:\"Admin::\";s:38:\"Admin Category name::Admin Category ID\";}'),(3,'Blocks',3,'Blocks manager','blocks','Provides an interface for adding, removing and administering the site\'s side and center blocks.',0,'Blocks','3.6',1,'Jim McDonald, Mark West','http://www.mcdee.net/, http://www.markwest.me.uk/',1,1,0,0,3,'','','','','a:2:{s:8:\"Blocks::\";s:30:\"Block key:Block title:Block ID\";s:16:\"Blocks::position\";s:26:\"Position name::Position ID\";}'),(4,'Categories',3,'Categories manager','categories','Provides support for categorisation of content in other modules, and an interface for adding, removing and administering categories.',0,'Categories','1.1',1,'Robert Gasch','rgasch@gmail.com',1,1,0,0,3,'','','','','a:1:{s:20:\"Categories::Category\";s:40:\"Category ID:Category Path:Category IPath\";}'),(5,'Errors',3,'Errors','errors','Provides the core system of the site with error-logging capability.',0,'Errors','1.1',1,'Brian Lindner <Furbo>','furbo@sigtauonline.com',0,1,0,0,3,'','','','','a:1:{s:8:\"Errors::\";s:2:\"::\";}'),(6,'Groups',3,'Groups manager','groups','Provides support for user groups, and incorporates an interface for adding, removing and administering them.',0,'Groups','2.3',1,'Mark West, Franky Chestnut, Michael Halbook','http://www.markwest.me.uk/, http://dev.pnconcept.com, http://www.halbrooktech.com',1,1,0,0,3,'','','','','a:1:{s:8:\"Groups::\";s:10:\"Group ID::\";}'),(7,'Mailer',3,'Mailer','mailer','Provides mail-sending functionality for communication with the site\'s users, and an interface for managing the e-mail service settings used by the mailer.',0,'Mailer','1.3',1,'Mark West','http://www.markwest.me.uk/',1,0,0,0,3,'','','','','a:1:{s:8:\"Mailer::\";s:2:\"::\";}'),(8,'ObjectData',3,'Object data','objectdata','Provides a framework for implementing and managing object-model data items, for use by other modules and applications.',0,'ObjectData','1.03',0,'Robert Gasch','rgasch@gmail.com',0,0,0,0,3,'','','','','a:1:{s:12:\"ObjectData::\";s:2:\"::\";}'),(9,'PageLock',3,'Page lock','pagelock','Provides the ability to lock pages when they are in use, for content and access control.',0,'PageLock','1.1',1,'Jorn Wildt','http://www.elfisk.dk',0,0,0,0,3,'','','','','a:1:{s:10:\"PageLock::\";s:2:\"::\";}'),(10,'Permissions',3,'Permission manager','permissions','Provides an interface for fine-grained management of accessibility of the site\'s functionality and content through permission rules.',0,'Permissions','1.1',1,'Jim McDonald, M.Maes','http://www.mcdee.net/, http://www.mmaes.com',1,0,0,0,3,'','','','','a:1:{s:13:\"Permissions::\";s:2:\"::\";}'),(11,'Search',3,'Site search engine','search','Provides an engine for searching within the site, and an interface for managing search page settings.',0,'Search','1.5',1,'Patrick Kellum','http://www.ctarl-ctarl.com',1,1,0,0,3,'','','','','a:1:{s:8:\"Search::\";s:13:\"Module name::\";}'),(12,'SecurityCenter',3,'Security center','securitycenter','Provides the ability to manage site security. It logs attempted hacks and similar events, and incorporates a user interface for customising alerting and security settings.',0,'SecurityCenter','1.4.1',1,'Mark West','http://www.zikula.org',1,0,0,0,3,'','','','','a:1:{s:16:\"SecurityCenter::\";s:16:\"hackid::hacktime\";}'),(13,'Settings',3,'General settings','settings','Provides an interface for managing the site\'s general settings, i.e. site start page settings, multi-lingual settings, error reporting options and various other features that are not administered within other modules.',0,'Settings','2.9.2',1,'Simon Wunderlin','',1,0,0,0,3,'','','','','a:1:{s:10:\"Settings::\";s:2:\"::\";}'),(14,'SysInfo',3,'System info','sysinfo','Provides detailed information reports about the system configuration and environment, for tracking and troubleshooting purposes.',0,'SysInfo','1.1',1,'Simon Birtwistle','hammerhead@zikula.org',1,0,0,0,3,'','','','','a:1:{s:9:\"SysInfo::\";s:2:\"::\";}'),(15,'Theme',3,'Themes manager','theme','Provides the site\'s theming system, and an interface for managing themes, to control the site\'s presentation and appearance.',0,'Theme','3.4',1,'Mark West','http://www.markwest.me.uk/',1,1,0,0,3,'','','','','a:1:{s:7:\"Theme::\";s:12:\"Theme name::\";}'),(16,'Users',3,'Users manager','users','Provides an interface for configuring and administering registered user accounts. Incorporates all needed functionality, but can work in close unison with the third party profile module configured in the general settings of the site.',0,'Users','1.16',1,'Xiaoyu Huang, Drak','class007@sina.com, drak@zikula.org',1,1,0,0,3,'','','','','a:2:{s:7:\"Users::\";s:14:\"Uname::User ID\";s:16:\"Users::MailUsers\";s:2:\"::\";}'),(17,'Workflow',3,'Workflow engine','workflow','Provides a workflow engine, and an interface for designing and administering workflows comprised of actions and events.',0,'Workflow','1.1',1,'Drak','drak@hostnuke.com',0,0,0,0,3,'','','','','a:1:{s:10:\"Workflow::\";s:2:\"::\";}'),(18,'Gettext',2,'Gettext','gettext','Extract translation strings from themes and modules',0,'Gettext','1.0',1,'Drak','drak@zikula.org',0,1,0,0,3,'','','','','a:1:{s:9:\"Gettext::\";s:2:\"::\";}');

--
-- Table structure for table `z_objectdata_attributes`
--

DROP TABLE IF EXISTS `z_objectdata_attributes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_objectdata_attributes` (
  `oba_id` bigint(20) NOT NULL auto_increment,
  `oba_attribute_name` varchar(80) NOT NULL default '',
  `oba_object_id` bigint(20) NOT NULL default '0',
  `oba_object_type` varchar(80) NOT NULL default '',
  `oba_value` longtext NOT NULL,
  `oba_obj_status` varchar(1) NOT NULL default 'A',
  `oba_cr_date` datetime NOT NULL default '1970-01-01 00:00:00',
  `oba_cr_uid` int(11) NOT NULL default '0',
  `oba_lu_date` datetime NOT NULL default '1970-01-01 00:00:00',
  `oba_lu_uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`oba_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_objectdata_attributes`
--

INSERT INTO `z_objectdata_attributes` (`oba_id`, `oba_attribute_name`, `oba_object_id`, `oba_object_type`, `oba_value`, `oba_obj_status`, `oba_cr_date`, `oba_cr_uid`, `oba_lu_date`, `oba_lu_uid`) VALUES (1,'code',5,'categories_category','Y','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(2,'code',6,'categories_category','N','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(3,'code',11,'categories_category','P','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(4,'code',12,'categories_category','C','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(5,'code',13,'categories_category','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(6,'code',14,'categories_category','O','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(7,'code',15,'categories_category','R','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(8,'code',17,'categories_category','M','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(9,'code',18,'categories_category','F','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(10,'code',26,'categories_category','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(11,'code',27,'categories_category','I','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(12,'code',29,'categories_category','P','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0),(13,'code',30,'categories_category','A','A','2010-05-19 20:00:05',0,'2010-05-19 20:00:05',0);

--
-- Table structure for table `z_objectdata_log`
--

DROP TABLE IF EXISTS `z_objectdata_log`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_objectdata_log` (
  `obl_id` bigint(20) NOT NULL auto_increment,
  `obl_object_type` varchar(80) NOT NULL default '',
  `obl_object_id` bigint(20) NOT NULL default '0',
  `obl_op` varchar(16) NOT NULL default '',
  `obl_diff` longtext,
  `obl_obj_status` varchar(1) NOT NULL default 'A',
  `obl_cr_date` datetime NOT NULL default '1970-01-01 00:00:00',
  `obl_cr_uid` int(11) NOT NULL default '0',
  `obl_lu_date` datetime NOT NULL default '1970-01-01 00:00:00',
  `obl_lu_uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`obl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_objectdata_log`
--


--
-- Table structure for table `z_objectdata_meta`
--

DROP TABLE IF EXISTS `z_objectdata_meta`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_objectdata_meta` (
  `obm_id` bigint(20) NOT NULL auto_increment,
  `obm_module` varchar(40) NOT NULL default '',
  `obm_table` varchar(40) NOT NULL default '',
  `obm_idcolumn` varchar(40) NOT NULL default '',
  `obm_obj_id` bigint(20) NOT NULL default '0',
  `obm_permissions` varchar(255) default NULL,
  `obm_dc_title` varchar(80) default NULL,
  `obm_dc_author` varchar(80) default NULL,
  `obm_dc_subject` varchar(255) default NULL,
  `obm_dc_keywords` varchar(128) default NULL,
  `obm_dc_description` varchar(255) default NULL,
  `obm_dc_publisher` varchar(128) default NULL,
  `obm_dc_contributor` varchar(128) default NULL,
  `obm_dc_startdate` datetime default '1970-01-01 00:00:00',
  `obm_dc_enddate` datetime default '1970-01-01 00:00:00',
  `obm_dc_type` varchar(128) default NULL,
  `obm_dc_format` varchar(128) default NULL,
  `obm_dc_uri` varchar(255) default NULL,
  `obm_dc_source` varchar(128) default NULL,
  `obm_dc_language` varchar(32) default NULL,
  `obm_dc_relation` varchar(255) default NULL,
  `obm_dc_coverage` varchar(64) default NULL,
  `obm_dc_entity` varchar(64) default NULL,
  `obm_dc_comment` varchar(255) default NULL,
  `obm_dc_extra` varchar(255) default NULL,
  `obm_obj_status` varchar(1) NOT NULL default 'A',
  `obm_cr_date` datetime NOT NULL default '1970-01-01 00:00:00',
  `obm_cr_uid` int(11) NOT NULL default '0',
  `obm_lu_date` datetime NOT NULL default '1970-01-01 00:00:00',
  `obm_lu_uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`obm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_objectdata_meta`
--


--
-- Table structure for table `z_pagelock`
--

DROP TABLE IF EXISTS `z_pagelock`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_pagelock` (
  `plock_id` int(11) NOT NULL auto_increment,
  `plock_name` varchar(100) NOT NULL default '',
  `plock_cdate` datetime NOT NULL,
  `plock_edate` datetime NOT NULL,
  `plock_session` varchar(50) NOT NULL,
  `plock_title` varchar(100) NOT NULL,
  `plock_ipno` varchar(30) NOT NULL,
  PRIMARY KEY  (`plock_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_pagelock`
--


--
-- Table structure for table `z_sc_anticracker`
--

DROP TABLE IF EXISTS `z_sc_anticracker`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_sc_anticracker` (
  `pn_hid` int(11) NOT NULL auto_increment,
  `pn_hacktime` varchar(20) default NULL,
  `pn_hackfile` varchar(255) default NULL,
  `pn_hackline` int(11) default NULL,
  `pn_hacktype` varchar(255) default NULL,
  `pn_hackinfo` longtext,
  `pn_userid` int(11) default NULL,
  `pn_browserinfo` longtext,
  `pn_requestarray` longtext,
  `pn_gettarray` longtext,
  `pn_postarray` longtext,
  `pn_serverarray` longtext,
  `pn_envarray` longtext,
  `pn_cookiearray` longtext,
  `pn_filesarray` longtext,
  `pn_sessionarray` longtext,
  PRIMARY KEY  (`pn_hid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_sc_anticracker`
--


--
-- Table structure for table `z_sc_intrusion`
--

DROP TABLE IF EXISTS `z_sc_intrusion`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_sc_intrusion` (
  `ids_id` int(11) NOT NULL auto_increment,
  `ids_name` varchar(128) NOT NULL default '',
  `ids_tag` varchar(40) default NULL,
  `ids_value` longtext NOT NULL,
  `ids_page` longtext NOT NULL,
  `ids_uid` bigint(20) default NULL,
  `ids_ip` varchar(40) NOT NULL default '',
  `ids_impact` bigint(20) NOT NULL default '0',
  `ids_date` datetime NOT NULL,
  PRIMARY KEY  (`ids_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_sc_intrusion`
--


--
-- Table structure for table `z_sc_log_event`
--

DROP TABLE IF EXISTS `z_sc_log_event`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_sc_log_event` (
  `lge_id` int(11) NOT NULL auto_increment,
  `lge_date` datetime default NULL,
  `lge_uid` bigint(20) default NULL,
  `lge_component` varchar(64) default NULL,
  `lge_module` varchar(64) default NULL,
  `lge_type` varchar(64) default NULL,
  `lge_function` varchar(64) default NULL,
  `lge_sec_component` varchar(64) default NULL,
  `lge_sec_instance` varchar(64) default NULL,
  `lge_sec_permission` varchar(64) default NULL,
  `lge_message` varchar(255) default NULL,
  PRIMARY KEY  (`lge_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_sc_log_event`
--


--
-- Table structure for table `z_search_result`
--

DROP TABLE IF EXISTS `z_search_result`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_search_result` (
  `sres_id` bigint(20) NOT NULL auto_increment,
  `sres_title` varchar(255) NOT NULL default '',
  `sres_text` longtext,
  `sres_module` varchar(100) default NULL,
  `sres_extra` varchar(100) default NULL,
  `sres_created` datetime default NULL,
  `sres_found` datetime default NULL,
  `sres_sesid` varchar(50) default NULL,
  PRIMARY KEY  (`sres_id`),
  KEY `title` (`sres_title`),
  KEY `module` (`sres_module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_search_result`
--


--
-- Table structure for table `z_search_stat`
--

DROP TABLE IF EXISTS `z_search_stat`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_search_stat` (
  `pn_id` bigint(20) NOT NULL auto_increment,
  `pn_search` varchar(50) NOT NULL default '',
  `pn_count` bigint(20) NOT NULL default '0',
  `pn_date` date default NULL,
  PRIMARY KEY  (`pn_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_search_stat`
--


--
-- Table structure for table `z_session_info`
--

DROP TABLE IF EXISTS `z_session_info`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_session_info` (
  `pn_sessid` varchar(40) NOT NULL default '',
  `pn_ipaddr` varchar(32) NOT NULL default '',
  `pn_lastused` datetime default '1970-01-01 00:00:00',
  `pn_uid` bigint(20) default '0',
  `pn_remember` smallint(6) NOT NULL default '0',
  `pn_vars` longtext NOT NULL,
  PRIMARY KEY  (`pn_sessid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_session_info`
--

INSERT INTO `z_session_info` (`pn_sessid`, `pn_ipaddr`, `pn_lastused`, `pn_uid`, `pn_remember`, `pn_vars`) VALUES ('9j6tunlo74udsg56o2sp1rm376g0l69o','90f36fe0d9daabc615751d3e6c960f2c','2010-05-19 20:00:28',2,0,'iLTour7by5NsUgjf7Q1fIgf4xUCmFcAdImddGMRkV6A0pWTr8GsO1auFXw1arLQZD4Erwe5tjaBB26-OM7t87lTOcnBubWAfbCExydo6PQeOLHbYF7RbLkhvbwSbbRPCcwZrx48AKYzXdFZXSC9qh7e2a1tOmFrKQSRq8AFZvHgaQ48ldK9uaYSbdd--A3aYp-EM1k-fpfeGe9sMkJfNcwU6jDGuOryvY-R_3q6rvP_FzAv0ybZqCjm1LYEmHhpyrMreOd72psHMtVgyGxrCZBwf1gybWy2sEeVPcY9MGvxhoFIpkBvy84VvMlLnT0TemzarddO8NNCQk4zuen3dLkT9NQ74kbTNiUCeY0NTPRCypdfOw5V47EVqUNYL2r8e3JcXp4_YfHPf2_B5k2syPZmc7ryjgkqqzs8H-3Eq4Vo.');

--
-- Table structure for table `z_themes`
--

DROP TABLE IF EXISTS `z_themes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_themes` (
  `pn_id` int(11) NOT NULL auto_increment,
  `pn_name` varchar(64) NOT NULL default '',
  `pn_type` smallint(6) NOT NULL default '0',
  `pn_displayname` varchar(64) NOT NULL default '',
  `pn_description` varchar(255) NOT NULL default '',
  `pn_regid` int(11) NOT NULL default '0',
  `pn_directory` varchar(64) NOT NULL default '',
  `pn_version` varchar(10) NOT NULL default '0',
  `pn_official` smallint(6) NOT NULL default '0',
  `pn_author` varchar(255) NOT NULL default '',
  `pn_contact` varchar(255) NOT NULL default '',
  `pn_admin` smallint(6) NOT NULL default '0',
  `pn_user` smallint(6) NOT NULL default '0',
  `pn_system` smallint(6) NOT NULL default '0',
  `pn_state` smallint(6) NOT NULL default '0',
  `pn_credits` varchar(255) NOT NULL default '',
  `pn_changelog` varchar(255) NOT NULL default '',
  `pn_help` varchar(255) NOT NULL default '',
  `pn_license` varchar(255) NOT NULL default '',
  `pn_xhtml` smallint(6) NOT NULL default '1',
  PRIMARY KEY  (`pn_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_themes`
--

INSERT INTO `z_themes` (`pn_id`, `pn_name`, `pn_type`, `pn_displayname`, `pn_description`, `pn_regid`, `pn_directory`, `pn_version`, `pn_official`, `pn_author`, `pn_contact`, `pn_admin`, `pn_user`, `pn_system`, `pn_state`, `pn_credits`, `pn_changelog`, `pn_help`, `pn_license`, `pn_xhtml`) VALUES (1,'andreas08',3,'Andreas08','The Andreas08 theme is a very good template for light CSS-compatible browser-oriented themes.',0,'andreas08','1.1',0,'David Brucas, Mark West, Andreas Viklund','http://dbrucas.povpromotions.com, http://www.markwest.me.uk, http://www.andreasviklund.com',1,1,0,1,'','','','',1),(2,'Atom',3,'Atom','The Atom theme is an auxiliary theme specially designed for rendering pages in Atom mark-up.',0,'Atom','1.0',0,'Franz Skaaning','http://www.lexebus.net/',0,0,1,1,'','','','',0),(3,'Printer',3,'Printer','The Printer theme is an auxiliary theme designed specially for outputting pages in a printer-friendly format.',0,'Printer','2.0',0,'Mark West','http://www.markwest.me.uk',0,0,1,1,'','','','',1),(4,'RSS',3,'RSS','The RSS theme is an auxiliary theme designed specially for outputting pages as an RSS feed.',0,'rss','1.0',0,'Mark West','http://www.markwest.me.uk',0,0,1,1,'docs/credits.txt','docs/changelog.txt','docs/help.txt','docs/license.txt',0),(5,'SeaBreeze',3,'SeaBreeze','The SeaBreeze theme is a browser-oriented theme, and was updated for the release of Zikula 1.0, with revised colours and new graphics.',0,'SeaBreeze','3.1',0,'Carsten Volmer, Vanessa Haakenson, Mark West, Martin Andersen','http://www.zikula.org',0,1,0,1,'','','','',1);

--
-- Table structure for table `z_userblocks`
--

DROP TABLE IF EXISTS `z_userblocks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_userblocks` (
  `pn_uid` int(11) NOT NULL default '0',
  `pn_bid` int(11) NOT NULL default '0',
  `pn_active` smallint(6) NOT NULL default '1',
  `pn_last_update` datetime default NULL,
  KEY `bid_uid_idx` (`pn_uid`,`pn_bid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_userblocks`
--


--
-- Table structure for table `z_users`
--

DROP TABLE IF EXISTS `z_users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_users` (
  `pn_uid` bigint(20) NOT NULL auto_increment,
  `pn_uname` varchar(25) NOT NULL default '',
  `pn_email` varchar(60) NOT NULL default '',
  `pn_user_regdate` datetime NOT NULL default '1970-01-01 00:00:00',
  `pn_user_viewemail` int(11) default '0',
  `pn_user_theme` varchar(64) default NULL,
  `pn_pass` varchar(128) NOT NULL default '',
  `pn_storynum` int(11) NOT NULL default '10',
  `pn_ublockon` smallint(6) NOT NULL default '0',
  `pn_ublock` longtext NOT NULL,
  `pn_theme` varchar(255) NOT NULL default '',
  `pn_counter` bigint(20) NOT NULL default '0',
  `pn_activated` smallint(6) NOT NULL default '0',
  `pn_lastlogin` datetime NOT NULL default '1970-01-01 00:00:00',
  `pn_validfrom` bigint(20) NOT NULL default '0',
  `pn_validuntil` bigint(20) NOT NULL default '0',
  `pn_hash_method` smallint(6) NOT NULL default '8',
  PRIMARY KEY  (`pn_uid`),
  KEY `uname` (`pn_uname`),
  KEY `email` (`pn_email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_users`
--

INSERT INTO `z_users` (`pn_uid`, `pn_uname`, `pn_email`, `pn_user_regdate`, `pn_user_viewemail`, `pn_user_theme`, `pn_pass`, `pn_storynum`, `pn_ublockon`, `pn_ublock`, `pn_theme`, `pn_counter`, `pn_activated`, `pn_lastlogin`, `pn_validfrom`, `pn_validuntil`, `pn_hash_method`) VALUES (1,'guest','','1970-01-01 00:00:00',0,NULL,'',10,0,'','',0,1,'1970-01-01 00:00:00',0,0,1),(2,'admin','example@example.com','2010-05-19 20:00:12',0,NULL,'e7cf3ef4f17c3999a94f2c6f612e8a888e5b1026878e4e19398b23bd38ec221a',10,0,'','',0,1,'2010-05-19 20:00:13',0,0,8);

--
-- Table structure for table `z_users_temp`
--

DROP TABLE IF EXISTS `z_users_temp`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_users_temp` (
  `pn_tid` bigint(20) NOT NULL auto_increment,
  `pn_uname` varchar(25) NOT NULL default '',
  `pn_email` varchar(60) NOT NULL default '',
  `pn_femail` smallint(6) NOT NULL default '0',
  `pn_pass` varchar(128) NOT NULL default '',
  `pn_dynamics` longtext NOT NULL,
  `pn_comment` varchar(254) NOT NULL default '',
  `pn_type` smallint(6) NOT NULL default '0',
  `pn_tag` smallint(6) NOT NULL default '0',
  `pn_hash_method` smallint(6) NOT NULL default '8',
  PRIMARY KEY  (`pn_tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_users_temp`
--


--
-- Table structure for table `z_workflows`
--

DROP TABLE IF EXISTS `z_workflows`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_workflows` (
  `id` int(11) NOT NULL auto_increment,
  `metaid` int(11) NOT NULL default '0',
  `module` varchar(255) NOT NULL default '',
  `schemaname` varchar(255) NOT NULL default '',
  `state` varchar(255) NOT NULL default '',
  `type` int(11) NOT NULL default '1',
  `obj_table` varchar(40) NOT NULL default '',
  `obj_idcolumn` varchar(40) NOT NULL default '',
  `obj_id` bigint(20) NOT NULL default '0',
  `busy` int(11) NOT NULL default '0',
  `debug` longblob,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_workflows`
--

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-05-19 20:01:31
