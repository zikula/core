-- MySQL dump 10.11
--
-- Host: 192.168.137.1    Database: zikula130
-- ------------------------------------------------------
-- Server version	5.1.41
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `z_admin_category`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_admin_category` (
  `z_cid` int(11) NOT NULL,
  `z_name` varchar(32) NOT NULL,
  `z_description` varchar(254) NOT NULL,
  `z_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`z_cid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_admin_category`
--

INSERT INTO `z_admin_category` VALUES (1,'System','Core modules at the heart of operation of the site.',0);
INSERT INTO `z_admin_category` VALUES (2,'Layout','Layout modules for controlling the site\'s look and feel.',0);
INSERT INTO `z_admin_category` VALUES (3,'Users','Modules for controlling user membership, access rights and profiles.',0);
INSERT INTO `z_admin_category` VALUES (4,'Content','Modules for providing content to your users.',0);
INSERT INTO `z_admin_category` VALUES (5,'Uncategorised','Newly-installed or uncategorized modules.',0);
INSERT INTO `z_admin_category` VALUES (6,'Security','Modules for managing the site\'s security.',0);

--
-- Table structure for table `z_admin_module`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_admin_module` (
  `z_amid` int(11) NOT NULL,
  `z_mid` int(11) NOT NULL DEFAULT '0',
  `z_cid` int(11) NOT NULL DEFAULT '0',
  `z_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`z_amid`),
  KEY `mid_cid` (`z_mid`,`z_cid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_admin_module`
--

INSERT INTO `z_admin_module` VALUES (1,1,1,0);
INSERT INTO `z_admin_module` VALUES (2,12,1,1);
INSERT INTO `z_admin_module` VALUES (3,14,2,0);
INSERT INTO `z_admin_module` VALUES (4,2,1,2);
INSERT INTO `z_admin_module` VALUES (5,9,3,0);
INSERT INTO `z_admin_module` VALUES (6,6,3,1);
INSERT INTO `z_admin_module` VALUES (7,3,2,1);
INSERT INTO `z_admin_module` VALUES (8,15,3,2);
INSERT INTO `z_admin_module` VALUES (9,11,6,0);
INSERT INTO `z_admin_module` VALUES (10,4,4,0);
INSERT INTO `z_admin_module` VALUES (11,22,4,1);
INSERT INTO `z_admin_module` VALUES (12,7,1,3);
INSERT INTO `z_admin_module` VALUES (13,5,1,4);
INSERT INTO `z_admin_module` VALUES (14,10,4,2);
INSERT INTO `z_admin_module` VALUES (15,13,6,1);

--
-- Table structure for table `z_block_placements`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_block_placements` (
  `z_pid` int(11) NOT NULL DEFAULT '0',
  `z_bid` int(11) NOT NULL DEFAULT '0',
  `z_order` int(11) NOT NULL DEFAULT '0',
  KEY `bid_pid_idx` (`z_bid`,`z_pid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_block_placements`
--

INSERT INTO `z_block_placements` VALUES (1,1,0);
INSERT INTO `z_block_placements` VALUES (4,2,0);
INSERT INTO `z_block_placements` VALUES (3,3,0);
INSERT INTO `z_block_placements` VALUES (2,4,0);
INSERT INTO `z_block_placements` VALUES (2,5,0);
INSERT INTO `z_block_placements` VALUES (7,6,0);

--
-- Table structure for table `z_block_positions`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_block_positions` (
  `z_pid` int(11) NOT NULL,
  `z_name` varchar(255) NOT NULL,
  `z_description` varchar(255) NOT NULL,
  PRIMARY KEY (`z_pid`),
  KEY `name_idx` (`z_name`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_block_positions`
--

INSERT INTO `z_block_positions` VALUES (1,'left','Left blocks');
INSERT INTO `z_block_positions` VALUES (2,'right','Right blocks');
INSERT INTO `z_block_positions` VALUES (3,'center','Center blocks');
INSERT INTO `z_block_positions` VALUES (4,'search','Search block');
INSERT INTO `z_block_positions` VALUES (5,'header','Header block');
INSERT INTO `z_block_positions` VALUES (6,'footer','Footer block');
INSERT INTO `z_block_positions` VALUES (7,'topnav','Top navigation block');
INSERT INTO `z_block_positions` VALUES (8,'bottomnav','Bottom navigation block');

--
-- Table structure for table `z_blocks`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_blocks` (
  `z_bid` int(11) NOT NULL,
  `z_bkey` varchar(255) NOT NULL,
  `z_title` varchar(255) NOT NULL,
  `z_description` longtext NOT NULL,
  `z_content` longtext NOT NULL,
  `z_url` longtext NOT NULL,
  `z_mid` int(11) NOT NULL DEFAULT '0',
  `z_filter` longtext NOT NULL,
  `z_active` tinyint(4) NOT NULL DEFAULT '1',
  `z_collapsable` int(11) NOT NULL DEFAULT '1',
  `z_defaultstate` int(11) NOT NULL DEFAULT '1',
  `z_refresh` int(11) NOT NULL DEFAULT '0',
  `z_last_update` datetime NOT NULL,
  `z_language` varchar(30) NOT NULL,
  PRIMARY KEY (`z_bid`),
  KEY `active_idx` (`z_active`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_blocks`
--

INSERT INTO `z_blocks` VALUES (1,'Extmenu','Main menu','Main menu','a:5:{s:14:\"displaymodules\";s:1:\"0\";s:10:\"stylesheet\";s:11:\"extmenu.css\";s:8:\"template\";s:24:\"blocks_block_extmenu.tpl\";s:11:\"blocktitles\";a:1:{s:2:\"en\";s:9:\"Main menu\";}s:5:\"links\";a:1:{s:2:\"en\";a:5:{i:0;a:7:{s:4:\"name\";s:4:\"Home\";s:3:\"url\";s:10:\"{homepage}\";s:5:\"title\";s:26:\"Go to the site\'s home page\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:1;a:7:{s:4:\"name\";s:14:\"Administration\";s:3:\"url\";s:24:\"{Admin:admin:adminpanel}\";s:5:\"title\";s:29:\"Go to the site administration\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:2;a:7:{s:4:\"name\";s:10:\"My Account\";s:3:\"url\";s:7:\"{Users:user:main}\";s:5:\"title\";s:24:\"Go to your account panel\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:3;a:7:{s:4:\"name\";s:7:\"Log out\";s:3:\"url\";s:14:\"{Users:user:logout}\";s:5:\"title\";s:20:\"Log out of this site\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:4;a:7:{s:4:\"name\";s:11:\"Site search\";s:3:\"url\";s:8:\"{Search:user:form}\";s:5:\"title\";s:16:\"Search this site\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}}}}','',3,'',1,1,1,3600,'2011-01-27 03:46:38','');
INSERT INTO `z_blocks` VALUES (2,'Search','Search box','Search block','a:1:{s:16:\"displaySearchBtn\";i:1;}','',10,'',1,1,1,3600,'2011-01-27 03:46:38','');
INSERT INTO `z_blocks` VALUES (3,'Html','This site is powered by Zikula!','HTML block','<p><a href=\"http://zikula.org\">Zikula</a> is a content management system (CMS) and application framework. It is secure and stable, and is a good choice for sites with a large volume of traffic.</p><p>With Zikula:</p><ul><li>you can customise all aspects of the site\'s appearance through themes, with support for CSS style sheets, JavaScript, Flash and all other modern web development technologies;</li><li>you can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation and internationalisation of your site and pages;</li><li>you can be sure that your pages will display properly in all browsers, thanks to Zikula\'s full compliance with W3C HTML standards;</li><li>you get a standard application-programming interface (API) that lets you easily augment your site\'s functionality through modules, blocks and other extensions;</li><li>you can get help and support from the Zikula community of webmasters and developers at <a href=\"http://www.zikula.org\">zikula.org</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>','',3,'',1,1,1,3600,'2011-01-27 03:46:38','');
INSERT INTO `z_blocks` VALUES (4,'Login','User log-in','Login block','','',15,'',1,1,1,3600,'2011-01-27 03:46:38','');
INSERT INTO `z_blocks` VALUES (5,'Online','Who\'s on-line','Online block','','',15,'',1,1,1,3600,'2011-01-27 03:46:38','');
INSERT INTO `z_blocks` VALUES (6,'Extmenu','Top navigation','Theme navigation','a:5:{s:14:\"displaymodules\";s:1:\"0\";s:10:\"stylesheet\";s:11:\"extmenu.css\";s:8:\"template\";s:31:\"blocks_block_extmenu_topnav.tpl\";s:11:\"blocktitles\";a:1:{s:2:\"en\";s:14:\"Top navigation\";}s:5:\"links\";a:1:{s:2:\"en\";a:3:{i:0;a:7:{s:4:\"name\";s:4:\"Home\";s:3:\"url\";s:10:\"{homepage}\";s:5:\"title\";s:26:\"Go to the site\'s home page\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:1;a:7:{s:4:\"name\";s:10:\"My Account\";s:3:\"url\";s:7:\"{Users}\";s:5:\"title\";s:24:\"Go to your account panel\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:2;a:7:{s:4:\"name\";s:11:\"Site search\";s:3:\"url\";s:8:\"{Search}\";s:5:\"title\";s:16:\"Search this site\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}}}}','',3,'',1,1,1,3600,'2011-01-27 03:46:38','');

--
-- Table structure for table `z_categories_category`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_categories_category` (
  `cat_id` int(11) NOT NULL,
  `cat_parent_id` int(11) NOT NULL DEFAULT '1',
  `cat_is_locked` tinyint(4) NOT NULL DEFAULT '0',
  `cat_is_leaf` tinyint(4) NOT NULL DEFAULT '0',
  `cat_name` varchar(255) NOT NULL,
  `cat_value` varchar(255) NOT NULL,
  `cat_sort_value` int(11) NOT NULL DEFAULT '0',
  `cat_display_name` longtext NOT NULL,
  `cat_display_desc` longtext NOT NULL,
  `cat_path` longtext NOT NULL,
  `cat_ipath` varchar(255) NOT NULL,
  `cat_status` varchar(1) NOT NULL DEFAULT 'A',
  `cat_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cat_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cat_cr_uid` int(11) NOT NULL DEFAULT '0',
  `cat_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cat_lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  KEY `idx_categories_parent` (`cat_parent_id`),
  KEY `idx_categories_is_leaf` (`cat_is_leaf`),
  KEY `idx_categories_name` (`cat_name`),
  KEY `idx_categories_ipath` (`cat_ipath`,`cat_is_leaf`,`cat_status`),
  KEY `idx_categories_status` (`cat_status`),
  KEY `idx_categories_ipath_status` (`cat_ipath`,`cat_status`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_categories_category`
--

INSERT INTO `z_categories_category` VALUES (1,0,1,0,'__SYSTEM__','',0,'b:0;','b:0;','/__SYSTEM__','/1','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (2,1,0,0,'Modules','',0,'a:1:{s:2:\"en\";s:7:\"Modules\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules','/1/2','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (3,1,0,0,'General','',0,'a:1:{s:2:\"en\";s:7:\"General\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General','/1/3','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (4,3,0,0,'YesNo','',0,'a:1:{s:2:\"en\";s:6:\"Yes/No\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/YesNo','/1/3/4','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (5,4,0,1,'1 - Yes','',0,'b:0;','b:0;','/__SYSTEM__/General/YesNo/1 - Yes','/1/3/4/5','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (6,4,0,1,'2 - No','',0,'b:0;','b:0;','/__SYSTEM__/General/YesNo/2 - No','/1/3/4/6','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (10,3,0,0,'Publication Status (extended)','',0,'a:1:{s:2:\"en\";s:29:\"Publication status (extended)\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended','/1/3/10','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (11,10,0,1,'Pending','',0,'a:1:{s:2:\"en\";s:7:\"Pending\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Pending','/1/3/10/11','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (12,10,0,1,'Checked','',0,'a:1:{s:2:\"en\";s:7:\"Checked\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Checked','/1/3/10/12','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (13,10,0,1,'Approved','',0,'a:1:{s:2:\"en\";s:8:\"Approved\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Approved','/1/3/10/13','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (14,10,0,1,'On-line','',0,'a:1:{s:2:\"en\";s:7:\"On-line\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Online','/1/3/10/14','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (15,10,0,1,'Rejected','',0,'a:1:{s:2:\"en\";s:8:\"Rejected\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Rejected','/1/3/10/15','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (16,3,0,0,'Gender','',0,'a:1:{s:2:\"en\";s:6:\"Gender\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Gender','/1/3/16','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (17,16,0,1,'Male','',0,'a:1:{s:2:\"en\";s:4:\"Male\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Gender/Male','/1/3/16/17','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (18,16,0,1,'Female','',0,'a:1:{s:2:\"en\";s:6:\"Female\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Gender/Female','/1/3/16/18','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (19,3,0,0,'Title','',0,'a:1:{s:2:\"en\";s:5:\"Title\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title','/1/3/19','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (20,19,0,1,'Mr','',0,'a:1:{s:2:\"en\";s:3:\"Mr.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Mr','/1/3/19/20','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (21,19,0,1,'Mrs','',0,'a:1:{s:2:\"en\";s:4:\"Mrs.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Mrs','/1/3/19/21','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (22,19,0,1,'Ms','',0,'a:1:{s:2:\"en\";s:3:\"Ms.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Ms','/1/3/19/22','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (23,19,0,1,'Miss','',0,'a:1:{s:2:\"en\";s:4:\"Miss\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Miss','/1/3/19/23','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (24,19,0,1,'Dr','',0,'a:1:{s:2:\"en\";s:3:\"Dr.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Dr','/1/3/19/24','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (25,3,0,0,'ActiveStatus','',0,'a:1:{s:2:\"en\";s:15:\"Activity status\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus','/1/3/25','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (26,25,0,1,'Active','',0,'a:1:{s:2:\"en\";s:6:\"Active\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus/Active','/1/3/25/26','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (27,25,0,1,'Inactive','',0,'a:1:{s:2:\"en\";s:8:\"Inactive\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus/Inactive','/1/3/25/27','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (28,3,0,0,'Publication status (basic)','',0,'a:1:{s:2:\"en\";s:26:\"Publication status (basic)\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic','/1/3/28','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (29,28,0,1,'Pending','',0,'a:1:{s:2:\"en\";s:7:\"Pending\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic/Pending','/1/3/28/29','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (30,28,0,1,'Approved','',0,'a:1:{s:2:\"en\";s:8:\"Approved\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic/Approved','/1/3/28/30','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (31,1,0,0,'Users','',0,'a:1:{s:2:\"en\";s:5:\"Users\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Users','/1/31','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (32,2,0,0,'Global','',0,'a:1:{s:2:\"en\";s:6:\"Global\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global','/1/2/32','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (33,32,0,1,'Blogging','',0,'a:1:{s:2:\"en\";s:8:\"Blogging\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/Blogging','/1/2/32/33','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (34,32,0,1,'Music and audio','',0,'a:1:{s:2:\"en\";s:15:\"Music and audio\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/MusicAndAudio','/1/2/32/34','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (35,32,0,1,'Art and photography','',0,'a:1:{s:2:\"en\";s:19:\"Art and photography\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ArtAndPhotography','/1/2/32/35','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (36,32,0,1,'Writing and thinking','',0,'a:1:{s:2:\"en\";s:20:\"Writing and thinking\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/WritingAndThinking','/1/2/32/36','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (37,32,0,1,'Communications and media','',0,'a:1:{s:2:\"en\";s:24:\"Communications and media\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/CommunicationsAndMedia','/1/2/32/37','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (38,32,0,1,'Travel and culture','',0,'a:1:{s:2:\"en\";s:18:\"Travel and culture\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/TravelAndCulture','/1/2/32/38','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (39,32,0,1,'Science and technology','',0,'a:1:{s:2:\"en\";s:22:\"Science and technology\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ScienceAndTechnology','/1/2/32/39','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (40,32,0,1,'Sport and activities','',0,'a:1:{s:2:\"en\";s:20:\"Sport and activities\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/SportAndActivities','/1/2/32/40','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_categories_category` VALUES (41,32,0,1,'Business and work','',0,'a:1:{s:2:\"en\";s:17:\"Business and work\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/BusinessAndWork','/1/2/32/41','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);

--
-- Table structure for table `z_categories_mapmeta`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_categories_mapmeta` (
  `cmm_id` int(11) NOT NULL,
  `cmm_meta_id` int(11) NOT NULL DEFAULT '0',
  `cmm_category_id` int(11) NOT NULL DEFAULT '0',
  `cmm_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cmm_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cmm_cr_uid` int(11) NOT NULL DEFAULT '0',
  `cmm_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cmm_lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmm_id`),
  KEY `idx_categories_mapmeta` (`cmm_meta_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_categories_mapmeta`
--


--
-- Table structure for table `z_categories_mapobj`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_categories_mapobj` (
  `cmo_id` int(11) NOT NULL,
  `cmo_modname` varchar(60) NOT NULL,
  `cmo_table` varchar(60) NOT NULL,
  `cmo_obj_id` int(11) NOT NULL DEFAULT '0',
  `cmo_obj_idcolumn` varchar(60) NOT NULL DEFAULT 'id',
  `cmo_reg_id` int(11) NOT NULL DEFAULT '0',
  `cmo_category_id` int(11) NOT NULL DEFAULT '0',
  `cmo_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cmo_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cmo_cr_uid` int(11) NOT NULL DEFAULT '0',
  `cmo_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cmo_lu_uid` int(11) NOT NULL DEFAULT '0',
  `cmo_reg_property` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`cmo_id`),
  KEY `idx_categories_mapobj` (`cmo_modname`,`cmo_table`,`cmo_obj_id`,`cmo_obj_idcolumn`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_categories_mapobj`
--


--
-- Table structure for table `z_categories_registry`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_categories_registry` (
  `crg_id` int(11) NOT NULL,
  `crg_modname` varchar(60) NOT NULL,
  `crg_table` varchar(60) NOT NULL,
  `crg_property` varchar(60) NOT NULL,
  `crg_category_id` int(11) NOT NULL DEFAULT '0',
  `crg_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `crg_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `crg_cr_uid` int(11) NOT NULL DEFAULT '0',
  `crg_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `crg_lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`crg_id`),
  KEY `idx_categories_registry` (`crg_modname`,`crg_table`,`crg_property`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_categories_registry`
--


--
-- Table structure for table `z_group_applications`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_group_applications` (
  `z_app_id` int(11) NOT NULL,
  `z_uid` int(11) NOT NULL DEFAULT '0',
  `z_gid` int(11) NOT NULL DEFAULT '0',
  `z_application` longblob NOT NULL,
  `z_status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`z_app_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_group_applications`
--


--
-- Table structure for table `z_group_membership`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_group_membership` (
  `z_gid` int(11) NOT NULL DEFAULT '0',
  `z_uid` int(11) NOT NULL DEFAULT '0',
  KEY `gid_uid` (`z_uid`,`z_gid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_group_membership`
--

INSERT INTO `z_group_membership` VALUES (1,1);
INSERT INTO `z_group_membership` VALUES (1,2);
INSERT INTO `z_group_membership` VALUES (2,2);

--
-- Table structure for table `z_group_perms`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_group_perms` (
  `z_pid` int(11) NOT NULL,
  `z_gid` int(11) NOT NULL DEFAULT '0',
  `z_sequence` int(11) NOT NULL DEFAULT '0',
  `z_realm` int(11) NOT NULL DEFAULT '0',
  `z_component` varchar(255) NOT NULL,
  `z_instance` varchar(255) NOT NULL,
  `z_level` int(11) NOT NULL DEFAULT '0',
  `z_bond` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`z_pid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_group_perms`
--

INSERT INTO `z_group_perms` VALUES (1,2,1,0,'.*','.*',800,0);
INSERT INTO `z_group_perms` VALUES (2,-1,2,0,'ExtendedMenublock::','1:1:',0,0);
INSERT INTO `z_group_perms` VALUES (3,1,3,0,'.*','.*',300,0);
INSERT INTO `z_group_perms` VALUES (4,0,4,0,'ExtendedMenublock::','1:(1|2|3):',0,0);
INSERT INTO `z_group_perms` VALUES (5,0,5,0,'.*','.*',200,0);

--
-- Table structure for table `z_groups`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_groups` (
  `z_gid` int(11) NOT NULL,
  `z_name` varchar(255) NOT NULL,
  `z_gtype` tinyint(4) NOT NULL DEFAULT '0',
  `z_description` varchar(200) NOT NULL,
  `z_prefix` varchar(25) NOT NULL,
  `z_state` tinyint(4) NOT NULL DEFAULT '0',
  `z_nbuser` int(11) NOT NULL DEFAULT '0',
  `z_nbumax` int(11) NOT NULL DEFAULT '0',
  `z_link` int(11) NOT NULL DEFAULT '0',
  `z_uidmaster` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`z_gid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_groups`
--

INSERT INTO `z_groups` VALUES (1,'Users',0,'By default, all users are made members of this group.','usr',0,0,0,0,0);
INSERT INTO `z_groups` VALUES (2,'Administrators',0,'By default, all administrators are made members of this group.','adm',0,0,0,0,0);

--
-- Table structure for table `z_hook_bindings`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_hook_bindings` (
  `id` int(11) NOT NULL,
  `subowner` varchar(40) NOT NULL,
  `providerowner` varchar(40) NOT NULL,
  `subarea` varchar(60) NOT NULL,
  `providerarea` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_hook_bindings`
--


--
-- Table structure for table `z_hook_providers`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_hook_providers` (
  `id` int(11) NOT NULL,
  `owner` varchar(40) NOT NULL,
  `area` varchar(60) NOT NULL,
  `name` text NOT NULL,
  `type` varchar(20) NOT NULL,
  `classname` varchar(60) NOT NULL,
  `method` varchar(20) NOT NULL,
  `serviceid` varchar(60) DEFAULT NULL,
  `weight` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `myindex` (`name`(60))
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_hook_providers`
--


--
-- Table structure for table `z_hook_subscribers`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_hook_subscribers` (
  `id` int(11) NOT NULL,
  `owner` varchar(40) NOT NULL,
  `area` varchar(60) NOT NULL,
  `type` varchar(20) NOT NULL,
  `eventname` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `myindex` (`eventname`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_hook_subscribers`
--

INSERT INTO `z_hook_subscribers` VALUES (1,'Users','modulehook_area.users.user','ui.edit','users.hook.user.ui.edit');
INSERT INTO `z_hook_subscribers` VALUES (2,'Users','modulehook_area.users.user','ui.delete','users.hook.user.ui.delete');
INSERT INTO `z_hook_subscribers` VALUES (3,'Users','modulehook_area.users.user','validate.edit','users.hook.user.validate.edit');
INSERT INTO `z_hook_subscribers` VALUES (4,'Users','modulehook_area.users.user','validate.delete','users.hook.user.validate.delete');
INSERT INTO `z_hook_subscribers` VALUES (5,'Users','modulehook_area.users.user','process.edit','users.hook.user.process.edit');
INSERT INTO `z_hook_subscribers` VALUES (6,'Users','modulehook_area.users.user','process.delete','users.hook.user.process.delete');

--
-- Table structure for table `z_hooks`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_hooks` (
  `z_id` int(11) NOT NULL,
  `z_object` varchar(64) NOT NULL,
  `z_action` varchar(64) NOT NULL,
  `z_smodule` varchar(64) NOT NULL,
  `z_stype` varchar(64) NOT NULL,
  `z_tarea` varchar(64) NOT NULL,
  `z_tmodule` varchar(64) NOT NULL,
  `z_ttype` varchar(64) NOT NULL,
  `z_tfunc` varchar(64) NOT NULL,
  `z_sequence` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`z_id`),
  KEY `smodule` (`z_smodule`),
  KEY `smodule_tmodule` (`z_smodule`,`z_tmodule`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_hooks`
--


--
-- Table structure for table `z_module_deps`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_module_deps` (
  `z_id` int(11) NOT NULL,
  `z_modid` int(11) NOT NULL DEFAULT '0',
  `z_modname` varchar(64) NOT NULL,
  `z_minversion` varchar(10) NOT NULL,
  `z_maxversion` varchar(10) NOT NULL,
  `z_status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`z_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_module_deps`
--

INSERT INTO `z_module_deps` VALUES (1,16,'Scribite','4.2.1','',2);
INSERT INTO `z_module_deps` VALUES (2,16,'Thumbnail','1.1','',2);
INSERT INTO `z_module_deps` VALUES (3,18,'EZComments','1.6','',1);
INSERT INTO `z_module_deps` VALUES (4,18,'Mailer','1.1','',1);
INSERT INTO `z_module_deps` VALUES (5,18,'MediaAttach','1.0','',1);
INSERT INTO `z_module_deps` VALUES (6,18,'Ratings','2.0','',1);
INSERT INTO `z_module_deps` VALUES (7,18,'pn_bbsmile','2.0','',2);
INSERT INTO `z_module_deps` VALUES (8,19,'Akismet','1.0','',2);

--
-- Table structure for table `z_module_vars`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_module_vars` (
  `z_id` int(11) NOT NULL,
  `z_modname` varchar(64) NOT NULL,
  `z_name` varchar(64) NOT NULL,
  `z_value` longtext,
  PRIMARY KEY (`z_id`),
  KEY `mod_var` (`z_modname`,`z_name`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_module_vars`
--

INSERT INTO `z_module_vars` VALUES (1,'/EventHandlers','Extensions','a:2:{i:0;a:3:{s:9:\"eventname\";s:27:\"controller.method_not_found\";s:8:\"callable\";a:2:{i:0;s:17:\"Extensions_HookUI\";i:1;s:5:\"hooks\";}s:6:\"weight\";i:10;}i:1;a:3:{s:9:\"eventname\";s:27:\"controller.method_not_found\";s:8:\"callable\";a:2:{i:0;s:17:\"Extensions_HookUI\";i:1;s:14:\"moduleservices\";}s:6:\"weight\";i:10;}}');
INSERT INTO `z_module_vars` VALUES (2,'Extensions','itemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (3,'ZConfig','debug','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (4,'ZConfig','sitename','s:9:\"Site name\";');
INSERT INTO `z_module_vars` VALUES (5,'ZConfig','slogan','s:16:\"Site description\";');
INSERT INTO `z_module_vars` VALUES (6,'ZConfig','metakeywords','s:237:\"zikula, portal, portal web, open source, web site, website, weblog, blog, content management, content management system, web content management, web content management system, enterprise web content management, cms, application framework\";');
INSERT INTO `z_module_vars` VALUES (7,'ZConfig','defaultpagetitle','s:9:\"Site name\";');
INSERT INTO `z_module_vars` VALUES (8,'ZConfig','defaultmetadescription','s:16:\"Site description\";');
INSERT INTO `z_module_vars` VALUES (9,'ZConfig','startdate','s:7:\"01/2011\";');
INSERT INTO `z_module_vars` VALUES (10,'ZConfig','adminmail','s:15:\"drak@zikula.org\";');
INSERT INTO `z_module_vars` VALUES (11,'ZConfig','Default_Theme','s:9:\"Andreas08\";');
INSERT INTO `z_module_vars` VALUES (12,'ZConfig','anonymous','s:5:\"Guest\";');
INSERT INTO `z_module_vars` VALUES (13,'ZConfig','timezone_offset','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (14,'ZConfig','timezone_server','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (15,'ZConfig','funtext','s:1:\"1\";');
INSERT INTO `z_module_vars` VALUES (16,'ZConfig','reportlevel','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (17,'ZConfig','startpage','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (18,'ZConfig','Version_Num','s:9:\"1.3.0-dev\";');
INSERT INTO `z_module_vars` VALUES (19,'ZConfig','Version_ID','s:6:\"Zikula\";');
INSERT INTO `z_module_vars` VALUES (20,'ZConfig','Version_Sub','s:3:\"vai\";');
INSERT INTO `z_module_vars` VALUES (21,'ZConfig','debug_sql','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (22,'ZConfig','multilingual','s:1:\"1\";');
INSERT INTO `z_module_vars` VALUES (23,'ZConfig','useflags','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (24,'ZConfig','theme_change','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (25,'ZConfig','UseCompression','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (26,'ZConfig','siteoff','i:0;');
INSERT INTO `z_module_vars` VALUES (27,'ZConfig','siteoffreason','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (28,'ZConfig','starttype','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (29,'ZConfig','startfunc','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (30,'ZConfig','startargs','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (31,'ZConfig','entrypoint','s:9:\"index.php\";');
INSERT INTO `z_module_vars` VALUES (32,'ZConfig','language_detect','i:0;');
INSERT INTO `z_module_vars` VALUES (33,'ZConfig','shorturls','b:0;');
INSERT INTO `z_module_vars` VALUES (34,'ZConfig','shorturlstype','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (35,'ZConfig','shorturlsseparator','s:1:\"-\";');
INSERT INTO `z_module_vars` VALUES (36,'ZConfig','shorturlsstripentrypoint','b:0;');
INSERT INTO `z_module_vars` VALUES (37,'ZConfig','shorturlsdefaultmodule','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (38,'ZConfig','profilemodule','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (39,'ZConfig','messagemodule','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (40,'ZConfig','languageurl','i:0;');
INSERT INTO `z_module_vars` VALUES (41,'ZConfig','ajaxtimeout','i:5000;');
INSERT INTO `z_module_vars` VALUES (42,'ZConfig','permasearch','s:161:\"À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü\";');
INSERT INTO `z_module_vars` VALUES (43,'ZConfig','permareplace','s:107:\"A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,s,a,A,o,O,u,U\";');
INSERT INTO `z_module_vars` VALUES (44,'ZConfig','language','s:3:\"eng\";');
INSERT INTO `z_module_vars` VALUES (45,'ZConfig','locale','s:2:\"en\";');
INSERT INTO `z_module_vars` VALUES (46,'ZConfig','language_i18n','s:2:\"en\";');
INSERT INTO `z_module_vars` VALUES (47,'ZConfig','idnnames','i:1;');
INSERT INTO `z_module_vars` VALUES (48,'Theme','modulesnocache','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (49,'Theme','enablecache','b:0;');
INSERT INTO `z_module_vars` VALUES (50,'Theme','compile_check','b:1;');
INSERT INTO `z_module_vars` VALUES (51,'Theme','cache_lifetime','i:3600;');
INSERT INTO `z_module_vars` VALUES (52,'Theme','force_compile','b:0;');
INSERT INTO `z_module_vars` VALUES (53,'Theme','trimwhitespace','b:0;');
INSERT INTO `z_module_vars` VALUES (54,'Theme','maxsizeforlinks','i:30;');
INSERT INTO `z_module_vars` VALUES (55,'Theme','itemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (56,'Theme','cssjscombine','b:0;');
INSERT INTO `z_module_vars` VALUES (57,'Theme','cssjscompress','b:0;');
INSERT INTO `z_module_vars` VALUES (58,'Theme','cssjsminify','b:0;');
INSERT INTO `z_module_vars` VALUES (59,'Theme','cssjscombine_lifetime','i:3600;');
INSERT INTO `z_module_vars` VALUES (60,'Theme','render_compile_check','b:1;');
INSERT INTO `z_module_vars` VALUES (61,'Theme','render_force_compile','b:1;');
INSERT INTO `z_module_vars` VALUES (62,'Theme','render_cache','b:0;');
INSERT INTO `z_module_vars` VALUES (63,'Theme','render_expose_template','b:0;');
INSERT INTO `z_module_vars` VALUES (64,'Theme','render_lifetime','i:3600;');
INSERT INTO `z_module_vars` VALUES (65,'Admin','modulesperrow','i:3;');
INSERT INTO `z_module_vars` VALUES (66,'Admin','itemsperpage','i:15;');
INSERT INTO `z_module_vars` VALUES (67,'Admin','defaultcategory','i:5;');
INSERT INTO `z_module_vars` VALUES (68,'Admin','modulestylesheet','s:11:\"navtabs.css\";');
INSERT INTO `z_module_vars` VALUES (69,'Admin','admingraphic','i:1;');
INSERT INTO `z_module_vars` VALUES (70,'Admin','startcategory','i:1;');
INSERT INTO `z_module_vars` VALUES (71,'Admin','ignoreinstallercheck','i:0;');
INSERT INTO `z_module_vars` VALUES (72,'Admin','admintheme','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (73,'Admin','displaynametype','i:1;');
INSERT INTO `z_module_vars` VALUES (74,'Permissions','filter','i:1;');
INSERT INTO `z_module_vars` VALUES (75,'Permissions','warnbar','i:1;');
INSERT INTO `z_module_vars` VALUES (76,'Permissions','rowview','i:20;');
INSERT INTO `z_module_vars` VALUES (77,'Permissions','rowedit','i:20;');
INSERT INTO `z_module_vars` VALUES (78,'Permissions','lockadmin','i:1;');
INSERT INTO `z_module_vars` VALUES (79,'Permissions','adminid','i:1;');
INSERT INTO `z_module_vars` VALUES (80,'Groups','itemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (81,'Groups','defaultgroup','i:1;');
INSERT INTO `z_module_vars` VALUES (82,'Groups','mailwarning','i:0;');
INSERT INTO `z_module_vars` VALUES (83,'Groups','hideclosed','i:0;');
INSERT INTO `z_module_vars` VALUES (84,'Groups','primaryadmingroup','i:2;');
INSERT INTO `z_module_vars` VALUES (85,'Blocks','collapseable','i:0;');
INSERT INTO `z_module_vars` VALUES (86,'Users','itemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (87,'Users','accountdisplaygraphics','i:1;');
INSERT INTO `z_module_vars` VALUES (88,'Users','accountitemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (89,'Users','accountitemsperrow','i:5;');
INSERT INTO `z_module_vars` VALUES (90,'Users','changepassword','i:1;');
INSERT INTO `z_module_vars` VALUES (91,'Users','changeemail','i:1;');
INSERT INTO `z_module_vars` VALUES (92,'Users','reg_allowreg','i:1;');
INSERT INTO `z_module_vars` VALUES (93,'Users','reg_verifyemail','i:2;');
INSERT INTO `z_module_vars` VALUES (94,'Users','reg_Illegalusername','s:87:\"root adm linux webmaster admin god administrator administrador nobody anonymous anonimo\";');
INSERT INTO `z_module_vars` VALUES (95,'Users','reg_Illegaldomains','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (96,'Users','reg_Illegaluseragents','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (97,'Users','reg_noregreasons','s:51:\"Sorry! New user registration is currently disabled.\";');
INSERT INTO `z_module_vars` VALUES (98,'Users','reg_uniemail','i:1;');
INSERT INTO `z_module_vars` VALUES (99,'Users','reg_notifyemail','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (100,'Users','reg_optitems','i:0;');
INSERT INTO `z_module_vars` VALUES (101,'Users','userimg','s:11:\"images/menu\";');
INSERT INTO `z_module_vars` VALUES (102,'Users','avatarpath','s:13:\"images/avatar\";');
INSERT INTO `z_module_vars` VALUES (103,'Users','allowgravatars','i:1;');
INSERT INTO `z_module_vars` VALUES (104,'Users','gravatarimage','s:12:\"gravatar.gif\";');
INSERT INTO `z_module_vars` VALUES (105,'Users','minage','i:13;');
INSERT INTO `z_module_vars` VALUES (106,'Users','minpass','i:5;');
INSERT INTO `z_module_vars` VALUES (107,'Users','anonymous','s:5:\"Guest\";');
INSERT INTO `z_module_vars` VALUES (108,'Users','loginviaoption','i:0;');
INSERT INTO `z_module_vars` VALUES (109,'Users','moderation','i:0;');
INSERT INTO `z_module_vars` VALUES (110,'Users','hash_method','s:6:\"sha256\";');
INSERT INTO `z_module_vars` VALUES (111,'Users','login_redirect','i:1;');
INSERT INTO `z_module_vars` VALUES (112,'Users','reg_question','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (113,'Users','reg_answer','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (114,'Users','use_password_strength_meter','i:0;');
INSERT INTO `z_module_vars` VALUES (115,'Users','default_authmodule','s:5:\"Users\";');
INSERT INTO `z_module_vars` VALUES (116,'Users','moderation_order','i:0;');
INSERT INTO `z_module_vars` VALUES (117,'Users','login_displaymarkeddel','b:0;');
INSERT INTO `z_module_vars` VALUES (118,'Users','login_displayinactive','b:0;');
INSERT INTO `z_module_vars` VALUES (119,'Users','login_displayverify','b:0;');
INSERT INTO `z_module_vars` VALUES (120,'Users','login_displayapproval','b:0;');
INSERT INTO `z_module_vars` VALUES (121,'Users','chgemail_expiredays','i:0;');
INSERT INTO `z_module_vars` VALUES (122,'Users','chgpass_expiredays','i:0;');
INSERT INTO `z_module_vars` VALUES (123,'Users','reg_expiredays','i:0;');
INSERT INTO `z_module_vars` VALUES (124,'/EventHandlers','Users','a:1:{i:0;a:3:{s:9:\"eventname\";s:19:\"get.pending_content\";s:8:\"callable\";a:2:{i:0;s:15:\"Users_Listeners\";i:1;s:22:\"pendingContentListener\";}s:6:\"weight\";i:10;}}');
INSERT INTO `z_module_vars` VALUES (125,'SecurityCenter','itemsperpage','i:10;');
INSERT INTO `z_module_vars` VALUES (126,'ZConfig','updatecheck','i:1;');
INSERT INTO `z_module_vars` VALUES (127,'ZConfig','updatefrequency','i:7;');
INSERT INTO `z_module_vars` VALUES (128,'ZConfig','updatelastchecked','i:1296100004;');
INSERT INTO `z_module_vars` VALUES (129,'ZConfig','updateversion','s:5:\"1.2.5\";');
INSERT INTO `z_module_vars` VALUES (130,'ZConfig','keyexpiry','i:0;');
INSERT INTO `z_module_vars` VALUES (131,'ZConfig','sessionauthkeyua','b:0;');
INSERT INTO `z_module_vars` VALUES (132,'ZConfig','secure_domain','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (133,'ZConfig','signcookies','i:1;');
INSERT INTO `z_module_vars` VALUES (134,'ZConfig','signingkey','s:40:\"ec550728b4458f559478a2d805d6a4c629b2e1c0\";');
INSERT INTO `z_module_vars` VALUES (135,'ZConfig','seclevel','s:6:\"Medium\";');
INSERT INTO `z_module_vars` VALUES (136,'ZConfig','secmeddays','i:7;');
INSERT INTO `z_module_vars` VALUES (137,'ZConfig','secinactivemins','i:20;');
INSERT INTO `z_module_vars` VALUES (138,'ZConfig','sessionstoretofile','i:0;');
INSERT INTO `z_module_vars` VALUES (139,'ZConfig','sessionsavepath','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (140,'ZConfig','gc_probability','i:100;');
INSERT INTO `z_module_vars` VALUES (141,'ZConfig','anonymoussessions','i:1;');
INSERT INTO `z_module_vars` VALUES (142,'ZConfig','sessionrandregenerate','b:1;');
INSERT INTO `z_module_vars` VALUES (143,'ZConfig','sessionregenerate','b:1;');
INSERT INTO `z_module_vars` VALUES (144,'ZConfig','sessionregeneratefreq','i:10;');
INSERT INTO `z_module_vars` VALUES (145,'ZConfig','sessionipcheck','i:0;');
INSERT INTO `z_module_vars` VALUES (146,'ZConfig','sessionname','s:4:\"ZSID\";');
INSERT INTO `z_module_vars` VALUES (147,'ZConfig','filtergetvars','i:1;');
INSERT INTO `z_module_vars` VALUES (148,'ZConfig','filterpostvars','i:1;');
INSERT INTO `z_module_vars` VALUES (149,'ZConfig','filtercookievars','i:1;');
INSERT INTO `z_module_vars` VALUES (150,'ZConfig','outputfilter','i:1;');
INSERT INTO `z_module_vars` VALUES (151,'ZConfig','htmlpurifierlocation','s:46:\"system/SecurityCenter/lib/vendor/htmlpurifier/\";');
INSERT INTO `z_module_vars` VALUES (152,'SecurityCenter','htmlpurifierConfig','s:3805:\"a:10:{s:4:\"Attr\";a:15:{s:14:\"AllowedClasses\";N;s:19:\"AllowedFrameTargets\";a:0:{}s:10:\"AllowedRel\";a:2:{s:8:\"nofollow\";b:1;s:11:\"imageviewer\";b:1;}s:10:\"AllowedRev\";a:0:{}s:13:\"ClassUseCDATA\";N;s:15:\"DefaultImageAlt\";N;s:19:\"DefaultInvalidImage\";s:0:\"\";s:22:\"DefaultInvalidImageAlt\";s:13:\"Invalid image\";s:14:\"DefaultTextDir\";s:3:\"ltr\";s:8:\"EnableID\";b:0;s:16:\"ForbiddenClasses\";a:0:{}s:11:\"IDBlacklist\";a:0:{}s:17:\"IDBlacklistRegexp\";N;s:8:\"IDPrefix\";s:0:\"\";s:13:\"IDPrefixLocal\";s:0:\"\";}s:10:\"AutoFormat\";a:10:{s:13:\"AutoParagraph\";b:0;s:6:\"Custom\";a:0:{}s:14:\"DisplayLinkURI\";b:0;s:7:\"Linkify\";b:0;s:22:\"PurifierLinkify.DocURL\";s:3:\"#%s\";s:15:\"PurifierLinkify\";b:0;s:33:\"RemoveEmpty.RemoveNbsp.Exceptions\";a:2:{s:2:\"td\";b:1;s:2:\"th\";b:1;}s:22:\"RemoveEmpty.RemoveNbsp\";b:0;s:11:\"RemoveEmpty\";b:0;s:28:\"RemoveSpansWithoutAttributes\";b:0;}s:3:\"CSS\";a:7:{s:14:\"AllowImportant\";b:0;s:11:\"AllowTricky\";b:0;s:17:\"AllowedProperties\";N;s:13:\"DefinitionRev\";i:1;s:19:\"ForbiddenProperties\";a:0:{}s:12:\"MaxImgLength\";s:6:\"1200px\";s:11:\"Proprietary\";b:0;}s:5:\"Cache\";a:2:{s:14:\"DefinitionImpl\";s:10:\"Serializer\";s:14:\"SerializerPath\";N;}s:4:\"Core\";a:17:{s:17:\"AggressivelyFixLt\";b:1;s:13:\"CollectErrors\";b:0;s:13:\"ColorKeywords\";a:17:{s:6:\"maroon\";s:7:\"#800000\";s:3:\"red\";s:7:\"#FF0000\";s:6:\"orange\";s:7:\"#FFA500\";s:6:\"yellow\";s:7:\"#FFFF00\";s:5:\"olive\";s:7:\"#808000\";s:6:\"purple\";s:7:\"#800080\";s:7:\"fuchsia\";s:7:\"#FF00FF\";s:5:\"white\";s:7:\"#FFFFFF\";s:4:\"lime\";s:7:\"#00FF00\";s:5:\"green\";s:7:\"#008000\";s:4:\"navy\";s:7:\"#000080\";s:4:\"blue\";s:7:\"#0000FF\";s:4:\"aqua\";s:7:\"#00FFFF\";s:4:\"teal\";s:7:\"#008080\";s:5:\"black\";s:7:\"#000000\";s:6:\"silver\";s:7:\"#C0C0C0\";s:4:\"gray\";s:7:\"#808080\";}s:25:\"ConvertDocumentToFragment\";b:1;s:31:\"DirectLexLineNumberSyncInterval\";i:0;s:8:\"Encoding\";s:5:\"utf-8\";s:21:\"EscapeInvalidChildren\";b:0;s:17:\"EscapeInvalidTags\";b:0;s:24:\"EscapeNonASCIICharacters\";b:0;s:14:\"HiddenElements\";a:2:{s:6:\"script\";b:1;s:5:\"style\";b:1;}s:8:\"Language\";s:2:\"en\";s:9:\"LexerImpl\";N;s:19:\"MaintainLineNumbers\";N;s:17:\"NormalizeNewlines\";b:1;s:16:\"RemoveInvalidImg\";b:1;s:28:\"RemoveProcessingInstructions\";b:0;s:20:\"RemoveScriptContents\";N;}s:6:\"Filter\";a:6:{s:6:\"Custom\";a:0:{}s:27:\"ExtractStyleBlocks.Escaping\";b:1;s:24:\"ExtractStyleBlocks.Scope\";N;s:27:\"ExtractStyleBlocks.TidyImpl\";N;s:18:\"ExtractStyleBlocks\";b:0;s:7:\"YouTube\";b:0;}s:4:\"HTML\";a:25:{s:7:\"Allowed\";N;s:17:\"AllowedAttributes\";N;s:15:\"AllowedElements\";N;s:14:\"AllowedModules\";N;s:18:\"Attr.Name.UseCDATA\";b:0;s:12:\"BlockWrapper\";s:1:\"p\";s:11:\"CoreModules\";a:7:{s:9:\"Structure\";b:1;s:4:\"Text\";b:1;s:9:\"Hypertext\";b:1;s:4:\"List\";b:1;s:22:\"NonXMLCommonAttributes\";b:1;s:19:\"XMLCommonAttributes\";b:1;s:16:\"CommonAttributes\";b:1;}s:13:\"CustomDoctype\";N;s:12:\"DefinitionID\";N;s:13:\"DefinitionRev\";i:1;s:7:\"Doctype\";s:22:\"HTML 4.01 Transitional\";s:20:\"FlashAllowFullScreen\";b:0;s:19:\"ForbiddenAttributes\";a:0:{}s:17:\"ForbiddenElements\";a:0:{}s:12:\"MaxImgLength\";i:1200;s:6:\"Parent\";s:3:\"div\";s:11:\"Proprietary\";b:0;s:9:\"SafeEmbed\";b:1;s:10:\"SafeObject\";b:1;s:6:\"Strict\";b:0;s:7:\"TidyAdd\";a:0:{}s:9:\"TidyLevel\";s:6:\"medium\";s:10:\"TidyRemove\";a:0:{}s:7:\"Trusted\";b:0;s:5:\"XHTML\";b:1;}s:6:\"Output\";a:5:{s:21:\"CommentScriptContents\";b:1;s:11:\"FlashCompat\";b:1;s:7:\"Newline\";N;s:8:\"SortAttr\";b:0;s:10:\"TidyFormat\";b:0;}s:4:\"Test\";a:1:{s:12:\"ForceNoIconv\";b:0;}s:3:\"URI\";a:16:{s:14:\"AllowedSchemes\";a:6:{s:4:\"http\";b:1;s:5:\"https\";b:1;s:6:\"mailto\";b:1;s:3:\"ftp\";b:1;s:4:\"nntp\";b:1;s:4:\"news\";b:1;}s:4:\"Base\";N;s:13:\"DefaultScheme\";s:4:\"http\";s:12:\"DefinitionID\";N;s:13:\"DefinitionRev\";i:1;s:7:\"Disable\";b:0;s:15:\"DisableExternal\";b:0;s:24:\"DisableExternalResources\";b:0;s:16:\"DisableResources\";b:0;s:4:\"Host\";N;s:13:\"HostBlacklist\";a:0:{}s:12:\"MakeAbsolute\";b:0;s:5:\"Munge\";N;s:14:\"MungeResources\";b:0;s:14:\"MungeSecretKey\";N;s:22:\"OverrideAllowedSchemes\";b:1;}}\";');
INSERT INTO `z_module_vars` VALUES (153,'ZConfig','useids','i:0;');
INSERT INTO `z_module_vars` VALUES (154,'ZConfig','idsmail','i:0;');
INSERT INTO `z_module_vars` VALUES (155,'ZConfig','idsrulepath','s:32:\"config/phpids_zikula_default.xml\";');
INSERT INTO `z_module_vars` VALUES (156,'ZConfig','idssoftblock','i:1;');
INSERT INTO `z_module_vars` VALUES (157,'ZConfig','idsfilter','s:3:\"xml\";');
INSERT INTO `z_module_vars` VALUES (158,'ZConfig','idsimpactthresholdone','i:1;');
INSERT INTO `z_module_vars` VALUES (159,'ZConfig','idsimpactthresholdtwo','i:10;');
INSERT INTO `z_module_vars` VALUES (160,'ZConfig','idsimpactthresholdthree','i:25;');
INSERT INTO `z_module_vars` VALUES (161,'ZConfig','idsimpactthresholdfour','i:75;');
INSERT INTO `z_module_vars` VALUES (162,'ZConfig','idsimpactmode','i:1;');
INSERT INTO `z_module_vars` VALUES (163,'ZConfig','idshtmlfields','a:1:{i:0;s:14:\"POST.__wysiwyg\";}');
INSERT INTO `z_module_vars` VALUES (164,'ZConfig','idsjsonfields','a:1:{i:0;s:15:\"POST.__jsondata\";}');
INSERT INTO `z_module_vars` VALUES (165,'ZConfig','idsexceptions','a:12:{i:0;s:10:\"GET.__utmz\";i:1;s:10:\"GET.__utmc\";i:2;s:18:\"REQUEST.linksorder\";i:3;s:15:\"POST.linksorder\";i:4;s:19:\"REQUEST.fullcontent\";i:5;s:16:\"POST.fullcontent\";i:6;s:22:\"REQUEST.summarycontent\";i:7;s:19:\"POST.summarycontent\";i:8;s:19:\"REQUEST.filter.page\";i:9;s:16:\"POST.filter.page\";i:10;s:20:\"REQUEST.filter.value\";i:11;s:17:\"POST.filter.value\";}');
INSERT INTO `z_module_vars` VALUES (166,'ZConfig','summarycontent','s:1130:\"For the attention of %sitename% administration staff:\n\nOn %date% at %time%, Zikula detected that somebody tried to interact with the site in a way that may have been intended compromise its security. This is not necessarily the case: it could have been caused by work you were doing on the site, or may have been due to some other reason. In any case, it was detected and blocked. \n\nThe suspicious activity was recognised in \'%filename%\' at line %linenumber%.\n\nType: %type%. \n\nAdditional information: %additionalinfo%.\n\nBelow is logged information that may help you identify what happened and who was responsible.\n\n=====================================\nInformation about the user:\n=====================================\nUser name:  %username%\nUser\'s e-mail address: %useremail%\nUser\'s real name: %userrealname%\n\n=====================================\nIP numbers (if this was a cracker, the IP numbers may not be the true point of origin)\n=====================================\nIP according to HTTP_CLIENT_IP: %httpclientip%\nIP according to REMOTE_ADDR: %remoteaddr%\nIP according to GetHostByName($REMOTE_ADDR): %gethostbyremoteaddr%\n\";');
INSERT INTO `z_module_vars` VALUES (167,'ZConfig','fullcontent','s:1289:\"=====================================\nInformation in the $_REQUEST array\n=====================================\n%requestarray%\n\n=====================================\nInformation in the $_GET array\n(variables that may have been in the URL string or in a \'GET\'-type form)\n=====================================\n%getarray%\n\n=====================================\nInformation in the $_POST array\n(visible and invisible form elements)\n=====================================\n%postarray%\n\n=====================================\nBrowser information\n=====================================\n%browserinfo%\n\n=====================================\nInformation in the $_SERVER array\n=====================================\n%serverarray%\n\n=====================================\nInformation in the $_ENV array\n=====================================\n%envarray%\n\n=====================================\nInformation in the $_COOKIE array\n=====================================\n%cookiearray%\n\n=====================================\nInformation in the $_FILES array\n=====================================\n%filearray%\n\n=====================================\nInformation in the $_SESSION array\n(session information -- variables starting with PNSV are Zikula session variables)\n=====================================\n%sessionarray%\n\";');
INSERT INTO `z_module_vars` VALUES (168,'ZConfig','usehtaccessbans','i:0;');
INSERT INTO `z_module_vars` VALUES (169,'ZConfig','extrapostprotection','i:0;');
INSERT INTO `z_module_vars` VALUES (170,'ZConfig','extragetprotection','i:0;');
INSERT INTO `z_module_vars` VALUES (171,'ZConfig','checkmultipost','i:0;');
INSERT INTO `z_module_vars` VALUES (172,'ZConfig','maxmultipost','i:4;');
INSERT INTO `z_module_vars` VALUES (173,'ZConfig','cpuloadmonitor','i:0;');
INSERT INTO `z_module_vars` VALUES (174,'ZConfig','cpumaxload','d:10;');
INSERT INTO `z_module_vars` VALUES (175,'ZConfig','ccisessionpath','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (176,'ZConfig','htaccessfilelocation','s:9:\".htaccess\";');
INSERT INTO `z_module_vars` VALUES (177,'ZConfig','nocookiebanthreshold','i:10;');
INSERT INTO `z_module_vars` VALUES (178,'ZConfig','nocookiewarningthreshold','i:2;');
INSERT INTO `z_module_vars` VALUES (179,'ZConfig','fastaccessbanthreshold','i:40;');
INSERT INTO `z_module_vars` VALUES (180,'ZConfig','fastaccesswarnthreshold','i:10;');
INSERT INTO `z_module_vars` VALUES (181,'ZConfig','javababble','i:0;');
INSERT INTO `z_module_vars` VALUES (182,'ZConfig','javaencrypt','i:0;');
INSERT INTO `z_module_vars` VALUES (183,'ZConfig','preservehead','i:0;');
INSERT INTO `z_module_vars` VALUES (184,'ZConfig','filterarrays','i:1;');
INSERT INTO `z_module_vars` VALUES (185,'ZConfig','htmlentities','s:1:\"1\";');
INSERT INTO `z_module_vars` VALUES (186,'ZConfig','AllowableHTML','a:110:{s:3:\"!--\";i:2;s:1:\"a\";i:2;s:4:\"abbr\";i:1;s:7:\"acronym\";i:1;s:7:\"address\";i:1;s:6:\"applet\";i:0;s:4:\"area\";i:0;s:7:\"article\";i:1;s:5:\"aside\";i:1;s:5:\"audio\";i:0;s:1:\"b\";i:1;s:4:\"base\";i:0;s:8:\"basefont\";i:0;s:3:\"bdo\";i:0;s:3:\"big\";i:0;s:10:\"blockquote\";i:2;s:2:\"br\";i:2;s:6:\"button\";i:0;s:6:\"canvas\";i:0;s:7:\"caption\";i:1;s:6:\"center\";i:2;s:4:\"cite\";i:1;s:4:\"code\";i:0;s:3:\"col\";i:1;s:8:\"colgroup\";i:1;s:7:\"command\";i:0;s:8:\"datalist\";i:0;s:2:\"dd\";i:1;s:3:\"del\";i:0;s:7:\"details\";i:1;s:3:\"dfn\";i:0;s:3:\"dir\";i:0;s:3:\"div\";i:2;s:2:\"dl\";i:1;s:2:\"dt\";i:1;s:2:\"em\";i:2;s:5:\"embed\";i:0;s:8:\"fieldset\";i:1;s:10:\"figcaption\";i:0;s:6:\"figure\";i:0;s:6:\"footer\";i:0;s:4:\"font\";i:0;s:4:\"form\";i:0;s:2:\"h1\";i:1;s:2:\"h2\";i:1;s:2:\"h3\";i:1;s:2:\"h4\";i:1;s:2:\"h5\";i:1;s:2:\"h6\";i:1;s:6:\"header\";i:0;s:6:\"hgroup\";i:0;s:2:\"hr\";i:2;s:1:\"i\";i:1;s:6:\"iframe\";i:0;s:3:\"img\";i:2;s:5:\"input\";i:0;s:3:\"ins\";i:0;s:6:\"keygen\";i:0;s:3:\"kbd\";i:0;s:5:\"label\";i:1;s:6:\"legend\";i:1;s:2:\"li\";i:2;s:3:\"map\";i:0;s:4:\"mark\";i:0;s:4:\"menu\";i:0;s:7:\"marquee\";i:0;s:5:\"meter\";i:0;s:3:\"nav\";i:0;s:4:\"nobr\";i:0;s:6:\"object\";i:0;s:2:\"ol\";i:2;s:8:\"optgroup\";i:0;s:6:\"option\";i:0;s:6:\"output\";i:0;s:1:\"p\";i:2;s:5:\"param\";i:0;s:3:\"pre\";i:2;s:8:\"progress\";i:0;s:1:\"q\";i:0;s:2:\"rp\";i:0;s:2:\"rt\";i:0;s:4:\"ruby\";i:0;s:1:\"s\";i:0;s:4:\"samp\";i:0;s:6:\"script\";i:0;s:7:\"section\";i:0;s:6:\"select\";i:0;s:5:\"small\";i:0;s:6:\"source\";i:0;s:4:\"span\";i:2;s:6:\"strike\";i:0;s:6:\"strong\";i:2;s:3:\"sub\";i:1;s:7:\"summary\";i:1;s:3:\"sup\";i:0;s:5:\"table\";i:2;s:5:\"tbody\";i:1;s:2:\"td\";i:2;s:8:\"textarea\";i:0;s:5:\"tfoot\";i:1;s:2:\"th\";i:2;s:5:\"thead\";i:0;s:4:\"time\";i:0;s:2:\"tr\";i:2;s:2:\"tt\";i:2;s:1:\"u\";i:0;s:2:\"ul\";i:2;s:3:\"var\";i:0;s:5:\"video\";i:0;s:3:\"wbr\";i:0;}');
INSERT INTO `z_module_vars` VALUES (187,'Categories','userrootcat','s:17:\"/__SYSTEM__/Users\";');
INSERT INTO `z_module_vars` VALUES (188,'Categories','allowusercatedit','i:0;');
INSERT INTO `z_module_vars` VALUES (189,'Categories','autocreateusercat','i:0;');
INSERT INTO `z_module_vars` VALUES (190,'Categories','autocreateuserdefaultcat','i:0;');
INSERT INTO `z_module_vars` VALUES (191,'Categories','userdefaultcatname','s:7:\"Default\";');
INSERT INTO `z_module_vars` VALUES (192,'Legal','termsofuse','b:1;');
INSERT INTO `z_module_vars` VALUES (193,'Legal','privacypolicy','b:1;');
INSERT INTO `z_module_vars` VALUES (194,'Legal','accessibilitystatement','b:1;');
INSERT INTO `z_module_vars` VALUES (195,'Mailer','mailertype','i:1;');
INSERT INTO `z_module_vars` VALUES (196,'Mailer','charset','s:5:\"utf-8\";');
INSERT INTO `z_module_vars` VALUES (197,'Mailer','encoding','s:4:\"8bit\";');
INSERT INTO `z_module_vars` VALUES (198,'Mailer','html','b:0;');
INSERT INTO `z_module_vars` VALUES (199,'Mailer','wordwrap','i:50;');
INSERT INTO `z_module_vars` VALUES (200,'Mailer','msmailheaders','b:0;');
INSERT INTO `z_module_vars` VALUES (201,'Mailer','sendmailpath','s:18:\"/usr/sbin/sendmail\";');
INSERT INTO `z_module_vars` VALUES (202,'Mailer','smtpauth','b:0;');
INSERT INTO `z_module_vars` VALUES (203,'Mailer','smtpserver','s:9:\"localhost\";');
INSERT INTO `z_module_vars` VALUES (204,'Mailer','smtpport','i:25;');
INSERT INTO `z_module_vars` VALUES (205,'Mailer','smtptimeout','i:10;');
INSERT INTO `z_module_vars` VALUES (206,'Mailer','smtpusername','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (207,'Mailer','smtppassword','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (208,'Search','itemsperpage','i:10;');
INSERT INTO `z_module_vars` VALUES (209,'Search','limitsummary','i:255;');

--
-- Table structure for table `z_modules`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_modules` (
  `z_id` int(11) NOT NULL,
  `z_name` varchar(64) NOT NULL,
  `z_type` tinyint(4) NOT NULL DEFAULT '0',
  `z_displayname` varchar(64) NOT NULL,
  `z_url` varchar(64) NOT NULL,
  `z_description` varchar(255) NOT NULL,
  `z_directory` varchar(64) NOT NULL,
  `z_version` varchar(10) NOT NULL DEFAULT '0',
  `z_capabilities` longtext NOT NULL,
  `z_state` smallint(6) NOT NULL DEFAULT '0',
  `z_securityschema` longtext NOT NULL,
  `z_core_min` varchar(9) NOT NULL,
  `z_core_max` varchar(9) NOT NULL,
  PRIMARY KEY (`z_id`),
  KEY `state` (`z_state`),
  KEY `mod_state` (`z_name`,`z_state`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_modules`
--

INSERT INTO `z_modules` VALUES (1,'Extensions',3,'Extensions manager','extensions','Manage your modules and plugins.','Extensions','3.7.9','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:12:\"Extensions::\";s:2:\"::\";}','','');
INSERT INTO `z_modules` VALUES (2,'Admin',3,'Administration panel','adminpanel','Provides the site\'s administration panel, and the ability to configure and manage it.','Admin','1.9.0','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:7:\"Admin::\";s:38:\"Admin Category name::Admin Category ID\";}','','');
INSERT INTO `z_modules` VALUES (3,'Blocks',3,'Blocks manager','blocks','Provides an interface for adding, removing and administering the site\'s side and center blocks.','Blocks','3.8.0','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:2:{s:8:\"Blocks::\";s:30:\"Block key:Block title:Block ID\";s:16:\"Blocks::position\";s:26:\"Position name::Position ID\";}','','');
INSERT INTO `z_modules` VALUES (4,'Categories',3,'Categories manager','categories','Provides support for categorisation of content in other modules, and an interface for adding, removing and administering categories.','Categories','1.2.1','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:20:\"Categories::Category\";s:40:\"Category ID:Category Path:Category IPath\";}','','');
INSERT INTO `z_modules` VALUES (5,'Errors',3,'Errors','errors','Provides the core system of the site with error-logging capability.','Errors','1.1.1','a:1:{s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:8:\"Errors::\";s:2:\"::\";}','','');
INSERT INTO `z_modules` VALUES (6,'Groups',3,'Groups manager','groups','Provides support for user groups, and incorporates an interface for adding, removing and administering them.','Groups','2.3.2','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:8:\"Groups::\";s:10:\"Group ID::\";}','','');
INSERT INTO `z_modules` VALUES (7,'Mailer',3,'Mailer','mailer','Provides mail-sending functionality for communication with the site\'s users, and an interface for managing the e-mail service settings used by the mailer.','Mailer','1.3.1','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:8:\"Mailer::\";s:2:\"::\";}','','');
INSERT INTO `z_modules` VALUES (8,'PageLock',3,'Page lock','pagelock','Provides the ability to lock pages when they are in use, for content and access control.','PageLock','1.1.1','a:0:{}',1,'a:1:{s:10:\"PageLock::\";s:2:\"::\";}','','');
INSERT INTO `z_modules` VALUES (9,'Permissions',3,'Permission manager','permissions','Provides an interface for fine-grained management of accessibility of the site\'s functionality and content through permission rules.','Permissions','1.1.1','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:13:\"Permissions::\";s:2:\"::\";}','','');
INSERT INTO `z_modules` VALUES (10,'Search',3,'Site search','search','Provides an engine for searching within the site, and an interface for managing search page settings.','Search','1.5.1','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:8:\"Search::\";s:13:\"Module name::\";}','','');
INSERT INTO `z_modules` VALUES (11,'SecurityCenter',3,'Security center','securitycenter','Provides the ability to manage site security. It logs attempted hacks and similar events, and incorporates a user interface for customising alerting and security settings.','SecurityCenter','1.4.3','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:16:\"SecurityCenter::\";s:2:\"::\";}','','');
INSERT INTO `z_modules` VALUES (12,'Settings',3,'General settings','settings','Provides an interface for managing the site\'s general settings, i.e. site start page settings, multi-lingual settings, error reporting options and various other features that are not administered within other modules.','Settings','2.9.6','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:10:\"Settings::\";s:2:\"::\";}','','');
INSERT INTO `z_modules` VALUES (13,'SysInfo',3,'System info','sysinfo','Provides detailed information reports about the system configuration and environment, for tracking and troubleshooting purposes.','SysInfo','1.1.1','a:1:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:9:\"SysInfo::\";s:2:\"::\";}','','');
INSERT INTO `z_modules` VALUES (14,'Theme',3,'Themes manager','theme','Provides the site\'s theming system, and an interface for managing themes, to control the site\'s presentation and appearance.','Theme','3.4.1','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:1:{s:7:\"Theme::\";s:12:\"Theme name::\";}','','');
INSERT INTO `z_modules` VALUES (15,'Users',3,'Users manager','users','Provides an interface for configuring and administering registered user accounts. Incorporates all needed functionality, but can work in close unison with the third party profile module configured in the general settings of the site.','Users','2.1.3','a:4:{s:14:\"authentication\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:15:\"hook_subscriber\";a:1:{s:7:\"enabled\";b:1;}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:2:{s:7:\"Users::\";s:14:\"Uname::User ID\";s:16:\"Users::MailUsers\";s:2:\"::\";}','','');
INSERT INTO `z_modules` VALUES (16,'Clip',2,'Clip','clip','Dynamic content publishing platform for Zikula.','Clip','0.4.8','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',1,'a:4:{s:6:\"clip::\";s:2:\"::\";s:11:\"clip:input:\";s:21:\"tid:pid:workflowstate\";s:10:\"clip:full:\";s:16:\"tid:pid:template\";s:10:\"clip:list:\";s:13:\"tid::template\";}','1.3.0','');
INSERT INTO `z_modules` VALUES (17,'ExampleDoctrine',2,'ExampleDoctrine','exampledoctrine','ExampleDoctrine module.','ExampleDoctrine','1.0.0','a:1:{s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',1,'a:2:{s:17:\"ExampleDoctrine::\";s:2:\"::\";s:21:\"ExampleDoctrine:User:\";s:10:\"UserName::\";}','','');
INSERT INTO `z_modules` VALUES (18,'ExtDB',2,'Extensions Database','Extensions Database','Extensions Database module generated by ModuleStudio 0.4.10','ExtDB','1.0','a:2:{s:5:\"admin\";s:3:\"1.0\";s:4:\"user\";s:3:\"1.0\";}',1,'a:12:{s:7:\"ExtDB::\";s:2:\"::\";s:16:\"ExtDB:Component:\";s:13:\"ComponentID::\";s:23:\"ExtDB:ComponentVersion:\";s:20:\"ComponentVersionID::\";s:32:\"ExtDB:Component:ComponentVersion\";s:31:\"ComponentID:ComponentVersionID:\";s:19:\"ExtDB:Distribution:\";s:16:\"DistributionID::\";s:26:\"ExtDB:DistributionVersion:\";s:23:\"DistributionVersionID::\";s:38:\"ExtDB:Distribution:DistributionVersion\";s:37:\"DistributionID:DistributionVersionID:\";s:18:\"ExtDB:CoreVersion:\";s:15:\"CoreVersionID::\";s:18:\"ExtDB:Translation:\";s:15:\"TranslationID::\";s:34:\"ExtDB:ComponentVersion:Translation\";s:33:\"ComponentVersionID:TranslationID:\";s:37:\"ExtDB:DistributionVersion:Translation\";s:36:\"DistributionVersionID:TranslationID:\";s:29:\"ExtDB:CoreVersion:Translation\";s:28:\"CoreVersionID:TranslationID:\";}','','');
INSERT INTO `z_modules` VALUES (19,'EZComments',2,'Comments','comments','Attach comments to every kind of content using hooks','EZComments','3.0.1','a:4:{s:13:\"hook_provider\";a:1:{s:7:\"enabled\";b:1;}s:15:\"hook_subscriber\";a:1:{s:7:\"enabled\";b:1;}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',1,'a:3:{s:12:\"EZComments::\";s:25:\"Module:Item ID:Comment ID\";s:21:\"EZComments::trackback\";s:15:\"Module:Item ID:\";s:20:\"EZComments::pingback\";s:15:\"Module:Item ID:\";}','','');
INSERT INTO `z_modules` VALUES (20,'Gettext',2,'Gettext','gettext','Extract translation strings from themes and modules','Gettext','1.0.1','a:1:{s:4:\"user\";s:3:\"1.0\";}',1,'a:1:{s:9:\"Gettext::\";s:2:\"::\";}','','');
INSERT INTO `z_modules` VALUES (21,'KnowledgeBase',2,'KnowledgeBase','KnowledgeBase','KnowledgeBase module generated by ModuleStudio 0.5.2','KnowledgeBase','1.0.0','a:3:{s:15:\"hook_subscriber\";a:1:{s:7:\"enabled\";b:1;}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',1,'a:2:{s:15:\"KnowledgeBase::\";s:2:\"::\";s:21:\"KnowledgeBase:Ticket:\";s:10:\"TicketID::\";}','','');
INSERT INTO `z_modules` VALUES (22,'Legal',2,'Legal info manager','legalmod','Provides an interface for managing the site\'s \'Terms of use\', \'Privacy statement\' and \'Accessibility statement\'.','Legal','1.6.0','a:2:{s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',3,'a:4:{s:7:\"Legal::\";s:2:\"::\";s:17:\"Legal::termsofuse\";s:2:\"::\";s:14:\"Legal::privacy\";s:2:\"::\";s:29:\"Legal::accessibilitystatement\";s:2:\"::\";}','','');
INSERT INTO `z_modules` VALUES (23,'MyHookProvider',2,'MyHookProvider','myhookprovider','Module that provides hooks','MyHookProvider','1.0.0','a:3:{s:13:\"hook_provider\";a:1:{s:7:\"enabled\";b:1;}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',1,'a:1:{s:16:\"MyHookProvider::\";s:12:\"Modulename::\";}','','');
INSERT INTO `z_modules` VALUES (24,'MyHookSubscriber',2,'MyHookSubscriber','myhooksubscriber','Module that subscribes to hooks','MyHookSubscriber','1.0.0','a:3:{s:15:\"hook_subscriber\";a:1:{s:7:\"enabled\";b:1;}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',1,'a:1:{s:18:\"MyHookSubscriber::\";s:42:\"MyHookSubscriber name::MyHookSubscriber ID\";}','','');
INSERT INTO `z_modules` VALUES (25,'News',2,'News publisher','news','Provides the ability to publish and manage news articles contributed by site users, with support for news categories and various associated blocks.','News','3.0.1','a:3:{s:15:\"hook_subscriber\";a:1:{s:7:\"enabled\";b:1;}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',1,'a:3:{s:6:\"News::\";s:26:\"Contributor ID::Article ID\";s:19:\"News:pictureupload:\";s:2:\"::\";s:24:\"News:publicationdetails:\";s:2:\"::\";}','1.3.0','');
INSERT INTO `z_modules` VALUES (26,'PostCalendar',2,'PostCalendar','postcalendar','Calendar for Zikula','PostCalendar','7.0.0','a:4:{s:13:\"hook_provider\";a:1:{s:7:\"enabled\";b:1;}s:15:\"hook_subscriber\";a:1:{s:7:\"enabled\";b:1;}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',1,'a:2:{s:19:\"PostCalendar::Event\";s:21:\"Event Title::Event ID\";s:14:\"PostCalendar::\";s:2:\"::\";}','1.3.0','');
INSERT INTO `z_modules` VALUES (27,'Profile',2,'Profile manager','profile','Provides a personal account control panel for each registered user, an interface to administer the personal information items displayed within it, and a registered users list functionality. Works in close unison with the \'Users\' module.','Profile','1.5.3','a:3:{s:7:\"profile\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:5:\"admin\";a:1:{s:7:\"version\";s:3:\"1.0\";}s:4:\"user\";a:1:{s:7:\"version\";s:3:\"1.0\";}}',1,'a:6:{s:9:\"Profile::\";s:2:\"::\";s:13:\"Profile:view:\";s:2:\"::\";s:13:\"Profile::item\";s:56:\"DynamicUserData PropertyName::DynamicUserData PropertyID\";s:16:\"Profile:Members:\";s:2:\"::\";s:22:\"Profile:Members:recent\";s:2:\"::\";s:22:\"Profile:Members:online\";s:2:\"::\";}','','');
INSERT INTO `z_modules` VALUES (28,'Quickcheck',2,'Quickcheck','Quickcheck','A hookable module for displaying quizzes or exams as part of other modules.','Quickcheck','1.0','a:2:{s:5:\"admin\";s:3:\"1.0\";s:4:\"user\";s:3:\"1.0\";}',1,'a:1:{s:14:\"exam::question\";s:32:\"exam id (int)::question id (int)\";}','','');

--
-- Table structure for table `z_objectdata_attributes`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_objectdata_attributes` (
  `oba_id` int(11) NOT NULL,
  `oba_attribute_name` varchar(80) NOT NULL,
  `oba_object_id` int(11) NOT NULL DEFAULT '0',
  `oba_object_type` varchar(80) NOT NULL,
  `oba_value` longtext NOT NULL,
  `oba_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `oba_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `oba_cr_uid` int(11) NOT NULL DEFAULT '0',
  `oba_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `oba_lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`oba_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_objectdata_attributes`
--

INSERT INTO `z_objectdata_attributes` VALUES (1,'code',5,'categories_category','Y','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_objectdata_attributes` VALUES (2,'code',6,'categories_category','N','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_objectdata_attributes` VALUES (3,'code',11,'categories_category','P','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_objectdata_attributes` VALUES (4,'code',12,'categories_category','C','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_objectdata_attributes` VALUES (5,'code',13,'categories_category','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_objectdata_attributes` VALUES (6,'code',14,'categories_category','O','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_objectdata_attributes` VALUES (7,'code',15,'categories_category','R','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_objectdata_attributes` VALUES (8,'code',17,'categories_category','M','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_objectdata_attributes` VALUES (9,'code',18,'categories_category','F','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_objectdata_attributes` VALUES (10,'code',26,'categories_category','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_objectdata_attributes` VALUES (11,'code',27,'categories_category','I','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_objectdata_attributes` VALUES (12,'code',29,'categories_category','P','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);
INSERT INTO `z_objectdata_attributes` VALUES (13,'code',30,'categories_category','A','A','2011-01-27 03:46:38',0,'2011-01-27 03:46:38',0);

--
-- Table structure for table `z_objectdata_log`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_objectdata_log` (
  `obl_id` int(11) NOT NULL,
  `obl_object_type` varchar(80) NOT NULL,
  `obl_object_id` int(11) NOT NULL DEFAULT '0',
  `obl_op` varchar(16) NOT NULL,
  `obl_diff` longtext,
  `obl_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `obl_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `obl_cr_uid` int(11) NOT NULL DEFAULT '0',
  `obl_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `obl_lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`obl_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_objectdata_log`
--


--
-- Table structure for table `z_objectdata_meta`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_objectdata_meta` (
  `obm_id` int(11) NOT NULL,
  `obm_module` varchar(40) NOT NULL,
  `obm_table` varchar(40) NOT NULL,
  `obm_idcolumn` varchar(40) NOT NULL,
  `obm_obj_id` int(11) NOT NULL DEFAULT '0',
  `obm_permissions` varchar(255) DEFAULT NULL,
  `obm_dc_title` varchar(80) DEFAULT NULL,
  `obm_dc_author` varchar(80) DEFAULT NULL,
  `obm_dc_subject` varchar(255) DEFAULT NULL,
  `obm_dc_keywords` varchar(128) DEFAULT NULL,
  `obm_dc_description` varchar(255) DEFAULT NULL,
  `obm_dc_publisher` varchar(128) DEFAULT NULL,
  `obm_dc_contributor` varchar(128) DEFAULT NULL,
  `obm_dc_startdate` datetime DEFAULT '1970-01-01 00:00:00',
  `obm_dc_enddate` datetime DEFAULT '1970-01-01 00:00:00',
  `obm_dc_type` varchar(128) DEFAULT NULL,
  `obm_dc_format` varchar(128) DEFAULT NULL,
  `obm_dc_uri` varchar(255) DEFAULT NULL,
  `obm_dc_source` varchar(128) DEFAULT NULL,
  `obm_dc_language` varchar(32) DEFAULT NULL,
  `obm_dc_relation` varchar(255) DEFAULT NULL,
  `obm_dc_coverage` varchar(64) DEFAULT NULL,
  `obm_dc_entity` varchar(64) DEFAULT NULL,
  `obm_dc_comment` varchar(255) DEFAULT NULL,
  `obm_dc_extra` varchar(255) DEFAULT NULL,
  `obm_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `obm_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `obm_cr_uid` int(11) NOT NULL DEFAULT '0',
  `obm_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `obm_lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`obm_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_objectdata_meta`
--


--
-- Table structure for table `z_sc_intrusion`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_sc_intrusion` (
  `z_id` int(11) NOT NULL,
  `z_name` varchar(128) NOT NULL,
  `z_tag` varchar(40) DEFAULT NULL,
  `z_value` longtext NOT NULL,
  `z_page` longtext NOT NULL,
  `z_uid` int(11) DEFAULT NULL,
  `z_ip` varchar(40) NOT NULL,
  `z_impact` int(11) NOT NULL DEFAULT '0',
  `z_filters` longtext NOT NULL,
  `z_date` datetime NOT NULL,
  PRIMARY KEY (`z_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_sc_intrusion`
--


--
-- Table structure for table `z_search_result`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_search_result` (
  `z_id` int(11) NOT NULL,
  `z_title` varchar(255) NOT NULL,
  `z_text` longtext,
  `z_module` varchar(100) DEFAULT NULL,
  `z_extra` varchar(100) DEFAULT NULL,
  `z_created` datetime DEFAULT NULL,
  `z_found` datetime DEFAULT NULL,
  `z_sesid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`z_id`),
  KEY `title` (`z_title`),
  KEY `module` (`z_module`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_search_result`
--


--
-- Table structure for table `z_search_stat`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_search_stat` (
  `z_id` int(11) NOT NULL,
  `z_search` varchar(50) NOT NULL,
  `z_count` int(11) NOT NULL DEFAULT '0',
  `z_date` date DEFAULT NULL,
  PRIMARY KEY (`z_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_search_stat`
--


--
-- Table structure for table `z_session_info`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_session_info` (
  `z_sessid` varchar(40) NOT NULL,
  `z_ipaddr` varchar(32) NOT NULL,
  `z_lastused` datetime DEFAULT '1970-01-01 00:00:00',
  `z_uid` int(11) DEFAULT '0',
  `z_remember` tinyint(4) NOT NULL DEFAULT '0',
  `z_vars` longtext NOT NULL,
  PRIMARY KEY (`z_sessid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_session_info`
--

INSERT INTO `z_session_info` VALUES ('it3pmp78mt94t57i3025e8bns7rvphv8','837ec5754f503cfaaee0929fd48974e7','2011-01-27 03:46:45',2,0,'ZSVrand|a:1:{s:5:\"admin\";s:40:\"p3h*JjYz+ziXHvjLAI9fM~Zmr~%}EH=mzkca(-6r\";}ZSVuseragent|s:40:\"ff9d76fd01028c4b17ae27d7686c642244256ae1\";ZSVuid|i:2;');

--
-- Table structure for table `z_themes`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_themes` (
  `z_id` int(11) NOT NULL,
  `z_name` varchar(64) NOT NULL,
  `z_type` tinyint(4) NOT NULL DEFAULT '0',
  `z_displayname` varchar(64) NOT NULL,
  `z_description` varchar(255) NOT NULL,
  `z_directory` varchar(64) NOT NULL,
  `z_version` varchar(10) NOT NULL DEFAULT '0',
  `z_contact` varchar(255) NOT NULL,
  `z_admin` tinyint(4) NOT NULL DEFAULT '0',
  `z_user` tinyint(4) NOT NULL DEFAULT '0',
  `z_system` tinyint(4) NOT NULL DEFAULT '0',
  `z_state` tinyint(4) NOT NULL DEFAULT '0',
  `z_xhtml` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`z_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_themes`
--

INSERT INTO `z_themes` VALUES (1,'Admin',3,'Admin','Zikula Admin Theme','Admin','1.0','',1,1,0,1,1);
INSERT INTO `z_themes` VALUES (2,'Andreas08',3,'Andreas08','Based on the theme Andreas08 by Andreas Viklund and extended for Zikula with the CSS Framework \'fluid960gs\'.','Andreas08','2.0','',1,1,0,1,1);
INSERT INTO `z_themes` VALUES (3,'Atom',3,'Atom','The Atom theme is an auxiliary theme specially designed for rendering pages in Atom mark-up.','Atom','1.0','',0,0,1,1,0);
INSERT INTO `z_themes` VALUES (4,'Printer',3,'Printer','The Printer theme is an auxiliary theme designed specially for outputting pages in a printer-friendly format.','Printer','2.0','',0,0,1,1,1);
INSERT INTO `z_themes` VALUES (5,'RSS',3,'RSS','The RSS theme is an auxiliary theme designed specially for outputting pages as an RSS feed.','RSS','1.0','',0,0,1,1,0);
INSERT INTO `z_themes` VALUES (6,'SeaBreeze',3,'SeaBreeze','The SeaBreeze theme is a browser-oriented theme, and was updated for the release of Zikula 1.0, with revised colours and new graphics.','SeaBreeze','3.2','',0,1,0,1,1);

--
-- Table structure for table `z_userblocks`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_userblocks` (
  `z_uid` int(11) NOT NULL DEFAULT '0',
  `z_bid` int(11) NOT NULL DEFAULT '0',
  `z_active` tinyint(4) NOT NULL DEFAULT '1',
  `z_last_update` datetime DEFAULT NULL,
  KEY `bid_uid_idx` (`z_uid`,`z_bid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_userblocks`
--


--
-- Table structure for table `z_users`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_users` (
  `z_uid` int(11) NOT NULL,
  `z_uname` varchar(25) NOT NULL,
  `z_email` varchar(60) NOT NULL,
  `z_pass` varchar(138) NOT NULL,
  `z_passreminder` varchar(255) NOT NULL,
  `z_activated` smallint(6) NOT NULL DEFAULT '0',
  `z_approved_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `z_approved_by` int(11) NOT NULL DEFAULT '0',
  `z_user_regdate` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `z_lastlogin` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `z_theme` varchar(255) NOT NULL,
  `z_ublockon` tinyint(4) NOT NULL DEFAULT '0',
  `z_ublock` longtext NOT NULL,
  PRIMARY KEY (`z_uid`),
  KEY `uname` (`z_uname`),
  KEY `email` (`z_email`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_users`
--

INSERT INTO `z_users` VALUES (1,'guest','','','',1,'2011-01-15 06:56:17',2,'2011-01-15 06:56:17','1970-01-01 00:00:00','',0,'');
INSERT INTO `z_users` VALUES (2,'admin','example@example.com','8$N(Krv$e778359c124b41d2032774812e9fbb48712b63739502563543ea25493c5e1315','',1,'2011-01-15 06:56:17',2,'2011-01-15 06:56:20','2011-01-15 06:56:20','',0,'');

--
-- Table structure for table `z_users_verifychg`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_users_verifychg` (
  `z_id` int(11) NOT NULL,
  `z_changetype` tinyint(4) NOT NULL DEFAULT '0',
  `z_uid` int(11) NOT NULL DEFAULT '0',
  `z_newemail` varchar(60) NOT NULL,
  `z_verifycode` varchar(138) NOT NULL,
  `z_created_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`z_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_users_verifychg`
--


--
-- Table structure for table `z_workflows`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_workflows` (
  `id` int(11) NOT NULL,
  `metaid` int(11) NOT NULL DEFAULT '0',
  `module` varchar(255) NOT NULL,
  `schemaname` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `type` smallint(6) NOT NULL DEFAULT '1',
  `obj_table` varchar(40) NOT NULL,
  `obj_idcolumn` varchar(40) NOT NULL,
  `obj_id` int(11) NOT NULL DEFAULT '0',
  `busy` int(11) NOT NULL DEFAULT '0',
  `debug` longblob,
  PRIMARY KEY (`id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_workflows`
--

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-01-27  9:31:56
