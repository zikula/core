<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

include 'lib/ZLoader.php';
ZLoader::register();

ini_set('max_execution_time', 86400);

// load zikula core
define('_ZINSTALLVER', '1.3.0');
define('_Z_MINUPGVER', '1.1.0');

// include config file for retrieving name of temporary directory
require_once 'install/modify_config.php';
$GLOBALS['ZConfig']['System']['multilingual'] = true;
$GLOBALS['ZConfig']['System']['language_bc'] = false;
$_SESSION['_ZikulaUpgrader']['_ZikulaUpgradeFrom110'] = true;
pnInit(PN_CORE_ALL);
$action = FormUtil::getPassedValue('action', false, 'GETPOST');

// login to supplied admin credentials for action the following actions
if ($action === 'upgrademodules' || $action === 'convertdb' || $action === 'sanitycheck') {
    $username = FormUtil::getPassedValue('username', null, 'POST');
    $password = FormUtil::getPassedValue('password', null, 'POST');

    if (!pnUserLogin($username, $password)) {
        // force action to login
        $action = 'login';
    } else {
        define('_ZINSTALLEDVERSION', $installed = pnConfigGetVar('Version_Num', __('version info not available')));
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
    case 'convertdb':
        _upg_convertdb($username, $password);
        break;
    case 'upgrademodules':
        _upg_upgrademodules($username, $password);
        break;
    default:
        _upg_selectlanguage();
}

function _upg_header()
{
    $lang = ZLanguage::getLanguageCode();
    $charset = ZLanguage::getEncoding();
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . "\n";
    echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . $lang . '">' . "\n";
    echo '<head>' . "\n";
    echo '<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '" />' . "\n";
    echo '<title>' . __f('Zikula Upgrade script') . "</title>\n";
    echo '<link rel="stylesheet" href="install/pnstyle/upgrade.css" type="text/css" />' . "\n";
    echo '<link rel="stylesheet" href="javascript/style.css" type="text/css" />' . "\n";
    echo '</head>' . "\n";
    echo '<body>' . "\n";
    echo '<div id="container"><div id="wrapper" class="z-clearfix">' . "\n";
    echo '<div id="header" class="z-clearfix">' . "\n";
    echo '<div id="headertopleft"><img src="install/pnimages/top1.jpg" alt="" /></div>' . "\n";
    echo '<div id="headertopright"><img src="install/pnimages/top2.jpg" alt="" /></div>' . "\n";
    echo '</div>' . "\n";
    echo '<div class="menu">' . "\n";
    echo '<p id="notice">' . __('For more information about the upgrade process, please read the <a href="docs/upgrade.html">upgrade documentation</a>, visit our <a href="http://community.zikula.org/Wiki.htm">wiki</a> or the <a href="http://community.zikula.org/module-Forum.htm">support forum</a>') . '</p>';
    echo '</div>';
    echo '<div id="content">';
    if (pnUserLoggedIn()) {
        echo '<h1>' . __f('Zikula version %1$s Upgrade script (for Zikula version %2$s and up)', array(_ZINSTALLVER, _Z_MINUPGVER)) . '</h1>' . "\n";
        echo '<p>' . __f('This script will upgrade your Zikula v%1$s to v%2$s. Upgrades from less than Zikula v%1$s are not supported by this script.', array(_Z_MINUPGVER, _ZINSTALLVER)) . "</p>\n";
    }
}

function _upg_footer()
{
    echo '</div></div></div>' . "\n";
    echo '</body>' . "\n";
    echo '</html>';
    exit();
}

function _upg_selectlanguage()
{
    _upg_header();

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

function _upg_upgradeinit()
{
    _upg_header();

    echo '<h2>' . __('BEFORE proceeding you should backup your database!') . '</h2>' . "\n";
    _upg_login(false);

    _upg_footer();
}

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

function _upg_convertdb($username, $password)
{
    _upg_header();

    echo '<h2>' . __('Check the database character set: UTF8') . '</h2>' . "\n";

    // Some vars needed here.
    $converted = $error = false;
    $charset = 'utf8';
    $collation = 'utf8_general_ci';
    $feedback = '';

    // Database info
    global $ZConfig;
    $dbtype = $ZConfig['DBInfo']['default']['dbtype'];
    $dbchar = $ZConfig['DBInfo']['default']['dbcharset'];
    $dbhost = $ZConfig['DBInfo']['default']['dbhost'];
    $dbuser = $ZConfig['DBInfo']['default']['dbuname'];
    $dbpass = $ZConfig['DBInfo']['default']['dbpass'];
    $dbname = $ZConfig['DBInfo']['default']['dbname'];
    $prefix = $ZConfig['System']['prefix'];

    // sanity checks
    if ($dbchar == $charset) {
        $feedback .= '<p class="z-informationmsg">' . __("Your config.php reports your database is already in utf8 format.") . "</p>\n";
        $converted = true;
    } elseif (!strstr($dbtype, 'mysql')) {
        $feedback .= '<p class="z-errormsg">' . __f('This script is only for MySQL databases, you are using %s.', $dbtype) . "<br />\n";
        $feedback .= '<strong>' . __('You have to convert your database to utf8 yourself manually, before you can use this version of Zikula.') . "</strong></p>\n";
        $error = true;
    }

    if ($error) {
        echo $feedback;
    } elseif (!$converted) {
        // decode if necessary
        if ($ZConfig['DBInfo']['default']['encoded']) {
            $dbuser = base64_decode($dbuser);
            $dbpass = base64_decode($dbpass);
        }

        $feedback .= '<ul id="convertlist" class="check">' . "\n";

        // connect to DB
        $dbconn = mysql_connect($dbhost, $dbuser, $dbpass);
        $db = mysql_select_db($dbname);
        if ($db) {
            // alter database characterset and collation
            doSQL("ALTER DATABASE $dbname DEFAULT CHARACTER SET = $charset", $dbconn, $feedback);
            doSQL("ALTER DATABASE $dbname DEFAULT COLLATE = $collation", $dbconn, $feedback);

            $result = doSQL('SHOW TABLES', $dbconn, $feedback);
            if ($result) {
                // alter tables
                while ($row = mysql_fetch_row($result)) {
                    $table = mysql_real_escape_string($row[0]);
                    if (preg_match('/^' . $prefix . '_/', $table)) {
                        doSQL("ANALYZE TABLE $table", $dbconn, $feedback);
                        doSQL("REPAIR TABLE $table", $dbconn, $feedback);
                        doSQL("OPTIMIZE TABLE $table", $dbconn, $feedback);
                        doSQL("ALTER TABLE $table DEFAULT CHARACTER SET $charset COLLATE $collation", $dbconn, $feedback);
                        // hack for locations module - TODO remove after release.
                        if ($table == "{$prefix}_locations_location") {
                            // delete index
                            doSQL("ALTER TABLE {$prefix}_locations_location DROP INDEX locindex", $dbconn, $feedback);
                        }
                        doSQL("ALTER TABLE $table CONVERT TO CHARACTER SET $charset COLLATE $collation", $dbconn, $feedback);
                        if ($table == "{$prefix}_locations_location") {
                            // recreate index
                            doSQL("ALTER TABLE `{$prefix}_locations_location` ADD INDEX `locindex` (`pn_name`(50),`pn_city`(50),`pn_state`(50),`pn_country`(50))", $dbconn, $feedback);
                        }
                    } else {
                        $feedback .= '<li class="passed">' . __f("SKIPPED %s", $table) . "</li>\n";
                    }
                }

                mysql_close($dbconn);
                $feedback .= '</ul>' . "\n";

                // commit changes to config
                global $reg_src, $reg_rep;
                add_src_rep('dbcharset', $charset);
                $feedback .= "<div>";
                if (modify_file($reg_src, $reg_rep)) {
                    $feedback .= '<p class="z-statusmsg">' . __f('Updated %s', 'config/config.php $ZConfig[\'DBInfo\'][\'default\'][\'dbcharset\']  = \'' . $charset . "'; </p>\n");
                    if (file_exists('config/personal_config.php')) {
                        if (modify_file($reg_src, $reg_rep, 'config/personal_config.php')) {
                            $feedback .= '<p class="z-statusmsg">' . __f('Updated %s', 'config/personal_config.php $ZConfig[\'DBInfo\'][\'default\'][\'dbcharset\']  = \'' . $charset . "'; </p>\n");
                        } else {
                            $feedback .= '<p class="z-errormsg">' . __f('Failed to update %s', '$ZConfig[\'DBInfo\'][\'default\'][\'dbcharset\']  = \'' . $charset . "'; </p>\n");
                        }
                    }
                } else {
                    $feedback .= '<p class="z-errormsg">' . __f('Failed to update %s', '$ZConfig[\'DBInfo\'][\'default\'][\'dbcharset\']  = \'' . $charset . "'; </p>\n");
                }
                $feedback .= "</div>";
            } else {
                $feedback .= mysql_error() . "<br />\n";
            }

            $feedback .= '<p class="z-statusmsg">' . __('Conversion to UTF8 completed - now move to next step') . "</p>\n";
            $converted = true;
        } else {
            $feedback .= '<p class="z-errormsg">' . __("Unable to connect to the database. Please check the details in config/config.php") . "</p>\n";
            $feedback .= mysql_error() . "<br />\n";
        }
    }

    // clear errors
    unset($_SESSION['ZSV_ZErrorMsg']);
    unset($_SESSION['ZSV_ZErrorMsgType']);
    unset($_SESSION['ZSV_ZStatusMsg']);

    if ($converted) {
        echo '<div>' . $feedback . "\n";
        _upg_continue('upgrademodules', __('Upgrade modules'), $username, $password);
        echo "</div>\n";
    }
    _upg_footer();
}

function _upg_upgrademodules($username, $password)
{
    _upg_header();

    // force load the modules admin API
    pnModAPILoad('Modules', 'admin', true);

    echo '<h2>' . __('Starting upgrade') . '</h2>' . "\n";
    echo '<ul id="upgradelist" class="check">' . "\n";
    $upgradeCount = 0;
    // regenerate modules list
    $filemodules = pnModAPIFunc('Modules', 'admin', 'getfilemodules');
    pnModAPIFunc('Modules', 'admin', 'regenerate', array('filemodules' => $filemodules));
    $modinfo = pnModGetInfo(pnModGetIDFromName('ObjectData'));
    if ($modinfo['state'] == PNMODULE_STATE_UPGRADED) {
        if (pnModAPIFunc('Modules', 'admin', 'upgrade', array('id' => pnModGetIDFromName('ObjectData')))) {
            echo '<li class="passed">' . ' ObjectData ' . ' ' . __('upgraded') . '</li>' . "\n";
            $upgradeCount++;
        } else {
            echo '<li class="failed">' . ' ObjectData ' . ' ' . __('not upgraded') . '</li>' . "\n";
        }
    }
    if (pnModDBInfoLoad('Profile') && !DBUtil::changeTable('user_property')) {
        return false;
    }
    $filemodules = pnModAPIFunc('Modules', 'admin', 'getfilemodules');
    pnModAPIFunc('Modules', 'admin', 'regenerate', array('filemodules' => $filemodules));
    // get a list of modules needing upgrading
    $newmods = pnModAPIFunc('Modules', 'admin', 'list', array('state' => PNMODULE_STATE_UPGRADED, 'type' => 3));

    // Crazy sort to make sure the User's module is upgraded first
    $users_flag = false;
    $newmodsArray = array();
    foreach ($newmods as $mod) {
        if ($mod['name'] == 'Users') {
            $usersModule[] = $mod;
            $users_flag = true;
        } else {
            $newmodsArray[] = $mod;
        }
    }
    if ($users_flag) {
        $newmods = $usersModule;
        foreach ($newmodsArray as $mod) {
            $newmods[] = $mod;
        }
    }
    $newmods = array_merge($newmods, pnModAPIFunc('Modules', 'admin', 'list', array('state' => PNMODULE_STATE_UPGRADED, 'type' => 2)));
    $newmods = array_merge($newmods, pnModAPIFunc('Modules', 'admin', 'list', array('state' => PNMODULE_STATE_UPGRADED, 'type' => 1)));
    if (is_array($newmods) && !empty($newmods)) {
        foreach ($newmods as $newmod) {
            ZLanguage::bindModuleDomain($newmod['name']);
            if (pnModAPIFunc('Modules', 'admin', 'upgrade', array('id' => $newmod['id']))) {
                echo '<li class="passed">' . DataUtil::formatForDisplay($newmod['name']) . ' ' . __('upgraded') . '</li>' . "\n";
                $upgradeCount++;
            } else {
                echo '<li class="failed">' . DataUtil::formatForDisplay($newmod['name']) . ' ' . __('not upgraded') . '</li>' . "\n";
            }
        }

    }
    echo '</ul>' . "\n";
    if ($upgradeCount == 0) {
        echo '<ul class="check"><li class="passed">' . __('No modules required upgrading') . '</li></ul>';
    }


    // regenerate the modules list to pick up any final changes
    // suppress warnings because we did some upgrade black magic which will harmless generate an E_NOTICE
    @pnModAPIFunc('Modules', 'admin', 'regenerate');

    // regenerate the themes list
    pnModAPIFunc('Theme', 'admin', 'regenerate');

    // store the recent version in a config var for later usage. This enables us to determine the version we are upgrading from
    pnConfigSetVar('Version_Num', PN_VERSION_NUM);
    pnConfigSetVar('language_i18n', ZLanguage::getLanguageCode());
    pnConfigSetVar('language_bc', 0);

    // Relogin the admin user to give a proper admin link
    SessionUtil::requireSession();

    echo '<p class="z-statusmsg">' . __('Finished upgrade') . " - \n";
    if (!pnUserLogin($username, $password)) {
        $url = sprintf('<a href="%s">%s</a>', DataUtil::formatForDisplay(pnGetBaseURL()), DataUtil::formatForDisplay(pnConfigGetVar('sitename')));
        echo __f('Go to the startpage for %s', $url);
    } else {
        upgrade_clear_caches();
        $url = sprintf('<a href="%s">%s</a>', DataUtil::formatForDisplay(pnGetBaseURL().'admin.php'), DataUtil::formatForDisplay(pnConfigGetVar('sitename')));
        echo __f('Go to the admin panel for %s', $url);
    }
    echo "</p>\n";

    _upg_footer();
}

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

function _upg_sanity_check($username, $password)
{
    _upg_header();

    $validupgrade = true;
    if (version_compare(_ZINSTALLEDVERSION, _ZINSTALLVER, '=')) {
        // Already installed the correct version
        $validupgrade = false;
        echo '<h2>' . __('Already up to date') . '</h2>' . "\n";
        echo '<p class="z-errormsg">' . __f("It seems that you have already installed version %s. Please remove this upgrade script, you do not need it anymore", _ZINSTALLEDVERSION) . '</p>' . "\n";
    } elseif (is_dir('system/Profile')) {
        $validupgrade = false;
        echo '<h2>' . __f('Duplicate %1$s module found in %2$s.', array('Profile', 'system/Profile')) . '</h2>' . "\n";
        echo '<p class="z-errormsg">' . __f('In order to proceed with this upgrade the duplicated %1$s module in <strong>system/</strong> should be removed.  Please delete %1$s from the system/ directory and re-run this script.', array('Profile', 'system')) . '</p>' . "\n";
        echo _upg_continue('sanitycheck', __('Check again'), $username, $password);
    } elseif (is_dir('system/legal')) {
        $validupgrade = false;
        echo '<h2>' . __f('Duplicate %1$s module found in %2$s.', array('legal', 'system/legal')) . '</h2>' . "\n";
        echo '<p class="z-errormsg">' . __f('In order to proceed with this upgrade the duplicated %1$s module in <strong>system/</strong> should be removed.  Please delete %1$s from the system/ directory and re-run this script.', array('legal', 'system')) . '</p>' . "\n";
        echo _upg_continue('sanitycheck', __('Check again'), $username, $password);
    } elseif (!is_dir('modules/Profile')) {
        $validupgrade = false;
        echo '<h2>' . __f('Missing %s module.', 'Profile') . '</h2>' . "\n";
        echo '<p class="z-errormsg">' . __f('In order to proceed with this upgrade the %1$s module must be present in the <strong>%2$s/</strong> directory.  Please copy the %1$s module to the %2$s/ directory and re-run this script.', array('Profile', 'modules')) . '</p>' . "\n";
        echo _upg_continue('sanitycheck', __('Check again'), $username, $password);
    } elseif (!is_dir('modules/legal')) {
        $validupgrade = false;
        echo '<h2>' . __f('Missing %s module.', 'legal') . '</h2>' . "\n";
        echo '<p class="z-errormsg">' . __f('In order to proceed with this upgrade the %1$s module must be present in the <strong>%2$s/</strong> directory.  Please copy the %1$s module to the %2$s/ directory and re-run this script.', array('legal', 'modules')) . '</p>' . "\n";
        echo _upg_continue('sanitycheck', __('Check again'), $username, $password);
    } elseif (version_compare(_ZINSTALLEDVERSION, _Z_MINUPGVER, '<')) {
        // Not on version _Z_MINUPGVER yet
        $validupgrade = false;
        echo '<h2>' . __('Possible incompatible version found.') . '</h2>' . "\n";
        echo '<p class="z-warningmsg">' . __f('The current installed version of Zikula is reporting (%1$s). You must upgrade to version (%2$s) before you can use this upgrade.', array(_ZINSTALLEDVERSION, _Z_MINUPGVER)) . '</p>';
    } elseif (!is_writeable('config/config.php')) {
        echo '<p class="z-errormsg">' . '<strong>' . __('config/config.php must be writable before this script will run. Please correct this and try again') . "</strong></p>\n";
        echo _upg_continue('sanitycheck', __('Check again'), $username, $password);
        $validupgrade = false;
    } elseif (file_exists('config/personal_config.php') && !is_writeable('config/config.php')) {
        echo '<p class="z-errormsg">' . '<strong>' . __('config/personal_config.php must be writable before this script will run. Please correct this and try again') . "</strong></p>\n";
        echo _upg_continue('sanitycheck', __('Check again'), $username, $password);
        $validupgrade = false;
    } elseif (version_compare(PHP_VERSION, '5.3.0', '>=')) {
        if (ini_get('date.timezone') == '') {
            echo '<p class="z-errormsg">' . '<strong>' . __('date.timezone is currently not set.  Since PHP 5.3.0, it needs to be set to a valid timezone in your php.ini such as timezone like UTC, GMT+5, Europe/Berlin.') . "</strong></p>\n";
            echo _upg_continue('sanitycheck', __('Check again'), $username, $password);
            $validupgrade = false;
        }
    }

    if (!$validupgrade) {
        _upg_footer();
        pnShutDown();
    }

    _upg_continue('convertdb', __('Proceed to convert the database to UTF8'), $username, $password);
    _upg_footer();
}

function doSQL($sql, $resource, &$feedback)
{
    $result = mysql_query($sql, $resource);
    if (!$result) {
        $feedback .= '<li class="failed">' . $sql . "</li>\n";
        $feedback .= '<li class="failed">' . mysql_error($resource) . "</li>\n";
    } else {
        $feedback .= '<li class="passed">' . $sql . "</li>\n";
    }
    return $result;
}

function upgrade_clear_caches()
{
    pnModAPIFunc('Theme', 'user', 'render_clear_compiled');
    pnModAPIFunc('Theme', 'user', 'render_clear_cache');
    pnModAPIFunc('Theme', 'user', 'clear_compiled');
    pnModAPIFunc('Theme', 'user', 'clear_cache');
}
