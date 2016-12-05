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

/**
 * mbstring.internal_encoding
 *
 * This feature has been deprecated as of PHP 5.6.0. Relying on this feature is highly discouraged.
 * PHP 5.6 and later users should leave this empty and set default_charset instead.
 *
 * @link http://php.net/manual/en/mbstring.configuration.php#ini.mbstring.internal-encoding
 */
if (version_compare(\PHP_VERSION, '5.6.0', '<')) {
    ini_set('mbstring.internal_encoding', 'UTF-8');
}

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
    const VERSION_NUM = '1.3.11';

    /**
     * The version ID.
     */
    const VERSION_ID = 'Zikula';

    /**
     * The version sub-ID.
     */
    const VERSION_SUB = 'vai';

    /**
     * The minimum required PHP version for this release of core.
     */
    const PHP_MINIMUM_VERSION = '5.3.2';

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
     * Stage.
     *
     * @var integer
     */
    protected $stage = 0;

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
     * Directory where handlers are located.
     *
     * @var string
     */
    protected $handlerDir;

    /**
     * Array of the attached handlers.
     *
     * @var array
     */
    protected $attachedHandlers = array();

    /**
     * Array of handler files per directory.
     *
     * The key is the directory, with a non-indexed array of files.
     *
     * @var array
     */
    protected $directoryContents = array();

    /**
     * Array of scanned directories.
     *
     * @var array
     */
    protected $scannedDirs = array();

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
     * Constructor.
     *
     * @param string $handlerDir Directory where handlers are located.
     */
    public function __construct($handlerDir = 'lib/EventHandlers')
    {
        $this->handlerDir = $handlerDir;
        $this->baseMemory = memory_get_usage();
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

        $this->attachHandlers($this->handlerDir);
    }

    /**
     * Reboot.
     *
     * Shutdown the system flushing all event handlers, services and service arguments.
     *
     * @return void
     */
    public function reboot()
    {
        $event = new Zikula_Event('shutdown', $this);
        $this->eventManager->notify($event);

        // flush handlers
        $this->eventManager->flushHandlers();

        // flush all services
        $services = $this->serviceManager->listServices();
        rsort($services);
        foreach ($services as $id) {
            if (!in_array($id, array('zikula', 'zikula.servicemanager', 'zikula.eventmanager'))) {
                $this->serviceManager->unregisterService($id);
            }
        }

        // flush arguments.
        $this->serviceManager->setArguments(array());

        $this->attachedHandlers = array();
        $this->stage = 0;
        $this->bootime = microtime(true);
        $this->attachHandlers($this->handlerDir);
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
     * Loader for custom handlers.
     *
     * @param string $dir Path to the folder holding the eventhandler classes.
     *
     * @return void
     */
    public function attachHandlers($dir)
    {
        $dir = realpath($dir);

        // only ever scan a directory once at runtime (even if Core is restarted).
        if (!isset($this->scannedDirs[$dir])) {
            $it = FileUtil::getFiles($dir, false, false, 'php', 'f');

            foreach ($it as $file) {
                $before = get_declared_classes();
                include realpath($file);
                $after = get_declared_classes();

                $diff = new ArrayIterator(array_diff($after, $before));
                if (count($diff) > 1) {
                    while ($diff->valid()) {
                        $className = $diff->current();
                        $diff->next();
                    }
                } else {
                    $className = $diff->current();
                }

                if (!isset($this->directoryContents[$dir])) {
                    $this->directoryContents[$dir] = array();
                }
                $this->directoryContents[$dir][] = $className;
            }
            $this->scannedDirs[$dir] = true;
        }

        if (!isset($this->attachedHandlers[$dir]) && isset($this->directoryContents[$dir])) {
            foreach ($this->directoryContents[$dir] as $className) {
                $this->attachEventHandler($className);
                $this->attachedHandlers[$dir] = true;
            }
        }
    }

    /**
     * Load and attach handlers for Zikula_AbstractEventHandler listeners.
     *
     * Loads event handlers that extend Zikula_AbstractEventHandler
     *
     * @param string $className The name of the class.
     *
     * @throws LogicException If class is not instance of Zikula_AbstractEventHandler.
     *
     * @return void
     */
    public function attachEventHandler($className)
    {
        $r = new ReflectionClass($className);
        $handler = $r->newInstance($this->eventManager);

        if (!$handler instanceof Zikula_AbstractEventHandler) {
            throw new LogicException(sprintf('Class %s must be an instance of Zikula_AbstractEventHandler', $className));
        }

        $handler->setup();
        $handler->attach();
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
     * Get stage.
     *
     * @return integer
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * Initialise Zikula.
     *
     * Carries out a number of initialisation tasks to get Zikula up and
     * running.
     *
     * @param integer $stage Stage to load.
     *
     * @return boolean True initialisation successful false otherwise.
     */
    public function init($stage = self::STAGE_ALL)
    {
        $coreInitEvent = new Zikula_Event('core.init', $this);

        // store the load stages in a global so other API's can check whats loaded
        $this->stage = $this->stage | $stage;

        if (($stage & self::STAGE_PRE) && ($this->stage & ~self::STAGE_PRE)) {
            ModUtil::flushCache();
            System::flushCache();
            $this->eventManager->notify(new Zikula_Event('core.preinit', $this));
        }

        // Initialise and load configuration
        if ($stage & self::STAGE_CONFIG) {
            if (System::isLegacyMode()) {
                require_once 'lib/legacy/Compat.php';
            }

            // error reporting
            if (!System::isInstalling()) {
                // this is here because it depends on the config.php loading.
                $event = new Zikula_Event('setup.errorreporting', null, array('stage' => $stage));
                $this->eventManager->notify($event);
            }

            // initialise custom event listeners from config.php settings
            $coreInitEvent->setArg('stage', self::STAGE_CONFIG);
            $this->eventManager->notify($coreInitEvent);
        }

        // Check that Zikula is installed before continuing
        if (System::getVar('installed') == 0 && !System::isInstalling()) {
            System::redirect(System::getBaseUrl().'install.php?notinstalled');
            System::shutDown();
        }

        if ($stage & self::STAGE_DB) {
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

        if ($stage & self::STAGE_TABLES) {
            // Initialise dbtables
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

        if ($stage & self::STAGE_SESSIONS) {
            SessionUtil::requireSession();
            $coreInitEvent->setArg('stage', self::STAGE_SESSIONS);
            $this->eventManager->notify($coreInitEvent);
        }

        // Have to load in this order specifically since we cant setup the languages until we've decoded the URL if required (drak)
        // start block
        if ($stage & self::STAGE_LANGS) {
            $lang = ZLanguage::getInstance();
        }

        if ($stage & self::STAGE_DECODEURLS) {
            System::queryStringDecode();
            $coreInitEvent->setArg('stage', self::STAGE_DECODEURLS);
            $this->eventManager->notify($coreInitEvent);
        }

        if ($stage & self::STAGE_LANGS) {
            $lang->setup();
            $coreInitEvent->setArg('stage', self::STAGE_LANGS);
            $this->eventManager->notify($coreInitEvent);
        }
        // end block

        if ($stage & self::STAGE_MODS) {
            // Set compression on if desired
            if (System::getVar('UseCompression') == 1) {
                //ob_start("ob_gzhandler");
            }

            ModUtil::load('SecurityCenter');

            $coreInitEvent->setArg('stage', self::STAGE_MODS);
            $this->eventManager->notify($coreInitEvent);
        }

        if ($stage & self::STAGE_THEME) {
            // register default page vars
            PageUtil::registerVar('title');
            PageUtil::setVar('title', System::getVar('defaultpagetitle'));
            PageUtil::registerVar('keywords', true);
            PageUtil::registerVar('stylesheet', true);
            PageUtil::registerVar('javascript', true);
            PageUtil::registerVar('jsgettext', true);
            PageUtil::registerVar('body', true);
            PageUtil::registerVar('header', true);
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
            if ($userstatus != Users_Constant::ACTIVATED_ACTIVE) {
                UserUtil::logout();
                // TODO - When getting logged out this way, the existing session is destroyed and
                //        then a new one is created on the reentry into index.php. The message
                //        set by the registerStatus call below gets lost.
                LogUtil::registerStatus(__('You have been logged out.'));
                System::redirect(ModUtil::url('Users', 'user', 'login'));
            }
        }

        if (($stage & self::STAGE_POST) && ($this->stage & ~self::STAGE_POST)) {
            $this->eventManager->notify(new Zikula_Event('core.postinit', $this, array('stages' => $stage)));
        }
    }
}
