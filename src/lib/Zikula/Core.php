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

// Defines for access levels
define('ACCESS_INVALID', -1);
define('ACCESS_NONE', 0);
define('ACCESS_OVERVIEW', 100);
define('ACCESS_READ', 200);
define('ACCESS_COMMENT', 300);
define('ACCESS_MODERATE', 400);
define('ACCESS_EDIT', 500);
define('ACCESS_ADD', 600);
define('ACCESS_DELETE', 700);
define('ACCESS_ADMIN', 800);
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('default_charset', 'UTF-8');
mb_regex_encoding('UTF-8');

/**
 * System class.
 *
 * Core class with the base methods.
 */
class Zikula_Core
{
    /**
     * The core Zikula version number.
     */
    const VERSION_NUM = '1.3.0-dev';

    /**
     * The version ID.
     */
    const VERSION_ID = 'Zikula';

    /**
     * The version sub-ID.
     */
    const VERSION_SUB = 'vai';

    const STAGE_NONE = 0;
    const STAGE_PRE = 1;
    const STAGE_POST = 2;
    const STAGE_CONFIG = 4;
    const STAGE_DB = 8;
    const STAGE_TABLES = 16;
    const STAGE_SESSIONS = 32;
    const STAGE_LANGS = 64;
    const STAGE_MODS = 128;
    const STAGE_DECODEURLS = 1024;
    const STAGE_THEME = 2048;
    const STAGE_ALL = 4095;
    const STAGE_AJAX = 4096; // needs to be set explicitly, STAGE_ALL | STAGE_AJAX

    /**
     * Stages.
     *
     * @var integer
     */
    protected $stages = 0;

    /**
     * Boot time.
     *
     * @var float
     */
    protected $bootime;

    /**
     * Base memory at start.
     *
     * @var integer
     */
    protected $baseMemory;

    /**
     * ServiceManager.
     *
     * @var Zikula_ServiceManager
     */
    protected $serviceManager;

    /**
     * EventManager.
     *
     * @var Zikula_EventManager
     */
    protected $eventManager;

    /**
     * Booted flag.
     *
     * @var boolean
     */
    protected $booted = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->baseMemory = memory_get_usage();
    }

    /**
     * Get base memory.
     * 
     * @return integer
     */
    public function getBaseMemory()
    {
        return $this->baseMemory;
    }

    /**
     * Check booted status.
     *
     * @return boolean
     */
    public function hasBooted()
    {
        return $this->booted;
    }


    /**
     * Getter for servicemanager property.
     *
     * @return Zikula_ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Getter for eventmanager property.
     *
     * @return Zikula_Eventmanager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Boot Zikula.
     *
     * @throws LogicException If already booted.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            throw new LogicException('Already booted.');
        }

        $this->bootime = microtime(true);

        $this->serviceManager = new Zikula_ServiceManager('zikula.servicemanager');
        $this->eventManager = $this->serviceManager->attachService('zikula.eventmanager', new Zikula_EventManager($this->serviceManager));
        $this->serviceManager->attachService('zikula', $this);

        ServiceUtil::getManager($this);
        EventUtil::getManager($this);
    }

    /**
     * Get uptime.
     *
     * @return float
     */
    public function getUptime()
    {
        return microtime(true) - $this->bootime;
    }

    /**
     * Get boottime.
     *
     * @return float
     */
    public function getBoottime()
    {
        return $this->bootime;
    }

    /**
     * Get stages.
     *
     * @return integer
     */
    public function getStages()
    {
        return $this->stages;
    }

    /**
     * Initialise Zikula.
     *
     * Carries out a number of initialisation tasks to get Zikula up and
     * running.
     *
     * @param integer $stages Stages to load.
     *
     * @return boolean True initialisation successful false otherwise.
     */
    public function init($stages = self::STAGE_ALL)
    {
        $coreInitEvent = new Zikula_Event('core.init', $this);

        if (!is_numeric($stages)) {
            $stages = self::STAGE_ALL;
        }

        // store the load stages in a global so other API's can check whats loaded
        $this->stages = $this->stages | $stages;

        if (($stages & self::STAGE_PRE) && ($this->stages & ~self::STAGE_PRE)) {
            $this->eventManager->notify(new Zikula_Event('core.preinit', $this));
        }

        // Initialise and load configuration
        if ($stages & self::STAGE_CONFIG) {
            if (System::isLegacyMode()) {
                require_once 'lib/legacy/Compat.php';
            }

            // error reporting
            if (!System::isInstalling()) {
                // this is here because it depends on the config.php loading.
                $event = new Zikula_Event('setup.errorreporting', null, array('stage' => $stages));
                $this->eventManager->notifyUntil($event);
            }

            // initialise custom event listeners from config.php settings
            $coreInitEvent->setArg('stage', self::STAGE_CONFIG);
            $this->eventManager->notify($coreInitEvent);
        }

        // Check that Zikula is installed before continuing
        if (System::getVar('installed') == 0 && !System::isInstalling()) {
            System::redirect('install.php?notinstalled');
            System::shutDown();
        }

        if ($stages & self::STAGE_DB) {
            try {
                $dbEvent = new Zikula_Event('core.init', $this, array('stage' => self::STAGE_DB));
                $this->eventManager->notify($dbEvent);
            } catch (PDOException $e) {
                if (!System::isInstalling()) {
                    header('HTTP/1.1 503 Service Unavailable');
                    require_once System::getSystemErrorTemplate('dbconnectionerror.tpl');
                    System::shutDown();
                } else {
                    return false;
                }
            }
        }

        if ($stages & self::STAGE_TABLES) {
            // Initialise dbtables
            $GLOBALS['dbtables'] = isset($GLOBALS['dbtables']) ? $GLOBALS['dbtables'] : array();
            // ensure that the base modules info is available
            ModUtil::dbInfoLoad('Extensions', 'Extensions');
            ModUtil::initCoreVars();
            ModUtil::dbInfoLoad('Settings', 'Settings');
            ModUtil::dbInfoLoad('Theme', 'Theme');
            ModUtil::dbInfoLoad('Users', 'Users');
            ModUtil::dbInfoLoad('Groups', 'Groups');
            ModUtil::dbInfoLoad('Permissions', 'Permissions');
            ModUtil::dbInfoLoad('Categories', 'Categories');

            if (!System::isInstalling()) {
                ModUtil::registerAutoloaders();
            }
            $coreInitEvent->setArg('stage', self::STAGE_TABLES);
            $this->eventManager->notify($coreInitEvent);
        }

        // Have to load in this order specifically since we cant setup the languages until we've decoded the URL if required (drak)
        // start block
        if ($stages & self::STAGE_LANGS) {
            $lang = ZLanguage::getInstance();
        }

        if ($stages & self::STAGE_DECODEURLS) {
            System::queryStringDecode();
            $coreInitEvent->setArg('stage', self::STAGE_DECODEURLS);
            $this->eventManager->notify($coreInitEvent);
        }

        if ($stages & self::STAGE_LANGS) {
            $lang->setup();
            $coreInitEvent->setArg('stage', self::STAGE_LANGS);
            $this->eventManager->notify($coreInitEvent);
        }
        // end block

        System::checks();

        if ($stages & self::STAGE_SESSIONS) {
            // Other includes
            // ensure that the sesssions table info is available
            ModUtil::dbInfoLoad('Users', 'Users');
            $anonymoussessions = System::getVar('anonymoussessions');
            if ($anonymoussessions == '1' || !empty($_COOKIE[SessionUtil::getCookieName()])) {
                // we need to create a session for guests as configured or
                // a cookie exists which means we have been here before
                // Start session
                SessionUtil::requireSession();

                // Auto-login via HTTP(S) REMOTE_USER property
                if (System::getVar('session_http_login') && !UserUtil::isLoggedIn()) {
                    UserUtil::loginHttp();
                }
            }

            $coreInitEvent->setArg('stage', self::STAGE_SESSIONS);
            $this->eventManager->notify($coreInitEvent);
        }

        if ($stages & self::STAGE_MODS) {
            // Set compression on if desired
            if (System::getVar('UseCompression') == 1) {
                //ob_start("ob_gzhandler");
            }

            ModUtil::load('SecurityCenter');

            $coreInitEvent->setArg('stage', self::STAGE_MODS);
            $this->eventManager->notify($coreInitEvent);
        }

        if ($stages & self::STAGE_THEME) {
            // register default page vars
            PageUtil::registerVar('title');
            PageUtil::setVar('title', System::getVar('defaultpagetitle'));
            PageUtil::registerVar('keywords', true);
            PageUtil::registerVar('stylesheet', true);
            PageUtil::registerVar('javascript', true);
            PageUtil::registerVar('jsgettext', true);
            PageUtil::registerVar('body', true);
            PageUtil::registerVar('rawtext', true);
            PageUtil::registerVar('footer', true);

            $theme = Zikula_View_Theme::getInstance();

            // set some defaults
            // Metadata for SEO
            $this->serviceManager['zikula_view.metatags']['description'] = System::getVar('defaultmetadescription');
            $this->serviceManager['zikula_view.metatags']['keywords'] = System::getVar('metakeywords');

            $coreInitEvent->setArg('stage', self::STAGE_THEME);
            $this->eventManager->notify($coreInitEvent);
        }

        // check the users status, if not 1 then log him out
        if (UserUtil::isLoggedIn()) {
            $userstatus = UserUtil::getVar('activated');
            if ($userstatus != 1) {
                UserUtil::logout();
                LogUtil::registerStatus(__('You have been logged out.'));
                $params = ($userstatus == 2) ? array('confirmtou' => 1) : array();
                self::redirect(ModUtil::url('Users', 'user', 'loginscreen', $params));
            }
        }

        if (($stages & self::STAGE_POST) && ($this->stages & ~self::STAGE_POST)) {
            $this->eventManager->notify(new Zikula_Event('core.postinit', $this, array('stages' => $stages)));
        }
    }
}
