<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\Core\Event\GenericEvent;
use Zikula_Request_Http as Request;

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
 *
 * @deprecated
 */
class Zikula_Core
{
    /**
     * The core Zikula version number.
     */
    const VERSION_NUM = '1.4.3';

    /**
     * The version ID.
     */
    const VERSION_ID = 'Zikula';

    /**
     * The version sub-ID.
     */
    const VERSION_SUB = 'Overture'; // 2.0.0 to be named 'Concerto'

    /**
     * The minimum required PHP version for this release of core.
     */
    const PHP_MINIMUM_VERSION = '5.4.1';

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
    protected $container;

    /**
     * EventManager.
     *
     * @var Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
     */
    protected $dispatcher;

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
    protected $attachedHandlers = [];

    /**
     * Array of handler files per directory.
     *
     * The key is the directory, with a non-indexed array of files.
     *
     * @var array
     */
    protected $directoryContents = [];

    /**
     * Array of scanned directories.
     *
     * @var array
     */
    protected $scannedDirs = [];

    private $kernel;

    /**
     * Getter for servicemanager property.
     *
     * @depracated since 1.4
     * @see self::getContainer()
     *
     * @return Zikula_ServiceManager
     */
    public function getServiceManager()
    {
        return $this->container;
    }

    /**
     * Getter for servicemanager property.
     *
     * @return Zikula_ServiceManager
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Getter for eventmanager property.
     *
     * @deprecated since 1.4
     * @see self::getDispatcher()
     *
     * @return Zikula_Eventmanager
     */
    public function getEventManager()
    {
        return $this->dispatcher;
    }

    /**
     * Getter for eventmanager property.
     *
     * @return Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Constructor.
     *
     * @param string $handlerDir Directory where handlers are located
     */
    public function __construct($handlerDir = 'lib/EventHandlers')
    {
        $this->handlerDir = $handlerDir;
        $this->baseMemory = memory_get_usage();
    }

    public function setKernel($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Boot Zikula.
     *
     * @throws LogicException If already booted
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            throw new LogicException('Already booted.');
        }

        $this->bootime = microtime(true);

        $this->container = $this->kernel->getContainer();
        $this->container->setAlias('zikula.servicemanager', 'service_container');

        $this->dispatcher = $this->getContainer()->get('event_dispatcher');
        $this->container->setAlias('zikula.eventmanager', 'event_dispatcher');

        $this->container->set('zikula', $this);

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
        $event = new GenericEvent($this);
        $this->dispatcher->dispatch('shutdown', $event);

        // flush handlers
        $this->dispatcher->flushHandlers();

        // flush all services
        $services = $this->container->getServiceIds();
        rsort($services);
        foreach ($services as $id) {
            if (!in_array($id, ['zikula', 'zikula.servicemanager', 'service_container', 'zikula.eventmanager', 'event_dispatcher'])) {
                $this->container->removeDefinition($id);
            }
        }

        // flush arguments.
        $this->container->getParameterBag()->clear();

        $this->attachedHandlers = [];
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
     * @param string $dir Path to the folder holding the eventhandler classes
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
                include_once realpath($file);
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
                    $this->directoryContents[$dir] = [];
                }
                if (isset($className)) {
                    $this->directoryContents[$dir][] = $className;
                }
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
     * @param string $className The name of the class
     *
     * @throws LogicException If class is not instance of Zikula_AbstractEventHandler
     *
     * @return void
     */
    public function attachEventHandler($className)
    {
        $r = new ReflectionClass($className);
        $handler = $r->newInstance($this->dispatcher);

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
     * @param integer             $stage Stage to load
     * @param Zikula_Request_Http $request
     *
     * @return boolean True initialisation successful false otherwise
     */
    public function init($stage, Request $request)
    {
        $stage = isset($stage) ? $stage : self::STAGE_ALL;
        $GLOBALS['__request'] = $request; // hack for pre 1.5.0 - drak

        $coreInitEvent = new GenericEvent($this);

        // store the load stages in a global so other API's can check whats loaded
        $this->stage = $this->stage | $stage;

        if (($stage & self::STAGE_PRE) && ($this->stage & ~self::STAGE_PRE)) {
            ModUtil::flushCache();
            System::flushCache();
            $args = !System::isInstalling() ? ['lazy' => true] : [];
            $this->dispatcher->dispatch('core.preinit', new GenericEvent($this, $args));
        }

        // Initialise and load configuration
        if ($stage & self::STAGE_CONFIG) {
            // for BC only. remove this code in 2.0.0
            if (!System::isInstalling()) {
                $this->dispatcher->dispatch('setup.errorreporting', new GenericEvent(null, ['stage' => $stage]));
            }

            // initialise custom event listeners from config.php settings
            $coreInitEvent->setArgument('stage', self::STAGE_CONFIG);
            /***************************************************
             * NOTE: this event is monitored by
             * \Zikula\Bundle\CoreInstallerBundle\EventListener\InstallUpgradeCheckListener
             * to see if install or upgrade is needed
             ***************************************************/
            $this->dispatcher->dispatch('core.init', $coreInitEvent);
        }

        if ($stage & self::STAGE_DB) {
            try {
                $dbEvent = new GenericEvent($this, ['stage' => self::STAGE_DB]);
                $this->dispatcher->dispatch('core.init', $dbEvent);
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
            ModUtil::initCoreVars();
            ModUtil::dbInfoLoad('ZikulaUsersModule', 'ZikulaUsersModule');
            ModUtil::dbInfoLoad('ZikulaGroupsModule', 'ZikulaGroupsModule');
            ModUtil::dbInfoLoad('ZikulaPermissionsModule', 'ZikulaPermissionsModule');
            ModUtil::dbInfoLoad('ZikulaCategoriesModule', 'ZikulaCategoriesModule');

            // Add AutoLoading for non-symfony 1.3 modules in /modules
            if (!System::isInstalling()) {
                ModUtil::registerAutoloaders();
            }

            $coreInitEvent->setArgument('stage', self::STAGE_TABLES);
            $this->dispatcher->dispatch('core.init', $coreInitEvent);
        }

        if ($stage & self::STAGE_SESSIONS) {
            //            SessionUtil::requireSession();
            $coreInitEvent->setArgument('stage', self::STAGE_SESSIONS);
            $this->dispatcher->dispatch('core.init', $coreInitEvent);
        }

        // Have to load in this order specifically since we cant setup the languages until we've decoded the URL if required (drak)
        // start block
        if ($stage & self::STAGE_LANGS) {
            $lang = ZLanguage::getInstance();
        }

        if ($stage & self::STAGE_DECODEURLS) {
            $coreInitEvent->setArgument('stage', self::STAGE_DECODEURLS);
            $this->dispatcher->dispatch('core.init', $coreInitEvent);
        }

        if ($stage & self::STAGE_LANGS) {
            $lang->setup($request);
            $coreInitEvent->setArgument('stage', self::STAGE_LANGS);
            $this->dispatcher->dispatch('core.init', $coreInitEvent);
        }
        // end block

        if ($stage & self::STAGE_MODS) {
            if (!System::isInstalling()) {
                ModUtil::load('ZikulaSecurityCenterModule');
            }

            $coreInitEvent->setArgument('stage', self::STAGE_MODS);
            $this->dispatcher->dispatch('core.init', $coreInitEvent);
        }

        if ($stage & self::STAGE_THEME) {
            // register default page vars
            PageUtil::registerVar('polyfill_features', true);
            PageUtil::registerVar('title');
            PageUtil::setVar('title', System::getVar('defaultpagetitle'));
            PageUtil::registerVar('keywords', true);
            PageUtil::registerVar('stylesheet', true);
            PageUtil::registerVar('javascript', true);
            PageUtil::registerVar('jsgettext', true);
            PageUtil::registerVar('body', true);
            PageUtil::registerVar('header', true);
            PageUtil::registerVar('footer', true);

            // set some defaults
            // Metadata for SEO
            $this->container->setParameter('zikula_view.metatags', [
                'description' => System::getVar('defaultmetadescription'),
                'keywords' => System::getVar('metakeywords')
            ]);

            $coreInitEvent->setArgument('stage', self::STAGE_THEME);
            $this->dispatcher->dispatch('core.init', $coreInitEvent);
        }

        // check the users status, if not 1 then log him out
        if (!System::isInstalling() && UserUtil::isLoggedIn()) {
            $userstatus = UserUtil::getVar('activated');
            if ($userstatus != Users_Constant::ACTIVATED_ACTIVE) {
                UserUtil::logout();
                // TODO - When getting logged out this way, the existing session is destroyed and
                //        then a new one is created on the reentry into index.php. The message
                //        set by the registerStatus call below gets lost.
                LogUtil::registerStatus(__('You have been logged out.'));
                System::redirect(ModUtil::url('ZikulaUsersModule', 'user', 'login'));
            }
        }

        if (($stage & self::STAGE_POST) && ($this->stage & ~self::STAGE_POST)) {
            $this->dispatcher->dispatch('core.postinit', new GenericEvent($this, ['stages' => $stage]));
        }
    }
}
