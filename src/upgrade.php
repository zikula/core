<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Installer
 *          Please see the NOTICE file distributed with this source code for further
 *          information regarding copyright and licensing.
 */

use Zikula_Request_Http as Request;
use Zikula\Core\Event\GenericEvent;
use Doctrine\ORM\EntityManager;

ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('default_charset', 'UTF-8');
mb_regex_encoding('UTF-8');
ini_set('memory_limit', '64M');
ini_set('max_execution_time', 86400);

include 'lib/bootstrap.php';
$request = Request::createFromGlobals();
$core->getContainer()->set('request', $request);

$eventManager = $core->getDispatcher();
$container = $core->getContainer();
$eventManager->addListener('core.init', 'upgrade_suppressErrors');

// load zikula core
define('_ZINSTALLVER', Zikula_Core::VERSION_NUM);
define('_Z_MINUPGVER', '1.3.5');

// Signal that upgrade is running.
$GLOBALS['_ZikulaUpgrader'] = array();
$dbname = $container['databases']['default']['dbname'];

// Lazy load DB connection to avoid testing DSNs that are not yet valid (e.g. no DB created yet)
$eventManager->dispatch('doctrine.boot', new GenericEvent());
/** @var $em EntityManager */
$em = $container->get('doctrine.entitymanager');
$connection = $em->getConnection();

upgrade_136($dbname, $connection);

$installedVersion = upgrade_getCurrentInstalledCoreVersion($connection);

$core->init(Zikula_Core::STAGE_ALL);

$action = FormUtil::getPassedValue('action', false, 'GETPOST');

// login to supplied admin credentials for action the following actions
if ($action === 'upgrademodules' || $action === 'convertdb' || $action === 'sanitycheck') {
    $username = FormUtil::getPassedValue('username', null, 'POST');
    $password = FormUtil::getPassedValue('password', null, 'POST');

    $authenticationInfo = array(
        'login_id' => $username,
        'pass'     => $password
    );
    $authenticationMethod = array(
        'modname' => 'ZikulaUsersModule',
        'method'  => 'uname',
    );
    if (!UserUtil::loginUsing($authenticationMethod, $authenticationInfo)) {
        // force action to login
        $action = 'login';
    } else {
        define('_ZINSTALLEDVERSION', $installedVersion);
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
 * This function generate the header of upgrade page.
 *
 * @return void
 */
function _upg_header()
{
    $lang = ZLanguage::getLanguageCode();
    $charset = ZLanguage::getEncoding();
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">'."\n";
    echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$lang.'">'."\n";
    echo '<head>'."\n";
    echo '<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />'."\n";
    echo '<title>'.__('Zikula Upgrade script')."</title>\n";
    echo '<link rel="stylesheet" href="install/style/installer.css" type="text/css" />'."\n";
    echo '<link rel="stylesheet" href="style/core.css" type="text/css" />'."\n";
    echo '<!--[if IE]><link rel="stylesheet" type="text/css" href="style/core_iehacks.css" media="print,projection,screen" /><![endif]-->'."\n";
    echo '<script src="javascript/ajax/proto_scriptaculous.combined.min.js" type="text/javascript"></script>'."\n";
    echo '<script src="install/javascript/install.js" type="text/javascript"></script>'."\n";
    echo '</head>'."\n";
    echo '<body>'."\n";
    echo '<div id="container"><div id="content">'."\n";
    echo '<div id="header">'."\n";
    echo '<h1>'.__('Zikula Application Framework').'</h1>'."\n";
    echo '<h2>'.__('Upgrade script').'</h2></div>'."\n";
    echo '<div id="maincontent">';
    if (UserUtil::isLoggedIn()) {
        echo '<h3>'.__f('Zikula Upgrade script (for Zikula version %s and up)', array(_Z_MINUPGVER)).'</h3>'."\n";
        echo '<p>'.__f('This script will upgrade any Zikula v%1$s+ installation. Upgrades from less than Zikula v%1$s are not supported by this script.', array(_Z_MINUPGVER))."</p>\n";
    }
}

/**
 * Generate the footer of upgrade page.
 * This function generate the footer of upgrade page.
 *
 * @return void
 */
function _upg_footer()
{
    $lang = ZLanguage::getLanguageCode();
    echo '</div></div>'."\n";
    echo '<div id="footer">'."\n";
    echo '<p id="notice">'.__f('For more information about the upgrade process, please read the <a href="docs/%1$s/UPGRADING">upgrade documentation</a>, visit our <a href="http://community.zikula.org/Wiki.htm">wiki</a> or the <a href="http://community.zikula.org/module-Forum.htm">support forum</a>.', $lang).'</p>';
    echo '</div>'."\n";
    echo '</div></body>'."\n";
    echo '</html>';
    exit();
}

/**
 * Generate the language selector page.
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
        echo '<h2>'.__('FATAL ERROR!')."</h2>\n";
        echo '<p class="z-errormsg">'.__("Zikula does not appear to be installed.")."</p>\n";
    }

    if (!$validupgrade) {
        _upg_footer();
        System::shutdown();
    }

    $curlang = ZLanguage::getLanguageCode();
    echo '<form class="z-form" action="upgrade.php" method="get" enctype="application/x-www-form-urlencoded">'."\n";
    echo '<fieldset><legend>'.__('Please select your language').'</legend>'."\n";
    echo '<input type="hidden" name="action" value="upgradeinit" />'."\n";
    echo '<div class="z-formrow"><label for="lang">'.__('Choose a language').'</label>'."\n";
    echo '<select id="lang" name="lang">'."\n";
    $langs = ZLanguage::getInstalledLanguageNames();
    foreach ($langs as $lang => $name) {
        $selected = ($lang == $curlang ? ' selected="selected"' : '');
        echo '<option value="'.$lang.'" label="'.$name.'"'.$selected.'>'.$name."</option>\n";
    }
    echo '</select></div></fieldset>'."\n";
    echo '<div class="z-buttons z-center"><input type="submit" value="'.__('Submit').'" /></div>'."\n";
    echo '</form>'."\n";
    _upg_footer();
}

/**
 * Upgrade initial.
 * Display initial upgrade page.
 *
 * @return void
 */
function _upg_upgradeinit()
{
    _upg_header();

    echo '<h2>'.__('BEFORE proceeding you should backup your database!').'</h2>'."\n";
    _upg_login(false);

    _upg_footer();
}

/**
 * Generate the login bloc of login page.
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
        echo '<p class="z-errormsg">'.__('Failed to login to your site').'</p>'."\n";
    }
    echo '<p>'.__('For the next upgrade steps you need to be logged in. Please provide your admin account credentials').'</p>'."\n";
    echo '<form class="z-form" action="upgrade.php?lang='.$lang.'" method="post" enctype="application/x-www-form-urlencoded">'."\n";
    echo '<fieldset><legend>'.__('Log-in').'</legend>'."\n";
    echo '<div class="z-formrow"><label for="username">'.__('User name').'</label><input id="username" type="text" name="username" size="60" maxlength="25" /></div>'."\n";
    echo '<div class="z-formrow"><label for="password">'.__('Password').'</label><input id="password" type="password" name="password" size="25" maxlength="40" /></div>'."\n";
    echo '<input type="hidden" name="action" value="sanitycheck" />'."\n";

    if ($lang != null) {
        echo '<input type="hidden" name="lang" value="'.htmlspecialchars($lang).'" />'."\n";
    }
    echo '</fieldset>'."\n";
    echo '<div class="z-buttons z-center"><input name="submit" type="submit" value="'.__('Submit').'" /></div>'."\n";
    echo '</form>'."\n";
    if ($showheader == true) {
        _upg_footer();
    }
}

/**
 * Generate the upgrade module page.
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

    // force load the modules admin API
    ModUtil::loadApi('ZikulaExtensionsModule', 'admin', true);

    echo '<h2>'.__('Starting upgrade').'</h2>'."\n";
    echo '<ul id="upgradelist" class="check">'."\n";

    $results = ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'upgradeall');
    if ($results) {
        foreach ($results as $modname => $result) {
            if ($result) {
                echo '<li class="passed">'.DataUtil::formatForDisplay($modname).' '.__('upgraded').'</li>'."\n";
            } else {
                echo '<li class="failed">'.DataUtil::formatForDisplay($modname).' '.__('not upgraded').'</li>'."\n";
            }
        }
    }
    echo '</ul>'."\n";
    if (!$results) {
        echo '<ul class="check"><li class="passed">'.__('No modules required upgrading').'</li></ul>';
    }

    // regenerate the themes list
    ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'regenerate');

    // store the recent version in a config var for later usage. This enables us to determine the version we are upgrading from
    System::setVar('Version_Num', Zikula_Core::VERSION_NUM);
    System::setVar('language_i18n', ZLanguage::getLanguageCode());

    // Relogin the admin user to give a proper admin link
    SessionUtil::requireSession();

    echo '<p class="z-statusmsg">'.__('Finished upgrade')." - \n";

    $authenticationInfo = array(
        'login_id' => $username,
        'pass'     => $password
    );
    $authenticationMethod = array(
        'modname' => 'ZikulaUsersModule',
        'method'  => 'uname',
    );

    if (!UserUtil::loginUsing($authenticationMethod, $authenticationInfo)) {
        $url = sprintf('<a href="%s">%s</a>', DataUtil::formatForDisplay(System::getBaseUrl()), DataUtil::formatForDisplay(System::getVar('sitename')));
        echo __f('Go to the startpage for %s', $url);
    } else {
        upgrade_clear_caches();
        $url = sprintf('<a href="%s">%s</a>', ModUtil::url('ZikulaAdminModule', 'admin', 'adminpanel'), DataUtil::formatForDisplay(System::getVar('sitename')));
        echo __f('Go to the admin panel for %s', $url);
    }
    echo "</p>\n";

    _upg_footer();
}

/**
 * Generate the button for the next step of the upgrade.
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
    echo '<form class="z-form z-linear" method="post" action="upgrade.php?lang='.$lang."\">\n";
    echo '<div><fieldset><legend>'.DataUtil::formatForDisplay($text).'</legend>'."\n";
    if ($username != null && $password != null) {
        echo '<input type="hidden" name="username" value="'.DataUtil::formatForDisplay($username).'" />'."\n";
        echo '<input type="hidden" name="password" value="'.DataUtil::formatForDisplay($password).'" />'."\n";
    }
    echo '<input type="hidden" name="action" value="'.htmlspecialchars($action).'" />'."\n";
    echo '<div class="z-buttons z-center"><input type="submit" name="submit" value="'.htmlspecialchars($text).'" /></div>'."\n";
    echo '</fieldset></div>'."\n";
    echo '</form>'."\n";

    return;
}

/**
 * Generate the sanity check page.
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
        echo '<h2>'.__('Already up to date')."</h2>\n";
        echo '<p class="z-errormsg">'.__f("It seems that you have already installed version %s. Please remove this upgrade script, you do not need it anymore.", _ZINSTALLEDVERSION)."</p>\n";
    } elseif (version_compare(_ZINSTALLEDVERSION, _Z_MINUPGVER, '<')) {
        // Not on version _Z_MINUPGVER yet
        $validupgrade = false;
        echo '<h2>'.__('Possible incompatible version found.')."</h2>\n";
        echo '<p class="z-warningmsg">'.__f('The current installed version of Zikula is reporting (%1$s). You must upgrade to version (%2$s) before you can use this upgrade.', array(_ZINSTALLEDVERSION, _Z_MINUPGVER))."</p>\n";
    } elseif (version_compare(PHP_VERSION, '5.3.0', '>=')) {
        if (ini_get('date.timezone') == '') {
            echo '<p class="z-errormsg"><strong>'.__('date.timezone is currently not set. Since PHP 5.3.0, it needs to be set to a valid timezone in your php.ini such as timezone like UTC, GMT+5, Europe/Berlin.')."</strong></p>\n";
            _upg_continue('sanitycheck', __('Check again'), $username, $password);
            $validupgrade = false;
        }
    } elseif (version_compare(Zikula_Core::VERSION_NUM, '1.3.6', '>=') && (is_dir('plugins/Doctrine') || is_dir('plugins/DoctrineExtensions'))) {
        echo '<h2>'.__('Legacy plugins found.')."</h2>\n";
        echo '<p class="z-warningmsg">'.__f('Please delete the folders <strong>plugins/Doctrine</strong> and <strong>plugins/DoctrineExtensions</strong> as they have been deprecated', array(_ZINSTALLEDVERSION, _Z_MINUPGVER))."</p>\n";
        $validupgrade = false;
    }

    if ($validupgrade) {
        $pcreUnicodePropertiesEnabled = @preg_match('/^\p{L}+$/u', 'TheseAreLetters');
        if (!isset($pcreUnicodePropertiesEnabled) || !$pcreUnicodePropertiesEnabled) {
            // PCRE Unicode property support is not enabled.
            $validupgrade = false;
            echo '<h2>'.__('PCRE Unicode Property Support Needed.')."</h2>\n";
            echo '<p class="z-errormsg">'.__('The PCRE (Perl Compatible Regular Expression) library being used with your PHP installation does not support Unicode properties. This is required to handle multi-byte character sets in regular expressions. The PCRE library used must be compiled with the \'--enable-unicode-properties\' option.')."</p>\n";
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
 * @param \Doctrine\DBAL\Connection $connection PDO connection.
 *
 * @return string
 */
function upgrade_getCurrentInstalledCoreVersion(\Doctrine\DBAL\Connection $connection)
{
    $moduleTable = 'module_vars';

    $stmt = $connection->executeQuery("SELECT value FROM $moduleTable WHERE modname = 'ZConfig' AND name = 'Version_Num'");

    $result = $stmt->fetch(PDO::FETCH_NUM);

    return unserialize($result[0]);
}

/**
 * Upgrade tables from 1.3.5
 *
 * @param                           $dbname
 * @param \Doctrine\DBAL\Connection $conn
 */
function upgrade_136($dbname, \Doctrine\DBAL\Connection $conn)
{
    $res = $conn->executeQuery("SELECT name FROM $dbname.modules WHERE name = 'ZikulaExtensionsModule'");
    if ($res->fetch()) {
        // nothing to do, already converted.
        return;
    }

    $modules = array(
        'Admin', 'Blocks', 'Categories', 'Errors', 'Extensions', 'Groups',
        'Mailer', 'PageLock', 'Permissions', 'Search', 'SecurityCenter',
        'Settings', 'Theme', 'Users',
    );

    foreach ($modules as $module) {
        $conn->executeQuery("UPDATE $dbname.modules SET name = 'Zikula{$module}Module', directory = 'Zikula/Module/{$module}Module' WHERE name = '$module'");
        $conn->executeQuery("UPDATE $dbname.module_vars SET modname = 'Zikula{$module}Module' WHERE modname = '$module'");
        echo "Updated module: $module<br />\n";
    }
    echo "<br />\n";

    $themes = array(
        'Andreas08', 'Atom', 'SeaBreeze', 'Mobile', 'Printer',
    );
    foreach ($themes as $theme) {
        $conn->executeQuery("UPDATE $dbname.themes SET name = 'Zikula{$theme}Theme', directory = 'Zikula/Theme/{$theme}Theme' WHERE name = '$theme'");
        echo "Updated theme: $theme<br />\n";
    }
    $conn->executeQuery("UPDATE $dbname.themes SET name = 'ZikulaRssTheme', directory = 'Zikula/Theme/RssTheme' WHERE name = 'RSS'");
    echo "Updated theme: RSS<br />\n";

//$conn->executeQuery("UPDATE $dbname.module_vars SET value = 'ZikulaAndreas08Theme' WHERE modname = 'ZConfig' AND value='Default_Theme'");
//echo "Updated default theme to Andreas08<br />\n";
}
