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
ZLoader::addAutoloader('Users', 'system/Users/lib', '_');
include_once __DIR__.'/plugins/Doctrine/Plugin.php';

// check if the config.php was renewed
if (!isset($GLOBALS['ZConfig']['Log']['log.to_debug_toolbar'])) {
    echo __('It seems to be that your config.php is outdated. Please check the release notes for more information.');
    die();
}

PluginUtil::loadPlugin('SystemPlugin_Doctrine_Plugin');
$eventManager = $core->getEventManager();
$eventManager->attach('core.init', 'upgrade_suppressErrors');

// load zikula core
define('_ZINSTALLVER', Zikula_Core::VERSION_NUM);
define('_Z_MINUPGVER', '1.2.0');

// Signal that upgrade is running.
$GLOBALS['_ZikulaUpgrader'] = array();

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
    Doctrine_Core::createTablesFromArray(array('Zikula_Doctrine_Model_HookArea', 'Zikula_Doctrine_Model_HookProvider', 'Zikula_Doctrine_Model_HookSubscriber', 'Zikula_Doctrine_Model_HookBinding', 'Zikula_Doctrine_Model_HookRuntime'));
    ModUtil::dbInfoLoad('Extensions', 'Extensions', true);
    DBUtil::changeTable('modules');
    ModUtil::dbInfoLoad('Blocks', 'Blocks', true);
    DBUtil::changeTable('blocks');
}

$installedVersion = upgrade_getCurrentInstalledCoreVersion($connection);

if (version_compare($installedVersion, '1.3.0-dev') === -1) {
    $GLOBALS['_ZikulaUpgrader']['_ZikulaUpgradeFrom12x'] = true;
}

$core->init(Zikula_Core::STAGE_ALL);

$action = FormUtil::getPassedValue('action', false, 'GETPOST');

// login to supplied admin credentials for action the following actions
if ($action === 'upgrademodules' || $action === 'convertdb' || $action === 'sanitycheck') {
    $username = FormUtil::getPassedValue('username', null, 'POST');
    $password = FormUtil::getPassedValue('password', null, 'POST');

    $authenticationInfo = array(
        'login_id'  => $username,
        'pass'      => $password
    );
    $authenticationMethod = array(
        'modname'   => 'Users',
        'method'    => 'uname',
    );
    if (!UserUtil::loginUsing($authenticationMethod, $authenticationInfo)) {
        // force action to login
        $action = 'login';
    } else {
        define('_ZINSTALLEDVERSION', $installedVersion);
    }
}

// check if the default theme is compatible with Zikula >= 1.3
$themeName = System::getVar('Default_Theme');
$themeId   = ThemeUtil::getIDFromName($themeName);
$theme     = ThemeUtil::getInfo($themeId);
$directory = $theme['directory'];
if (!file_exists('themes/'.$directory.'/templates/master.tpl')) {
    if (ThemeUtil::getIDFromName('Andreas08')  && file_exists('themes/Andreas08/templates/master.tpl')) {
        System::setVar('Default_Theme', 'Andreas08');
    } elseif (ThemeUtil::getIDFromName('SeaBreeze') && file_exists('themes/SeaBreeze/templates/master.tpl')) {
        System::setVar('Default_Theme', 'SeaBreeze');
    } else {
        _upg_header();
        echo '<p class="z-errormsg">' . __('Theme is not valid!') . '</p>' . "\n";
        _upg_footer();
        die();
    }    
}

// deactivate file based shorturls
if (System::getVar('shorturls') && System::getVar('shorturlstype')) {
    System::setVar('shorturls', false);
    System::delVar('shorturlstype'); 
    System::delVar('shorturlsext');
    LogUtil::registerError('You were using file based shorturls. This feature will no longer be supported. The shorturls were disabled. Directory based shorturls can be activated in the General settings manager.');
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
    echo '<link rel="stylesheet" href="install/style/installer.css" type="text/css" />' . "\n";
    echo '<link rel="stylesheet" href="style/core.css" type="text/css" />' . "\n";
    echo '<!--[if IE]><link rel="stylesheet" type="text/css" href="style/core_iehacks.css" media="print,projection,screen" /><![endif]-->' . "\n";
    echo '<script src="javascript/ajax/proto_scriptaculous.combined.min.js" type="text/javascript"></script>' . "\n";
    echo '<script src="install/javascript/install.js" type="text/javascript"></script>' . "\n";
    echo '</head>' . "\n";
    echo '<body>' . "\n";
    echo '<div id="container"><div id="content">' . "\n";
    echo '<div id="header">' . "\n";
    echo '<h1>' . __('Zikula Application Framework') . '</h1>' . "\n";
    echo '<h2>' . __('Upgrade script') . '</h2></div>' . "\n";
    echo '<div id="maincontent">';
    if (UserUtil::isLoggedIn()) {
        echo '<h3>' . __f('Zikula Upgrade script (for Zikula version %s and up)', array(_Z_MINUPGVER)) . '</h3>' . "\n";
        echo '<p>' . __f('This script will upgrade any Zikula v%1$s+ installation. Upgrades from less than Zikula v%1$s are not supported by this script.', array(_Z_MINUPGVER)) . "</p>\n";
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
    $lang = ZLanguage::getLanguageCode();
    echo '</div></div>' . "\n";
    echo '<div id="footer">' . "\n";
    echo '<p id="notice">' . __f('For more information about the upgrade process, please read the <a href="docs/%1$s/UPGRADING">upgrade documentation</a>, visit our <a href="http://community.zikula.org/Wiki.htm">wiki</a> or the <a href="http://community.zikula.org/module-Forum.htm">support forum</a>.', $lang) . '</p>';
    echo '</div>' . "\n";
    echo '</div></body>' . "\n";
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
    echo '<div class="z-buttons z-center"><input type="submit" value="' . __('Submit') . '" /></div>' . "\n";
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
    echo '<div class="z-buttons z-center"><input name="submit" type="submit" value="' . __('Submit') . '" /></div>' . "\n";
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
    //$GLOBALS['_ZikulaUpgrader']['_ZikulaUpgradeFrom12x'] = false;

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

    // wipe out the deprecated modules from Modules list.
    $modTable = 'modules';
    $sql = "DELETE FROM $modTable WHERE name = 'Header_Footer' OR name = 'AuthPN' OR name = 'pnForm' OR name = 'Workflow' OR name = 'pnRender' OR name = 'Admin_Messages'";
    DBUtil::executeSQL($sql);

    // store localized displayname and description for Extensions module
    $extensionsDisplayname = __('Extensions');
    $extensionsDescription = __('Manage your modules and plugins.');
    $sql = "UPDATE modules SET name = 'Extensions', displayname = '{$extensionsDisplayname}', description = '{$extensionsDescription}' WHERE modules.name = 'Extensions'";
    DBUtil::executeSQL($sql);

    // regenerate the themes list
    ModUtil::apiFunc('Theme', 'admin', 'regenerate');

    // store the recent version in a config var for later usage. This enables us to determine the version we are upgrading from
    System::setVar('Version_Num', Zikula_Core::VERSION_NUM);
    System::setVar('language_i18n', ZLanguage::getLanguageCode());

    // Relogin the admin user to give a proper admin link
    SessionUtil::requireSession();

    echo '<p class="z-statusmsg">' . __('Finished upgrade') . " - \n";

    $authenticationInfo = array(
        'login_id'  => $username,
        'pass'      => $password
    );
    $authenticationMethod = array(
        'modname'   => 'Users',
        'method'    => 'uname',
    );

    if (!UserUtil::loginUsing($authenticationMethod, $authenticationInfo)) {
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
    echo '<div class="z-buttons z-center"><input type="submit" name="submit" value="' . htmlspecialchars($text) . '" /></div>' . "\n";
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
        if ($defaultTheme == 'andreas08') {
            System::setVar('Default_Theme', 'Andreas08');
            $defaultTheme = System::getVar('Default_Theme');
        }
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
    $moduleTable = 'module_vars';

    $stmt = $connection->prepare("SELECT value FROM $moduleTable WHERE modname = 'ZConfig' AND name = 'Version_Num'");
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
        return $connection->import->listTableColumns(($GLOBALS['ZConfig']['System']['prefix'] ? $GLOBALS['ZConfig']['System']['prefix'].'_' : '').$tableName);
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
    $commands[] = "ALTER TABLE {$prefix}_admin_category CHANGE pn_cid cid INT(11) NOT NULL AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_admin_category CHANGE pn_name name VARCHAR(32) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_admin_category CHANGE pn_description description VARCHAR(254) NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_admin_category TO admin_category";


    $commands[] = "ALTER TABLE {$prefix}_admin_module CHANGE pn_amid amid INT(11) NOT NULL AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_admin_module CHANGE pn_mid mid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_admin_module CHANGE pn_cid cid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_admin_module TO admin_module";

    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_bid bid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_bkey bkey VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_title title VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_content content LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_url url LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_mid mid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_filter filter LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_active active TINYINT DEFAULT '1' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_collapsable collapsable INT(11) DEFAULT '1' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_defaultstate defaultstate INT(11) DEFAULT '1' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_refresh refresh INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_last_update last_update DATETIME NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_blocks CHANGE pn_language language VARCHAR(30) NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_blocks TO blocks";

    $commands[] = "ALTER TABLE {$prefix}_userblocks CHANGE pn_uid uid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_userblocks CHANGE pn_bid bid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_userblocks CHANGE pn_active active TINYINT DEFAULT '1' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_userblocks CHANGE pn_last_update last_update DATETIME";
    $commands[] = "RENAME TABLE {$prefix}_userblocks TO userblocks";

    $commands[] = "ALTER TABLE {$prefix}_block_positions CHANGE pn_pid pid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_block_positions CHANGE pn_name name VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_block_positions CHANGE pn_description description VARCHAR(255) NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_block_positions TO block_positions";

    $commands[] = "ALTER TABLE {$prefix}_block_placements CHANGE pn_pid pid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_block_placements CHANGE pn_bid bid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_block_placements CHANGE pn_order sortorder INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_block_placements TO block_placements";

    $commands[] = "ALTER TABLE {$prefix}_group_membership CHANGE pn_gid gid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_membership CHANGE pn_uid uid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_group_membership TO group_membership";

    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_gid gid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_name name VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_gtype gtype TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_description description VARCHAR(200) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_prefix prefix VARCHAR(25) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_state state TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_nbuser nbuser INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_nbumax nbumax INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_link link INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_groups CHANGE pn_uidmaster uidmaster INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_groups TO groups";

    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE pn_app_id app_id INT(11) NOT NULL AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE pn_uid uid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE pn_gid gid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE pn_application application LONGBLOB NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_applications CHANGE pn_status status TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_group_applications TO group_applications";

    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_id id BIGINT AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_object object VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_action action VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_smodule smodule VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_stype stype VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_tarea tarea VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_tmodule tmodule VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_ttype ttype VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_tfunc tfunc VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_hooks CHANGE pn_sequence sequence INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_hooks TO hooks";

    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_id id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_name name VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_type type TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_displayname displayname VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_url url VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_description description VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_regid regid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_directory directory VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_version version VARCHAR(10) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_official official TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_author author VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_contact contact VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_admin_capable admin_capable TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_user_capable user_capable TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_profile_capable profile_capable TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_message_capable message_capable TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_state state SMALLINT(6) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_credits credits VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_changelog changelog VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_help help VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_license license VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_modules CHANGE pn_securityschema securityschema TEXT NOT NULL";
    $commands[] = "UPDATE {$prefix}_modules SET name = 'Extensions', displayname = 'Extensions', url = 'extensions', description = 'Manage your modules and plugins.', directory =  'Extensions', securityschema = 'a:1:{s:9:\"Extensions::\";s:2:\"::\";}' WHERE {$prefix}_modules.name = 'Modules'";
    $commands[] = "RENAME TABLE {$prefix}_modules TO modules";

    $commands[] = "ALTER TABLE {$prefix}_module_vars CHANGE pn_id id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_module_vars CHANGE pn_modname modname VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_vars CHANGE pn_name name VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_vars CHANGE pn_value value LONGTEXT";
    $commands[] = "UPDATE {$prefix}_module_vars SET modname='Extensions' WHERE modname='Modules'";
    $commands[] = "RENAME TABLE {$prefix}_module_vars TO module_vars";

    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE pn_id id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE pn_modid modid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE pn_modname modname VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE pn_minversion minversion VARCHAR(10) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE pn_maxversion maxversion VARCHAR(10) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_module_deps CHANGE pn_status status TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_module_deps TO module_deps";

    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_pid pid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_gid gid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_sequence sequence INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_realm realm INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_component component VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_instance instance VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_level level INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_group_perms CHANGE pn_bond bond INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_group_perms TO group_perms";

    $commands[] = "ALTER TABLE {$prefix}_search_stat CHANGE pn_id id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_search_stat CHANGE pn_search search VARCHAR(50) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_search_stat CHANGE pn_count scount INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_search_stat CHANGE pn_date date DATE";
    $commands[] = "RENAME TABLE {$prefix}_search_stat TO search_stat";

    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_id id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_title title VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_text text LONGTEXT";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_module module VARCHAR(100)";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_extra extra VARCHAR(100)";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_created created DATETIME";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_found found DATETIME";
    $commands[] = "ALTER TABLE {$prefix}_search_result CHANGE sres_sesid sesid VARCHAR(50)";
    $commands[] = "RENAME TABLE {$prefix}_search_result TO search_result";

    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_id id INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_name name VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_type type TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_displayname displayname VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_description description VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_regid regid INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_directory directory VARCHAR(64) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_version version VARCHAR(10) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_official official TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_author author VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_contact contact VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_admin admin TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_user user TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_system system TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_state state TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_credits credits VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_changelog changelog VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_help help VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_license license VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_themes CHANGE pn_xhtml xhtml TINYINT DEFAULT '1' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_themes TO themes";

    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_uid uid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_uname uname VARCHAR(25) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_email email VARCHAR(60) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_user_regdate user_regdate DATETIME DEFAULT '1970-01-01 00:00:00' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_user_viewemail user_viewemail SMALLINT DEFAULT '0'";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_user_theme user_theme VARCHAR(64)";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_pass pass VARCHAR(128) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_storynum storynum INT DEFAULT '10' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_ublockon ublockon TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_ublock ublock TEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_theme theme VARCHAR(255) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_counter counter INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_activated activated TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_lastlogin lastlogin DATETIME DEFAULT '1970-01-01 00:00:00' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_validfrom validfrom INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_validuntil validuntil INT(11) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users CHANGE pn_hash_method hash_method TINYINT(4) DEFAULT '8' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_users TO users";

    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_tid tid INT(11) AUTO_INCREMENT";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_uname uname VARCHAR(25) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_email email VARCHAR(60) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_femail femail TINYINT(4) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_pass pass VARCHAR(128) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_dynamics dynamics LONGTEXT NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_comment comment VARCHAR(254) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_type type TINYINT(4) DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_users_temp CHANGE pn_tag tag TINYINT(4) DEFAULT '0' NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_users_temp TO users_temp";

    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE pn_sessid sessid VARCHAR(40) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE pn_ipaddr ipaddr VARCHAR(32) NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE pn_lastused lastused DATETIME DEFAULT '1970-01-01 00:00:00'";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE pn_uid uid INT(11) DEFAULT '0'";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE pn_remember remember TINYINT DEFAULT '0' NOT NULL";
    $commands[] = "ALTER TABLE {$prefix}_session_info CHANGE pn_vars vars LONGTEXT NOT NULL";
    $commands[] = "RENAME TABLE {$prefix}_session_info TO session_info";

    $commands[] = "RENAME TABLE {$prefix}_categories_category TO categories_category";
    $commands[] = "ALTER TABLE `categories_category` CHANGE `cat_id` `id` INT(11) NOT NULL AUTO_INCREMENT, CHANGE `cat_parent_id` `parent_id` INT(11) NOT NULL DEFAULT '1', CHANGE `cat_is_locked` `is_locked` TINYINT(4) NOT NULL DEFAULT '0', CHANGE `cat_is_leaf` `is_leaf` TINYINT(4) NOT NULL DEFAULT '0', CHANGE `cat_name` `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `cat_value` `value` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `cat_sort_value` `sort_value` INT(11) NOT NULL DEFAULT '0', CHANGE `cat_display_name` `display_name` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `cat_display_desc` `display_desc` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `cat_path` `path` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `cat_ipath` `ipath` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `cat_status` `status` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A', CHANGE `cat_obj_status` `obj_status` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A', CHANGE `cat_cr_date` `cr_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', CHANGE `cat_cr_uid` `cr_uid` INT(11) NOT NULL DEFAULT '0', CHANGE `cat_lu_date` `lu_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', CHANGE `cat_lu_uid` `lu_uid` INT(11) NOT NULL DEFAULT '0'";
    $commands[] = "RENAME TABLE {$prefix}_categories_mapmeta TO categories_mapmeta";
    $commands[] = "ALTER TABLE `categories_mapmeta` CHANGE  `cmm_id`  `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
CHANGE  `cmm_meta_id`  `meta_id` INT( 11 ) NOT NULL DEFAULT  '0',
CHANGE  `cmm_category_id`  `category_id` INT( 11 ) NOT NULL DEFAULT  '0',
CHANGE  `cmm_obj_status`  `obj_status` VARCHAR( 1 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'A',
CHANGE  `cmm_cr_date`  `cr_date` DATETIME NOT NULL DEFAULT  '1970-01-01 00:00:00',
CHANGE  `cmm_cr_uid`  `cr_uid` INT( 11 ) NOT NULL DEFAULT  '0',
CHANGE  `cmm_lu_date`  `lu_date` DATETIME NOT NULL DEFAULT  '1970-01-01 00:00:00',
CHANGE  `cmm_lu_uid`  `lu_uid` INT( 11 ) NOT NULL DEFAULT  '0'";

    $commands[] = "RENAME TABLE {$prefix}_categories_mapobj TO categories_mapobj";
    $commands[] = "ALTER TABLE `categories_mapobj` CHANGE `cmo_id` `id` INT(11) NOT NULL AUTO_INCREMENT, CHANGE `cmo_modname` `modname` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `cmo_table` `tablename` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `cmo_obj_id` `obj_id` INT(11) NOT NULL DEFAULT '0', CHANGE `cmo_obj_idcolumn` `obj_idcolumn` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'id', CHANGE `cmo_reg_id` `reg_id` INT(11) NOT NULL DEFAULT '0', CHANGE `cmo_category_id` `category_id` INT(11) NOT NULL DEFAULT '0', CHANGE `cmo_obj_status` `obj_status` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A', CHANGE `cmo_cr_date` `cr_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', CHANGE `cmo_cr_uid` `cr_uid` INT(11) NOT NULL DEFAULT '0', CHANGE `cmo_lu_date` `lu_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', CHANGE `cmo_lu_uid` `lu_uid` INT(11) NOT NULL DEFAULT '0'";

    $commands[] = "RENAME TABLE {$prefix}_categories_registry TO categories_registry";
    $commands[] = "ALTER TABLE `categories_registry` CHANGE `crg_id` `id` INT(11) NOT NULL AUTO_INCREMENT, CHANGE `crg_modname` `modname` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `crg_table` `tablename` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `crg_property` `property` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `crg_category_id` `category_id` INT(11) NOT NULL DEFAULT '0', CHANGE `crg_obj_status` `obj_status` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A', CHANGE `crg_cr_date` `cr_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', CHANGE `crg_cr_uid` `cr_uid` INT(11) NOT NULL DEFAULT '0', CHANGE `crg_lu_date` `lu_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', CHANGE `crg_lu_uid` `lu_uid` INT(11) NOT NULL DEFAULT '0'";

    $commands[] = "RENAME TABLE {$prefix}_objectdata_attributes TO objectdata_attributes";
    $commands[] = "ALTER TABLE `objectdata_attributes` CHANGE `oba_id` `id` INT(11) NOT NULL AUTO_INCREMENT, CHANGE `oba_attribute_name` `attribute_name` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `oba_object_id` `object_id` INT(11) NOT NULL DEFAULT '0', CHANGE `oba_object_type` `object_type` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `oba_value` `value` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `oba_obj_status` `obj_status` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A', CHANGE `oba_cr_date` `cr_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', CHANGE `oba_cr_uid` `cr_uid` INT(11) NOT NULL DEFAULT '0', CHANGE `oba_lu_date` `lu_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', CHANGE `oba_lu_uid` `lu_uid` INT(11) NOT NULL DEFAULT '0'";

    $commands[] = "RENAME TABLE {$prefix}_objectdata_log TO objectdata_log";
    $commands[] = "ALTER TABLE `objectdata_log` CHANGE `obl_id` `id` INT(11) NOT NULL AUTO_INCREMENT, CHANGE `obl_object_type` `object_type` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `obl_object_id` `object_id` INT(11) NOT NULL DEFAULT '0', CHANGE `obl_op` `op` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `obl_diff` `diff` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obl_obj_status` `obj_status` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A', CHANGE `obl_cr_date` `cr_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', CHANGE `obl_cr_uid` `cr_uid` INT(11) NOT NULL DEFAULT '0', CHANGE `obl_lu_date` `lu_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', CHANGE `obl_lu_uid` `lu_uid` INT(11) NOT NULL DEFAULT '0'";

    $commands[] = "RENAME TABLE {$prefix}_objectdata_meta TO objectdata_meta";
    $commands[] = "ALTER TABLE `objectdata_meta` CHANGE `obm_id` `id` INT(11) NOT NULL AUTO_INCREMENT, CHANGE `obm_module` `module` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `obm_table` `tablename` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `obm_idcolumn` `idcolumn` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `obm_obj_id` `obj_id` INT(11) NOT NULL DEFAULT '0', CHANGE `obm_permissions` `permissions` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_title` `dc_title` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_author` `dc_author` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_subject` `dc_subject` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_keywords` `dc_keywords` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_description` `dc_description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_publisher` `dc_publisher` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_contributor` `dc_contributor` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_startdate` `dc_startdate` DATETIME NULL DEFAULT '1970-01-01 00:00:00', CHANGE `obm_dc_enddate` `dc_enddate` DATETIME NULL DEFAULT '1970-01-01 00:00:00', CHANGE `obm_dc_type` `dc_type` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_format` `dc_format` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_uri` `dc_uri` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_source` `dc_source` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_language` `dc_language` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_relation` `dc_relation` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_coverage` `dc_coverage` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_entity` `dc_entity` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_comment` `dc_comment` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_dc_extra` `dc_extra` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `obm_obj_status` `obj_status` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'A', CHANGE `obm_cr_date` `cr_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', CHANGE `obm_cr_uid` `cr_uid` INT(11) NOT NULL DEFAULT '0', CHANGE `obm_lu_date` `lu_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', CHANGE `obm_lu_uid` `lu_uid` INT(11) NOT NULL DEFAULT '0'";

    $commands[] = "DROP TABLE {$prefix}_sc_anticracker";

    $commands[] = "DROP TABLE {$prefix}_sc_log_event";

    $commands[] = "UPDATE module_vars SET modname = 'ZConfig' WHERE modname = '/PNConfig'";
    $silentCommands = array();
    $silentCommands[] = "ALTER TABLE {$prefix}_message CHANGE pn_mid mid INT(11) NOT NULL AUTO_INCREMENT ,
CHANGE pn_title title VARCHAR(100) NOT NULL DEFAULT  '',
CHANGE pn_content content LONGTEXT NOT NULL ,
CHANGE pn_date date INT(11) NOT NULL DEFAULT  '0',
CHANGE pn_expire expire INT(11) NOT NULL DEFAULT  '0',
CHANGE pn_active active INT(11) NOT NULL DEFAULT  '1',
CHANGE pn_view view INT(11) NOT NULL DEFAULT  '1',
CHANGE pn_language language VARCHAR(30) NOT NULL DEFAULT  ''";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE plock_id id INT(11) NOT NULL AUTO_INCREMENT";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE plock_name name VARCHAR(100) NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE plock_cdate cdate DATETIME NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE plock_edate edate DATETIME NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE plock_session session VARCHAR(50) NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE plock_title title VARCHAR(100) NOT NULL";
    $silentCommands[] = "ALTER TABLE {$prefix}_pagelock CHANGE plock_ipno ipno VARCHAR(30) NOT NULL";
    $silentCommands[] = "RENAME TABLE {$prefix}_pagelock TO pagelock";

    // LONGBLOB is not supported by Doctrine 2
    $silentCommands[] = "ALTER TABLE {$prefix}_workflows CHANGE debug debug LONGTEXT NULL DEFAULT NULL";
    $silentCommands[] = "RENAME TABLE {$prefix}_workflows TO workflows";
    $silentCommands[] = "ALTER TABLE group_applications CHANGE application application LONGTEXT NOT NULL";
    
    // Handle case of andreas08 themes on linux environments.
    $silentCommands[] = "UPDATE themes SET name = 'Andreas08', directory = 'Andreas08' WHERE name = 'andreas08'";
    $silentCommands[] = "UPDATE module_vars SET value = 's:9:\"Andreas08\";' WHERE modname = 'ZConfig' AND value ='s:9:\"andreas08\";'";

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
