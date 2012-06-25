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

namespace Zikula\Core;

use Zikula\Component\DependencyInjection\ContainerBuilder;
use Zikula\Core\Event\GenericEvent;
use Zikula\Framework\AbstractEventHandler;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use UsersModule\Constants as UsersConstant;


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
class Core
{
    /**
     * The core Zikula version number.
     */
    const VERSION_NUM = '2.0.0-dev';

    /**
     * The version ID.
     */
    const VERSION_ID = 'Zikula';

    /**
     * The version sub-ID.
     */
    const VERSION_SUB = 'urmila';

    /**
     * The minimum required PHP version for this release of core.
     */
    const PHP_MINIMUM_VERSION = '5.3.3';

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
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $container;

    /**
     * EventDispatcher.
     *
     * @var ContainerAwareEventDispatcher
     */
    protected $dispatcher;

    /**
     * Booted flag.
     *
     * @var boolean
     */
    protected $booted = false;

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
     * @return ContainerBuilder
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Getter for eventmanager property.
     *
     * @return ContainerAwareEventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Constructor.
     *
     * @param ContainerBuilder $container Directory where handlers are located.
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->baseMemory = memory_get_usage();
        $this->container = $container;
    }

    /**
     * Boot Zikula.
     *
     * @throws \LogicException If already booted.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            throw new \LogicException('Already booted.');
        }

        $this->bootime = microtime(true);

        $this->dispatcher = $this->container->get('event_dispatcher');


        // Load system configuration
        $this->dispatcher->dispatch('bootstrap.getconfig', new GenericEvent($this));
        $this->dispatcher->dispatch('bootstrap.custom', new GenericEvent($this));
    }

    public function loadService($path)
    {
        $fileLocator = new FileLocator(array($path));
        $xmlFileLoader = new XmlFileLoader($this->getContainer()->get('service_container'), $fileLocator);
        $xmlFileLoader->load($path);
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
        foreach ($this->dispatcher->getListeners() as $eventName => $listener) {
            $this->dispatcher->removeListener($eventName, $listener);
        }

        // flush all services
        $services = $this->container->getServiceIds();
        rsort($services);
        foreach ($services as $id) {
            if (!in_array($id, array('zikula', 'service_container', 'event_dispatcher'))) {
                $this->container->removeDefinition($id);
            }
        }

        // flush arguments.
        $this->container->setArguments(array());

        $this->attachedHandlers = array();
        $this->stage = 0;
        $this->bootime = microtime(true);
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
            $it = \FileUtil::getFiles($dir, false, false, 'php', 'f');

            foreach ($it as $file) {
                $before = get_declared_classes();
                include realpath($file);
                $after = get_declared_classes();

                $diff = new \ArrayIterator(array_diff($after, $before));
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
     * Load and attach handlers for AbstractEventHandler listeners.
     *
     * Loads event handlers that extend AbstractEventHandler
     *
     * @param string $className The name of the class.
     *
     * @throws \LogicException If class is not instance of AbstractEventHandler.
     *
     * @return void
     */
    public function attachEventHandler($className)
    {
        $r = new \ReflectionClass($className);
        /* @var AbstractEventHandler $handler */
        $handler = $r->newInstance($this->dispatcher);

        if (!$handler instanceof AbstractEventHandler) {
            throw new \LogicException(sprintf('Class %s must be an instance of Zikula\Framework\AbstractEventHandler', $className));
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
}
