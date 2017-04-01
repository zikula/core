-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Apr 01, 2017 at 11:17 AM
-- Server version: 5.5.42
-- PHP Version: 5.5.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zk14x`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_category`
--

CREATE TABLE `admin_category` (
  `cid` int(11) NOT NULL,
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sortorder` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `admin_category`
--

INSERT INTO `admin_category` (`cid`, `name`, `description`, `sortorder`) VALUES
(1, 'System', 'Core modules at the heart of operation of the site.', 0),
(2, 'Layout', 'Layout modules for controlling the site''s look and feel.', 1),
(3, 'Users', 'Modules for controlling user membership, access rights and profiles.', 2),
(4, 'Content', 'Modules for providing content to your users.', 3),
(5, 'Uncategorised', 'Newly-installed or uncategorized modules.', 4),
(6, 'Security', 'Modules for managing the site''s security.', 5);

-- --------------------------------------------------------

--
-- Table structure for table `admin_module`
--

CREATE TABLE `admin_module` (
  `amid` int(11) NOT NULL,
  `mid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `sortorder` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `admin_module`
--

INSERT INTO `admin_module` (`amid`, `mid`, `cid`, `sortorder`) VALUES
(1, 2, 1, 0),
(2, 3, 2, 0),
(3, 4, 4, 0),
(4, 1, 1, 1),
(5, 5, 3, 0),
(6, 6, 1, 2),
(7, 9, 3, 1),
(8, 10, 1, 3),
(9, 11, 4, 1),
(10, 12, 6, 0),
(11, 13, 1, 4),
(12, 14, 2, 1),
(13, 15, 3, 2),
(14, 16, 3, 3),
(15, 7, 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `blocks`
--

CREATE TABLE `blocks` (
  `bid` int(11) NOT NULL,
  `bkey` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `blocktype` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `properties` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `filter` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `active` int(11) NOT NULL,
  `last_update` datetime NOT NULL,
  `language` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `mid` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `blocks`
--

INSERT INTO `blocks` (`bid`, `bkey`, `blocktype`, `title`, `description`, `properties`, `filter`, `active`, `last_update`, `language`, `mid`) VALUES
(1, 'ZikulaSearchModule:\\Zikula\\SearchModule\\Block\\SearchBlock', 'Search', 'Search box', 'Search block', 'a:2:{s:16:"displaySearchBtn";b:1;s:6:"active";a:1:{s:17:"ZikulaUsersModule";i:1;}}', 'a:0:{}', 1, '2017-04-01 11:17:25', '', 11),
(2, 'ZikulaBlocksModule:\\Zikula\\BlocksModule\\Block\\HtmlBlock', 'Html', 'This site is powered by Zikula!', 'HTML block', 'a:1:{s:7:"content";s:1216:"<p><a href="http://zikula.org/">Zikula</a> is a content management system (CMS) and application framework. It is secure and stable, and is a good choice for sites with a large volume of traffic.</p><p>With Zikula:</p><ul><li>you can customise all aspects of the site''s appearance through themes, with support for CSS style sheets, JavaScript, Flash and all other modern web development technologies;</li><li>you can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation and internationalisation of your site;</li><li>you can be sure that your pages will display properly in all browsers, thanks to Zikula''s full compliance with W3C HTML standards;</li><li>you get a standard application-programming interface (API) that lets you easily augment your site''s functionality through modules, blocks and other extensions;</li><li>you can get help and support from the Zikula community of webmasters and developers at <a href="http://www.zikula.org">zikula.org</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>";}', 'a:0:{}', 1, '2017-04-01 11:17:25', '', 3),
(3, 'ZikulaUsersModule:\\Zikula\\UsersModule\\Block\\LoginBlock', 'Login', 'User log-in', 'Login block', 'a:0:{}', 'a:1:{i:0;a:4:{s:9:"attribute";s:6:"_route";s:14:"queryParameter";N;s:10:"comparator";s:2:"!=";s:5:"value";s:30:"zikulausersmodule_access_login";}}', 1, '2017-04-01 11:17:25', '', 15),
(4, 'ZikulaMenuModule:\\Zikula\\MenuModule\\Block\\MenuBlock', 'Menu', 'Main menu', 'Main menu', 'a:2:{s:4:"name";s:8:"mainMenu";s:7:"options";s:73:"{"template": "ZikulaMenuModule:Override:bootstrap_fontawesome.html.twig"}";}', 'a:0:{}', 1, '2017-04-01 11:17:25', '', 7);

-- --------------------------------------------------------

--
-- Table structure for table `block_placements`
--

CREATE TABLE `block_placements` (
  `pid` int(11) NOT NULL,
  `bid` int(11) NOT NULL,
  `sortorder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `block_placements`
--

INSERT INTO `block_placements` (`pid`, `bid`, `sortorder`) VALUES
(1, 1, 0),
(3, 2, 0),
(7, 3, 1),
(7, 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `block_positions`
--

CREATE TABLE `block_positions` (
  `pid` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `block_positions`
--

INSERT INTO `block_positions` (`pid`, `name`, `description`) VALUES
(1, 'left', 'Left blocks'),
(2, 'right', 'Right blocks'),
(3, 'center', 'Center blocks'),
(4, 'search', 'Search block'),
(5, 'header', 'Header block'),
(6, 'footer', 'Footer block'),
(7, 'topnav', 'Top navigation block'),
(8, 'bottomnav', 'Bottom navigation block');

-- --------------------------------------------------------

--
-- Table structure for table `bundles`
--

CREATE TABLE `bundles` (
  `id` int(11) NOT NULL,
  `bundlename` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `autoload` varchar(384) COLLATE utf8_unicode_ci NOT NULL,
  `bundleclass` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `bundletype` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `bundlestate` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `bundles`
--

INSERT INTO `bundles` (`id`, `bundlename`, `autoload`, `bundleclass`, `bundletype`, `bundlestate`) VALUES
(1, 'zikula/pages-module', 'a:1:{s:5:"psr-4";a:1:{s:19:"Zikula\\PagesModule\\";s:13:"modules/Pages";}}', 'Zikula\\PagesModule\\ZikulaPagesModule', 'M', 3),
(2, 'zikula/legal-module', 'a:1:{s:5:"psr-4";a:1:{s:19:"Zikula\\LegalModule\\";s:27:"modules/zikula/legal-module";}}', 'Zikula\\LegalModule\\ZikulaLegalModule', 'M', 3),
(3, 'zikula/profile-module', 'a:1:{s:5:"psr-4";a:1:{s:21:"Zikula\\ProfileModule\\";s:29:"modules/zikula/profile-module";}}', 'Zikula\\ProfileModule\\ZikulaProfileModule', 'M', 3),
(4, 'zikula/andreas08-theme', 'a:1:{s:5:"psr-4";a:1:{s:22:"Zikula\\Andreas08Theme\\";s:21:"themes/Andreas08Theme";}}', 'Zikula\\Andreas08Theme\\ZikulaAndreas08Theme', 'T', 3),
(5, 'zikula/atom-theme', 'a:1:{s:5:"psr-4";a:1:{s:17:"Zikula\\AtomTheme\\";s:16:"themes/AtomTheme";}}', 'Zikula\\AtomTheme\\ZikulaAtomTheme', 'T', 3),
(6, 'zikula/bootstrap-theme', 'a:1:{s:5:"psr-4";a:1:{s:22:"Zikula\\BootstrapTheme\\";s:21:"themes/BootstrapTheme";}}', 'Zikula\\BootstrapTheme\\ZikulaBootstrapTheme', 'T', 3),
(7, 'zikula/printer-theme', 'a:1:{s:5:"psr-4";a:1:{s:20:"Zikula\\PrinterTheme\\";s:19:"themes/PrinterTheme";}}', 'Zikula\\PrinterTheme\\ZikulaPrinterTheme', 'T', 3),
(8, 'zikula/rss-theme', 'a:1:{s:5:"psr-4";a:1:{s:16:"Zikula\\RssTheme\\";s:15:"themes/RssTheme";}}', 'Zikula\\RssTheme\\ZikulaRssTheme', 'T', 3),
(9, 'zikula/seabreeze-theme', 'a:1:{s:5:"psr-4";a:1:{s:22:"Zikula\\SeaBreezeTheme\\";s:21:"themes/SeaBreezeTheme";}}', 'Zikula\\SeaBreezeTheme\\ZikulaSeaBreezeTheme', 'T', 3);

-- --------------------------------------------------------

--
-- Table structure for table `categories_attributes`
--

CREATE TABLE `categories_attributes` (
  `category_id` int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `categories_attributes`
--

INSERT INTO `categories_attributes` (`category_id`, `name`, `value`) VALUES
(5, 'Y', 'code'),
(6, 'N', 'code'),
(11, 'P', 'code'),
(12, 'C', 'code'),
(13, 'A', 'code'),
(14, 'O', 'code'),
(15, 'R', 'code'),
(17, 'M', 'code'),
(18, 'F', 'code'),
(26, 'A', 'code'),
(27, 'I', 'code'),
(29, 'P', 'code'),
(30, 'A', 'code');

-- --------------------------------------------------------

--
-- Table structure for table `categories_category`
--

CREATE TABLE `categories_category` (
  `id` int(11) NOT NULL,
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
  `lu_uid` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `categories_category`
--

INSERT INTO `categories_category` (`id`, `parent_id`, `is_locked`, `is_leaf`, `name`, `value`, `sort_value`, `display_name`, `display_desc`, `path`, `ipath`, `status`, `cr_date`, `lu_date`, `obj_status`, `cr_uid`, `lu_uid`) VALUES
(1, NULL, 1, 0, '__SYSTEM__', '', 1, 's:0:"";', 's:0:"";', '/__SYSTEM__', '/1', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(2, 1, 0, 0, 'Modules', '', 2, 'a:1:{s:2:"en";s:7:"Modules";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules', '/1/2', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(3, 1, 0, 0, 'General', '', 3, 'a:1:{s:2:"en";s:7:"General";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General', '/1/3', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(4, 3, 0, 0, 'YesNo', '', 4, 'a:1:{s:2:"en";s:6:"Yes/No";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/YesNo', '/1/3/4', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(5, 4, 0, 1, '1 - Yes', 'Y', 5, 's:0:"";', 's:0:"";', '/__SYSTEM__/General/YesNo/1 - Yes', '/1/3/4/5', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(6, 4, 0, 1, '2 - No', 'N', 6, 's:0:"";', 's:0:"";', '/__SYSTEM__/General/YesNo/2 - No', '/1/3/4/6', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(10, 3, 0, 0, 'Publication Status (extended)', '', 10, 'a:1:{s:2:"en";s:29:"Publication status (extended)";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Extended', '/1/3/10', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(11, 10, 0, 1, 'Pending', 'P', 11, 'a:1:{s:2:"en";s:7:"Pending";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Extended/Pending', '/1/3/10/11', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(12, 10, 0, 1, 'Checked', 'C', 12, 'a:1:{s:2:"en";s:7:"Checked";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Extended/Checked', '/1/3/10/12', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(13, 10, 0, 1, 'Approved', 'A', 13, 'a:1:{s:2:"en";s:8:"Approved";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Extended/Approved', '/1/3/10/13', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(14, 10, 0, 1, 'On-line', 'O', 14, 'a:1:{s:2:"en";s:7:"On-line";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Extended/Online', '/1/3/10/14', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(15, 10, 0, 1, 'Rejected', 'R', 15, 'a:1:{s:2:"en";s:8:"Rejected";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Extended/Rejected', '/1/3/10/15', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(16, 3, 0, 0, 'Gender', '', 16, 'a:1:{s:2:"en";s:6:"Gender";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Gender', '/1/3/16', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(17, 16, 0, 1, 'Male', 'M', 17, 'a:1:{s:2:"en";s:4:"Male";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Gender/Male', '/1/3/16/17', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(18, 16, 0, 1, 'Female', 'F', 18, 'a:1:{s:2:"en";s:6:"Female";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Gender/Female', '/1/3/16/18', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(19, 3, 0, 0, 'Title', '', 19, 'a:1:{s:2:"en";s:5:"Title";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Title', '/1/3/19', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(20, 19, 0, 1, 'Mr', 'Mr', 20, 'a:1:{s:2:"en";s:3:"Mr.";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Title/Mr', '/1/3/19/20', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(21, 19, 0, 1, 'Mrs', 'Mrs', 21, 'a:1:{s:2:"en";s:4:"Mrs.";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Title/Mrs', '/1/3/19/21', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(22, 19, 0, 1, 'Ms', 'Ms', 22, 'a:1:{s:2:"en";s:3:"Ms.";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Title/Ms', '/1/3/19/22', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(23, 19, 0, 1, 'Miss', 'Miss', 23, 'a:1:{s:2:"en";s:4:"Miss";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Title/Miss', '/1/3/19/23', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(24, 19, 0, 1, 'Dr', 'Dr', 24, 'a:1:{s:2:"en";s:3:"Dr.";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Title/Dr', '/1/3/19/24', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(25, 3, 0, 0, 'ActiveStatus', '', 25, 'a:1:{s:2:"en";s:15:"Activity status";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/ActiveStatus', '/1/3/25', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(26, 25, 0, 1, 'Active', 'A', 26, 'a:1:{s:2:"en";s:6:"Active";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/ActiveStatus/Active', '/1/3/25/26', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(27, 25, 0, 1, 'Inactive', 'I', 27, 'a:1:{s:2:"en";s:8:"Inactive";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/ActiveStatus/Inactive', '/1/3/25/27', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(28, 3, 0, 0, 'Publication status (basic)', '', 28, 'a:1:{s:2:"en";s:26:"Publication status (basic)";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Basic', '/1/3/28', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(29, 28, 0, 1, 'Pending', 'P', 29, 'a:1:{s:2:"en";s:7:"Pending";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Basic/Pending', '/1/3/28/29', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(30, 28, 0, 1, 'Approved', 'A', 30, 'a:1:{s:2:"en";s:8:"Approved";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/General/Publication Status Basic/Approved', '/1/3/28/30', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(31, 1, 0, 0, 'ZikulaUsersModule', '', 31, 'a:1:{s:2:"en";s:5:"Users";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Users', '/1/31', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(32, 2, 0, 0, 'Global', '', 32, 'a:1:{s:2:"en";s:6:"Global";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global', '/1/2/32', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(33, 32, 0, 1, 'Blogging', '', 33, 'a:1:{s:2:"en";s:8:"Blogging";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/Blogging', '/1/2/32/33', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(34, 32, 0, 1, 'Music and audio', '', 34, 'a:1:{s:2:"en";s:15:"Music and audio";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/MusicAndAudio', '/1/2/32/34', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(35, 32, 0, 1, 'Art and photography', '', 35, 'a:1:{s:2:"en";s:19:"Art and photography";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/ArtAndPhotography', '/1/2/32/35', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(36, 32, 0, 1, 'Writing and thinking', '', 36, 'a:1:{s:2:"en";s:20:"Writing and thinking";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/WritingAndThinking', '/1/2/32/36', 'A', '2017-04-01 11:17:15', '2017-04-01 11:17:15', 'A', 2, 2),
(37, 32, 0, 1, 'Communications and media', '', 37, 'a:1:{s:2:"en";s:24:"Communications and media";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/CommunicationsAndMedia', '/1/2/32/37', 'A', '2017-04-01 11:17:16', '2017-04-01 11:17:16', 'A', 2, 2),
(38, 32, 0, 1, 'Travel and culture', '', 38, 'a:1:{s:2:"en";s:18:"Travel and culture";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/TravelAndCulture', '/1/2/32/38', 'A', '2017-04-01 11:17:16', '2017-04-01 11:17:16', 'A', 2, 2),
(39, 32, 0, 1, 'Science and technology', '', 39, 'a:1:{s:2:"en";s:22:"Science and technology";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/ScienceAndTechnology', '/1/2/32/39', 'A', '2017-04-01 11:17:16', '2017-04-01 11:17:16', 'A', 2, 2),
(40, 32, 0, 1, 'Sport and activities', '', 40, 'a:1:{s:2:"en";s:20:"Sport and activities";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/SportAndActivities', '/1/2/32/40', 'A', '2017-04-01 11:17:16', '2017-04-01 11:17:16', 'A', 2, 2),
(41, 32, 0, 1, 'Business and work', '', 41, 'a:1:{s:2:"en";s:17:"Business and work";}', 'a:1:{s:2:"en";s:0:"";}', '/__SYSTEM__/Modules/Global/BusinessAndWork', '/1/2/32/41', 'A', '2017-04-01 11:17:16', '2017-04-01 11:17:16', 'A', 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `categories_mapobj`
--

CREATE TABLE `categories_mapobj` (
  `id` int(11) NOT NULL,
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
  `lu_uid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories_registry`
--

CREATE TABLE `categories_registry` (
  `id` int(11) NOT NULL,
  `modname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `entityname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `property` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(11) NOT NULL,
  `cr_date` datetime NOT NULL,
  `lu_date` datetime NOT NULL,
  `obj_status` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `cr_uid` int(11) DEFAULT NULL,
  `lu_uid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `gid` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `gtype` smallint(6) NOT NULL,
  `description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `prefix` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `state` smallint(6) NOT NULL,
  `nbuser` int(11) NOT NULL,
  `nbumax` int(11) NOT NULL,
  `link` int(11) NOT NULL,
  `uidmaster` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`gid`, `name`, `gtype`, `description`, `prefix`, `state`, `nbuser`, `nbumax`, `link`, `uidmaster`) VALUES
(1, 'Users', 0, 'By default, all users are made members of this group.', 'usr', 0, 0, 0, 0, 0),
(2, 'Administrators', 0, 'Group of administrators of this site.', 'adm', 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `group_applications`
--

CREATE TABLE `group_applications` (
  `app_id` int(11) NOT NULL,
  `gid` int(11) DEFAULT NULL,
  `application` longtext COLLATE utf8_unicode_ci NOT NULL,
  `status` smallint(6) NOT NULL,
  `uid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_membership`
--

CREATE TABLE `group_membership` (
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `group_membership`
--

INSERT INTO `group_membership` (`uid`, `gid`) VALUES
(1, 1),
(2, 1),
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `group_perms`
--

CREATE TABLE `group_perms` (
  `pid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `sequence` int(11) NOT NULL,
  `realm` int(11) NOT NULL,
  `component` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `instance` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `level` int(11) NOT NULL,
  `bond` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `group_perms`
--

INSERT INTO `group_perms` (`pid`, `gid`, `sequence`, `realm`, `component`, `instance`, `level`, `bond`) VALUES
(1, 2, 1, 0, '.*', '.*', 800, 0),
(2, -1, 2, 0, 'ZikulaThemeModule::ThemeChange', ':(ZikulaRssTheme|ZikulaPrinterTheme|ZikulaAtomTheme):', 300, 0),
(3, 1, 2, 0, '.*', '.*', 300, 0),
(4, 0, 3, 0, '.*', '.*', 200, 0);

-- --------------------------------------------------------

--
-- Table structure for table `hook_area`
--

CREATE TABLE `hook_area` (
  `id` int(11) NOT NULL,
  `owner` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subowner` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `areatype` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `category` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `areaname` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `hook_area`
--

INSERT INTO `hook_area` (`id`, `owner`, `subowner`, `areatype`, `category`, `areaname`) VALUES
(1, 'ZikulaUsersModule', NULL, 's', 'ui_hooks', 'subscriber.users.ui_hooks.user'),
(2, 'ZikulaUsersModule', NULL, 's', 'ui_hooks', 'subscriber.users.ui_hooks.registration'),
(3, 'ZikulaUsersModule', NULL, 's', 'ui_hooks', 'subscriber.users.ui_hooks.login_screen'),
(4, 'ZikulaBlocksModule', NULL, 's', 'ui_hooks', 'subscriber.blocks.ui_hooks.htmlblock.content'),
(5, 'ZikulaMailerModule', NULL, 's', 'ui_hooks', 'subscriber.mailer.ui_hooks.htmlmail');

-- --------------------------------------------------------

--
-- Table structure for table `hook_binding`
--

CREATE TABLE `hook_binding` (
  `id` int(11) NOT NULL,
  `sowner` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subsowner` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `powner` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subpowner` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sareaid` int(11) NOT NULL,
  `pareaid` int(11) NOT NULL,
  `category` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `sortorder` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hook_provider`
--

CREATE TABLE `hook_provider` (
  `id` int(11) NOT NULL,
  `owner` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subowner` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pareaid` int(11) NOT NULL,
  `hooktype` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `category` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `classname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `serviceid` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hook_runtime`
--

CREATE TABLE `hook_runtime` (
  `id` int(11) NOT NULL,
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
  `priority` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hook_subscriber`
--

CREATE TABLE `hook_subscriber` (
  `id` int(11) NOT NULL,
  `owner` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subowner` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sareaid` int(11) NOT NULL,
  `hooktype` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `category` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `eventname` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `hook_subscriber`
--

INSERT INTO `hook_subscriber` (`id`, `owner`, `subowner`, `sareaid`, `hooktype`, `category`, `eventname`) VALUES
(1, 'ZikulaUsersModule', NULL, 1, 'display_view', 'ui_hooks', 'users.ui_hooks.user.display_view'),
(2, 'ZikulaUsersModule', NULL, 1, 'form_edit', 'ui_hooks', 'users.ui_hooks.user.form_edit'),
(3, 'ZikulaUsersModule', NULL, 1, 'validate_edit', 'ui_hooks', 'users.ui_hooks.user.validate_edit'),
(4, 'ZikulaUsersModule', NULL, 1, 'process_edit', 'ui_hooks', 'users.ui_hooks.user.process_edit'),
(5, 'ZikulaUsersModule', NULL, 1, 'form_delete', 'ui_hooks', 'users.ui_hooks.user.form_delete'),
(6, 'ZikulaUsersModule', NULL, 1, 'validate_delete', 'ui_hooks', 'users.ui_hooks.user.validate_delete'),
(7, 'ZikulaUsersModule', NULL, 1, 'process_delete', 'ui_hooks', 'users.ui_hooks.user.process_delete'),
(8, 'ZikulaUsersModule', NULL, 2, 'display_view', 'ui_hooks', 'users.ui_hooks.registration.display_view'),
(9, 'ZikulaUsersModule', NULL, 2, 'form_edit', 'ui_hooks', 'users.ui_hooks.registration.form_edit'),
(10, 'ZikulaUsersModule', NULL, 2, 'validate_edit', 'ui_hooks', 'users.ui_hooks.registration.validate_edit'),
(11, 'ZikulaUsersModule', NULL, 2, 'process_edit', 'ui_hooks', 'users.ui_hooks.registration.process_edit'),
(12, 'ZikulaUsersModule', NULL, 2, 'form_delete', 'ui_hooks', 'users.ui_hooks.registration.form_delete'),
(13, 'ZikulaUsersModule', NULL, 2, 'validate_delete', 'ui_hooks', 'users.ui_hooks.registration.validate_delete'),
(14, 'ZikulaUsersModule', NULL, 2, 'process_delete', 'ui_hooks', 'users.ui_hooks.registration.process_delete'),
(15, 'ZikulaUsersModule', NULL, 3, 'form_edit', 'ui_hooks', 'users.ui_hooks.login_screen.form_edit'),
(16, 'ZikulaUsersModule', NULL, 3, 'validate_edit', 'ui_hooks', 'users.ui_hooks.login_screen.validate_edit'),
(17, 'ZikulaUsersModule', NULL, 3, 'process_edit', 'ui_hooks', 'users.ui_hooks.login_screen.process_edit'),
(18, 'ZikulaBlocksModule', NULL, 4, 'form_edit', 'ui_hooks', 'blocks.ui_hooks.htmlblock.content.form_edit'),
(19, 'ZikulaMailerModule', NULL, 5, 'form_edit', 'ui_hooks', 'mailer.ui_hooks.htmlmail.form_edit');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `root_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `lft` int(11) NOT NULL,
  `lvl` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `options` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)'
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `root_id`, `parent_id`, `title`, `lft`, `lvl`, `rgt`, `options`) VALUES
(1, 1, NULL, 'mainMenu', 1, 0, 6, 'a:1:{s:18:"childrenAttributes";a:1:{s:5:"class";s:14:"nav navbar-nav";}}'),
(2, 1, 1, 'Home', 2, 1, 3, 'a:2:{s:5:"route";s:4:"home";s:10:"attributes";a:1:{s:4:"icon";s:10:"fa fa-home";}}'),
(3, 1, 1, 'Site search', 4, 1, 5, 'a:2:{s:5:"route";s:28:"zikulasearchmodule_user_form";s:10:"attributes";a:1:{s:4:"icon";s:12:"fa fa-search";}}');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
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
  `core_min` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `core_max` varchar(64) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `name`, `type`, `displayname`, `url`, `description`, `directory`, `version`, `capabilities`, `state`, `securityschema`, `core_min`, `core_max`) VALUES
(1, 'ZikulaExtensionsModule', 3, 'Extensions', 'extensions', 'Extensions administration', 'ExtensionsModule', '3.7.14', 'a:1:{s:5:"admin";a:1:{s:5:"route";s:44:"zikulaextensionsmodule_module_viewmodulelist";}}', 3, 'a:2:{s:24:"ZikulaExtensionsModule::";s:2:"::";s:30:"ZikulaExtensionsModule::modify";s:27:"extensionName::extensionsId";}', '>=1.4.2', ''),
(2, 'ZikulaAdminModule', 3, 'Administration panel', 'adminpanel', 'Backend administration interface', 'AdminModule', '2.0.0', 'a:1:{s:5:"admin";a:1:{s:5:"route";s:28:"zikulaadminmodule_admin_view";}}', 3, 'a:2:{s:19:"ZikulaAdminModule::";s:38:"Admin Category name::Admin Category ID";s:32:"ZikulaAdminModule:adminnavblock:";s:21:"Block title::Block ID";}', '>=1.4.1', ''),
(3, 'ZikulaBlocksModule', 3, 'Blocks Module', 'blocks', 'Blocks administration', 'BlocksModule', '3.9.7', 'a:2:{s:5:"admin";a:1:{s:5:"route";s:29:"zikulablocksmodule_admin_view";}s:15:"hook_subscriber";a:1:{s:5:"class";s:43:"Zikula\\BlocksModule\\Container\\HookContainer";}}', 3, 'a:13:{s:20:"ZikulaBlocksModule::";s:30:"Block key:Block title:Block ID";s:28:"ZikulaBlocksModule::position";s:26:"Position name::Position ID";s:19:"ExtendedMenublock::";s:17:"Block ID:Link ID:";s:15:"fincludeblock::";s:13:"Block title::";s:11:"HTMLblock::";s:13:"Block title::";s:14:"HTMLblock::bid";s:5:"::bid";s:15:"Languageblock::";s:13:"Block title::";s:11:"Menublock::";s:22:"Block title:Link name:";s:23:"Menutree:menutreeblock:";s:26:"Block ID:Link Name:Link ID";s:16:"PendingContent::";s:13:"Block title::";s:11:"Textblock::";s:13:"Block title::";s:14:"Textblock::bid";s:5:"::bid";s:11:"xsltblock::";s:13:"Block title::";}', '>=1.4.1', ''),
(4, 'ZikulaCategoriesModule', 3, 'Categories', 'categories', 'Categories administration', 'CategoriesModule', '1.3.0', 'a:2:{s:5:"admin";a:1:{s:5:"route";s:33:"zikulacategoriesmodule_admin_view";}s:4:"user";a:1:{s:3:"url";s:25:"/core.git/src/categories/";}}', 3, 'a:2:{s:24:"ZikulaCategoriesModule::";s:2:"::";s:32:"ZikulaCategoriesModule::Category";s:40:"Category ID:Category Path:Category IPath";}', '>=1.4.1', ''),
(5, 'ZikulaGroupsModule', 3, 'Groups', 'groups', 'User group administration', 'GroupsModule', '2.4.1', 'a:2:{s:5:"admin";a:1:{s:5:"route";s:34:"zikulagroupsmodule_group_adminlist";}s:4:"user";a:1:{s:5:"route";s:29:"zikulagroupsmodule_group_list";}}', 3, 'a:2:{s:20:"ZikulaGroupsModule::";s:10:"Group ID::";s:31:"ZikulaGroupsModule::memberslist";s:2:"::";}', '>=1.4.1', ''),
(6, 'ZikulaMailerModule', 3, 'Mailer Module', 'mailer', 'Mailer support', 'MailerModule', '1.5.0', 'a:2:{s:5:"admin";a:1:{s:5:"route";s:32:"zikulamailermodule_config_config";}s:15:"hook_subscriber";a:1:{s:5:"class";s:43:"Zikula\\MailerModule\\Container\\HookContainer";}}', 3, 'a:1:{s:20:"ZikulaMailerModule::";s:2:"::";}', '>=1.4.1', ''),
(7, 'ZikulaMenuModule', 3, 'MenuModule', 'menu', 'Menu management', 'MenuModule', '1.0.0', 'a:1:{s:5:"admin";a:1:{s:5:"route";s:26:"zikulamenumodule_menu_list";}}', 3, 'a:1:{s:18:"ZikulaMenuModule::";s:2:"::";}', '>=1.4.4', ''),
(8, 'ZikulaPageLockModule', 2, 'Page lock', 'pagelock', 'Page locking support', 'PageLockModule', '1.2.0', 'a:0:{}', 1, 'a:1:{s:22:"ZikulaPageLockModule::";s:2:"::";}', '>=1.4.1', ''),
(9, 'ZikulaPermissionsModule', 3, 'Permissions', 'permissions', 'User permissions manager', 'PermissionsModule', '1.2.0', 'a:1:{s:5:"admin";a:1:{s:5:"route";s:39:"zikulapermissionsmodule_permission_list";}}', 3, 'a:1:{s:25:"ZikulaPermissionsModule::";s:2:"::";}', '>=1.4.1', ''),
(10, 'ZikulaRoutesModule', 3, 'Routes', 'routes', 'Routes management', 'RoutesModule', '1.1.1', 'a:2:{s:4:"user";a:1:{s:5:"route";s:30:"zikularoutesmodule_route_index";}s:5:"admin";a:1:{s:5:"route";s:35:"zikularoutesmodule_route_adminindex";}}', 3, 'a:3:{s:20:"ZikulaRoutesModule::";s:2:"::";s:25:"ZikulaRoutesModule:Route:";s:10:"Route ID::";s:24:"ZikulaRoutesModule::Ajax";s:2:"::";}', '>=1.4.5 <3.0', ''),
(11, 'ZikulaSearchModule', 3, 'Site search', 'search', 'Site search support', 'SearchModule', '1.6.0', 'a:2:{s:5:"admin";a:1:{s:5:"route";s:32:"zikulasearchmodule_config_config";}s:4:"user";a:1:{s:5:"route";s:28:"zikulasearchmodule_user_form";}}', 3, 'a:2:{s:20:"ZikulaSearchModule::";s:13:"Module name::";s:13:"Searchblock::";s:13:"Block title::";}', '>=1.4.1', ''),
(12, 'ZikulaSecurityCenterModule', 3, 'Security Center', 'securitycenter', 'Security administration', 'SecurityCenterModule', '1.5.1', 'a:1:{s:5:"admin";a:1:{s:5:"route";s:40:"zikulasecuritycentermodule_config_config";}}', 3, 'a:1:{s:28:"ZikulaSecurityCenterModule::";s:2:"::";}', '>=1.4.1', ''),
(13, 'ZikulaSettingsModule', 3, 'General settings', 'settings', 'System settings administration', 'SettingsModule', '2.9.12', 'a:1:{s:5:"admin";a:1:{s:5:"route";s:34:"zikulasettingsmodule_settings_main";}}', 3, 'a:3:{s:22:"ZikulaSettingsModule::";s:2:"::";s:13:"LocaleBlock::";s:2:"::";s:16:"LocaleBlock::bid";s:5:"::bid";}', '>=1.4.2', ''),
(14, 'ZikulaThemeModule', 3, 'ThemeModule', 'theme', 'Theme system and administration', 'ThemeModule', '3.4.3', 'a:2:{s:5:"admin";a:1:{s:5:"route";s:28:"zikulathememodule_theme_view";}s:4:"user";a:1:{s:5:"route";s:28:"zikulathememodule_user_index";}}', 3, 'a:2:{s:19:"ZikulaThemeModule::";s:11:"ThemeName::";s:30:"ZikulaThemeModule::ThemeChange";s:23:":(ThemeName|ThemeName):";}', '>=1.4.2', ''),
(15, 'ZikulaUsersModule', 3, 'Users Module', 'users', 'User account administration', 'UsersModule', '3.0.3', 'a:3:{s:5:"admin";a:1:{s:5:"route";s:41:"zikulausersmodule_useradministration_list";}s:4:"user";a:1:{s:5:"route";s:30:"zikulausersmodule_account_menu";}s:15:"hook_subscriber";a:1:{s:5:"class";s:42:"Zikula\\UsersModule\\Container\\HookContainer";}}', 3, 'a:5:{s:19:"ZikulaUsersModule::";s:14:"Uname::User ID";s:28:"ZikulaUsersModule::MailUsers";s:2:"::";s:14:"Accountlinks::";s:13:"Block title::";s:12:"Loginblock::";s:13:"Block title::";s:13:"Onlineblock::";s:10:"Block ID::";}', '>=1.4.3', ''),
(16, 'ZikulaZAuthModule', 3, 'Zikula Native Authorization', 'zauth', 'Native zikula authentication', 'ZAuthModule', '1.0.1', 'a:1:{s:5:"admin";a:1:{s:5:"route";s:41:"zikulazauthmodule_useradministration_list";}}', 3, 'a:1:{s:19:"ZikulaZAuthModule::";s:2:"::";}', '>=1.4.3', ''),
(17, 'ZikulaPagesModule', 2, 'Pages', 'pages', 'Pages is a Static Page creation module for the Zikula Application Framework.', 'Pages', '3.2.0', 'a:5:{s:15:"hook_subscriber";a:1:{s:5:"class";s:42:"Zikula\\PagesModule\\Container\\HookContainer";}s:10:"searchable";a:1:{s:5:"class";s:38:"Zikula\\PagesModule\\Helper\\SearchHelper";}s:13:"categorizable";a:1:{s:8:"entities";a:1:{i:0;s:36:"Zikula\\PagesModule\\Entity\\PageEntity";}}s:4:"user";a:1:{s:5:"route";s:28:"zikulapagesmodule_user_index";}s:5:"admin";a:1:{s:5:"route";s:29:"zikulapagesmodule_admin_index";}}', 1, 'a:4:{s:19:"ZikulaPagesModule::";s:18:"Page name::Page ID";s:27:"ZikulaPagesModule:category:";s:13:"Category ID::";s:33:"ZikulaPagesModule:pageslistblock:";s:13:"Block title::";s:28:"ZikulaPagesModule:pageblock:";s:13:"Block title::";}', '>=1.4.6', ''),
(18, 'ZikulaLegalModule', 2, 'Legal', 'legal', 'Provides an interface for managing the site''s legal documents.', 'zikula/legal-module', '3.0.0', 'a:2:{s:5:"admin";a:1:{s:5:"route";s:31:"zikulalegalmodule_config_config";}s:4:"user";a:1:{s:5:"route";s:33:"zikulalegalmodule_user_termsofuse";}}', 1, 'a:8:{s:19:"ZikulaLegalModule::";s:2:"::";s:30:"ZikulaLegalModule::legalNotice";s:2:"::";s:29:"ZikulaLegalModule::termsOfUse";s:2:"::";s:32:"ZikulaLegalModule::privacyPolicy";s:2:"::";s:28:"ZikulaLegalModule::agePolicy";s:2:"::";s:41:"ZikulaLegalModule::accessibilityStatement";s:2:"::";s:42:"ZikulaLegalModule::cancellationRightPolicy";s:2:"::";s:34:"ZikulaLegalModule::tradeConditions";s:2:"::";}', '>=1.4.3', ''),
(19, 'ZikulaProfileModule', 2, 'Profile', 'profile', 'User profiles and member list', 'zikula/profile-module', '2.1.0', 'a:3:{s:5:"admin";a:1:{s:5:"route";s:33:"zikulaprofilemodule_config_config";}s:4:"user";a:1:{s:5:"route";s:35:"zikulaprofilemodule_profile_display";}s:7:"profile";a:1:{s:7:"version";s:3:"1.0";}}', 1, 'a:11:{s:21:"ZikulaProfileModule::";s:2:"::";s:25:"ZikulaProfileModule::view";s:2:"::";s:25:"ZikulaProfileModule::item";s:56:"DynamicUserData PropertyName::DynamicUserData PropertyID";s:27:"ZikulaProfileModule:Members";s:2:"::";s:34:"ZikulaProfileModule:Members:recent";s:2:"::";s:34:"ZikulaProfileModule:Members:online";s:2:"::";s:38:"ZikulaProfileModule:FeaturedUserblock:";s:13:"Block title::";s:34:"ZikulaProfileModule:LastSeenblock:";s:13:"Block title::";s:36:"ZikulaProfileModule:LastXUsersblock:";s:13:"Block title::";s:39:"ZikulaProfileModule:MembersOnlineblock:";s:13:"Block title::";s:10:"Userblock:";s:13:"Block title::";}', '>=1.4.3', '');

-- --------------------------------------------------------

--
-- Table structure for table `module_deps`
--

CREATE TABLE `module_deps` (
  `id` int(11) NOT NULL,
  `modid` int(11) NOT NULL,
  `modname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `minversion` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `maxversion` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `module_deps`
--

INSERT INTO `module_deps` (`id`, `modid`, `modname`, `minversion`, `maxversion`, `status`) VALUES
(1, 2, 'php', '>5.4.0', '>5.4.0', 1),
(2, 3, 'php', '>5.4.1', '>5.4.1', 1),
(3, 3, 'Scribite', '>=5.0.0', '>=5.0.0', 2),
(4, 4, 'php', '>5.4.0', '>5.4.0', 1),
(5, 1, 'php', '>5.4.0', '>5.4.0', 1),
(6, 5, 'php', '>5.4.0', '>5.4.0', 1),
(7, 6, 'php', '>5.4.0', '>5.4.0', 1),
(8, 7, 'php', '>5.4.1', '>5.4.1', 1),
(9, 8, 'php', '>5.4.0', '>5.4.0', 1),
(10, 9, 'php', '>5.4.0', '>5.4.0', 1),
(11, 10, 'php', '>=5.4.1', '>=5.4.1', 1),
(12, 11, 'php', '>5.4.0', '>5.4.0', 1),
(13, 12, 'php', '>5.4.0', '>5.4.0', 1),
(14, 13, 'php', '>5.4.0', '>5.4.0', 1),
(15, 14, 'php', '>5.4.1', '>5.4.1', 1),
(16, 15, 'php', '>5.4.1', '>5.4.1', 1),
(17, 16, 'php', '>5.4.1', '>5.4.1', 1),
(18, 17, 'php', '>5.3.3', '>5.3.3', 1),
(19, 17, 'Scribite', '>=5.0.0', '>=5.0.0', 2),
(20, 18, 'php', '>5.4.0', '>5.4.0', 1),
(21, 19, 'php', '>5.4.0', '>5.4.0', 1);

-- --------------------------------------------------------

--
-- Table structure for table `module_vars`
--

CREATE TABLE `module_vars` (
  `id` int(11) NOT NULL,
  `modname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=183 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `module_vars`
--

INSERT INTO `module_vars` (`id`, `modname`, `name`, `value`) VALUES
(1, 'ZikulaExtensionsModule', 'itemsperpage', 'i:40;'),
(2, 'ZConfig', 'debug', 's:1:"0";'),
(3, 'ZConfig', 'startdate', 's:7:"04/2017";'),
(4, 'ZConfig', 'adminmail', 's:17:"admin@example.com";'),
(5, 'ZConfig', 'Default_Theme', 's:20:"ZikulaBootstrapTheme";'),
(6, 'ZConfig', 'timezone_offset', 's:1:"0";'),
(7, 'ZConfig', 'timezone_server', 's:1:"0";'),
(8, 'ZConfig', 'funtext', 's:1:"1";'),
(9, 'ZConfig', 'reportlevel', 's:1:"0";'),
(10, 'ZConfig', 'startpage', 's:0:"";'),
(11, 'ZConfig', 'Version_Num', 's:5:"1.4.6";'),
(12, 'ZConfig', 'Version_ID', 's:6:"Zikula";'),
(13, 'ZConfig', 'Version_Sub', 's:8:"Overture";'),
(14, 'ZConfig', 'debug_sql', 's:1:"0";'),
(15, 'ZConfig', 'multilingual', 's:1:"1";'),
(16, 'ZConfig', 'useflags', 's:1:"0";'),
(17, 'ZConfig', 'theme_change', 's:1:"0";'),
(18, 'ZConfig', 'UseCompression', 's:1:"0";'),
(19, 'ZConfig', 'siteoff', 'i:0;'),
(20, 'ZConfig', 'siteoffreason', 's:0:"";'),
(21, 'ZConfig', 'starttype', 's:0:"";'),
(22, 'ZConfig', 'startfunc', 's:0:"";'),
(23, 'ZConfig', 'startargs', 's:0:"";'),
(24, 'ZConfig', 'entrypoint', 's:9:"index.php";'),
(25, 'ZConfig', 'language_detect', 'i:0;'),
(26, 'ZConfig', 'shorturls', 'b:0;'),
(27, 'ZConfig', 'shorturlstype', 's:1:"0";'),
(28, 'ZConfig', 'shorturlsseparator', 's:1:"-";'),
(29, 'ZConfig', 'sitename_en', 's:9:"Site name";'),
(30, 'ZConfig', 'slogan_en', 's:16:"Site description";'),
(31, 'ZConfig', 'metakeywords_en', 's:115:"zikula, portal, open source, web site, website, weblog, blog, content management system, cms, application framework";'),
(32, 'ZConfig', 'defaultpagetitle_en', 's:9:"Site name";'),
(33, 'ZConfig', 'defaultmetadescription_en', 's:16:"Site description";'),
(34, 'ZConfig', 'shorturlsstripentrypoint', 'b:1;'),
(35, 'ZConfig', 'shorturlsdefaultmodule', 's:0:"";'),
(36, 'ZConfig', 'profilemodule', 's:0:"";'),
(37, 'ZConfig', 'messagemodule', 's:0:"";'),
(38, 'ZConfig', 'languageurl', 'i:0;'),
(39, 'ZConfig', 'ajaxtimeout', 'i:5000;'),
(40, 'ZConfig', 'permasearch', 's:161:"À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü";'),
(41, 'ZConfig', 'permareplace', 's:114:"A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,ss,ae,Ae,oe,Oe,ue,Ue";'),
(42, 'ZConfig', 'language', 's:3:"eng";'),
(43, 'ZConfig', 'locale', 's:2:"en";'),
(44, 'ZConfig', 'language_i18n', 's:2:"en";'),
(45, 'ZConfig', 'idnnames', 'i:1;'),
(46, 'ZikulaThemeModule', 'modulesnocache', 's:0:"";'),
(47, 'ZikulaThemeModule', 'enablecache', 'b:0;'),
(48, 'ZikulaThemeModule', 'compile_check', 'b:1;'),
(49, 'ZikulaThemeModule', 'cache_lifetime', 'i:1800;'),
(50, 'ZikulaThemeModule', 'cache_lifetime_mods', 'i:1800;'),
(51, 'ZikulaThemeModule', 'force_compile', 'b:0;'),
(52, 'ZikulaThemeModule', 'trimwhitespace', 'b:0;'),
(53, 'ZikulaThemeModule', 'maxsizeforlinks', 'i:30;'),
(54, 'ZikulaThemeModule', 'itemsperpage', 'i:25;'),
(55, 'ZikulaThemeModule', 'cssjscombine', 'b:0;'),
(56, 'ZikulaThemeModule', 'cssjscompress', 'b:0;'),
(57, 'ZikulaThemeModule', 'cssjsminify', 'b:0;'),
(58, 'ZikulaThemeModule', 'cssjscombine_lifetime', 'i:3600;'),
(59, 'ZikulaThemeModule', 'render_compile_check', 'b:1;'),
(60, 'ZikulaThemeModule', 'render_force_compile', 'b:0;'),
(61, 'ZikulaThemeModule', 'render_cache', 'b:0;'),
(62, 'ZikulaThemeModule', 'render_expose_template', 'b:0;'),
(63, 'ZikulaThemeModule', 'render_lifetime', 'i:3600;'),
(64, 'ZikulaAdminModule', 'modulesperrow', 'i:3;'),
(65, 'ZikulaAdminModule', 'itemsperpage', 'i:15;'),
(66, 'ZikulaAdminModule', 'defaultcategory', 'i:5;'),
(67, 'ZikulaAdminModule', 'admingraphic', 'i:1;'),
(68, 'ZikulaAdminModule', 'startcategory', 'i:1;'),
(69, 'ZikulaAdminModule', 'ignoreinstallercheck', 'i:0;'),
(70, 'ZikulaAdminModule', 'admintheme', 's:0:"";'),
(71, 'ZikulaAdminModule', 'displaynametype', 'i:1;'),
(72, 'ZikulaPermissionsModule', 'lockadmin', 'i:1;'),
(73, 'ZikulaPermissionsModule', 'adminid', 'i:1;'),
(74, 'ZikulaPermissionsModule', 'filter', 'i:1;'),
(75, 'ZikulaPermissionsModule', 'rowview', 'i:25;'),
(76, 'ZikulaPermissionsModule', 'rowedit', 'i:35;'),
(77, 'ZikulaUsersModule', 'accountdisplaygraphics', 'b:1;'),
(78, 'ZikulaUsersModule', 'accountitemsperpage', 'i:25;'),
(79, 'ZikulaUsersModule', 'accountitemsperrow', 'i:5;'),
(80, 'ZikulaUsersModule', 'userimg', 's:11:"images/menu";'),
(81, 'ZikulaUsersModule', 'anonymous', 's:5:"Guest";'),
(82, 'ZikulaUsersModule', 'avatarpath', 's:13:"images/avatar";'),
(83, 'ZikulaUsersModule', 'allowgravatars', 'b:1;'),
(84, 'ZikulaUsersModule', 'gravatarimage', 's:12:"gravatar.jpg";'),
(85, 'ZikulaUsersModule', 'itemsperpage', 'i:25;'),
(86, 'ZikulaUsersModule', 'login_displayapproval', 'b:0;'),
(87, 'ZikulaUsersModule', 'login_displaydelete', 'b:0;'),
(88, 'ZikulaUsersModule', 'login_displayinactive', 'b:0;'),
(89, 'ZikulaUsersModule', 'login_displayverify', 'b:0;'),
(90, 'ZikulaUsersModule', 'reg_notifyemail', 's:0:"";'),
(91, 'ZikulaUsersModule', 'moderation', 'b:0;'),
(92, 'ZikulaUsersModule', 'reg_autologin', 'b:0;'),
(93, 'ZikulaUsersModule', 'reg_noregreasons', 's:51:"Sorry! New user registration is currently disabled.";'),
(94, 'ZikulaUsersModule', 'reg_allowreg', 'b:1;'),
(95, 'ZikulaUsersModule', 'reg_Illegaluseragents', 's:0:"";'),
(96, 'ZikulaUsersModule', 'reg_Illegaldomains', 's:0:"";'),
(97, 'ZikulaUsersModule', 'reg_Illegalusername', 's:66:"root, webmaster, admin, administrator, nobody, anonymous, username";'),
(98, 'ZConfig', 'authenticationMethodsStatus', 'a:1:{s:12:"native_uname";b:1;}'),
(99, 'ZikulaZAuthModule', 'hash_method', 's:6:"sha256";'),
(100, 'ZikulaZAuthModule', 'minpass', 'i:5;'),
(101, 'ZikulaZAuthModule', 'use_password_strength_meter', 'b:0;'),
(102, 'ZikulaZAuthModule', 'chgemail_expiredays', 'i:0;'),
(103, 'ZikulaZAuthModule', 'chgpass_expiredays', 'i:0;'),
(104, 'ZikulaZAuthModule', 'reg_expiredays', 'i:0;'),
(105, 'ZikulaZAuthModule', 'email_verification_required', 'b:1;'),
(106, 'ZikulaZAuthModule', 'reg_answer', 's:0:"";'),
(107, 'ZikulaZAuthModule', 'reg_question', 's:0:"";'),
(108, 'ZikulaGroupsModule', 'itemsperpage', 'i:25;'),
(109, 'ZikulaGroupsModule', 'defaultgroup', 'i:1;'),
(110, 'ZikulaGroupsModule', 'mailwarning', 'b:0;'),
(111, 'ZikulaGroupsModule', 'hideclosed', 'b:0;'),
(112, 'ZikulaGroupsModule', 'hidePrivate', 'b:0;'),
(113, 'ZikulaGroupsModule', 'primaryadmingroup', 'i:2;'),
(114, 'ZikulaBlocksModule', 'collapseable', 'b:0;'),
(115, 'ZikulaSecurityCenterModule', 'itemsperpage', 'i:10;'),
(116, 'ZConfig', 'updatecheck', 'i:1;'),
(117, 'ZConfig', 'updatefrequency', 'i:7;'),
(118, 'ZConfig', 'updatelastchecked', 'i:1491059862;'),
(119, 'ZConfig', 'updateversion', 's:5:"1.4.6";'),
(120, 'ZConfig', 'keyexpiry', 'i:0;'),
(121, 'ZConfig', 'sessionauthkeyua', 'i:0;'),
(122, 'ZConfig', 'secure_domain', 's:0:"";'),
(123, 'ZConfig', 'signcookies', 'i:1;'),
(124, 'ZConfig', 'signingkey', 's:40:"34576f9794629981f0a92d13b87133bccb60d460";'),
(125, 'ZConfig', 'seclevel', 's:6:"Medium";'),
(126, 'ZConfig', 'secmeddays', 'i:7;'),
(127, 'ZConfig', 'secinactivemins', 'i:20;'),
(128, 'ZConfig', 'sessionstoretofile', 'i:0;'),
(129, 'ZConfig', 'sessionsavepath', 's:0:"";'),
(130, 'ZConfig', 'gc_probability', 'i:100;'),
(131, 'ZConfig', 'sessioncsrftokenonetime', 'i:1;'),
(132, 'ZConfig', 'anonymoussessions', 'i:1;'),
(133, 'ZConfig', 'sessionrandregenerate', 'i:1;'),
(134, 'ZConfig', 'sessionregenerate', 'i:1;'),
(135, 'ZConfig', 'sessionregeneratefreq', 'i:10;'),
(136, 'ZConfig', 'sessionipcheck', 'i:0;'),
(137, 'ZConfig', 'sessionname', 's:5:"_zsid";'),
(138, 'ZConfig', 'filtergetvars', 'i:1;'),
(139, 'ZConfig', 'filterpostvars', 'i:1;'),
(140, 'ZConfig', 'filtercookievars', 'i:1;'),
(141, 'ZikulaSecurityCenterModule', 'htmlpurifierConfig', 's:25185:"O:19:"HTMLPurifier_Config":12:{s:7:"version";s:5:"4.8.0";s:12:"autoFinalize";b:1;s:10:"\0*\0serials";a:4:{s:4:"Attr";b:0;s:4:"HTML";b:0;s:6:"Output";b:0;s:5:"Cache";b:0;}s:9:"\0*\0serial";N;s:9:"\0*\0parser";N;s:3:"def";O:25:"HTMLPurifier_ConfigSchema":3:{s:8:"defaults";a:123:{s:19:"Attr.AllowedClasses";N;s:24:"Attr.AllowedFrameTargets";a:0:{}s:15:"Attr.AllowedRel";a:0:{}s:15:"Attr.AllowedRev";a:0:{}s:18:"Attr.ClassUseCDATA";N;s:20:"Attr.DefaultImageAlt";N;s:24:"Attr.DefaultInvalidImage";s:0:"";s:27:"Attr.DefaultInvalidImageAlt";s:13:"Invalid image";s:19:"Attr.DefaultTextDir";s:3:"ltr";s:13:"Attr.EnableID";b:0;s:21:"Attr.ForbiddenClasses";a:0:{}s:13:"Attr.ID.HTML5";N;s:16:"Attr.IDBlacklist";a:0:{}s:22:"Attr.IDBlacklistRegexp";N;s:13:"Attr.IDPrefix";s:0:"";s:18:"Attr.IDPrefixLocal";s:0:"";s:24:"AutoFormat.AutoParagraph";b:0;s:17:"AutoFormat.Custom";a:0:{}s:25:"AutoFormat.DisplayLinkURI";b:0;s:18:"AutoFormat.Linkify";b:0;s:33:"AutoFormat.PurifierLinkify.DocURL";s:3:"#%s";s:26:"AutoFormat.PurifierLinkify";b:0;s:32:"AutoFormat.RemoveEmpty.Predicate";a:4:{s:8:"colgroup";a:0:{}s:2:"th";a:0:{}s:2:"td";a:0:{}s:6:"iframe";a:1:{i:0;s:3:"src";}}s:44:"AutoFormat.RemoveEmpty.RemoveNbsp.Exceptions";a:2:{s:2:"td";b:1;s:2:"th";b:1;}s:33:"AutoFormat.RemoveEmpty.RemoveNbsp";b:0;s:22:"AutoFormat.RemoveEmpty";b:0;s:39:"AutoFormat.RemoveSpansWithoutAttributes";b:0;s:19:"CSS.AllowDuplicates";b:0;s:18:"CSS.AllowImportant";b:0;s:15:"CSS.AllowTricky";b:0;s:16:"CSS.AllowedFonts";N;s:21:"CSS.AllowedProperties";N;s:17:"CSS.DefinitionRev";i:1;s:23:"CSS.ForbiddenProperties";a:0:{}s:16:"CSS.MaxImgLength";s:6:"1200px";s:15:"CSS.Proprietary";b:0;s:11:"CSS.Trusted";b:0;s:20:"Cache.DefinitionImpl";s:10:"Serializer";s:20:"Cache.SerializerPath";N;s:27:"Cache.SerializerPermissions";i:493;s:22:"Core.AggressivelyFixLt";b:1;s:28:"Core.AllowHostnameUnderscore";b:0;s:18:"Core.CollectErrors";b:0;s:18:"Core.ColorKeywords";a:17:{s:6:"maroon";s:7:"#800000";s:3:"red";s:7:"#FF0000";s:6:"orange";s:7:"#FFA500";s:6:"yellow";s:7:"#FFFF00";s:5:"olive";s:7:"#808000";s:6:"purple";s:7:"#800080";s:7:"fuchsia";s:7:"#FF00FF";s:5:"white";s:7:"#FFFFFF";s:4:"lime";s:7:"#00FF00";s:5:"green";s:7:"#008000";s:4:"navy";s:7:"#000080";s:4:"blue";s:7:"#0000FF";s:4:"aqua";s:7:"#00FFFF";s:4:"teal";s:7:"#008080";s:5:"black";s:7:"#000000";s:6:"silver";s:7:"#C0C0C0";s:4:"gray";s:7:"#808080";}s:30:"Core.ConvertDocumentToFragment";b:1;s:36:"Core.DirectLexLineNumberSyncInterval";i:0;s:20:"Core.DisableExcludes";b:0;s:15:"Core.EnableIDNA";b:0;s:13:"Core.Encoding";s:5:"utf-8";s:26:"Core.EscapeInvalidChildren";b:0;s:22:"Core.EscapeInvalidTags";b:0;s:29:"Core.EscapeNonASCIICharacters";b:0;s:19:"Core.HiddenElements";a:2:{s:6:"script";b:1;s:5:"style";b:1;}s:13:"Core.Language";s:2:"en";s:14:"Core.LexerImpl";N;s:24:"Core.MaintainLineNumbers";N;s:22:"Core.NormalizeNewlines";b:1;s:21:"Core.RemoveInvalidImg";b:1;s:33:"Core.RemoveProcessingInstructions";b:0;s:25:"Core.RemoveScriptContents";N;s:13:"Filter.Custom";a:0:{}s:34:"Filter.ExtractStyleBlocks.Escaping";b:1;s:31:"Filter.ExtractStyleBlocks.Scope";N;s:34:"Filter.ExtractStyleBlocks.TidyImpl";N;s:25:"Filter.ExtractStyleBlocks";b:0;s:14:"Filter.YouTube";b:0;s:12:"HTML.Allowed";N;s:22:"HTML.AllowedAttributes";N;s:20:"HTML.AllowedComments";a:0:{}s:26:"HTML.AllowedCommentsRegexp";N;s:20:"HTML.AllowedElements";N;s:19:"HTML.AllowedModules";N;s:23:"HTML.Attr.Name.UseCDATA";b:0;s:17:"HTML.BlockWrapper";s:1:"p";s:16:"HTML.CoreModules";a:7:{s:9:"Structure";b:1;s:4:"Text";b:1;s:9:"Hypertext";b:1;s:4:"List";b:1;s:22:"NonXMLCommonAttributes";b:1;s:19:"XMLCommonAttributes";b:1;s:16:"CommonAttributes";b:1;}s:18:"HTML.CustomDoctype";N;s:17:"HTML.DefinitionID";N;s:18:"HTML.DefinitionRev";i:1;s:12:"HTML.Doctype";N;s:25:"HTML.FlashAllowFullScreen";b:0;s:24:"HTML.ForbiddenAttributes";a:0:{}s:22:"HTML.ForbiddenElements";a:0:{}s:17:"HTML.MaxImgLength";i:1200;s:13:"HTML.Nofollow";b:0;s:11:"HTML.Parent";s:3:"div";s:16:"HTML.Proprietary";b:0;s:14:"HTML.SafeEmbed";b:0;s:15:"HTML.SafeIframe";b:0;s:15:"HTML.SafeObject";b:0;s:18:"HTML.SafeScripting";a:0:{}s:11:"HTML.Strict";b:0;s:16:"HTML.TargetBlank";b:0;s:19:"HTML.TargetNoopener";b:1;s:21:"HTML.TargetNoreferrer";b:1;s:12:"HTML.TidyAdd";a:0:{}s:14:"HTML.TidyLevel";s:6:"medium";s:15:"HTML.TidyRemove";a:0:{}s:12:"HTML.Trusted";b:0;s:10:"HTML.XHTML";b:1;s:28:"Output.CommentScriptContents";b:1;s:19:"Output.FixInnerHTML";b:1;s:18:"Output.FlashCompat";b:0;s:14:"Output.Newline";N;s:15:"Output.SortAttr";b:0;s:17:"Output.TidyFormat";b:0;s:17:"Test.ForceNoIconv";b:0;s:18:"URI.AllowedSchemes";a:7:{s:4:"http";b:1;s:5:"https";b:1;s:6:"mailto";b:1;s:3:"ftp";b:1;s:4:"nntp";b:1;s:4:"news";b:1;s:3:"tel";b:1;}s:8:"URI.Base";N;s:17:"URI.DefaultScheme";s:4:"http";s:16:"URI.DefinitionID";N;s:17:"URI.DefinitionRev";i:1;s:11:"URI.Disable";b:0;s:19:"URI.DisableExternal";b:0;s:28:"URI.DisableExternalResources";b:0;s:20:"URI.DisableResources";b:0;s:8:"URI.Host";N;s:17:"URI.HostBlacklist";a:0:{}s:16:"URI.MakeAbsolute";b:0;s:9:"URI.Munge";N;s:18:"URI.MungeResources";b:0;s:18:"URI.MungeSecretKey";N;s:26:"URI.OverrideAllowedSchemes";b:1;s:20:"URI.SafeIframeRegexp";N;}s:12:"defaultPlist";O:25:"HTMLPurifier_PropertyList":3:{s:7:"\0*\0data";a:123:{s:19:"Attr.AllowedClasses";N;s:24:"Attr.AllowedFrameTargets";a:0:{}s:15:"Attr.AllowedRel";a:0:{}s:15:"Attr.AllowedRev";a:0:{}s:18:"Attr.ClassUseCDATA";N;s:20:"Attr.DefaultImageAlt";N;s:24:"Attr.DefaultInvalidImage";s:0:"";s:27:"Attr.DefaultInvalidImageAlt";s:13:"Invalid image";s:19:"Attr.DefaultTextDir";s:3:"ltr";s:13:"Attr.EnableID";b:0;s:21:"Attr.ForbiddenClasses";a:0:{}s:13:"Attr.ID.HTML5";N;s:16:"Attr.IDBlacklist";a:0:{}s:22:"Attr.IDBlacklistRegexp";N;s:13:"Attr.IDPrefix";s:0:"";s:18:"Attr.IDPrefixLocal";s:0:"";s:24:"AutoFormat.AutoParagraph";b:0;s:17:"AutoFormat.Custom";a:0:{}s:25:"AutoFormat.DisplayLinkURI";b:0;s:18:"AutoFormat.Linkify";b:0;s:33:"AutoFormat.PurifierLinkify.DocURL";s:3:"#%s";s:26:"AutoFormat.PurifierLinkify";b:0;s:32:"AutoFormat.RemoveEmpty.Predicate";a:4:{s:8:"colgroup";a:0:{}s:2:"th";a:0:{}s:2:"td";a:0:{}s:6:"iframe";a:1:{i:0;s:3:"src";}}s:44:"AutoFormat.RemoveEmpty.RemoveNbsp.Exceptions";a:2:{s:2:"td";b:1;s:2:"th";b:1;}s:33:"AutoFormat.RemoveEmpty.RemoveNbsp";b:0;s:22:"AutoFormat.RemoveEmpty";b:0;s:39:"AutoFormat.RemoveSpansWithoutAttributes";b:0;s:19:"CSS.AllowDuplicates";b:0;s:18:"CSS.AllowImportant";b:0;s:15:"CSS.AllowTricky";b:0;s:16:"CSS.AllowedFonts";N;s:21:"CSS.AllowedProperties";N;s:17:"CSS.DefinitionRev";i:1;s:23:"CSS.ForbiddenProperties";a:0:{}s:16:"CSS.MaxImgLength";s:6:"1200px";s:15:"CSS.Proprietary";b:0;s:11:"CSS.Trusted";b:0;s:20:"Cache.DefinitionImpl";s:10:"Serializer";s:20:"Cache.SerializerPath";N;s:27:"Cache.SerializerPermissions";i:493;s:22:"Core.AggressivelyFixLt";b:1;s:28:"Core.AllowHostnameUnderscore";b:0;s:18:"Core.CollectErrors";b:0;s:18:"Core.ColorKeywords";a:17:{s:6:"maroon";s:7:"#800000";s:3:"red";s:7:"#FF0000";s:6:"orange";s:7:"#FFA500";s:6:"yellow";s:7:"#FFFF00";s:5:"olive";s:7:"#808000";s:6:"purple";s:7:"#800080";s:7:"fuchsia";s:7:"#FF00FF";s:5:"white";s:7:"#FFFFFF";s:4:"lime";s:7:"#00FF00";s:5:"green";s:7:"#008000";s:4:"navy";s:7:"#000080";s:4:"blue";s:7:"#0000FF";s:4:"aqua";s:7:"#00FFFF";s:4:"teal";s:7:"#008080";s:5:"black";s:7:"#000000";s:6:"silver";s:7:"#C0C0C0";s:4:"gray";s:7:"#808080";}s:30:"Core.ConvertDocumentToFragment";b:1;s:36:"Core.DirectLexLineNumberSyncInterval";i:0;s:20:"Core.DisableExcludes";b:0;s:15:"Core.EnableIDNA";b:0;s:13:"Core.Encoding";s:5:"utf-8";s:26:"Core.EscapeInvalidChildren";b:0;s:22:"Core.EscapeInvalidTags";b:0;s:29:"Core.EscapeNonASCIICharacters";b:0;s:19:"Core.HiddenElements";a:2:{s:6:"script";b:1;s:5:"style";b:1;}s:13:"Core.Language";s:2:"en";s:14:"Core.LexerImpl";N;s:24:"Core.MaintainLineNumbers";N;s:22:"Core.NormalizeNewlines";b:1;s:21:"Core.RemoveInvalidImg";b:1;s:33:"Core.RemoveProcessingInstructions";b:0;s:25:"Core.RemoveScriptContents";N;s:13:"Filter.Custom";a:0:{}s:34:"Filter.ExtractStyleBlocks.Escaping";b:1;s:31:"Filter.ExtractStyleBlocks.Scope";N;s:34:"Filter.ExtractStyleBlocks.TidyImpl";N;s:25:"Filter.ExtractStyleBlocks";b:0;s:14:"Filter.YouTube";b:0;s:12:"HTML.Allowed";N;s:22:"HTML.AllowedAttributes";N;s:20:"HTML.AllowedComments";a:0:{}s:26:"HTML.AllowedCommentsRegexp";N;s:20:"HTML.AllowedElements";N;s:19:"HTML.AllowedModules";N;s:23:"HTML.Attr.Name.UseCDATA";b:0;s:17:"HTML.BlockWrapper";s:1:"p";s:16:"HTML.CoreModules";a:7:{s:9:"Structure";b:1;s:4:"Text";b:1;s:9:"Hypertext";b:1;s:4:"List";b:1;s:22:"NonXMLCommonAttributes";b:1;s:19:"XMLCommonAttributes";b:1;s:16:"CommonAttributes";b:1;}s:18:"HTML.CustomDoctype";N;s:17:"HTML.DefinitionID";N;s:18:"HTML.DefinitionRev";i:1;s:12:"HTML.Doctype";N;s:25:"HTML.FlashAllowFullScreen";b:0;s:24:"HTML.ForbiddenAttributes";a:0:{}s:22:"HTML.ForbiddenElements";a:0:{}s:17:"HTML.MaxImgLength";i:1200;s:13:"HTML.Nofollow";b:0;s:11:"HTML.Parent";s:3:"div";s:16:"HTML.Proprietary";b:0;s:14:"HTML.SafeEmbed";b:0;s:15:"HTML.SafeIframe";b:0;s:15:"HTML.SafeObject";b:0;s:18:"HTML.SafeScripting";a:0:{}s:11:"HTML.Strict";b:0;s:16:"HTML.TargetBlank";b:0;s:19:"HTML.TargetNoopener";b:1;s:21:"HTML.TargetNoreferrer";b:1;s:12:"HTML.TidyAdd";a:0:{}s:14:"HTML.TidyLevel";s:6:"medium";s:15:"HTML.TidyRemove";a:0:{}s:12:"HTML.Trusted";b:0;s:10:"HTML.XHTML";b:1;s:28:"Output.CommentScriptContents";b:1;s:19:"Output.FixInnerHTML";b:1;s:18:"Output.FlashCompat";b:0;s:14:"Output.Newline";N;s:15:"Output.SortAttr";b:0;s:17:"Output.TidyFormat";b:0;s:17:"Test.ForceNoIconv";b:0;s:18:"URI.AllowedSchemes";a:7:{s:4:"http";b:1;s:5:"https";b:1;s:6:"mailto";b:1;s:3:"ftp";b:1;s:4:"nntp";b:1;s:4:"news";b:1;s:3:"tel";b:1;}s:8:"URI.Base";N;s:17:"URI.DefaultScheme";s:4:"http";s:16:"URI.DefinitionID";N;s:17:"URI.DefinitionRev";i:1;s:11:"URI.Disable";b:0;s:19:"URI.DisableExternal";b:0;s:28:"URI.DisableExternalResources";b:0;s:20:"URI.DisableResources";b:0;s:8:"URI.Host";N;s:17:"URI.HostBlacklist";a:0:{}s:16:"URI.MakeAbsolute";b:0;s:9:"URI.Munge";N;s:18:"URI.MungeResources";b:0;s:18:"URI.MungeSecretKey";N;s:26:"URI.OverrideAllowedSchemes";b:1;s:20:"URI.SafeIframeRegexp";N;}s:9:"\0*\0parent";N;s:8:"\0*\0cache";N;}s:4:"info";a:136:{s:19:"Attr.AllowedClasses";i:-8;s:24:"Attr.AllowedFrameTargets";i:8;s:15:"Attr.AllowedRel";i:8;s:15:"Attr.AllowedRev";i:8;s:18:"Attr.ClassUseCDATA";i:-7;s:20:"Attr.DefaultImageAlt";i:-1;s:24:"Attr.DefaultInvalidImage";i:1;s:27:"Attr.DefaultInvalidImageAlt";i:1;s:19:"Attr.DefaultTextDir";O:8:"stdClass":2:{s:4:"type";i:1;s:7:"allowed";a:2:{s:3:"ltr";b:1;s:3:"rtl";b:1;}}s:13:"Attr.EnableID";i:7;s:17:"HTML.EnableAttrID";O:8:"stdClass":2:{s:3:"key";s:13:"Attr.EnableID";s:7:"isAlias";b:1;}s:21:"Attr.ForbiddenClasses";i:8;s:13:"Attr.ID.HTML5";i:-7;s:16:"Attr.IDBlacklist";i:9;s:22:"Attr.IDBlacklistRegexp";i:-1;s:13:"Attr.IDPrefix";i:1;s:18:"Attr.IDPrefixLocal";i:1;s:24:"AutoFormat.AutoParagraph";i:7;s:17:"AutoFormat.Custom";i:9;s:25:"AutoFormat.DisplayLinkURI";i:7;s:18:"AutoFormat.Linkify";i:7;s:33:"AutoFormat.PurifierLinkify.DocURL";i:1;s:37:"AutoFormatParam.PurifierLinkifyDocURL";O:8:"stdClass":2:{s:3:"key";s:33:"AutoFormat.PurifierLinkify.DocURL";s:7:"isAlias";b:1;}s:26:"AutoFormat.PurifierLinkify";i:7;s:32:"AutoFormat.RemoveEmpty.Predicate";i:10;s:44:"AutoFormat.RemoveEmpty.RemoveNbsp.Exceptions";i:8;s:33:"AutoFormat.RemoveEmpty.RemoveNbsp";i:7;s:22:"AutoFormat.RemoveEmpty";i:7;s:39:"AutoFormat.RemoveSpansWithoutAttributes";i:7;s:19:"CSS.AllowDuplicates";i:7;s:18:"CSS.AllowImportant";i:7;s:15:"CSS.AllowTricky";i:7;s:16:"CSS.AllowedFonts";i:-8;s:21:"CSS.AllowedProperties";i:-8;s:17:"CSS.DefinitionRev";i:5;s:23:"CSS.ForbiddenProperties";i:8;s:16:"CSS.MaxImgLength";i:-1;s:15:"CSS.Proprietary";i:7;s:11:"CSS.Trusted";i:7;s:20:"Cache.DefinitionImpl";i:-1;s:20:"Core.DefinitionCache";O:8:"stdClass":2:{s:3:"key";s:20:"Cache.DefinitionImpl";s:7:"isAlias";b:1;}s:20:"Cache.SerializerPath";i:-1;s:27:"Cache.SerializerPermissions";i:-5;s:22:"Core.AggressivelyFixLt";i:7;s:28:"Core.AllowHostnameUnderscore";i:7;s:18:"Core.CollectErrors";i:7;s:18:"Core.ColorKeywords";i:10;s:30:"Core.ConvertDocumentToFragment";i:7;s:24:"Core.AcceptFullDocuments";O:8:"stdClass":2:{s:3:"key";s:30:"Core.ConvertDocumentToFragment";s:7:"isAlias";b:1;}s:36:"Core.DirectLexLineNumberSyncInterval";i:5;s:20:"Core.DisableExcludes";i:7;s:15:"Core.EnableIDNA";i:7;s:13:"Core.Encoding";i:2;s:26:"Core.EscapeInvalidChildren";i:7;s:22:"Core.EscapeInvalidTags";i:7;s:29:"Core.EscapeNonASCIICharacters";i:7;s:19:"Core.HiddenElements";i:8;s:13:"Core.Language";i:1;s:14:"Core.LexerImpl";i:-11;s:24:"Core.MaintainLineNumbers";i:-7;s:22:"Core.NormalizeNewlines";i:7;s:21:"Core.RemoveInvalidImg";i:7;s:33:"Core.RemoveProcessingInstructions";i:7;s:25:"Core.RemoveScriptContents";i:-7;s:13:"Filter.Custom";i:9;s:34:"Filter.ExtractStyleBlocks.Escaping";i:7;s:33:"Filter.ExtractStyleBlocksEscaping";O:8:"stdClass":2:{s:3:"key";s:34:"Filter.ExtractStyleBlocks.Escaping";s:7:"isAlias";b:1;}s:38:"FilterParam.ExtractStyleBlocksEscaping";O:8:"stdClass":2:{s:3:"key";s:34:"Filter.ExtractStyleBlocks.Escaping";s:7:"isAlias";b:1;}s:31:"Filter.ExtractStyleBlocks.Scope";i:-1;s:30:"Filter.ExtractStyleBlocksScope";O:8:"stdClass":2:{s:3:"key";s:31:"Filter.ExtractStyleBlocks.Scope";s:7:"isAlias";b:1;}s:35:"FilterParam.ExtractStyleBlocksScope";O:8:"stdClass":2:{s:3:"key";s:31:"Filter.ExtractStyleBlocks.Scope";s:7:"isAlias";b:1;}s:34:"Filter.ExtractStyleBlocks.TidyImpl";i:-11;s:38:"FilterParam.ExtractStyleBlocksTidyImpl";O:8:"stdClass":2:{s:3:"key";s:34:"Filter.ExtractStyleBlocks.TidyImpl";s:7:"isAlias";b:1;}s:25:"Filter.ExtractStyleBlocks";i:7;s:14:"Filter.YouTube";i:7;s:12:"HTML.Allowed";i:-4;s:22:"HTML.AllowedAttributes";i:-8;s:20:"HTML.AllowedComments";i:8;s:26:"HTML.AllowedCommentsRegexp";i:-1;s:20:"HTML.AllowedElements";i:-8;s:19:"HTML.AllowedModules";i:-8;s:23:"HTML.Attr.Name.UseCDATA";i:7;s:17:"HTML.BlockWrapper";i:1;s:16:"HTML.CoreModules";i:8;s:18:"HTML.CustomDoctype";i:-1;s:17:"HTML.DefinitionID";i:-1;s:18:"HTML.DefinitionRev";i:5;s:12:"HTML.Doctype";O:8:"stdClass":3:{s:4:"type";i:1;s:10:"allow_null";b:1;s:7:"allowed";a:5:{s:22:"HTML 4.01 Transitional";b:1;s:16:"HTML 4.01 Strict";b:1;s:22:"XHTML 1.0 Transitional";b:1;s:16:"XHTML 1.0 Strict";b:1;s:9:"XHTML 1.1";b:1;}}s:25:"HTML.FlashAllowFullScreen";i:7;s:24:"HTML.ForbiddenAttributes";i:8;s:22:"HTML.ForbiddenElements";i:8;s:17:"HTML.MaxImgLength";i:-5;s:13:"HTML.Nofollow";i:7;s:11:"HTML.Parent";i:1;s:16:"HTML.Proprietary";i:7;s:14:"HTML.SafeEmbed";i:7;s:15:"HTML.SafeIframe";i:7;s:15:"HTML.SafeObject";i:7;s:18:"HTML.SafeScripting";i:8;s:11:"HTML.Strict";i:7;s:16:"HTML.TargetBlank";i:7;s:19:"HTML.TargetNoopener";i:7;s:21:"HTML.TargetNoreferrer";i:7;s:12:"HTML.TidyAdd";i:8;s:14:"HTML.TidyLevel";O:8:"stdClass":2:{s:4:"type";i:1;s:7:"allowed";a:4:{s:4:"none";b:1;s:5:"light";b:1;s:6:"medium";b:1;s:5:"heavy";b:1;}}s:15:"HTML.TidyRemove";i:8;s:12:"HTML.Trusted";i:7;s:10:"HTML.XHTML";i:7;s:10:"Core.XHTML";O:8:"stdClass":2:{s:3:"key";s:10:"HTML.XHTML";s:7:"isAlias";b:1;}s:28:"Output.CommentScriptContents";i:7;s:26:"Core.CommentScriptContents";O:8:"stdClass":2:{s:3:"key";s:28:"Output.CommentScriptContents";s:7:"isAlias";b:1;}s:19:"Output.FixInnerHTML";i:7;s:18:"Output.FlashCompat";i:7;s:14:"Output.Newline";i:-1;s:15:"Output.SortAttr";i:7;s:17:"Output.TidyFormat";i:7;s:15:"Core.TidyFormat";O:8:"stdClass":2:{s:3:"key";s:17:"Output.TidyFormat";s:7:"isAlias";b:1;}s:17:"Test.ForceNoIconv";i:7;s:18:"URI.AllowedSchemes";i:8;s:8:"URI.Base";i:-1;s:17:"URI.DefaultScheme";i:-1;s:16:"URI.DefinitionID";i:-1;s:17:"URI.DefinitionRev";i:5;s:11:"URI.Disable";i:7;s:15:"Attr.DisableURI";O:8:"stdClass":2:{s:3:"key";s:11:"URI.Disable";s:7:"isAlias";b:1;}s:19:"URI.DisableExternal";i:7;s:28:"URI.DisableExternalResources";i:7;s:20:"URI.DisableResources";i:7;s:8:"URI.Host";i:-1;s:17:"URI.HostBlacklist";i:9;s:16:"URI.MakeAbsolute";i:7;s:9:"URI.Munge";i:-1;s:18:"URI.MungeResources";i:7;s:18:"URI.MungeSecretKey";i:-1;s:26:"URI.OverrideAllowedSchemes";i:7;s:20:"URI.SafeIframeRegexp";i:-1;}}s:14:"\0*\0definitions";a:1:{s:4:"HTML";O:27:"HTMLPurifier_HTMLDefinition":16:{s:4:"info";a:0:{}s:16:"info_global_attr";a:0:{}s:11:"info_parent";s:3:"div";s:15:"info_parent_def";N;s:18:"info_block_wrapper";s:1:"p";s:18:"info_tag_transform";a:0:{}s:23:"info_attr_transform_pre";a:0:{}s:24:"info_attr_transform_post";a:0:{}s:17:"info_content_sets";a:0:{}s:13:"info_injector";a:0:{}s:7:"doctype";N;s:40:"\0HTMLPurifier_HTMLDefinition\0_anonModule";O:23:"HTMLPurifier_HTMLModule":11:{s:4:"name";s:9:"Anonymous";s:8:"elements";a:1:{i:0;s:6:"iframe";}s:4:"info";a:1:{s:6:"iframe";O:23:"HTMLPurifier_ElementDef":13:{s:10:"standalone";b:0;s:4:"attr";a:1:{s:15:"allowfullscreen";s:4:"Bool";}s:18:"attr_transform_pre";a:0:{}s:19:"attr_transform_post";a:0:{}s:5:"child";N;s:13:"content_model";N;s:18:"content_model_type";N;s:22:"descendants_are_inline";b:0;s:13:"required_attr";a:0:{}s:8:"excludes";a:0:{}s:9:"autoclose";a:0:{}s:4:"wrap";N;s:10:"formatting";N;}}s:12:"content_sets";a:0:{}s:16:"attr_collections";a:0:{}s:18:"info_tag_transform";a:0:{}s:23:"info_attr_transform_pre";a:0:{}s:24:"info_attr_transform_post";a:0:{}s:13:"info_injector";a:0:{}s:17:"defines_child_def";b:0;s:4:"safe";b:1;}s:4:"type";s:4:"HTML";s:7:"manager";O:30:"HTMLPurifier_HTMLModuleManager":11:{s:8:"doctypes";O:28:"HTMLPurifier_DoctypeRegistry":2:{s:11:"\0*\0doctypes";a:5:{s:22:"HTML 4.01 Transitional";O:20:"HTMLPurifier_Doctype":7:{s:4:"name";s:22:"HTML 4.01 Transitional";s:7:"modules";a:18:{i:0;s:16:"CommonAttributes";i:1;s:4:"Text";i:2;s:9:"Hypertext";i:3;s:4:"List";i:4;s:12:"Presentation";i:5;s:4:"Edit";i:6;s:3:"Bdo";i:7;s:6:"Tables";i:8;s:5:"Image";i:9;s:14:"StyleAttribute";i:10;s:9:"Scripting";i:11;s:6:"Object";i:12;s:5:"Forms";i:13;s:4:"Name";i:14;s:6:"Legacy";i:15;s:6:"Target";i:16;s:6:"Iframe";i:17;s:22:"NonXMLCommonAttributes";}s:11:"tidyModules";a:2:{i:0;s:17:"Tidy_Transitional";i:1;s:16:"Tidy_Proprietary";}s:3:"xml";b:0;s:7:"aliases";a:0:{}s:9:"dtdPublic";s:38:"-//W3C//DTD HTML 4.01 Transitional//EN";s:9:"dtdSystem";s:36:"http://www.w3.org/TR/html4/loose.dtd";}s:16:"HTML 4.01 Strict";O:20:"HTMLPurifier_Doctype":7:{s:4:"name";s:16:"HTML 4.01 Strict";s:7:"modules";a:15:{i:0;s:16:"CommonAttributes";i:1;s:4:"Text";i:2;s:9:"Hypertext";i:3;s:4:"List";i:4;s:12:"Presentation";i:5;s:4:"Edit";i:6;s:3:"Bdo";i:7;s:6:"Tables";i:8;s:5:"Image";i:9;s:14:"StyleAttribute";i:10;s:9:"Scripting";i:11;s:6:"Object";i:12;s:5:"Forms";i:13;s:4:"Name";i:14;s:22:"NonXMLCommonAttributes";}s:11:"tidyModules";a:3:{i:0;s:11:"Tidy_Strict";i:1;s:16:"Tidy_Proprietary";i:2;s:9:"Tidy_Name";}s:3:"xml";b:0;s:7:"aliases";a:0:{}s:9:"dtdPublic";s:25:"-//W3C//DTD HTML 4.01//EN";s:9:"dtdSystem";s:37:"http://www.w3.org/TR/html4/strict.dtd";}s:22:"XHTML 1.0 Transitional";O:20:"HTMLPurifier_Doctype":7:{s:4:"name";s:22:"XHTML 1.0 Transitional";s:7:"modules";a:19:{i:0;s:16:"CommonAttributes";i:1;s:4:"Text";i:2;s:9:"Hypertext";i:3;s:4:"List";i:4;s:12:"Presentation";i:5;s:4:"Edit";i:6;s:3:"Bdo";i:7;s:6:"Tables";i:8;s:5:"Image";i:9;s:14:"StyleAttribute";i:10;s:9:"Scripting";i:11;s:6:"Object";i:12;s:5:"Forms";i:13;s:4:"Name";i:14;s:6:"Legacy";i:15;s:6:"Target";i:16;s:6:"Iframe";i:17;s:19:"XMLCommonAttributes";i:18;s:22:"NonXMLCommonAttributes";}s:11:"tidyModules";a:4:{i:0;s:17:"Tidy_Transitional";i:1;s:10:"Tidy_XHTML";i:2;s:16:"Tidy_Proprietary";i:3;s:9:"Tidy_Name";}s:3:"xml";b:1;s:7:"aliases";a:0:{}s:9:"dtdPublic";s:38:"-//W3C//DTD XHTML 1.0 Transitional//EN";s:9:"dtdSystem";s:55:"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd";}s:16:"XHTML 1.0 Strict";O:20:"HTMLPurifier_Doctype":7:{s:4:"name";s:16:"XHTML 1.0 Strict";s:7:"modules";a:16:{i:0;s:16:"CommonAttributes";i:1;s:4:"Text";i:2;s:9:"Hypertext";i:3;s:4:"List";i:4;s:12:"Presentation";i:5;s:4:"Edit";i:6;s:3:"Bdo";i:7;s:6:"Tables";i:8;s:5:"Image";i:9;s:14:"StyleAttribute";i:10;s:9:"Scripting";i:11;s:6:"Object";i:12;s:5:"Forms";i:13;s:4:"Name";i:14;s:19:"XMLCommonAttributes";i:15;s:22:"NonXMLCommonAttributes";}s:11:"tidyModules";a:5:{i:0;s:11:"Tidy_Strict";i:1;s:10:"Tidy_XHTML";i:2;s:11:"Tidy_Strict";i:3;s:16:"Tidy_Proprietary";i:4;s:9:"Tidy_Name";}s:3:"xml";b:1;s:7:"aliases";a:0:{}s:9:"dtdPublic";s:32:"-//W3C//DTD XHTML 1.0 Strict//EN";s:9:"dtdSystem";s:49:"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd";}s:9:"XHTML 1.1";O:20:"HTMLPurifier_Doctype":7:{s:4:"name";s:9:"XHTML 1.1";s:7:"modules";a:17:{i:0;s:16:"CommonAttributes";i:1;s:4:"Text";i:2;s:9:"Hypertext";i:3;s:4:"List";i:4;s:12:"Presentation";i:5;s:4:"Edit";i:6;s:3:"Bdo";i:7;s:6:"Tables";i:8;s:5:"Image";i:9;s:14:"StyleAttribute";i:10;s:9:"Scripting";i:11;s:6:"Object";i:12;s:5:"Forms";i:13;s:4:"Name";i:14;s:19:"XMLCommonAttributes";i:15;s:4:"Ruby";i:16;s:6:"Iframe";}s:11:"tidyModules";a:5:{i:0;s:11:"Tidy_Strict";i:1;s:10:"Tidy_XHTML";i:2;s:16:"Tidy_Proprietary";i:3;s:11:"Tidy_Strict";i:4;s:9:"Tidy_Name";}s:3:"xml";b:1;s:7:"aliases";a:0:{}s:9:"dtdPublic";s:25:"-//W3C//DTD XHTML 1.1//EN";s:9:"dtdSystem";s:44:"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd";}}s:10:"\0*\0aliases";N;}s:7:"doctype";N;s:9:"attrTypes";O:22:"HTMLPurifier_AttrTypes":1:{s:7:"\0*\0info";a:21:{s:4:"Enum";O:25:"HTMLPurifier_AttrDef_Enum":4:{s:12:"valid_values";a:0:{}s:17:"\0*\0case_sensitive";b:0;s:9:"minimized";b:0;s:8:"required";b:0;}s:4:"Bool";O:30:"HTMLPurifier_AttrDef_HTML_Bool":3:{s:7:"\0*\0name";b:0;s:9:"minimized";b:1;s:8:"required";b:0;}s:5:"CDATA";O:25:"HTMLPurifier_AttrDef_Text":2:{s:9:"minimized";b:0;s:8:"required";b:0;}s:2:"ID";O:28:"HTMLPurifier_AttrDef_HTML_ID":3:{s:11:"\0*\0selector";b:0;s:9:"minimized";b:0;s:8:"required";b:0;}s:6:"Length";O:32:"HTMLPurifier_AttrDef_HTML_Length":3:{s:6:"\0*\0max";N;s:9:"minimized";b:0;s:8:"required";b:0;}s:11:"MultiLength";O:37:"HTMLPurifier_AttrDef_HTML_MultiLength":3:{s:6:"\0*\0max";N;s:9:"minimized";b:0;s:8:"required";b:0;}s:8:"NMTOKENS";O:34:"HTMLPurifier_AttrDef_HTML_Nmtokens":2:{s:9:"minimized";b:0;s:8:"required";b:0;}s:6:"Pixels";O:32:"HTMLPurifier_AttrDef_HTML_Pixels":3:{s:6:"\0*\0max";N;s:9:"minimized";b:0;s:8:"required";b:0;}s:4:"Text";O:25:"HTMLPurifier_AttrDef_Text":2:{s:9:"minimized";b:0;s:8:"required";b:0;}s:3:"URI";O:24:"HTMLPurifier_AttrDef_URI":4:{s:9:"\0*\0parser";O:22:"HTMLPurifier_URIParser":1:{s:17:"\0*\0percentEncoder";O:27:"HTMLPurifier_PercentEncoder":1:{s:11:"\0*\0preserve";a:66:{i:48;b:1;i:49;b:1;i:50;b:1;i:51;b:1;i:52;b:1;i:53;b:1;i:54;b:1;i:55;b:1;i:56;b:1;i:57;b:1;i:65;b:1;i:66;b:1;i:67;b:1;i:68;b:1;i:69;b:1;i:70;b:1;i:71;b:1;i:72;b:1;i:73;b:1;i:74;b:1;i:75;b:1;i:76;b:1;i:77;b:1;i:78;b:1;i:79;b:1;i:80;b:1;i:81;b:1;i:82;b:1;i:83;b:1;i:84;b:1;i:85;b:1;i:86;b:1;i:87;b:1;i:88;b:1;i:89;b:1;i:90;b:1;i:97;b:1;i:98;b:1;i:99;b:1;i:100;b:1;i:101;b:1;i:102;b:1;i:103;b:1;i:104;b:1;i:105;b:1;i:106;b:1;i:107;b:1;i:108;b:1;i:109;b:1;i:110;b:1;i:111;b:1;i:112;b:1;i:113;b:1;i:114;b:1;i:115;b:1;i:116;b:1;i:117;b:1;i:118;b:1;i:119;b:1;i:120;b:1;i:121;b:1;i:122;b:1;i:45;b:1;i:46;b:1;i:95;b:1;i:126;b:1;}}}s:17:"\0*\0embedsResource";b:0;s:9:"minimized";b:0;s:8:"required";b:0;}s:12:"LanguageCode";O:25:"HTMLPurifier_AttrDef_Lang":2:{s:9:"minimized";b:0;s:8:"required";b:0;}s:5:"Color";O:31:"HTMLPurifier_AttrDef_HTML_Color":2:{s:9:"minimized";b:0;s:8:"required";b:0;}s:6:"IAlign";O:26:"HTMLPurifier_AttrDef_Clone":3:{s:8:"\0*\0clone";O:25:"HTMLPurifier_AttrDef_Enum":4:{s:12:"valid_values";a:5:{s:3:"top";i:0;s:6:"middle";i:1;s:6:"bottom";i:2;s:4:"left";i:3;s:5:"right";i:4;}s:17:"\0*\0case_sensitive";b:0;s:9:"minimized";b:0;s:8:"required";b:0;}s:9:"minimized";b:0;s:8:"required";b:0;}s:6:"LAlign";O:26:"HTMLPurifier_AttrDef_Clone":3:{s:8:"\0*\0clone";O:25:"HTMLPurifier_AttrDef_Enum":4:{s:12:"valid_values";a:4:{s:3:"top";i:0;s:6:"bottom";i:1;s:4:"left";i:2;s:5:"right";i:3;}s:17:"\0*\0case_sensitive";b:0;s:9:"minimized";b:0;s:8:"required";b:0;}s:9:"minimized";b:0;s:8:"required";b:0;}s:11:"FrameTarget";O:37:"HTMLPurifier_AttrDef_HTML_FrameTarget":4:{s:12:"valid_values";b:0;s:17:"\0*\0case_sensitive";b:0;s:9:"minimized";b:0;s:8:"required";b:0;}s:11:"ContentType";O:25:"HTMLPurifier_AttrDef_Text":2:{s:9:"minimized";b:0;s:8:"required";b:0;}s:12:"ContentTypes";O:25:"HTMLPurifier_AttrDef_Text":2:{s:9:"minimized";b:0;s:8:"required";b:0;}s:8:"Charsets";O:25:"HTMLPurifier_AttrDef_Text":2:{s:9:"minimized";b:0;s:8:"required";b:0;}s:9:"Character";O:25:"HTMLPurifier_AttrDef_Text":2:{s:9:"minimized";b:0;s:8:"required";b:0;}s:5:"Class";O:31:"HTMLPurifier_AttrDef_HTML_Class":2:{s:9:"minimized";b:0;s:8:"required";b:0;}s:6:"Number";O:28:"HTMLPurifier_AttrDef_Integer":5:{s:11:"\0*\0negative";b:0;s:7:"\0*\0zero";b:0;s:11:"\0*\0positive";b:1;s:9:"minimized";b:0;s:8:"required";b:0;}}}s:7:"modules";a:0:{}s:17:"registeredModules";a:0:{}s:11:"userModules";a:0:{}s:13:"elementLookup";a:0:{}s:8:"prefixes";a:1:{i:0;s:24:"HTMLPurifier_HTMLModule_";}s:11:"contentSets";N;s:15:"attrCollections";N;s:7:"trusted";b:0;}s:5:"setup";b:0;s:9:"optimized";b:0;}}s:12:"\0*\0finalized";b:1;s:8:"\0*\0plist";O:25:"HTMLPurifier_PropertyList":3:{s:7:"\0*\0data";a:5:{s:15:"Attr.AllowedRel";a:3:{s:8:"nofollow";b:1;s:11:"imageviewer";b:1;s:8:"lightbox";b:1;}s:15:"HTML.SafeObject";b:1;s:18:"Output.FlashCompat";b:1;s:14:"HTML.SafeEmbed";b:1;s:20:"Cache.SerializerPath";s:62:"/Applications/MAMP/htdocs/core.git/src/app/cache/prod/purifier";}s:9:"\0*\0parent";r:176;s:8:"\0*\0cache";N;}s:30:"\0HTMLPurifier_Config\0aliasMode";N;s:6:"chatty";b:1;s:25:"\0HTMLPurifier_Config\0lock";N;}";'),
(142, 'ZConfig', 'useids', 'i:0;'),
(143, 'ZConfig', 'idsmail', 'i:0;'),
(144, 'ZConfig', 'idsrulepath', 's:70:"system/SecurityCenterModule/Resources/config/phpids_zikula_default.xml";'),
(145, 'ZConfig', 'idssoftblock', 'i:1;'),
(146, 'ZConfig', 'idsfilter', 's:3:"xml";'),
(147, 'ZConfig', 'idsimpactthresholdone', 'i:1;'),
(148, 'ZConfig', 'idsimpactthresholdtwo', 'i:10;'),
(149, 'ZConfig', 'idsimpactthresholdthree', 'i:25;'),
(150, 'ZConfig', 'idsimpactthresholdfour', 'i:75;'),
(151, 'ZConfig', 'idsimpactmode', 'i:1;'),
(152, 'ZConfig', 'idshtmlfields', 'a:1:{i:0;s:14:"POST.__wysiwyg";}'),
(153, 'ZConfig', 'idsjsonfields', 'a:1:{i:0;s:15:"POST.__jsondata";}'),
(154, 'ZConfig', 'idsexceptions', 'a:12:{i:0;s:10:"GET.__utmz";i:1;s:10:"GET.__utmc";i:2;s:18:"REQUEST.linksorder";i:3;s:15:"POST.linksorder";i:4;s:19:"REQUEST.fullcontent";i:5;s:16:"POST.fullcontent";i:6;s:22:"REQUEST.summarycontent";i:7;s:19:"POST.summarycontent";i:8;s:19:"REQUEST.filter.page";i:9;s:16:"POST.filter.page";i:10;s:20:"REQUEST.filter.value";i:11;s:17:"POST.filter.value";}'),
(155, 'ZConfig', 'outputfilter', 'i:1;'),
(156, 'ZConfig', 'htmlentities', 'i:1;'),
(157, 'ZConfig', 'AllowableHTML', 'a:110:{s:3:"!--";i:2;s:1:"a";i:2;s:4:"abbr";i:1;s:7:"acronym";i:1;s:7:"address";i:1;s:6:"applet";i:0;s:4:"area";i:0;s:7:"article";i:1;s:5:"aside";i:1;s:5:"audio";i:0;s:1:"b";i:1;s:4:"base";i:0;s:8:"basefont";i:0;s:3:"bdo";i:0;s:3:"big";i:0;s:10:"blockquote";i:2;s:2:"br";i:2;s:6:"button";i:0;s:6:"canvas";i:0;s:7:"caption";i:1;s:6:"center";i:2;s:4:"cite";i:1;s:4:"code";i:0;s:3:"col";i:1;s:8:"colgroup";i:1;s:7:"command";i:0;s:8:"datalist";i:0;s:2:"dd";i:1;s:3:"del";i:0;s:7:"details";i:1;s:3:"dfn";i:0;s:3:"dir";i:0;s:3:"div";i:2;s:2:"dl";i:1;s:2:"dt";i:1;s:2:"em";i:2;s:5:"embed";i:0;s:8:"fieldset";i:1;s:10:"figcaption";i:0;s:6:"figure";i:0;s:6:"footer";i:0;s:4:"font";i:0;s:4:"form";i:0;s:2:"h1";i:1;s:2:"h2";i:1;s:2:"h3";i:1;s:2:"h4";i:1;s:2:"h5";i:1;s:2:"h6";i:1;s:6:"header";i:0;s:6:"hgroup";i:0;s:2:"hr";i:2;s:1:"i";i:1;s:6:"iframe";i:0;s:3:"img";i:2;s:5:"input";i:0;s:3:"ins";i:0;s:6:"keygen";i:0;s:3:"kbd";i:0;s:5:"label";i:1;s:6:"legend";i:1;s:2:"li";i:2;s:3:"map";i:0;s:4:"mark";i:0;s:4:"menu";i:0;s:7:"marquee";i:0;s:5:"meter";i:0;s:3:"nav";i:0;s:4:"nobr";i:0;s:6:"object";i:0;s:2:"ol";i:2;s:8:"optgroup";i:0;s:6:"option";i:0;s:6:"output";i:0;s:1:"p";i:2;s:5:"param";i:0;s:3:"pre";i:2;s:8:"progress";i:0;s:1:"q";i:0;s:2:"rp";i:0;s:2:"rt";i:0;s:4:"ruby";i:0;s:1:"s";i:0;s:4:"samp";i:0;s:6:"script";i:0;s:7:"section";i:0;s:6:"select";i:0;s:5:"small";i:0;s:6:"source";i:0;s:4:"span";i:2;s:6:"strike";i:0;s:6:"strong";i:2;s:3:"sub";i:1;s:7:"summary";i:1;s:3:"sup";i:0;s:5:"table";i:2;s:5:"tbody";i:1;s:2:"td";i:2;s:8:"textarea";i:0;s:5:"tfoot";i:1;s:2:"th";i:2;s:5:"thead";i:0;s:4:"time";i:0;s:2:"tr";i:2;s:2:"tt";i:2;s:1:"u";i:0;s:2:"ul";i:2;s:3:"var";i:0;s:5:"video";i:0;s:3:"wbr";i:0;}'),
(158, 'ZikulaCategoriesModule', 'userrootcat', 's:17:"/__SYSTEM__/Users";'),
(159, 'ZikulaCategoriesModule', 'allowusercatedit', 'i:0;'),
(160, 'ZikulaCategoriesModule', 'autocreateusercat', 'i:0;'),
(161, 'ZikulaCategoriesModule', 'autocreateuserdefaultcat', 'i:0;'),
(162, 'ZikulaCategoriesModule', 'permissionsall', 'i:0;'),
(163, 'ZikulaCategoriesModule', 'userdefaultcatname', 's:7:"Default";'),
(164, 'ZikulaMailerModule', 'charset', 's:5:"UTF-8";'),
(165, 'ZikulaMailerModule', 'encoding', 's:4:"8bit";'),
(166, 'ZikulaMailerModule', 'html', 'b:0;'),
(167, 'ZikulaMailerModule', 'wordwrap', 'i:50;'),
(168, 'ZikulaMailerModule', 'enableLogging', 'b:0;'),
(169, 'ZikulaSearchModule', 'itemsperpage', 'i:10;'),
(170, 'ZikulaSearchModule', 'limitsummary', 'i:255;'),
(171, 'ZikulaSearchModule', 'opensearch_enabled', 'b:1;'),
(172, 'ZikulaSearchModule', 'opensearch_adult_content', 'b:0;'),
(173, 'ZikulaRoutesModule', 'routeEntriesPerPage', 'i:10;'),
(174, 'ZConfig', 'system_identifier', 's:32:"914504919958dfc487d22df796356046";'),
(175, 'systemplugin.imagine', 'version', 's:5:"0.6.2";'),
(176, 'systemplugin.imagine', 'thumb_dir', 's:20:"systemplugin.imagine";'),
(177, 'systemplugin.imagine', 'thumb_auto_cleanup', 'b:0;'),
(178, 'systemplugin.imagine', 'thumb_auto_cleanup_period', 's:3:"P1D";'),
(179, 'systemplugin.imagine', 'presets', 'a:1:{s:7:"default";C:27:"SystemPlugin_Imagine_Preset":266:{x:i:2;a:8:{s:5:"width";i:100;s:6:"height";i:100;s:4:"mode";s:5:"inset";s:9:"extension";N;s:7:"options";a:2:{s:12:"jpeg_quality";i:75;s:21:"png_compression_level";i:7;}s:8:"__module";N;s:9:"__imagine";N;s:16:"__transformation";N;};m:a:1:{s:7:"\0*\0name";s:7:"default";}}}'),
(180, 'ZikulaBootstrapTheme', 'home', 's:18:"3col_w_centerblock";'),
(181, 'ZikulaBootstrapTheme', 'master', 's:4:"2col";'),
(182, 'ZikulaBootstrapTheme', 'admin', 's:4:"1col";');

-- --------------------------------------------------------

--
-- Table structure for table `objectdata_attributes`
--

CREATE TABLE `objectdata_attributes` (
  `id` int(11) NOT NULL,
  `attribute_name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `object_id` int(11) NOT NULL,
  `object_type` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  `obj_status` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `cr_date` datetime NOT NULL,
  `cr_uid` int(11) NOT NULL,
  `lu_date` datetime NOT NULL,
  `lu_uid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `objectdata_log`
--

CREATE TABLE `objectdata_log` (
  `id` int(11) NOT NULL,
  `object_type` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `object_id` int(11) NOT NULL,
  `op` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `diff` longtext COLLATE utf8_unicode_ci,
  `obj_status` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `cr_date` datetime NOT NULL,
  `cr_uid` int(11) NOT NULL,
  `lu_date` datetime NOT NULL,
  `lu_uid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `objectdata_meta`
--

CREATE TABLE `objectdata_meta` (
  `id` int(11) NOT NULL,
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
  `lu_uid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sc_intrusion`
--

CREATE TABLE `sc_intrusion` (
  `id` int(11) NOT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `tag` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  `page` longtext COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `impact` int(11) NOT NULL,
  `filters` longtext COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `uid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `search_result`
--

CREATE TABLE `search_result` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `text` longtext COLLATE utf8_unicode_ci,
  `module` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `extra` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `found` datetime DEFAULT NULL,
  `sesid` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:object)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `search_stat`
--

CREATE TABLE `search_stat` (
  `id` int(11) NOT NULL,
  `search` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `scount` int(11) NOT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `session_info`
--

CREATE TABLE `session_info` (
  `sessid` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `ipaddr` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `lastused` datetime NOT NULL,
  `uid` int(11) NOT NULL,
  `remember` smallint(6) NOT NULL,
  `vars` longtext COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `session_info`
--

INSERT INTO `session_info` (`sessid`, `ipaddr`, `lastused`, `uid`, `remember`, `vars`) VALUES
('60db671383d1de1e75b4dea730ec7316', '127.0.0.1', '2017-04-01 11:17:41', 2, 0, '_sf2_attributes|a:2:{s:3:"uid";i:2;s:7:"_locale";s:2:"en";}_sf2_flashes|a:0:{}_sf2_meta|a:3:{s:1:"u";i:1491059865;s:1:"c";i:1491059857;s:1:"l";s:1:"0";}'),
('67673bbf13542d0ad69e48ba319f72b3', '127.0.0.1', '2017-04-01 11:17:46', 0, 0, '_sf2_attributes|a:0:{}_sf2_flashes|a:0:{}_sf2_meta|a:3:{s:1:"u";i:1491059866;s:1:"c";i:1491059866;s:1:"l";s:1:"0";}');

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE `themes` (
  `id` int(11) NOT NULL,
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
  `xhtml` smallint(6) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id`, `name`, `type`, `displayname`, `description`, `directory`, `version`, `contact`, `admin`, `user`, `system`, `state`, `xhtml`) VALUES
(1, 'ZikulaAndreas08Theme', 3, 'Andreas08', 'Based on the theme Andreas08 by Andreas Viklund', 'Andreas08Theme', '2.0.0', '3', 1, 1, 0, 1, 1),
(2, 'ZikulaAtomTheme', 3, 'Atom', 'The Atom theme is an auxiliary theme specially designed for rendering pages in Atom mark-up.', 'AtomTheme', '2.0.0', '3', 0, 1, 0, 1, 1),
(3, 'ZikulaBootstrapTheme', 3, 'Bootstrap', 'Bootstrap based theme', 'BootstrapTheme', '1.0.0', '3', 1, 1, 0, 1, 1),
(4, 'ZikulaPrinterTheme', 3, 'Printer', 'The Printer theme is an auxiliary theme designed specially for outputting pages in a printer-friendly format.', 'PrinterTheme', '3.0.0', '3', 0, 1, 0, 1, 1),
(5, 'ZikulaRssTheme', 3, 'Rss', 'The RSS theme is an auxiliary theme designed specially for outputting pages as an RSS feed.', 'RssTheme', '2.0.0', '3', 0, 1, 0, 1, 1),
(6, 'ZikulaSeaBreezeTheme', 3, 'SeaBreeze', 'The SeaBreeze theme is a browser-oriented theme.', 'SeaBreezeTheme', '4.0.0', '3', 1, 1, 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` int(11) NOT NULL,
  `uname` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `pass` varchar(138) COLLATE utf8_unicode_ci NOT NULL,
  `activated` smallint(6) NOT NULL,
  `approved_date` datetime NOT NULL,
  `approved_by` int(11) NOT NULL,
  `user_regdate` datetime NOT NULL,
  `lastlogin` datetime NOT NULL,
  `theme` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tz` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `locale` varchar(5) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uid`, `uname`, `email`, `pass`, `activated`, `approved_date`, `approved_by`, `user_regdate`, `lastlogin`, `theme`, `tz`, `locale`) VALUES
(1, 'guest', '', '', 1, '1970-01-01 00:00:00', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '', '', ''),
(2, 'admin', 'admin@example.com', '1$$dc647eb65e6711e155375218212b3964', 1, '2017-04-01 15:17:10', 2, '2017-04-01 15:17:26', '2017-04-01 15:17:27', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `users_attributes`
--

CREATE TABLE `users_attributes` (
  `user_id` int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_verifychg`
--

CREATE TABLE `users_verifychg` (
  `id` int(11) NOT NULL,
  `changetype` smallint(6) NOT NULL,
  `uid` int(11) NOT NULL,
  `newemail` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `verifycode` varchar(138) COLLATE utf8_unicode_ci NOT NULL,
  `created_dt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workflows`
--

CREATE TABLE `workflows` (
  `id` int(11) NOT NULL,
  `metaid` int(11) NOT NULL,
  `module` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `schemaname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` smallint(6) NOT NULL,
  `obj_table` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `obj_idcolumn` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `obj_id` int(11) NOT NULL,
  `busy` int(11) NOT NULL,
  `debug` longtext COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zauth_authentication_mapping`
--

CREATE TABLE `zauth_authentication_mapping` (
  `id` int(11) NOT NULL,
  `method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `uid` int(11) NOT NULL,
  `uname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `verifiedEmail` tinyint(1) NOT NULL,
  `pass` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `zauth_authentication_mapping`
--

INSERT INTO `zauth_authentication_mapping` (`id`, `method`, `uid`, `uname`, `email`, `verifiedEmail`, `pass`) VALUES
(1, 'native_uname', 2, 'admin', 'admin@example.com', 1, '8$15G5K$3ce8b6109dd78431834e5f4fd6d20dc7993433cba338b0832491e3faee2897a8');

-- --------------------------------------------------------

--
-- Table structure for table `zikula_routes_route`
--

CREATE TABLE `zikula_routes_route` (
  `id` int(11) NOT NULL,
  `workflowState` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `routeType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `replacedRouteName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bundle` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `controller` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `route_action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `route_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `host` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `schemes` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `methods` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prependBundlePrefix` tinyint(1) NOT NULL,
  `translatable` tinyint(1) NOT NULL,
  `translationPrefix` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `route_defaults` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `requirements` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `route_condition` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort` int(11) NOT NULL,
  `sort_group` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdDate` datetime NOT NULL,
  `updatedDate` datetime NOT NULL,
  `createdBy_id` int(11) DEFAULT NULL,
  `updatedBy_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_category`
--
ALTER TABLE `admin_category`
  ADD PRIMARY KEY (`cid`);

--
-- Indexes for table `admin_module`
--
ALTER TABLE `admin_module`
  ADD PRIMARY KEY (`amid`),
  ADD KEY `mid_cid` (`mid`,`cid`);

--
-- Indexes for table `blocks`
--
ALTER TABLE `blocks`
  ADD PRIMARY KEY (`bid`),
  ADD KEY `IDX_CEED957841AEF4CE` (`mid`),
  ADD KEY `active_idx` (`active`);

--
-- Indexes for table `block_placements`
--
ALTER TABLE `block_placements`
  ADD PRIMARY KEY (`pid`,`bid`),
  ADD KEY `IDX_911332125550C4ED` (`pid`),
  ADD KEY `IDX_911332124AF2B3F3` (`bid`),
  ADD KEY `bid_pid_idx` (`bid`,`pid`);

--
-- Indexes for table `block_positions`
--
ALTER TABLE `block_positions`
  ADD PRIMARY KEY (`pid`),
  ADD KEY `name_idx` (`name`);

--
-- Indexes for table `bundles`
--
ALTER TABLE `bundles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_D8A73A9867B776D8` (`bundlename`);

--
-- Indexes for table `categories_attributes`
--
ALTER TABLE `categories_attributes`
  ADD PRIMARY KEY (`category_id`,`name`),
  ADD KEY `IDX_9015DE7812469DE2` (`category_id`);

--
-- Indexes for table `categories_category`
--
ALTER TABLE `categories_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_D0B2B0F8727ACA70` (`parent_id`),
  ADD KEY `IDX_D0B2B0F88304AF18` (`cr_uid`),
  ADD KEY `IDX_D0B2B0F8C072C1DD` (`lu_uid`),
  ADD KEY `idx_categories_is_leaf` (`is_leaf`),
  ADD KEY `idx_categories_name` (`name`),
  ADD KEY `idx_categories_ipath` (`ipath`,`is_leaf`,`status`),
  ADD KEY `idx_categories_status` (`status`),
  ADD KEY `idx_categories_ipath_status` (`ipath`,`status`);

--
-- Indexes for table `categories_mapobj`
--
ALTER TABLE `categories_mapobj`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories_registry`
--
ALTER TABLE `categories_registry`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_1B56B4338304AF18` (`cr_uid`),
  ADD KEY `IDX_1B56B433C072C1DD` (`lu_uid`),
  ADD KEY `idx_categories_registry` (`modname`,`entityname`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`gid`),
  ADD UNIQUE KEY `UNIQ_F06D39705E237E06` (`name`);

--
-- Indexes for table `group_applications`
--
ALTER TABLE `group_applications`
  ADD PRIMARY KEY (`app_id`),
  ADD KEY `IDX_1B8F2CC9539B0606` (`uid`),
  ADD KEY `IDX_1B8F2CC94C397118` (`gid`);

--
-- Indexes for table `group_membership`
--
ALTER TABLE `group_membership`
  ADD PRIMARY KEY (`uid`,`gid`),
  ADD KEY `IDX_5132B337539B0606` (`uid`),
  ADD KEY `IDX_5132B3374C397118` (`gid`);

--
-- Indexes for table `group_perms`
--
ALTER TABLE `group_perms`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `hook_area`
--
ALTER TABLE `hook_area`
  ADD PRIMARY KEY (`id`),
  ADD KEY `areaidx` (`areaname`);

--
-- Indexes for table `hook_binding`
--
ALTER TABLE `hook_binding`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hook_provider`
--
ALTER TABLE `hook_provider`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hook_runtime`
--
ALTER TABLE `hook_runtime`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hook_subscriber`
--
ALTER TABLE `hook_subscriber`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_70B2CA2A79066886` (`root_id`),
  ADD KEY `IDX_70B2CA2A727ACA70` (`parent_id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `module_deps`
--
ALTER TABLE `module_deps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `module_vars`
--
ALTER TABLE `module_vars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `objectdata_attributes`
--
ALTER TABLE `objectdata_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `object_type` (`object_type`),
  ADD KEY `object_id` (`object_id`);

--
-- Indexes for table `objectdata_log`
--
ALTER TABLE `objectdata_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `objectdata_meta`
--
ALTER TABLE `objectdata_meta`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sc_intrusion`
--
ALTER TABLE `sc_intrusion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_8595CE46539B0606` (`uid`);

--
-- Indexes for table `search_result`
--
ALTER TABLE `search_result`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `search_stat`
--
ALTER TABLE `search_stat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `session_info`
--
ALTER TABLE `session_info`
  ADD PRIMARY KEY (`sessid`);

--
-- Indexes for table `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `uname` (`uname`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `users_attributes`
--
ALTER TABLE `users_attributes`
  ADD PRIMARY KEY (`user_id`,`name`),
  ADD KEY `IDX_E6F031E4A76ED395` (`user_id`);

--
-- Indexes for table `users_verifychg`
--
ALTER TABLE `users_verifychg`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `workflows`
--
ALTER TABLE `workflows`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zauth_authentication_mapping`
--
ALTER TABLE `zauth_authentication_mapping`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zikula_routes_route`
--
ALTER TABLE `zikula_routes_route`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_516F4628BF396750` (`id`),
  ADD KEY `IDX_516F46283174800F` (`createdBy_id`),
  ADD KEY `IDX_516F462865FF1AEC` (`updatedBy_id`),
  ADD KEY `workflowstateindex` (`workflowState`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_category`
--
ALTER TABLE `admin_category`
  MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `admin_module`
--
ALTER TABLE `admin_module`
  MODIFY `amid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `blocks`
--
ALTER TABLE `blocks`
  MODIFY `bid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `block_positions`
--
ALTER TABLE `block_positions`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `bundles`
--
ALTER TABLE `bundles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `categories_category`
--
ALTER TABLE `categories_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=10000;
--
-- AUTO_INCREMENT for table `categories_mapobj`
--
ALTER TABLE `categories_mapobj`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `categories_registry`
--
ALTER TABLE `categories_registry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `gid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `group_applications`
--
ALTER TABLE `group_applications`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `group_perms`
--
ALTER TABLE `group_perms`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `hook_area`
--
ALTER TABLE `hook_area`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `hook_binding`
--
ALTER TABLE `hook_binding`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `hook_provider`
--
ALTER TABLE `hook_provider`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `hook_runtime`
--
ALTER TABLE `hook_runtime`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `hook_subscriber`
--
ALTER TABLE `hook_subscriber`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `module_deps`
--
ALTER TABLE `module_deps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT for table `module_vars`
--
ALTER TABLE `module_vars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=183;
--
-- AUTO_INCREMENT for table `objectdata_attributes`
--
ALTER TABLE `objectdata_attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `objectdata_log`
--
ALTER TABLE `objectdata_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `objectdata_meta`
--
ALTER TABLE `objectdata_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sc_intrusion`
--
ALTER TABLE `sc_intrusion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `search_result`
--
ALTER TABLE `search_result`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `search_stat`
--
ALTER TABLE `search_stat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `themes`
--
ALTER TABLE `themes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `users_verifychg`
--
ALTER TABLE `users_verifychg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `workflows`
--
ALTER TABLE `workflows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `zauth_authentication_mapping`
--
ALTER TABLE `zauth_authentication_mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `zikula_routes_route`
--
ALTER TABLE `zikula_routes_route`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `block_placements`
--
ALTER TABLE `block_placements`
  ADD CONSTRAINT `FK_911332124AF2B3F3` FOREIGN KEY (`bid`) REFERENCES `blocks` (`bid`),
  ADD CONSTRAINT `FK_911332125550C4ED` FOREIGN KEY (`pid`) REFERENCES `block_positions` (`pid`);

--
-- Constraints for table `categories_attributes`
--
ALTER TABLE `categories_attributes`
  ADD CONSTRAINT `FK_9015DE7812469DE2` FOREIGN KEY (`category_id`) REFERENCES `categories_category` (`id`);

--
-- Constraints for table `categories_category`
--
ALTER TABLE `categories_category`
  ADD CONSTRAINT `FK_D0B2B0F8727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `categories_category` (`id`);

--
-- Constraints for table `group_applications`
--
ALTER TABLE `group_applications`
  ADD CONSTRAINT `FK_1B8F2CC94C397118` FOREIGN KEY (`gid`) REFERENCES `groups` (`gid`);

--
-- Constraints for table `group_membership`
--
ALTER TABLE `group_membership`
  ADD CONSTRAINT `FK_5132B337539B0606` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `FK_70B2CA2A727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_70B2CA2A79066886` FOREIGN KEY (`root_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users_attributes`
--
ALTER TABLE `users_attributes`
  ADD CONSTRAINT `FK_E6F031E4A76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
