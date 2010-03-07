/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='Asia/Katmandu' */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;
/*!40101 SET SQL_MODE='' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;
/*!40103 SET SQL_NOTES='ON' */;

DROP TABLE IF EXISTS `z_admin_category`;
CREATE TABLE `z_admin_category` (
  `pn_cid` int(11) NOT NULL AUTO_INCREMENT,
  `pn_name` varchar(32) NOT NULL DEFAULT '',
  `pn_description` varchar(254) NOT NULL DEFAULT '',
  PRIMARY KEY (`pn_cid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

INSERT INTO `z_admin_category` VALUES (1,'System','Core modules at the heart of operation of the site.');
INSERT INTO `z_admin_category` VALUES (2,'Layout','Layout modules for controlling the site\'s look and feel.');
INSERT INTO `z_admin_category` VALUES (3,'Users','Modules for controlling user membership, access rights and profiles.');
INSERT INTO `z_admin_category` VALUES (4,'Content','Modules for providing content to your users.');
INSERT INTO `z_admin_category` VALUES (5,'3rd-party','3rd-party add-on modules and newly-installed modules.');
INSERT INTO `z_admin_category` VALUES (6,'Security','Modules for managing the site\'s security.');
INSERT INTO `z_admin_category` VALUES (7,'Hooked','Auxiliary modules designed to be hooked to other modules, to give them added functionality.');
DROP TABLE IF EXISTS `z_admin_module`;
CREATE TABLE `z_admin_module` (
  `pn_amid` int(11) NOT NULL AUTO_INCREMENT,
  `pn_mid` int(11) NOT NULL DEFAULT '0',
  `pn_cid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pn_amid`),
  KEY `mid_cid` (`pn_mid`,`pn_cid`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

INSERT INTO `z_admin_module` VALUES (1,1,1);
INSERT INTO `z_admin_module` VALUES (2,2,1);
INSERT INTO `z_admin_module` VALUES (3,13,3);
INSERT INTO `z_admin_module` VALUES (4,8,3);
INSERT INTO `z_admin_module` VALUES (5,5,2);
INSERT INTO `z_admin_module` VALUES (6,11,1);
INSERT INTO `z_admin_module` VALUES (7,21,3);
INSERT INTO `z_admin_module` VALUES (8,20,2);
INSERT INTO `z_admin_module` VALUES (9,18,1);
INSERT INTO `z_admin_module` VALUES (10,3,4);
INSERT INTO `z_admin_module` VALUES (11,17,6);
INSERT INTO `z_admin_module` VALUES (12,37,4);
INSERT INTO `z_admin_module` VALUES (13,6,4);
INSERT INTO `z_admin_module` VALUES (14,9,2);
INSERT INTO `z_admin_module` VALUES (15,29,4);
INSERT INTO `z_admin_module` VALUES (16,10,1);
INSERT INTO `z_admin_module` VALUES (17,7,1);
INSERT INTO `z_admin_module` VALUES (18,15,2);
INSERT INTO `z_admin_module` VALUES (19,14,1);
INSERT INTO `z_admin_module` VALUES (20,16,4);
INSERT INTO `z_admin_module` VALUES (21,22,1);
INSERT INTO `z_admin_module` VALUES (22,12,1);
INSERT INTO `z_admin_module` VALUES (23,19,6);
DROP TABLE IF EXISTS `z_block_placements`;
CREATE TABLE `z_block_placements` (
  `pn_pid` int(11) NOT NULL DEFAULT '0',
  `pn_bid` int(11) NOT NULL DEFAULT '0',
  `pn_order` int(11) NOT NULL DEFAULT '0',
  KEY `bid_pid_idx` (`pn_bid`,`pn_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `z_block_placements` VALUES (1,1,0);
INSERT INTO `z_block_placements` VALUES (1,2,0);
INSERT INTO `z_block_placements` VALUES (2,3,0);
INSERT INTO `z_block_placements` VALUES (2,4,0);
INSERT INTO `z_block_placements` VALUES (3,5,0);
DROP TABLE IF EXISTS `z_block_positions`;
CREATE TABLE `z_block_positions` (
  `pn_pid` int(11) NOT NULL AUTO_INCREMENT,
  `pn_name` varchar(255) NOT NULL DEFAULT '',
  `pn_description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`pn_pid`),
  KEY `name_idx` (`pn_name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO `z_block_positions` VALUES (1,'left','Left blocks');
INSERT INTO `z_block_positions` VALUES (2,'right','Right blocks');
INSERT INTO `z_block_positions` VALUES (3,'center','Centre blocks');
DROP TABLE IF EXISTS `z_blocks`;
CREATE TABLE `z_blocks` (
  `pn_bid` int(11) NOT NULL AUTO_INCREMENT,
  `pn_bkey` varchar(255) NOT NULL DEFAULT '',
  `pn_title` varchar(255) NOT NULL DEFAULT '',
  `pn_content` longtext NOT NULL,
  `pn_url` longtext NOT NULL,
  `pn_mid` int(11) NOT NULL DEFAULT '0',
  `pn_filter` longtext NOT NULL,
  `pn_active` tinyint(4) NOT NULL DEFAULT '1',
  `pn_collapsable` int(11) NOT NULL DEFAULT '1',
  `pn_defaultstate` int(11) NOT NULL DEFAULT '1',
  `pn_refresh` int(11) NOT NULL DEFAULT '0',
  `pn_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `pn_language` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`pn_bid`),
  KEY `active_idx` (`pn_active`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

INSERT INTO `z_blocks` VALUES (1,'extmenu','Main menu','a:5:{s:14:\"displaymodules\";s:1:\"1\";s:10:\"stylesheet\";s:11:\"extmenu.css\";s:8:\"template\";s:24:\"blocks_block_extmenu.htm\";s:11:\"blocktitles\";a:2:{s:2:\"de\";s:10:\"Hauptmenü\";s:2:\"en\";s:9:\"Main menu\";}s:5:\"links\";a:2:{s:2:\"de\";a:4:{i:0;a:7:{s:4:\"name\";s:10:\"Startseite\";s:3:\"url\";s:10:\"{homepage}\";s:5:\"title\";s:26:\"Zur Startseite der Website\";s:5:\"level\";i:0;s:6:\"parent\";i:0;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:1;a:7:{s:4:\"name\";s:14:\"Administration\";s:3:\"url\";s:24:\"{Admin:adminpanel:admin}\";s:5:\"title\";s:35:\"Gehe zur Administration der Website\";s:5:\"level\";i:0;s:6:\"parent\";i:0;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:2;a:7:{s:4:\"name\";s:13:\"Benutzerkonto\";s:3:\"url\";s:7:\"{Users}\";s:5:\"title\";s:29:\"Verwaltung des Benutzerkontos\";s:5:\"level\";i:0;s:6:\"parent\";i:0;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:3;a:7:{s:4:\"name\";s:8:\"Abmelden\";s:3:\"url\";s:14:\"{Users:logout}\";s:5:\"title\";s:27:\"Aktuellen Benutzer abmelden\";s:5:\"level\";i:0;s:6:\"parent\";i:0;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}}s:2:\"en\";a:4:{i:0;a:7:{s:4:\"name\";s:4:\"Home\";s:3:\"url\";s:10:\"{homepage}\";s:5:\"title\";s:26:\"Go to the site\'s home page\";s:5:\"level\";i:0;s:6:\"parent\";i:0;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:1;a:7:{s:4:\"name\";s:16:\"Site admin panel\";s:3:\"url\";s:24:\"{Admin:adminpanel:admin}\";s:5:\"title\";s:26:\"Go to the site admin panel\";s:5:\"level\";i:0;s:6:\"parent\";i:0;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:2;a:7:{s:4:\"name\";s:18:\"User account panel\";s:3:\"url\";s:7:\"{Users}\";s:5:\"title\";s:29:\"Go to your user account panel\";s:5:\"level\";i:0;s:6:\"parent\";i:0;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}i:3;a:7:{s:4:\"name\";s:7:\"Log out\";s:3:\"url\";s:14:\"{Users:logout}\";s:5:\"title\";s:28:\"Log out of your user account\";s:5:\"level\";i:0;s:6:\"parent\";i:0;s:5:\"image\";s:0:\"\";s:6:\"active\";s:1:\"1\";}}}}','',5,'',1,1,1,3600,'2009-11-05 16:11:23','');
INSERT INTO `z_blocks` VALUES (2,'thelang','Languages','','',5,'',1,1,1,3600,'2009-11-05 16:11:23','');
INSERT INTO `z_blocks` VALUES (3,'login','User log-in','','',21,'',1,1,1,3600,'2009-11-05 16:11:23','');
INSERT INTO `z_blocks` VALUES (4,'online','Who\'s on-line','','',21,'',1,1,1,3600,'2009-11-05 16:11:23','');
INSERT INTO `z_blocks` VALUES (5,'messages','Admin messages','','',3,'',1,1,1,3600,'2009-11-05 16:11:23','');
DROP TABLE IF EXISTS `z_categories_category`;
CREATE TABLE `z_categories_category` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_parent_id` int(11) NOT NULL DEFAULT '1',
  `cat_is_locked` tinyint(4) NOT NULL DEFAULT '0',
  `cat_is_leaf` tinyint(4) NOT NULL DEFAULT '0',
  `cat_name` varchar(255) NOT NULL DEFAULT '',
  `cat_value` varchar(255) NOT NULL DEFAULT '',
  `cat_sort_value` int(11) NOT NULL DEFAULT '0',
  `cat_display_name` text,
  `cat_display_desc` text,
  `cat_path` text,
  `cat_ipath` varchar(255) NOT NULL DEFAULT '',
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
) ENGINE=MyISAM AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8;

INSERT INTO `z_categories_category` VALUES (1,0,1,0,'__SYSTEM__','',0,'b:0;','b:0;','/__SYSTEM__','/1','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (2,1,0,0,'Modules','',0,'a:1:{s:2:\"en\";s:7:\"Modules\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules','/1/2','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (3,1,0,0,'General','',0,'a:1:{s:2:\"en\";s:7:\"General\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General','/1/3','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (4,3,0,0,'YesNo','',0,'a:1:{s:2:\"en\";s:6:\"Yes/No\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/YesNo','/1/3/4','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (5,4,0,1,'1 - Yes','',0,'b:0;','b:0;','/__SYSTEM__/General/YesNo/1 - Yes','/1/3/4/5','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (6,4,0,1,'2 - No','',0,'b:0;','b:0;','/__SYSTEM__/General/YesNo/2 - No','/1/3/4/6','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (10,3,0,0,'Publication Status (extended)','',0,'a:1:{s:2:\"en\";s:29:\"Publication status (extended)\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended','/1/3/10','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (11,10,0,1,'Pending','',0,'a:1:{s:2:\"en\";s:7:\"Pending\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Pending','/1/3/10/11','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (12,10,0,1,'Checked','',0,'a:1:{s:2:\"en\";s:7:\"Checked\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Checked','/1/3/10/12','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (13,10,0,1,'Approved','',0,'a:1:{s:2:\"en\";s:8:\"Approved\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Approved','/1/3/10/13','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (14,10,0,1,'On-line','',0,'a:1:{s:2:\"en\";s:7:\"On-line\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Online','/1/3/10/14','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (15,10,0,1,'Rejected','',0,'a:1:{s:2:\"en\";s:8:\"Rejected\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Extended/Rejected','/1/3/10/15','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (16,3,0,0,'Gender','',0,'a:1:{s:2:\"en\";s:6:\"Gender\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Gender','/1/3/16','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (17,16,0,1,'Male','',0,'a:1:{s:2:\"en\";s:4:\"Male\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Gender/Male','/1/3/16/17','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (18,16,0,1,'Female','',0,'a:1:{s:2:\"en\";s:6:\"Female\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Gender/Female','/1/3/16/18','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (19,3,0,0,'Title','',0,'a:1:{s:2:\"en\";s:5:\"Title\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Title','/1/3/19','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (20,19,0,1,'Mr','',0,'a:1:{s:2:\"en\";s:3:\"Mr.\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Title/Mr','/1/3/19/20','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (21,19,0,1,'Mrs','',0,'a:1:{s:2:\"en\";s:4:\"Mrs.\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Title/Mrs','/1/3/19/21','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (22,19,0,1,'Ms','',0,'a:1:{s:2:\"en\";s:3:\"Ms.\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Title/Ms','/1/3/19/22','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (23,19,0,1,'Miss','',0,'a:1:{s:2:\"en\";s:4:\"Miss\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Title/Miss','/1/3/19/23','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (24,19,0,1,'Dr','',0,'a:1:{s:2:\"en\";s:3:\"Dr.\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Title/Dr','/1/3/19/24','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (25,3,0,0,'ActiveStatus','',0,'a:1:{s:2:\"en\";s:15:\"Activity status\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus','/1/3/25','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (26,23,0,1,'Active','',0,'a:1:{s:2:\"en\";s:6:\"Active\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus/Active','/1/3/25/26','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (27,23,0,1,'Inactive','',0,'a:1:{s:2:\"en\";s:8:\"Inactive\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/ActiveStatus/Inactive','/1/3/25/27','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (28,3,0,0,'Publication status (basic)','',0,'a:1:{s:2:\"en\";s:26:\"Publication status (basic)\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic','/1/3/28','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (29,28,0,1,'Pending','',0,'a:1:{s:2:\"en\";s:7:\"Pending\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic/Pending','/1/3/28/29','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (30,28,0,1,'Approved','',0,'a:1:{s:2:\"en\";s:8:\"Approved\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/General/Publication Status Basic/Approved','/1/3/28/30','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (31,1,0,0,'Users','',0,'a:1:{s:2:\"en\";s:5:\"Users\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Users','/1/31','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (32,2,0,0,'Global','',0,'a:1:{s:2:\"en\";s:6:\"Global\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global','/1/2/32','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (33,32,0,1,'Blogging','',0,'a:1:{s:2:\"en\";s:8:\"Blogging\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/Blogging','/1/2/32/33','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (34,32,0,1,'Music and audio','',0,'a:1:{s:2:\"en\";s:15:\"Music and audio\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/MusicAndAudio','/1/2/32/34','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (35,32,0,1,'Art and photography','',0,'a:1:{s:2:\"en\";s:19:\"Art and photography\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ArtAndPhotography','/1/2/32/35','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (36,32,0,1,'Writing and thinking','',0,'a:1:{s:2:\"en\";s:20:\"Writing and thinking\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/WritingAndThinking','/1/2/32/36','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (37,32,0,1,'Communications and media','',0,'a:1:{s:2:\"en\";s:24:\"Communications and media\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/CommunicationsAndMedia','/1/2/32/37','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (38,32,0,1,'Travel and culture','',0,'a:1:{s:2:\"en\";s:18:\"Travel and culture\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/TravelAndCulture','/1/2/32/38','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (39,32,0,1,'Science and technology','',0,'a:1:{s:2:\"en\";s:22:\"Science and technology\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ScienceAndTechnology','/1/2/32/39','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (40,32,0,1,'Sport and activities','',0,'a:1:{s:2:\"en\";s:20:\"Sport and activities\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/SportAndActivities','/1/2/32/40','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (41,32,0,1,'Business and work','',0,'a:1:{s:2:\"en\";s:17:\"Business and work\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/BusinessAndWork','/1/2/32/41','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_categories_category` VALUES (42,32,0,1,'Activism and action','',0,'a:1:{s:2:\"en\";s:19:\"Activism and action\";}','a:1:{s:3:\"eng\";s:0:\"\";}','/__SYSTEM__/Modules/Global/ActivismAndAction','/1/2/32/42','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
DROP TABLE IF EXISTS `z_categories_mapmeta`;
CREATE TABLE `z_categories_mapmeta` (
  `cmm_id` int(11) NOT NULL AUTO_INCREMENT,
  `cmm_meta_id` int(11) NOT NULL DEFAULT '0',
  `cmm_category_id` int(11) NOT NULL DEFAULT '0',
  `cmm_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cmm_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cmm_cr_uid` int(11) NOT NULL DEFAULT '0',
  `cmm_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cmm_lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmm_id`),
  KEY `idx_categories_mapmeta` (`cmm_meta_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_categories_mapobj`;
CREATE TABLE `z_categories_mapobj` (
  `cmo_id` int(11) NOT NULL AUTO_INCREMENT,
  `cmo_modname` varchar(60) NOT NULL DEFAULT '',
  `cmo_table` varchar(60) NOT NULL,
  `cmo_obj_id` int(11) NOT NULL DEFAULT '0',
  `cmo_obj_idcolumn` varchar(60) NOT NULL DEFAULT 'id',
  `cmo_reg_id` int(11) NOT NULL,
  `cmo_category_id` int(11) NOT NULL,
  `cmo_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `cmo_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cmo_cr_uid` int(11) NOT NULL DEFAULT '0',
  `cmo_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `cmo_lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmo_id`),
  KEY `idx_categories_mapobj` (`cmo_modname`,`cmo_table`,`cmo_obj_id`,`cmo_obj_idcolumn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_categories_registry`;
CREATE TABLE `z_categories_registry` (
  `crg_id` int(11) NOT NULL AUTO_INCREMENT,
  `crg_modname` varchar(60) NOT NULL DEFAULT '',
  `crg_table` varchar(60) NOT NULL DEFAULT '',
  `crg_property` varchar(60) NOT NULL DEFAULT '',
  `crg_category_id` int(11) NOT NULL DEFAULT '0',
  `crg_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `crg_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `crg_cr_uid` int(11) NOT NULL DEFAULT '0',
  `crg_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `crg_lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`crg_id`),
  KEY `idx_categories_registry` (`crg_modname`,`crg_table`,`crg_property`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_group_applications`;
CREATE TABLE `z_group_applications` (
  `pn_app_id` int(11) NOT NULL AUTO_INCREMENT,
  `pn_uid` int(11) NOT NULL DEFAULT '0',
  `pn_gid` int(11) NOT NULL DEFAULT '0',
  `pn_application` longblob,
  `pn_status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pn_app_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_group_membership`;
CREATE TABLE `z_group_membership` (
  `pn_gid` int(11) NOT NULL DEFAULT '0',
  `pn_uid` int(11) NOT NULL DEFAULT '0',
  KEY `gid_uid` (`pn_uid`,`pn_gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `z_group_membership` VALUES (1,1);
INSERT INTO `z_group_membership` VALUES (2,2);
DROP TABLE IF EXISTS `z_group_perms`;
CREATE TABLE `z_group_perms` (
  `pn_pid` int(11) NOT NULL AUTO_INCREMENT,
  `pn_gid` int(11) NOT NULL DEFAULT '0',
  `pn_sequence` int(11) NOT NULL DEFAULT '0',
  `pn_realm` int(11) NOT NULL DEFAULT '0',
  `pn_component` varchar(255) NOT NULL DEFAULT '',
  `pn_instance` varchar(255) NOT NULL DEFAULT '',
  `pn_level` int(11) NOT NULL DEFAULT '0',
  `pn_bond` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pn_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

INSERT INTO `z_group_perms` VALUES (1,2,1,0,'.*','.*',800,0);
INSERT INTO `z_group_perms` VALUES (2,-1,2,0,'ExtendedMenublock::','1:1:',0,0);
INSERT INTO `z_group_perms` VALUES (3,1,3,0,'.*','.*',300,0);
INSERT INTO `z_group_perms` VALUES (4,0,4,0,'ExtendedMenublock::','1:(1|2|3):',0,0);
INSERT INTO `z_group_perms` VALUES (5,0,5,0,'.*','.*',200,0);
DROP TABLE IF EXISTS `z_groups`;
CREATE TABLE `z_groups` (
  `pn_gid` int(11) NOT NULL AUTO_INCREMENT,
  `pn_name` varchar(255) NOT NULL DEFAULT '',
  `pn_gtype` tinyint(4) NOT NULL DEFAULT '0',
  `pn_description` varchar(200) NOT NULL DEFAULT '',
  `pn_prefix` varchar(25) NOT NULL DEFAULT '',
  `pn_state` tinyint(4) NOT NULL DEFAULT '0',
  `pn_nbuser` int(11) NOT NULL DEFAULT '0',
  `pn_nbumax` int(11) NOT NULL DEFAULT '0',
  `pn_link` int(11) NOT NULL DEFAULT '0',
  `pn_uidmaster` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pn_gid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO `z_groups` VALUES (1,'Users',0,'By default, all users are made members of this group.','usr',0,0,0,0,0);
INSERT INTO `z_groups` VALUES (2,'Administrators',0,'By default, all administrators are made members of this group.','adm',0,0,0,0,0);
DROP TABLE IF EXISTS `z_hooks`;
CREATE TABLE `z_hooks` (
  `pn_id` int(11) NOT NULL AUTO_INCREMENT,
  `pn_object` varchar(64) NOT NULL DEFAULT '',
  `pn_action` varchar(64) NOT NULL DEFAULT '',
  `pn_smodule` varchar(64) NOT NULL DEFAULT '',
  `pn_stype` varchar(64) NOT NULL DEFAULT '',
  `pn_tarea` varchar(64) NOT NULL DEFAULT '',
  `pn_tmodule` varchar(64) NOT NULL DEFAULT '',
  `pn_ttype` varchar(64) NOT NULL DEFAULT '',
  `pn_tfunc` varchar(64) NOT NULL DEFAULT '',
  `pn_sequence` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pn_id`),
  KEY `smodule` (`pn_smodule`),
  KEY `smodule_tmodule` (`pn_smodule`,`pn_tmodule`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_message`;
CREATE TABLE `z_message` (
  `pn_mid` int(11) NOT NULL AUTO_INCREMENT,
  `pn_title` varchar(100) NOT NULL DEFAULT '',
  `pn_content` longtext NOT NULL,
  `pn_date` int(11) NOT NULL DEFAULT '0',
  `pn_expire` int(11) NOT NULL DEFAULT '0',
  `pn_active` int(11) NOT NULL DEFAULT '1',
  `pn_view` int(11) NOT NULL DEFAULT '1',
  `pn_language` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`pn_mid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `z_message` VALUES (1,'This site is powered by Zikula!','<p><a href=\"http://www.zikula.org\">Zikula</a> is a content management system (CMS) and application framework. It is secure and stable, and is a good choice for sites with a large volume of traffic.</p><p>With Zikula:</p><ul><li>you can customise all aspects of the site\'s appearance through themes, with support for CSS style sheets, JavaScript, Flash and all other modern web development technologies;</li><li>you can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation and internationalisation of your site and pages;</li><li>you can be sure that your pages will display properly in all browsers, thanks to Zikula\'s full compliance with W3C HTML standards;</li><li>you get a standard application-programming interface (API) that lets you easily augment your site\'s functionality through modules, blocks and other extensions;</li><li>you can get help and support from the Zikula community of webmasters and developers at <a href=\"http://www.zikula.org\">zikula.org</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>',1257416783,0,1,1,'');
DROP TABLE IF EXISTS `z_module_deps`;
CREATE TABLE `z_module_deps` (
  `pn_id` int(11) NOT NULL AUTO_INCREMENT,
  `pn_modid` int(11) NOT NULL DEFAULT '0',
  `pn_modname` varchar(64) NOT NULL DEFAULT '',
  `pn_minversion` varchar(10) NOT NULL DEFAULT '',
  `pn_maxversion` varchar(10) NOT NULL DEFAULT '',
  `pn_status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pn_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_module_vars`;
CREATE TABLE `z_module_vars` (
  `pn_id` int(11) NOT NULL AUTO_INCREMENT,
  `pn_modname` varchar(64) NOT NULL DEFAULT '',
  `pn_name` varchar(64) NOT NULL DEFAULT '',
  `pn_value` longtext,
  PRIMARY KEY (`pn_id`),
  KEY `mod_var` (`pn_modname`,`pn_name`)
) ENGINE=MyISAM AUTO_INCREMENT=197 DEFAULT CHARSET=utf8;

INSERT INTO `z_module_vars` VALUES (1,'Modules','itemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (2,'/PNConfig','loadlegacy','i:0;');
INSERT INTO `z_module_vars` VALUES (3,'Admin','modulesperrow','i:3;');
INSERT INTO `z_module_vars` VALUES (4,'Admin','itemsperpage','i:15;');
INSERT INTO `z_module_vars` VALUES (5,'Admin','defaultcategory','i:5;');
INSERT INTO `z_module_vars` VALUES (6,'Admin','modulestylesheet','s:11:\"navtabs.css\";');
INSERT INTO `z_module_vars` VALUES (7,'Admin','admingraphic','i:1;');
INSERT INTO `z_module_vars` VALUES (8,'Admin','startcategory','i:1;');
INSERT INTO `z_module_vars` VALUES (9,'Admin','ignoreinstallercheck','i:0;');
INSERT INTO `z_module_vars` VALUES (10,'Admin','admintheme','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (11,'Admin','displaynametype','i:1;');
INSERT INTO `z_module_vars` VALUES (12,'Admin','moduledescription','i:1;');
INSERT INTO `z_module_vars` VALUES (13,'Permissions','filter','i:1;');
INSERT INTO `z_module_vars` VALUES (14,'Permissions','warnbar','i:1;');
INSERT INTO `z_module_vars` VALUES (15,'Permissions','rowview','i:20;');
INSERT INTO `z_module_vars` VALUES (16,'Permissions','rowedit','i:20;');
INSERT INTO `z_module_vars` VALUES (17,'Permissions','lockadmin','i:1;');
INSERT INTO `z_module_vars` VALUES (18,'Permissions','adminid','i:1;');
INSERT INTO `z_module_vars` VALUES (19,'Groups','itemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (20,'Groups','defaultgroup','i:1;');
INSERT INTO `z_module_vars` VALUES (21,'Groups','mailwarning','i:0;');
INSERT INTO `z_module_vars` VALUES (22,'Groups','hideclosed','i:0;');
INSERT INTO `z_module_vars` VALUES (23,'Blocks','collapseable','i:0;');
INSERT INTO `z_module_vars` VALUES (24,'Users','itemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (25,'Users','accountdisplaygraphics','i:1;');
INSERT INTO `z_module_vars` VALUES (26,'Users','accountitemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (27,'Users','accountitemsperrow','i:5;');
INSERT INTO `z_module_vars` VALUES (28,'Users','changepassword','i:1;');
INSERT INTO `z_module_vars` VALUES (29,'Users','changeemail','i:1;');
INSERT INTO `z_module_vars` VALUES (30,'Users','reg_allowreg','i:1;');
INSERT INTO `z_module_vars` VALUES (31,'Users','reg_verifyemail','i:1;');
INSERT INTO `z_module_vars` VALUES (32,'Users','reg_Illegalusername','s:87:\"root adm linux webmaster admin god administrator administrador nobody anonymous anonimo\";');
INSERT INTO `z_module_vars` VALUES (33,'Users','reg_Illegaldomains','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (34,'Users','reg_Illegaluseragents','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (35,'Users','reg_noregreasons','s:51:\"Sorry! New user registration is currently disabled.\";');
INSERT INTO `z_module_vars` VALUES (36,'Users','reg_uniemail','i:1;');
INSERT INTO `z_module_vars` VALUES (37,'Users','reg_notifyemail','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (38,'Users','reg_optitems','i:0;');
INSERT INTO `z_module_vars` VALUES (39,'Users','userimg','s:11:\"images/menu\";');
INSERT INTO `z_module_vars` VALUES (40,'Users','avatarpath','s:13:\"images/avatar\";');
INSERT INTO `z_module_vars` VALUES (41,'Users','minage','i:13;');
INSERT INTO `z_module_vars` VALUES (42,'Users','minpass','i:5;');
INSERT INTO `z_module_vars` VALUES (43,'Users','anonymous','s:5:\"Guest\";');
INSERT INTO `z_module_vars` VALUES (44,'Users','savelastlogindate','i:0;');
INSERT INTO `z_module_vars` VALUES (45,'Users','loginviaoption','i:0;');
INSERT INTO `z_module_vars` VALUES (46,'Users','lowercaseuname','i:0;');
INSERT INTO `z_module_vars` VALUES (47,'Users','moderation','i:0;');
INSERT INTO `z_module_vars` VALUES (48,'Users','hash_method','s:6:\"sha256\";');
INSERT INTO `z_module_vars` VALUES (49,'Users','login_redirect','i:1;');
INSERT INTO `z_module_vars` VALUES (50,'Users','reg_question','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (51,'Users','reg_answer','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (52,'Users','idnnames','i:1;');
INSERT INTO `z_module_vars` VALUES (53,'Theme','modulesnocache','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (54,'Theme','enablecache','b:0;');
INSERT INTO `z_module_vars` VALUES (55,'Theme','compile_check','b:1;');
INSERT INTO `z_module_vars` VALUES (56,'Theme','cache_lifetime','i:3600;');
INSERT INTO `z_module_vars` VALUES (57,'Theme','force_compile','b:0;');
INSERT INTO `z_module_vars` VALUES (58,'Theme','trimwhitespace','b:0;');
INSERT INTO `z_module_vars` VALUES (59,'Theme','makelinks','b:0;');
INSERT INTO `z_module_vars` VALUES (60,'Theme','maxsizeforlinks','i:30;');
INSERT INTO `z_module_vars` VALUES (61,'Theme','itemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (62,'Theme','cssjscombine','b:0;');
INSERT INTO `z_module_vars` VALUES (63,'Theme','cssjscompress','b:0;');
INSERT INTO `z_module_vars` VALUES (64,'Theme','cssjsminify','b:0;');
INSERT INTO `z_module_vars` VALUES (65,'Theme','cssjscombine_lifetime','i:3600;');
INSERT INTO `z_module_vars` VALUES (66,'/PNConfig','debug','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (67,'/PNConfig','sitename','s:9:\"Site name\";');
INSERT INTO `z_module_vars` VALUES (68,'/PNConfig','slogan','s:16:\"Site description\";');
INSERT INTO `z_module_vars` VALUES (69,'/PNConfig','metakeywords','s:253:\"zikula, community, portal, portal web, open source, gpl, web site, website, weblog, blog, content management, content management system, web content management, web content management system, enterprise web content management, cms, application framework\";');
INSERT INTO `z_module_vars` VALUES (70,'/PNConfig','startdate','s:7:\"11/2009\";');
INSERT INTO `z_module_vars` VALUES (71,'/PNConfig','adminmail','s:19:\"example@example.com\";');
INSERT INTO `z_module_vars` VALUES (72,'/PNConfig','Default_Theme','s:9:\"andreas08\";');
INSERT INTO `z_module_vars` VALUES (73,'/PNConfig','anonymous','s:5:\"Guest\";');
INSERT INTO `z_module_vars` VALUES (74,'/PNConfig','timezone_offset','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (75,'/PNConfig','timezone_server','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (76,'/PNConfig','funtext','s:1:\"1\";');
INSERT INTO `z_module_vars` VALUES (77,'/PNConfig','reportlevel','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (78,'/PNConfig','startpage','s:4:\"Tour\";');
INSERT INTO `z_module_vars` VALUES (79,'/PNConfig','Version_Num','s:5:\"1.2.0\";');
INSERT INTO `z_module_vars` VALUES (80,'/PNConfig','Version_ID','s:6:\"Zikula\";');
INSERT INTO `z_module_vars` VALUES (81,'/PNConfig','Version_Sub','s:6:\"forest\";');
INSERT INTO `z_module_vars` VALUES (82,'/PNConfig','debug_sql','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (83,'/PNConfig','multilingual','s:1:\"1\";');
INSERT INTO `z_module_vars` VALUES (84,'/PNConfig','useflags','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (85,'/PNConfig','theme_change','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (86,'/PNConfig','UseCompression','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (87,'/PNConfig','timezone_info','a:38:{i:-12;s:31:\"(GMT -12:00 hours) Baker Island\";i:-11;s:39:\"(GMT -11:00 hours) Midway Island, Samoa\";i:-10;s:25:\"(GMT -10:00 hours) Hawaii\";s:4:\"-9.5\";s:34:\"(GMT -9:30 hours) French Polynesia\";i:-9;s:24:\"(GMT -9:00 hours) Alaska\";i:-8;s:44:\"(GMT -8:00 hours) Pacific Time (US & Canada)\";i:-7;s:45:\"(GMT -7:00 hours) Mountain Time (US & Canada)\";i:-6;s:57:\"(GMT -6:00 hours) Central Time (US & Canada), Mexico City\";i:-5;s:65:\"(GMT -5:00 hours) Eastern Time (US & Canada), Bogota, Lima, Quito\";i:-4;s:57:\"(GMT -4:00 hours) Atlantic Time (Canada), Caracas, La Paz\";s:4:\"-3.5\";s:30:\"(GMT -3:30 hours) Newfoundland\";i:-3;s:50:\"(GMT -3:00 hours) Brazil, Buenos Aires, Georgetown\";i:-2;s:30:\"(GMT -2:00 hours) Mid-Atlantic\";i:-1;s:44:\"(GMT -1:00 hours) Azores, Cape Verde Islands\";i:0;s:63:\"(GMT) Western Europe Time, London, Lisbon, Casablanca, Monrovia\";i:1;s:80:\"(GMT +1:00 hours) CET (Central Europe Time), Brussels, Copenhagen, Madrid, Paris\";i:2;s:70:\"(GMT +2:00 hours) EET (Eastern Europe Time), Kaliningrad, South Africa\";i:3;s:65:\"(GMT +3:00 hours) Baghdad, Kuwait, Riyadh, Moscow, St. Petersburg\";s:3:\"3.5\";s:24:\"(GMT +3:30 hours) Tehran\";i:4;s:50:\"(GMT +4:00 hours) Abu Dhabi, Muscat, Baku, Tbilisi\";s:3:\"4.5\";s:23:\"(GMT +4:30 hours) Kabul\";i:5;s:60:\"(GMT +5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent\";s:3:\"5.5\";s:53:\"(GMT +5:30 hours) Bombay, Calcutta, Madras, New Delhi\";s:4:\"5.75\";s:27:\"(GMT +5:45 hours) Kathmandu\";i:6;s:40:\"(GMT +6:00 hours) Almaty, Dhaka, Colombo\";s:3:\"6.5\";s:40:\"(GMT +6:30 hours) Cocos Islands, Myanmar\";i:7;s:41:\"(GMT +7:00 hours) Bangkok, Hanoi, Jakarta\";i:8;s:81:\"(GMT +8:00 hours) Beijing, Perth, Singapore, Hong Kong, Chongqing, Urumqi, Taipei\";i:9;s:55:\"(GMT +9:00 hours) Tokyo, Seoul, Osaka, Sapporo, Yakutsk\";s:3:\"9.5\";s:34:\"(GMT +9:30 hours) Adelaide, Darwin\";i:10;s:50:\"(GMT +10:00 hours) EAST (East Australian Standard)\";s:4:\"10.5\";s:52:\"(GMT +10:30 hours) Lord Howe Island (NSW, Australia)\";i:11;s:58:\"(GMT +11:00 hours) Magadan, Solomon Islands, New Caledonia\";s:4:\"11.5\";s:33:\"(GMT +11:30 hours) Norfolk Island\";i:12;s:73:\"(GMT +12:00 hours) Auckland, Wellington, Fiji, Kamchatka, Marshall Island\";s:5:\"12.75\";s:34:\"(GMT +12:45 hours) Chatham Islands\";i:13;s:51:\"(GMT +13:00 hours Tonga, Kiribati (Phoenix Islands)\";i:14;s:42:\"(GMT +14:00 hours) Kiribati (Line Islands)\";}');
INSERT INTO `z_module_vars` VALUES (88,'/PNConfig','errordisplay','i:1;');
INSERT INTO `z_module_vars` VALUES (89,'/PNConfig','errorlog','i:0;');
INSERT INTO `z_module_vars` VALUES (90,'/PNConfig','errorlogtype','i:0;');
INSERT INTO `z_module_vars` VALUES (91,'/PNConfig','errormailto','s:14:\"me@example.com\";');
INSERT INTO `z_module_vars` VALUES (92,'/PNConfig','siteoff','i:0;');
INSERT INTO `z_module_vars` VALUES (93,'/PNConfig','siteoffreason','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (94,'/PNConfig','starttype','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (95,'/PNConfig','startfunc','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (96,'/PNConfig','startargs','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (97,'/PNConfig','entrypoint','s:9:\"index.php\";');
INSERT INTO `z_module_vars` VALUES (98,'/PNConfig','language_detect','i:0;');
INSERT INTO `z_module_vars` VALUES (99,'/PNConfig','shorturls','b:0;');
INSERT INTO `z_module_vars` VALUES (100,'/PNConfig','shorturlstype','s:1:\"0\";');
INSERT INTO `z_module_vars` VALUES (101,'/PNConfig','shorturlsext','s:4:\"html\";');
INSERT INTO `z_module_vars` VALUES (102,'/PNConfig','shorturlsseparator','s:1:\"-\";');
INSERT INTO `z_module_vars` VALUES (103,'/PNConfig','shorturlsstripentrypoint','b:0;');
INSERT INTO `z_module_vars` VALUES (104,'/PNConfig','shorturlsdefaultmodule','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (105,'/PNConfig','profilemodule','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (106,'/PNConfig','messagemodule','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (107,'/PNConfig','languageurl','i:0;');
INSERT INTO `z_module_vars` VALUES (108,'/PNConfig','ajaxtimeout','i:5000;');
INSERT INTO `z_module_vars` VALUES (109,'/PNConfig','permasearch','s:161:\"À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü\";');
INSERT INTO `z_module_vars` VALUES (110,'/PNConfig','permareplace','s:114:\"A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,ss,ae,Ae,oe,Oe,ue,Ue\";');
INSERT INTO `z_module_vars` VALUES (111,'/PNConfig','language','s:3:\"eng\";');
INSERT INTO `z_module_vars` VALUES (112,'/PNConfig','locale','s:2:\"en\";');
INSERT INTO `z_module_vars` VALUES (113,'/PNConfig','language_i18n','s:2:\"en\";');
INSERT INTO `z_module_vars` VALUES (114,'/PNConfig','language_bc','i:1;');
INSERT INTO `z_module_vars` VALUES (115,'Admin_Messages','itemsperpage','i:25;');
INSERT INTO `z_module_vars` VALUES (116,'Admin_Messages','allowsearchinactive','b:0;');
INSERT INTO `z_module_vars` VALUES (117,'SecurityCenter','itemsperpage','i:10;');
INSERT INTO `z_module_vars` VALUES (118,'/PNConfig','enableanticracker','i:1;');
INSERT INTO `z_module_vars` VALUES (119,'/PNConfig','emailhackattempt','i:1;');
INSERT INTO `z_module_vars` VALUES (120,'/PNConfig','loghackattempttodb','i:1;');
INSERT INTO `z_module_vars` VALUES (121,'/PNConfig','onlysendsummarybyemail','i:1;');
INSERT INTO `z_module_vars` VALUES (122,'/PNConfig','updatecheck','i:1;');
INSERT INTO `z_module_vars` VALUES (123,'/PNConfig','updatefrequency','i:7;');
INSERT INTO `z_module_vars` VALUES (124,'/PNConfig','updatelastchecked','i:1257416815;');
INSERT INTO `z_module_vars` VALUES (125,'/PNConfig','updateversion','s:5:\"1.1.2\";');
INSERT INTO `z_module_vars` VALUES (126,'/PNConfig','keyexpiry','i:0;');
INSERT INTO `z_module_vars` VALUES (127,'/PNConfig','sessionauthkeyua','b:0;');
INSERT INTO `z_module_vars` VALUES (128,'/PNConfig','secure_domain','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (129,'/PNConfig','signcookies','i:1;');
INSERT INTO `z_module_vars` VALUES (130,'/PNConfig','signingkey','s:40:\"f36182e357c69a2d86ba3771df8210ea4a628d78\";');
INSERT INTO `z_module_vars` VALUES (131,'/PNConfig','seclevel','s:6:\"Medium\";');
INSERT INTO `z_module_vars` VALUES (132,'/PNConfig','secmeddays','i:7;');
INSERT INTO `z_module_vars` VALUES (133,'/PNConfig','secinactivemins','i:20;');
INSERT INTO `z_module_vars` VALUES (134,'/PNConfig','sessionstoretofile','i:0;');
INSERT INTO `z_module_vars` VALUES (135,'/PNConfig','sessionsavepath','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (136,'/PNConfig','gc_probability','i:100;');
INSERT INTO `z_module_vars` VALUES (137,'/PNConfig','anonymoussessions','i:1;');
INSERT INTO `z_module_vars` VALUES (138,'/PNConfig','sessionrandregenerate','b:1;');
INSERT INTO `z_module_vars` VALUES (139,'/PNConfig','sessionregenerate','b:1;');
INSERT INTO `z_module_vars` VALUES (140,'/PNConfig','sessionregeneratefreq','i:10;');
INSERT INTO `z_module_vars` VALUES (141,'/PNConfig','sessionipcheck','i:0;');
INSERT INTO `z_module_vars` VALUES (142,'/PNConfig','sessionname','s:4:\"ZSID\";');
INSERT INTO `z_module_vars` VALUES (143,'/PNConfig','filtergetvars','i:1;');
INSERT INTO `z_module_vars` VALUES (144,'/PNConfig','filterpostvars','i:1;');
INSERT INTO `z_module_vars` VALUES (145,'/PNConfig','filtercookievars','i:1;');
INSERT INTO `z_module_vars` VALUES (146,'/PNConfig','outputfilter','i:1;');
INSERT INTO `z_module_vars` VALUES (147,'/PNConfig','summarycontent','s:1155:\"For the attention of %sitename% administration staff:\r\n\r\nOn %date% at %time%, Zikula detected that somebody tried to interact with the site in a way that may have been intended compromise its security. This is not necessarily the case: it could have been caused by work you were doing on the site, or may have been due to some other reason. In any case, it was detected and blocked. \r\n\r\nThe suspicious activity was recognised in \'%filename%\' at line %linenumber%.\r\n\r\nType: %type%. \r\n\r\nAdditional information: %additionalinfo%.\r\n\r\nBelow is logged information that may help you identify what happened and who was responsible.\r\n\r\n=====================================\r\nInformation about the user:\r\n=====================================\r\nUser name:  %username%\r\nUser\'s e-mail address: %useremail%\r\nUser\'s real name: %userrealname%\r\n\r\n=====================================\r\nIP numbers (if this was a cracker, the IP numbers may not be the true point of origin)\r\n=====================================\r\nIP according to HTTP_CLIENT_IP: %httpclientip%\r\nIP according to REMOTE_ADDR: %remoteaddr%\r\nIP according to GetHostByName($REMOTE_ADDR): %gethostbyremoteaddr%\r\n\";');
INSERT INTO `z_module_vars` VALUES (148,'/PNConfig','fullcontent','s:1336:\"=====================================\r\nInformation in the $_REQUEST array\r\n=====================================\r\n%requestarray%\r\n\r\n=====================================\r\nInformation in the $_GET array\r\n(variables that may have been in the URL string or in a \'GET\'-type form)\r\n=====================================\r\n%getarray%\r\n\r\n=====================================\r\nInformation in the $_POST array\r\n(visible and invisible form elements)\r\n=====================================\r\n%postarray%\r\n\r\n=====================================\r\nBrowser information\r\n=====================================\r\n%browserinfo%\r\n\r\n=====================================\r\nInformation in the $_SERVER array\r\n=====================================\r\n%serverarray%\r\n\r\n=====================================\r\nInformation in the $_ENV array\r\n=====================================\r\n%envarray%\r\n\r\n=====================================\r\nInformation in the $_COOKIE array\r\n=====================================\r\n%cookiearray%\r\n\r\n=====================================\r\nInformation in the $_FILES array\r\n=====================================\r\n%filearray%\r\n\r\n=====================================\r\nInformation in the $_SESSION array\r\n(session information -- variables starting with PNSV are Zikula session variables)\r\n=====================================\r\n%sessionarray%\r\n\";');
INSERT INTO `z_module_vars` VALUES (149,'/PNConfig','usehtaccessbans','i:0;');
INSERT INTO `z_module_vars` VALUES (150,'/PNConfig','extrapostprotection','i:0;');
INSERT INTO `z_module_vars` VALUES (151,'/PNConfig','extragetprotection','i:0;');
INSERT INTO `z_module_vars` VALUES (152,'/PNConfig','checkmultipost','i:0;');
INSERT INTO `z_module_vars` VALUES (153,'/PNConfig','maxmultipost','i:4;');
INSERT INTO `z_module_vars` VALUES (154,'/PNConfig','cpuloadmonitor','i:0;');
INSERT INTO `z_module_vars` VALUES (155,'/PNConfig','cpumaxload','d:10;');
INSERT INTO `z_module_vars` VALUES (156,'/PNConfig','ccisessionpath','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (157,'/PNConfig','htaccessfilelocation','s:9:\".htaccess\";');
INSERT INTO `z_module_vars` VALUES (158,'/PNConfig','nocookiebanthreshold','i:10;');
INSERT INTO `z_module_vars` VALUES (159,'/PNConfig','nocookiewarningthreshold','i:2;');
INSERT INTO `z_module_vars` VALUES (160,'/PNConfig','fastaccessbanthreshold','i:40;');
INSERT INTO `z_module_vars` VALUES (161,'/PNConfig','fastaccesswarnthreshold','i:10;');
INSERT INTO `z_module_vars` VALUES (162,'/PNConfig','javababble','i:0;');
INSERT INTO `z_module_vars` VALUES (163,'/PNConfig','javaencrypt','i:0;');
INSERT INTO `z_module_vars` VALUES (164,'/PNConfig','preservehead','i:0;');
INSERT INTO `z_module_vars` VALUES (165,'/PNConfig','filterarrays','i:1;');
INSERT INTO `z_module_vars` VALUES (166,'/PNConfig','htmlentities','s:1:\"1\";');
INSERT INTO `z_module_vars` VALUES (167,'/PNConfig','AllowableHTML','a:83:{s:3:\"!--\";s:1:\"2\";s:1:\"a\";s:1:\"2\";s:4:\"abbr\";s:1:\"0\";s:7:\"acronym\";s:1:\"0\";s:7:\"address\";s:1:\"0\";s:6:\"applet\";s:1:\"0\";s:4:\"area\";s:1:\"0\";s:1:\"b\";s:1:\"2\";s:4:\"base\";s:1:\"0\";s:8:\"basefont\";s:1:\"0\";s:3:\"bdo\";s:1:\"0\";s:3:\"big\";s:1:\"0\";s:10:\"blockquote\";s:1:\"2\";s:2:\"br\";s:1:\"2\";s:6:\"button\";s:1:\"0\";s:7:\"caption\";s:1:\"0\";s:6:\"center\";s:1:\"2\";s:4:\"cite\";s:1:\"0\";s:4:\"code\";s:1:\"0\";s:3:\"col\";s:1:\"0\";s:8:\"colgroup\";s:1:\"0\";s:3:\"del\";s:1:\"0\";s:3:\"dfn\";s:1:\"0\";s:3:\"dir\";s:1:\"0\";s:3:\"div\";s:1:\"2\";s:2:\"dl\";s:1:\"1\";s:2:\"dd\";s:1:\"1\";s:2:\"dt\";s:1:\"1\";s:2:\"em\";s:1:\"2\";s:5:\"embed\";s:1:\"0\";s:8:\"fieldset\";s:1:\"0\";s:4:\"font\";i:0;s:4:\"form\";s:1:\"0\";s:2:\"h1\";s:1:\"1\";s:2:\"h2\";s:1:\"1\";s:2:\"h3\";s:1:\"1\";s:2:\"h4\";s:1:\"1\";s:2:\"h5\";s:1:\"1\";s:2:\"h6\";s:1:\"1\";s:2:\"hr\";s:1:\"2\";s:1:\"i\";s:1:\"2\";s:6:\"iframe\";s:1:\"0\";s:3:\"img\";i:0;s:5:\"input\";s:1:\"0\";s:3:\"ins\";s:1:\"0\";s:3:\"kbd\";s:1:\"0\";s:5:\"label\";s:1:\"0\";s:6:\"legend\";s:1:\"0\";s:2:\"li\";s:1:\"2\";s:3:\"map\";s:1:\"0\";s:7:\"marquee\";i:0;s:4:\"menu\";s:1:\"0\";s:4:\"nobr\";s:1:\"0\";s:6:\"object\";s:1:\"0\";s:2:\"ol\";s:1:\"2\";s:8:\"optgroup\";s:1:\"0\";s:6:\"option\";s:1:\"0\";s:1:\"p\";s:1:\"2\";s:5:\"param\";s:1:\"0\";s:3:\"pre\";s:1:\"2\";s:1:\"q\";s:1:\"0\";s:1:\"s\";s:1:\"0\";s:4:\"samp\";s:1:\"0\";s:6:\"script\";s:1:\"0\";s:6:\"select\";s:1:\"0\";s:5:\"small\";s:1:\"0\";s:4:\"span\";i:0;s:6:\"strike\";s:1:\"0\";s:6:\"strong\";s:1:\"2\";s:3:\"sub\";s:1:\"0\";s:3:\"sup\";s:1:\"0\";s:5:\"table\";s:1:\"2\";s:5:\"tbody\";s:1:\"0\";s:2:\"td\";s:1:\"2\";s:8:\"textarea\";s:1:\"0\";s:5:\"tfoot\";s:1:\"0\";s:2:\"th\";s:1:\"2\";s:5:\"thead\";s:1:\"0\";s:2:\"tr\";s:1:\"2\";s:2:\"tt\";s:1:\"2\";s:1:\"u\";s:1:\"0\";s:2:\"ul\";s:1:\"2\";s:3:\"var\";s:1:\"0\";}');
INSERT INTO `z_module_vars` VALUES (168,'Categories','userrootcat','s:17:\"/__SYSTEM__/Users\";');
INSERT INTO `z_module_vars` VALUES (169,'Categories','allowusercatedit','i:0;');
INSERT INTO `z_module_vars` VALUES (170,'Categories','autocreateusercat','i:0;');
INSERT INTO `z_module_vars` VALUES (171,'Categories','autocreateuserdefaultcat','i:0;');
INSERT INTO `z_module_vars` VALUES (172,'Categories','userdefaultcatname','s:7:\"Default\";');
INSERT INTO `z_module_vars` VALUES (173,'legal','termsofuse','b:1;');
INSERT INTO `z_module_vars` VALUES (174,'legal','privacypolicy','b:1;');
INSERT INTO `z_module_vars` VALUES (175,'legal','accessibilitystatement','b:1;');
INSERT INTO `z_module_vars` VALUES (176,'Mailer','mailertype','i:1;');
INSERT INTO `z_module_vars` VALUES (177,'Mailer','charset','s:5:\"utf-8\";');
INSERT INTO `z_module_vars` VALUES (178,'Mailer','encoding','s:4:\"8bit\";');
INSERT INTO `z_module_vars` VALUES (179,'Mailer','html','b:0;');
INSERT INTO `z_module_vars` VALUES (180,'Mailer','wordwrap','i:50;');
INSERT INTO `z_module_vars` VALUES (181,'Mailer','msmailheaders','b:0;');
INSERT INTO `z_module_vars` VALUES (182,'Mailer','sendmailpath','s:18:\"/usr/sbin/sendmail\";');
INSERT INTO `z_module_vars` VALUES (183,'Mailer','smtpauth','b:0;');
INSERT INTO `z_module_vars` VALUES (184,'Mailer','smtpserver','s:9:\"localhost\";');
INSERT INTO `z_module_vars` VALUES (185,'Mailer','smtpport','i:25;');
INSERT INTO `z_module_vars` VALUES (186,'Mailer','smtptimeout','i:10;');
INSERT INTO `z_module_vars` VALUES (187,'Mailer','smtpusername','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (188,'Mailer','smtppassword','s:0:\"\";');
INSERT INTO `z_module_vars` VALUES (189,'pnRender','compile_check','b:1;');
INSERT INTO `z_module_vars` VALUES (190,'pnRender','force_compile','b:0;');
INSERT INTO `z_module_vars` VALUES (191,'pnRender','cache','b:0;');
INSERT INTO `z_module_vars` VALUES (192,'pnRender','expose_template','b:0;');
INSERT INTO `z_module_vars` VALUES (193,'pnRender','lifetime','i:3600;');
INSERT INTO `z_module_vars` VALUES (194,'Search','itemsperpage','i:10;');
INSERT INTO `z_module_vars` VALUES (195,'Search','limitsummary','i:255;');
INSERT INTO `z_module_vars` VALUES (196,'/PNConfig','log_last_rotate','i:1257416813;');
DROP TABLE IF EXISTS `z_modules`;
CREATE TABLE `z_modules` (
  `pn_id` int(11) NOT NULL AUTO_INCREMENT,
  `pn_name` varchar(64) NOT NULL DEFAULT '',
  `pn_type` tinyint(4) NOT NULL DEFAULT '0',
  `pn_displayname` varchar(64) NOT NULL DEFAULT '',
  `pn_url` varchar(64) NOT NULL DEFAULT '',
  `pn_description` varchar(255) NOT NULL DEFAULT '',
  `pn_regid` int(11) NOT NULL DEFAULT '0',
  `pn_directory` varchar(64) NOT NULL DEFAULT '',
  `pn_version` varchar(10) NOT NULL DEFAULT '0',
  `pn_official` tinyint(4) NOT NULL DEFAULT '0',
  `pn_author` varchar(255) NOT NULL DEFAULT '',
  `pn_contact` varchar(255) NOT NULL DEFAULT '',
  `pn_admin_capable` tinyint(4) NOT NULL DEFAULT '0',
  `pn_user_capable` tinyint(4) NOT NULL DEFAULT '0',
  `pn_profile_capable` tinyint(4) NOT NULL DEFAULT '0',
  `pn_message_capable` tinyint(4) NOT NULL DEFAULT '0',
  `pn_state` smallint(6) NOT NULL DEFAULT '0',
  `pn_credits` varchar(255) NOT NULL DEFAULT '',
  `pn_changelog` varchar(255) NOT NULL DEFAULT '',
  `pn_help` varchar(255) NOT NULL DEFAULT '',
  `pn_license` varchar(255) NOT NULL DEFAULT '',
  `pn_securityschema` text,
  PRIMARY KEY (`pn_id`),
  KEY `state` (`pn_state`),
  KEY `mod_state` (`pn_name`,`pn_state`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;

INSERT INTO `z_modules` VALUES (1,'Modules',3,'Modules manager','modules','Provides support for modules, and incorporates an interface for adding, removing and administering core system modules and add-on modules.',0,'Modules','3.6',1,'Jim McDonald, Mark West','http://www.zikula.org',1,0,0,0,3,'','','','','a:1:{s:9:\"Modules::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (2,'Admin',3,'Admin panel manager','adminpanel','Provides the site\'s administration panel, and the ability to configure and manage it.',0,'Admin','1.8',1,'Mark West','http://www.markwest.me.uk/',1,0,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:1:{s:7:\"Admin::\";s:38:\"Admin Category name::Admin Category ID\";}');
INSERT INTO `z_modules` VALUES (3,'Admin_Messages',3,'Admin messages manager','adminmessages','Provides a means of publishing, editing and scheduling special announcements from the site administrator.',0,'Admin_Messages','2.2',1,'Mark West','http://www.markwest.me.uk',1,0,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:1:{s:16:\"Admin_Messages::\";s:25:\"message title::message id\";}');
INSERT INTO `z_modules` VALUES (4,'AuthPN',3,'Authentication manager','authpn','Provides the ability to employ non-core user-authentication systems within a site, notably LDAP or OpenID.',0,'AuthPN','1.1',1,'Mark West','http://www.markwest.me.uk/',1,0,0,0,1,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:0:{}');
INSERT INTO `z_modules` VALUES (5,'Blocks',3,'Blocks manager','blocksmanager','Provides an interface for adding, removing and administering the site\'s side and centre blocks.',0,'Blocks','3.6',1,'Jim McDonald, Mark West','http://www.mcdee.net/, http://www.markwest.me.uk/',1,1,0,0,3,'','','','','a:2:{s:8:\"Blocks::\";s:30:\"Block key:Block title:Block ID\";s:16:\"Blocks::position\";s:26:\"Position name::Position ID\";}');
INSERT INTO `z_modules` VALUES (6,'Categories',3,'Categories manager','categories','Provides support for categorisation of content in other modules, and an interface for adding, removing and administering categories.',0,'Categories','1.1',1,'Robert Gasch','rgasch@gmail.com',1,1,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:1:{s:20:\"Categories::Category\";s:40:\"Category ID:Category Path:Category IPath\";}');
INSERT INTO `z_modules` VALUES (7,'Errors',3,'Error logger','errorlogger','Provides the core system of the site with error-logging capability.',0,'Errors','1.1',1,'Brian Lindner <Furbo>','furbo@sigtauonline.com',0,1,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:1:{s:8:\"Errors::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (8,'Groups',3,'Groups manager','groupsmanager','Provides support for user groups, and incorporates an interface for adding, removing and administering them.',0,'Groups','2.3',1,'Mark West, Franky Chestnut, Michael Halbook','http://www.markwest.me.uk/, http://dev.pnconcept.com, http://www.halbrooktech.com',1,1,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:1:{s:8:\"Groups::\";s:10:\"Group ID::\";}');
INSERT INTO `z_modules` VALUES (9,'Header_Footer',3,'Header and footer handler','headerfooter','Provides the header and footer portions of legacy themes and other non-Xanthia themes.',0,'Header_Footer','1.1',1,'Mark West','http://www.markwest.me.uk/',0,1,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:0:{}');
INSERT INTO `z_modules` VALUES (10,'Mailer',3,'Mailer','mailer','Provides mail-sending functionality for communication with the site\'s users, and an interface for managing the e-mail service settings used by the mailer.',0,'Mailer','1.3',1,'Mark West','http://www.markwest.me.uk/',1,0,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:1:{s:8:\"Mailer::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (11,'ObjectData',3,'Object data manager','objectdata','Provides a framework for implementing and managing object-model data items, for use by other modules and applications.',0,'ObjectData','1.03',0,'Robert Gasch','rgasch@gmail.com',0,0,0,0,3,'docs/credits.txt','docs/changelog.txt','docs/help.txt','docs/license.txt','a:1:{s:12:\"ObjectData::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (12,'PageLock',3,'Page lock manager','pagelock','Provides the ability to lock pages when they are in use, for content and access control.',0,'PageLock','1.1',1,'Jorn Wildt','http://www.elfisk.dk',0,0,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/install.txt','pndocs/license.txt','a:1:{s:10:\"PageLock::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (13,'Permissions',3,'Permission rules manager','permissions','Provides an interface for fine-grained management of accessibility of the site\'s functionality and content through permission rules.',0,'Permissions','1.1',1,'Jim McDonald, M.Maes','http://www.mcdee.net/, http://www.mmaes.com',1,0,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/install.txt','pndocs/license.txt','a:1:{s:13:\"Permissions::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (14,'pnForm',3,'Forms manager','pnforms','Provides a framework for standardised presentation of the site\'s forms.',0,'pnForm','1.1',1,'The Zikula development team','http://www.zikula.org/',0,0,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:1:{s:10:\"pnRender::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (15,'pnRender',3,'Rendering engine','pnrender','Provides the core system with a Smarty-based engine to control content rendering and presentation.',0,'pnRender','1.1',1,'The Zikula development team','http://www.zikula.org/',1,0,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:1:{s:10:\"pnRender::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (16,'Search',3,'Site search engine','search','Provides an engine for searching within the site, and an interface for managing search page settings.',0,'Search','1.5',1,'Patrick Kellum','http://www.ctarl-ctarl.com',1,1,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/install.txt','pndocs/license.txt','a:1:{s:8:\"Search::\";s:13:\"Module name::\";}');
INSERT INTO `z_modules` VALUES (17,'SecurityCenter',3,'Security centre','securitycenter','Provides the ability to manage site security. It logs attempted hacks and similar events, and incorporates a user interface for customising alerting and security settings.',0,'SecurityCenter','1.3',1,'Mark West','http://www.markwest.me.uk',1,0,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:1:{s:16:\"SecurityCenter::\";s:16:\"hackid::hacktime\";}');
INSERT INTO `z_modules` VALUES (18,'Settings',3,'General settings manager','settings','Provides an interface for managing the site\'s general settings, i.e. ownership information, site start page settings, multi-lingual settings, error reporting options, and various other features that are not administered within other modules.',0,'Settings','2.9',1,'Simon Wunderlin','',1,0,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:1:{s:10:\"Settings::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (19,'SysInfo',3,'System info panel','sysinfo','Provides detailed information reports about the system configuration and environment, for tracking and troubleshooting purposes.',0,'SysInfo','1.1',1,'Simon Birtwistle','hammerhead@zikula.org',1,0,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:1:{s:9:\"SysInfo::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (20,'Theme',3,'Themes manager','theme','Provides the site\'s theming system, and an interface for managing themes, to control the site\'s presentation and appearance.',0,'Theme','3.3',1,'Mark West','http://www.markwest.me.uk/',1,1,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:1:{s:7:\"Theme::\";s:12:\"Theme name::\";}');
INSERT INTO `z_modules` VALUES (21,'Users',3,'Users manager','users','Provides an interface for configuring and administering registered user accounts. Incorporates all needed functionality, but can work in close unison with the third party profile module configured in the general settings of the site.',0,'Users','1.13',1,'Xiaoyu Huang, Drak','class007@sina.com, drak@zikula.org',1,1,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/help.txt','pndocs/license.txt','a:2:{s:7:\"Users::\";s:14:\"Uname::User ID\";s:16:\"Users::MailUsers\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (22,'Workflow',3,'Workflow manager','workflow','Provides a workflow engine, and an interface for designing and administering workflows comprised of actions and events.',0,'Workflow','1.1',1,'Drak','drak@hostnuke.com',0,0,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/manual.html','pndocs/copying.txt','a:1:{s:10:\"Workflow::\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (25,'Ephemerids',2,'Ephemerids ','ephemerids','Provides a block displaying an information byte (historical event, thought for the day, etc.) linked to the day\'s date, with daily roll-over, and incorporates an interface for adding, editing and maintaining ephemerids.',0,'Ephemerids','1.7',1,'Mark West','http://www.markwest.me.uk',1,0,0,0,1,'','','','','a:1:{s:12:\"Ephemerids::\";s:14:\"::Ephemerid ID\";}');
INSERT INTO `z_modules` VALUES (29,'legal',2,'Legal info manager','legalmod','Provides an interface for managing the site\'s \'Terms of use\', \'Privacy statement\' and \'Accessibility statement\'.',0,'legal','1.3',1,'Michael M. Wechsler','michael@thelaw.com',1,1,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/install.txt','pndocs/license.txt','a:4:{s:7:\"legal::\";s:2:\"::\";s:17:\"legal::termsofuse\";s:2:\"::\";s:14:\"legal::privacy\";s:2:\"::\";s:29:\"legal::accessibilitystatement\";s:2:\"::\";}');
INSERT INTO `z_modules` VALUES (37,'Tour',2,'Tour','tour','First time configuration and Zikula Tour.',0,'Tour','1.2',1,'Simon Birtwistle','http://www.itbegins.co.uk',0,1,0,0,3,'pndocs/credits.txt','pndocs/changelog.txt','pndocs/install.txt','pndocs/license.txt','a:0:{}');
DROP TABLE IF EXISTS `z_objectdata_attributes`;
CREATE TABLE `z_objectdata_attributes` (
  `oba_id` int(11) NOT NULL AUTO_INCREMENT,
  `oba_attribute_name` varchar(80) NOT NULL DEFAULT '',
  `oba_object_id` int(11) NOT NULL DEFAULT '0',
  `oba_object_type` varchar(80) NOT NULL DEFAULT '',
  `oba_value` text,
  `oba_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `oba_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `oba_cr_uid` int(11) NOT NULL DEFAULT '0',
  `oba_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `oba_lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`oba_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

INSERT INTO `z_objectdata_attributes` VALUES (1,'code',5,'categories_category','Y','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_objectdata_attributes` VALUES (2,'code',6,'categories_category','N','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_objectdata_attributes` VALUES (3,'code',11,'categories_category','P','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_objectdata_attributes` VALUES (4,'code',12,'categories_category','C','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_objectdata_attributes` VALUES (5,'code',13,'categories_category','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_objectdata_attributes` VALUES (6,'code',14,'categories_category','O','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_objectdata_attributes` VALUES (7,'code',15,'categories_category','R','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_objectdata_attributes` VALUES (8,'code',17,'categories_category','M','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_objectdata_attributes` VALUES (9,'code',18,'categories_category','F','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_objectdata_attributes` VALUES (10,'code',26,'categories_category','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_objectdata_attributes` VALUES (11,'code',27,'categories_category','I','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_objectdata_attributes` VALUES (12,'code',29,'categories_category','P','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
INSERT INTO `z_objectdata_attributes` VALUES (13,'code',30,'categories_category','A','A','2009-11-05 16:11:24',0,'2009-11-05 16:11:24',0);
DROP TABLE IF EXISTS `z_objectdata_log`;
CREATE TABLE `z_objectdata_log` (
  `obl_id` int(11) NOT NULL AUTO_INCREMENT,
  `obl_object_type` varchar(80) NOT NULL DEFAULT '',
  `obl_object_id` int(11) NOT NULL DEFAULT '0',
  `obl_op` varchar(16) NOT NULL DEFAULT '',
  `obl_diff` text,
  `obl_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `obl_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `obl_cr_uid` int(11) NOT NULL DEFAULT '0',
  `obl_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `obl_lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`obl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_objectdata_meta`;
CREATE TABLE `z_objectdata_meta` (
  `obm_id` int(11) NOT NULL AUTO_INCREMENT,
  `obm_module` varchar(40) NOT NULL DEFAULT '',
  `obm_table` varchar(40) NOT NULL DEFAULT '',
  `obm_idcolumn` varchar(40) NOT NULL DEFAULT '',
  `obm_obj_id` int(11) NOT NULL DEFAULT '0',
  `obm_permissions` varchar(255) DEFAULT '',
  `obm_dc_title` varchar(80) DEFAULT '',
  `obm_dc_author` varchar(80) DEFAULT '',
  `obm_dc_subject` varchar(255) DEFAULT '',
  `obm_dc_keywords` varchar(128) DEFAULT '',
  `obm_dc_description` varchar(255) DEFAULT '',
  `obm_dc_publisher` varchar(128) DEFAULT '',
  `obm_dc_contributor` varchar(128) DEFAULT '',
  `obm_dc_startdate` datetime DEFAULT '1970-01-01 00:00:00',
  `obm_dc_enddate` datetime DEFAULT '1970-01-01 00:00:00',
  `obm_dc_type` varchar(128) DEFAULT '',
  `obm_dc_format` varchar(128) DEFAULT '',
  `obm_dc_uri` varchar(255) DEFAULT '',
  `obm_dc_source` varchar(128) DEFAULT '',
  `obm_dc_language` varchar(32) DEFAULT '',
  `obm_dc_relation` varchar(255) DEFAULT '',
  `obm_dc_coverage` varchar(64) DEFAULT '',
  `obm_dc_entity` varchar(64) DEFAULT '',
  `obm_dc_comment` varchar(255) DEFAULT '',
  `obm_dc_extra` varchar(255) DEFAULT '',
  `obm_obj_status` varchar(1) NOT NULL DEFAULT 'A',
  `obm_cr_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `obm_cr_uid` int(11) NOT NULL DEFAULT '0',
  `obm_lu_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `obm_lu_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`obm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_pagelock`;
CREATE TABLE `z_pagelock` (
  `plock_id` int(11) NOT NULL AUTO_INCREMENT,
  `plock_name` varchar(100) NOT NULL DEFAULT '',
  `plock_cdate` datetime NOT NULL,
  `plock_edate` datetime NOT NULL,
  `plock_session` varchar(50) NOT NULL,
  `plock_title` varchar(100) NOT NULL,
  `plock_ipno` varchar(30) NOT NULL,
  PRIMARY KEY (`plock_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_sc_anticracker`;
CREATE TABLE `z_sc_anticracker` (
  `pn_hid` int(11) NOT NULL AUTO_INCREMENT,
  `pn_hacktime` varchar(20) DEFAULT NULL,
  `pn_hackfile` varchar(255) DEFAULT '',
  `pn_hackline` int(11) DEFAULT NULL,
  `pn_hacktype` varchar(255) DEFAULT '',
  `pn_hackinfo` longtext,
  `pn_userid` int(11) DEFAULT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_sc_log_event`;
CREATE TABLE `z_sc_log_event` (
  `lge_id` int(11) NOT NULL AUTO_INCREMENT,
  `lge_date` datetime DEFAULT NULL,
  `lge_uid` int(11) DEFAULT NULL,
  `lge_component` varchar(64) DEFAULT NULL,
  `lge_module` varchar(64) DEFAULT NULL,
  `lge_type` varchar(64) DEFAULT NULL,
  `lge_function` varchar(64) DEFAULT NULL,
  `lge_sec_component` varchar(64) DEFAULT NULL,
  `lge_sec_instance` varchar(64) DEFAULT NULL,
  `lge_sec_permission` varchar(64) DEFAULT NULL,
  `lge_message` varchar(255) DEFAULT '',
  PRIMARY KEY (`lge_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_search_result`;
CREATE TABLE `z_search_result` (
  `sres_id` int(11) NOT NULL AUTO_INCREMENT,
  `sres_title` varchar(255) NOT NULL DEFAULT '',
  `sres_text` longtext,
  `sres_module` varchar(100) DEFAULT NULL,
  `sres_extra` varchar(100) DEFAULT NULL,
  `sres_created` datetime DEFAULT NULL,
  `sres_found` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sres_sesid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`sres_id`),
  KEY `title` (`sres_title`),
  KEY `module` (`sres_module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_search_stat`;
CREATE TABLE `z_search_stat` (
  `pn_id` int(11) NOT NULL AUTO_INCREMENT,
  `pn_search` varchar(50) NOT NULL DEFAULT '',
  `pn_count` int(11) NOT NULL DEFAULT '0',
  `pn_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pn_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_session_info`;
CREATE TABLE `z_session_info` (
  `pn_sessid` varchar(40) NOT NULL DEFAULT '',
  `pn_ipaddr` varchar(32) NOT NULL DEFAULT '',
  `pn_lastused` datetime DEFAULT '1970-01-01 00:00:00',
  `pn_uid` int(11) DEFAULT '0',
  `pn_remember` tinyint(4) NOT NULL DEFAULT '0',
  `pn_vars` longtext NOT NULL,
  PRIMARY KEY (`pn_sessid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_themes`;
CREATE TABLE `z_themes` (
  `pn_id` int(11) NOT NULL AUTO_INCREMENT,
  `pn_name` varchar(64) NOT NULL DEFAULT '',
  `pn_type` tinyint(4) NOT NULL DEFAULT '0',
  `pn_displayname` varchar(64) NOT NULL DEFAULT '',
  `pn_description` varchar(255) NOT NULL DEFAULT '',
  `pn_regid` int(11) NOT NULL DEFAULT '0',
  `pn_directory` varchar(64) NOT NULL DEFAULT '',
  `pn_version` varchar(10) NOT NULL DEFAULT '0',
  `pn_official` tinyint(4) NOT NULL DEFAULT '0',
  `pn_author` varchar(255) NOT NULL DEFAULT '',
  `pn_contact` varchar(255) NOT NULL DEFAULT '',
  `pn_admin` tinyint(4) NOT NULL DEFAULT '0',
  `pn_user` tinyint(4) NOT NULL DEFAULT '0',
  `pn_system` tinyint(4) NOT NULL DEFAULT '0',
  `pn_state` tinyint(4) NOT NULL DEFAULT '0',
  `pn_credits` varchar(255) NOT NULL DEFAULT '',
  `pn_changelog` varchar(255) NOT NULL DEFAULT '',
  `pn_help` varchar(255) NOT NULL DEFAULT '',
  `pn_license` varchar(255) NOT NULL DEFAULT '',
  `pn_xhtml` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`pn_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

INSERT INTO `z_themes` VALUES (1,'Andreas08',3,'Andreas08','The Andreas08 theme is a very good template for light CSS-compatible browser-oriented themes.',0,'andreas08','1.1',0,'David Brucas, Mark West, Andreas Viklund','http://dbrucas.povpromotions.com, http://www.markwest.me.uk, http://www.andreasviklund.com',1,1,0,1,'','','','',1);
INSERT INTO `z_themes` VALUES (2,'Atom',3,'Atom','The Atom theme is an auxiliary theme specially designed for rendering pages in Atom mark-up.',0,'Atom','1.0',0,'Franz Skaaning','http://www.lexebus.net/',0,0,1,1,'','','','',0);
INSERT INTO `z_themes` VALUES (3,'Printer',3,'Printer','The Printer theme is an auxiliary theme designed specially for outputting pages in a printer-friendly format.',0,'Printer','2.0',0,'Mark West','http://www.markwest.me.uk',0,0,1,1,'','','','',1);
INSERT INTO `z_themes` VALUES (4,'RSS',3,'RSS','The RSS theme is an auxiliary theme designed specially for outputting pages as an RSS feed.',0,'rss','1.0',0,'Mark West','http://www.markwest.me.uk',0,0,1,1,'docs/credits.txt','docs/changelog.txt','docs/help.txt','docs/license.txt',0);
INSERT INTO `z_themes` VALUES (5,'SeaBreeze',3,'SeaBreeze','The SeaBreeze theme is a browser-oriented theme, and was updated for the release of Zikula 1.0, with revised colours and new graphics.',0,'SeaBreeze','3.1',0,'Carsten Volmer, Vanessa Haakenson, Mark West, Martin Andersen','http://www.zikula.org',0,1,0,1,'','','','',1);
INSERT INTO `z_themes` VALUES (6,'VoodooDolly',3,'VoodooDolly','The VoodooDolly theme is a conservative browser-oriented theme with a Web 2.0 look and feel.',0,'voodoodolly','1.0',0,'Mark West, pogy366','http://www.markwest.me.uk, http://www.dbfnetwork.info/rayk/index.html',0,1,0,1,'','','','',1);
DROP TABLE IF EXISTS `z_userblocks`;
CREATE TABLE `z_userblocks` (
  `pn_uid` int(11) NOT NULL DEFAULT '0',
  `pn_bid` int(11) NOT NULL DEFAULT '0',
  `pn_active` tinyint(4) NOT NULL DEFAULT '1',
  `pn_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `bid_uid_idx` (`pn_uid`,`pn_bid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_users`;
CREATE TABLE `z_users` (
  `pn_uid` int(11) NOT NULL AUTO_INCREMENT,
  `pn_uname` varchar(25) NOT NULL DEFAULT '',
  `pn_email` varchar(60) NOT NULL DEFAULT '',
  `pn_user_regdate` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `pn_user_viewemail` smallint(6) DEFAULT '0',
  `pn_user_theme` varchar(64) DEFAULT '',
  `pn_pass` varchar(128) NOT NULL DEFAULT '',
  `pn_storynum` int(4) NOT NULL DEFAULT '10',
  `pn_ublockon` tinyint(4) NOT NULL DEFAULT '0',
  `pn_ublock` text,
  `pn_theme` varchar(255) NOT NULL DEFAULT '',
  `pn_counter` int(11) NOT NULL DEFAULT '0',
  `pn_activated` tinyint(4) NOT NULL DEFAULT '0',
  `pn_lastlogin` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `pn_validfrom` int(11) NOT NULL DEFAULT '0',
  `pn_validuntil` int(11) NOT NULL DEFAULT '0',
  `pn_hash_method` tinyint(4) NOT NULL DEFAULT '8',
  PRIMARY KEY (`pn_uid`),
  KEY `uname` (`pn_uname`),
  KEY `email` (`pn_email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO `z_users` VALUES (1,'guest','','1970-01-01',0,'','',10,0,'','',0,1,'1970-01-01',0,0,1);
INSERT INTO `z_users` VALUES (2,'admin','example@example.com','2009-11-05 16:11:45',0,'','e7cf3ef4f17c3999a94f2c6f612e8a888e5b1026878e4e19398b23bd38ec221a',10,0,'','',0,1,'2009-11-05 16:11:45',0,0,8);
DROP TABLE IF EXISTS `z_users_temp`;
CREATE TABLE `z_users_temp` (
  `pn_tid` int(11) NOT NULL AUTO_INCREMENT,
  `pn_uname` varchar(25) NOT NULL DEFAULT '',
  `pn_email` varchar(60) NOT NULL DEFAULT '',
  `pn_femail` tinyint(4) NOT NULL DEFAULT '0',
  `pn_pass` varchar(128) NOT NULL DEFAULT '',
  `pn_dynamics` longtext NOT NULL,
  `pn_comment` varchar(254) NOT NULL DEFAULT '',
  `pn_type` tinyint(4) NOT NULL DEFAULT '0',
  `pn_tag` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pn_tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `z_workflows`;
CREATE TABLE `z_workflows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metaid` int(11) NOT NULL DEFAULT '0',
  `module` varchar(255) NOT NULL DEFAULT '',
  `schemaname` varchar(255) NOT NULL DEFAULT '',
  `state` varchar(255) NOT NULL DEFAULT '',
  `type` smallint(6) NOT NULL DEFAULT '1',
  `obj_table` varchar(40) NOT NULL DEFAULT '',
  `obj_idcolumn` varchar(40) NOT NULL DEFAULT '',
  `obj_id` int(11) NOT NULL DEFAULT '0',
  `busy` int(11) NOT NULL DEFAULT '0',
  `debug` longblob,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
