<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Installer
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('default_charset', 'UTF-8');
mb_regex_encoding('UTF-8');
ini_set('memory_limit', '64M');
ini_set('max_execution_time', 86400);

include 'lib/bootstrap.php';
$eventManager = $core->getEventManager();
$eventManager->attach('core.init', 'upgrade_suppressErrors');

// load zikula core
define('_ZINSTALLVER', Zikula_Core::VERSION_NUM);
define('_Z_MINUPGVER', '1.2.0');

// include config file for retrieving name of temporary directory
$GLOBALS['ZConfig']['System']['multilingual'] = true;

$GLOBALS['ZConfig']['System']['Z_CONFIG_USE_OBJECT_ATTRIBUTION'] = false;
$GLOBALS['ZConfig']['System']['Z_CONFIG_USE_OBJECT_LOGGING'] = false;
$GLOBALS['ZConfig']['System']['Z_CONFIG_USE_OBJECT_META'] = false;



// Lazy load DB connection to avoid testing DSNs that are not yet valid (e.g. no DB created yet)
$dbEvent = new Zikula_Event('doctrine.init_connection', null, array('lazy' => true));
$connection = $eventManager->notify($dbEvent)->getData();

$columns = upgrade_getColumnsForTable($connection, 'modules');

if (in_array('pn_id', array_keys($columns))) {
    upgrade_columns($connection);
}

if (!isset($columns['capabilities'])) {
    ModUtil::dbInfoLoad('Extensions', 'Extensions');
    DBUtil::changeTable('modules');
    ModUtil::dbInfoLoad('Blocks', 'Blocks');
    DBUtil::changeTable('blocks');
}

$_SESSION['_ZikulaUpgrader'] = array();
$installedVersion = upgrade_getCurrentInstalledCoreVersion($connection);

if (version_compare($installedVersion, '1.3.0-dev') === -1) {
    $_SESSION['_ZikulaUpgrader']['_ZikulaUpgradeFrom12x'] = true;
}

$core->init(Zikula_Core::STAGE_ALL);

$action = FormUtil::getPassedValue('action', false, 'GETPOST');

// login to supplied admin credentials for action the following actions
if ($action === 'upgrademodules' || $action === 'convertdb' || $action === 'sanitycheck') {
    $username = FormUtil::getPassedValue('username', null, 'POST');
    $password = FormUtil::getPassedValue('password', null, 'POST');

    if (!UserUtil::loginUsing('Users', array('loginid' => $username, 'pass' => $password))) {
        // force action to login
        $action = 'login';
    } else {
        define('_ZINSTALLEDVERSION', $installedVersion);
    }
}

switch ($action) {
    case 'upgradeinit':
        _upg_upgradeinit();
        break;
    case 'login':
        _upg_login(true);
        break;
    case 'sanitycheck':
        _upg_sanity_check($username, $password);
        break;
    case 'upgrademodules':
        _upg_upgrademodules($username, $password);
        break;
    default:
        _upg_selectlanguage();
        break;
}

/**
 * Generate the header of upgrade page.
 *
 * This function generate the header of upgrade page.
 *
 * @return void
 */
function _upg_header()
{
    $lang = ZLanguage::getLanguageCode();
    $charset = ZLanguage::getEncoding();
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . "\n";
    echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . $lang . '">' . "\n";
    echo '<head>' . "\n";
    echo '<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '" />' . "\n";
    echo '<title>' . __('Zikula Upgrade script') . "</title>\n";
    echo '<link rel="stylesheet" href="install/style/upgrade.css" type="text/css" />' . "\n";
    echo '<link rel="stylesheet" href="styles/core.css" type="text/css" />' . "\n";
    echo '<!--[if IE]><link rel="stylesheet" type="text/css" href="styles/core_iehacks.css" media="print,projection,screen" /><![endif]-->' . "\n";
    echo '</head>' . "\n";
    echo '<body>' . "\n";
    echo '<div id="container"><div id="wrapper" class="z-clearfix">' . "\n";
    echo '<div id="header" class="z-clearfix">' . "\n";
    echo '<div id="headertopleft"><img src="install/images/top1.jpg" alt="" /></div>' . "\n";
    echo '<div id="headertopright"><img src="install/images/top2.jpg" alt="" /></div>' . "\n";
    echo '</div>' . "\n";
    echo '<div class="menu">' . "\n";
    echo '<p id="notice">' . __('For more information about the upgrade process, please read the <a href="docs/\' . $lang . \'/UPGRADING">upgrade documentation</a>, visit our <a href="http://community.zikula.org/Wiki.htm">wiki</a> or the <a href="http://community.zikula.org/module-Forum.htm">support forum</a>.') . '</p>';
    echo '</div>';
    echo '<div id="content">';
    if (UserUtil::isLoggedIn()) {
        echo '<h1>' . __f('Zikula version %1$s Upgrade script (for Zikula version %2$s and up)', array(_ZINSTALLVER, _Z_MINUPGVER)) . '</h1>' . "\n";
        echo '<p>' . __f('This script will upgrade any Zikula v%1$s+ installation to v%2$s. Upgrades from less than Zikula v%1$s are not supported by this script.', array(_Z_MINUPGVER, _ZINSTALLVER)) . "</p>\n";
    }
}

/**
 * Generate the footer of upgrade page.
 *
 * This function generate the footer of upgrade page.
 *
 * @return void
 */
function _upg_footer()
{
    echo '</div></div></div>' . "\n";
    echo '</body>' . "\n";
    echo '</html>';
    exit();
}

/**
 * Generate the language selector page.
 *
 * This function generate the language selector page.
 *
 * @return void
 */
function _upg_selectlanguage()
{
    _upg_header();
    $validupgrade = true;
    if (!$GLOBALS['ZConfig']['System']['installed']) {
        $validupgrade = false;
        echo '<h2>' . __('FATAL ERROR!') . "</h2>\n";
        echo '<p class="z-errormsg">' . __("Zikula does not appear to be installed.") . "</p>\n";
    }

    if (!$validupgrade) {
        _upg_footer();
        System::shutdown();
    }

    $curlang = ZLanguage::getLanguageCode();
    echo '<form class="z-form" action="upgrade.php" method="get" enctype="application/x-www-form-urlencoded">' . "\n";
    echo '<fieldset><legend>' . __('Please select your language') . '</legend>' . "\n";
    echo '<input type="hidden" name="action" value="upgradeinit" />' . "\n";
    echo '<div class="z-formrow"><label for="lang">' . __('Choose a language') . '</label>' . "\n";
    echo '<select id="lang" name="lang">' . "\n";
    $langs = ZLanguage::getInstalledLanguageNames();
    foreach ($langs as $lang => $name) {
        $selected = ($lang == $curlang ? ' selected="selected"' : '');
        echo '<option value="' . $lang . '" label="' . $name . '"' . $selected . '>' . $name . "</option>\n";
    }
    echo '</select></div></fieldset>' . "\n";
    echo '<div class="z-formbuttons"><input type="submit" value="' . __('Submit') . '" /></div>' . "\n";
    echo '</form>' . "\n";
    _upg_footer();
}

/**
 * Upgrade initial.
 *
 * Display initial upgrade page.
 *
 * @return void
 */
function _upg_upgradeinit()
{
    _upg_header();

    echo '<h2>' . __('BEFORE proceeding you should backup your database!') . '</h2>' . "\n";
    _upg_login(false);

    _upg_footer();
}

/**
 * Generate the login bloc of login page.
 *
 * This function generate the authentification part of login page.
 *
 * @param boolean $showheader Show header and footer.
 *
 * @return void
 */
function _upg_login($showheader = true)
{
    $lang = ZLanguage::getLanguageCode();
    if ($showheader == true) {
        _upg_header();
        echo '<p class="z-errormsg">' . __('Failed to login to your site') . '</p>' . "\n";
    }
    echo '<p>' . __('For the next upgrade steps you need to be logged in. Please provide your admin account credentials') . '</p>' . "\n";
    echo '<form class="z-form" action="upgrade.php?lang=' . $lang . '" method="post" enctype="application/x-www-form-urlencoded">' . "\n";
    echo '<fieldset><legend>' . __('Log-in') . '</legend>' . "\n";
    echo '<div class="z-formrow"><label for="username">' . __('User name') . '</label><input id="username" type="text" name="username" size="60" maxlength="25" /></div>' . "\n";
    echo '<div class="z-formrow"><label for="password">' . __('Password') . '</label><input id="password" type="password" name="password" size="25" maxlength="40" /></div>' . "\n";
    echo '<input type="hidden" name="action" value="sanitycheck" />' . "\n";

    if ($lang != null) {
        echo '<input type="hidden" name="lang" value="' . htmlspecialchars($lang) . '" />' . "\n";
    }
    echo '</fieldset>' . "\n";
    echo '<div class="z-formbuttons"><input name="submit" type="submit" value="' . __('Submit') . '" /></div>' . "\n";
    echo '</form>' . "\n";
    if ($showheader == true) {
        _upg_footer();
    }
}

/**
 * Generate the upgrade module page.
 *
 * This function upgrade available module to an upgrade
 *
 * @param string $username Username of the admin user.
 * @param string $password Password of the admin user.
 *
 * @return void
 */
function _upg_upgrademodules($username, $password)
{
    _upg_header();

    $modvars = DBUtil::selectObjectArray('module_vars');
    foreach ($modvars as $modvar) {
        if ($modvar['value'] == '0' || $modvar['value'] == '1') {
            $modvar['value'] = serialize($modvar['value']);
            DBUtil::updateObject($modvar, 'module_vars');
        }
    }

    // force load the modules admin API
    ModUtil::loadApi('Extensions', 'admin', true);

    echo '<h2>' . __('Starting upgrade') . '</h2>' . "\n";
    echo '<ul id="upgradelist" class="check">' . "\n";

    // reset for User module
    //$_SESSION['_ZikulaUpgrader']['_ZikulaUpgradeFrom12x'] = false;

    $results = ModUtil::apiFunc('Extensions', 'admin', 'upgradeall');
    if ($results) {
        foreach ($results as $modname => $result) {
            if ($result) {
                echo '<li class="passed">' . DataUtil::formatForDisplay($modname) . ' ' . __('upgraded') . '</li>' . "\n";
            } else {
                echo '<li class="failed">' . DataUtil::formatForDisplay($modname) . ' ' . __('not upgraded') . '</li>' . "\n";
            }
        }
    }
    echo '</ul>' . "\n";
    if (!$results) {
        echo '<ul class="check"><li class="passed">' . __('No modules required upgrading') . '</li></ul>';
    }

    // wipe out the deprecated moduels from Modules list.
    $modTable = DBUtil::getLimitedTablename('modules');
    $sql = "DELETE FROM $modTable WHERE z_name = 'Header_Footer' OR z_name = 'AuthPN' OR z_name = 'pnForm' OR z_name = 'Workflow' OR z_name = 'pnRender' OR z_name = 'Admin_Messages'";
    DBUtil::executeSQL($sql);

    // regenerate the themes list
    ModUtil::apiFunc('Theme', 'admin', 'regenerate');

    // store the recent version in a config var for later usage. This enables us to determine the version we are upgrading from
    System::setVar('Version_Num', Zikula_Core::VERSION_NUM);
    System::setVar('language_i18n', ZLanguage::getLanguageCode());

    // Relogin the admin user to give a proper admin link
    SessionUtil::requireSession();

    echo '<p class="z-statusmsg">' . __('Finished upgrade') . " - \n";
    if (!UserUtil::loginUsing('Users', array('loginid' => $username, 'pass' => $password))) {
        $url = sprintf('<a href="%s">%s</a>', DataUtil::formatForDisplay(System::getBaseUrl()), DataUtil::formatForDisplay(System::getVar('sitename')));
        echo __f('Go to the startpage for %s', $url);
    } else {
        upgrade_clear_caches();
        $url = sprintf('<a href="%s">%s</a>', ModUtil::url('Admin', 'admin', 'adminpanel'), DataUtil::formatForDisplay(System::getVar('sitename')));
        echo __f('Go to the admin panel for %s', $url);
    }
    echo "</p>\n";

    _upg_footer();
}

/**
 * Generate the button for the next step of the upgrade.
 *
 * This function generate the button to allow users to go to the next step of the upgrade.
 *
 * @param string $action   Name of the next function called (without upgrade_upg_).
 * @param string $text     Text printed.
 * @param string $username Username of the admin user.
 * @param string $password Password of the admin user.
 *
 * @return void
 */
function _upg_continue($action, $text, $username, $password)
{
    $lang = ZLanguage::getLanguageCode();
    echo '<form class="z-form z-linear" method="post" action="upgrade.php?lang=' . $lang . "\">\n";
    echo '<div><fieldset><legend>' . DataUtil::formatForDisplay($text) . '</legend>' . "\n";
    if ($username != null && $password != null) {
        echo '<input type="hidden" name="username" value="' . DataUtil::formatForDisplay($username) . '" />' . "\n";
        echo '<input type="hidden" name="password" value="' . DataUtil::formatForDisplay($password) . '" />' . "\n";
    }
    echo '<input type="hidden" name="action" value="' . htmlspecialchars($action) . '" />' . "\n";
    echo '<div class="z-formbuttons"><input type="submit" name="submit" value="' . htmlspecialchars($text) . '" /></div>' . "\n";
    echo '</fieldset></div>' . "\n";
    echo '</form>' . "\n";
    return;
}

/**
 * Generate the sanity check page.
 *
 * This function do and generate the stanity check page.
 *
 * @param string $username Username of the admin user.
 * @param string $password Password of the admin user.
 *
 * @return void
 */
function _upg_sanity_check($username, $password)
{
    _upg_header();

    $validupgrade = true;
    if (version_compare(_ZINSTALLEDVERSION, _ZINSTALLVER, '=')) {
        // Already installed the correct version
        $validupgrade = false;
        echo '<h2>' . __('Already up to date') . "</h2>\n";
        echo '<p class="z-errormsg">' . __f("It seems that you have already installed version %s. Please remove this upgrade script, you do not need it anymore.", _ZINSTALLEDVERSION) . "</p>\n";
    } elseif (version_compare(_ZINSTALLEDVERSION, _Z_MINUPGVER, '<')) {
        // Not on version _Z_MINUPGVER yet
        $validupgrade = false;
        echo '<h2>' . __('Possible incompatible version found.') . "</h2>\n";
        echo '<p class="z-warningmsg">' . __f('The current installed version of Zikula is reporting (%1$s). You must upgrade to version (%2$s) before you can use this upgrade.', array(_ZINSTALLEDVERSION, _Z_MINUPGVER)) . "</p>\n";
    } elseif (version_compare(PHP_VERSION, '5.3.0', '>=')) {
        if (ini_get('date.timezone') == '') {
            echo '<p class="z-errormsg"><strong>' . __('date.timezone is currently not set. Since PHP 5.3.0, it needs to be set to a valid timezone in your php.ini such as timezone like UTC, GMT+5, Europe/Berlin.') . "</strong></p>\n";
            echo _upg_continue('sanitycheck', __('Check again'), $username, $password);
            $validupgrade = false;
        }
    }

    if ($validupgrade) {
        $pcreUnicodePropertiesEnabled = @preg_match('/^\p{L}+$/u', 'TheseAreLetters');
        if (!isset($pcreUnicodePropertiesEnabled) || !$pcreUnicodePropertiesEnabled) {
            // PCRE Unicode property support is not enabled.
            $validupgrade = false;
            echo '<h2>' . __('PCRE Unicode Property Support Needed.') . "</h2>\n";
            echo '<p class="z-errormsg">' . __('The PCRE (Perl Compatible Regular Expression) library being used with your PHP installation does not support Unicode properties. This is required to handle multi-byte character sets in regular expressions. The PCRE library used must be compiled with the \'--enable-unicode-properties\' option.') . "</p>\n";
        }
    }

    if ($validupgrade) {
        $defaultTheme = System::getVar('Default_Theme');
        $dir = is_dir("themes/$defaultTheme");
        $casing = preg_match('/\p{Lu}/u', substr($defaultTheme, 0, 1)); // first letter is uppercase.
        $underscore = preg_match('/_/', $defaultTheme); // has underscore
        if (!$dir || !$casing || $underscore) {
            // The default theme must be installed!
            $validupgrade = false;
            echo '<h2>' . __f("Theme Check Failed", $defaultTheme) . "</h2>\n";
            if (!$dir) {
                echo '<p class="z-errormsg">' . __f("Your configuration specifies a theme called '%s' that doesn't exist.  Please ensure that theme exists in themes/%s", array($defaultTheme, $defaultTheme)) . "</p>\n";
            }
            if (!$casing) {
                echo '<p class="z-errormsg">' . __f("Your configuration specifies a theme called '%s' which begins with a lower case letter.  You must first upgrade the theme's name to start with a capital letter.  This should be done in your 1.2.x installation before attempting this upgrade again.", array($defaultTheme, $defaultTheme)) . "</p>\n";
            }
            if ($underscore) {
                echo '<p class="z-errormsg">' . __f("Your theme called '%s' contains an underscore, this is now deprecated.  You must first upgrade the theme's name so it does not contain any underscore character.  This should be done in your 1.2.x installation before attempting this upgrade again.", array($defaultTheme, $defaultTheme)) . "</p>\n";
            }
        }

    }

    if (!$validupgrade) {
        _upg_footer();
        System::shutdown();
    }

    _upg_continue('upgrademodules', __('Proceed to upgrade (click once and wait)'), $username, $password);
    _upg_footer();
}

/**
 * Clear the Zikula cache.
 *
 * This function clear the zikula cache.
 *
 * @return void
 */
function upgrade_clear_caches()
{
    Zikula_View_Theme::getInstance()->clear_all_cache();
    Zikula_View_Theme::getInstance()->clear_compiled();
    Zikula_View_Theme::getInstance()->clear_cssjscombinecache();
    Zikula_View::getInstance()->clear_all_cache();
    Zikula_View::getInstance()->clear_compiled();
}

/**
 * Suppress errors event listener.
 *
 * @param Zikula_Event $event Event.
 *
 * @return void
 */
function upgrade_suppressErrors(Zikula_Event $event)
{
    if (!$event['stage'] == Zikula_Core::STAGE_CONFIG) {
        return;
    }

    error_reporting(~E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);
    $GLOBALS['ZConfig']['System']['development'] = 0;
}

/**
 * Get current intalled version number
 *
 * @param object $connection PDO connection.
 *
 * @return string
 */
function upgrade_getCurrentInstalledCoreVersion($connection)
{
    $prefix = $GLOBALS['ZConfig']['System']['prefix'];
    $moduleTable = $prefix . '_module_vars';

    $stmt = $connection->prepare("SELECT z_value FROM $moduleTable WHERE z_modname = 'ZConfig' AND z_name = 'Version_Num'");
    if (!$stmt->execute()) {
        die(__('FATAL ERROR: Cannot start, unable to determine installed Core version.'));
    }

    $result = $stmt->fetch(PDO::FETCH_NUM);
    return unserialize($result[0]);
}

/**
 * Get tables in database from current connection.
 *
 * @param object $connection PDO connection.
 *
 * @return array
 */
function upgrade_getTables($connection)
{
    $tables = $connection->import->listTables();
    if (!$tables) {
        die(__('FATAL ERROR: Cannot start, unable to determine installed Core version.'));
    }

    $prefixLen = strlen($GLOBALS['ZConfig']['System']['prefix'] . '_');
    foreach ($tables as $key => $value) {
        $tables[$key] = substr($value, $prefixLen, strlen($value));
    }
    return $tables;
}

/**
 * Get tables in database from current connection.
 *
 * @param object $connection PDO connection.
 * @param string $tableName  The name of the table.
 *
 * @return array
 */
function upgrade_getColumnsForTable($connection, $tableName)
{
    $tables = $connection->import->listTables();
    if (!$tables) {
        die(__('FATAL ERROR: Cannot start, unable access database.'));
    }

    try {
        return $connection->import->listTableColumns($GLOBALS['ZConfig']['System']['prefix'] . "_$tableName");
    } catch (Exception $e) {
        // TODO - do something with the exception here?
    }
}

/**
 * Standardise table columns.
 *
 * @param PDOConnection $connection The PDO connection instance.
 *
 * @return void
 */
function upgrade_columns($connection)
{
    $prefix = $GLOBALS['ZConfig']['System']['prefix'];
    $commands = array();
    $commands[] = "ALTER TABLE {$prefix}_admin_category CHANGE pn_cid z_cid INT(11) NOT NULL AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_admin_category CHANGE pn_name z_name VARCHAR(32) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_admin_category CHANGE pn_description z_description VARCHAR(254) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_admin_module CHANGE pn_amid z_amid INT(11) NOT NULL AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_admin_module CHANGE pn_mid z_mid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_admin_module CHANGE pn_cid z_cid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_bid z_bid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_bkey z_bkey VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_title z_title VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_content z_content LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_url z_url LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_mid z_mid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_filter z_filter LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_active z_active TINYINT DEFAULT '1' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_collapsable z_collapsable INT(11) DEFAULT '1' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_defaultstate z_defaultstate INT(11) DEFAULT '1' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_refresh z_refresh INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_last_update z_last_update DATETIME NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_language z_language VARCHAR(30) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_userblocks CHANGE pn_uid z_uid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_userblocks CHANGE pn_bid z_bid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_userblocks CHANGE pn_active z_active TINYINT DEFAULT '1' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_userblocks CHANGE pn_last_update z_last_update DATETIME";
    $commands[] = "ALTER TABLE {$prefix}_block_positions CHANGE pn_pid z_pid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_block_positions CHANGE pn_name z_name VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_block_positions CHANGE pn_description z_description VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_block_placements CHANGE pn_pid z_pid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_block_placements CHANGE pn_bid z_bid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_block_placements CHANGE pn_order z_order INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_membership CHANGE pn_gid z_gid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_membership CHANGE pn_uid z_uid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_gid z_gid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_name z_name VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_gtype z_gtype TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_description z_description VARCHAR(200) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_prefix z_prefix VARCHAR(25) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_state z_state TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_nbuser z_nbuser INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_nbumax z_nbumax INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_link z_link INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_uidmaster z_uidmaster INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE pn_app_id z_app_id INT(11) NOT NULL AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE pn_uid z_uid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE pn_gid z_gid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE pn_application z_application LONGBLOB NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE pn_status z_status TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_id z_id BIGINT AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_object z_object VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_action z_action VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_smodule z_smodule VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_stype z_stype VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_tarea z_tarea VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_tmodule z_tmodule VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_ttype z_ttype VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_tfunc z_tfunc VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_sequence z_sequence INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_id z_id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_name z_name VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_type z_type TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_displayname z_displayname VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_url z_url VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_description z_description VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_regid z_regid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_directory z_directory VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_version z_version VARCHAR(10) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_official z_official TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_author z_author VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_contact z_contact VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_admin_capable z_admin_capable TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_user_capable z_user_capable TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_profile_capable z_profile_capable TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_message_capable z_message_capable TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_state z_state SMALLINT(6) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_credits z_credits VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_changelog z_changelog VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_help z_help VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_license z_license VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_securityschema z_securityschema TEXT NOT NULL";
    $commands[] = "UPDATE {$prefix}_modules SET z_name = 'Extensions', z_displayname = 'Extensions manager', z_url = 'extensions', z_description = 'Manage your modules and plugins.', z_directory =  'Extensions', z_securityschema = 'a:1:{s:9:\"Extensions::\";s:2:\"::\";}' WHERE {$prefix}_modules.z_id = 1 AND {$prefix}_modules.z_name = 'Modules'";
    $commands[] = "ALTER TABLE {$prefix}_module_vars CHANGE pn_id z_id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_module_vars CHANGE pn_modname z_modname VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_vars CHANGE pn_name z_name VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_vars CHANGE pn_value z_value LONGTEXT";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE pn_id z_id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE pn_modid z_modid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE pn_modname z_modname VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE pn_minversion z_minversion VARCHAR(10) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE pn_maxversion z_maxversion VARCHAR(10) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE pn_status z_status TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_pid z_pid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_gid z_gid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_sequence z_sequence INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_realm z_realm INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_component z_component VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_instance z_instance VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_level z_level INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_bond z_bond INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_search_stat CHANGE pn_id z_id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_search_stat CHANGE pn_search z_search VARCHAR(50) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_search_stat CHANGE pn_count z_count INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_search_stat CHANGE pn_date z_date DATE";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_id z_id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_title z_title VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_text z_text LONGTEXT";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_module z_module VARCHAR(100)";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_extra z_extra VARCHAR(100)";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_created z_created DATETIME";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_found z_found DATETIME";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_sesid z_sesid VARCHAR(50)";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_id z_id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_name z_name VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_type z_type TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_displayname z_displayname VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_description z_description VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_regid z_regid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_directory z_directory VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_version z_version VARCHAR(10) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_official z_official TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_author z_author VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_contact z_contact VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_admin z_admin TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_user z_user TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_system z_system TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_state z_state TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_credits z_credits VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_changelog z_changelog VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_help z_help VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_license z_license VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_xhtml z_xhtml TINYINT DEFAULT '1' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_uid z_uid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_uname z_uname VARCHAR(25) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_email z_email VARCHAR(60) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_user_regdate z_user_regdate DATETIME DEFAULT '1970-01-01 00:00:00' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_user_viewemail z_user_viewemail SMALLINT DEFAULT '0'";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_user_theme z_user_theme VARCHAR(64)";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_pass z_pass VARCHAR(128) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_storynum z_storynum INT DEFAULT '10' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_ublockon z_ublockon TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_ublock z_ublock TEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_theme z_theme VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_counter z_counter INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_activated z_activated TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_lastlogin z_lastlogin DATETIME DEFAULT '1970-01-01 00:00:00' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_validfrom z_validfrom INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_validuntil z_validuntil INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_hash_method z_hash_method TINYINT(4) DEFAULT '8' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_tid z_tid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_uname z_uname VARCHAR(25) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_email z_email VARCHAR(60) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_femail z_femail TINYINT(4) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_pass z_pass VARCHAR(128) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_dynamics z_dynamics LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_comment z_comment VARCHAR(254) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_type z_type TINYINT(4) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_tag z_tag TINYINT(4) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE pn_sessid z_sessid VARCHAR(40) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE pn_ipaddr z_ipaddr VARCHAR(32) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE pn_lastused z_lastused DATETIME DEFAULT '1970-01-01 00:00:00'";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE pn_uid z_uid INT(11) DEFAULT '0'";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE pn_remember z_remember TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE pn_vars z_vars LONGTEXT NOT NULL";
    $commands[] = "UPDATE {$prefix}_module_vars SET z_modname = 'ZConfig' WHERE z_modname = '/PNConfig'";
    $silentCommands = array();
    $silentCommands[] = "ALTER TABLE {$prefix}_message CHANGE pn_mid z_mid INT(11) NOT NULL AUTO_INCREMENT ,
CHANGE pn_title z_title VARCHAR(100) NOT NULL DEFAULT  '',
CHANGE pn_content z_content LONGTEXT NOT NULL ,
CHANGE pn_date z_date INT(11) NOT NULL DEFAULT  '0',
CHANGE pn_expire z_expire INT(11) NOT NULL DEFAULT  '0',
CHANGE pn_active z_active INT(11) NOT NULL DEFAULT  '1',
CHANGE pn_view z_view INT(11) NOT NULL DEFAULT  '1',
CHANGE pn_language z_language VARCHAR(30) NOT NULL DEFAULT  ''";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE plock_id z_plock_id INT(11) NOT NULL AUTO_INCREMENT";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE plock_name z_plock_name VARCHAR(100) NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE plock_cdate z_plock_cdate DATETIME NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE plock_edate z_plock_edate DATETIME NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE plock_session z_plock_session VARCHAR(50) NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE plock_title z_plock_title VARCHAR(100) NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE plock_ipno z_plock_ipno VARCHAR(30) NOT NULL";

    foreach ($commands as $sql) {
        $stmt = $connection->prepare($sql);
        $stmt->execute();
    }

    foreach ($silentCommands as $sql) {
        $stmt = $connection->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            // Silent - trap and toss exceptions.
        }
    }
}
