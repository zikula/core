-- MySQL dump 10.11
--
-- Host: localhost    Database: zikula130
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
  `pn_cid` bigint(20) NOT NULL,
  `pn_name` varchar(32) NOT NULL,
  `pn_description` varchar(254) NOT NULL,
  PRIMARY KEY (`pn_cid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_admin_category`
--

INSERT INTO `z_admin_category` VALUES (1,'System','Core modules at the heart of operation of the site.');
INSERT INTO `z_admin_category` VALUES (2,'Layout','Layout modules for controlling the site\'s look and feel.');
INSERT INTO `z_admin_category` VALUES (3,'Users','Modules for controlling user membership, access rights and profiles.');
INSERT INTO `z_admin_category` VALUES (4,'Content','Modules for providing content to your users.');
INSERT INTO `z_admin_category` VALUES (5,'Uncategorised','Newly-installed or uncategorized modules.');
INSERT INTO `z_admin_category` VALUES (6,'Security','Modules for managing the site\'s security.');

--
-- Table structure for table `z_admin_module`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_admin_module` (
  `pn_amid` bigint(20) NOT NULL,
  `pn_mid` bigint(20) NOT NULL DEFAULT '0',
  `pn_cid` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pn_amid`),
  KEY `mid_cid` (`pn_mid`,`pn_cid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_admin_module`
--

INSERT INTO `z_admin_module` VALUES (1,1,1);
INSERT INTO `z_admin_module` VALUES (2,12,1);
INSERT INTO `z_admin_module` VALUES (3,14,2);
INSERT INTO `z_admin_module` VALUES (4,2,1);
INSERT INTO `z_admin_module` VALUES (5,9,3);
INSERT INTO `z_admin_module` VALUES (6,6,3);
INSERT INTO `z_admin_module` VALUES (7,3,2);
INSERT INTO `z_admin_module` VALUES (8,15,3);
INSERT INTO `z_admin_module` VALUES (9,11,6);
INSERT INTO `z_admin_module` VALUES (10,16,4);
INSERT INTO `z_admin_module` VALUES (11,4,4);
INSERT INTO `z_admin_module` VALUES (12,7,1);
INSERT INTO `z_admin_module` VALUES (13,5,1);
INSERT INTO `z_admin_module` VALUES (14,10,4);
INSERT INTO `z_admin_module` VALUES (15,8,1);
INSERT INTO `z_admin_module` VALUES (16,13,6);

--
-- Table structure for table `z_block_placements`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_block_placements` (
  `pn_pid` bigint(20) NOT NULL DEFAULT '0',
  `pn_bid` bigint(20) NOT NULL DEFAULT '0',
  `pn_order` bigint(20) NOT NULL DEFAULT '0',
  KEY `bid_pid_idx` (`pn_bid`,`pn_pid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_block_placements`
--

INSERT INTO `z_block_placements` VALUES (1,1,0);
INSERT INTO `z_block_placements` VALUES (3,2,0);
INSERT INTO `z_block_placements` VALUES (2,3,0);

--
-- Table structure for table `z_block_positions`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_block_positions` (
  `pn_pid` bigint(20) NOT NULL,
  `pn_name` varchar(255) NOT NULL,
  `pn_description` varchar(255) NOT NULL,
  PRIMARY KEY (`pn_pid`),
  KEY `name_idx` (`pn_name`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_block_positions`
--

INSERT INTO `z_block_positions` VALUES (1,'left','Left blocks');
INSERT INTO `z_block_positions` VALUES (2,'right','Right blocks');
INSERT INTO `z_block_positions` VALUES (3,'center','Center blocks');

--
-- Table structure for table `z_blocks`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_blocks` (
  `pn_bid` bigint(20) NOT NULL,
  `pn_bkey` varchar(255) NOT NULL,
  `pn_title` varchar(255) NOT NULL,
  `pn_content` longtext NOT NULL,
  `pn_url` longtext NOT NULL,
  `pn_mid` bigint(20) NOT NULL DEFAULT '0',
  `pn_filter` longtext NOT NULL,
  `pn_active` bigint(20) NOT NULL DEFAULT '1',
  `pn_collapsable` bigint(20) NOT NULL DEFAULT '1',
  `pn_defaultstate` bigint(20) NOT NULL DEFAULT '1',
  `pn_refresh` bigint(20) NOT NULL DEFAULT '0',
  `pn_last_update` datetime NOT NULL,
  `pn_language` varchar(30) NOT NULL,
  PRIMARY KEY (`pn_bid`),
  KEY `active_idx` (`pn_active`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_blocks`
--

INSERT INTO `z_blocks` VALUES (1,'extmenu','Main menu','a:5:{s:14:\"displaymodules\";s:1:\"0\";s:10:\"stylesheet\";s:11:\"extmenu.css\";s:8:\"template\";s:24:\"blocks_block_extmenu.tpl\";s:11:\"blocktitles\";a:1:{s:2:\"en\";s:9:\"Main menu\";}s:5:\"links\";a:1:{s:2:\"en\";a:4:{i:0;a:7:{s:4:\"name\";s:4:\"Home\";s:3:\"url\";s:10:\"{homepage}\";s:5:\"title\";s:26:\"Go to the site\'s home page\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:1;a:7:{s:4:\"name\";s:14:\"Administration\";s:3:\"url\";s:24:\"{Admin:adminpanel:admin}\";s:5:\"title\";s:29:\"Go to the site administration\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:2;a:7:{s:4:\"name\";s:10:\"My Account\";s:3:\"url\";s:7:\"{Users}\";s:5:\"title\";s:24:\"Go to your account panel\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:3;a:7:{s:4:\"name\";s:7:\"Log out\";s:3:\"url\";s:14:\"{Users:logout}\";s:5:\"title\";s:20:\"Log out of this site\";s:5:\"level\";i:0;s:8:\"parentid\";N;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}}}}','',3,'',1,1,1,3600,'2010-06-25 03:32:28','');
INSERT INTO `z_blocks` VALUES (2,'html','This site is powered by Zikula!','<p><a href=\"http://www.zikula.org\">Zikula</a> is a content management system (CMS) and application framework. It is secure and stable, and is a good choice for sites with a large volume of traffic.</p><p>With Zikula:</p><ul><li>you can customise all aspects of the site\'s appearance through themes, with support for CSS style sheets, JavaScript, Flash and all other modern web development technologies;</li><li>you can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation and internationalisation of your site and pages;</li><li>you can be sure that your pages will display properly in all browsers, thanks to Zikula\'s full compliance with W3C HTML standards;</li><li>you get a standard application-programming interface (API) that lets you easily augment your site\'s functionality through modules, blocks and other extensions;</li><li>you can get help and support from the Zikula community of webmasters and developers at <a href=\"http://www.zikula.org\">zikula.org</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>','',3,'',1,1,1,3600,'2010-06-25 03:32:28','');
INSERT INTO `z_blocks` VALUES (3,'login','User log-in','','',15,'',1,1,1,3600,'2010-06-25 03:32:28','');

--
-- Table structure for table `z_categories_category`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_categories_category` (
  `cat_id` bigint(20) NOT NULL,
  `cat_parent_id` bigint(20) NOT NULL DEFAULT '1',
  `cat_is_locked` bigint(20) NOT NULL DEFAULT '0',
  `cat_is_leaf` bigint(20) NOT NULL DEFAULT '0',
  `cat_name` varchar(255) NOT NULL,
  `cat_value` varchar(255) NOT NULL,
  `cat_sort_value` bigint(20) NOT NULL DEFAULT '0',
  `cat_display_name` longtext NOT NULL,
  `cat_display_desc` longtext NOT NULL,
  `cat_path` longtext NOT NULL,
  `cat_ipath` varchar(255) NOT NULL,
  `cat_status` varchar(1) NOT NULL DEFAULT 'A',
  `cat_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cat_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cat_cr_uid` bigint(20) NOT NULL DEFAULT '0',
  `cat_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cat_lu_uid` bigint(20) NOT NULL DEFAULT '0',
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

INSERT INTO `z_categories_category` VALUES (1,0,1,0,'__SYSTEM__','',0,'b:0;','b:0;','/__SYSTEM__','/1','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (2,1,0,0,'Modules','',0,'a:1:{s:2:\"en\";s:7:\"Modules\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules','/1/2','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (3,1,0,0,'General','',0,'a:1:{s:2:\"en\";s:7:\"General\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General','/1/3','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (4,3,0,0,'YesNo','',0,'a:1:{s:2:\"en\";s:6:\"Yes/No\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/YesNo','/1/3/4','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (5,4,0,1,'1 - Yes','',0,'b:0;','b:0;','/__SYSTEM__/General/YesNo/1 - Yes','/1/3/4/5','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (6,4,0,1,'2 - No','',0,'b:0;','b:0;','/__SYSTEM__/General/YesNo/2 - No','/1/3/4/6','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (10,3,0,0,'Publication Status (extended)','',0,'a:1:{s:2:\"en\";s:29:\"Publication status (extended)\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended','/1/3/10','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (11,10,0,1,'Pending','',0,'a:1:{s:2:\"en\";s:7:\"Pending\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Pending','/1/3/10/11','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (12,10,0,1,'Checked','',0,'a:1:{s:2:\"en\";s:7:\"Checked\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Checked','/1/3/10/12','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (13,10,0,1,'Approved','',0,'a:1:{s:2:\"en\";s:8:\"Approved\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Approved','/1/3/10/13','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (14,10,0,1,'On-line','',0,'a:1:{s:2:\"en\";s:7:\"On-line\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Online','/1/3/10/14','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (15,10,0,1,'Rejected','',0,'a:1:{s:2:\"en\";s:8:\"Rejected\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Rejected','/1/3/10/15','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (16,3,0,0,'Gender','',0,'a:1:{s:2:\"en\";s:6:\"Gender\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Gender','/1/3/16','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (17,16,0,1,'Male','',0,'a:1:{s:2:\"en\";s:4:\"Male\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Gender/Male','/1/3/16/17','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (18,16,0,1,'Female','',0,'a:1:{s:2:\"en\";s:6:\"Female\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Gender/Female','/1/3/16/18','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (19,3,0,0,'Title','',0,'a:1:{s:2:\"en\";s:5:\"Title\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title','/1/3/19','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (20,19,0,1,'Mr','',0,'a:1:{s:2:\"en\";s:3:\"Mr.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Mr','/1/3/19/20','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (21,19,0,1,'Mrs','',0,'a:1:{s:2:\"en\";s:4:\"Mrs.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Mrs','/1/3/19/21','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (22,19,0,1,'Ms','',0,'a:1:{s:2:\"en\";s:3:\"Ms.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Ms','/1/3/19/22','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (23,19,0,1,'Miss','',0,'a:1:{s:2:\"en\";s:4:\"Miss\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Miss','/1/3/19/23','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (24,19,0,1,'Dr','',0,'a:1:{s:2:\"en\";s:3:\"Dr.\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Title/Dr','/1/3/19/24','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (25,3,0,0,'ActiveStatus','',0,'a:1:{s:2:\"en\";s:15:\"Activity status\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus','/1/3/25','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (26,25,0,1,'Active','',0,'a:1:{s:2:\"en\";s:6:\"Active\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus/Active','/1/3/25/26','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (27,25,0,1,'Inactive','',0,'a:1:{s:2:\"en\";s:8:\"Inactive\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus/Inactive','/1/3/25/27','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (28,3,0,0,'Publication status (basic)','',0,'a:1:{s:2:\"en\";s:26:\"Publication status (basic)\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic','/1/3/28','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (29,28,0,1,'Pending','',0,'a:1:{s:2:\"en\";s:7:\"Pending\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic/Pending','/1/3/28/29','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (30,28,0,1,'Approved','',0,'a:1:{s:2:\"en\";s:8:\"Approved\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic/Approved','/1/3/28/30','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (31,1,0,0,'Users','',0,'a:1:{s:2:\"en\";s:5:\"Users\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Users','/1/31','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (32,2,0,0,'Global','',0,'a:1:{s:2:\"en\";s:6:\"Global\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global','/1/2/32','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (33,32,0,1,'Blogging','',0,'a:1:{s:2:\"en\";s:8:\"Blogging\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/Blogging','/1/2/32/33','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_categories_category` VALUES (34,32,0,1,'Music and audio','',0,'a:1:{s:2:\"en\";s:15:\"Music and audio\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/MusicAndAudio','/1/2/32/34','A','A','2010-06-25 03:32:29',0,'2010-06-25 03:32:29',0);
INSERT INTO `z_categories_category` VALUES (35,32,0,1,'Art and photography','',0,'a:1:{s:2:\"en\";s:19:\"Art and photography\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ArtAndPhotography','/1/2/32/35','A','A','2010-06-25 03:32:29',0,'2010-06-25 03:32:29',0);
INSERT INTO `z_categories_category` VALUES (36,32,0,1,'Writing and thinking','',0,'a:1:{s:2:\"en\";s:20:\"Writing and thinking\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/WritingAndThinking','/1/2/32/36','A','A','2010-06-25 03:32:29',0,'2010-06-25 03:32:29',0);
INSERT INTO `z_categories_category` VALUES (37,32,0,1,'Communications and media','',0,'a:1:{s:2:\"en\";s:24:\"Communications and media\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/CommunicationsAndMedia','/1/2/32/37','A','A','2010-06-25 03:32:29',0,'2010-06-25 03:32:29',0);
INSERT INTO `z_categories_category` VALUES (38,32,0,1,'Travel and culture','',0,'a:1:{s:2:\"en\";s:18:\"Travel and culture\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/TravelAndCulture','/1/2/32/38','A','A','2010-06-25 03:32:29',0,'2010-06-25 03:32:29',0);
INSERT INTO `z_categories_category` VALUES (39,32,0,1,'Science and technology','',0,'a:1:{s:2:\"en\";s:22:\"Science and technology\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ScienceAndTechnology','/1/2/32/39','A','A','2010-06-25 03:32:29',0,'2010-06-25 03:32:29',0);
INSERT INTO `z_categories_category` VALUES (40,32,0,1,'Sport and activities','',0,'a:1:{s:2:\"en\";s:20:\"Sport and activities\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/SportAndActivities','/1/2/32/40','A','A','2010-06-25 03:32:29',0,'2010-06-25 03:32:29',0);
INSERT INTO `z_categories_category` VALUES (41,32,0,1,'Business and work','',0,'a:1:{s:2:\"en\";s:17:\"Business and work\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/BusinessAndWork','/1/2/32/41','A','A','2010-06-25 03:32:29',0,'2010-06-25 03:32:29',0);
INSERT INTO `z_categories_category` VALUES (42,32,0,1,'Activism and action','',0,'a:1:{s:2:\"en\";s:19:\"Activism and action\";}','a:1:{s:2:\"en\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ActivismAndAction','/1/2/32/42','A','A','2010-06-25 03:32:29',0,'2010-06-25 03:32:29',0);

--
-- Table structure for table `z_categories_mapmeta`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_categories_mapmeta` (
  `cmm_id` bigint(20) NOT NULL,
  `cmm_meta_id` bigint(20) NOT NULL DEFAULT '0',
  `cmm_category_id` bigint(20) NOT NULL DEFAULT '0',
  `cmm_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cmm_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cmm_cr_uid` bigint(20) NOT NULL DEFAULT '0',
  `cmm_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cmm_lu_uid` bigint(20) NOT NULL DEFAULT '0',
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
  `cmo_id` bigint(20) NOT NULL,
  `cmo_modname` varchar(60) NOT NULL,
  `cmo_table` varchar(60) NOT NULL,
  `cmo_obj_id` bigint(20) NOT NULL DEFAULT '0',
  `cmo_obj_idcolumn` varchar(60) NOT NULL DEFAULT 'id',
  `cmo_reg_id` bigint(20) NOT NULL DEFAULT '0',
  `cmo_category_id` bigint(20) NOT NULL DEFAULT '0',
  `cmo_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cmo_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cmo_cr_uid` bigint(20) NOT NULL DEFAULT '0',
  `cmo_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cmo_lu_uid` bigint(20) NOT NULL DEFAULT '0',
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
  `crg_id` bigint(20) NOT NULL,
  `crg_modname` varchar(60) NOT NULL,
  `crg_table` varchar(60) NOT NULL,
  `crg_property` varchar(60) NOT NULL,
  `crg_category_id` bigint(20) NOT NULL DEFAULT '0',
  `crg_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `crg_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `crg_cr_uid` bigint(20) NOT NULL DEFAULT '0',
  `crg_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `crg_lu_uid` bigint(20) NOT NULL DEFAULT '0',
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
  `pn_app_id` bigint(20) NOT NULL,
  `pn_uid` bigint(20) NOT NULL DEFAULT '0',
  `pn_gid` bigint(20) NOT NULL DEFAULT '0',
  `pn_application` longblob NOT NULL,
  `pn_status` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pn_app_id`)
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
  `pn_gid` bigint(20) NOT NULL DEFAULT '0',
  `pn_uid` bigint(20) NOT NULL DEFAULT '0',
  KEY `gid_uid` (`pn_uid`,`pn_gid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_group_membership`
--

INSERT INTO `z_group_membership` VALUES (1,1);
INSERT INTO `z_group_membership` VALUES (2,2);

--
-- Table structure for table `z_group_perms`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_group_perms` (
  `pn_pid` bigint(20) NOT NULL,
  `pn_gid` bigint(20) NOT NULL DEFAULT '0',
  `pn_sequence` bigint(20) NOT NULL DEFAULT '0',
  `pn_realm` bigint(20) NOT NULL DEFAULT '0',
  `pn_component` varchar(255) NOT NULL,
  `pn_instance` varchar(255) NOT NULL,
  `pn_level` bigint(20) NOT NULL DEFAULT '0',
  `pn_bond` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pn_pid`)
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
  `pn_gid` bigint(20) NOT NULL,
  `pn_name` varchar(255) NOT NULL,
  `pn_gtype` bigint(20) NOT NULL DEFAULT '0',
  `pn_description` varchar(200) NOT NULL,
  `pn_prefix` varchar(25) NOT NULL,
  `pn_state` bigint(20) NOT NULL DEFAULT '0',
  `pn_nbuser` bigint(20) NOT NULL DEFAULT '0',
  `pn_nbumax` bigint(20) NOT NULL DEFAULT '0',
  `pn_link` bigint(20) NOT NULL DEFAULT '0',
  `pn_uidmaster` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pn_gid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_groups`
--

INSERT INTO `z_groups` VALUES (1,'Users',0,'By default, all users are made members of this group.','usr',0,0,0,0,0);
INSERT INTO `z_groups` VALUES (2,'Administrators',0,'By default, all administrators are made members of this group.','adm',0,0,0,0,0);

--
-- Table structure for table `z_hooks`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_hooks` (
  `pn_id` bigint(20) NOT NULL,
  `pn_object` varchar(64) NOT NULL,
  `pn_action` varchar(64) NOT NULL,
  `pn_smodule` varchar(64) NOT NULL,
  `pn_stype` varchar(64) NOT NULL,
  `pn_tarea` varchar(64) NOT NULL,
  `pn_tmodule` varchar(64) NOT NULL,
  `pn_ttype` varchar(64) NOT NULL,
  `pn_tfunc` varchar(64) NOT NULL,
  `pn_sequence` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pn_id`),
  KEY `smodule` (`pn_smodule`),
  KEY `smodule_tmodule` (`pn_smodule`,`pn_tmodule`)
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
  `pn_id` bigint(20) NOT NULL,
  `pn_modid` bigint(20) NOT NULL DEFAULT '0',
  `pn_modname` varchar(64) NOT NULL,
  `pn_minversion` varchar(10) NOT NULL,
  `pn_maxversion` varchar(10) NOT NULL,
  `pn_status` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pn_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_module_deps`
--


--
-- Table structure for table `z_module_vars`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_module_vars` (
  `pn_id` bigint(20) NOT NULL,
  `pn_modname` varchar(64) NOT NULL,
  `pn_name` varchar(64) NOT NULL,
  `pn_value` longtext,
  PRIMARY KEY (`pn_id`),
  KEY `mod_var` (`pn_modname`,`pn_name`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_module_vars`
--

INSERT INTO `z_module_vars` VALUES (1,'Modules','itemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (2,'/PNConfig','debug','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (3,'/PNConfig','sitename','s:9:\"Site name\";');
INSERT INTO `z_module_vars` VALUES (4,'/PNConfig','slogan','s:16:\"Site description\";');
INSERT INTO `z_module_vars` VALUES (5,'/PNConfig','metakeywords','s:253:\"zikula, community, portal, portal web, open source, gpl, web site, website, weblog, blog, content management, content management system, web content management, web content management system, enterprise web content management, cms, application framework\";');
INSERT INTO `z_module_vars` VALUES (6,'/PNConfig','startdate','s:7:\"06/2010\";');
INSERT INTO `z_module_vars` VALUES (7,'/PNConfig','adminmail','s:19:\"example@example.com\";');
INSERT INTO `z_module_vars` VALUES (8,'/PNConfig','Default_Theme','s:9:\"andreas08\";');
INSERT INTO `z_module_vars` VALUES (9,'/PNConfig','anonymous','s:5:\"Guest\";');
INSERT INTO `z_module_vars` VALUES (10,'/PNConfig','timezone_offset','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (11,'/PNConfig','timezone_server','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (12,'/PNConfig','funtext','s:1:\"1\";');
INSERT INTO `z_module_vars` VALUES (13,'/PNConfig','reportlevel','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (14,'/PNConfig','startpage','s:4:\"Tour\";');
INSERT INTO `z_module_vars` VALUES (15,'/PNConfig','Version_Num','s:9:\"1.3.0-dev\";');
INSERT INTO `z_module_vars` VALUES (16,'/PNConfig','Version_ID','s:6:\"Zikula\";');
INSERT INTO `z_module_vars` VALUES (17,'/PNConfig','Version_Sub','s:3:\"vai\";');
INSERT INTO `z_module_vars` VALUES (18,'/PNConfig','debug_sql','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (19,'/PNConfig','multilingual','s:1:\"1\";');
INSERT INTO `z_module_vars` VALUES (20,'/PNConfig','useflags','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (21,'/PNConfig','theme_change','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (22,'/PNConfig','UseCompression','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (23,'/PNConfig','errordisplay','i:1;');
INSERT INTO `z_module_vars` VALUES (24,'/PNConfig','errorlog','i:0;');
INSERT INTO `z_module_vars` VALUES (25,'/PNConfig','errorlogtype','i:0;');
INSERT INTO `z_module_vars` VALUES (26,'/PNConfig','errormailto','s:14:\"me@example.com\";');
INSERT INTO `z_module_vars` VALUES (27,'/PNConfig','siteoff','i:0;');
INSERT INTO `z_module_vars` VALUES (28,'/PNConfig','siteoffreason','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (29,'/PNConfig','starttype','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (30,'/PNConfig','startfunc','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (31,'/PNConfig','startargs','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (32,'/PNConfig','entrypoint','s:9:\"index.php\";');
INSERT INTO `z_module_vars` VALUES (33,'/PNConfig','language_detect','i:0;');
INSERT INTO `z_module_vars` VALUES (34,'/PNConfig','shorturls','b:0;');
INSERT INTO `z_module_vars` VALUES (35,'/PNConfig','shorturlstype','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (36,'/PNConfig','shorturlsext','s:4:\"html\";');
INSERT INTO `z_module_vars` VALUES (37,'/PNConfig','shorturlsseparator','s:1:\"-\";');
INSERT INTO `z_module_vars` VALUES (38,'/PNConfig','shorturlsstripentrypoint','b:0;');
INSERT INTO `z_module_vars` VALUES (39,'/PNConfig','shorturlsdefaultmodule','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (40,'/PNConfig','profilemodule','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (41,'/PNConfig','messagemodule','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (42,'/PNConfig','languageurl','i:0;');
INSERT INTO `z_module_vars` VALUES (43,'/PNConfig','ajaxtimeout','i:5000;');
INSERT INTO `z_module_vars` VALUES (44,'/PNConfig','permasearch','s:161:\"À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü\";');
INSERT INTO `z_module_vars` VALUES (45,'/PNConfig','permareplace','s:107:\"A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,s,a,A,o,O,u,U\";');
INSERT INTO `z_module_vars` VALUES (46,'/PNConfig','language','s:3:\"eng\";');
INSERT INTO `z_module_vars` VALUES (47,'/PNConfig','locale','s:2:\"en\";');
INSERT INTO `z_module_vars` VALUES (48,'/PNConfig','language_i18n','s:2:\"en\";');
INSERT INTO `z_module_vars` VALUES (49,'/PNConfig','language_bc','i:1;');
INSERT INTO `z_module_vars` VALUES (50,'Theme','modulesnocache','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (51,'Theme','enablecache','b:0;');
INSERT INTO `z_module_vars` VALUES (52,'Theme','compile_check','b:1;');
INSERT INTO `z_module_vars` VALUES (53,'Theme','cache_lifetime','i:3600;');
INSERT INTO `z_module_vars` VALUES (54,'Theme','force_compile','b:0;');
INSERT INTO `z_module_vars` VALUES (55,'Theme','trimwhitespace','b:0;');
INSERT INTO `z_module_vars` VALUES (56,'Theme','maxsizeforlinks','i:30;');
INSERT INTO `z_module_vars` VALUES (57,'Theme','itemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (58,'Theme','cssjscombine','b:0;');
INSERT INTO `z_module_vars` VALUES (59,'Theme','cssjscompress','b:0;');
INSERT INTO `z_module_vars` VALUES (60,'Theme','cssjsminify','b:0;');
INSERT INTO `z_module_vars` VALUES (61,'Theme','cssjscombine_lifetime','i:3600;');
INSERT INTO `z_module_vars` VALUES (62,'Theme','render_compile_check','b:1;');
INSERT INTO `z_module_vars` VALUES (63,'Theme','render_force_compile','b:1;');
INSERT INTO `z_module_vars` VALUES (64,'Theme','render_cache','b:0;');
INSERT INTO `z_module_vars` VALUES (65,'Theme','render_expose_template','b:0;');
INSERT INTO `z_module_vars` VALUES (66,'Theme','render_lifetime','i:3600;');
INSERT INTO `z_module_vars` VALUES (67,'Admin','modulesperrow','i:3;');
INSERT INTO `z_module_vars` VALUES (68,'Admin','itemsperpage','i:15;');
INSERT INTO `z_module_vars` VALUES (69,'Admin','defaultcategory','i:5;');
INSERT INTO `z_module_vars` VALUES (70,'Admin','modulestylesheet','s:11:\"navtabs.css\";');
INSERT INTO `z_module_vars` VALUES (71,'Admin','admingraphic','i:1;');
INSERT INTO `z_module_vars` VALUES (72,'Admin','startcategory','i:1;');
INSERT INTO `z_module_vars` VALUES (73,'Admin','ignoreinstallercheck','i:0;');
INSERT INTO `z_module_vars` VALUES (74,'Admin','admintheme','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (75,'Admin','displaynametype','i:1;');
INSERT INTO `z_module_vars` VALUES (76,'Admin','moduledescription','i:1;');
INSERT INTO `z_module_vars` VALUES (77,'Permissions','filter','i:1;');
INSERT INTO `z_module_vars` VALUES (78,'Permissions','warnbar','i:1;');
INSERT INTO `z_module_vars` VALUES (79,'Permissions','rowview','i:20;');
INSERT INTO `z_module_vars` VALUES (80,'Permissions','rowedit','i:20;');
INSERT INTO `z_module_vars` VALUES (81,'Permissions','lockadmin','i:1;');
INSERT INTO `z_module_vars` VALUES (82,'Permissions','adminid','i:1;');
INSERT INTO `z_module_vars` VALUES (83,'Groups','itemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (84,'Groups','defaultgroup','i:1;');
INSERT INTO `z_module_vars` VALUES (85,'Groups','mailwarning','i:0;');
INSERT INTO `z_module_vars` VALUES (86,'Groups','hideclosed','i:0;');
INSERT INTO `z_module_vars` VALUES (87,'Blocks','collapseable','i:0;');
INSERT INTO `z_module_vars` VALUES (88,'Users','itemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (89,'Users','accountdisplaygraphics','i:1;');
INSERT INTO `z_module_vars` VALUES (90,'Users','accountitemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (91,'Users','accountitemsperrow','i:5;');
INSERT INTO `z_module_vars` VALUES (92,'Users','changepassword','i:1;');
INSERT INTO `z_module_vars` VALUES (93,'Users','changeemail','i:1;');
INSERT INTO `z_module_vars` VALUES (94,'Users','reg_allowreg','i:1;');
INSERT INTO `z_module_vars` VALUES (95,'Users','reg_verifyemail','i:1;');
INSERT INTO `z_module_vars` VALUES (96,'Users','reg_Illegalusername','s:87:\"root adm linux webmaster admin god administrator administrador nobody anonymous anonimo\";');
INSERT INTO `z_module_vars` VALUES (97,'Users','reg_Illegaldomains','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (98,'Users','reg_Illegaluseragents','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (99,'Users','reg_noregreasons','s:51:\"Sorry! New user registration is currently disabled.\";');
INSERT INTO `z_module_vars` VALUES (100,'Users','reg_uniemail','i:1;');
INSERT INTO `z_module_vars` VALUES (101,'Users','reg_notifyemail','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (102,'Users','reg_optitems','i:0;');
INSERT INTO `z_module_vars` VALUES (103,'Users','userimg','s:11:\"images/menu\";');
INSERT INTO `z_module_vars` VALUES (104,'Users','avatarpath','s:13:\"images/avatar\";');
INSERT INTO `z_module_vars` VALUES (105,'Users','allowgravatars','i:1;');
INSERT INTO `z_module_vars` VALUES (106,'Users','gravatarimage','s:12:\"gravatar.gif\";');
INSERT INTO `z_module_vars` VALUES (107,'Users','minage','i:13;');
INSERT INTO `z_module_vars` VALUES (108,'Users','minpass','i:5;');
INSERT INTO `z_module_vars` VALUES (109,'Users','anonymous','s:5:\"Guest\";');
INSERT INTO `z_module_vars` VALUES (110,'Users','loginviaoption','i:0;');
INSERT INTO `z_module_vars` VALUES (111,'Users','moderation','i:0;');
INSERT INTO `z_module_vars` VALUES (112,'Users','hash_method','s:6:\"sha256\";');
INSERT INTO `z_module_vars` VALUES (113,'Users','login_redirect','i:1;');
INSERT INTO `z_module_vars` VALUES (114,'Users','reg_question','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (115,'Users','reg_answer','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (116,'Users','use_password_strength_meter','i:0;');
INSERT INTO `z_module_vars` VALUES (117,'Users','default_authmodule','s:5:\"Users\";');
INSERT INTO `z_module_vars` VALUES (118,'Users','moderation_order','i:0;');
INSERT INTO `z_module_vars` VALUES (119,'SecurityCenter','itemsperpage','i:10;');
INSERT INTO `z_module_vars` VALUES (120,'/PNConfig','enableanticracker','i:1;');
INSERT INTO `z_module_vars` VALUES (121,'/PNConfig','emailhackattempt','i:1;');
INSERT INTO `z_module_vars` VALUES (122,'/PNConfig','loghackattempttodb','i:1;');
INSERT INTO `z_module_vars` VALUES (123,'/PNConfig','onlysendsummarybyemail','i:1;');
INSERT INTO `z_module_vars` VALUES (124,'/PNConfig','updatecheck','i:1;');
INSERT INTO `z_module_vars` VALUES (125,'/PNConfig','updatefrequency','i:7;');
INSERT INTO `z_module_vars` VALUES (126,'/PNConfig','updatelastchecked','i:1277436764;');
INSERT INTO `z_module_vars` VALUES (127,'/PNConfig','updateversion','s:5:\"1.2.3\";');
INSERT INTO `z_module_vars` VALUES (128,'/PNConfig','keyexpiry','i:0;');
INSERT INTO `z_module_vars` VALUES (129,'/PNConfig','sessionauthkeyua','b:0;');
INSERT INTO `z_module_vars` VALUES (130,'/PNConfig','secure_domain','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (131,'/PNConfig','signcookies','i:1;');
INSERT INTO `z_module_vars` VALUES (132,'/PNConfig','signingkey','s:40:\"e917c596e59ab22375a1651627f6470924d88ede\";');
INSERT INTO `z_module_vars` VALUES (133,'/PNConfig','seclevel','s:6:\"Medium\";');
INSERT INTO `z_module_vars` VALUES (134,'/PNConfig','secmeddays','i:7;');
INSERT INTO `z_module_vars` VALUES (135,'/PNConfig','secinactivemins','i:20;');
INSERT INTO `z_module_vars` VALUES (136,'/PNConfig','sessionstoretofile','i:0;');
INSERT INTO `z_module_vars` VALUES (137,'/PNConfig','sessionsavepath','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (138,'/PNConfig','gc_probability','i:100;');
INSERT INTO `z_module_vars` VALUES (139,'/PNConfig','anonymoussessions','i:1;');
INSERT INTO `z_module_vars` VALUES (140,'/PNConfig','sessionrandregenerate','b:1;');
INSERT INTO `z_module_vars` VALUES (141,'/PNConfig','sessionregenerate','b:1;');
INSERT INTO `z_module_vars` VALUES (142,'/PNConfig','sessionregeneratefreq','i:10;');
INSERT INTO `z_module_vars` VALUES (143,'/PNConfig','sessionipcheck','i:0;');
INSERT INTO `z_module_vars` VALUES (144,'/PNConfig','sessionname','s:4:\"ZSID\";');
INSERT INTO `z_module_vars` VALUES (145,'/PNConfig','filtergetvars','i:1;');
INSERT INTO `z_module_vars` VALUES (146,'/PNConfig','filterpostvars','i:1;');
INSERT INTO `z_module_vars` VALUES (147,'/PNConfig','filtercookievars','i:1;');
INSERT INTO `z_module_vars` VALUES (148,'/PNConfig','outputfilter','i:1;');
INSERT INTO `z_module_vars` VALUES (149,'/PNConfig','htmlpurifierlocation','s:46:\"system/SecurityCenter/lib/vendor/htmlpurifier/\";');
INSERT INTO `z_module_vars` VALUES (150,'SecurityCenter','htmlpurifierConfig','s:3696:\"a:10:{s:4:\"Attr\";a:15:{s:14:\"AllowedClasses\";N;s:19:\"AllowedFrameTargets\";a:0:{}s:10:\"AllowedRel\";a:2:{s:8:\"nofollow\";i:1;s:11:\"imageviewer\";i:1;}s:10:\"AllowedRev\";a:0:{}s:13:\"ClassUseCDATA\";N;s:15:\"DefaultImageAlt\";N;s:19:\"DefaultInvalidImage\";s:0:\"\";s:22:\"DefaultInvalidImageAlt\";s:13:\"Invalid image\";s:14:\"DefaultTextDir\";s:3:\"ltr\";s:8:\"EnableID\";b:0;s:16:\"ForbiddenClasses\";a:0:{}s:11:\"IDBlacklist\";a:0:{}s:17:\"IDBlacklistRegexp\";N;s:8:\"IDPrefix\";s:0:\"\";s:13:\"IDPrefixLocal\";s:0:\"\";}s:10:\"AutoFormat\";a:10:{s:13:\"AutoParagraph\";b:0;s:6:\"Custom\";a:0:{}s:14:\"DisplayLinkURI\";b:0;s:7:\"Linkify\";b:0;s:22:\"PurifierLinkify.DocURL\";s:3:\"#%s\";s:15:\"PurifierLinkify\";b:0;s:33:\"RemoveEmpty.RemoveNbsp.Exceptions\";a:2:{s:2:\"td\";b:1;s:2:\"th\";b:1;}s:22:\"RemoveEmpty.RemoveNbsp\";b:0;s:11:\"RemoveEmpty\";b:0;s:28:\"RemoveSpansWithoutAttributes\";b:0;}s:3:\"CSS\";a:6:{s:14:\"AllowImportant\";b:0;s:11:\"AllowTricky\";b:0;s:17:\"AllowedProperties\";N;s:13:\"DefinitionRev\";i:1;s:12:\"MaxImgLength\";s:6:\"1200px\";s:11:\"Proprietary\";b:0;}s:5:\"Cache\";a:2:{s:14:\"DefinitionImpl\";s:10:\"Serializer\";s:14:\"SerializerPath\";s:19:\"ztemp/purifierCache\";}s:4:\"Core\";a:15:{s:17:\"AggressivelyFixLt\";b:1;s:13:\"CollectErrors\";b:0;s:13:\"ColorKeywords\";a:17:{s:6:\"maroon\";s:7:\"#800000\";s:3:\"red\";s:7:\"#FF0000\";s:6:\"orange\";s:7:\"#FFA500\";s:6:\"yellow\";s:7:\"#FFFF00\";s:5:\"olive\";s:7:\"#808000\";s:6:\"purple\";s:7:\"#800080\";s:7:\"fuchsia\";s:7:\"#FF00FF\";s:5:\"white\";s:7:\"#FFFFFF\";s:4:\"lime\";s:7:\"#00FF00\";s:5:\"green\";s:7:\"#008000\";s:4:\"navy\";s:7:\"#000080\";s:4:\"blue\";s:7:\"#0000FF\";s:4:\"aqua\";s:7:\"#00FFFF\";s:4:\"teal\";s:7:\"#008080\";s:5:\"black\";s:7:\"#000000\";s:6:\"silver\";s:7:\"#C0C0C0\";s:4:\"gray\";s:7:\"#808080\";}s:25:\"ConvertDocumentToFragment\";b:1;s:31:\"DirectLexLineNumberSyncInterval\";i:0;s:8:\"Encoding\";s:5:\"utf-8\";s:21:\"EscapeInvalidChildren\";b:0;s:17:\"EscapeInvalidTags\";b:0;s:24:\"EscapeNonASCIICharacters\";b:0;s:14:\"HiddenElements\";a:2:{s:6:\"script\";b:1;s:5:\"style\";b:1;}s:8:\"Language\";s:2:\"en\";s:9:\"LexerImpl\";N;s:19:\"MaintainLineNumbers\";N;s:16:\"RemoveInvalidImg\";b:1;s:20:\"RemoveScriptContents\";N;}s:6:\"Filter\";a:6:{s:6:\"Custom\";a:0:{}s:27:\"ExtractStyleBlocks.Escaping\";b:1;s:24:\"ExtractStyleBlocks.Scope\";N;s:27:\"ExtractStyleBlocks.TidyImpl\";N;s:18:\"ExtractStyleBlocks\";b:0;s:7:\"YouTube\";b:0;}s:4:\"HTML\";a:24:{s:7:\"Allowed\";N;s:17:\"AllowedAttributes\";N;s:15:\"AllowedElements\";N;s:14:\"AllowedModules\";N;s:18:\"Attr.Name.UseCDATA\";b:0;s:12:\"BlockWrapper\";s:1:\"p\";s:11:\"CoreModules\";a:7:{s:9:\"Structure\";b:1;s:4:\"Text\";b:1;s:9:\"Hypertext\";b:1;s:4:\"List\";b:1;s:22:\"NonXMLCommonAttributes\";b:1;s:19:\"XMLCommonAttributes\";b:1;s:16:\"CommonAttributes\";b:1;}s:13:\"CustomDoctype\";N;s:12:\"DefinitionID\";N;s:13:\"DefinitionRev\";i:1;s:7:\"Doctype\";s:22:\"HTML 4.01 Transitional\";s:19:\"ForbiddenAttributes\";a:0:{}s:17:\"ForbiddenElements\";a:0:{}s:12:\"MaxImgLength\";i:1200;s:6:\"Parent\";s:3:\"div\";s:11:\"Proprietary\";b:0;s:9:\"SafeEmbed\";b:0;s:10:\"SafeObject\";b:0;s:6:\"Strict\";b:0;s:7:\"TidyAdd\";a:0:{}s:9:\"TidyLevel\";s:6:\"medium\";s:10:\"TidyRemove\";a:0:{}s:7:\"Trusted\";b:0;s:5:\"XHTML\";b:1;}s:6:\"Output\";a:5:{s:21:\"CommentScriptContents\";b:1;s:11:\"FlashCompat\";b:0;s:7:\"Newline\";N;s:8:\"SortAttr\";b:0;s:10:\"TidyFormat\";b:0;}s:4:\"Test\";a:1:{s:12:\"ForceNoIconv\";b:0;}s:3:\"URI\";a:16:{s:14:\"AllowedSchemes\";a:6:{s:4:\"http\";b:1;s:5:\"https\";b:1;s:6:\"mailto\";b:1;s:3:\"ftp\";b:1;s:4:\"nntp\";b:1;s:4:\"news\";b:1;}s:4:\"Base\";N;s:13:\"DefaultScheme\";s:4:\"http\";s:12:\"DefinitionID\";N;s:13:\"DefinitionRev\";i:1;s:7:\"Disable\";b:0;s:15:\"DisableExternal\";b:0;s:24:\"DisableExternalResources\";b:0;s:16:\"DisableResources\";b:0;s:4:\"Host\";N;s:13:\"HostBlacklist\";a:0:{}s:12:\"MakeAbsolute\";b:0;s:5:\"Munge\";N;s:14:\"MungeResources\";b:0;s:14:\"MungeSecretKey\";N;s:22:\"OverrideAllowedSchemes\";b:1;}}\";');
INSERT INTO `z_module_vars` VALUES (151,'/PNConfig','useids','i:0;');
INSERT INTO `z_module_vars` VALUES (152,'/PNConfig','idssoftblock','i:1;');
INSERT INTO `z_module_vars` VALUES (153,'/PNConfig','idsfilter','s:3:\"xml\";');
INSERT INTO `z_module_vars` VALUES (154,'/PNConfig','idsimpactthresholdone','i:1;');
INSERT INTO `z_module_vars` VALUES (155,'/PNConfig','idsimpactthresholdtwo','i:10;');
INSERT INTO `z_module_vars` VALUES (156,'/PNConfig','idsimpactthresholdthree','i:25;');
INSERT INTO `z_module_vars` VALUES (157,'/PNConfig','idsimpactthresholdfour','i:75;');
INSERT INTO `z_module_vars` VALUES (158,'/PNConfig','idsimpactmode','i:1;');
INSERT INTO `z_module_vars` VALUES (159,'/PNConfig','idshtmlfields','a:1:{i:0;s:14:\"POST.__wysiwyg\";}');
INSERT INTO `z_module_vars` VALUES (160,'/PNConfig','idsjsonfields','a:1:{i:0;s:15:\"POST.__jsondata\";}');
INSERT INTO `z_module_vars` VALUES (161,'/PNConfig','idsexceptions','a:12:{i:0;s:10:\"GET.__utmz\";i:1;s:10:\"GET.__utmc\";i:2;s:18:\"REQUEST.linksorder\";i:3;s:15:\"POST.linksorder\";i:4;s:19:\"REQUEST.fullcontent\";i:5;s:16:\"POST.fullcontent\";i:6;s:22:\"REQUEST.summarycontent\";i:7;s:19:\"POST.summarycontent\";i:8;s:19:\"REQUEST.filter.page\";i:9;s:16:\"POST.filter.page\";i:10;s:20:\"REQUEST.filter.value\";i:11;s:17:\"POST.filter.value\";}');
INSERT INTO `z_module_vars` VALUES (162,'/PNConfig','summarycontent','s:1130:\"For the attention of %sitename% administration staff:\n\nOn %date% at %time%, Zikula detected that somebody tried to interact with the site in a way that may have been intended compromise its security. This is not necessarily the case: it could have been caused by work you were doing on the site, or may have been due to some other reason. In any case, it was detected and blocked. \n\nThe suspicious activity was recognised in \'%filename%\' at line %linenumber%.\n\nType: %type%. \n\nAdditional information: %additionalinfo%.\n\nBelow is logged information that may help you identify what happened and who was responsible.\n\n=====================================\nInformation about the user:\n=====================================\nUser name:  %username%\nUser\'s e-mail address: %useremail%\nUser\'s real name: %userrealname%\n\n=====================================\nIP numbers (if this was a cracker, the IP numbers may not be the true point of origin)\n=====================================\nIP according to HTTP_CLIENT_IP: %httpclientip%\nIP according to REMOTE_ADDR: %remoteaddr%\nIP according to GetHostByName($REMOTE_ADDR): %gethostbyremoteaddr%\n\";');
INSERT INTO `z_module_vars` VALUES (163,'/PNConfig','fullcontent','s:1289:\"=====================================\nInformation in the $_REQUEST array\n=====================================\n%requestarray%\n\n=====================================\nInformation in the $_GET array\n(variables that may have been in the URL string or in a \'GET\'-type form)\n=====================================\n%getarray%\n\n=====================================\nInformation in the $_POST array\n(visible and invisible form elements)\n=====================================\n%postarray%\n\n=====================================\nBrowser information\n=====================================\n%browserinfo%\n\n=====================================\nInformation in the $_SERVER array\n=====================================\n%serverarray%\n\n=====================================\nInformation in the $_ENV array\n=====================================\n%envarray%\n\n=====================================\nInformation in the $_COOKIE array\n=====================================\n%cookiearray%\n\n=====================================\nInformation in the $_FILES array\n=====================================\n%filearray%\n\n=====================================\nInformation in the $_SESSION array\n(session information -- variables starting with PNSV are Zikula session variables)\n=====================================\n%sessionarray%\n\";');
INSERT INTO `z_module_vars` VALUES (164,'/PNConfig','usehtaccessbans','i:0;');
INSERT INTO `z_module_vars` VALUES (165,'/PNConfig','extrapostprotection','i:0;');
INSERT INTO `z_module_vars` VALUES (166,'/PNConfig','extragetprotection','i:0;');
INSERT INTO `z_module_vars` VALUES (167,'/PNConfig','checkmultipost','i:0;');
INSERT INTO `z_module_vars` VALUES (168,'/PNConfig','maxmultipost','i:4;');
INSERT INTO `z_module_vars` VALUES (169,'/PNConfig','cpuloadmonitor','i:0;');
INSERT INTO `z_module_vars` VALUES (170,'/PNConfig','cpumaxload','d:10;');
INSERT INTO `z_module_vars` VALUES (171,'/PNConfig','ccisessionpath','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (172,'/PNConfig','htaccessfilelocation','s:9:\".htaccess\";');
INSERT INTO `z_module_vars` VALUES (173,'/PNConfig','nocookiebanthreshold','i:10;');
INSERT INTO `z_module_vars` VALUES (174,'/PNConfig','nocookiewarningthreshold','i:2;');
INSERT INTO `z_module_vars` VALUES (175,'/PNConfig','fastaccessbanthreshold','i:40;');
INSERT INTO `z_module_vars` VALUES (176,'/PNConfig','fastaccesswarnthreshold','i:10;');
INSERT INTO `z_module_vars` VALUES (177,'/PNConfig','javababble','i:0;');
INSERT INTO `z_module_vars` VALUES (178,'/PNConfig','javaencrypt','i:0;');
INSERT INTO `z_module_vars` VALUES (179,'/PNConfig','preservehead','i:0;');
INSERT INTO `z_module_vars` VALUES (180,'/PNConfig','filterarrays','i:1;');
INSERT INTO `z_module_vars` VALUES (181,'/PNConfig','htmlentities','s:1:\"1\";');
INSERT INTO `z_module_vars` VALUES (182,'/PNConfig','AllowableHTML','a:103:{s:3:\"!--\";i:2;s:1:\"a\";i:2;s:4:\"abbr\";i:0;s:7:\"acronym\";i:0;s:7:\"address\";i:0;s:6:\"applet\";i:0;s:4:\"area\";i:0;s:7:\"article\";i:0;s:5:\"aside\";i:0;s:5:\"audio\";i:0;s:1:\"b\";i:2;s:4:\"base\";i:0;s:8:\"basefont\";i:0;s:3:\"bdo\";i:0;s:3:\"big\";i:0;s:10:\"blockquote\";i:2;s:2:\"br\";i:2;s:6:\"button\";i:0;s:6:\"canvas\";i:0;s:7:\"caption\";i:0;s:6:\"center\";i:2;s:4:\"cite\";i:0;s:4:\"code\";i:0;s:3:\"col\";i:0;s:8:\"colgroup\";i:0;s:7:\"command\";i:0;s:8:\"datalist\";i:0;s:2:\"dd\";i:1;s:3:\"del\";i:0;s:7:\"details\";i:0;s:3:\"dfn\";i:0;s:3:\"dir\";i:0;s:3:\"div\";i:2;s:2:\"dl\";i:1;s:2:\"dt\";i:1;s:2:\"em\";i:2;s:5:\"embed\";i:0;s:8:\"fieldset\";i:0;s:10:\"figcaption\";i:0;s:6:\"figure\";i:0;s:4:\"font\";i:0;s:4:\"form\";i:0;s:2:\"h1\";i:1;s:2:\"h2\";i:1;s:2:\"h3\";i:1;s:2:\"h4\";i:1;s:2:\"h5\";i:1;s:2:\"h6\";i:1;s:6:\"hgroup\";i:0;s:2:\"hr\";i:2;s:1:\"i\";i:2;s:6:\"iframe\";i:0;s:3:\"img\";i:0;s:5:\"input\";i:0;s:3:\"ins\";i:0;s:6:\"keygen\";i:0;s:3:\"kbd\";i:0;s:5:\"label\";i:0;s:6:\"legend\";i:0;s:2:\"li\";i:2;s:3:\"map\";i:0;s:4:\"mark\";i:0;s:4:\"menu\";i:0;s:7:\"marquee\";i:0;s:5:\"meter\";i:0;s:3:\"nav\";i:0;s:4:\"nobr\";i:0;s:6:\"object\";i:0;s:2:\"ol\";i:2;s:8:\"optgroup\";i:0;s:6:\"option\";i:0;s:1:\"p\";i:2;s:5:\"param\";i:0;s:3:\"pre\";i:2;s:8:\"progress\";i:0;s:1:\"q\";i:0;s:1:\"s\";i:0;s:4:\"samp\";i:0;s:6:\"script\";i:0;s:7:\"section\";i:0;s:6:\"select\";i:0;s:5:\"small\";i:0;s:6:\"source\";i:0;s:4:\"span\";i:0;s:6:\"strike\";i:0;s:6:\"strong\";i:2;s:3:\"sub\";i:0;s:7:\"summary\";i:0;s:3:\"sup\";i:0;s:5:\"table\";i:2;s:5:\"tbody\";i:0;s:2:\"td\";i:2;s:8:\"textarea\";i:0;s:5:\"tfoot\";i:0;s:2:\"th\";i:2;s:5:\"thead\";i:0;s:4:\"time\";i:0;s:2:\"tr\";i:2;s:2:\"tt\";i:2;s:1:\"u\";i:0;s:2:\"ul\";i:2;s:3:\"var\";i:0;s:5:\"video\";i:0;}');
INSERT INTO `z_module_vars` VALUES (183,'Categories','userrootcat','s:17:\"/__SYSTEM__/Users\";');
INSERT INTO `z_module_vars` VALUES (184,'Categories','allowusercatedit','i:0;');
INSERT INTO `z_module_vars` VALUES (185,'Categories','autocreateusercat','i:0;');
INSERT INTO `z_module_vars` VALUES (186,'Categories','autocreateuserdefaultcat','i:0;');
INSERT INTO `z_module_vars` VALUES (187,'Categories','userdefaultcatname','s:7:\"Default\";');
INSERT INTO `z_module_vars` VALUES (188,'Mailer','mailertype','i:1;');
INSERT INTO `z_module_vars` VALUES (189,'Mailer','charset','s:5:\"utf-8\";');
INSERT INTO `z_module_vars` VALUES (190,'Mailer','encoding','s:4:\"8bit\";');
INSERT INTO `z_module_vars` VALUES (191,'Mailer','html','b:0;');
INSERT INTO `z_module_vars` VALUES (192,'Mailer','wordwrap','i:50;');
INSERT INTO `z_module_vars` VALUES (193,'Mailer','msmailheaders','b:0;');
INSERT INTO `z_module_vars` VALUES (194,'Mailer','sendmailpath','s:18:\"/usr/sbin/sendmail\";');
INSERT INTO `z_module_vars` VALUES (195,'Mailer','smtpauth','b:0;');
INSERT INTO `z_module_vars` VALUES (196,'Mailer','smtpserver','s:9:\"localhost\";');
INSERT INTO `z_module_vars` VALUES (197,'Mailer','smtpport','i:25;');
INSERT INTO `z_module_vars` VALUES (198,'Mailer','smtptimeout','i:10;');
INSERT INTO `z_module_vars` VALUES (199,'Mailer','smtpusername','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (200,'Mailer','smtppassword','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (201,'Search','itemsperpage','i:10;');
INSERT INTO `z_module_vars` VALUES (202,'Search','limitsummary','i:255;');
INSERT INTO `z_module_vars` VALUES (203,'/PNConfig','log_last_rotate','i:1277436762;');

--
-- Table structure for table `z_modules`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_modules` (
  `pn_id` bigint(20) NOT NULL,
  `pn_name` varchar(64) NOT NULL,
  `pn_type` bigint(20) NOT NULL DEFAULT '0',
  `pn_displayname` varchar(64) NOT NULL,
  `pn_url` varchar(64) NOT NULL,
  `pn_description` varchar(255) NOT NULL,
  `pn_regid` bigint(20) NOT NULL DEFAULT '0',
  `pn_directory` varchar(64) NOT NULL,
  `pn_version` varchar(10) NOT NULL DEFAULT '0',
  `pn_official` bigint(20) NOT NULL DEFAULT '0',
  `pn_author` varchar(255) NOT NULL,
  `pn_contact` varchar(255) NOT NULL,
  `pn_admin_capable` bigint(20) NOT NULL DEFAULT '0',
  `pn_user_capable` bigint(20) NOT NULL DEFAULT '0',
  `pn_profile_capable` bigint(20) NOT NULL DEFAULT '0',
  `pn_message_capable` bigint(20) NOT NULL DEFAULT '0',
  `pn_state` bigint(20) NOT NULL DEFAULT '0',
  `pn_credits` varchar(255) NOT NULL,
  `pn_changelog` varchar(255) NOT NULL,
  `pn_help` varchar(255) NOT NULL,
  `pn_license` varchar(255) NOT NULL,
  `pn_securityschema` longtext NOT NULL,
  PRIMARY KEY (`pn_id`),
  KEY `state` (`pn_state`),
  KEY `mod_state` (`pn_name`,`pn_state`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_modules`
--

INSERT INTO `z_modules` VALUES (1,'Modules',3,'Modules manager','modules','Provides support for modules, and incorporates an interface for adding, removing and administering core system modules and add-on modules.',0,'Modules','3.7.1',1,'Jim McDonald, Mark West','http://www.zikula.org',1,0,0,0,3,'','','','','a:1:{s:9:\"Modules::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (2,'Admin',3,'Administration panel','adminpanel','Provides the site\'s administration panel, and the ability to configure and manage it.',0,'Admin','1.8',1,'Mark West','http://www.markwest.me.uk/',1,0,0,0,3,'','','','','a:1:{s:7:\"Admin::\";s:38:\"Admin Category name::Admin Category ID\";}');
INSERT INTO `z_modules` VALUES (3,'Blocks',3,'Blocks manager','blocks','Provides an interface for adding, removing and administering the site\'s side and center blocks.',0,'Blocks','3.7',1,'Jim McDonald, Mark West','http://www.mcdee.net/, http://www.markwest.me.uk/',1,1,0,0,3,'','','','','a:2:{s:8:\"Blocks::\";s:30:\"Block key:Block title:Block ID\";s:16:\"Blocks::position\";s:26:\"Position name::Position ID\";}');
INSERT INTO `z_modules` VALUES (4,'Categories',3,'Categories manager','categories','Provides support for categorisation of content in other modules, and an interface for adding, removing and administering categories.',0,'Categories','1.2',1,'Robert Gasch','rgasch@gmail.com',1,1,0,0,3,'','','','','a:1:{s:20:\"Categories::Category\";s:40:\"Category ID:Category Path:Category IPath\";}');
INSERT INTO `z_modules` VALUES (5,'Errors',3,'Errors','errors','Provides the core system of the site with error-logging capability.',0,'Errors','1.1',1,'Brian Lindner <Furbo>','furbo@sigtauonline.com',0,1,0,0,3,'','','','','a:1:{s:8:\"Errors::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (6,'Groups',3,'Groups manager','groups','Provides support for user groups, and incorporates an interface for adding, removing and administering them.',0,'Groups','2.3',1,'Mark West, Franky Chestnut, Michael Halbook','http://www.markwest.me.uk/, http://dev.pnconcept.com, http://www.halbrooktech.com',1,1,0,0,3,'','','','','a:1:{s:8:\"Groups::\";s:10:\"Group ID::\";}');
INSERT INTO `z_modules` VALUES (7,'Mailer',3,'Mailer','mailer','Provides mail-sending functionality for communication with the site\'s users, and an interface for managing the e-mail service settings used by the mailer.',0,'Mailer','1.3',1,'Mark West','http://www.markwest.me.uk/',1,0,0,0,3,'','','','','a:1:{s:8:\"Mailer::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (8,'PageLock',3,'Page lock','pagelock','Provides the ability to lock pages when they are in use, for content and access control.',0,'PageLock','1.1',1,'Jorn Wildt','http://www.elfisk.dk',0,0,0,0,3,'','','','','a:1:{s:10:\"PageLock::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (9,'Permissions',3,'Permission manager','permissions','Provides an interface for fine-grained management of accessibility of the site\'s functionality and content through permission rules.',0,'Permissions','1.1',1,'Jim McDonald, M.Maes','http://www.mcdee.net/, http://www.mmaes.com',1,0,0,0,3,'','','','','a:1:{s:13:\"Permissions::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (10,'Search',3,'Site search engine','search','Provides an engine for searching within the site, and an interface for managing search page settings.',0,'Search','1.5',1,'Patrick Kellum','http://www.ctarl-ctarl.com',1,1,0,0,3,'','','','','a:1:{s:8:\"Search::\";s:13:\"Module name::\";}');
INSERT INTO `z_modules` VALUES (11,'SecurityCenter',3,'Security center','securitycenter','Provides the ability to manage site security. It logs attempted hacks and similar events, and incorporates a user interface for customising alerting and security settings.',0,'SecurityCenter','1.4.2',1,'Mark West','http://www.zikula.org',1,0,0,0,3,'','','','','a:1:{s:16:\"SecurityCenter::\";s:16:\"hackid::hacktime\";}');
INSERT INTO `z_modules` VALUES (12,'Settings',3,'General settings','settings','Provides an interface for managing the site\'s general settings, i.e. site start page settings, multi-lingual settings, error reporting options and various other features that are not administered within other modules.',0,'Settings','2.9.3',1,'Simon Wunderlin','',1,0,0,0,3,'','','','','a:1:{s:10:\"Settings::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (13,'SysInfo',3,'System info','sysinfo','Provides detailed information reports about the system configuration and environment, for tracking and troubleshooting purposes.',0,'SysInfo','1.1',1,'Simon Birtwistle','hammerhead@zikula.org',1,0,0,0,3,'','','','','a:1:{s:9:\"SysInfo::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (14,'Theme',3,'Themes manager','theme','Provides the site\'s theming system, and an interface for managing themes, to control the site\'s presentation and appearance.',0,'Theme','3.4',1,'Mark West','http://www.markwest.me.uk/',1,1,0,0,3,'','','','','a:1:{s:7:\"Theme::\";s:12:\"Theme name::\";}');
INSERT INTO `z_modules` VALUES (15,'Users',3,'Users manager','users','Provides an interface for configuring and administering registered user accounts. Incorporates all needed functionality, but can work in close unison with the third party profile module configured in the general settings of the site.',0,'Users','2.0.0',1,'Xiaoyu Huang, Drak','class007@sina.com, drak@zikula.org',1,1,0,0,3,'','','','','a:2:{s:7:\"Users::\";s:14:\"Uname::User ID\";s:16:\"Users::MailUsers\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (16,'Tour',2,'Tour','tour','First time configuration and Zikula Tour.',0,'Tour','1.3',1,'Simon Birtwistle','http://www.itbegins.co.uk',0,1,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/install.txt','pndocs/license.txt','a:0:{}');

--
-- Table structure for table `z_objectdata_attributes`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_objectdata_attributes` (
  `oba_id` bigint(20) NOT NULL,
  `oba_attribute_name` varchar(80) NOT NULL,
  `oba_object_id` bigint(20) NOT NULL DEFAULT '0',
  `oba_object_type` varchar(80) NOT NULL,
  `oba_value` longtext NOT NULL,
  `oba_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `oba_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `oba_cr_uid` bigint(20) NOT NULL DEFAULT '0',
  `oba_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `oba_lu_uid` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`oba_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_objectdata_attributes`
--

INSERT INTO `z_objectdata_attributes` VALUES (1,'code',5,'categories_category','Y','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_objectdata_attributes` VALUES (2,'code',6,'categories_category','N','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_objectdata_attributes` VALUES (3,'code',11,'categories_category','P','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_objectdata_attributes` VALUES (4,'code',12,'categories_category','C','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_objectdata_attributes` VALUES (5,'code',13,'categories_category','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_objectdata_attributes` VALUES (6,'code',14,'categories_category','O','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_objectdata_attributes` VALUES (7,'code',15,'categories_category','R','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_objectdata_attributes` VALUES (8,'code',17,'categories_category','M','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_objectdata_attributes` VALUES (9,'code',18,'categories_category','F','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_objectdata_attributes` VALUES (10,'code',26,'categories_category','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_objectdata_attributes` VALUES (11,'code',27,'categories_category','I','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_objectdata_attributes` VALUES (12,'code',29,'categories_category','P','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);
INSERT INTO `z_objectdata_attributes` VALUES (13,'code',30,'categories_category','A','A','2010-06-25 03:32:28',0,'2010-06-25 03:32:28',0);

--
-- Table structure for table `z_objectdata_log`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_objectdata_log` (
  `obl_id` bigint(20) NOT NULL,
  `obl_object_type` varchar(80) NOT NULL,
  `obl_object_id` bigint(20) NOT NULL DEFAULT '0',
  `obl_op` varchar(16) NOT NULL,
  `obl_diff` longtext,
  `obl_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `obl_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `obl_cr_uid` bigint(20) NOT NULL DEFAULT '0',
  `obl_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `obl_lu_uid` bigint(20) NOT NULL DEFAULT '0',
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
  `obm_id` bigint(20) NOT NULL,
  `obm_module` varchar(40) NOT NULL,
  `obm_table` varchar(40) NOT NULL,
  `obm_idcolumn` varchar(40) NOT NULL,
  `obm_obj_id` bigint(20) NOT NULL DEFAULT '0',
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
  `obm_cr_uid` bigint(20) NOT NULL DEFAULT '0',
  `obm_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `obm_lu_uid` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`obm_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_objectdata_meta`
--


--
-- Table structure for table `z_pagelock`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_pagelock` (
  `plock_id` bigint(20) NOT NULL,
  `plock_name` varchar(100) NOT NULL,
  `plock_cdate` datetime NOT NULL,
  `plock_edate` datetime NOT NULL,
  `plock_session` varchar(50) NOT NULL,
  `plock_title` varchar(100) NOT NULL,
  `plock_ipno` varchar(30) NOT NULL,
  PRIMARY KEY (`plock_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_pagelock`
--


--
-- Table structure for table `z_sc_anticracker`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_sc_anticracker` (
  `pn_hid` bigint(20) NOT NULL,
  `pn_hacktime` varchar(20) DEFAULT NULL,
  `pn_hackfile` varchar(255) DEFAULT NULL,
  `pn_hackline` bigint(20) DEFAULT NULL,
  `pn_hacktype` varchar(255) DEFAULT NULL,
  `pn_hackinfo` longtext,
  `pn_userid` bigint(20) DEFAULT NULL,
  `pn_browserinfo` longtext,
  `pn_requestarray` longtext,
  `pn_gettarray` longtext,
  `pn_postarray` longtext,
  `pn_serverarray` longtext,
  `pn_envarray` longtext,
  `pn_cookiearray` longtext,
  `pn_filesarray` longtext,
  `pn_sessionarray` longtext,
  PRIMARY KEY (`pn_hid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_sc_anticracker`
--


--
-- Table structure for table `z_sc_intrusion`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_sc_intrusion` (
  `ids_id` bigint(20) NOT NULL,
  `ids_name` varchar(128) NOT NULL,
  `ids_tag` varchar(40) DEFAULT NULL,
  `ids_value` longtext NOT NULL,
  `ids_page` longtext NOT NULL,
  `ids_uid` bigint(20) DEFAULT NULL,
  `ids_ip` varchar(40) NOT NULL,
  `ids_impact` bigint(20) NOT NULL DEFAULT '0',
  `ids_date` datetime NOT NULL,
  PRIMARY KEY (`ids_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_sc_intrusion`
--


--
-- Table structure for table `z_sc_log_event`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_sc_log_event` (
  `lge_id` bigint(20) NOT NULL,
  `lge_date` datetime DEFAULT NULL,
  `lge_uid` bigint(20) DEFAULT NULL,
  `lge_component` varchar(64) DEFAULT NULL,
  `lge_module` varchar(64) DEFAULT NULL,
  `lge_type` varchar(64) DEFAULT NULL,
  `lge_function` varchar(64) DEFAULT NULL,
  `lge_sec_component` varchar(64) DEFAULT NULL,
  `lge_sec_instance` varchar(64) DEFAULT NULL,
  `lge_sec_permission` varchar(64) DEFAULT NULL,
  `lge_message` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`lge_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_sc_log_event`
--


--
-- Table structure for table `z_search_result`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_search_result` (
  `sres_id` bigint(20) NOT NULL,
  `sres_title` varchar(255) NOT NULL,
  `sres_text` longtext,
  `sres_module` varchar(100) DEFAULT NULL,
  `sres_extra` varchar(100) DEFAULT NULL,
  `sres_created` datetime DEFAULT NULL,
  `sres_found` datetime DEFAULT NULL,
  `sres_sesid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`sres_id`),
  KEY `title` (`sres_title`),
  KEY `module` (`sres_module`)
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
  `pn_id` bigint(20) NOT NULL,
  `pn_search` varchar(50) NOT NULL,
  `pn_count` bigint(20) NOT NULL DEFAULT '0',
  `pn_date` date DEFAULT NULL,
  PRIMARY KEY (`pn_id`)
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
  `pn_sessid` varchar(40) NOT NULL,
  `pn_ipaddr` varchar(32) NOT NULL,
  `pn_lastused` datetime DEFAULT '1970-01-01 00:00:00',
  `pn_uid` bigint(20) DEFAULT '0',
  `pn_remember` bigint(20) NOT NULL DEFAULT '0',
  `pn_vars` longtext NOT NULL,
  PRIMARY KEY (`pn_sessid`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_session_info`
--

INSERT INTO `z_session_info` VALUES ('9lvbkl1ieu2ca9r75o9fvneasq8qvinl','837ec5754f503cfaaee0929fd48974e7','2010-06-25 03:32:44',2,0,'ZSVrand|a:1:{s:5:\"admin\";s:33:\"duEOf9=WlcavXO42AnZUYN_pCIeq*Lr(|\";}ZSVuseragent|s:40:\"c258845b3b650a4a085d93294c4d4d03f7daef94\";ZSVuid|i:2;ZSVauthmodule|s:5:\"Users\";ZSVlastacid|i:1;ZSVlastcid|i:1;');

--
-- Table structure for table `z_themes`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_themes` (
  `pn_id` bigint(20) NOT NULL,
  `pn_name` varchar(64) NOT NULL,
  `pn_type` bigint(20) NOT NULL DEFAULT '0',
  `pn_displayname` varchar(64) NOT NULL,
  `pn_description` varchar(255) NOT NULL,
  `pn_regid` bigint(20) NOT NULL DEFAULT '0',
  `pn_directory` varchar(64) NOT NULL,
  `pn_version` varchar(10) NOT NULL DEFAULT '0',
  `pn_official` bigint(20) NOT NULL DEFAULT '0',
  `pn_author` varchar(255) NOT NULL,
  `pn_contact` varchar(255) NOT NULL,
  `pn_admin` bigint(20) NOT NULL DEFAULT '0',
  `pn_user` bigint(20) NOT NULL DEFAULT '0',
  `pn_system` bigint(20) NOT NULL DEFAULT '0',
  `pn_state` bigint(20) NOT NULL DEFAULT '0',
  `pn_credits` varchar(255) NOT NULL,
  `pn_changelog` varchar(255) NOT NULL,
  `pn_help` varchar(255) NOT NULL,
  `pn_license` varchar(255) NOT NULL,
  `pn_xhtml` bigint(20) NOT NULL DEFAULT '1',
  PRIMARY KEY (`pn_id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_themes`
--

INSERT INTO `z_themes` VALUES (1,'andreas08',3,'Andreas08','The Andreas08 theme is a very good template for light CSS-compatible browser-oriented themes.',0,'andreas08','1.1',0,'David Brucas, Mark West, Andreas Viklund','http://dbrucas.povpromotions.com, http://www.markwest.me.uk, http://www.andreasviklund.com',1,1,0,1,'','','','',1);
INSERT INTO `z_themes` VALUES (2,'Atom',3,'Atom','The Atom theme is an auxiliary theme specially designed for rendering pages in Atom mark-up.',0,'Atom','1.0',0,'Franz Skaaning','http://www.lexebus.net/',0,0,1,1,'','','','',0);
INSERT INTO `z_themes` VALUES (3,'Printer',3,'Printer','The Printer theme is an auxiliary theme designed specially for outputting pages in a printer-friendly format.',0,'Printer','2.0',0,'Mark West','http://www.markwest.me.uk',0,0,1,1,'','','','',1);
INSERT INTO `z_themes` VALUES (4,'RSS',3,'RSS','The RSS theme is an auxiliary theme designed specially for outputting pages as an RSS feed.',0,'rss','1.0',0,'Mark West','http://www.markwest.me.uk',0,0,1,1,'docs/credits.txt','docs/changelog.txt','docs/help.txt','docs/license.txt',0);
INSERT INTO `z_themes` VALUES (5,'SeaBreeze',3,'SeaBreeze','The SeaBreeze theme is a browser-oriented theme, and was updated for the release of Zikula 1.0, with revised colours and new graphics.',0,'SeaBreeze','3.1',0,'Carsten Volmer, Vanessa Haakenson, Mark West, Martin Andersen','http://www.zikula.org',0,1,0,1,'','','','',1);

--
-- Table structure for table `z_userblocks`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_userblocks` (
  `pn_uid` bigint(20) NOT NULL DEFAULT '0',
  `pn_bid` bigint(20) NOT NULL DEFAULT '0',
  `pn_active` bigint(20) NOT NULL DEFAULT '1',
  `pn_last_update` datetime DEFAULT NULL,
  KEY `bid_uid_idx` (`pn_uid`,`pn_bid`)
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
  `pn_uid` bigint(20) NOT NULL,
  `pn_uname` varchar(25) NOT NULL,
  `pn_email` varchar(60) NOT NULL,
  `pn_pass` varchar(138) NOT NULL,
  `passreminder` varchar(255) NOT NULL,
  `passrecovery` longtext NOT NULL,
  `pn_activated` bigint(20) NOT NULL DEFAULT '0',
  `pn_user_regdate` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `pn_lastlogin` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `pn_theme` varchar(255) NOT NULL,
  `pn_ublockon` bigint(20) NOT NULL DEFAULT '0',
  `pn_ublock` longtext NOT NULL,
  PRIMARY KEY (`pn_uid`),
  KEY `uname` (`pn_uname`),
  KEY `email` (`pn_email`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_users`
--

INSERT INTO `z_users` VALUES (1,'guest','','','','',1,'2010-06-25 03:32:27','1970-01-01 00:00:00','',0,'');
INSERT INTO `z_users` VALUES (2,'admin','example@example.com','8$c1NKK$09fa20774dfe897315a7a2d72323de6d183395d94b89f9c8c09cdc30577a8950','','',1,'2010-06-25 03:32:39','2010-06-25 03:32:39','',0,'');

--
-- Table structure for table `z_users_registration`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_users_registration` (
  `id` bigint(20) NOT NULL,
  `uname` varchar(25) NOT NULL,
  `email` varchar(60) NOT NULL,
  `pass` varchar(138) NOT NULL,
  `passreminder` varchar(255) NOT NULL,
  `passrecovery` longtext NOT NULL,
  `agreetoterms` int(11) NOT NULL DEFAULT '0',
  `dynadata` longtext NOT NULL,
  `verifycode` varchar(138) NOT NULL,
  `validuntil` datetime DEFAULT NULL,
  `isapproved` int(11) NOT NULL DEFAULT '0',
  `isverified` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `z_users_registration`
--


--
-- Table structure for table `z_users_verifychg`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `z_users_verifychg` (
  `id` bigint(20) NOT NULL,
  `changetype` bigint(20) NOT NULL DEFAULT '0',
  `uid` bigint(20) NOT NULL DEFAULT '0',
  `newemail` varchar(60) NOT NULL,
  `verifycode` varchar(138) NOT NULL,
  `validuntil` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
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
  `id` bigint(20) NOT NULL,
  `metaid` bigint(20) NOT NULL DEFAULT '0',
  `module` varchar(255) NOT NULL,
  `schemaname` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `type` bigint(20) NOT NULL DEFAULT '1',
  `obj_table` varchar(40) NOT NULL,
  `obj_idcolumn` varchar(40) NOT NULL,
  `obj_id` bigint(20) NOT NULL DEFAULT '0',
  `busy` bigint(20) NOT NULL DEFAULT '0',
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

-- Dump completed on 2010-06-25  9:21:49
