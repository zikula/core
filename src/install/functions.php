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
function install()
{
    // configure our installation environment
    // no time limit since installation might take a while
    // error reporting level for debugging
    ini_set('max_execution_time', 86400);
    ini_set('memory_limit', '64M');

    $installbySQL = (file_exists('install/sql/custom.sql') ? true : false);
;
    require_once 'install/modify_config.php';

    // start the basics of Zikula
    include 'lib/ZLoader.php';
    ZLoader::register();

    define('_ZINSTALLVER', Zikula_Core::VERSION_NUM);

    $core = new Zikula_Core();
    $core->boot();
    $eventManager = $core->getEventManager();
    $serviceManager = $core->getServiceManager();

    require 'config/config.php';
    $serviceManager->loadArguments($GLOBALS['ZConfig']['Log']);
    $serviceManager->loadArguments($GLOBALS['ZConfig']['Debug']);
    $serviceManager->loadArguments($GLOBALS['ZConfig']['System']);
    $serviceManager->loadArguments($GLOBALS['ZConfig']['Multisites']);

    // Lazy load DB connection to avoid testing DSNs that are not yet valid (e.g. no DB created yet)
    DBConnectionStack::init('default', true);

    $core->init(Zikula_Core::STAGE_ALL & ~Zikula_Core::STAGE_THEME & ~Zikula_Core::STAGE_MODS & ~Zikula_Core::STAGE_LANGS & ~Zikula_Core::STAGE_DECODEURLS & ~Zikula_Core::STAGE_SESSIONS);

    // get our input
    $vars = array(
            'lang',
            'dbhost',
            'dbusername',
            'dbpassword',
            'dbname',
            'dbprefix',
            'dbtype',
            'dbtabletype',
            'createdb',
            'username',
            'password',
            'repeatpassword',
            'email',
            'action',
            'loginuser',
            'loginpassword',
            'defaulttheme');

    foreach ($vars as $var) {
        // in the install we're sure we don't wany any html so we can be stricter than
        // the FormUtil::getPassedValue API
        $$var = strip_tags(stripslashes(FormUtil::getPassedValue($var, '', 'GETPOST')));
    }

    // Power users might have moved the temp folder out of the root and changed the config.php
    // accordingly. Make sure we respect this security related settings
    $tempDir = (isset($GLOBALS['ZConfig']['System']['temp']) ? $GLOBALS['ZConfig']['System']['temp'] : 'ztemp');
    $dataDir = (isset($GLOBALS['ZConfig']['System']['datadir']) ? $GLOBALS['ZConfig']['System']['datadir'] : 'data');

    // define our smarty object
    $smarty = new Smarty();
    $smarty->left_delimiter = '{';
    $smarty->right_delimiter = '}';
    $smarty->compile_dir = $tempDir . '/view_compiled';
    $smarty->template_dir = 'install/templates';
    $smarty->plugins_dir = array(
            'plugins',
            'install/templates/plugins',
            'lib/view/plugins');

    // load the installer language files
    if (empty($lang)) {
        $available = ZLanguage::getInstalledLanguages();
        $detector = new ZLanguageBrowser($available);
        $lang = $detector->discover();
    }

    // setup multilingual
    $GLOBALS['ZConfig']['System']['language_i18n'] = $lang;
    $GLOBALS['ZConfig']['System']['multilingual'] = true;
    $GLOBALS['ZConfig']['System']['languageurl'] = true;
    $GLOBALS['ZConfig']['System']['language_detect'] = false;
    $serviceManager->loadArguments($GLOBALS['ZConfig']['System']);

    $_lang = ZLanguage::getInstance();
    $_lang->setup();

    $lang = ZLanguage::getLanguageCode();

    $smarty->assign('lang', $lang);
    $smarty->assign('installbySQL', $installbySQL);
    $smarty->assign('langdirection', ZLanguage::getDirection());
    $smarty->assign('charset', ZLanguage::getEncoding());

    // assign the values from config.php
    $smarty->assign($GLOBALS['ZConfig']['System']);

    // if the system is already installed, require login
    if ($GLOBALS['ZConfig']['System']['installed']) {
        $action = _forcelogin($action);
    }

    if ($GLOBALS['ZConfig']['System']['installed'] && !isset($_GET['lang'])) {
        // // need auth because Zikula is already installed.
        _installer_alreadyinstalled($smarty);
    }

    // check for an empty action - if so then show the first installer page
    if (empty($action)) {
        $action = 'lang';
    }

    // perform tasks based on our action
    switch ($action) {
        case 'login' :
            if (empty($loginuser) && empty($loginpassword)) {

            } elseif (UserUtil::loginUsing('Users', array('loginid' => $loginuser, 'pass' => $loginpassword), null, false)) {
                if (!SecurityUtil::checkPermission('.*', '.*', ACCESS_ADMIN)) {
                    // not admin user so boot
                    UserUtil::logout();
                    $action = 'login';
                    $smarty->assign(array('loginstate' => 'notadmin'));
                } else {
                    $action = 'lang';
                }
            } else {
                // not a valid user
                $smarty->assign(array('loginstate' => 'failed'));
            }
            break;

        case 'processBDInfo':
            $dbname = trim($dbname);
            $dbusername = trim($dbusername);
            if (empty($dbname) || empty($dbusername)) {
                $action = 'dbinformation';
                $smarty->assign('dbconnectmissing', true);
            } elseif (preg_match('/\W/', $dbprefix)) {
                $action = 'dbinformation';
                $smarty->assign('dbinvalidprefix', true);
            } elseif (!preg_match('/^[\w-]*$/', $dbname) ||
                      strlen($dbname) > 64) {
                $action = 'dbinformation';
                $smarty->assign('dbinvalidname', true);
            } else {
                update_config_php($dbhost, $dbusername, $dbpassword, $dbname, $dbprefix, $dbtype, $dbtabletype);
                update_installed_status('0');
                // Easier to create initial DB direct with PDO
                if ($createdb) {
                    try {
                        $dbh = new PDO("$dbtype:host=$dbhost", $dbusername, $dbpassword);
                        makedb($dbh, $dbname, $dbtype);
                    } catch (PDOException $e) {
                        $action = 'dbinformation';
                        $smarty->assign('reason', $e->getMessage());
                        $smarty->assign('dbcreatefailed', true);
                    }
                } else {
                    try {
                        $dbh = new PDO("$dbtype:host=$dbhost;dbname=$dbname", $dbusername, $dbpassword);
                    } catch (PDOException $e) {
                        $action = 'dbinformation';
                        $smarty->assign('reason', $e->getMessage());
                        $smarty->assign('dbconnectfailed', true);
                    }
                }
            }
            if ($action != 'dbinformation') {
                $action = 'createadmin';
            }
            break;

        case 'finish':
            if ((!$username) || preg_match('/[^\p{L}\p{N}_\.\-]/u', $username)) {
                $action = 'createadmin';
                $smarty->assign('uservalidatefailed', true);
                $smarty->assign(array(
                        'username' => $username,
                        'password' => $password,
                        'repeatpassword' => $repeatpassword,
                        'email' => $email));
            } elseif (mb_strlen($password) < 7) {
                $action = 'createadmin';
                $smarty->assign('badpassword', true);
                $smarty->assign(array(
                        'username' => $username,
                        'password' => $password,
                        'repeatpassword' => $repeatpassword,
                        'email' => $email));
            } elseif ($password !== $repeatpassword) {
                $action = 'createadmin';
                $smarty->assign('passwordcomparefailed', true);
                $smarty->assign(array(
                        'username' => $username,
                        'password' => $password,
                        'repeatpassword' => $repeatpassword,
                        'email' => $email));
            } elseif (!validateMail($email)) {
                $action = 'createadmin';
                $smarty->assign('emailvalidatefailed', true);
                $smarty->assign(array(
                        'username' => $username,
                        'password' => $password,
                        'repeatpassword' => $repeatpassword,
                        'email' => $email));
            } else {
                // create database
                // if it is the distribution and the process have not failed in a previous step
                if ($installbySQL) {
                    // checks if exists a previous installation with the same prefix
                    $proceed = true;
                    $exec = ($dbtype == 'mysql' || $dbtype == 'mysqli') ? 
                            "SHOW TABLES FROM `$dbname` LIKE '" . $dbprefix . "_%'" :
                            "SHOW TABLES FROM $dbname LIKE '" . $dbprefix . "_%'";
                    $tables = DBUtil::executeSQL($exec);
                    if ($tables->rowCount() > 0) {
                        $proceed = false;
                        $action = 'dbinformation';
                        $smarty->assign('dbexists', true);
                    }
                    if ($proceed) {
                        // create the database
                        // set sql dump file path
                        $fileurl = 'install/sql/custom.sql';
                        // checks if file exists
                        if (!file_exists($fileurl)) {
                            $action = 'dbinformation';
                            $smarty->assign('dbdumpfailed', true);
                        } else {
                            // execute the SQL dump
                            $lines = file($fileurl);
                            $exec = '';
                            foreach ($lines as $line_num => $line) {
                                $line = trim($line);
                                if (empty($line) || strpos($line, '--') === 0) continue;
                                $exec .= $line;
                                if (strrpos($line, ';') === strlen($line) - 1) {
                                    if (!DBUtil::executeSQL(str_replace('z_', $dbprefix . '_', $exec))) {
                                        $action = 'dbinformation';
                                        $smarty->assign('dbdumpfailed', true);
                                        break;
                                    }
                                    $exec = '';
                                }
                            }
                        }
                    }
                } else {
                    installmodules($lang);
                }
                // create our new site admin
                // TODO test the call the users module api to create the user
                //ModUtil::apiFunc('Users', 'user', 'finishnewuser', array('uname' => $username, 'email' => $email, 'pass' => $password));
                createuser($username, $password, $email);
                SessionUtil::requireSession();
                UserUtil::loginUsing('Users', array('loginid' => $username, 'pass' => $password));

                // add admin email as site email
                System::setVar('adminmail', $email);
            }
            break;

        case 'gotosite':
            if (!$installbySQL) {
                ModUtil::apiFunc('Theme', 'admin', 'regenerate');
            }
            // set site status as installed and protect config.php file
            update_installed_status();
            @chmod('config/config.php', 0400);
            if (!is_readable('config/config.php')) {
                @chmod('config/config.php', 0440);
                if (!is_readable('config/config.php')) {
                    @chmod('config/config.php', 0444);
                }
            }
            // install all plugins
            $systemPlugins = PluginUtil::loadAllSystemPlugins();
            foreach ($systemPlugins as $plugin) {
                PluginUtil::install($plugin);
            }

            SessionUtil::requireSession();
            if (!UserUtil::isLoggedIn()) {
                return System::redirect();
            } else {
                return System::redirect(ModUtil::url('Admin', 'admin', 'adminpanel'));
            }
    }

    // assign some generic variables
    $smarty->assign(compact($vars));

    // check our action template exists
    $action = DataUtil::formatForOS($action);
    if ($smarty->template_exists("installer_$action.tpl")) {
        $smarty->assign('action', $action);
        $templatename = "install/templates/installer_$action.tpl";
    } else {
        $smarty->assign('action', 'error');
        $templatename = 'install/templates/installer_error.tpl';
    }

    // at this point we now have all the information requried to display
    // the output. We don't use normal smarty functions here since we
    // want to avoid the need for a template compilation directory
    // TODO: smarty kicks up some odd errors when eval'ing templates
    // this way so the evaluation is suppressed.
    // get and evaluate the action specific template and assign to our
    // main smarty object as a new template variable
    $template = file_get_contents($templatename);
    $smarty->_compile_source('evaluated template', $template, $_var_compiled);
    ob_start();
    @$smarty->_eval('?>' . $_var_compiled);
    $_includecontents = ob_get_contents();
    ob_end_clean();
    $smarty->assign('maincontent', $_includecontents);

    // get and evaluate the page template
    $template = file_get_contents('install/templates/installer_page.tpl');
    $smarty->_compile_source('evaluated template', $template, $_var_compiled);
    ob_start();
    @$smarty->_eval('?>' . $_var_compiled);
    $_contents = ob_get_contents();
    ob_end_clean();

    // echo our final result - the combination of the two templates
    echo $_contents;
}

/**
 * Creates the DB on new install
 *
 * This function creates the DB on new installs.
 *
 * @param string $dbconn Database connection.
 * @param string $dbname Database name.
 */
function makedb($dbh, $dbname, $dbtype)
{
    switch ($dbtype) {
        case 'mysql':
        case 'mysqli':
            $query = "CREATE DATABASE `$dbname` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci";
            break;
        case 'pgsql':
            $query = "CREATE DATABASE $dbname ENCODING='utf8'";
            break;
        case 'oci':
            $query = "CREATE DATABASE $dbname national character set utf8";
            break;
    }

    try {
        $dbh->query($query);
    } catch (PDOException $e) {
        throw new PDOException($e);
    }
}

/**
 * This function inserts the default data on new installs
 */
function createuser($username, $password, $email)
{
    $connection = Doctrine_Manager::connection();
    $connection->setCharset(DBConnectionStack::getConnectionDBCharset());
    $connection->setCollate(DBConnectionStack::getConnectionDBCollate());

    // get the database connection
    ModUtil::dbInfoLoad('Users', 'Users');
    ModUtil::dbInfoLoad('Extensions', 'Extensions');
    $dbtables = DBUtil::getTables();

    // create the password hash
    $password = UserUtil::getHashedPassword($password);

    // prepare the data
    $username = mb_strtolower(DataUtil::formatForStore($username));
    $password = DataUtil::formatForStore($password);
    $email = mb_strtolower(DataUtil::formatForStore($email));

    $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
    $nowUTCStr = $nowUTC->format(UserUtil::DATETIME_FORMAT);

    // create the admin user
    $sql = "UPDATE {$dbtables['users']}
            SET    z_uname        = '{$username}',
                   z_email        = '{$email}',
                   z_pass         = '{$password}',
                   z_activated    = 1,
                   z_user_regdate = '{$nowUTCStr}',
                   z_lastlogin    = '{$nowUTCStr}'
            WHERE  z_uid   = 2";

    $result = DBUtil::executeSQL($sql);

    return ($result) ? true : false;
}

function installmodules($lang = 'en')
{
    $connection = Doctrine_Manager::connection();
    $connection->setCharset(DBConnectionStack::getConnectionDBCharset());
    $connection->setCollate(DBConnectionStack::getConnectionDBCollate());

    static $modscat;

    // Lang validation
    $lang = DataUtil::formatForOS($lang);

    // create a result set
    $results = array();

    $sm = ServiceUtil::getManager();
    $em = EventUtil::getManager();

    $coremodules = array('Extensions',
			 'Settings',
			 'Theme',
			 'Admin',
			 'Permissions',
			 'Groups',
			 'Blocks',
			 'Users',
                        );

    // manually install the modules module
    foreach ($coremodules as $coremodule) {
        // sanity check - check if module is already installed
        if ($coremodule != 'Extensions' && ModUtil::available($coremodule)) {
            continue;
        }

        $modpath = 'system';
        if (is_dir("$modpath/$coremodule/lib")) {
            ZLoader::addAutoloader($coremodule, "$modpath/$coremodule/lib");
        }

        $bootstrap = "$modpath/$coremodule/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        ModUtil::dbInfoLoad($coremodule, $coremodule);
        $className = "{$coremodule}_Installer";
        $instance = new $className($sm);
        if ($instance->install()) {
            $results[$coremodule] = true;
        }
    }

    // regenerate modules list
    $filemodules = ModUtil::apiFunc('Extensions', 'admin', 'getfilemodules');
    ModUtil::apiFunc('Extensions', 'admin', 'regenerate',
                      array('filemodules' => $filemodules));

    // set each of the core modules to active
    reset($coremodules);
    foreach ($coremodules as $coremodule) {
        $mid = ModUtil::getIdFromName($coremodule, true);
        ModUtil::apiFunc('Extensions', 'admin', 'setstate',
                          array('id' => $mid,
                                'state' => ModUtil::STATE_INACTIVE));
        ModUtil::apiFunc('Extensions', 'admin', 'setstate',
                          array('id' => $mid,
                                'state' => ModUtil::STATE_ACTIVE));
    }
    // Add them to the appropriate category
    reset($coremodules);

    $coremodscat = array('Extensions' => __('System'),
		         'Permissions' => __('Users'),
		         'Groups' => __('Users'),
		         'Blocks' => __('Layout'),
		         'Users' => __('Users'),
		         'Theme' => __('Layout'),
		         'Admin' => __('System'),
		         'Settings' => __('System'));

    $categories = ModUtil::apiFunc('Admin', 'admin', 'getall');
    $modscat = array();
    foreach ($categories as $category) {
        $modscat[$category['catname']] = $category['cid'];
    }
    foreach ($coremodules as $coremodule) {
        $category = $coremodscat[$coremodule];
        ModUtil::apiFunc('Admin', 'admin', 'addmodtocategory',
                          array('module' => $coremodule,
                                'category' => $modscat[$category]));
    }
    // create the default blocks.
    $blockInstance = new Blocks_Installer($sm);
    $blockInstance->defaultdata();

    // install all the basic modules
    $modules =array(array('module'   => 'SecurityCenter',
                          'category' => __('Security')),
                    array('module'   => 'Tour',
                          'category' => __('Content')),
                    array('module'   => 'Categories',
                          'category' => __('Content')),
                    array('module'   => 'Legal',
                          'category' => __('Content')),
                    array('module'   => 'Mailer',
                          'category' => __('System')),
                    array('module'   => 'Errors',
                          'category' => __('System')),
                    array('module'   => 'Theme',
                          'category' => __('Layout')),
                    array('module'   => 'Search',
                          'category' => __('Content')),
                    array('module'   => 'SysInfo',
                          'category' => __('Security')));

    foreach ($modules as $module) {
        // sanity check - check if module is already installed
        if (ModUtil::available($module['module'])) {
            continue;
        }
        $modpath = 'modules';
        if (is_dir("$modpath/$module/lib")) {
            ZLoader::addAutoloader($module, "$modpath/$module/lib");
        }
        $bootstrap = "$modpath/$module/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        ZLanguage::bindModuleDomain($module);

        $results[$module['module']] = false;

        // #6048 - prevent trying to install modules which are contained in an install type, but are not available physically
        if (!file_exists('system/' . $module['module'] . '/') && !file_exists('modules/' . $module['module'] . '/')) {
            continue;
        }

        $mid = ModUtil::getIdFromName($module['module']);

        // init it
        if (ModUtil::apiFunc('Extensions', 'admin', 'initialise',
                              array('id' => $mid)) == true) {
            // activate it
            if (ModUtil::apiFunc('Extensions', 'admin', 'setstate',
                                  array('id' => $mid,
                                        'state' => ModUtil::STATE_ACTIVE))) {
                $results[$module['module']] = true;
            }
            // Set category
            ModUtil::apiFunc('Admin', 'admin', 'addmodtocategory',
                              array('module' => $module['module'],
                                    'category' => $modscat[$module['category']]));
        }
    }

    System::setVar('language_i18n', $lang);
    return $results;
}

function _forcelogin($action = '')
{
    // login to supplied admin credentials
    if ($GLOBALS['ZConfig']['System']['installed']) { // need auth because Zikula is already installed.
        $dsnParts = Doctrine_Manager::getInstance()->parseDsn($GLOBALS['ZConfig']['DBInfo']['default']['dsn']);
        $connInfo = array();
        $connInfo['dbtype'] = strtolower($dsnParts['scheme']);
        $connInfo['dbhost'] = $dsnParts['host'];
        $connInfo['dbname'] = $dsnParts['database'];
        $connInfo['prefix'] = System::getVar('prefix') . '_';

        try {
            $dbh = new PDO("$connInfo[dbtype]:host=$connInfo[dbhost];dbname=$connInfo[dbname]", $dsnParts['user'], $dsnParts['pass']);
        } catch (PDOException $e) {
            header('HTTP/1.1 503 Service Unavailable');
            $templateFile = 'dbconnectionerror.tpl';
            if (file_exists("config/templates/$templateFile")) {
                include "config/templates/$templateFile";
            } else {
                include "system/Theme/templates/system/$templateFile";
            }
            System::shutDown();
        }

        ServiceUtil::getManager()->getService('zikula')->init(Zikula_Core::STAGE_SESSIONS);
        if (UserUtil::isLoggedIn()) {
            if (!SecurityUtil::checkPermission('.*', '.*', ACCESS_ADMIN)) {
                UserUtil::logout(); // not administrator user so boot them.
                $action = 'login';
            }
        } else { // login failed
            $action = 'login';
        }
    }

    return $action;
}

function _installer_alreadyinstalled(Smarty $smarty)
{
    header('HTTP/1.1 400 Bad Request');
    $smarty->display('installer_alreadyinstalled.tpl');
    System::shutDown();
    exit;
}

function validateMail($mail)
{
    if (!preg_match('/^(?:[^\s\000-\037\177\(\)<>@,;:\\"\[\]]\.?)+@(?:[^\s\000-\037\177\(\)<>@,;:\\\"\[\]]\.?)+\.[a-z]{2,6}$/Ui', $mail)) {
        return false;
    }

    return true;
}
