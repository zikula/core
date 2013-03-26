-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2013 at 10:30 AM
-- Server version: 5.5.27
-- PHP Version: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `zikula`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_category`
--

CREATE TABLE IF NOT EXISTS `admin_category` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `sortorder` int(11) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `admin_category`
--

INSERT INTO `admin_category` VALUES(1, 'System', 'Core modules at the heart of operation of the site.', 0);
INSERT INTO `admin_category` VALUES(2, 'Layout', 'Layout modules for controlling the site''s look and feel.', 0);
INSERT INTO `admin_category` VALUES(3, 'Users', 'Modules for controlling user membership, access rights and profiles.', 0);
INSERT INTO `admin_category` VALUES(4, 'Content', 'Modules for providing content to your users.', 0);
INSERT INTO `admin_category` VALUES(5, 'Uncategorised', 'Newly-installed or uncategorized modules.', 0);
INSERT INTO `admin_category` VALUES(6, 'Security', 'Modules for managing the site''s security.', 0);

-- --------------------------------------------------------

--
-- Table structure for table `admin_module`
--

CREATE TABLE IF NOT EXISTS `admin_module` (
  `amid` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `sortorder` int(11) NOT NULL,
  PRIMARY KEY (`amid`),
  KEY `mid_cid` (`mid`,`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `admin_module`
--

INSERT INTO `admin_module` VALUES(1, 1, 1, 0);
INSERT INTO `admin_module` VALUES(2, 11, 1, 1);
INSERT INTO `admin_module` VALUES(3, 12, 2, 0);
INSERT INTO `admin_module` VALUES(4, 2, 1, 2);
INSERT INTO `admin_module` VALUES(5, 8, 3, 0);
INSERT INTO `admin_module` VALUES(6, 5, 3, 1);
INSERT INTO `admin_module` VALUES(7, 3, 2, 1);
INSERT INTO `admin_module` VALUES(8, 13, 3, 2);
INSERT INTO `admin_module` VALUES(9, 10, 6, 0);
INSERT INTO `admin_module` VALUES(10, 0, 4, 0);
INSERT INTO `admin_module` VALUES(11, 6, 1, 3);
INSERT INTO `admin_module` VALUES(12, 9, 4, 1);
INSERT INTO `admin_module` VALUES(13, 4, 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `blocks`
--

CREATE TABLE IF NOT EXISTS `blocks` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `blocks`
--

INSERT INTO `blocks` VALUES(1, 'Extmenu', 'Main menu', 'Main menu', 'a:5:{s:14:"displaymodules";s:1:"0";s:10:"stylesheet";s:11:"extmenu.css";s:8:"template";s:24:"blocks_block_extmenu.tpl";s:11:"blocktitles";a:1:{s:2:"en";s:9:"Main menu";}s:5:"links";a:1:{s:2:"en";a:5:{i:0;a:7:{s:4:"name";s:4:"Home";s:3:"url";s:10:"{homepage}";s:5:"title";s:19:"Go to the home page";s:5:"level";i:0;s:8:"parentid";N;s:5:"image";s:0:"";s:6:"active";s:1:"1";}i:1;a:7:{s:4:"name";s:14:"Administration";s:3:"url";s:24:"{Admin:admin:adminpanel}";s:5:"title";s:29:"Go to the site administration";s:5:"level";i:0;s:8:"parentid";N;s:5:"image";s:0:"";s:6:"active";s:1:"1";}i:2;a:7:{s:4:"name";s:10:"My Account";s:3:"url";s:19:"{ZikulaUsersModule}";s:5:"title";s:24:"Go to your account panel";s:5:"level";i:0;s:8:"parentid";N;s:5:"image";s:0:"";s:6:"active";s:1:"1";}i:3;a:7:{s:4:"name";s:7:"Log out";s:3:"url";s:31:"{ZikulaUsersModule:user:logout}";s:5:"title";s:20:"Log out of this site";s:5:"level";i:0;s:8:"parentid";N;s:5:"image";s:0:"";s:6:"active";s:1:"1";}i:4;a:7:{s:4:"name";s:11:"Site search";s:3:"url";s:20:"{ZikulaSearchModule}";s:5:"title";s:16:"Search this site";s:5:"level";i:0;s:8:"parentid";N;s:5:"image";s:0:"";s:6:"active";s:1:"1";}}}}', '', 3, 'a:0:{}', 1, 1, 1, 3600, '2013-03-26 10:29:17', '');
INSERT INTO `blocks` VALUES(2, 'Search', 'Search box', 'Search block', 'a:2:{s:16:"displaySearchBtn";i:1;s:6:"active";a:1:{s:17:"ZikulaUsersModule";i:1;}}', '', 9, 'a:0:{}', 1, 1, 1, 3600, '2013-03-26 10:29:17', '');
INSERT INTO `blocks` VALUES(3, 'Html', 'This site is powered by Zikula!', 'HTML block', '<p><a href="http://zikula.org/">Zikula</a> is a content management system (CMS) and application framework. It is secure and stable, and is a good choice for sites with a large volume of traffic.</p><p>With Zikula:</p><ul><li>you can customise all aspects of the site''s appearance through themes, with support for CSS style sheets, JavaScript, Flash and all other modern web development technologies;</li><li>you can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation and internationalisation of your site;</li><li>you can be sure that your pages will display properly in all browsers, thanks to Zikula''s full compliance with W3C HTML standards;</li><li>you get a standard application-programming interface (API) that lets you easily augment your site''s functionality through modules, blocks and other extensions;</li><li>you can get help and support from the Zikula community of webmasters and developers at <a href="http://www.zikula.org">zikula.org</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>', '', 3, 'a:0:{}', 1, 1, 1, 3600, '2013-03-26 10:29:17', '');
INSERT INTO `blocks` VALUES(4, 'Login', 'User log-in', 'Login block', '', '', 13, 'a:0:{}', 1, 1, 1, 3600, '2013-03-26 10:29:17', '');
INSERT INTO `blocks` VALUES(5, 'Extmenu', 'Top navigation', 'Theme navigation', 'a:5:{s:14:"displaymodules";s:1:"0";s:10:"stylesheet";s:11:"extmenu.css";s:8:"template";s:31:"blocks_block_extmenu_topnav.tpl";s:11:"blocktitles";a:1:{s:2:"en";s:14:"Top navigation";}s:5:"links";a:1:{s:2:"en";a:3:{i:0;a:7:{s:4:"name";s:4:"Home";s:3:"url";s:10:"{homepage}";s:5:"title";s:26:"Go to the site''s home page";s:5:"level";i:0;s:8:"parentid";N;s:5:"image";s:0:"";s:6:"active";s:1:"1";}i:1;a:7:{s:4:"name";s:10:"My Account";s:3:"url";s:19:"{ZikulaUsersModule}";s:5:"title";s:24:"Go to your account panel";s:5:"level";i:0;s:8:"parentid";N;s:5:"image";s:0:"";s:6:"active";s:1:"1";}i:2;a:7:{s:4:"name";s:11:"Site search";s:3:"url";s:20:"{ZikulaSearchModule}";s:5:"title";s:16:"Search this site";s:5:"level";i:0;s:8:"parentid";N;s:5:"image";s:0:"";s:6:"active";s:1:"1";}}}}', '', 3, 'a:0:{}', 1, 1, 1, 3600, '2013-03-26 10:29:18', '');

-- --------------------------------------------------------

--
-- Table structure for table `block_placements`
--

CREATE TABLE IF NOT EXISTS `block_placements` (
  `pid` int(11) NOT NULL,
  `bid` int(11) NOT NULL,
  `sortorder` int(11) NOT NULL,
  PRIMARY KEY (`pid`,`bid`),
  KEY `bid_pid_idx` (`bid`,`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `block_placements`
--

INSERT INTO `block_placements` VALUES(1, 1, 0);
INSERT INTO `block_placements` VALUES(4, 2, 0);
INSERT INTO `block_placements` VALUES(3, 3, 0);
INSERT INTO `block_placements` VALUES(2, 4, 0);
INSERT INTO `block_placements` VALUES(7, 5, 0);

-- --------------------------------------------------------

--
-- Table structure for table `block_positions`
--

CREATE TABLE IF NOT EXISTS `block_positions` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`pid`),
  KEY `name_idx` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `block_positions`
--

INSERT INTO `block_positions` VALUES(1, 'left', 'Left blocks');
INSERT INTO `block_positions` VALUES(2, 'right', 'Right blocks');
INSERT INTO `block_positions` VALUES(3, 'center', 'Center blocks');
INSERT INTO `block_positions` VALUES(4, 'search', 'Search block');
INSERT INTO `block_positions` VALUES(5, 'header', 'Header block');
INSERT INTO `block_positions` VALUES(6, 'footer', 'Footer block');
INSERT INTO `block_positions` VALUES(7, 'topnav', 'Top navigation block');
INSERT INTO `block_positions` VALUES(8, 'bottomnav', 'Bottom navigation block');

-- --------------------------------------------------------

--
-- Table structure for table `categories_category`
--

CREATE TABLE IF NOT EXISTS `categories_category` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10000 ;

--
-- Dumping data for table `categories_category`
--

INSERT INTO `categories_category` VALUES(1, 0, 1, 0, '__SYSTEM__', '', 1, 'b:0;', 'b:0;', '/__SYSTEM__', '/1', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(2, 1, 0, 0, 'Modules', '', 2, 'a:1:{s:2:"en";s:7:"Modules";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules', '/1/2', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(3, 1, 0, 0, 'General', '', 3, 'a:1:{s:2:"en";s:7:"General";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General', '/1/3', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(4, 3, 0, 0, 'YesNo', '', 4, 'a:1:{s:2:"en";s:6:"Yes/No";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/YesNo', '/1/3/4', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(5, 4, 0, 1, '1 - Yes', 'Y', 5, 'b:0;', 'b:0;', '/__SYSTEM__/General/YesNo/1 - Yes', '/1/3/4/5', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(6, 4, 0, 1, '2 - No', 'N', 6, 'b:0;', 'b:0;', '/__SYSTEM__/General/YesNo/2 - No', '/1/3/4/6', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(10, 3, 0, 0, 'Publication Status (extended)', '', 10, 'a:1:{s:2:"en";s:29:"Publication status (extended)";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Extended', '/1/3/10', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(11, 10, 0, 1, 'Pending', 'P', 11, 'a:1:{s:2:"en";s:7:"Pending";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Extended/Pending', '/1/3/10/11', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(12, 10, 0, 1, 'Checked', 'C', 12, 'a:1:{s:2:"en";s:7:"Checked";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Extended/Checked', '/1/3/10/12', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(13, 10, 0, 1, 'Approved', 'A', 13, 'a:1:{s:2:"en";s:8:"Approved";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Extended/Approved', '/1/3/10/13', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(14, 10, 0, 1, 'On-line', 'O', 14, 'a:1:{s:2:"en";s:7:"On-line";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Extended/Online', '/1/3/10/14', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(15, 10, 0, 1, 'Rejected', 'R', 15, 'a:1:{s:2:"en";s:8:"Rejected";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Extended/Rejected', '/1/3/10/15', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(16, 3, 0, 0, 'Gender', '', 16, 'a:1:{s:2:"en";s:6:"Gender";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Gender', '/1/3/16', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(17, 16, 0, 1, 'Male', 'M', 17, 'a:1:{s:2:"en";s:4:"Male";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Gender/Male', '/1/3/16/17', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(18, 16, 0, 1, 'Female', 'F', 18, 'a:1:{s:2:"en";s:6:"Female";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Gender/Female', '/1/3/16/18', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(19, 3, 0, 0, 'Title', '', 19, 'a:1:{s:2:"en";s:5:"Title";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Title', '/1/3/19', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(20, 19, 0, 1, 'Mr', 'Mr', 20, 'a:1:{s:2:"en";s:3:"Mr.";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Title/Mr', '/1/3/19/20', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(21, 19, 0, 1, 'Mrs', 'Mrs', 21, 'a:1:{s:2:"en";s:4:"Mrs.";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Title/Mrs', '/1/3/19/21', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(22, 19, 0, 1, 'Ms', 'Ms', 22, 'a:1:{s:2:"en";s:3:"Ms.";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Title/Ms', '/1/3/19/22', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(23, 19, 0, 1, 'Miss', 'Miss', 23, 'a:1:{s:2:"en";s:4:"Miss";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Title/Miss', '/1/3/19/23', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(24, 19, 0, 1, 'Dr', 'Dr', 24, 'a:1:{s:2:"en";s:3:"Dr.";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Title/Dr', '/1/3/19/24', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(25, 3, 0, 0, 'ActiveStatus', '', 25, 'a:1:{s:2:"en";s:15:"Activity status";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/ActiveStatus', '/1/3/25', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(26, 25, 0, 1, 'Active', 'A', 26, 'a:1:{s:2:"en";s:6:"Active";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/ActiveStatus/Active', '/1/3/25/26', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(27, 25, 0, 1, 'Inactive', 'I', 27, 'a:1:{s:2:"en";s:8:"Inactive";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/ActiveStatus/Inactive', '/1/3/25/27', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(28, 3, 0, 0, 'Publication status (basic)', '', 28, 'a:1:{s:2:"en";s:26:"Publication status (basic)";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Basic', '/1/3/28', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(29, 28, 0, 1, 'Pending', 'P', 29, 'a:1:{s:2:"en";s:7:"Pending";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Basic/Pending', '/1/3/28/29', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(30, 28, 0, 1, 'Approved', 'A', 30, 'a:1:{s:2:"en";s:8:"Approved";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Basic/Approved', '/1/3/28/30', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(31, 1, 0, 0, 'ZikulaUsersModule', '', 31, 'a:1:{s:2:"en";s:5:"Users";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Users', '/1/31', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(32, 2, 0, 0, 'Global', '', 32, 'a:1:{s:2:"en";s:6:"Global";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global', '/1/2/32', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(33, 32, 0, 1, 'Blogging', '', 33, 'a:1:{s:2:"en";s:8:"Blogging";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/Blogging', '/1/2/32/33', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(34, 32, 0, 1, 'Music and audio', '', 34, 'a:1:{s:2:"en";s:15:"Music and audio";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/MusicAndAudio', '/1/2/32/34', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(35, 32, 0, 1, 'Art and photography', '', 35, 'a:1:{s:2:"en";s:19:"Art and photography";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/ArtAndPhotography', '/1/2/32/35', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(36, 32, 0, 1, 'Writing and thinking', '', 36, 'a:1:{s:2:"en";s:20:"Writing and thinking";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/WritingAndThinking', '/1/2/32/36', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(37, 32, 0, 1, 'Communications and media', '', 37, 'a:1:{s:2:"en";s:24:"Communications and media";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/CommunicationsAndMedia', '/1/2/32/37', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(38, 32, 0, 1, 'Travel and culture', '', 38, 'a:1:{s:2:"en";s:18:"Travel and culture";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/TravelAndCulture', '/1/2/32/38', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(39, 32, 0, 1, 'Science and technology', '', 39, 'a:1:{s:2:"en";s:22:"Science and technology";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/ScienceAndTechnology', '/1/2/32/39', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(40, 32, 0, 1, 'Sport and activities', '', 40, 'a:1:{s:2:"en";s:20:"Sport and activities";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/SportAndActivities', '/1/2/32/40', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `categories_category` VALUES(41, 32, 0, 1, 'Business and work', '', 41, 'a:1:{s:2:"en";s:17:"Business and work";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/BusinessAndWork', '/1/2/32/41', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);

-- --------------------------------------------------------

--
-- Table structure for table `categories_mapmeta`
--

CREATE TABLE IF NOT EXISTS `categories_mapmeta` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `categories_mapobj`
--

CREATE TABLE IF NOT EXISTS `categories_mapobj` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `categories_registry`
--

CREATE TABLE IF NOT EXISTS `categories_registry` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `gtype` smallint(6) NOT NULL,
  `description` varchar(200) NOT NULL,
  `prefix` varchar(25) NOT NULL,
  `state` smallint(6) NOT NULL,
  `nbuser` int(11) NOT NULL,
  `nbumax` int(11) NOT NULL,
  `link` int(11) NOT NULL,
  `uidmaster` int(11) NOT NULL,
  PRIMARY KEY (`gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` VALUES(1, 'Users', 0, 'By default, all users are made members of this group.', 'usr', 0, 0, 0, 0, 0);
INSERT INTO `groups` VALUES(2, 'Administrators', 0, 'Group of administrators of this site.', 'adm', 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `group_applications`
--

CREATE TABLE IF NOT EXISTS `group_applications` (
  `app_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `application` longtext NOT NULL,
  `status` smallint(6) NOT NULL,
  PRIMARY KEY (`app_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `group_membership`
--

CREATE TABLE IF NOT EXISTS `group_membership` (
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`gid`,`uid`),
  KEY `gid_uid` (`uid`,`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `group_membership`
--

INSERT INTO `group_membership` VALUES(1, 1);
INSERT INTO `group_membership` VALUES(1, 2);
INSERT INTO `group_membership` VALUES(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `group_perms`
--

CREATE TABLE IF NOT EXISTS `group_perms` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `sequence` int(11) NOT NULL,
  `realm` int(11) NOT NULL,
  `component` varchar(255) NOT NULL,
  `instance` varchar(255) NOT NULL,
  `level` int(11) NOT NULL,
  `bond` int(11) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `group_perms`
--

INSERT INTO `group_perms` VALUES(1, 2, 1, 0, '.*', '.*', 800, 0);
INSERT INTO `group_perms` VALUES(2, -1, 2, 0, 'ExtendedMenublock::', '1:1:', 0, 0);
INSERT INTO `group_perms` VALUES(3, 1, 3, 0, '.*', '.*', 300, 0);
INSERT INTO `group_perms` VALUES(4, 0, 4, 0, 'ExtendedMenublock::', '1:(1|2|3):', 0, 0);
INSERT INTO `group_perms` VALUES(5, 0, 5, 0, '.*', '.*', 200, 0);

-- --------------------------------------------------------

--
-- Table structure for table `hooks`
--

CREATE TABLE IF NOT EXISTS `hooks` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `hook_area`
--

CREATE TABLE IF NOT EXISTS `hook_area` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` varchar(40) NOT NULL,
  `subowner` varchar(40) DEFAULT NULL,
  `areatype` varchar(1) NOT NULL,
  `category` varchar(20) NOT NULL,
  `areaname` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `areaidx` (`areaname`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `hook_area`
--

INSERT INTO `hook_area` VALUES(1, 'Zikula', NULL, 's', 'ui_hooks', 'subscriber.blocks.ui_hooks.htmlblock.content');
INSERT INTO `hook_area` VALUES(2, 'Zikula', NULL, 's', 'ui_hooks', 'subscriber.users.ui_hooks.user');
INSERT INTO `hook_area` VALUES(3, 'Zikula', NULL, 's', 'ui_hooks', 'subscriber.users.ui_hooks.registration');
INSERT INTO `hook_area` VALUES(4, 'Zikula', NULL, 's', 'ui_hooks', 'subscriber.users.ui_hooks.login_screen');
INSERT INTO `hook_area` VALUES(5, 'Zikula', NULL, 's', 'ui_hooks', 'subscriber.users.ui_hooks.login_block');

-- --------------------------------------------------------

--
-- Table structure for table `hook_binding`
--

CREATE TABLE IF NOT EXISTS `hook_binding` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `hook_provider`
--

CREATE TABLE IF NOT EXISTS `hook_provider` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `hook_runtime`
--

CREATE TABLE IF NOT EXISTS `hook_runtime` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `hook_subscriber`
--

CREATE TABLE IF NOT EXISTS `hook_subscriber` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` varchar(40) NOT NULL,
  `subowner` varchar(40) DEFAULT NULL,
  `sareaid` int(11) NOT NULL,
  `hooktype` varchar(20) NOT NULL,
  `category` varchar(20) NOT NULL,
  `eventname` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `myindex` (`eventname`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

--
-- Dumping data for table `hook_subscriber`
--

INSERT INTO `hook_subscriber` VALUES(1, 'Zikula', NULL, 1, 'form_edit', 'ui_hooks', 'blocks.ui_hooks.htmlblock.content.form_edit');
INSERT INTO `hook_subscriber` VALUES(2, 'Zikula', NULL, 2, 'display_view', 'ui_hooks', 'users.ui_hooks.user.display_view');
INSERT INTO `hook_subscriber` VALUES(3, 'Zikula', NULL, 2, 'form_edit', 'ui_hooks', 'users.ui_hooks.user.form_edit');
INSERT INTO `hook_subscriber` VALUES(4, 'Zikula', NULL, 2, 'validate_edit', 'ui_hooks', 'users.ui_hooks.user.validate_edit');
INSERT INTO `hook_subscriber` VALUES(5, 'Zikula', NULL, 2, 'process_edit', 'ui_hooks', 'users.ui_hooks.user.process_edit');
INSERT INTO `hook_subscriber` VALUES(6, 'Zikula', NULL, 2, 'form_delete', 'ui_hooks', 'users.ui_hooks.user.form_delete');
INSERT INTO `hook_subscriber` VALUES(7, 'Zikula', NULL, 2, 'validate_delete', 'ui_hooks', 'users.ui_hooks.user.validate_delete');
INSERT INTO `hook_subscriber` VALUES(8, 'Zikula', NULL, 2, 'process_delete', 'ui_hooks', 'users.ui_hooks.user.process_delete');
INSERT INTO `hook_subscriber` VALUES(9, 'Zikula', NULL, 3, 'display_view', 'ui_hooks', 'users.ui_hooks.registration.display_view');
INSERT INTO `hook_subscriber` VALUES(10, 'Zikula', NULL, 3, 'form_edit', 'ui_hooks', 'users.ui_hooks.registration.form_edit');
INSERT INTO `hook_subscriber` VALUES(11, 'Zikula', NULL, 3, 'validate_edit', 'ui_hooks', 'users.ui_hooks.registration.validate_edit');
INSERT INTO `hook_subscriber` VALUES(12, 'Zikula', NULL, 3, 'process_edit', 'ui_hooks', 'users.ui_hooks.registration.process_edit');
INSERT INTO `hook_subscriber` VALUES(13, 'Zikula', NULL, 3, 'form_delete', 'ui_hooks', 'users.ui_hooks.registration.form_delete');
INSERT INTO `hook_subscriber` VALUES(14, 'Zikula', NULL, 3, 'validate_delete', 'ui_hooks', 'users.ui_hooks.registration.validate_delete');
INSERT INTO `hook_subscriber` VALUES(15, 'Zikula', NULL, 3, 'process_delete', 'ui_hooks', 'users.ui_hooks.registration.process_delete');
INSERT INTO `hook_subscriber` VALUES(16, 'Zikula', NULL, 4, 'form_edit', 'ui_hooks', 'users.ui_hooks.login_screen.form_edit');
INSERT INTO `hook_subscriber` VALUES(17, 'Zikula', NULL, 4, 'validate_edit', 'ui_hooks', 'users.ui_hooks.login_screen.validate_edit');
INSERT INTO `hook_subscriber` VALUES(18, 'Zikula', NULL, 4, 'process_edit', 'ui_hooks', 'users.ui_hooks.login_screen.process_edit');
INSERT INTO `hook_subscriber` VALUES(19, 'Zikula', NULL, 5, 'form_edit', 'ui_hooks', 'users.ui_hooks.login_block.form_edit');
INSERT INTO `hook_subscriber` VALUES(20, 'Zikula', NULL, 5, 'validate_edit', 'ui_hooks', 'users.ui_hooks.login_block.validate_edit');
INSERT INTO `hook_subscriber` VALUES(21, 'Zikula', NULL, 5, 'process_edit', 'ui_hooks', 'users.ui_hooks.login_block.process_edit');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `type` int(11) NOT NULL,
  `displayname` varchar(64) NOT NULL,
  `url` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL,
  `directory` varchar(255) NOT NULL,
  `version` varchar(10) NOT NULL,
  `capabilities` tinytext NOT NULL COMMENT '(DC2Type:array)',
  `state` int(11) NOT NULL,
  `securityschema` longtext NOT NULL COMMENT '(DC2Type:array)',
  `core_min` varchar(10) NOT NULL,
  `core_max` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` VALUES(1, 'ZikulaExtensionsModule', 3, 'Extensions', 'extensions', 'Manage your modules and plugins.', 'Zikula/Module/ExtensionsModule', '3.7.10', 'a:1:{s:5:"admin";a:1:{s:7:"version";s:3:"1.0";}}', 3, 'a:1:{s:24:"ZikulaExtensionsModule::";s:2:"::";}', '1.3.6', '');
INSERT INTO `modules` VALUES(2, 'ZikulaAdminModule', 3, 'Administration panel', 'adminpanel', 'Backend administration interface.', 'Zikula/Module/AdminModule', '1.9.1', 'a:1:{s:5:"admin";a:1:{s:7:"version";s:3:"1.0";}}', 3, 'a:1:{s:19:"ZikulaAdminModule::";s:38:"Admin Category name::Admin Category ID";}', '1.3.6', '');
INSERT INTO `modules` VALUES(3, 'ZikulaBlocksModule', 3, 'Blocks', 'blocks', 'Block administration module.', 'Zikula/Module/BlocksModule', '3.8.2', 'a:3:{s:15:"hook_subscriber";a:1:{s:7:"enabled";b:1;}s:5:"admin";a:1:{s:7:"version";s:3:"1.0";}s:4:"user";a:1:{s:7:"version";s:3:"1.0";}}', 3, 'a:4:{s:20:"ZikulaBlocksModule::";s:30:"Block key:Block title:Block ID";s:28:"ZikulaBlocksModule::position";s:26:"Position name::Position ID";s:23:"Menutree:menutreeblock:";s:26:"Block ID:Link Name:Link ID";s:19:"ExtendedMenublock::";s:17:"Block ID:Link ID:";}', '1.3.6', '');
INSERT INTO `modules` VALUES(4, 'ZikulaErrorsModule', 3, 'Errors', 'errors', 'Error display module.', 'Zikula/Module/ErrorsModule', '1.1.1', 'a:1:{s:4:"user";a:1:{s:7:"version";s:3:"1.0";}}', 3, 'a:1:{s:20:"ZikulaErrorsModule::";s:2:"::";}', '1.3.6', '');
INSERT INTO `modules` VALUES(5, 'ZikulaGroupsModule', 3, 'Groups', 'groups', 'User group administration module.', 'Zikula/Module/GroupsModule', '2.3.2', 'a:2:{s:5:"admin";a:1:{s:7:"version";s:3:"1.0";}s:4:"user";a:1:{s:7:"version";s:3:"1.0";}}', 3, 'a:1:{s:20:"ZikulaGroupsModule::";s:10:"Group ID::";}', '1.3.6', '');
INSERT INTO `modules` VALUES(6, 'ZikulaMailerModule', 3, 'Mailer Module', 'mailer', 'Mailer module, provides mail API and mail setting administration.', 'Zikula/Module/MailerModule', '1.3.2', 'a:1:{s:5:"admin";a:1:{s:7:"version";s:3:"1.0";}}', 3, 'a:1:{s:20:"ZikulaMailerModule::";s:2:"::";}', '1.3.6', '');
INSERT INTO `modules` VALUES(7, 'ZikulaPageLockModule', 3, 'Page lock', 'pagelock', 'Provides the ability to lock pages when they are in use, for content and access control.', 'Zikula/Module/PageLockModule', '1.1.1', 'a:0:{}', 1, 'a:1:{s:22:"ZikulaPageLockModule::";s:2:"::";}', '1.3.6', '');
INSERT INTO `modules` VALUES(8, 'ZikulaPermissionsModule', 3, 'Permissions', 'permissions', 'User permissions manager.', 'Zikula/Module/PermissionsModule', '1.1.1', 'a:1:{s:5:"admin";a:1:{s:7:"version";s:3:"1.0";}}', 3, 'a:1:{s:25:"ZikulaPermissionsModule::";s:2:"::";}', '1.3.6', '');
INSERT INTO `modules` VALUES(9, 'ZikulaSearchModule', 3, 'Site search', 'search', 'Site search module.', 'Zikula/Module/SearchModule', '1.5.2', 'a:2:{s:5:"admin";a:1:{s:7:"version";s:3:"1.0";}s:4:"user";a:1:{s:7:"version";s:3:"1.0";}}', 3, 'a:1:{s:20:"ZikulaSearchModule::";s:13:"Module name::";}', '1.3.6', '');
INSERT INTO `modules` VALUES(10, 'ZikulaSecurityCenterModule', 3, 'Security Center', 'securitycenter', 'Manage site security and settings.', 'Zikula/Module/SecurityCenterModule', '1.4.4', 'a:1:{s:5:"admin";a:1:{s:7:"version";s:3:"1.0";}}', 3, 'a:1:{s:28:"ZikulaSecurityCenterModule::";s:2:"::";}', '1.3.6', '');
INSERT INTO `modules` VALUES(11, 'ZikulaSettingsModule', 3, 'General settings', 'settings', 'General site configuration interface.', 'Zikula/Module/SettingsModule', '2.9.7', 'a:1:{s:5:"admin";a:1:{s:7:"version";s:3:"1.0";}}', 3, 'a:1:{s:22:"ZikulaSettingsModule::";s:2:"::";}', '1.3.6', '');
INSERT INTO `modules` VALUES(12, 'ZikulaThemeModule', 3, 'Themes', 'theme', 'Themes module to manage site layout, render and cache settings.', 'Zikula/Module/ThemeModule', '3.4.3', 'a:2:{s:5:"admin";a:1:{s:7:"version";s:3:"1.0";}s:4:"user";a:1:{s:7:"version";s:3:"1.0";}}', 3, 'a:1:{s:19:"ZikulaThemeModule::";s:12:"Theme name::";}', '1.3.6', '');
INSERT INTO `modules` VALUES(13, 'ZikulaUsersModule', 3, 'Users', 'users', 'Provides an interface for configuring and administering registered user accounts. Incorporates all needed functionality, but can work in close unison with the third party profile module configured in the general settings of the site.', 'Zikula/Module/UsersModule', '2.2.1', 'a:4:{s:14:"authentication";a:1:{s:7:"version";s:3:"1.0";}s:15:"hook_subscriber";a:1:{s:7:"enabled";b:1;}s:5:"admin";a:1:{s:7:"version";s:3:"1.0";}s:4:"user";a:1:{s:7:"version";s:3:"1.0";}}', 3, 'a:2:{s:19:"ZikulaUsersModule::";s:14:"Uname::User ID";s:28:"ZikulaUsersModule::MailUsers";s:2:"::";}', '1.3.6', '');

-- --------------------------------------------------------

--
-- Table structure for table `module_deps`
--

CREATE TABLE IF NOT EXISTS `module_deps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modid` int(11) NOT NULL,
  `modname` varchar(64) NOT NULL,
  `minversion` varchar(10) NOT NULL,
  `maxversion` varchar(10) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `module_deps`
--

INSERT INTO `module_deps` VALUES(1, 3, 'Scribite', '5.0.0', '', 2);

-- --------------------------------------------------------

--
-- Table structure for table `module_vars`
--

CREATE TABLE IF NOT EXISTS `module_vars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modname` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=194 ;

--
-- Dumping data for table `module_vars`
--

INSERT INTO `module_vars` VALUES(1, '/EventHandlers', 'ZikulaExtensionsModule', 'a:2:{i:0;a:3:{s:9:"eventname";s:27:"controller.method_not_found";s:8:"callable";a:2:{i:0;s:54:"Zikula\\Module\\ExtensionsModule\\Listener\\HookUiListener";i:1;s:5:"hooks";}s:6:"weight";i:10;}i:1;a:3:{s:9:"eventname";s:27:"controller.method_not_found";s:8:"callable";a:2:{i:0;s:54:"Zikula\\Module\\ExtensionsModule\\Listener\\HookUiListener";i:1;s:14:"moduleservices";}s:6:"weight";i:10;}}');
INSERT INTO `module_vars` VALUES(2, 'ZikulaExtensionsModule', 'itemsperpage', 'i:25;');
INSERT INTO `module_vars` VALUES(3, 'ZConfig', 'debug', 's:1:"0";');
INSERT INTO `module_vars` VALUES(4, 'ZConfig', 'sitename', 's:9:"Site name";');
INSERT INTO `module_vars` VALUES(5, 'ZConfig', 'slogan', 's:16:"Site description";');
INSERT INTO `module_vars` VALUES(6, 'ZConfig', 'metakeywords', 's:237:"zikula, portal, portal web, open source, web site, website, weblog, blog, content management, content management system, web content management, web content management system, enterprise web content management, cms, application framework";');
INSERT INTO `module_vars` VALUES(7, 'ZConfig', 'defaultpagetitle', 's:9:"Site name";');
INSERT INTO `module_vars` VALUES(8, 'ZConfig', 'defaultmetadescription', 's:16:"Site description";');
INSERT INTO `module_vars` VALUES(9, 'ZConfig', 'startdate', 's:7:"03/2013";');
INSERT INTO `module_vars` VALUES(10, 'ZConfig', 'adminmail', 's:19:"example@example.com";');
INSERT INTO `module_vars` VALUES(11, 'ZConfig', 'Default_Theme', 's:9:"Andreas08";');
INSERT INTO `module_vars` VALUES(12, 'ZConfig', 'timezone_offset', 's:1:"0";');
INSERT INTO `module_vars` VALUES(13, 'ZConfig', 'timezone_server', 's:1:"0";');
INSERT INTO `module_vars` VALUES(14, 'ZConfig', 'funtext', 's:1:"1";');
INSERT INTO `module_vars` VALUES(15, 'ZConfig', 'reportlevel', 's:1:"0";');
INSERT INTO `module_vars` VALUES(16, 'ZConfig', 'startpage', 's:0:"";');
INSERT INTO `module_vars` VALUES(17, 'ZConfig', 'Version_Num', 's:5:"1.3.6";');
INSERT INTO `module_vars` VALUES(18, 'ZConfig', 'Version_ID', 's:6:"Zikula";');
INSERT INTO `module_vars` VALUES(19, 'ZConfig', 'Version_Sub', 's:3:"vai";');
INSERT INTO `module_vars` VALUES(20, 'ZConfig', 'debug_sql', 's:1:"0";');
INSERT INTO `module_vars` VALUES(21, 'ZConfig', 'multilingual', 's:1:"1";');
INSERT INTO `module_vars` VALUES(22, 'ZConfig', 'useflags', 's:1:"0";');
INSERT INTO `module_vars` VALUES(23, 'ZConfig', 'theme_change', 's:1:"0";');
INSERT INTO `module_vars` VALUES(24, 'ZConfig', 'UseCompression', 's:1:"0";');
INSERT INTO `module_vars` VALUES(25, 'ZConfig', 'siteoff', 'i:0;');
INSERT INTO `module_vars` VALUES(26, 'ZConfig', 'siteoffreason', 's:0:"";');
INSERT INTO `module_vars` VALUES(27, 'ZConfig', 'starttype', 's:0:"";');
INSERT INTO `module_vars` VALUES(28, 'ZConfig', 'startfunc', 's:0:"";');
INSERT INTO `module_vars` VALUES(29, 'ZConfig', 'startargs', 's:0:"";');
INSERT INTO `module_vars` VALUES(30, 'ZConfig', 'entrypoint', 's:9:"index.php";');
INSERT INTO `module_vars` VALUES(31, 'ZConfig', 'language_detect', 'i:0;');
INSERT INTO `module_vars` VALUES(32, 'ZConfig', 'shorturls', 'b:0;');
INSERT INTO `module_vars` VALUES(33, 'ZConfig', 'shorturlstype', 's:1:"0";');
INSERT INTO `module_vars` VALUES(34, 'ZConfig', 'shorturlsseparator', 's:1:"-";');
INSERT INTO `module_vars` VALUES(35, 'ZConfig', 'shorturlsstripentrypoint', 'b:1;');
INSERT INTO `module_vars` VALUES(36, 'ZConfig', 'shorturlsdefaultmodule', 's:0:"";');
INSERT INTO `module_vars` VALUES(37, 'ZConfig', 'profilemodule', 's:0:"";');
INSERT INTO `module_vars` VALUES(38, 'ZConfig', 'messagemodule', 's:0:"";');
INSERT INTO `module_vars` VALUES(39, 'ZConfig', 'languageurl', 'i:0;');
INSERT INTO `module_vars` VALUES(40, 'ZConfig', 'ajaxtimeout', 'i:5000;');
INSERT INTO `module_vars` VALUES(41, 'ZConfig', 'permasearch', 's:161:"À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü";');
INSERT INTO `module_vars` VALUES(42, 'ZConfig', 'permareplace', 's:114:"A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,ss,ae,Ae,oe,Oe,ue,Ue";');
INSERT INTO `module_vars` VALUES(43, 'ZConfig', 'language', 's:3:"eng";');
INSERT INTO `module_vars` VALUES(44, 'ZConfig', 'locale', 's:2:"en";');
INSERT INTO `module_vars` VALUES(45, 'ZConfig', 'language_i18n', 's:2:"en";');
INSERT INTO `module_vars` VALUES(46, 'ZConfig', 'idnnames', 'i:1;');
INSERT INTO `module_vars` VALUES(47, 'ZikulaThemeModule', 'modulesnocache', 's:0:"";');
INSERT INTO `module_vars` VALUES(48, 'ZikulaThemeModule', 'enablecache', 'b:0;');
INSERT INTO `module_vars` VALUES(49, 'ZikulaThemeModule', 'compile_check', 'b:1;');
INSERT INTO `module_vars` VALUES(50, 'ZikulaThemeModule', 'cache_lifetime', 'i:1800;');
INSERT INTO `module_vars` VALUES(51, 'ZikulaThemeModule', 'cache_lifetime_mods', 'i:1800;');
INSERT INTO `module_vars` VALUES(52, 'ZikulaThemeModule', 'force_compile', 'b:0;');
INSERT INTO `module_vars` VALUES(53, 'ZikulaThemeModule', 'trimwhitespace', 'b:0;');
INSERT INTO `module_vars` VALUES(54, 'ZikulaThemeModule', 'maxsizeforlinks', 'i:30;');
INSERT INTO `module_vars` VALUES(55, 'ZikulaThemeModule', 'itemsperpage', 'i:25;');
INSERT INTO `module_vars` VALUES(56, 'ZikulaThemeModule', 'cssjscombine', 'b:0;');
INSERT INTO `module_vars` VALUES(57, 'ZikulaThemeModule', 'cssjscompress', 'b:0;');
INSERT INTO `module_vars` VALUES(58, 'ZikulaThemeModule', 'cssjsminify', 'b:0;');
INSERT INTO `module_vars` VALUES(59, 'ZikulaThemeModule', 'cssjscombine_lifetime', 'i:3600;');
INSERT INTO `module_vars` VALUES(60, 'ZikulaThemeModule', 'render_compile_check', 'b:1;');
INSERT INTO `module_vars` VALUES(61, 'ZikulaThemeModule', 'render_force_compile', 'b:0;');
INSERT INTO `module_vars` VALUES(62, 'ZikulaThemeModule', 'render_cache', 'b:0;');
INSERT INTO `module_vars` VALUES(63, 'ZikulaThemeModule', 'render_expose_template', 'b:0;');
INSERT INTO `module_vars` VALUES(64, 'ZikulaThemeModule', 'render_lifetime', 'i:3600;');
INSERT INTO `module_vars` VALUES(65, 'ZikulaThemeModule', 'enable_mobile_theme', 'b:0;');
INSERT INTO `module_vars` VALUES(66, 'ZikulaAdminModule', 'modulesperrow', 'i:3;');
INSERT INTO `module_vars` VALUES(67, 'ZikulaAdminModule', 'itemsperpage', 'i:15;');
INSERT INTO `module_vars` VALUES(68, 'ZikulaAdminModule', 'defaultcategory', 'i:5;');
INSERT INTO `module_vars` VALUES(69, 'ZikulaAdminModule', 'admingraphic', 'i:1;');
INSERT INTO `module_vars` VALUES(70, 'ZikulaAdminModule', 'startcategory', 'i:1;');
INSERT INTO `module_vars` VALUES(71, 'ZikulaAdminModule', 'ignoreinstallercheck', 'i:0;');
INSERT INTO `module_vars` VALUES(72, 'ZikulaAdminModule', 'admintheme', 's:0:"";');
INSERT INTO `module_vars` VALUES(73, 'ZikulaAdminModule', 'displaynametype', 'i:1;');
INSERT INTO `module_vars` VALUES(74, 'ZikulaPermissionsModule', 'filter', 'i:1;');
INSERT INTO `module_vars` VALUES(75, 'ZikulaPermissionsModule', 'warnbar', 'i:1;');
INSERT INTO `module_vars` VALUES(76, 'ZikulaPermissionsModule', 'rowview', 'i:20;');
INSERT INTO `module_vars` VALUES(77, 'ZikulaPermissionsModule', 'rowedit', 'i:20;');
INSERT INTO `module_vars` VALUES(78, 'ZikulaPermissionsModule', 'lockadmin', 'i:1;');
INSERT INTO `module_vars` VALUES(79, 'ZikulaPermissionsModule', 'adminid', 'i:1;');
INSERT INTO `module_vars` VALUES(80, 'ZikulaGroupsModule', 'itemsperpage', 'i:25;');
INSERT INTO `module_vars` VALUES(81, 'ZikulaGroupsModule', 'defaultgroup', 'i:1;');
INSERT INTO `module_vars` VALUES(82, 'ZikulaGroupsModule', 'mailwarning', 'i:0;');
INSERT INTO `module_vars` VALUES(83, 'ZikulaGroupsModule', 'hideclosed', 'i:0;');
INSERT INTO `module_vars` VALUES(84, 'ZikulaGroupsModule', 'primaryadmingroup', 'i:2;');
INSERT INTO `module_vars` VALUES(85, 'ZikulaBlocksModule', 'collapseable', 'i:0;');
INSERT INTO `module_vars` VALUES(86, 'ZikulaUsersModule', 'accountdisplaygraphics', 'b:1;');
INSERT INTO `module_vars` VALUES(87, 'ZikulaUsersModule', 'accountitemsperpage', 'i:25;');
INSERT INTO `module_vars` VALUES(88, 'ZikulaUsersModule', 'accountitemsperrow', 'i:5;');
INSERT INTO `module_vars` VALUES(89, 'ZikulaUsersModule', 'userimg', 's:11:"images/menu";');
INSERT INTO `module_vars` VALUES(90, 'ZikulaUsersModule', 'anonymous', 's:5:"Guest";');
INSERT INTO `module_vars` VALUES(91, 'ZikulaUsersModule', 'avatarpath', 's:13:"images/avatar";');
INSERT INTO `module_vars` VALUES(92, 'ZikulaUsersModule', 'chgemail_expiredays', 'i:0;');
INSERT INTO `module_vars` VALUES(93, 'ZikulaUsersModule', 'chgpass_expiredays', 'i:0;');
INSERT INTO `module_vars` VALUES(94, 'ZikulaUsersModule', 'reg_expiredays', 'i:0;');
INSERT INTO `module_vars` VALUES(95, 'ZikulaUsersModule', 'allowgravatars', 'b:1;');
INSERT INTO `module_vars` VALUES(96, 'ZikulaUsersModule', 'gravatarimage', 's:12:"gravatar.gif";');
INSERT INTO `module_vars` VALUES(97, 'ZikulaUsersModule', 'hash_method', 's:6:"sha256";');
INSERT INTO `module_vars` VALUES(98, 'ZikulaUsersModule', 'itemsperpage', 'i:25;');
INSERT INTO `module_vars` VALUES(99, 'ZikulaUsersModule', 'login_displayapproval', 'b:0;');
INSERT INTO `module_vars` VALUES(100, 'ZikulaUsersModule', 'login_displaydelete', 'b:0;');
INSERT INTO `module_vars` VALUES(101, 'ZikulaUsersModule', 'login_displayinactive', 'b:0;');
INSERT INTO `module_vars` VALUES(102, 'ZikulaUsersModule', 'login_displayverify', 'b:0;');
INSERT INTO `module_vars` VALUES(103, 'ZikulaUsersModule', 'loginviaoption', 'i:0;');
INSERT INTO `module_vars` VALUES(104, 'ZikulaUsersModule', 'login_redirect', 'b:1;');
INSERT INTO `module_vars` VALUES(105, 'ZikulaUsersModule', 'changeemail', 'b:1;');
INSERT INTO `module_vars` VALUES(106, 'ZikulaUsersModule', 'minpass', 'i:5;');
INSERT INTO `module_vars` VALUES(107, 'ZikulaUsersModule', 'use_password_strength_meter', 'b:0;');
INSERT INTO `module_vars` VALUES(108, 'ZikulaUsersModule', 'reg_notifyemail', 's:0:"";');
INSERT INTO `module_vars` VALUES(109, 'ZikulaUsersModule', 'reg_question', 's:0:"";');
INSERT INTO `module_vars` VALUES(110, 'ZikulaUsersModule', 'reg_answer', 's:0:"";');
INSERT INTO `module_vars` VALUES(111, 'ZikulaUsersModule', 'moderation', 'b:0;');
INSERT INTO `module_vars` VALUES(112, 'ZikulaUsersModule', 'moderation_order', 'i:0;');
INSERT INTO `module_vars` VALUES(113, 'ZikulaUsersModule', 'reg_autologin', 'b:0;');
INSERT INTO `module_vars` VALUES(114, 'ZikulaUsersModule', 'reg_noregreasons', 's:51:"Sorry! New user registration is currently disabled.";');
INSERT INTO `module_vars` VALUES(115, 'ZikulaUsersModule', 'reg_allowreg', 'b:1;');
INSERT INTO `module_vars` VALUES(116, 'ZikulaUsersModule', 'reg_Illegaluseragents', 's:0:"";');
INSERT INTO `module_vars` VALUES(117, 'ZikulaUsersModule', 'reg_Illegaldomains', 's:0:"";');
INSERT INTO `module_vars` VALUES(118, 'ZikulaUsersModule', 'reg_Illegalusername', 's:66:"root, webmaster, admin, administrator, nobody, anonymous, username";');
INSERT INTO `module_vars` VALUES(119, 'ZikulaUsersModule', 'reg_verifyemail', 'i:2;');
INSERT INTO `module_vars` VALUES(120, 'ZikulaUsersModule', 'reg_uniemail', 'b:1;');
INSERT INTO `module_vars` VALUES(121, '/EventHandlers', 'ZikulaUsersModule', 'a:4:{i:0;a:3:{s:9:"eventname";s:19:"get.pending_content";s:8:"callable";a:2:{i:0;s:57:"Zikula\\Module\\UsersModule\\Listener\\PendingContentListener";i:1;s:22:"pendingContentListener";}s:6:"weight";i:10;}i:1;a:3:{s:9:"eventname";s:15:"user.login.veto";s:8:"callable";a:2:{i:0;s:63:"Zikula\\Module\\UsersModule\\Listener\\ForcedPasswordChangeListener";i:1;s:28:"forcedPasswordChangeListener";}s:6:"weight";i:10;}i:2;a:3:{s:9:"eventname";s:21:"user.logout.succeeded";s:8:"callable";a:2:{i:0;s:62:"Zikula\\Module\\UsersModule\\Listener\\ClearUsersNamespaceListener";i:1;s:27:"clearUsersNamespaceListener";}s:6:"weight";i:10;}i:3;a:3:{s:9:"eventname";s:25:"frontcontroller.exception";s:8:"callable";a:2:{i:0;s:62:"Zikula\\Module\\UsersModule\\Listener\\ClearUsersNamespaceListener";i:1;s:27:"clearUsersNamespaceListener";}s:6:"weight";i:10;}}');
INSERT INTO `module_vars` VALUES(122, 'ZikulaSecurityCenterModule', 'itemsperpage', 'i:10;');
INSERT INTO `module_vars` VALUES(123, 'ZConfig', 'updatecheck', 'i:1;');
INSERT INTO `module_vars` VALUES(124, 'ZConfig', 'updatefrequency', 'i:7;');
INSERT INTO `module_vars` VALUES(125, 'ZConfig', 'updatelastchecked', 'i:1364293761;');
INSERT INTO `module_vars` VALUES(126, 'ZConfig', 'updateversion', 's:0:"";');
INSERT INTO `module_vars` VALUES(127, 'ZConfig', 'keyexpiry', 'i:0;');
INSERT INTO `module_vars` VALUES(128, 'ZConfig', 'sessionauthkeyua', 'b:0;');
INSERT INTO `module_vars` VALUES(129, 'ZConfig', 'secure_domain', 's:0:"";');
INSERT INTO `module_vars` VALUES(130, 'ZConfig', 'signcookies', 'i:1;');
INSERT INTO `module_vars` VALUES(131, 'ZConfig', 'signingkey', 's:40:"1542ff9dd4f98446f00ebfff42f0068455f99805";');
INSERT INTO `module_vars` VALUES(132, 'ZConfig', 'seclevel', 's:6:"Medium";');
INSERT INTO `module_vars` VALUES(133, 'ZConfig', 'secmeddays', 'i:7;');
INSERT INTO `module_vars` VALUES(134, 'ZConfig', 'secinactivemins', 'i:20;');
INSERT INTO `module_vars` VALUES(135, 'ZConfig', 'sessionstoretofile', 'i:0;');
INSERT INTO `module_vars` VALUES(136, 'ZConfig', 'sessionsavepath', 's:0:"";');
INSERT INTO `module_vars` VALUES(137, 'ZConfig', 'gc_probability', 'i:100;');
INSERT INTO `module_vars` VALUES(138, 'ZConfig', 'anonymoussessions', 'i:1;');
INSERT INTO `module_vars` VALUES(139, 'ZConfig', 'sessionrandregenerate', 'b:1;');
INSERT INTO `module_vars` VALUES(140, 'ZConfig', 'sessionregenerate', 'b:1;');
INSERT INTO `module_vars` VALUES(141, 'ZConfig', 'sessionregeneratefreq', 'i:10;');
INSERT INTO `module_vars` VALUES(142, 'ZConfig', 'sessionipcheck', 'i:0;');
INSERT INTO `module_vars` VALUES(143, 'ZConfig', 'sessionname', 's:5:"_zsid";');
INSERT INTO `module_vars` VALUES(144, 'ZConfig', 'sessioncsrftokenonetime', 'i:0;');
INSERT INTO `module_vars` VALUES(145, 'ZConfig', 'filtergetvars', 'i:1;');
INSERT INTO `module_vars` VALUES(146, 'ZConfig', 'filterpostvars', 'i:1;');
INSERT INTO `module_vars` VALUES(147, 'ZConfig', 'filtercookievars', 'i:1;');
INSERT INTO `module_vars` VALUES(148, 'ZConfig', 'outputfilter', 'i:1;');
INSERT INTO `module_vars` VALUES(149, 'ZConfig', 'htmlpurifierlocation', 's:89:"C:\\xampp\\htdocs\\core13\\src\\system\\Zikula\\Module\\SecurityCenterModule/vendor/htmlpurifier/";');
INSERT INTO `module_vars` VALUES(150, 'ZikulaSecurityCenterModule', 'htmlpurifierConfig', 's:3942:"a:10:{s:4:"Attr";a:15:{s:14:"AllowedClasses";N;s:19:"AllowedFrameTargets";a:0:{}s:10:"AllowedRel";a:3:{s:8:"nofollow";b:1;s:11:"imageviewer";b:1;s:8:"lightbox";b:1;}s:10:"AllowedRev";a:0:{}s:13:"ClassUseCDATA";N;s:15:"DefaultImageAlt";N;s:19:"DefaultInvalidImage";s:0:"";s:22:"DefaultInvalidImageAlt";s:13:"Invalid image";s:14:"DefaultTextDir";s:3:"ltr";s:8:"EnableID";b:0;s:16:"ForbiddenClasses";a:0:{}s:11:"IDBlacklist";a:0:{}s:17:"IDBlacklistRegexp";N;s:8:"IDPrefix";s:0:"";s:13:"IDPrefixLocal";s:0:"";}s:10:"AutoFormat";a:10:{s:13:"AutoParagraph";b:0;s:6:"Custom";a:0:{}s:14:"DisplayLinkURI";b:0;s:7:"Linkify";b:0;s:22:"PurifierLinkify.DocURL";s:3:"#%s";s:15:"PurifierLinkify";b:0;s:33:"RemoveEmpty.RemoveNbsp.Exceptions";a:2:{s:2:"td";b:1;s:2:"th";b:1;}s:22:"RemoveEmpty.RemoveNbsp";b:0;s:11:"RemoveEmpty";b:0;s:28:"RemoveSpansWithoutAttributes";b:0;}s:3:"CSS";a:9:{s:14:"AllowImportant";b:0;s:11:"AllowTricky";b:0;s:12:"AllowedFonts";N;s:17:"AllowedProperties";N;s:13:"DefinitionRev";i:1;s:19:"ForbiddenProperties";a:0:{}s:12:"MaxImgLength";s:6:"1200px";s:11:"Proprietary";b:0;s:7:"Trusted";b:0;}s:5:"Cache";a:3:{s:14:"DefinitionImpl";s:10:"Serializer";s:14:"SerializerPath";N;s:21:"SerializerPermissions";i:493;}s:4:"Core";a:17:{s:17:"AggressivelyFixLt";b:1;s:13:"CollectErrors";b:0;s:13:"ColorKeywords";a:17:{s:6:"maroon";s:7:"#800000";s:3:"red";s:7:"#FF0000";s:6:"orange";s:7:"#FFA500";s:6:"yellow";s:7:"#FFFF00";s:5:"olive";s:7:"#808000";s:6:"purple";s:7:"#800080";s:7:"fuchsia";s:7:"#FF00FF";s:5:"white";s:7:"#FFFFFF";s:4:"lime";s:7:"#00FF00";s:5:"green";s:7:"#008000";s:4:"navy";s:7:"#000080";s:4:"blue";s:7:"#0000FF";s:4:"aqua";s:7:"#00FFFF";s:4:"teal";s:7:"#008080";s:5:"black";s:7:"#000000";s:6:"silver";s:7:"#C0C0C0";s:4:"gray";s:7:"#808080";}s:25:"ConvertDocumentToFragment";b:1;s:31:"DirectLexLineNumberSyncInterval";i:0;s:8:"Encoding";s:5:"utf-8";s:21:"EscapeInvalidChildren";b:0;s:17:"EscapeInvalidTags";b:0;s:24:"EscapeNonASCIICharacters";b:0;s:14:"HiddenElements";a:2:{s:6:"script";b:1;s:5:"style";b:1;}s:8:"Language";s:2:"en";s:9:"LexerImpl";N;s:19:"MaintainLineNumbers";N;s:17:"NormalizeNewlines";b:1;s:16:"RemoveInvalidImg";b:1;s:28:"RemoveProcessingInstructions";b:0;s:20:"RemoveScriptContents";N;}s:6:"Filter";a:6:{s:6:"Custom";a:0:{}s:27:"ExtractStyleBlocks.Escaping";b:1;s:24:"ExtractStyleBlocks.Scope";N;s:27:"ExtractStyleBlocks.TidyImpl";N;s:18:"ExtractStyleBlocks";b:0;s:7:"YouTube";b:0;}s:4:"HTML";a:26:{s:7:"Allowed";N;s:17:"AllowedAttributes";N;s:15:"AllowedElements";N;s:14:"AllowedModules";N;s:18:"Attr.Name.UseCDATA";b:0;s:12:"BlockWrapper";s:1:"p";s:11:"CoreModules";a:7:{s:9:"Structure";b:1;s:4:"Text";b:1;s:9:"Hypertext";b:1;s:4:"List";b:1;s:22:"NonXMLCommonAttributes";b:1;s:19:"XMLCommonAttributes";b:1;s:16:"CommonAttributes";b:1;}s:13:"CustomDoctype";N;s:12:"DefinitionID";N;s:13:"DefinitionRev";i:1;s:7:"Doctype";s:22:"HTML 4.01 Transitional";s:20:"FlashAllowFullScreen";b:0;s:19:"ForbiddenAttributes";a:0:{}s:17:"ForbiddenElements";a:0:{}s:12:"MaxImgLength";i:1200;s:8:"Nofollow";b:0;s:6:"Parent";s:3:"div";s:11:"Proprietary";b:0;s:9:"SafeEmbed";b:1;s:10:"SafeObject";b:1;s:6:"Strict";b:0;s:7:"TidyAdd";a:0:{}s:9:"TidyLevel";s:6:"medium";s:10:"TidyRemove";a:0:{}s:7:"Trusted";b:0;s:5:"XHTML";b:1;}s:6:"Output";a:6:{s:21:"CommentScriptContents";b:1;s:12:"FixInnerHTML";b:1;s:11:"FlashCompat";b:1;s:7:"Newline";N;s:8:"SortAttr";b:0;s:10:"TidyFormat";b:0;}s:4:"Test";a:1:{s:12:"ForceNoIconv";b:0;}s:3:"URI";a:16:{s:14:"AllowedSchemes";a:6:{s:4:"http";b:1;s:5:"https";b:1;s:6:"mailto";b:1;s:3:"ftp";b:1;s:4:"nntp";b:1;s:4:"news";b:1;}s:4:"Base";N;s:13:"DefaultScheme";s:4:"http";s:12:"DefinitionID";N;s:13:"DefinitionRev";i:1;s:7:"Disable";b:0;s:15:"DisableExternal";b:0;s:24:"DisableExternalResources";b:0;s:16:"DisableResources";b:0;s:4:"Host";N;s:13:"HostBlacklist";a:0:{}s:12:"MakeAbsolute";b:0;s:5:"Munge";N;s:14:"MungeResources";b:0;s:14:"MungeSecretKey";N;s:22:"OverrideAllowedSchemes";b:1;}}";');
INSERT INTO `module_vars` VALUES(151, 'ZConfig', 'useids', 'i:0;');
INSERT INTO `module_vars` VALUES(152, 'ZConfig', 'idsmail', 'i:0;');
INSERT INTO `module_vars` VALUES(153, 'ZConfig', 'idsrulepath', 's:111:"C:\\xampp\\htdocs\\core13\\src\\system\\Zikula\\Module\\SecurityCenterModule/Resources/config/phpids_zikula_default.xml";');
INSERT INTO `module_vars` VALUES(154, 'ZConfig', 'idssoftblock', 'i:1;');
INSERT INTO `module_vars` VALUES(155, 'ZConfig', 'idsfilter', 's:3:"xml";');
INSERT INTO `module_vars` VALUES(156, 'ZConfig', 'idsimpactthresholdone', 'i:1;');
INSERT INTO `module_vars` VALUES(157, 'ZConfig', 'idsimpactthresholdtwo', 'i:10;');
INSERT INTO `module_vars` VALUES(158, 'ZConfig', 'idsimpactthresholdthree', 'i:25;');
INSERT INTO `module_vars` VALUES(159, 'ZConfig', 'idsimpactthresholdfour', 'i:75;');
INSERT INTO `module_vars` VALUES(160, 'ZConfig', 'idsimpactmode', 'i:1;');
INSERT INTO `module_vars` VALUES(161, 'ZConfig', 'idshtmlfields', 'a:1:{i:0;s:14:"POST.__wysiwyg";}');
INSERT INTO `module_vars` VALUES(162, 'ZConfig', 'idsjsonfields', 'a:1:{i:0;s:15:"POST.__jsondata";}');
INSERT INTO `module_vars` VALUES(163, 'ZConfig', 'idsexceptions', 'a:12:{i:0;s:10:"GET.__utmz";i:1;s:10:"GET.__utmc";i:2;s:18:"REQUEST.linksorder";i:3;s:15:"POST.linksorder";i:4;s:19:"REQUEST.fullcontent";i:5;s:16:"POST.fullcontent";i:6;s:22:"REQUEST.summarycontent";i:7;s:19:"POST.summarycontent";i:8;s:19:"REQUEST.filter.page";i:9;s:16:"POST.filter.page";i:10;s:20:"REQUEST.filter.value";i:11;s:17:"POST.filter.value";}');
INSERT INTO `module_vars` VALUES(164, 'ZConfig', 'summarycontent', 'N;');
INSERT INTO `module_vars` VALUES(165, 'ZConfig', 'fullcontent', 'N;');
INSERT INTO `module_vars` VALUES(166, 'ZConfig', 'htmlentities', 's:1:"1";');
INSERT INTO `module_vars` VALUES(167, 'ZConfig', 'AllowableHTML', 'a:110:{s:3:"!--";i:2;s:1:"a";i:2;s:4:"abbr";i:1;s:7:"acronym";i:1;s:7:"address";i:1;s:6:"applet";i:0;s:4:"area";i:0;s:7:"article";i:1;s:5:"aside";i:1;s:5:"audio";i:0;s:1:"b";i:1;s:4:"base";i:0;s:8:"basefont";i:0;s:3:"bdo";i:0;s:3:"big";i:0;s:10:"blockquote";i:2;s:2:"br";i:2;s:6:"button";i:0;s:6:"canvas";i:0;s:7:"caption";i:1;s:6:"center";i:2;s:4:"cite";i:1;s:4:"code";i:0;s:3:"col";i:1;s:8:"colgroup";i:1;s:7:"command";i:0;s:8:"datalist";i:0;s:2:"dd";i:1;s:3:"del";i:0;s:7:"details";i:1;s:3:"dfn";i:0;s:3:"dir";i:0;s:3:"div";i:2;s:2:"dl";i:1;s:2:"dt";i:1;s:2:"em";i:2;s:5:"embed";i:0;s:8:"fieldset";i:1;s:10:"figcaption";i:0;s:6:"figure";i:0;s:6:"footer";i:0;s:4:"font";i:0;s:4:"form";i:0;s:2:"h1";i:1;s:2:"h2";i:1;s:2:"h3";i:1;s:2:"h4";i:1;s:2:"h5";i:1;s:2:"h6";i:1;s:6:"header";i:0;s:6:"hgroup";i:0;s:2:"hr";i:2;s:1:"i";i:1;s:6:"iframe";i:0;s:3:"img";i:2;s:5:"input";i:0;s:3:"ins";i:0;s:6:"keygen";i:0;s:3:"kbd";i:0;s:5:"label";i:1;s:6:"legend";i:1;s:2:"li";i:2;s:3:"map";i:0;s:4:"mark";i:0;s:4:"menu";i:0;s:7:"marquee";i:0;s:5:"meter";i:0;s:3:"nav";i:0;s:4:"nobr";i:0;s:6:"object";i:0;s:2:"ol";i:2;s:8:"optgroup";i:0;s:6:"option";i:0;s:6:"output";i:0;s:1:"p";i:2;s:5:"param";i:0;s:3:"pre";i:2;s:8:"progress";i:0;s:1:"q";i:0;s:2:"rp";i:0;s:2:"rt";i:0;s:4:"ruby";i:0;s:1:"s";i:0;s:4:"samp";i:0;s:6:"script";i:0;s:7:"section";i:0;s:6:"select";i:0;s:5:"small";i:0;s:6:"source";i:0;s:4:"span";i:2;s:6:"strike";i:0;s:6:"strong";i:2;s:3:"sub";i:1;s:7:"summary";i:1;s:3:"sup";i:0;s:5:"table";i:2;s:5:"tbody";i:1;s:2:"td";i:2;s:8:"textarea";i:0;s:5:"tfoot";i:1;s:2:"th";i:2;s:5:"thead";i:0;s:4:"time";i:0;s:2:"tr";i:2;s:2:"tt";i:2;s:1:"u";i:0;s:2:"ul";i:2;s:3:"var";i:0;s:5:"video";i:0;s:3:"wbr";i:0;}');
INSERT INTO `module_vars` VALUES(168, 'ZikulaCategoriesModule', 'userrootcat', 's:17:"/__SYSTEM__/Users";');
INSERT INTO `module_vars` VALUES(169, 'ZikulaCategoriesModule', 'allowusercatedit', 'i:0;');
INSERT INTO `module_vars` VALUES(170, 'ZikulaCategoriesModule', 'autocreateusercat', 'i:0;');
INSERT INTO `module_vars` VALUES(171, 'ZikulaCategoriesModule', 'autocreateuserdefaultcat', 'i:0;');
INSERT INTO `module_vars` VALUES(172, 'ZikulaCategoriesModule', 'userdefaultcatname', 's:7:"Default";');
INSERT INTO `module_vars` VALUES(173, 'ZikulaMailerModule', 'mailertype', 'i:1;');
INSERT INTO `module_vars` VALUES(174, 'ZikulaMailerModule', 'charset', 's:5:"utf-8";');
INSERT INTO `module_vars` VALUES(175, 'ZikulaMailerModule', 'encoding', 's:4:"8bit";');
INSERT INTO `module_vars` VALUES(176, 'ZikulaMailerModule', 'html', 'b:0;');
INSERT INTO `module_vars` VALUES(177, 'ZikulaMailerModule', 'wordwrap', 'i:50;');
INSERT INTO `module_vars` VALUES(178, 'ZikulaMailerModule', 'msmailheaders', 'b:0;');
INSERT INTO `module_vars` VALUES(179, 'ZikulaMailerModule', 'sendmailpath', 's:18:"/usr/sbin/sendmail";');
INSERT INTO `module_vars` VALUES(180, 'ZikulaMailerModule', 'smtpauth', 'b:0;');
INSERT INTO `module_vars` VALUES(181, 'ZikulaMailerModule', 'smtpserver', 's:9:"localhost";');
INSERT INTO `module_vars` VALUES(182, 'ZikulaMailerModule', 'smtpport', 'i:25;');
INSERT INTO `module_vars` VALUES(183, 'ZikulaMailerModule', 'smtptimeout', 'i:10;');
INSERT INTO `module_vars` VALUES(184, 'ZikulaMailerModule', 'smtpusername', 's:0:"";');
INSERT INTO `module_vars` VALUES(185, 'ZikulaMailerModule', 'smtppassword', 's:0:"";');
INSERT INTO `module_vars` VALUES(186, 'ZikulaMailerModule', 'smtpsecuremethod', 's:3:"ssl";');
INSERT INTO `module_vars` VALUES(187, 'ZikulaSearchModule', 'itemsperpage', 'i:10;');
INSERT INTO `module_vars` VALUES(188, 'ZikulaSearchModule', 'limitsummary', 'i:255;');
INSERT INTO `module_vars` VALUES(189, '/EventHandlers', 'ZikulaSearchModule', 'a:1:{i:0;a:3:{s:9:"eventname";s:26:"installer.module.installed";s:8:"callable";a:2:{i:0;s:50:"Zikula\\Module\\SearchModule\\Listener\\ModuleListener";i:1;s:13:"moduleInstall";}s:6:"weight";i:10;}}');
INSERT INTO `module_vars` VALUES(190, 'systemplugin.imagine', 'version', 's:5:"1.0.0";');
INSERT INTO `module_vars` VALUES(191, 'systemplugin.imagine', 'thumb_dir', 's:20:"systemplugin.imagine";');
INSERT INTO `module_vars` VALUES(192, 'systemplugin.imagine', 'thumb_auto_cleanup', 'b:0;');
INSERT INTO `module_vars` VALUES(193, 'systemplugin.imagine', 'presets', 'a:1:{s:7:"default";C:27:"SystemPlugin_Imagine_Preset":178:{x:i:2;a:7:{s:5:"width";i:100;s:6:"height";i:100;s:4:"mode";N;s:9:"extension";N;s:8:"__module";N;s:9:"__imagine";N;s:16:"__transformation";N;};m:a:1:{s:7:"\0*\0name";s:7:"default";}}}');

-- --------------------------------------------------------

--
-- Table structure for table `objectdata_attributes`
--

CREATE TABLE IF NOT EXISTS `objectdata_attributes` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `objectdata_attributes`
--

INSERT INTO `objectdata_attributes` VALUES(1, 'code', 5, 'categories_category', 'Y', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `objectdata_attributes` VALUES(2, 'code', 6, 'categories_category', 'N', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `objectdata_attributes` VALUES(3, 'code', 11, 'categories_category', 'P', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `objectdata_attributes` VALUES(4, 'code', 12, 'categories_category', 'C', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `objectdata_attributes` VALUES(5, 'code', 13, 'categories_category', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `objectdata_attributes` VALUES(6, 'code', 14, 'categories_category', 'O', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `objectdata_attributes` VALUES(7, 'code', 15, 'categories_category', 'R', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `objectdata_attributes` VALUES(8, 'code', 17, 'categories_category', 'M', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `objectdata_attributes` VALUES(9, 'code', 18, 'categories_category', 'F', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `objectdata_attributes` VALUES(10, 'code', 26, 'categories_category', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `objectdata_attributes` VALUES(11, 'code', 27, 'categories_category', 'I', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `objectdata_attributes` VALUES(12, 'code', 29, 'categories_category', 'P', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);
INSERT INTO `objectdata_attributes` VALUES(13, 'code', 30, 'categories_category', 'A', 'A', '2013-03-26 10:29:11', 0, '2013-03-26 10:29:11', 0);

-- --------------------------------------------------------

--
-- Table structure for table `objectdata_log`
--

CREATE TABLE IF NOT EXISTS `objectdata_log` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `objectdata_meta`
--

CREATE TABLE IF NOT EXISTS `objectdata_meta` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sc_intrusion`
--

CREATE TABLE IF NOT EXISTS `sc_intrusion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `tag` varchar(40) DEFAULT NULL,
  `value` longtext NOT NULL,
  `page` longtext NOT NULL,
  `ip` varchar(40) NOT NULL,
  `impact` int(11) NOT NULL,
  `filters` longtext NOT NULL,
  `date` datetime NOT NULL,
  `uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8595CE46539B0606` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `search_result`
--

CREATE TABLE IF NOT EXISTS `search_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `text` longtext,
  `module` varchar(100) DEFAULT NULL,
  `extra` varchar(100) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `found` datetime DEFAULT NULL,
  `sesid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `search_stat`
--

CREATE TABLE IF NOT EXISTS `search_stat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `search` varchar(50) NOT NULL,
  `scount` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `session_info`
--

CREATE TABLE IF NOT EXISTS `session_info` (
  `sessid` varchar(40) NOT NULL,
  `ipaddr` varchar(32) NOT NULL,
  `lastused` datetime NOT NULL,
  `uid` int(11) NOT NULL,
  `remember` smallint(6) NOT NULL,
  `vars` longtext NOT NULL,
  PRIMARY KEY (`sessid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `session_info`
--

INSERT INTO `session_info` VALUES('vk94gce8drgak6o5anulvlpak5', '', '2013-03-26 10:29:28', 2, 0, '_sf2_attributes|a:6:{s:3:"uid";i:2;s:21:"authentication_method";a:2:{s:7:"modname";s:17:"ZikulaUsersModule";s:6:"method";s:5:"uname";}s:5:"state";N;s:4:"sort";s:4:"name";s:7:"sortdir";s:3:"ASC";s:7:"_tokens";a:1:{s:23:"5151788a074546.24120821";a:2:{s:5:"token";s:92:"NTE1MTc4OGEwNzQ1NDYuMjQxMjA4MjE6Yzg4ZGQ5NTFkYmZkNzZjYTZkZDY2OTZkMjIzZGQ4MmU6MTM2NDI5Mzc3MA==";s:4:"time";i:1364293770;}}}_sf2_flashes|a:0:{}_sf2_meta|a:3:{s:1:"u";i:1364293769;s:1:"c";i:1364293738;s:1:"l";s:9:"788940000";}');

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE IF NOT EXISTS `themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `type` smallint(6) NOT NULL,
  `displayname` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL,
  `directory` varchar(64) NOT NULL,
  `version` varchar(10) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `admin` smallint(6) NOT NULL,
  `user` smallint(6) NOT NULL,
  `system` smallint(6) NOT NULL,
  `state` smallint(6) NOT NULL,
  `xhtml` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` VALUES(1, 'ZikulaAndreas08Theme', 3, 'Andreas08', 'Based on the theme Andreas08 by Andreas Viklund and extended for Zikula with the CSS Framework ''fluid960gs''.', 'Zikula/Theme/Andreas08Theme', '2.0.0', '3', 1, 1, 0, 1, 1);
INSERT INTO `themes` VALUES(2, 'ZikulaAtomTheme', 3, 'Atom', 'The Atom theme is an auxiliary theme specially designed for rendering pages in Atom mark-up.', 'Zikula/Theme/AtomTheme', '1.0.0', '3', 0, 0, 1, 1, 1);
INSERT INTO `themes` VALUES(3, 'ZikulaMobileTheme', 3, 'Mobile', 'The mobile theme is an auxiliary theme designed specially for outputting pages in a mobile-friendly format.', 'Zikula/Theme/MobileTheme', '1.0.0', '3', 0, 0, 1, 1, 1);
INSERT INTO `themes` VALUES(4, 'ZikulaPrinterTheme', 3, 'Printer', 'The Printer theme is an auxiliary theme designed specially for outputting pages in a printer-friendly format.', 'Zikula/Theme/PrinterTheme', '2.0.0', '3', 0, 0, 1, 1, 1);
INSERT INTO `themes` VALUES(5, 'ZikulaRSSTheme', 3, 'RSS', 'The RSS theme is an auxiliary theme designed specially for outputting pages as an RSS feed.', 'Zikula/Theme/RssTheme', '0', '3', 0, 0, 1, 1, 1);
INSERT INTO `themes` VALUES(6, 'ZikulaSeaBreezeTheme', 3, 'SeaBreeze', 'The SeaBreeze theme is a browser-oriented theme.', 'Zikula/Theme/SeaBreezeTheme', '3.2.0', '3', 0, 1, 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `userblocks`
--

CREATE TABLE IF NOT EXISTS `userblocks` (
  `uid` int(11) NOT NULL,
  `bid` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `last_update` datetime NOT NULL,
  PRIMARY KEY (`uid`,`bid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `uname` varchar(25) NOT NULL,
  `email` varchar(60) NOT NULL,
  `pass` varchar(138) NOT NULL,
  `passreminder` varchar(255) NOT NULL,
  `activated` smallint(6) NOT NULL,
  `approved_date` datetime NOT NULL,
  `approved_by` int(11) NOT NULL,
  `user_regdate` datetime NOT NULL,
  `lastlogin` datetime NOT NULL,
  `theme` varchar(255) NOT NULL,
  `ublockon` smallint(6) NOT NULL,
  `ublock` longtext NOT NULL,
  `tz` varchar(30) NOT NULL,
  `locale` varchar(5) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `uname` (`uname`),
  KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` VALUES(1, 'guest', '', '', '', 1, '1970-01-01 00:00:00', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '', 0, '', '', '');
INSERT INTO `users` VALUES(2, 'admin', 'example@example.com', '8$*bQoH$637bbd76985f3ff002982ed46820976b7a5fbda1e111496ea6d9488f69d3b1cb', '', 1, '2013-03-26 10:29:08', 2, '2013-03-26 10:29:18', '2013-03-26 10:29:18', '', 0, '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `users_attributes`
--

CREATE TABLE IF NOT EXISTS `users_attributes` (
  `user_id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`user_id`,`name`),
  KEY `IDX_E6F031E4A76ED395` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_verifychg`
--

CREATE TABLE IF NOT EXISTS `users_verifychg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `changetype` smallint(6) NOT NULL,
  `uid` int(11) NOT NULL,
  `newemail` varchar(60) NOT NULL,
  `verifycode` varchar(138) NOT NULL,
  `created_dt` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `workflows`
--

CREATE TABLE IF NOT EXISTS `workflows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metaid` int(11) NOT NULL,
  `module` varchar(255) NOT NULL,
  `schemaname` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `type` smallint(6) NOT NULL,
  `obj_table` varchar(40) NOT NULL,
  `obj_idcolumn` varchar(40) NOT NULL,
  `obj_id` int(11) NOT NULL,
  `busy` int(11) NOT NULL,
  `debug` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
