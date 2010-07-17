<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('default_charset', 'UTF-8');
mb_regex_encoding('UTF-8');

include 'lib/ZLoader.php';
ZLoader::register();

EventUtil::attach('core.init', 'upgrade_suppressErrors');

ini_set('max_execution_time', 86400);

// load zikula core
define('_ZINSTALLVER', '1.3.0');
define('_Z_MINUPGVER', '1.2.0');

// include config file for retrieving name of temporary directory
require_once 'install/modify_config.php';
$GLOBALS['ZConfig']['System']['multilingual'] = true;
$GLOBALS['ZConfig']['System']['language_bc'] = false;

include 'config/config.php';
$connection = Doctrine_Manager::connection($GLOBALS['ZConfig']['DBInfo']['default']['dsn'], 'upgrader');
//$connection->setCharset('utf8');
//$connection->setCollate('utf8_general_ci');
//$stmt = $connection->prepare("SET NAMES 'utf8'");// COLLATE 'utf8_general_ci'");
//$stmt->execute();

//$columns = upgrade_getColumnsForTable($connection, 'modules');
//if (in_array('pn_id', array_keys($columns))) {
//    upgrade_columns($connection);
//}

if (!isset($columns['capabilities']) || !isset($columns['min_version'])) {
    ModUtil::dbInfoLoad('Modules', 'Modules');
    DBUtil::changeTable('modules');
    ModUtil::dbInfoLoad('Blocks', 'Blocks');
    DBUtil::changeTable('blocks');
}

$tables = upgrade_getTables($connection);
$_SESSION['_ZikulaUpgrader'] = array();
if (!in_array('users_registration', $tables)) {
    $_SESSION['_ZikulaUpgrader']['_ZikulaUpgradeFrom12x'] = true;
}

System::init(System::CORE_STAGES_ALL);

$action = FormUtil::getPassedValue('action', false, 'GETPOST');

// login to supplied admin credentials for action the following actions
if ($action === 'upgrademodules' || $action === 'convertdb' || $action === 'sanitycheck') {
    $username = FormUtil::getPassedValue('username', null, 'POST');
    $password = FormUtil::getPassedValue('password', null, 'POST');

    if (!UserUtil::loginUsing('Users', array('loginid' => $username, 'pass' => $password))) {
        // force action to login
        $action = 'login';
    } else {
        define('_ZINSTALLEDVERSION', $installed = System::getVar('Version_Num', __('version info not available')));
    }
}

switch ($action)
{
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
    echo '<div class="z-formrow"><label for="username">' . __('User name') . '</label><input id="username" type="text" name="username" size="25" maxlength="25" /></div>' . "\n";
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
    ModUtil::loadApi('Modules', 'admin', true);

    echo '<h2>' . __('Starting upgrade') . '</h2>' . "\n";
    echo '<ul id="upgradelist" class="check">' . "\n";

    // reset for User module
    //$_SESSION['_ZikulaUpgrader']['_ZikulaUpgradeFrom12x'] = false;

    $results = ModUtil::apiFunc('Modules', 'admin', 'upgradeall');
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
    $sql = "DELETE FROM $modTable WHERE pn_name = 'Header_Footer' OR pn_name = 'AuthPN' OR pn_name = 'pnForm' OR pn_name = 'Workflow' OR pn_name = 'pnRender' OR pn_name = 'Admin_Messages'";
    DBUtil::executeSQL($sql);

    // regenerate the themes list
    ModUtil::apiFunc('Theme', 'admin', 'regenerate');

    // store the recent version in a config var for later usage. This enables us to determine the version we are upgrading from
    System::setVar('Version_Num', System::VERSION_NUM);
    System::setVar('language_i18n', ZLanguage::getLanguageCode());
    System::setVar('language_bc', 0);

    // Relogin the admin user to give a proper admin link
    SessionUtil::requireSession();

    echo '<p class="z-statusmsg">' . __('Finished upgrade') . " - \n";
    if (!UserUtil::loginUsing('Users', array('loginid' => $username, 'pass' => $password))) {
        $url = sprintf('<a href="%s">%s</a>', DataUtil::formatForDisplay(System::getBaseUrl()), DataUtil::formatForDisplay(System::getVar('sitename')));
        echo __f('Go to the startpage for %s', $url);
    } else {
        upgrade_clear_caches();
        $url = sprintf('<a href="%s">%s</a>', DataUtil::formatForDisplay(System::getBaseUrl().'admin.php'), DataUtil::formatForDisplay(System::getVar('sitename')));
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

    if (!$validupgrade) {
        _upg_footer();
        System::shutdown();
    }

    _upg_continue('upgrademodules', __('Proceed to upgrade.'), $username, $password);
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
    if (!$event['stage'] == System::CORE_STAGES_CONFIG) {
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
    $moduleTable = $prefix . 'module_vars';

    $stmt = $connection->prepare("SELECT pn_value FROM $moduleTable WHERE pn_modname = '/PNConfig' AND pn_name = 'Version_Num'");
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
 *
 * @return array
 */
function upgrade_getColumnsForTable($connection, $tableName)
{
    $tables = $connection->import->listTables();
    if (!$tables) {
        die(__('FATAL ERROR: Cannot start, unable to determine installed Core version.'));
    }

    try {
        return $connection->import->listTableColumns($GLOBALS['ZConfig']['System']['prefix'] . "_$tableName");
    } catch (Exception $e) {

    }
}

/**
 * Evil.
 *
 * @param PDOConnection $connection
 */
function upgrade_columns($connection)
{
    $prefix = $GLOBALS['ZConfig']['System']['prefix'];
    $tables = array('admin_category', 'admin_module', 'block_placements', 'block_positions', 'blocks', 'categories_category', 'categories_mapmeta', 'categories_mapobj', 'categories_registry', 'group_applications', 'group_membership', 'group_perms', 'groups', 'hooks', 'message', 'module_deps', 'module_vars', 'modules', 'objectdata_attributes', 'objectdata_log', 'objectdata_meta', 'pagelock', 'search_result', 'search_stat', 'session_info', 'themes', 'userblocks', 'users', 'users_registration', 'users_verifychg', 'workflows');
    foreach ($tables as $table) {
        $columns = upgrade_getColumnsForTable($connection, $table);
        if (!$columns) {
            continue;
        }
        foreach ($columns as $columnDef) {
            $newName = preg_replace('#^pn_(.*)#', '$1', $columnDef['name']);
            if ($newName == $columnDef['name']) {
                echo '.';
                continue;
            }
            $renameColumnArray = array($columnDef['name'] => array('name' => "z_$newName", 'definition' => $columnDef));
            try {
                $connection->export->alterTable($prefix.'_'.$table, array('rename' => $renameColumnArray));
            } catch (Exception $e) {
                echo $e->getMessage() .'<br />';
            }
        }
    }

}