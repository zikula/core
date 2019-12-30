-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 30, 2019 at 02:52 PM
-- Server version: 5.7.26
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `159`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `admin_category`
--

INSERT INTO `admin_category` (`cid`, `name`, `description`, `sortorder`) VALUES
(1, 'System', 'Core modules at the heart of operation of the site.', 0),
(2, 'Layout', 'Layout modules for controlling the site\'s look and feel.', 1),
(3, 'Users', 'Modules for controlling user membership, access rights and profiles.', 2),
(4, 'Content', 'Modules for providing content to your users.', 3),
(5, 'Uncategorised', 'Newly-installed or uncategorized modules.', 4),
(6, 'Security', 'Modules for managing the site\'s security.', 5);

-- --------------------------------------------------------

--
-- Table structure for table `admin_module`
--

CREATE TABLE `admin_module` (
  `amid` int(11) NOT NULL,
  `mid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `sortorder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `admin_module`
--

INSERT INTO `admin_module` (`amid`, `mid`, `cid`, `sortorder`) VALUES
(1, 5, 1, 0),
(2, 3, 2, 0),
(3, 7, 4, 0),
(4, 1, 1, 1),
(5, 11, 3, 0),
(6, 10, 1, 2),
(7, 6, 3, 1),
(8, 13, 1, 3),
(9, 12, 4, 1),
(10, 9, 6, 0),
(11, 8, 1, 4),
(12, 15, 2, 1),
(13, 2, 3, 2),
(14, 4, 3, 3),
(15, 14, 4, 2);

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
  `last_update` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `language` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `mid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `blocks`
--

INSERT INTO `blocks` (`bid`, `bkey`, `blocktype`, `title`, `description`, `properties`, `filter`, `active`, `last_update`, `language`, `mid`) VALUES
(1, 'ZikulaSearchModule:\\Zikula\\SearchModule\\Block\\SearchBlock', 'Search', 'Search box', 'Search block', 'a:2:{s:16:\"displaySearchBtn\";b:1;s:6:\"active\";a:1:{s:17:\"ZikulaUsersModule\";i:1;}}', 'a:0:{}', 1, '2019-12-30 14:51:53', '', 12),
(2, 'ZikulaBlocksModule:\\Zikula\\BlocksModule\\Block\\HtmlBlock', 'Html', 'This site is powered by Zikula!', 'HTML block', 'a:1:{s:7:\"content\";s:1548:\"<p><a href=\"https://ziku.la\">Zikula</a> is an Open Source Content Application Framework (CMF) built on top of Symfony.</p><p>With Zikula:</p><ul><li><strong>Power:</strong> You get the all the features of <a href=\"http://symfony.com\">Symfony</a> PLUS: </li><li><strong>User Management:</strong> Built in User and Group management with Rights/Roles control</li><li><strong>Front end control:</strong> You can customise all aspects of the site\'s appearance through themes, with support for <a href=\"http://jquery.com\">jQuery</a>, <a href=\"http://getbootstrap.com\">Bootstrap</a> and many other modern technologies</li><li><strong>Internationalization (i18n):</strong> You can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation of your site</li><li><strong>Extensibility:</strong> you get a standard application-programming interface (API) that lets you easily extend your site\'s functionality through modules</li><li><strong>More:</strong> Admin UI, global categories, site-wide search, content blocks, menu creation, and more!</li><li><strong>Support:</strong> you can get help and support from the Zikula community of webmasters and developers at <a href=\"https://ziku.la\">ziku.la</a>, <a href=\"https://github.com/zikula/core\">Github</a> and <a href=\"https://zikula.slack.com/\">Slack</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>\";}', 'a:0:{}', 1, '2019-12-30 14:51:53', '', 3),
(3, 'ZikulaUsersModule:\\Zikula\\UsersModule\\Block\\LoginBlock', 'Login', 'User log-in', 'Login block', 'a:0:{}', 'a:1:{i:0;a:4:{s:9:\"attribute\";s:6:\"_route\";s:14:\"queryParameter\";N;s:10:\"comparator\";s:2:\"!=\";s:5:\"value\";s:30:\"zikulausersmodule_access_login\";}}', 1, '2019-12-30 14:51:53', '', 2),
(4, 'ZikulaMenuModule:\\Zikula\\MenuModule\\Block\\MenuBlock', 'Menu', 'Main menu', 'Main menu', 'a:2:{s:4:\"name\";s:8:\"mainMenu\";s:7:\"options\";s:73:\"{\"template\": \"ZikulaMenuModule:Override:bootstrap_fontawesome.html.twig\"}\";}', 'a:0:{}', 1, '2019-12-30 14:51:53', '', 14);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `bundles`
--

INSERT INTO `bundles` (`id`, `bundlename`, `autoload`, `bundleclass`, `bundletype`, `bundlestate`) VALUES
(1, 'zikula/oauth-module', 'a:1:{s:5:\"psr-4\";a:1:{s:19:\"Zikula\\OAuthModule\\\";s:27:\"modules/zikula/oauth-module\";}}', 'Zikula\\OAuthModule\\ZikulaOAuthModule', 'M', 3),
(2, 'zikula/legal-module', 'a:1:{s:5:\"psr-4\";a:1:{s:19:\"Zikula\\LegalModule\\\";s:27:\"modules/zikula/legal-module\";}}', 'Zikula\\LegalModule\\ZikulaLegalModule', 'M', 3),
(3, 'zikula/profile-module', 'a:1:{s:5:\"psr-4\";a:1:{s:21:\"Zikula\\ProfileModule\\\";s:29:\"modules/zikula/profile-module\";}}', 'Zikula\\ProfileModule\\ZikulaProfileModule', 'M', 3),
(4, 'zikula/pagelock-module', 'a:1:{s:5:\"psr-4\";a:1:{s:22:\"Zikula\\PageLockModule\\\";s:30:\"modules/zikula/pagelock-module\";}}', 'Zikula\\PageLockModule\\ZikulaPageLockModule', 'M', 3),
(5, 'zikula/rss-theme', 'a:1:{s:5:\"psr-4\";a:1:{s:16:\"Zikula\\RssTheme\\\";s:15:\"themes/RssTheme\";}}', 'Zikula\\RssTheme\\ZikulaRssTheme', 'T', 3),
(6, 'zikula/seabreeze-theme', 'a:1:{s:5:\"psr-4\";a:1:{s:22:\"Zikula\\SeaBreezeTheme\\\";s:29:\"themes/zikula/seabreeze-theme\";}}', 'Zikula\\SeaBreezeTheme\\ZikulaSeaBreezeTheme', 'T', 3),
(7, 'zikula/andreas08-theme', 'a:1:{s:5:\"psr-4\";a:1:{s:22:\"Zikula\\Andreas08Theme\\\";s:29:\"themes/zikula/andreas08-theme\";}}', 'Zikula\\Andreas08Theme\\ZikulaAndreas08Theme', 'T', 3),
(8, 'zikula/printer-theme', 'a:1:{s:5:\"psr-4\";a:1:{s:20:\"Zikula\\PrinterTheme\\\";s:19:\"themes/PrinterTheme\";}}', 'Zikula\\PrinterTheme\\ZikulaPrinterTheme', 'T', 3),
(9, 'zikula/atom-theme', 'a:1:{s:5:\"psr-4\";a:1:{s:17:\"Zikula\\AtomTheme\\\";s:16:\"themes/AtomTheme\";}}', 'Zikula\\AtomTheme\\ZikulaAtomTheme', 'T', 3),
(10, 'zikula/bootstrap-theme', 'a:1:{s:5:\"psr-4\";a:1:{s:22:\"Zikula\\BootstrapTheme\\\";s:21:\"themes/BootstrapTheme\";}}', 'Zikula\\BootstrapTheme\\ZikulaBootstrapTheme', 'T', 3);

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
(11, 'code', 'P'),
(12, 'code', 'C'),
(13, 'code', 'A'),
(14, 'code', 'O'),
(15, 'code', 'R'),
(26, 'code', 'A'),
(27, 'code', 'I'),
(29, 'code', 'P'),
(30, 'code', 'A');

-- --------------------------------------------------------

--
-- Table structure for table `categories_category`
--

CREATE TABLE `categories_category` (
  `id` int(11) NOT NULL,
  `tree_root` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `lft` int(11) NOT NULL,
  `lvl` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `is_locked` tinyint(1) NOT NULL,
  `is_leaf` tinyint(1) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `display_desc` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `status` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `cr_date` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `lu_date` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `cr_uid` int(11) DEFAULT NULL,
  `lu_uid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `categories_category`
--

INSERT INTO `categories_category` (`id`, `tree_root`, `parent_id`, `lft`, `lvl`, `rgt`, `is_locked`, `is_leaf`, `name`, `value`, `display_name`, `display_desc`, `status`, `cr_date`, `lu_date`, `cr_uid`, `lu_uid`) VALUES
(1, 1, NULL, 1, 0, 50, 1, 0, '__SYSTEM__', '', 'a:1:{s:2:\"en\";s:13:\"Category root\";}', 's:0:\"\";', 'A', '2019-12-30 14:51:39', '2019-12-30 14:51:39', 2, 2),
(2, 1, 1, 2, 1, 23, 0, 0, 'Modules', '', 'a:1:{s:2:\"en\";s:7:\"Modules\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:39', '2019-12-30 14:51:39', 2, 2),
(3, 1, 1, 24, 1, 49, 0, 0, 'General', '', 'a:1:{s:2:\"en\";s:7:\"General\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:39', '2019-12-30 14:51:39', 2, 2),
(10, 1, 3, 25, 2, 36, 0, 0, 'Publication Status (extended)', '', 'a:1:{s:2:\"en\";s:29:\"Publication status (extended)\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:39', '2019-12-30 14:51:39', 2, 2),
(11, 1, 10, 26, 3, 27, 0, 1, 'Pending', 'P', 'a:1:{s:2:\"en\";s:7:\"Pending\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:39', '2019-12-30 14:51:39', 2, 2),
(12, 1, 10, 28, 3, 29, 0, 1, 'Checked', 'C', 'a:1:{s:2:\"en\";s:7:\"Checked\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(13, 1, 10, 30, 3, 31, 0, 1, 'Approved', 'A', 'a:1:{s:2:\"en\";s:8:\"Approved\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(14, 1, 10, 32, 3, 33, 0, 1, 'On-line', 'O', 'a:1:{s:2:\"en\";s:7:\"On-line\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(15, 1, 10, 34, 3, 35, 0, 1, 'Rejected', 'R', 'a:1:{s:2:\"en\";s:8:\"Rejected\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(25, 1, 3, 37, 2, 42, 0, 0, 'ActiveStatus', '', 'a:1:{s:2:\"en\";s:15:\"Activity status\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(26, 1, 25, 38, 3, 39, 0, 1, 'Active', 'A', 'a:1:{s:2:\"en\";s:6:\"Active\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(27, 1, 25, 40, 3, 41, 0, 1, 'Inactive', 'I', 'a:1:{s:2:\"en\";s:8:\"Inactive\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(28, 1, 3, 43, 2, 48, 0, 0, 'Publication status (basic)', '', 'a:1:{s:2:\"en\";s:26:\"Publication status (basic)\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(29, 1, 28, 44, 3, 45, 0, 1, 'Pending', 'P', 'a:1:{s:2:\"en\";s:7:\"Pending\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(30, 1, 28, 46, 3, 47, 0, 1, 'Approved', 'A', 'a:1:{s:2:\"en\";s:8:\"Approved\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(32, 1, 2, 3, 2, 22, 0, 0, 'Global', '', 'a:1:{s:2:\"en\";s:6:\"Global\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(33, 1, 32, 4, 3, 5, 0, 1, 'Blogging', '', 'a:1:{s:2:\"en\";s:8:\"Blogging\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(34, 1, 32, 6, 3, 7, 0, 1, 'Music and audio', '', 'a:1:{s:2:\"en\";s:15:\"Music and audio\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(35, 1, 32, 8, 3, 9, 0, 1, 'Art and photography', '', 'a:1:{s:2:\"en\";s:19:\"Art and photography\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(36, 1, 32, 10, 3, 11, 0, 1, 'Writing and thinking', '', 'a:1:{s:2:\"en\";s:20:\"Writing and thinking\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(37, 1, 32, 12, 3, 13, 0, 1, 'Communications and media', '', 'a:1:{s:2:\"en\";s:24:\"Communications and media\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(38, 1, 32, 14, 3, 15, 0, 1, 'Travel and culture', '', 'a:1:{s:2:\"en\";s:18:\"Travel and culture\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(39, 1, 32, 16, 3, 17, 0, 1, 'Science and technology', '', 'a:1:{s:2:\"en\";s:22:\"Science and technology\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(40, 1, 32, 18, 3, 19, 0, 1, 'Sport and activities', '', 'a:1:{s:2:\"en\";s:20:\"Sport and activities\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2),
(41, 1, 32, 20, 3, 21, 0, 1, 'Business and work', '', 'a:1:{s:2:\"en\";s:17:\"Business and work\";}', 'a:1:{s:2:\"en\";s:0:\"\";}', 'A', '2019-12-30 14:51:40', '2019-12-30 14:51:40', 2, 2);

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
  `cr_date` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `cr_uid` int(11) NOT NULL,
  `lu_date` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `lu_uid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories_registry`
--

CREATE TABLE `categories_registry` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `modname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `entityname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `property` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cr_date` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `lu_date` datetime NOT NULL COMMENT '(DC2Type:datetime)',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `sareaid` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `pareaid` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
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
  `pareaid` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
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
  `sowner` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `subsowner` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `powner` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `subpowner` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sareaid` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `pareaid` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `eventname` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `classname` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `serviceid` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  `sareaid` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `hooktype` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `category` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `eventname` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `root_id`, `parent_id`, `title`, `lft`, `lvl`, `rgt`, `options`) VALUES
(1, 1, NULL, 'mainMenu', 1, 0, 6, 'a:1:{s:18:\"childrenAttributes\";a:1:{s:5:\"class\";s:14:\"nav navbar-nav\";}}'),
(2, 1, 1, 'Home', 2, 1, 3, 'a:2:{s:5:\"route\";s:4:\"home\";s:10:\"attributes\";a:1:{s:4:\"icon\";s:10:\"fa fa-home\";}}'),
(3, 1, 1, 'Site search', 4, 1, 5, 'a:2:{s:5:\"route\";s:33:\"zikulasearchmodule_search_execute\";s:10:\"attributes\";a:1:{s:4:\"icon\";s:12:\"fa fa-search\";}}');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `name`, `type`, `displayname`, `url`, `description`, `directory`, `version`, `capabilities`, `state`, `securityschema`, `core_min`, `core_max`) VALUES
(1, 'ZikulaExtensionsModule', 3, 'Extensions', 'extensions', 'Extensions administration', 'ExtensionsModule', '3.7.14', 'a:1:{s:5:\"admin\";a:1:{s:5:\"route\";s:44:\"zikulaextensionsmodule_module_viewmodulelist\";}}', 3, 'a:2:{s:24:\"ZikulaExtensionsModule::\";s:2:\"::\";s:30:\"ZikulaExtensionsModule::modify\";s:27:\"extensionName::extensionsId\";}', '>=1.4.2', ''),
(2, 'ZikulaUsersModule', 3, 'Users Module', 'users', 'User account administration', 'UsersModule', '3.0.5', 'a:2:{s:5:\"admin\";a:1:{s:5:\"route\";s:41:\"zikulausersmodule_useradministration_list\";}s:4:\"user\";a:1:{s:5:\"route\";s:30:\"zikulausersmodule_account_menu\";}}', 3, 'a:5:{s:19:\"ZikulaUsersModule::\";s:14:\"Uname::User ID\";s:28:\"ZikulaUsersModule::MailUsers\";s:2:\"::\";s:14:\"Accountlinks::\";s:13:\"Block title::\";s:12:\"Loginblock::\";s:13:\"Block title::\";s:13:\"Onlineblock::\";s:10:\"Block ID::\";}', '>=1.4.3', ''),
(3, 'ZikulaBlocksModule', 3, 'Blocks Module', 'blocks', 'Blocks administration', 'BlocksModule', '3.9.8', 'a:1:{s:5:\"admin\";a:1:{s:5:\"route\";s:29:\"zikulablocksmodule_admin_view\";}}', 3, 'a:13:{s:20:\"ZikulaBlocksModule::\";s:30:\"Block key:Block title:Block ID\";s:28:\"ZikulaBlocksModule::position\";s:26:\"Position name::Position ID\";s:19:\"ExtendedMenublock::\";s:17:\"Block ID:Link ID:\";s:15:\"fincludeblock::\";s:13:\"Block title::\";s:11:\"HTMLblock::\";s:13:\"Block title::\";s:14:\"HTMLblock::bid\";s:5:\"::bid\";s:15:\"Languageblock::\";s:13:\"Block title::\";s:11:\"Menublock::\";s:22:\"Block title:Link name:\";s:23:\"Menutree:menutreeblock:\";s:26:\"Block ID:Link Name:Link ID\";s:16:\"PendingContent::\";s:13:\"Block title::\";s:11:\"Textblock::\";s:13:\"Block title::\";s:14:\"Textblock::bid\";s:5:\"::bid\";s:11:\"xsltblock::\";s:13:\"Block title::\";}', '>=1.4.1', ''),
(4, 'ZikulaZAuthModule', 3, 'Zikula Native Authorization', 'zauth', 'Native zikula authentication', 'ZAuthModule', '1.0.1', 'a:1:{s:5:\"admin\";a:1:{s:5:\"route\";s:41:\"zikulazauthmodule_useradministration_list\";}}', 3, 'a:1:{s:19:\"ZikulaZAuthModule::\";s:2:\"::\";}', '>=1.4.3', ''),
(5, 'ZikulaAdminModule', 3, 'Administration panel', 'adminpanel', 'Backend administration interface', 'AdminModule', '2.0.0', 'a:1:{s:5:\"admin\";a:1:{s:5:\"route\";s:28:\"zikulaadminmodule_admin_view\";}}', 3, 'a:2:{s:19:\"ZikulaAdminModule::\";s:38:\"Admin Category name::Admin Category ID\";s:32:\"ZikulaAdminModule:adminnavblock:\";s:21:\"Block title::Block ID\";}', '>=1.4.1', ''),
(6, 'ZikulaPermissionsModule', 3, 'Permissions', 'permissions', 'User permissions manager', 'PermissionsModule', '1.2.0', 'a:1:{s:5:\"admin\";a:1:{s:5:\"route\";s:39:\"zikulapermissionsmodule_permission_list\";}}', 3, 'a:1:{s:25:\"ZikulaPermissionsModule::\";s:2:\"::\";}', '>=1.4.1', ''),
(7, 'ZikulaCategoriesModule', 3, 'Categories', 'categories', 'Categories administration', 'CategoriesModule', '1.3.1', 'a:2:{s:5:\"admin\";a:1:{s:5:\"route\";s:36:\"zikulacategoriesmodule_category_list\";}s:4:\"user\";a:1:{s:3:\"url\";s:16:\"/159/categories/\";}}', 3, 'a:2:{s:24:\"ZikulaCategoriesModule::\";s:2:\"::\";s:44:\"ZikulaCategoriesModule:PropertyId:CategoryId\";s:15:\"$propId::$catId\";}', '>=1.4.1', ''),
(8, 'ZikulaSettingsModule', 3, 'General settings', 'settings', 'System settings administration', 'SettingsModule', '2.9.13', 'a:1:{s:5:\"admin\";a:1:{s:5:\"route\";s:34:\"zikulasettingsmodule_settings_main\";}}', 3, 'a:3:{s:22:\"ZikulaSettingsModule::\";s:2:\"::\";s:13:\"LocaleBlock::\";s:2:\"::\";s:16:\"LocaleBlock::bid\";s:5:\"::bid\";}', '>=1.4.2', ''),
(9, 'ZikulaSecurityCenterModule', 3, 'Security Center', 'securitycenter', 'Security administration', 'SecurityCenterModule', '1.5.2', 'a:1:{s:5:\"admin\";a:1:{s:5:\"route\";s:40:\"zikulasecuritycentermodule_config_config\";}}', 3, 'a:1:{s:28:\"ZikulaSecurityCenterModule::\";s:2:\"::\";}', '>=1.4.1', ''),
(10, 'ZikulaMailerModule', 3, 'Mailer Module', 'mailer', 'Mailer support', 'MailerModule', '1.5.1', 'a:1:{s:5:\"admin\";a:1:{s:5:\"route\";s:32:\"zikulamailermodule_config_config\";}}', 3, 'a:1:{s:20:\"ZikulaMailerModule::\";s:2:\"::\";}', '>=1.4.1', ''),
(11, 'ZikulaGroupsModule', 3, 'Groups', 'groups', 'User group administration', 'GroupsModule', '2.4.2', 'a:2:{s:5:\"admin\";a:1:{s:5:\"route\";s:34:\"zikulagroupsmodule_group_adminlist\";}s:4:\"user\";a:1:{s:5:\"route\";s:29:\"zikulagroupsmodule_group_list\";}}', 3, 'a:2:{s:20:\"ZikulaGroupsModule::\";s:10:\"Group ID::\";s:31:\"ZikulaGroupsModule::memberslist\";s:2:\"::\";}', '>=1.4.1', ''),
(12, 'ZikulaSearchModule', 3, 'Site search', 'search', 'Site search support', 'SearchModule', '1.6.0', 'a:2:{s:5:\"admin\";a:1:{s:5:\"route\";s:32:\"zikulasearchmodule_config_config\";}s:4:\"user\";a:1:{s:5:\"route\";s:33:\"zikulasearchmodule_search_execute\";}}', 3, 'a:2:{s:20:\"ZikulaSearchModule::\";s:13:\"Module name::\";s:13:\"Searchblock::\";s:13:\"Block title::\";}', '>=1.4.1', ''),
(13, 'ZikulaRoutesModule', 3, 'Routes', 'routes', 'Routes management', 'RoutesModule', '1.1.2', 'a:2:{s:4:\"user\";a:1:{s:5:\"route\";s:30:\"zikularoutesmodule_route_index\";}s:5:\"admin\";a:1:{s:5:\"route\";s:35:\"zikularoutesmodule_route_adminindex\";}}', 3, 'a:3:{s:20:\"ZikulaRoutesModule::\";s:2:\"::\";s:25:\"ZikulaRoutesModule:Route:\";s:10:\"Route ID::\";s:24:\"ZikulaRoutesModule::Ajax\";s:2:\"::\";}', '>=1.5.0 <3.0', ''),
(14, 'ZikulaMenuModule', 3, 'Menu Module', 'menu', 'Menu management', 'MenuModule', '1.0.1', 'a:1:{s:5:\"admin\";a:1:{s:5:\"route\";s:26:\"zikulamenumodule_menu_list\";}}', 3, 'a:2:{s:18:\"ZikulaMenuModule::\";s:2:\"::\";s:20:\"ZikulaMenuModule::id\";s:4:\"::id\";}', '>=1.4.4', ''),
(15, 'ZikulaThemeModule', 3, 'Theme Module', 'theme', 'Theme system and administration', 'ThemeModule', '3.4.3', 'a:2:{s:5:\"admin\";a:1:{s:5:\"route\";s:28:\"zikulathememodule_theme_view\";}s:4:\"user\";a:1:{s:5:\"route\";s:28:\"zikulathememodule_user_index\";}}', 3, 'a:2:{s:19:\"ZikulaThemeModule::\";s:11:\"ThemeName::\";s:30:\"ZikulaThemeModule::ThemeChange\";s:23:\":(ThemeName|ThemeName):\";}', '>=1.4.2', ''),
(16, 'ZikulaOAuthModule', 2, 'OAuthModule', 'oauth', 'Integrates league/oauth2-client and various providers.', 'zikula/oauth-module', '1.0.4', 'a:1:{s:5:\"admin\";a:1:{s:5:\"route\";s:33:\"zikulaoauthmodule_config_settings\";}}', 1, 'a:1:{s:19:\"ZikulaOAuthModule::\";s:2:\"::\";}', '>=1.4.3', ''),
(17, 'ZikulaLegalModule', 2, 'Legal', 'legal', 'Provides an interface for managing the site\'s legal documents.', 'zikula/legal-module', '3.1.2', 'a:2:{s:5:\"admin\";a:1:{s:5:\"route\";s:31:\"zikulalegalmodule_config_config\";}s:4:\"user\";a:1:{s:5:\"route\";s:33:\"zikulalegalmodule_user_termsofuse\";}}', 1, 'a:8:{s:19:\"ZikulaLegalModule::\";s:2:\"::\";s:30:\"ZikulaLegalModule::legalNotice\";s:2:\"::\";s:29:\"ZikulaLegalModule::termsOfUse\";s:2:\"::\";s:32:\"ZikulaLegalModule::privacyPolicy\";s:2:\"::\";s:28:\"ZikulaLegalModule::agePolicy\";s:2:\"::\";s:41:\"ZikulaLegalModule::accessibilityStatement\";s:2:\"::\";s:42:\"ZikulaLegalModule::cancellationRightPolicy\";s:2:\"::\";s:34:\"ZikulaLegalModule::tradeConditions\";s:2:\"::\";}', '>=1.5.0', ''),
(18, 'ZikulaProfileModule', 2, 'Profile', 'profile', 'User profiles and member list', 'zikula/profile-module', '3.0.5', 'a:2:{s:5:\"admin\";a:1:{s:5:\"route\";s:33:\"zikulaprofilemodule_property_list\";}s:4:\"user\";a:1:{s:5:\"route\";s:35:\"zikulaprofilemodule_profile_display\";}}', 1, 'a:11:{s:21:\"ZikulaProfileModule::\";s:2:\"::\";s:25:\"ZikulaProfileModule::view\";s:2:\"::\";s:25:\"ZikulaProfileModule::item\";s:12:\"PropertyId::\";s:27:\"ZikulaProfileModule:Members\";s:2:\"::\";s:34:\"ZikulaProfileModule:Members:recent\";s:2:\"::\";s:34:\"ZikulaProfileModule:Members:online\";s:2:\"::\";s:38:\"ZikulaProfileModule:FeaturedUserblock:\";s:13:\"Block title::\";s:34:\"ZikulaProfileModule:LastSeenblock:\";s:13:\"Block title::\";s:36:\"ZikulaProfileModule:LastXUsersblock:\";s:13:\"Block title::\";s:39:\"ZikulaProfileModule:MembersOnlineblock:\";s:13:\"Block title::\";s:11:\"Userblock::\";s:13:\"Block title::\";}', '>=1.5.0', ''),
(19, 'ZikulaPageLockModule', 2, 'Page lock', 'pagelock', 'Page locking support', 'zikula/pagelock-module', '1.2.3', 'a:0:{}', 1, 'a:1:{s:22:\"ZikulaPageLockModule::\";s:2:\"::\";}', '>=1.4.1', '');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `module_deps`
--

INSERT INTO `module_deps` (`id`, `modid`, `modname`, `minversion`, `maxversion`, `status`) VALUES
(1, 2, 'php', '>=5.5.9', '>=5.5.9', 1),
(2, 1, 'php', '>=5.5.9', '>=5.5.9', 1),
(3, 1, 'composer/semver', '1.*', '1.*', 1),
(4, 1, 'justinrainbow/json-schema', '4.*', '4.*', 1),
(5, 3, 'php', '>=5.5.9', '>=5.5.9', 1),
(6, 3, 'Scribite', '>=5.0.0', '>=5.0.0', 2),
(7, 4, 'php', '>=5.5.9', '>=5.5.9', 1),
(8, 4, 'ircmaxell/random-lib', '1.*', '1.*', 1),
(9, 5, 'php', '>=5.5.9', '>=5.5.9', 1),
(10, 5, 'jquery.mmenu', '7.*', '7.*', 1),
(11, 6, 'php', '>=5.5.9', '>=5.5.9', 1),
(12, 7, 'php', '>=5.5.9', '>=5.5.9', 1),
(13, 8, 'php', '>=5.5.9', '>=5.5.9', 1),
(14, 9, 'php', '>=5.5.9', '>=5.5.9', 1),
(15, 9, 'phpids/phpids', 'dev-master', 'dev-master', 1),
(16, 10, 'php', '>=5.5.9', '>=5.5.9', 1),
(17, 10, 'symfony/swiftmailer-bundle', '2.4.*', '2.4.*', 1),
(18, 11, 'php', '>=5.5.9', '>=5.5.9', 1),
(19, 12, 'php', '>=5.5.9', '>=5.5.9', 1),
(20, 13, 'php', '>=5.5.9', '>=5.5.9', 1),
(21, 13, 'jms/i18n-routing-bundle', '2.0.0', '2.0.0', 1),
(22, 13, 'friendsofsymfony/jsrouting-bundle', '1.6.*', '1.6.*', 1),
(23, 14, 'php', '>=5.5.9', '>=5.5.9', 1),
(24, 14, 'knplabs/knp-menu-bundle', '^2.0', '^2.0', 1),
(25, 15, 'php', '>=5.5.9', '>=5.5.9', 1),
(26, 15, 'robloach/component-installer', '*', '*', 1),
(27, 15, 'zikula/jquery-bundle', '1.*', '1.*', 1),
(28, 15, 'zikula/jquery-ui-bundle', '1.*', '1.*', 1),
(29, 15, 'zikula/bootstrap-bundle', '3.*', '3.*', 1),
(30, 15, 'zikula/fontawesome-bundle', '4.1.*', '4.1.*', 1),
(31, 15, 'zikula/jquery-minicolors-bundle', '1.*', '1.*', 1),
(32, 15, 'bootstrap-plus/bootstrap-media-lightbox', '3.*', '3.*', 1),
(33, 15, 'bootstrap-plus/bootstrap-jqueryui', 'dev-master', 'dev-master', 1),
(34, 15, 'dimsemenov/magnific-popup', '1.*', '1.*', 1),
(35, 15, 'vakata/jstree', '3.*', '3.*', 1),
(36, 16, 'php', '>5.4.1', '>5.4.1', 1),
(37, 16, 'league/oauth2-facebook', '~1.4.1', '~1.4.1', 1),
(38, 16, 'league/oauth2-github', '~0.2.1', '~0.2.1', 1),
(39, 16, 'league/oauth2-google', '~1.0.1', '~1.0.1', 1),
(40, 16, 'league/oauth2-instagram', '~0.2.2', '~0.2.2', 1),
(41, 16, 'league/oauth2-linkedin', '~0.4.0', '~0.4.0', 1),
(42, 17, 'php', '>=5.5.9', '>=5.5.9', 1),
(43, 18, 'php', '>=5.5.9', '>=5.5.9', 1),
(44, 19, 'php', '>5.4.0', '>5.4.0', 1);

-- --------------------------------------------------------

--
-- Table structure for table `module_vars`
--

CREATE TABLE `module_vars` (
  `id` int(11) NOT NULL,
  `modname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `module_vars`
--

INSERT INTO `module_vars` (`id`, `modname`, `name`, `value`) VALUES
(1, 'ZikulaExtensionsModule', 'itemsperpage', 'i:40;'),
(2, 'ZConfig', 'debug', 's:1:\"0\";'),
(3, 'ZConfig', 'startdate', 's:7:\"12/2019\";'),
(4, 'ZConfig', 'adminmail', 's:17:\"admin@example.com\";'),
(5, 'ZConfig', 'Default_Theme', 's:20:\"ZikulaBootstrapTheme\";'),
(6, 'ZConfig', 'timezone', 's:15:\"America/Detroit\";'),
(7, 'ZConfig', 'funtext', 's:1:\"1\";'),
(8, 'ZConfig', 'reportlevel', 's:1:\"0\";'),
(9, 'ZConfig', 'startpage', 's:0:\"\";'),
(10, 'ZConfig', 'Version_Num', 's:5:\"1.5.9\";'),
(11, 'ZConfig', 'Version_ID', 's:6:\"Zikula\";'),
(12, 'ZConfig', 'Version_Sub', 's:9:\"Interlude\";'),
(13, 'ZConfig', 'debug_sql', 's:1:\"0\";'),
(14, 'ZConfig', 'multilingual', 's:1:\"1\";'),
(15, 'ZConfig', 'useflags', 's:1:\"0\";'),
(16, 'ZConfig', 'theme_change', 's:1:\"0\";'),
(17, 'ZConfig', 'UseCompression', 's:1:\"0\";'),
(18, 'ZConfig', 'siteoff', 'i:0;'),
(19, 'ZConfig', 'siteoffreason', 's:0:\"\";'),
(20, 'ZConfig', 'starttype', 's:0:\"\";'),
(21, 'ZConfig', 'startfunc', 's:0:\"\";'),
(22, 'ZConfig', 'startargs', 's:0:\"\";'),
(23, 'ZConfig', 'entrypoint', 's:9:\"index.php\";'),
(24, 'ZConfig', 'language_detect', 'i:0;'),
(25, 'ZConfig', 'shorturls', 'b:0;'),
(26, 'ZConfig', 'shorturlstype', 's:1:\"0\";'),
(27, 'ZConfig', 'shorturlsseparator', 's:1:\"-\";'),
(28, 'ZConfig', 'sitename_en', 's:9:\"Site name\";'),
(29, 'ZConfig', 'slogan_en', 's:16:\"Site description\";'),
(30, 'ZConfig', 'metakeywords_en', 's:115:\"zikula, portal, open source, web site, website, weblog, blog, content management system, cms, application framework\";'),
(31, 'ZConfig', 'defaultpagetitle_en', 's:9:\"Site name\";'),
(32, 'ZConfig', 'defaultmetadescription_en', 's:16:\"Site description\";'),
(33, 'ZConfig', 'shorturlsstripentrypoint', 'b:0;'),
(34, 'ZConfig', 'shorturlsdefaultmodule', 's:0:\"\";'),
(35, 'ZConfig', 'profilemodule', 's:0:\"\";'),
(36, 'ZConfig', 'messagemodule', 's:0:\"\";'),
(37, 'ZConfig', 'languageurl', 'i:0;'),
(38, 'ZConfig', 'ajaxtimeout', 'i:5000;'),
(39, 'ZConfig', 'permasearch', 's:161:\"À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü\";'),
(40, 'ZConfig', 'permareplace', 's:114:\"A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,ss,ae,Ae,oe,Oe,ue,Ue\";'),
(41, 'ZConfig', 'language', 's:3:\"eng\";'),
(42, 'ZConfig', 'locale', 's:2:\"en\";'),
(43, 'ZConfig', 'language_i18n', 's:2:\"en\";'),
(44, 'ZConfig', 'idnnames', 'i:1;'),
(45, 'ZikulaThemeModule', 'modulesnocache', 's:0:\"\";'),
(46, 'ZikulaThemeModule', 'enablecache', 'b:0;'),
(47, 'ZikulaThemeModule', 'compile_check', 'b:1;'),
(48, 'ZikulaThemeModule', 'cache_lifetime', 'i:1800;'),
(49, 'ZikulaThemeModule', 'cache_lifetime_mods', 'i:1800;'),
(50, 'ZikulaThemeModule', 'force_compile', 'b:0;'),
(51, 'ZikulaThemeModule', 'trimwhitespace', 'b:0;'),
(52, 'ZikulaThemeModule', 'maxsizeforlinks', 'i:30;'),
(53, 'ZikulaThemeModule', 'itemsperpage', 'i:25;'),
(54, 'ZikulaThemeModule', 'cssjscombine', 'b:0;'),
(55, 'ZikulaThemeModule', 'cssjscompress', 'b:0;'),
(56, 'ZikulaThemeModule', 'cssjsminify', 'b:0;'),
(57, 'ZikulaThemeModule', 'cssjscombine_lifetime', 'i:3600;'),
(58, 'ZikulaThemeModule', 'render_compile_check', 'b:1;'),
(59, 'ZikulaThemeModule', 'render_force_compile', 'b:0;'),
(60, 'ZikulaThemeModule', 'render_cache', 'b:0;'),
(61, 'ZikulaThemeModule', 'render_expose_template', 'b:0;'),
(62, 'ZikulaThemeModule', 'render_lifetime', 'i:3600;'),
(63, 'ZikulaAdminModule', 'modulesperrow', 'i:3;'),
(64, 'ZikulaAdminModule', 'itemsperpage', 'i:15;'),
(65, 'ZikulaAdminModule', 'defaultcategory', 'i:5;'),
(66, 'ZikulaAdminModule', 'admingraphic', 'i:1;'),
(67, 'ZikulaAdminModule', 'startcategory', 'i:1;'),
(68, 'ZikulaAdminModule', 'ignoreinstallercheck', 'i:0;'),
(69, 'ZikulaAdminModule', 'admintheme', 's:0:\"\";'),
(70, 'ZikulaAdminModule', 'displaynametype', 'i:1;'),
(71, 'ZikulaPermissionsModule', 'lockadmin', 'i:1;'),
(72, 'ZikulaPermissionsModule', 'adminid', 'i:1;'),
(73, 'ZikulaPermissionsModule', 'filter', 'i:1;'),
(74, 'ZikulaPermissionsModule', 'rowview', 'i:25;'),
(75, 'ZikulaPermissionsModule', 'rowedit', 'i:35;'),
(76, 'ZikulaUsersModule', 'accountdisplaygraphics', 'b:1;'),
(77, 'ZikulaUsersModule', 'accountitemsperpage', 'i:25;'),
(78, 'ZikulaUsersModule', 'accountitemsperrow', 'i:5;'),
(79, 'ZikulaUsersModule', 'userimg', 's:11:\"images/menu\";'),
(80, 'ZikulaUsersModule', 'anonymous', 's:5:\"Guest\";'),
(81, 'ZikulaUsersModule', 'itemsperpage', 'i:25;'),
(82, 'ZikulaUsersModule', 'login_displayapproval', 'b:0;'),
(83, 'ZikulaUsersModule', 'login_displaydelete', 'b:0;'),
(84, 'ZikulaUsersModule', 'login_displayinactive', 'b:0;'),
(85, 'ZikulaUsersModule', 'login_displayverify', 'b:0;'),
(86, 'ZikulaUsersModule', 'reg_notifyemail', 's:0:\"\";'),
(87, 'ZikulaUsersModule', 'moderation', 'b:0;'),
(88, 'ZikulaUsersModule', 'reg_autologin', 'b:1;'),
(89, 'ZikulaUsersModule', 'reg_noregreasons', 's:51:\"Sorry! New user registration is currently disabled.\";'),
(90, 'ZikulaUsersModule', 'reg_allowreg', 'b:1;'),
(91, 'ZikulaUsersModule', 'reg_Illegaluseragents', 's:0:\"\";'),
(92, 'ZikulaUsersModule', 'reg_Illegaldomains', 's:0:\"\";'),
(93, 'ZikulaUsersModule', 'reg_Illegalusername', 's:66:\"root, webmaster, admin, administrator, nobody, anonymous, username\";'),
(94, 'ZConfig', 'authenticationMethodsStatus', 'a:1:{s:12:\"native_uname\";b:1;}'),
(95, 'ZikulaZAuthModule', 'hash_method', 's:6:\"sha256\";'),
(96, 'ZikulaZAuthModule', 'minpass', 'i:5;'),
(97, 'ZikulaZAuthModule', 'use_password_strength_meter', 'b:0;'),
(98, 'ZikulaZAuthModule', 'chgemail_expiredays', 'i:0;'),
(99, 'ZikulaZAuthModule', 'chgpass_expiredays', 'i:0;'),
(100, 'ZikulaZAuthModule', 'reg_expiredays', 'i:0;'),
(101, 'ZikulaZAuthModule', 'email_verification_required', 'b:1;'),
(102, 'ZikulaZAuthModule', 'reg_answer', 's:0:\"\";'),
(103, 'ZikulaZAuthModule', 'reg_question', 's:0:\"\";'),
(104, 'ZikulaGroupsModule', 'itemsperpage', 'i:25;'),
(105, 'ZikulaGroupsModule', 'defaultgroup', 'i:1;'),
(106, 'ZikulaGroupsModule', 'mailwarning', 'b:0;'),
(107, 'ZikulaGroupsModule', 'hideclosed', 'b:0;'),
(108, 'ZikulaGroupsModule', 'hidePrivate', 'b:0;'),
(109, 'ZikulaGroupsModule', 'primaryadmingroup', 'i:2;'),
(110, 'ZikulaBlocksModule', 'collapseable', 'b:0;'),
(111, 'ZikulaSecurityCenterModule', 'itemsperpage', 'i:10;'),
(112, 'ZConfig', 'updatecheck', 'i:1;'),
(113, 'ZConfig', 'updatefrequency', 'i:7;'),
(114, 'ZConfig', 'updatelastchecked', 'i:1577735533;'),
(115, 'ZConfig', 'updateversion', 's:6:\"2.0.15\";'),
(116, 'ZConfig', 'keyexpiry', 'i:0;'),
(117, 'ZConfig', 'sessionauthkeyua', 'i:0;'),
(118, 'ZConfig', 'secure_domain', 's:0:\"\";'),
(119, 'ZConfig', 'signcookies', 'i:1;'),
(120, 'ZConfig', 'signingkey', 's:40:\"093a544d339cfd04b145b03cb768c91ae2f4f73e\";'),
(121, 'ZConfig', 'seclevel', 's:6:\"Medium\";'),
(122, 'ZConfig', 'secmeddays', 'i:7;'),
(123, 'ZConfig', 'secinactivemins', 'i:20;'),
(124, 'ZConfig', 'sessionstoretofile', 'i:1;'),
(125, 'ZConfig', 'sessionsavepath', 's:0:\"\";'),
(126, 'ZConfig', 'gc_probability', 'i:100;'),
(127, 'ZConfig', 'sessioncsrftokenonetime', 'i:1;'),
(128, 'ZConfig', 'anonymoussessions', 'i:1;'),
(129, 'ZConfig', 'sessionrandregenerate', 'i:1;'),
(130, 'ZConfig', 'sessionregenerate', 'i:1;'),
(131, 'ZConfig', 'sessionregeneratefreq', 'i:10;'),
(132, 'ZConfig', 'sessionipcheck', 'i:0;'),
(133, 'ZConfig', 'sessionname', 's:5:\"_zsid\";'),
(134, 'ZConfig', 'filtergetvars', 'i:1;'),
(135, 'ZConfig', 'filterpostvars', 'i:1;'),
(136, 'ZConfig', 'filtercookievars', 'i:1;'),
(137, 'ZikulaSecurityCenterModule', 'htmlpurifierConfig', 's:33504:\"O:19:\"HTMLPurifier_Config\":12:{s:7:\"version\";s:6:\"4.10.0\";s:12:\"autoFinalize\";b:1;s:10:\"\0*\0serials\";a:4:{s:4:\"Attr\";b:0;s:4:\"HTML\";b:0;s:6:\"Output\";b:0;s:5:\"Cache\";b:0;}s:9:\"\0*\0serial\";N;s:9:\"\0*\0parser\";N;s:3:\"def\";O:25:\"HTMLPurifier_ConfigSchema\":3:{s:8:\"defaults\";a:125:{s:19:\"Attr.AllowedClasses\";N;s:24:\"Attr.AllowedFrameTargets\";a:0:{}s:15:\"Attr.AllowedRel\";a:0:{}s:15:\"Attr.AllowedRev\";a:0:{}s:18:\"Attr.ClassUseCDATA\";N;s:20:\"Attr.DefaultImageAlt\";N;s:24:\"Attr.DefaultInvalidImage\";s:0:\"\";s:27:\"Attr.DefaultInvalidImageAlt\";s:13:\"Invalid image\";s:19:\"Attr.DefaultTextDir\";s:3:\"ltr\";s:13:\"Attr.EnableID\";b:0;s:21:\"Attr.ForbiddenClasses\";a:0:{}s:13:\"Attr.ID.HTML5\";N;s:16:\"Attr.IDBlacklist\";a:0:{}s:22:\"Attr.IDBlacklistRegexp\";N;s:13:\"Attr.IDPrefix\";s:0:\"\";s:18:\"Attr.IDPrefixLocal\";s:0:\"\";s:24:\"AutoFormat.AutoParagraph\";b:0;s:17:\"AutoFormat.Custom\";a:0:{}s:25:\"AutoFormat.DisplayLinkURI\";b:0;s:18:\"AutoFormat.Linkify\";b:0;s:33:\"AutoFormat.PurifierLinkify.DocURL\";s:3:\"#%s\";s:26:\"AutoFormat.PurifierLinkify\";b:0;s:32:\"AutoFormat.RemoveEmpty.Predicate\";a:4:{s:8:\"colgroup\";a:0:{}s:2:\"th\";a:0:{}s:2:\"td\";a:0:{}s:6:\"iframe\";a:1:{i:0;s:3:\"src\";}}s:44:\"AutoFormat.RemoveEmpty.RemoveNbsp.Exceptions\";a:2:{s:2:\"td\";b:1;s:2:\"th\";b:1;}s:33:\"AutoFormat.RemoveEmpty.RemoveNbsp\";b:0;s:22:\"AutoFormat.RemoveEmpty\";b:0;s:39:\"AutoFormat.RemoveSpansWithoutAttributes\";b:0;s:19:\"CSS.AllowDuplicates\";b:0;s:18:\"CSS.AllowImportant\";b:0;s:15:\"CSS.AllowTricky\";b:0;s:16:\"CSS.AllowedFonts\";N;s:21:\"CSS.AllowedProperties\";N;s:17:\"CSS.DefinitionRev\";i:1;s:23:\"CSS.ForbiddenProperties\";a:0:{}s:16:\"CSS.MaxImgLength\";s:6:\"1200px\";s:15:\"CSS.Proprietary\";b:0;s:11:\"CSS.Trusted\";b:0;s:20:\"Cache.DefinitionImpl\";s:10:\"Serializer\";s:20:\"Cache.SerializerPath\";N;s:27:\"Cache.SerializerPermissions\";i:493;s:22:\"Core.AggressivelyFixLt\";b:1;s:29:\"Core.AggressivelyRemoveScript\";b:1;s:28:\"Core.AllowHostnameUnderscore\";b:0;s:18:\"Core.CollectErrors\";b:0;s:18:\"Core.ColorKeywords\";a:148:{s:9:\"aliceblue\";s:7:\"#F0F8FF\";s:12:\"antiquewhite\";s:7:\"#FAEBD7\";s:4:\"aqua\";s:7:\"#00FFFF\";s:10:\"aquamarine\";s:7:\"#7FFFD4\";s:5:\"azure\";s:7:\"#F0FFFF\";s:5:\"beige\";s:7:\"#F5F5DC\";s:6:\"bisque\";s:7:\"#FFE4C4\";s:5:\"black\";s:7:\"#000000\";s:14:\"blanchedalmond\";s:7:\"#FFEBCD\";s:4:\"blue\";s:7:\"#0000FF\";s:10:\"blueviolet\";s:7:\"#8A2BE2\";s:5:\"brown\";s:7:\"#A52A2A\";s:9:\"burlywood\";s:7:\"#DEB887\";s:9:\"cadetblue\";s:7:\"#5F9EA0\";s:10:\"chartreuse\";s:7:\"#7FFF00\";s:9:\"chocolate\";s:7:\"#D2691E\";s:5:\"coral\";s:7:\"#FF7F50\";s:14:\"cornflowerblue\";s:7:\"#6495ED\";s:8:\"cornsilk\";s:7:\"#FFF8DC\";s:7:\"crimson\";s:7:\"#DC143C\";s:4:\"cyan\";s:7:\"#00FFFF\";s:8:\"darkblue\";s:7:\"#00008B\";s:8:\"darkcyan\";s:7:\"#008B8B\";s:13:\"darkgoldenrod\";s:7:\"#B8860B\";s:8:\"darkgray\";s:7:\"#A9A9A9\";s:8:\"darkgrey\";s:7:\"#A9A9A9\";s:9:\"darkgreen\";s:7:\"#006400\";s:9:\"darkkhaki\";s:7:\"#BDB76B\";s:11:\"darkmagenta\";s:7:\"#8B008B\";s:14:\"darkolivegreen\";s:7:\"#556B2F\";s:10:\"darkorange\";s:7:\"#FF8C00\";s:10:\"darkorchid\";s:7:\"#9932CC\";s:7:\"darkred\";s:7:\"#8B0000\";s:10:\"darksalmon\";s:7:\"#E9967A\";s:12:\"darkseagreen\";s:7:\"#8FBC8F\";s:13:\"darkslateblue\";s:7:\"#483D8B\";s:13:\"darkslategray\";s:7:\"#2F4F4F\";s:13:\"darkslategrey\";s:7:\"#2F4F4F\";s:13:\"darkturquoise\";s:7:\"#00CED1\";s:10:\"darkviolet\";s:7:\"#9400D3\";s:8:\"deeppink\";s:7:\"#FF1493\";s:11:\"deepskyblue\";s:7:\"#00BFFF\";s:7:\"dimgray\";s:7:\"#696969\";s:7:\"dimgrey\";s:7:\"#696969\";s:10:\"dodgerblue\";s:7:\"#1E90FF\";s:9:\"firebrick\";s:7:\"#B22222\";s:11:\"floralwhite\";s:7:\"#FFFAF0\";s:11:\"forestgreen\";s:7:\"#228B22\";s:7:\"fuchsia\";s:7:\"#FF00FF\";s:9:\"gainsboro\";s:7:\"#DCDCDC\";s:10:\"ghostwhite\";s:7:\"#F8F8FF\";s:4:\"gold\";s:7:\"#FFD700\";s:9:\"goldenrod\";s:7:\"#DAA520\";s:4:\"gray\";s:7:\"#808080\";s:4:\"grey\";s:7:\"#808080\";s:5:\"green\";s:7:\"#008000\";s:11:\"greenyellow\";s:7:\"#ADFF2F\";s:8:\"honeydew\";s:7:\"#F0FFF0\";s:7:\"hotpink\";s:7:\"#FF69B4\";s:9:\"indianred\";s:7:\"#CD5C5C\";s:6:\"indigo\";s:7:\"#4B0082\";s:5:\"ivory\";s:7:\"#FFFFF0\";s:5:\"khaki\";s:7:\"#F0E68C\";s:8:\"lavender\";s:7:\"#E6E6FA\";s:13:\"lavenderblush\";s:7:\"#FFF0F5\";s:9:\"lawngreen\";s:7:\"#7CFC00\";s:12:\"lemonchiffon\";s:7:\"#FFFACD\";s:9:\"lightblue\";s:7:\"#ADD8E6\";s:10:\"lightcoral\";s:7:\"#F08080\";s:9:\"lightcyan\";s:7:\"#E0FFFF\";s:20:\"lightgoldenrodyellow\";s:7:\"#FAFAD2\";s:9:\"lightgray\";s:7:\"#D3D3D3\";s:9:\"lightgrey\";s:7:\"#D3D3D3\";s:10:\"lightgreen\";s:7:\"#90EE90\";s:9:\"lightpink\";s:7:\"#FFB6C1\";s:11:\"lightsalmon\";s:7:\"#FFA07A\";s:13:\"lightseagreen\";s:7:\"#20B2AA\";s:12:\"lightskyblue\";s:7:\"#87CEFA\";s:14:\"lightslategray\";s:7:\"#778899\";s:14:\"lightslategrey\";s:7:\"#778899\";s:14:\"lightsteelblue\";s:7:\"#B0C4DE\";s:11:\"lightyellow\";s:7:\"#FFFFE0\";s:4:\"lime\";s:7:\"#00FF00\";s:9:\"limegreen\";s:7:\"#32CD32\";s:5:\"linen\";s:7:\"#FAF0E6\";s:7:\"magenta\";s:7:\"#FF00FF\";s:6:\"maroon\";s:7:\"#800000\";s:16:\"mediumaquamarine\";s:7:\"#66CDAA\";s:10:\"mediumblue\";s:7:\"#0000CD\";s:12:\"mediumorchid\";s:7:\"#BA55D3\";s:12:\"mediumpurple\";s:7:\"#9370DB\";s:14:\"mediumseagreen\";s:7:\"#3CB371\";s:15:\"mediumslateblue\";s:7:\"#7B68EE\";s:17:\"mediumspringgreen\";s:7:\"#00FA9A\";s:15:\"mediumturquoise\";s:7:\"#48D1CC\";s:15:\"mediumvioletred\";s:7:\"#C71585\";s:12:\"midnightblue\";s:7:\"#191970\";s:9:\"mintcream\";s:7:\"#F5FFFA\";s:9:\"mistyrose\";s:7:\"#FFE4E1\";s:8:\"moccasin\";s:7:\"#FFE4B5\";s:11:\"navajowhite\";s:7:\"#FFDEAD\";s:4:\"navy\";s:7:\"#000080\";s:7:\"oldlace\";s:7:\"#FDF5E6\";s:5:\"olive\";s:7:\"#808000\";s:9:\"olivedrab\";s:7:\"#6B8E23\";s:6:\"orange\";s:7:\"#FFA500\";s:9:\"orangered\";s:7:\"#FF4500\";s:6:\"orchid\";s:7:\"#DA70D6\";s:13:\"palegoldenrod\";s:7:\"#EEE8AA\";s:9:\"palegreen\";s:7:\"#98FB98\";s:13:\"paleturquoise\";s:7:\"#AFEEEE\";s:13:\"palevioletred\";s:7:\"#DB7093\";s:10:\"papayawhip\";s:7:\"#FFEFD5\";s:9:\"peachpuff\";s:7:\"#FFDAB9\";s:4:\"peru\";s:7:\"#CD853F\";s:4:\"pink\";s:7:\"#FFC0CB\";s:4:\"plum\";s:7:\"#DDA0DD\";s:10:\"powderblue\";s:7:\"#B0E0E6\";s:6:\"purple\";s:7:\"#800080\";s:13:\"rebeccapurple\";s:7:\"#663399\";s:3:\"red\";s:7:\"#FF0000\";s:9:\"rosybrown\";s:7:\"#BC8F8F\";s:9:\"royalblue\";s:7:\"#4169E1\";s:11:\"saddlebrown\";s:7:\"#8B4513\";s:6:\"salmon\";s:7:\"#FA8072\";s:10:\"sandybrown\";s:7:\"#F4A460\";s:8:\"seagreen\";s:7:\"#2E8B57\";s:8:\"seashell\";s:7:\"#FFF5EE\";s:6:\"sienna\";s:7:\"#A0522D\";s:6:\"silver\";s:7:\"#C0C0C0\";s:7:\"skyblue\";s:7:\"#87CEEB\";s:9:\"slateblue\";s:7:\"#6A5ACD\";s:9:\"slategray\";s:7:\"#708090\";s:9:\"slategrey\";s:7:\"#708090\";s:4:\"snow\";s:7:\"#FFFAFA\";s:11:\"springgreen\";s:7:\"#00FF7F\";s:9:\"steelblue\";s:7:\"#4682B4\";s:3:\"tan\";s:7:\"#D2B48C\";s:4:\"teal\";s:7:\"#008080\";s:7:\"thistle\";s:7:\"#D8BFD8\";s:6:\"tomato\";s:7:\"#FF6347\";s:9:\"turquoise\";s:7:\"#40E0D0\";s:6:\"violet\";s:7:\"#EE82EE\";s:5:\"wheat\";s:7:\"#F5DEB3\";s:5:\"white\";s:7:\"#FFFFFF\";s:10:\"whitesmoke\";s:7:\"#F5F5F5\";s:6:\"yellow\";s:7:\"#FFFF00\";s:11:\"yellowgreen\";s:7:\"#9ACD32\";}s:30:\"Core.ConvertDocumentToFragment\";b:1;s:36:\"Core.DirectLexLineNumberSyncInterval\";i:0;s:20:\"Core.DisableExcludes\";b:0;s:15:\"Core.EnableIDNA\";b:0;s:13:\"Core.Encoding\";s:5:\"utf-8\";s:26:\"Core.EscapeInvalidChildren\";b:0;s:22:\"Core.EscapeInvalidTags\";b:0;s:29:\"Core.EscapeNonASCIICharacters\";b:0;s:19:\"Core.HiddenElements\";a:2:{s:6:\"script\";b:1;s:5:\"style\";b:1;}s:13:\"Core.Language\";s:2:\"en\";s:24:\"Core.LegacyEntityDecoder\";b:0;s:14:\"Core.LexerImpl\";N;s:24:\"Core.MaintainLineNumbers\";N;s:22:\"Core.NormalizeNewlines\";b:1;s:21:\"Core.RemoveInvalidImg\";b:1;s:33:\"Core.RemoveProcessingInstructions\";b:0;s:25:\"Core.RemoveScriptContents\";N;s:13:\"Filter.Custom\";a:0:{}s:34:\"Filter.ExtractStyleBlocks.Escaping\";b:1;s:31:\"Filter.ExtractStyleBlocks.Scope\";N;s:34:\"Filter.ExtractStyleBlocks.TidyImpl\";N;s:25:\"Filter.ExtractStyleBlocks\";b:0;s:14:\"Filter.YouTube\";b:0;s:12:\"HTML.Allowed\";N;s:22:\"HTML.AllowedAttributes\";N;s:20:\"HTML.AllowedComments\";a:0:{}s:26:\"HTML.AllowedCommentsRegexp\";N;s:20:\"HTML.AllowedElements\";N;s:19:\"HTML.AllowedModules\";N;s:23:\"HTML.Attr.Name.UseCDATA\";b:0;s:17:\"HTML.BlockWrapper\";s:1:\"p\";s:16:\"HTML.CoreModules\";a:7:{s:9:\"Structure\";b:1;s:4:\"Text\";b:1;s:9:\"Hypertext\";b:1;s:4:\"List\";b:1;s:22:\"NonXMLCommonAttributes\";b:1;s:19:\"XMLCommonAttributes\";b:1;s:16:\"CommonAttributes\";b:1;}s:18:\"HTML.CustomDoctype\";N;s:17:\"HTML.DefinitionID\";N;s:18:\"HTML.DefinitionRev\";i:1;s:12:\"HTML.Doctype\";N;s:25:\"HTML.FlashAllowFullScreen\";b:0;s:24:\"HTML.ForbiddenAttributes\";a:0:{}s:22:\"HTML.ForbiddenElements\";a:0:{}s:17:\"HTML.MaxImgLength\";i:1200;s:13:\"HTML.Nofollow\";b:0;s:11:\"HTML.Parent\";s:3:\"div\";s:16:\"HTML.Proprietary\";b:0;s:14:\"HTML.SafeEmbed\";b:0;s:15:\"HTML.SafeIframe\";b:0;s:15:\"HTML.SafeObject\";b:0;s:18:\"HTML.SafeScripting\";a:0:{}s:11:\"HTML.Strict\";b:0;s:16:\"HTML.TargetBlank\";b:0;s:19:\"HTML.TargetNoopener\";b:1;s:21:\"HTML.TargetNoreferrer\";b:1;s:12:\"HTML.TidyAdd\";a:0:{}s:14:\"HTML.TidyLevel\";s:6:\"medium\";s:15:\"HTML.TidyRemove\";a:0:{}s:12:\"HTML.Trusted\";b:0;s:10:\"HTML.XHTML\";b:1;s:28:\"Output.CommentScriptContents\";b:1;s:19:\"Output.FixInnerHTML\";b:1;s:18:\"Output.FlashCompat\";b:0;s:14:\"Output.Newline\";N;s:15:\"Output.SortAttr\";b:0;s:17:\"Output.TidyFormat\";b:0;s:17:\"Test.ForceNoIconv\";b:0;s:18:\"URI.AllowedSchemes\";a:7:{s:4:\"http\";b:1;s:5:\"https\";b:1;s:6:\"mailto\";b:1;s:3:\"ftp\";b:1;s:4:\"nntp\";b:1;s:4:\"news\";b:1;s:3:\"tel\";b:1;}s:8:\"URI.Base\";N;s:17:\"URI.DefaultScheme\";s:4:\"http\";s:16:\"URI.DefinitionID\";N;s:17:\"URI.DefinitionRev\";i:1;s:11:\"URI.Disable\";b:0;s:19:\"URI.DisableExternal\";b:0;s:28:\"URI.DisableExternalResources\";b:0;s:20:\"URI.DisableResources\";b:0;s:8:\"URI.Host\";N;s:17:\"URI.HostBlacklist\";a:0:{}s:16:\"URI.MakeAbsolute\";b:0;s:9:\"URI.Munge\";N;s:18:\"URI.MungeResources\";b:0;s:18:\"URI.MungeSecretKey\";N;s:26:\"URI.OverrideAllowedSchemes\";b:1;s:20:\"URI.SafeIframeRegexp\";N;}s:12:\"defaultPlist\";O:25:\"HTMLPurifier_PropertyList\":3:{s:7:\"\0*\0data\";a:125:{s:19:\"Attr.AllowedClasses\";N;s:24:\"Attr.AllowedFrameTargets\";a:0:{}s:15:\"Attr.AllowedRel\";a:0:{}s:15:\"Attr.AllowedRev\";a:0:{}s:18:\"Attr.ClassUseCDATA\";N;s:20:\"Attr.DefaultImageAlt\";N;s:24:\"Attr.DefaultInvalidImage\";s:0:\"\";s:27:\"Attr.DefaultInvalidImageAlt\";s:13:\"Invalid image\";s:19:\"Attr.DefaultTextDir\";s:3:\"ltr\";s:13:\"Attr.EnableID\";b:0;s:21:\"Attr.ForbiddenClasses\";a:0:{}s:13:\"Attr.ID.HTML5\";N;s:16:\"Attr.IDBlacklist\";a:0:{}s:22:\"Attr.IDBlacklistRegexp\";N;s:13:\"Attr.IDPrefix\";s:0:\"\";s:18:\"Attr.IDPrefixLocal\";s:0:\"\";s:24:\"AutoFormat.AutoParagraph\";b:0;s:17:\"AutoFormat.Custom\";a:0:{}s:25:\"AutoFormat.DisplayLinkURI\";b:0;s:18:\"AutoFormat.Linkify\";b:0;s:33:\"AutoFormat.PurifierLinkify.DocURL\";s:3:\"#%s\";s:26:\"AutoFormat.PurifierLinkify\";b:0;s:32:\"AutoFormat.RemoveEmpty.Predicate\";a:4:{s:8:\"colgroup\";a:0:{}s:2:\"th\";a:0:{}s:2:\"td\";a:0:{}s:6:\"iframe\";a:1:{i:0;s:3:\"src\";}}s:44:\"AutoFormat.RemoveEmpty.RemoveNbsp.Exceptions\";a:2:{s:2:\"td\";b:1;s:2:\"th\";b:1;}s:33:\"AutoFormat.RemoveEmpty.RemoveNbsp\";b:0;s:22:\"AutoFormat.RemoveEmpty\";b:0;s:39:\"AutoFormat.RemoveSpansWithoutAttributes\";b:0;s:19:\"CSS.AllowDuplicates\";b:0;s:18:\"CSS.AllowImportant\";b:0;s:15:\"CSS.AllowTricky\";b:0;s:16:\"CSS.AllowedFonts\";N;s:21:\"CSS.AllowedProperties\";N;s:17:\"CSS.DefinitionRev\";i:1;s:23:\"CSS.ForbiddenProperties\";a:0:{}s:16:\"CSS.MaxImgLength\";s:6:\"1200px\";s:15:\"CSS.Proprietary\";b:0;s:11:\"CSS.Trusted\";b:0;s:20:\"Cache.DefinitionImpl\";s:10:\"Serializer\";s:20:\"Cache.SerializerPath\";N;s:27:\"Cache.SerializerPermissions\";i:493;s:22:\"Core.AggressivelyFixLt\";b:1;s:29:\"Core.AggressivelyRemoveScript\";b:1;s:28:\"Core.AllowHostnameUnderscore\";b:0;s:18:\"Core.CollectErrors\";b:0;s:18:\"Core.ColorKeywords\";a:148:{s:9:\"aliceblue\";s:7:\"#F0F8FF\";s:12:\"antiquewhite\";s:7:\"#FAEBD7\";s:4:\"aqua\";s:7:\"#00FFFF\";s:10:\"aquamarine\";s:7:\"#7FFFD4\";s:5:\"azure\";s:7:\"#F0FFFF\";s:5:\"beige\";s:7:\"#F5F5DC\";s:6:\"bisque\";s:7:\"#FFE4C4\";s:5:\"black\";s:7:\"#000000\";s:14:\"blanchedalmond\";s:7:\"#FFEBCD\";s:4:\"blue\";s:7:\"#0000FF\";s:10:\"blueviolet\";s:7:\"#8A2BE2\";s:5:\"brown\";s:7:\"#A52A2A\";s:9:\"burlywood\";s:7:\"#DEB887\";s:9:\"cadetblue\";s:7:\"#5F9EA0\";s:10:\"chartreuse\";s:7:\"#7FFF00\";s:9:\"chocolate\";s:7:\"#D2691E\";s:5:\"coral\";s:7:\"#FF7F50\";s:14:\"cornflowerblue\";s:7:\"#6495ED\";s:8:\"cornsilk\";s:7:\"#FFF8DC\";s:7:\"crimson\";s:7:\"#DC143C\";s:4:\"cyan\";s:7:\"#00FFFF\";s:8:\"darkblue\";s:7:\"#00008B\";s:8:\"darkcyan\";s:7:\"#008B8B\";s:13:\"darkgoldenrod\";s:7:\"#B8860B\";s:8:\"darkgray\";s:7:\"#A9A9A9\";s:8:\"darkgrey\";s:7:\"#A9A9A9\";s:9:\"darkgreen\";s:7:\"#006400\";s:9:\"darkkhaki\";s:7:\"#BDB76B\";s:11:\"darkmagenta\";s:7:\"#8B008B\";s:14:\"darkolivegreen\";s:7:\"#556B2F\";s:10:\"darkorange\";s:7:\"#FF8C00\";s:10:\"darkorchid\";s:7:\"#9932CC\";s:7:\"darkred\";s:7:\"#8B0000\";s:10:\"darksalmon\";s:7:\"#E9967A\";s:12:\"darkseagreen\";s:7:\"#8FBC8F\";s:13:\"darkslateblue\";s:7:\"#483D8B\";s:13:\"darkslategray\";s:7:\"#2F4F4F\";s:13:\"darkslategrey\";s:7:\"#2F4F4F\";s:13:\"darkturquoise\";s:7:\"#00CED1\";s:10:\"darkviolet\";s:7:\"#9400D3\";s:8:\"deeppink\";s:7:\"#FF1493\";s:11:\"deepskyblue\";s:7:\"#00BFFF\";s:7:\"dimgray\";s:7:\"#696969\";s:7:\"dimgrey\";s:7:\"#696969\";s:10:\"dodgerblue\";s:7:\"#1E90FF\";s:9:\"firebrick\";s:7:\"#B22222\";s:11:\"floralwhite\";s:7:\"#FFFAF0\";s:11:\"forestgreen\";s:7:\"#228B22\";s:7:\"fuchsia\";s:7:\"#FF00FF\";s:9:\"gainsboro\";s:7:\"#DCDCDC\";s:10:\"ghostwhite\";s:7:\"#F8F8FF\";s:4:\"gold\";s:7:\"#FFD700\";s:9:\"goldenrod\";s:7:\"#DAA520\";s:4:\"gray\";s:7:\"#808080\";s:4:\"grey\";s:7:\"#808080\";s:5:\"green\";s:7:\"#008000\";s:11:\"greenyellow\";s:7:\"#ADFF2F\";s:8:\"honeydew\";s:7:\"#F0FFF0\";s:7:\"hotpink\";s:7:\"#FF69B4\";s:9:\"indianred\";s:7:\"#CD5C5C\";s:6:\"indigo\";s:7:\"#4B0082\";s:5:\"ivory\";s:7:\"#FFFFF0\";s:5:\"khaki\";s:7:\"#F0E68C\";s:8:\"lavender\";s:7:\"#E6E6FA\";s:13:\"lavenderblush\";s:7:\"#FFF0F5\";s:9:\"lawngreen\";s:7:\"#7CFC00\";s:12:\"lemonchiffon\";s:7:\"#FFFACD\";s:9:\"lightblue\";s:7:\"#ADD8E6\";s:10:\"lightcoral\";s:7:\"#F08080\";s:9:\"lightcyan\";s:7:\"#E0FFFF\";s:20:\"lightgoldenrodyellow\";s:7:\"#FAFAD2\";s:9:\"lightgray\";s:7:\"#D3D3D3\";s:9:\"lightgrey\";s:7:\"#D3D3D3\";s:10:\"lightgreen\";s:7:\"#90EE90\";s:9:\"lightpink\";s:7:\"#FFB6C1\";s:11:\"lightsalmon\";s:7:\"#FFA07A\";s:13:\"lightseagreen\";s:7:\"#20B2AA\";s:12:\"lightskyblue\";s:7:\"#87CEFA\";s:14:\"lightslategray\";s:7:\"#778899\";s:14:\"lightslategrey\";s:7:\"#778899\";s:14:\"lightsteelblue\";s:7:\"#B0C4DE\";s:11:\"lightyellow\";s:7:\"#FFFFE0\";s:4:\"lime\";s:7:\"#00FF00\";s:9:\"limegreen\";s:7:\"#32CD32\";s:5:\"linen\";s:7:\"#FAF0E6\";s:7:\"magenta\";s:7:\"#FF00FF\";s:6:\"maroon\";s:7:\"#800000\";s:16:\"mediumaquamarine\";s:7:\"#66CDAA\";s:10:\"mediumblue\";s:7:\"#0000CD\";s:12:\"mediumorchid\";s:7:\"#BA55D3\";s:12:\"mediumpurple\";s:7:\"#9370DB\";s:14:\"mediumseagreen\";s:7:\"#3CB371\";s:15:\"mediumslateblue\";s:7:\"#7B68EE\";s:17:\"mediumspringgreen\";s:7:\"#00FA9A\";s:15:\"mediumturquoise\";s:7:\"#48D1CC\";s:15:\"mediumvioletred\";s:7:\"#C71585\";s:12:\"midnightblue\";s:7:\"#191970\";s:9:\"mintcream\";s:7:\"#F5FFFA\";s:9:\"mistyrose\";s:7:\"#FFE4E1\";s:8:\"moccasin\";s:7:\"#FFE4B5\";s:11:\"navajowhite\";s:7:\"#FFDEAD\";s:4:\"navy\";s:7:\"#000080\";s:7:\"oldlace\";s:7:\"#FDF5E6\";s:5:\"olive\";s:7:\"#808000\";s:9:\"olivedrab\";s:7:\"#6B8E23\";s:6:\"orange\";s:7:\"#FFA500\";s:9:\"orangered\";s:7:\"#FF4500\";s:6:\"orchid\";s:7:\"#DA70D6\";s:13:\"palegoldenrod\";s:7:\"#EEE8AA\";s:9:\"palegreen\";s:7:\"#98FB98\";s:13:\"paleturquoise\";s:7:\"#AFEEEE\";s:13:\"palevioletred\";s:7:\"#DB7093\";s:10:\"papayawhip\";s:7:\"#FFEFD5\";s:9:\"peachpuff\";s:7:\"#FFDAB9\";s:4:\"peru\";s:7:\"#CD853F\";s:4:\"pink\";s:7:\"#FFC0CB\";s:4:\"plum\";s:7:\"#DDA0DD\";s:10:\"powderblue\";s:7:\"#B0E0E6\";s:6:\"purple\";s:7:\"#800080\";s:13:\"rebeccapurple\";s:7:\"#663399\";s:3:\"red\";s:7:\"#FF0000\";s:9:\"rosybrown\";s:7:\"#BC8F8F\";s:9:\"royalblue\";s:7:\"#4169E1\";s:11:\"saddlebrown\";s:7:\"#8B4513\";s:6:\"salmon\";s:7:\"#FA8072\";s:10:\"sandybrown\";s:7:\"#F4A460\";s:8:\"seagreen\";s:7:\"#2E8B57\";s:8:\"seashell\";s:7:\"#FFF5EE\";s:6:\"sienna\";s:7:\"#A0522D\";s:6:\"silver\";s:7:\"#C0C0C0\";s:7:\"skyblue\";s:7:\"#87CEEB\";s:9:\"slateblue\";s:7:\"#6A5ACD\";s:9:\"slategray\";s:7:\"#708090\";s:9:\"slategrey\";s:7:\"#708090\";s:4:\"snow\";s:7:\"#FFFAFA\";s:11:\"springgreen\";s:7:\"#00FF7F\";s:9:\"steelblue\";s:7:\"#4682B4\";s:3:\"tan\";s:7:\"#D2B48C\";s:4:\"teal\";s:7:\"#008080\";s:7:\"thistle\";s:7:\"#D8BFD8\";s:6:\"tomato\";s:7:\"#FF6347\";s:9:\"turquoise\";s:7:\"#40E0D0\";s:6:\"violet\";s:7:\"#EE82EE\";s:5:\"wheat\";s:7:\"#F5DEB3\";s:5:\"white\";s:7:\"#FFFFFF\";s:10:\"whitesmoke\";s:7:\"#F5F5F5\";s:6:\"yellow\";s:7:\"#FFFF00\";s:11:\"yellowgreen\";s:7:\"#9ACD32\";}s:30:\"Core.ConvertDocumentToFragment\";b:1;s:36:\"Core.DirectLexLineNumberSyncInterval\";i:0;s:20:\"Core.DisableExcludes\";b:0;s:15:\"Core.EnableIDNA\";b:0;s:13:\"Core.Encoding\";s:5:\"utf-8\";s:26:\"Core.EscapeInvalidChildren\";b:0;s:22:\"Core.EscapeInvalidTags\";b:0;s:29:\"Core.EscapeNonASCIICharacters\";b:0;s:19:\"Core.HiddenElements\";a:2:{s:6:\"script\";b:1;s:5:\"style\";b:1;}s:13:\"Core.Language\";s:2:\"en\";s:24:\"Core.LegacyEntityDecoder\";b:0;s:14:\"Core.LexerImpl\";N;s:24:\"Core.MaintainLineNumbers\";N;s:22:\"Core.NormalizeNewlines\";b:1;s:21:\"Core.RemoveInvalidImg\";b:1;s:33:\"Core.RemoveProcessingInstructions\";b:0;s:25:\"Core.RemoveScriptContents\";N;s:13:\"Filter.Custom\";a:0:{}s:34:\"Filter.ExtractStyleBlocks.Escaping\";b:1;s:31:\"Filter.ExtractStyleBlocks.Scope\";N;s:34:\"Filter.ExtractStyleBlocks.TidyImpl\";N;s:25:\"Filter.ExtractStyleBlocks\";b:0;s:14:\"Filter.YouTube\";b:0;s:12:\"HTML.Allowed\";N;s:22:\"HTML.AllowedAttributes\";N;s:20:\"HTML.AllowedComments\";a:0:{}s:26:\"HTML.AllowedCommentsRegexp\";N;s:20:\"HTML.AllowedElements\";N;s:19:\"HTML.AllowedModules\";N;s:23:\"HTML.Attr.Name.UseCDATA\";b:0;s:17:\"HTML.BlockWrapper\";s:1:\"p\";s:16:\"HTML.CoreModules\";a:7:{s:9:\"Structure\";b:1;s:4:\"Text\";b:1;s:9:\"Hypertext\";b:1;s:4:\"List\";b:1;s:22:\"NonXMLCommonAttributes\";b:1;s:19:\"XMLCommonAttributes\";b:1;s:16:\"CommonAttributes\";b:1;}s:18:\"HTML.CustomDoctype\";N;s:17:\"HTML.DefinitionID\";N;s:18:\"HTML.DefinitionRev\";i:1;s:12:\"HTML.Doctype\";N;s:25:\"HTML.FlashAllowFullScreen\";b:0;s:24:\"HTML.ForbiddenAttributes\";a:0:{}s:22:\"HTML.ForbiddenElements\";a:0:{}s:17:\"HTML.MaxImgLength\";i:1200;s:13:\"HTML.Nofollow\";b:0;s:11:\"HTML.Parent\";s:3:\"div\";s:16:\"HTML.Proprietary\";b:0;s:14:\"HTML.SafeEmbed\";b:0;s:15:\"HTML.SafeIframe\";b:0;s:15:\"HTML.SafeObject\";b:0;s:18:\"HTML.SafeScripting\";a:0:{}s:11:\"HTML.Strict\";b:0;s:16:\"HTML.TargetBlank\";b:0;s:19:\"HTML.TargetNoopener\";b:1;s:21:\"HTML.TargetNoreferrer\";b:1;s:12:\"HTML.TidyAdd\";a:0:{}s:14:\"HTML.TidyLevel\";s:6:\"medium\";s:15:\"HTML.TidyRemove\";a:0:{}s:12:\"HTML.Trusted\";b:0;s:10:\"HTML.XHTML\";b:1;s:28:\"Output.CommentScriptContents\";b:1;s:19:\"Output.FixInnerHTML\";b:1;s:18:\"Output.FlashCompat\";b:0;s:14:\"Output.Newline\";N;s:15:\"Output.SortAttr\";b:0;s:17:\"Output.TidyFormat\";b:0;s:17:\"Test.ForceNoIconv\";b:0;s:18:\"URI.AllowedSchemes\";a:7:{s:4:\"http\";b:1;s:5:\"https\";b:1;s:6:\"mailto\";b:1;s:3:\"ftp\";b:1;s:4:\"nntp\";b:1;s:4:\"news\";b:1;s:3:\"tel\";b:1;}s:8:\"URI.Base\";N;s:17:\"URI.DefaultScheme\";s:4:\"http\";s:16:\"URI.DefinitionID\";N;s:17:\"URI.DefinitionRev\";i:1;s:11:\"URI.Disable\";b:0;s:19:\"URI.DisableExternal\";b:0;s:28:\"URI.DisableExternalResources\";b:0;s:20:\"URI.DisableResources\";b:0;s:8:\"URI.Host\";N;s:17:\"URI.HostBlacklist\";a:0:{}s:16:\"URI.MakeAbsolute\";b:0;s:9:\"URI.Munge\";N;s:18:\"URI.MungeResources\";b:0;s:18:\"URI.MungeSecretKey\";N;s:26:\"URI.OverrideAllowedSchemes\";b:1;s:20:\"URI.SafeIframeRegexp\";N;}s:9:\"\0*\0parent\";N;s:8:\"\0*\0cache\";N;}s:4:\"info\";a:138:{s:19:\"Attr.AllowedClasses\";i:-8;s:24:\"Attr.AllowedFrameTargets\";i:8;s:15:\"Attr.AllowedRel\";i:8;s:15:\"Attr.AllowedRev\";i:8;s:18:\"Attr.ClassUseCDATA\";i:-7;s:20:\"Attr.DefaultImageAlt\";i:-1;s:24:\"Attr.DefaultInvalidImage\";i:1;s:27:\"Attr.DefaultInvalidImageAlt\";i:1;s:19:\"Attr.DefaultTextDir\";O:8:\"stdClass\":2:{s:4:\"type\";i:1;s:7:\"allowed\";a:2:{s:3:\"ltr\";b:1;s:3:\"rtl\";b:1;}}s:13:\"Attr.EnableID\";i:7;s:17:\"HTML.EnableAttrID\";O:8:\"stdClass\":2:{s:3:\"key\";s:13:\"Attr.EnableID\";s:7:\"isAlias\";b:1;}s:21:\"Attr.ForbiddenClasses\";i:8;s:13:\"Attr.ID.HTML5\";i:-7;s:16:\"Attr.IDBlacklist\";i:9;s:22:\"Attr.IDBlacklistRegexp\";i:-1;s:13:\"Attr.IDPrefix\";i:1;s:18:\"Attr.IDPrefixLocal\";i:1;s:24:\"AutoFormat.AutoParagraph\";i:7;s:17:\"AutoFormat.Custom\";i:9;s:25:\"AutoFormat.DisplayLinkURI\";i:7;s:18:\"AutoFormat.Linkify\";i:7;s:33:\"AutoFormat.PurifierLinkify.DocURL\";i:1;s:37:\"AutoFormatParam.PurifierLinkifyDocURL\";O:8:\"stdClass\":2:{s:3:\"key\";s:33:\"AutoFormat.PurifierLinkify.DocURL\";s:7:\"isAlias\";b:1;}s:26:\"AutoFormat.PurifierLinkify\";i:7;s:32:\"AutoFormat.RemoveEmpty.Predicate\";i:10;s:44:\"AutoFormat.RemoveEmpty.RemoveNbsp.Exceptions\";i:8;s:33:\"AutoFormat.RemoveEmpty.RemoveNbsp\";i:7;s:22:\"AutoFormat.RemoveEmpty\";i:7;s:39:\"AutoFormat.RemoveSpansWithoutAttributes\";i:7;s:19:\"CSS.AllowDuplicates\";i:7;s:18:\"CSS.AllowImportant\";i:7;s:15:\"CSS.AllowTricky\";i:7;s:16:\"CSS.AllowedFonts\";i:-8;s:21:\"CSS.AllowedProperties\";i:-8;s:17:\"CSS.DefinitionRev\";i:5;s:23:\"CSS.ForbiddenProperties\";i:8;s:16:\"CSS.MaxImgLength\";i:-1;s:15:\"CSS.Proprietary\";i:7;s:11:\"CSS.Trusted\";i:7;s:20:\"Cache.DefinitionImpl\";i:-1;s:20:\"Core.DefinitionCache\";O:8:\"stdClass\":2:{s:3:\"key\";s:20:\"Cache.DefinitionImpl\";s:7:\"isAlias\";b:1;}s:20:\"Cache.SerializerPath\";i:-1;s:27:\"Cache.SerializerPermissions\";i:-5;s:22:\"Core.AggressivelyFixLt\";i:7;s:29:\"Core.AggressivelyRemoveScript\";i:7;s:28:\"Core.AllowHostnameUnderscore\";i:7;s:18:\"Core.CollectErrors\";i:7;s:18:\"Core.ColorKeywords\";i:10;s:30:\"Core.ConvertDocumentToFragment\";i:7;s:24:\"Core.AcceptFullDocuments\";O:8:\"stdClass\":2:{s:3:\"key\";s:30:\"Core.ConvertDocumentToFragment\";s:7:\"isAlias\";b:1;}s:36:\"Core.DirectLexLineNumberSyncInterval\";i:5;s:20:\"Core.DisableExcludes\";i:7;s:15:\"Core.EnableIDNA\";i:7;s:13:\"Core.Encoding\";i:2;s:26:\"Core.EscapeInvalidChildren\";i:7;s:22:\"Core.EscapeInvalidTags\";i:7;s:29:\"Core.EscapeNonASCIICharacters\";i:7;s:19:\"Core.HiddenElements\";i:8;s:13:\"Core.Language\";i:1;s:24:\"Core.LegacyEntityDecoder\";i:7;s:14:\"Core.LexerImpl\";i:-11;s:24:\"Core.MaintainLineNumbers\";i:-7;s:22:\"Core.NormalizeNewlines\";i:7;s:21:\"Core.RemoveInvalidImg\";i:7;s:33:\"Core.RemoveProcessingInstructions\";i:7;s:25:\"Core.RemoveScriptContents\";i:-7;s:13:\"Filter.Custom\";i:9;s:34:\"Filter.ExtractStyleBlocks.Escaping\";i:7;s:33:\"Filter.ExtractStyleBlocksEscaping\";O:8:\"stdClass\":2:{s:3:\"key\";s:34:\"Filter.ExtractStyleBlocks.Escaping\";s:7:\"isAlias\";b:1;}s:38:\"FilterParam.ExtractStyleBlocksEscaping\";O:8:\"stdClass\":2:{s:3:\"key\";s:34:\"Filter.ExtractStyleBlocks.Escaping\";s:7:\"isAlias\";b:1;}s:31:\"Filter.ExtractStyleBlocks.Scope\";i:-1;s:30:\"Filter.ExtractStyleBlocksScope\";O:8:\"stdClass\":2:{s:3:\"key\";s:31:\"Filter.ExtractStyleBlocks.Scope\";s:7:\"isAlias\";b:1;}s:35:\"FilterParam.ExtractStyleBlocksScope\";O:8:\"stdClass\":2:{s:3:\"key\";s:31:\"Filter.ExtractStyleBlocks.Scope\";s:7:\"isAlias\";b:1;}s:34:\"Filter.ExtractStyleBlocks.TidyImpl\";i:-11;s:38:\"FilterParam.ExtractStyleBlocksTidyImpl\";O:8:\"stdClass\":2:{s:3:\"key\";s:34:\"Filter.ExtractStyleBlocks.TidyImpl\";s:7:\"isAlias\";b:1;}s:25:\"Filter.ExtractStyleBlocks\";i:7;s:14:\"Filter.YouTube\";i:7;s:12:\"HTML.Allowed\";i:-4;s:22:\"HTML.AllowedAttributes\";i:-8;s:20:\"HTML.AllowedComments\";i:8;s:26:\"HTML.AllowedCommentsRegexp\";i:-1;s:20:\"HTML.AllowedElements\";i:-8;s:19:\"HTML.AllowedModules\";i:-8;s:23:\"HTML.Attr.Name.UseCDATA\";i:7;s:17:\"HTML.BlockWrapper\";i:1;s:16:\"HTML.CoreModules\";i:8;s:18:\"HTML.CustomDoctype\";i:-1;s:17:\"HTML.DefinitionID\";i:-1;s:18:\"HTML.DefinitionRev\";i:5;s:12:\"HTML.Doctype\";O:8:\"stdClass\":3:{s:4:\"type\";i:1;s:10:\"allow_null\";b:1;s:7:\"allowed\";a:5:{s:22:\"HTML 4.01 Transitional\";b:1;s:16:\"HTML 4.01 Strict\";b:1;s:22:\"XHTML 1.0 Transitional\";b:1;s:16:\"XHTML 1.0 Strict\";b:1;s:9:\"XHTML 1.1\";b:1;}}s:25:\"HTML.FlashAllowFullScreen\";i:7;s:24:\"HTML.ForbiddenAttributes\";i:8;s:22:\"HTML.ForbiddenElements\";i:8;s:17:\"HTML.MaxImgLength\";i:-5;s:13:\"HTML.Nofollow\";i:7;s:11:\"HTML.Parent\";i:1;s:16:\"HTML.Proprietary\";i:7;s:14:\"HTML.SafeEmbed\";i:7;s:15:\"HTML.SafeIframe\";i:7;s:15:\"HTML.SafeObject\";i:7;s:18:\"HTML.SafeScripting\";i:8;s:11:\"HTML.Strict\";i:7;s:16:\"HTML.TargetBlank\";i:7;s:19:\"HTML.TargetNoopener\";i:7;s:21:\"HTML.TargetNoreferrer\";i:7;s:12:\"HTML.TidyAdd\";i:8;s:14:\"HTML.TidyLevel\";O:8:\"stdClass\":2:{s:4:\"type\";i:1;s:7:\"allowed\";a:4:{s:4:\"none\";b:1;s:5:\"light\";b:1;s:6:\"medium\";b:1;s:5:\"heavy\";b:1;}}s:15:\"HTML.TidyRemove\";i:8;s:12:\"HTML.Trusted\";i:7;s:10:\"HTML.XHTML\";i:7;s:10:\"Core.XHTML\";O:8:\"stdClass\":2:{s:3:\"key\";s:10:\"HTML.XHTML\";s:7:\"isAlias\";b:1;}s:28:\"Output.CommentScriptContents\";i:7;s:26:\"Core.CommentScriptContents\";O:8:\"stdClass\":2:{s:3:\"key\";s:28:\"Output.CommentScriptContents\";s:7:\"isAlias\";b:1;}s:19:\"Output.FixInnerHTML\";i:7;s:18:\"Output.FlashCompat\";i:7;s:14:\"Output.Newline\";i:-1;s:15:\"Output.SortAttr\";i:7;s:17:\"Output.TidyFormat\";i:7;s:15:\"Core.TidyFormat\";O:8:\"stdClass\":2:{s:3:\"key\";s:17:\"Output.TidyFormat\";s:7:\"isAlias\";b:1;}s:17:\"Test.ForceNoIconv\";i:7;s:18:\"URI.AllowedSchemes\";i:8;s:8:\"URI.Base\";i:-1;s:17:\"URI.DefaultScheme\";i:-1;s:16:\"URI.DefinitionID\";i:-1;s:17:\"URI.DefinitionRev\";i:5;s:11:\"URI.Disable\";i:7;s:15:\"Attr.DisableURI\";O:8:\"stdClass\":2:{s:3:\"key\";s:11:\"URI.Disable\";s:7:\"isAlias\";b:1;}s:19:\"URI.DisableExternal\";i:7;s:28:\"URI.DisableExternalResources\";i:7;s:20:\"URI.DisableResources\";i:7;s:8:\"URI.Host\";i:-1;s:17:\"URI.HostBlacklist\";i:9;s:16:\"URI.MakeAbsolute\";i:7;s:9:\"URI.Munge\";i:-1;s:18:\"URI.MungeResources\";i:7;s:18:\"URI.MungeSecretKey\";i:-1;s:26:\"URI.OverrideAllowedSchemes\";i:7;s:20:\"URI.SafeIframeRegexp\";i:-1;}}s:14:\"\0*\0definitions\";a:1:{s:4:\"HTML\";O:27:\"HTMLPurifier_HTMLDefinition\":16:{s:4:\"info\";a:0:{}s:16:\"info_global_attr\";a:0:{}s:11:\"info_parent\";s:3:\"div\";s:15:\"info_parent_def\";N;s:18:\"info_block_wrapper\";s:1:\"p\";s:18:\"info_tag_transform\";a:0:{}s:23:\"info_attr_transform_pre\";a:0:{}s:24:\"info_attr_transform_post\";a:0:{}s:17:\"info_content_sets\";a:0:{}s:13:\"info_injector\";a:0:{}s:7:\"doctype\";N;s:40:\"\0HTMLPurifier_HTMLDefinition\0_anonModule\";O:23:\"HTMLPurifier_HTMLModule\":11:{s:4:\"name\";s:9:\"Anonymous\";s:8:\"elements\";a:1:{i:0;s:6:\"iframe\";}s:4:\"info\";a:1:{s:6:\"iframe\";O:23:\"HTMLPurifier_ElementDef\":13:{s:10:\"standalone\";b:0;s:4:\"attr\";a:1:{s:15:\"allowfullscreen\";s:4:\"Bool\";}s:18:\"attr_transform_pre\";a:0:{}s:19:\"attr_transform_post\";a:0:{}s:5:\"child\";N;s:13:\"content_model\";N;s:18:\"content_model_type\";N;s:22:\"descendants_are_inline\";b:0;s:13:\"required_attr\";a:0:{}s:8:\"excludes\";a:0:{}s:9:\"autoclose\";a:0:{}s:4:\"wrap\";N;s:10:\"formatting\";N;}}s:12:\"content_sets\";a:0:{}s:16:\"attr_collections\";a:0:{}s:18:\"info_tag_transform\";a:0:{}s:23:\"info_attr_transform_pre\";a:0:{}s:24:\"info_attr_transform_post\";a:0:{}s:13:\"info_injector\";a:0:{}s:17:\"defines_child_def\";b:0;s:4:\"safe\";b:1;}s:4:\"type\";s:4:\"HTML\";s:7:\"manager\";O:30:\"HTMLPurifier_HTMLModuleManager\":11:{s:8:\"doctypes\";O:28:\"HTMLPurifier_DoctypeRegistry\":2:{s:11:\"\0*\0doctypes\";a:5:{s:22:\"HTML 4.01 Transitional\";O:20:\"HTMLPurifier_Doctype\":7:{s:4:\"name\";s:22:\"HTML 4.01 Transitional\";s:7:\"modules\";a:18:{i:0;s:16:\"CommonAttributes\";i:1;s:4:\"Text\";i:2;s:9:\"Hypertext\";i:3;s:4:\"List\";i:4;s:12:\"Presentation\";i:5;s:4:\"Edit\";i:6;s:3:\"Bdo\";i:7;s:6:\"Tables\";i:8;s:5:\"Image\";i:9;s:14:\"StyleAttribute\";i:10;s:9:\"Scripting\";i:11;s:6:\"Object\";i:12;s:5:\"Forms\";i:13;s:4:\"Name\";i:14;s:6:\"Legacy\";i:15;s:6:\"Target\";i:16;s:6:\"Iframe\";i:17;s:22:\"NonXMLCommonAttributes\";}s:11:\"tidyModules\";a:2:{i:0;s:17:\"Tidy_Transitional\";i:1;s:16:\"Tidy_Proprietary\";}s:3:\"xml\";b:0;s:7:\"aliases\";a:0:{}s:9:\"dtdPublic\";s:38:\"-//W3C//DTD HTML 4.01 Transitional//EN\";s:9:\"dtdSystem\";s:36:\"http://www.w3.org/TR/html4/loose.dtd\";}s:16:\"HTML 4.01 Strict\";O:20:\"HTMLPurifier_Doctype\":7:{s:4:\"name\";s:16:\"HTML 4.01 Strict\";s:7:\"modules\";a:15:{i:0;s:16:\"CommonAttributes\";i:1;s:4:\"Text\";i:2;s:9:\"Hypertext\";i:3;s:4:\"List\";i:4;s:12:\"Presentation\";i:5;s:4:\"Edit\";i:6;s:3:\"Bdo\";i:7;s:6:\"Tables\";i:8;s:5:\"Image\";i:9;s:14:\"StyleAttribute\";i:10;s:9:\"Scripting\";i:11;s:6:\"Object\";i:12;s:5:\"Forms\";i:13;s:4:\"Name\";i:14;s:22:\"NonXMLCommonAttributes\";}s:11:\"tidyModules\";a:3:{i:0;s:11:\"Tidy_Strict\";i:1;s:16:\"Tidy_Proprietary\";i:2;s:9:\"Tidy_Name\";}s:3:\"xml\";b:0;s:7:\"aliases\";a:0:{}s:9:\"dtdPublic\";s:25:\"-//W3C//DTD HTML 4.01//EN\";s:9:\"dtdSystem\";s:37:\"http://www.w3.org/TR/html4/strict.dtd\";}s:22:\"XHTML 1.0 Transitional\";O:20:\"HTMLPurifier_Doctype\":7:{s:4:\"name\";s:22:\"XHTML 1.0 Transitional\";s:7:\"modules\";a:19:{i:0;s:16:\"CommonAttributes\";i:1;s:4:\"Text\";i:2;s:9:\"Hypertext\";i:3;s:4:\"List\";i:4;s:12:\"Presentation\";i:5;s:4:\"Edit\";i:6;s:3:\"Bdo\";i:7;s:6:\"Tables\";i:8;s:5:\"Image\";i:9;s:14:\"StyleAttribute\";i:10;s:9:\"Scripting\";i:11;s:6:\"Object\";i:12;s:5:\"Forms\";i:13;s:4:\"Name\";i:14;s:6:\"Legacy\";i:15;s:6:\"Target\";i:16;s:6:\"Iframe\";i:17;s:19:\"XMLCommonAttributes\";i:18;s:22:\"NonXMLCommonAttributes\";}s:11:\"tidyModules\";a:4:{i:0;s:17:\"Tidy_Transitional\";i:1;s:10:\"Tidy_XHTML\";i:2;s:16:\"Tidy_Proprietary\";i:3;s:9:\"Tidy_Name\";}s:3:\"xml\";b:1;s:7:\"aliases\";a:0:{}s:9:\"dtdPublic\";s:38:\"-//W3C//DTD XHTML 1.0 Transitional//EN\";s:9:\"dtdSystem\";s:55:\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\";}s:16:\"XHTML 1.0 Strict\";O:20:\"HTMLPurifier_Doctype\":7:{s:4:\"name\";s:16:\"XHTML 1.0 Strict\";s:7:\"modules\";a:16:{i:0;s:16:\"CommonAttributes\";i:1;s:4:\"Text\";i:2;s:9:\"Hypertext\";i:3;s:4:\"List\";i:4;s:12:\"Presentation\";i:5;s:4:\"Edit\";i:6;s:3:\"Bdo\";i:7;s:6:\"Tables\";i:8;s:5:\"Image\";i:9;s:14:\"StyleAttribute\";i:10;s:9:\"Scripting\";i:11;s:6:\"Object\";i:12;s:5:\"Forms\";i:13;s:4:\"Name\";i:14;s:19:\"XMLCommonAttributes\";i:15;s:22:\"NonXMLCommonAttributes\";}s:11:\"tidyModules\";a:5:{i:0;s:11:\"Tidy_Strict\";i:1;s:10:\"Tidy_XHTML\";i:2;s:11:\"Tidy_Strict\";i:3;s:16:\"Tidy_Proprietary\";i:4;s:9:\"Tidy_Name\";}s:3:\"xml\";b:1;s:7:\"aliases\";a:0:{}s:9:\"dtdPublic\";s:32:\"-//W3C//DTD XHTML 1.0 Strict//EN\";s:9:\"dtdSystem\";s:49:\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\";}s:9:\"XHTML 1.1\";O:20:\"HTMLPurifier_Doctype\":7:{s:4:\"name\";s:9:\"XHTML 1.1\";s:7:\"modules\";a:17:{i:0;s:16:\"CommonAttributes\";i:1;s:4:\"Text\";i:2;s:9:\"Hypertext\";i:3;s:4:\"List\";i:4;s:12:\"Presentation\";i:5;s:4:\"Edit\";i:6;s:3:\"Bdo\";i:7;s:6:\"Tables\";i:8;s:5:\"Image\";i:9;s:14:\"StyleAttribute\";i:10;s:9:\"Scripting\";i:11;s:6:\"Object\";i:12;s:5:\"Forms\";i:13;s:4:\"Name\";i:14;s:19:\"XMLCommonAttributes\";i:15;s:4:\"Ruby\";i:16;s:6:\"Iframe\";}s:11:\"tidyModules\";a:5:{i:0;s:11:\"Tidy_Strict\";i:1;s:10:\"Tidy_XHTML\";i:2;s:16:\"Tidy_Proprietary\";i:3;s:11:\"Tidy_Strict\";i:4;s:9:\"Tidy_Name\";}s:3:\"xml\";b:1;s:7:\"aliases\";a:0:{}s:9:\"dtdPublic\";s:25:\"-//W3C//DTD XHTML 1.1//EN\";s:9:\"dtdSystem\";s:44:\"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\";}}s:10:\"\0*\0aliases\";N;}s:7:\"doctype\";N;s:9:\"attrTypes\";O:22:\"HTMLPurifier_AttrTypes\":1:{s:7:\"\0*\0info\";a:21:{s:4:\"Enum\";O:25:\"HTMLPurifier_AttrDef_Enum\":4:{s:12:\"valid_values\";a:0:{}s:17:\"\0*\0case_sensitive\";b:0;s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:4:\"Bool\";O:30:\"HTMLPurifier_AttrDef_HTML_Bool\":3:{s:7:\"\0*\0name\";b:0;s:9:\"minimized\";b:1;s:8:\"required\";b:0;}s:5:\"CDATA\";O:25:\"HTMLPurifier_AttrDef_Text\":2:{s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:2:\"ID\";O:28:\"HTMLPurifier_AttrDef_HTML_ID\":3:{s:11:\"\0*\0selector\";b:0;s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:6:\"Length\";O:32:\"HTMLPurifier_AttrDef_HTML_Length\":3:{s:6:\"\0*\0max\";N;s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:11:\"MultiLength\";O:37:\"HTMLPurifier_AttrDef_HTML_MultiLength\":3:{s:6:\"\0*\0max\";N;s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:8:\"NMTOKENS\";O:34:\"HTMLPurifier_AttrDef_HTML_Nmtokens\":2:{s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:6:\"Pixels\";O:32:\"HTMLPurifier_AttrDef_HTML_Pixels\":3:{s:6:\"\0*\0max\";N;s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:4:\"Text\";O:25:\"HTMLPurifier_AttrDef_Text\":2:{s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:3:\"URI\";O:24:\"HTMLPurifier_AttrDef_URI\":4:{s:9:\"\0*\0parser\";O:22:\"HTMLPurifier_URIParser\":1:{s:17:\"\0*\0percentEncoder\";O:27:\"HTMLPurifier_PercentEncoder\":1:{s:11:\"\0*\0preserve\";a:66:{i:48;b:1;i:49;b:1;i:50;b:1;i:51;b:1;i:52;b:1;i:53;b:1;i:54;b:1;i:55;b:1;i:56;b:1;i:57;b:1;i:65;b:1;i:66;b:1;i:67;b:1;i:68;b:1;i:69;b:1;i:70;b:1;i:71;b:1;i:72;b:1;i:73;b:1;i:74;b:1;i:75;b:1;i:76;b:1;i:77;b:1;i:78;b:1;i:79;b:1;i:80;b:1;i:81;b:1;i:82;b:1;i:83;b:1;i:84;b:1;i:85;b:1;i:86;b:1;i:87;b:1;i:88;b:1;i:89;b:1;i:90;b:1;i:97;b:1;i:98;b:1;i:99;b:1;i:100;b:1;i:101;b:1;i:102;b:1;i:103;b:1;i:104;b:1;i:105;b:1;i:106;b:1;i:107;b:1;i:108;b:1;i:109;b:1;i:110;b:1;i:111;b:1;i:112;b:1;i:113;b:1;i:114;b:1;i:115;b:1;i:116;b:1;i:117;b:1;i:118;b:1;i:119;b:1;i:120;b:1;i:121;b:1;i:122;b:1;i:45;b:1;i:46;b:1;i:95;b:1;i:126;b:1;}}}s:17:\"\0*\0embedsResource\";b:0;s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:12:\"LanguageCode\";O:25:\"HTMLPurifier_AttrDef_Lang\":2:{s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:5:\"Color\";O:31:\"HTMLPurifier_AttrDef_HTML_Color\":2:{s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:6:\"IAlign\";O:26:\"HTMLPurifier_AttrDef_Clone\":3:{s:8:\"\0*\0clone\";O:25:\"HTMLPurifier_AttrDef_Enum\":4:{s:12:\"valid_values\";a:5:{s:3:\"top\";i:0;s:6:\"middle\";i:1;s:6:\"bottom\";i:2;s:4:\"left\";i:3;s:5:\"right\";i:4;}s:17:\"\0*\0case_sensitive\";b:0;s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:6:\"LAlign\";O:26:\"HTMLPurifier_AttrDef_Clone\":3:{s:8:\"\0*\0clone\";O:25:\"HTMLPurifier_AttrDef_Enum\":4:{s:12:\"valid_values\";a:4:{s:3:\"top\";i:0;s:6:\"bottom\";i:1;s:4:\"left\";i:2;s:5:\"right\";i:3;}s:17:\"\0*\0case_sensitive\";b:0;s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:11:\"FrameTarget\";O:37:\"HTMLPurifier_AttrDef_HTML_FrameTarget\":4:{s:12:\"valid_values\";b:0;s:17:\"\0*\0case_sensitive\";b:0;s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:11:\"ContentType\";O:25:\"HTMLPurifier_AttrDef_Text\":2:{s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:12:\"ContentTypes\";O:25:\"HTMLPurifier_AttrDef_Text\":2:{s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:8:\"Charsets\";O:25:\"HTMLPurifier_AttrDef_Text\":2:{s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:9:\"Character\";O:25:\"HTMLPurifier_AttrDef_Text\":2:{s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:5:\"Class\";O:31:\"HTMLPurifier_AttrDef_HTML_Class\":2:{s:9:\"minimized\";b:0;s:8:\"required\";b:0;}s:6:\"Number\";O:28:\"HTMLPurifier_AttrDef_Integer\":5:{s:11:\"\0*\0negative\";b:0;s:7:\"\0*\0zero\";b:0;s:11:\"\0*\0positive\";b:1;s:9:\"minimized\";b:0;s:8:\"required\";b:0;}}}s:7:\"modules\";a:0:{}s:17:\"registeredModules\";a:0:{}s:11:\"userModules\";a:0:{}s:13:\"elementLookup\";a:0:{}s:8:\"prefixes\";a:1:{i:0;s:24:\"HTMLPurifier_HTMLModule_\";}s:11:\"contentSets\";N;s:15:\"attrCollections\";N;s:7:\"trusted\";b:0;}s:5:\"setup\";b:0;s:9:\"optimized\";b:0;}}s:12:\"\0*\0finalized\";b:1;s:8:\"\0*\0plist\";O:25:\"HTMLPurifier_PropertyList\":3:{s:7:\"\0*\0data\";a:5:{s:15:\"Attr.AllowedRel\";a:3:{s:8:\"nofollow\";b:1;s:11:\"imageviewer\";b:1;s:8:\"lightbox\";b:1;}s:15:\"HTML.SafeObject\";b:1;s:18:\"Output.FlashCompat\";b:1;s:14:\"HTML.SafeEmbed\";b:1;s:20:\"Cache.SerializerPath\";s:53:\"/Applications/MAMP/htdocs/159/app/cache/prod/purifier\";}s:9:\"\0*\0parent\";r:309;s:8:\"\0*\0cache\";N;}s:30:\"\0HTMLPurifier_Config\0aliasMode\";N;s:6:\"chatty\";b:1;s:25:\"\0HTMLPurifier_Config\0lock\";N;}\";'),
(138, 'ZConfig', 'useids', 'i:0;'),
(139, 'ZConfig', 'idsmail', 'i:0;'),
(140, 'ZConfig', 'idsrulepath', 's:70:\"system/SecurityCenterModule/Resources/config/phpids_zikula_default.xml\";'),
(141, 'ZConfig', 'idssoftblock', 'i:1;'),
(142, 'ZConfig', 'idsfilter', 's:3:\"xml\";'),
(143, 'ZConfig', 'idsimpactthresholdone', 'i:1;'),
(144, 'ZConfig', 'idsimpactthresholdtwo', 'i:10;'),
(145, 'ZConfig', 'idsimpactthresholdthree', 'i:25;'),
(146, 'ZConfig', 'idsimpactthresholdfour', 'i:75;'),
(147, 'ZConfig', 'idsimpactmode', 'i:1;'),
(148, 'ZConfig', 'idshtmlfields', 'a:1:{i:0;s:14:\"POST.__wysiwyg\";}'),
(149, 'ZConfig', 'idsjsonfields', 'a:1:{i:0;s:15:\"POST.__jsondata\";}'),
(150, 'ZConfig', 'idsexceptions', 'a:12:{i:0;s:10:\"GET.__utmz\";i:1;s:10:\"GET.__utmc\";i:2;s:18:\"REQUEST.linksorder\";i:3;s:15:\"POST.linksorder\";i:4;s:19:\"REQUEST.fullcontent\";i:5;s:16:\"POST.fullcontent\";i:6;s:22:\"REQUEST.summarycontent\";i:7;s:19:\"POST.summarycontent\";i:8;s:19:\"REQUEST.filter.page\";i:9;s:16:\"POST.filter.page\";i:10;s:20:\"REQUEST.filter.value\";i:11;s:17:\"POST.filter.value\";}'),
(151, 'ZConfig', 'outputfilter', 'i:1;'),
(152, 'ZConfig', 'htmlentities', 'i:1;'),
(153, 'ZConfig', 'AllowableHTML', 'a:110:{s:3:\"!--\";i:2;s:1:\"a\";i:2;s:4:\"abbr\";i:1;s:7:\"acronym\";i:1;s:7:\"address\";i:1;s:6:\"applet\";i:0;s:4:\"area\";i:0;s:7:\"article\";i:1;s:5:\"aside\";i:1;s:5:\"audio\";i:0;s:1:\"b\";i:1;s:4:\"base\";i:0;s:8:\"basefont\";i:0;s:3:\"bdo\";i:0;s:3:\"big\";i:0;s:10:\"blockquote\";i:2;s:2:\"br\";i:2;s:6:\"button\";i:0;s:6:\"canvas\";i:0;s:7:\"caption\";i:1;s:6:\"center\";i:2;s:4:\"cite\";i:1;s:4:\"code\";i:0;s:3:\"col\";i:1;s:8:\"colgroup\";i:1;s:7:\"command\";i:0;s:8:\"datalist\";i:0;s:2:\"dd\";i:1;s:3:\"del\";i:0;s:7:\"details\";i:1;s:3:\"dfn\";i:0;s:3:\"dir\";i:0;s:3:\"div\";i:2;s:2:\"dl\";i:1;s:2:\"dt\";i:1;s:2:\"em\";i:2;s:5:\"embed\";i:0;s:8:\"fieldset\";i:1;s:10:\"figcaption\";i:0;s:6:\"figure\";i:0;s:6:\"footer\";i:0;s:4:\"font\";i:0;s:4:\"form\";i:0;s:2:\"h1\";i:1;s:2:\"h2\";i:1;s:2:\"h3\";i:1;s:2:\"h4\";i:1;s:2:\"h5\";i:1;s:2:\"h6\";i:1;s:6:\"header\";i:0;s:6:\"hgroup\";i:0;s:2:\"hr\";i:2;s:1:\"i\";i:1;s:6:\"iframe\";i:0;s:3:\"img\";i:2;s:5:\"input\";i:0;s:3:\"ins\";i:0;s:6:\"keygen\";i:0;s:3:\"kbd\";i:0;s:5:\"label\";i:1;s:6:\"legend\";i:1;s:2:\"li\";i:2;s:3:\"map\";i:0;s:4:\"mark\";i:0;s:4:\"menu\";i:0;s:7:\"marquee\";i:0;s:5:\"meter\";i:0;s:3:\"nav\";i:0;s:4:\"nobr\";i:0;s:6:\"object\";i:0;s:2:\"ol\";i:2;s:8:\"optgroup\";i:0;s:6:\"option\";i:0;s:6:\"output\";i:0;s:1:\"p\";i:2;s:5:\"param\";i:0;s:3:\"pre\";i:2;s:8:\"progress\";i:0;s:1:\"q\";i:0;s:2:\"rp\";i:0;s:2:\"rt\";i:0;s:4:\"ruby\";i:0;s:1:\"s\";i:0;s:4:\"samp\";i:0;s:6:\"script\";i:0;s:7:\"section\";i:0;s:6:\"select\";i:0;s:5:\"small\";i:0;s:6:\"source\";i:0;s:4:\"span\";i:2;s:6:\"strike\";i:0;s:6:\"strong\";i:2;s:3:\"sub\";i:1;s:7:\"summary\";i:1;s:3:\"sup\";i:0;s:5:\"table\";i:2;s:5:\"tbody\";i:1;s:2:\"td\";i:2;s:8:\"textarea\";i:0;s:5:\"tfoot\";i:1;s:2:\"th\";i:2;s:5:\"thead\";i:0;s:4:\"time\";i:0;s:2:\"tr\";i:2;s:2:\"tt\";i:2;s:1:\"u\";i:0;s:2:\"ul\";i:2;s:3:\"var\";i:0;s:5:\"video\";i:0;s:3:\"wbr\";i:0;}'),
(154, 'ZikulaMailerModule', 'charset', 's:5:\"UTF-8\";'),
(155, 'ZikulaMailerModule', 'encoding', 's:4:\"8bit\";'),
(156, 'ZikulaMailerModule', 'html', 'b:0;'),
(157, 'ZikulaMailerModule', 'wordwrap', 'i:50;'),
(158, 'ZikulaMailerModule', 'enableLogging', 'b:0;'),
(159, 'ZikulaSearchModule', 'itemsperpage', 'i:10;'),
(160, 'ZikulaSearchModule', 'limitsummary', 'i:255;'),
(161, 'ZikulaSearchModule', 'opensearch_enabled', 'b:1;'),
(162, 'ZikulaSearchModule', 'opensearch_adult_content', 'b:0;'),
(163, 'ZikulaRoutesModule', 'routeEntriesPerPage', 'i:10;'),
(164, 'ZConfig', 'system_identifier', 's:32:\"42101343475e0a555b848b8911583339\";'),
(165, 'systemplugin.imagine', 'version', 's:5:\"0.6.2\";'),
(166, 'systemplugin.imagine', 'thumb_dir', 's:20:\"systemplugin.imagine\";'),
(167, 'systemplugin.imagine', 'thumb_auto_cleanup', 'b:0;'),
(168, 'systemplugin.imagine', 'thumb_auto_cleanup_period', 's:3:\"P1D\";'),
(169, 'systemplugin.imagine', 'presets', 'a:1:{s:7:\"default\";C:27:\"SystemPlugin_Imagine_Preset\":266:{x:i:2;a:8:{s:5:\"width\";i:100;s:6:\"height\";i:100;s:4:\"mode\";s:5:\"inset\";s:9:\"extension\";N;s:7:\"options\";a:2:{s:12:\"jpeg_quality\";i:75;s:21:\"png_compression_level\";i:7;}s:8:\"__module\";N;s:9:\"__imagine\";N;s:16:\"__transformation\";N;};m:a:1:{s:7:\"\0*\0name\";s:7:\"default\";}}}'),
(170, 'ZikulaBootstrapTheme', 'home', 's:18:\"3col_w_centerblock\";'),
(171, 'ZikulaBootstrapTheme', 'master', 's:4:\"2col\";'),
(172, 'ZikulaBootstrapTheme', 'admin', 's:4:\"1col\";');

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
  `cr_date` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `cr_uid` int(11) NOT NULL,
  `lu_date` datetime NOT NULL COMMENT '(DC2Type:datetime)',
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
  `cr_date` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `cr_uid` int(11) NOT NULL,
  `lu_date` datetime NOT NULL COMMENT '(DC2Type:datetime)',
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
  `dc_startdate` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  `dc_enddate` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
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
  `cr_date` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `cr_uid` int(11) NOT NULL,
  `lu_date` datetime NOT NULL COMMENT '(DC2Type:datetime)',
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
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime)',
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
  `created` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  `found` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
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
  `lastused` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `uid` int(11) NOT NULL,
  `remember` smallint(6) NOT NULL,
  `vars` longtext COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id`, `name`, `type`, `displayname`, `description`, `directory`, `version`, `contact`, `admin`, `user`, `system`, `state`, `xhtml`) VALUES
(1, 'ZikulaRssTheme', 3, 'Rss', 'The RSS theme is an auxiliary theme designed specially for outputting pages as an RSS feed.', 'RssTheme', '2.0.0', '3', 0, 1, 0, 1, 1),
(2, 'ZikulaSeaBreezeTheme', 3, 'SeaBreeze', 'The SeaBreeze theme is a browser-oriented theme.', 'zikula/seabreeze-theme', '4.0.3', '3', 1, 1, 0, 1, 1),
(3, 'ZikulaAndreas08Theme', 3, 'Andreas08', 'Based on the theme Andreas08 by Andreas Viklund', 'zikula/andreas08-theme', '2.0.3', '3', 1, 1, 0, 1, 1),
(4, 'ZikulaPrinterTheme', 3, 'Printer', 'The Printer theme is an auxiliary theme designed specially for outputting pages in a printer-friendly format.', 'PrinterTheme', '3.0.0', '3', 0, 1, 0, 1, 1),
(5, 'ZikulaAtomTheme', 3, 'Atom', 'The Atom theme is an auxiliary theme specially designed for rendering pages in Atom mark-up.', 'AtomTheme', '2.0.0', '3', 0, 1, 0, 1, 1),
(6, 'ZikulaBootstrapTheme', 3, 'Bootstrap', 'Bootstrap based theme', 'BootstrapTheme', '1.0.0', '3', 1, 1, 0, 1, 1);

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
  `approved_date` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `approved_by` int(11) NOT NULL,
  `user_regdate` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `lastlogin` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `theme` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tz` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `locale` varchar(5) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uid`, `uname`, `email`, `pass`, `activated`, `approved_date`, `approved_by`, `user_regdate`, `lastlogin`, `theme`, `tz`, `locale`) VALUES
(1, 'guest', '', '', 1, '1970-01-01 05:00:00', 2, '1970-01-01 05:00:00', '1970-01-01 05:00:00', '', '', ''),
(2, 'admin', 'admin@example.com', '', 1, '2019-12-30 19:51:34', 2, '2019-12-30 19:51:53', '2019-12-30 19:51:54', '', '', '');

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
  `created_dt` datetime NOT NULL COMMENT '(DC2Type:datetime)'
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `zauth_authentication_mapping`
--

INSERT INTO `zauth_authentication_mapping` (`id`, `method`, `uid`, `uname`, `email`, `verifiedEmail`, `pass`) VALUES
(1, 'native_uname', 2, 'admin', 'admin@example.com', 1, '8$*y(b=$6cc812a7635d64d4ad0d9f915b3daa3446ffce1d93de1be361594c0b9934a8d6');

-- --------------------------------------------------------

--
-- Table structure for table `zikula_routes_route`
--

CREATE TABLE `zikula_routes_route` (
  `id` int(11) NOT NULL,
  `workflowState` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
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
  `options` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `route_condition` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort` int(11) NOT NULL,
  `createdDate` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `updatedDate` datetime NOT NULL COMMENT '(DC2Type:datetime)',
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
  ADD KEY `IDX_D0B2B0F8A977936C` (`tree_root`),
  ADD KEY `IDX_D0B2B0F8727ACA70` (`parent_id`),
  ADD KEY `IDX_D0B2B0F88304AF18` (`cr_uid`),
  ADD KEY `IDX_D0B2B0F8C072C1DD` (`lu_uid`),
  ADD KEY `idx_categories_is_leaf` (`is_leaf`),
  ADD KEY `idx_categories_name` (`name`),
  ADD KEY `idx_categories_status` (`status`);

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
  ADD KEY `IDX_1B56B43312469DE2` (`category_id`),
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
  MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `admin_module`
--
ALTER TABLE `admin_module`
  MODIFY `amid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `blocks`
--
ALTER TABLE `blocks`
  MODIFY `bid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `block_positions`
--
ALTER TABLE `block_positions`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `bundles`
--
ALTER TABLE `bundles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `categories_category`
--
ALTER TABLE `categories_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10000;

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
  MODIFY `gid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `group_applications`
--
ALTER TABLE `group_applications`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `group_perms`
--
ALTER TABLE `group_perms`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `hook_area`
--
ALTER TABLE `hook_area`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `module_deps`
--
ALTER TABLE `module_deps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `module_vars`
--
ALTER TABLE `module_vars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  ADD CONSTRAINT `FK_D0B2B0F8727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `categories_category` (`id`),
  ADD CONSTRAINT `FK_D0B2B0F8A977936C` FOREIGN KEY (`tree_root`) REFERENCES `categories_category` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories_registry`
--
ALTER TABLE `categories_registry`
  ADD CONSTRAINT `FK_1B56B43312469DE2` FOREIGN KEY (`category_id`) REFERENCES `categories_category` (`id`);

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
