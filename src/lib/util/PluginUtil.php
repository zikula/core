<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PluginUtil class.
 */
class PluginUtil
{
    const DISABLED = 0;
    const ENABLED = 1;
    const NOTINSTALLED = 2;
    const CONFIG = '/Plugin';

    /**
     * Default state.
     *
     * @var constant
     */
    protected static $defaultState = [
        'state' => self::NOTINSTALLED,
        'version' => 0
    ];

    /**
     * Get plugin state.
     *
     * @param string $name    Plugin name.
     * @param mixed  $default Default return value.
     *
     * @return mixed
     */
    public static function getState($name, $default = null)
    {
        return ModUtil::getVar(self::CONFIG, $name, $default);
    }

    /**
     * Delete plugin state.
     *
     * @param string $name Plugin name.
     *
     * @return boolean
     */
    public static function delState($name)
    {
        return ModUtil::delVar(self::CONFIG, $name);
    }

    /**
     * Set plugin state.
     *
     * @param string   $name  Plugin name.
     * @param constant $value Plugin state.
     *
     * @return boolean
     */
    public static function setState($name, $value)
    {
        return ModUtil::setVar(self::CONFIG, $name, $value);
    }

    /**
     * Get default state.
     *
     * @return constant
     */
    public static function getDefaultState()
    {
        return self::$defaultState;
    }

    /**
     * Load all plugins in path.
     *
     * @param string $path      Path.
     * @param string $namespace Namespace.
     *
     * @throws RuntimeException If file does not exist.
     * @return void
     */
    public static function loadPlugins($path, $namespace)
    {
        static $loaded;

        $path = realpath($path);

        if (isset($loaded[$path])) {
            return;
        }

        $it = FileUtil::getFiles($path, false, false, null, 'd');

        foreach ($it as $dir) {
            if (strrpos($dir, 'Doctrine')) {
                // todo consider removing this condition - drak
                die('Please delete plugins/Doctrine and plugins/DoctrineExtensions folders - they have been deprecated');
            }
            $file = $dir . DIRECTORY_SEPARATOR . 'Plugin.php';
            if (!file_exists($file)) {
                // silently ignore non-compliant folders
                if (!System::isDevelopmentMode()) {
                    break;
                }

                throw new RuntimeException(sprintf('%s must exist', $file));
            }
            include_once $file;

            $p = explode(DIRECTORY_SEPARATOR, $dir);
            $dir = end($p);
            prev($p);
            $module = prev($p);

            $className = "{$namespace}_{$dir}_Plugin";
            self::loadPlugin($className);
        }

        $loaded[$path] = true;
    }

    /**
     * Load an initialise plugin.
     *
     * @param string $className Class name.
     *
     * @throws LogicException        If Plugin class is not a Zikula_AbstractPlugin.
     * @return Zikula_AbstractPlugin Plugin class.
     */
    public static function loadPlugin($className)
    {
        $sm = ServiceUtil::getManager();
        $serviceId = self::getServiceId($className);
        if ($sm->has($serviceId)) {
            return $sm->get($serviceId);
        }

        $r = new ReflectionClass($className);
        $plugin = $r->newInstanceArgs([$sm, $sm->get('event_dispatcher')]);

        if (!$plugin instanceof Zikula_AbstractPlugin) {
            throw new LogicException(sprintf('Class %s must be an instance of Zikula_AbstractPlugin', $className));
        }

        if (!$plugin->hasBooted() && $plugin->isInstalled() && $plugin->isEnabled()) {
            $plugin->preInitialize();
            $plugin->initialize();
            $plugin->postInitialize();

            if ($plugin->getEventNames()) {
                $plugin->attach();
            }
            $plugin->setBooted();
        }
        $sm->set($serviceId, $plugin);

        return $plugin;
    }

    /**
     * Get plugin object.
     *
     * @param string $className Class name.
     *
     * @return Zikula_AbstractPlugin
     */
    public static function getPlugin($className)
    {
        $sm = ServiceUtil::getManager();
        $serviceId = self::getServiceId($className);
        if ($sm->has($serviceId)) {
            return $sm->get($serviceId);
        }
    }

    /**
     * Discover all plugins.
     *
     * @return array
     */
    public static function getAllPlugins()
    {
        return array_merge(self::getAllSystemPlugins(), self::getAllModulePlugins());
    }

    /**
     * Discover all module plugins.
     *
     * @return array Array of plugins paths.
     */
    public static function getAllModulePlugins()
    {
        $pluginsArray = [];

        $dirs = ['system', 'modules'];

        foreach ($dirs as $dir) {
            $modules = FileUtil::getFiles($dir, false, false, null, 'd');
            foreach ($modules as $module) {
                if (is_dir("$module/plugins")) {
                    $it = FileUtil::getFiles("$module/plugins", false, false, null, 'd');
                    $pluginsArray = array_merge($pluginsArray, $it);
                }
            }
        }

        return $pluginsArray;
    }

    /**
     * Discover all system plugins.
     *
     * @return array Array of plugin paths.
     */
    public static function getAllSystemPlugins()
    {
        return FileUtil::getFiles('plugins', false, false, null, 'd');
    }

    /**
     * Load all plugins.
     *
     * @return array Array of class names.
     */
    public static function loadAllPlugins()
    {
        return array_merge(self::loadAllSystemPlugins(), self::loadAllModulePlugins());
    }

    /**
     * Load all system plugins.
     *
     * @return array Array of class names.
     */
    public static function loadAllSystemPlugins()
    {
        $classNames = [];
        $plugins = self::getAllSystemPlugins();
        foreach ($plugins as $plugin) {
            $plugin = realpath($plugin);
            self::_includeFile($plugin);
            $p = explode(DIRECTORY_SEPARATOR, $plugin);
            $name = end($p);
            $className = "SystemPlugin_{$name}_Plugin";
            self::loadPlugin($className);
            $classNames[] = $className;
        }

        return $classNames;
    }

    /**
     * Load all module plugins.
     *
     * @return array Array of class names.
     */
    public static function loadAllModulePlugins()
    {
        $classNames = [];
        $plugins = self::getAllModulePlugins();
        foreach ($plugins as $plugin) {
            $plugin = realpath($plugin);
            self::_includeFile($plugin);
            $p = explode(DIRECTORY_SEPARATOR, $plugin);
            $dir = end($p);
            prev($p);
            $module = prev($p);
            $className = "ModulePlugin_{$module}_{$dir}_Plugin";
            self::loadPlugin($className);
            $classNames[] = $className;
        }

        return $classNames;
    }

    /**
     * Include plugin file.
     *
     * @param string $plugin Plugin path.
     *
     * @throws RuntimeException If plugin file does not exist.
     * @return void
     */
    private static function _includeFile($plugin)
    {
        $file = $plugin . DIRECTORY_SEPARATOR . "Plugin.php";
        if (!file_exists($file)) {
            throw new RuntimeException(sprintf('%s must exist', $file));
        }
        include_once $file;
    }

    /**
     * Check's if a module has plugins or not.
     *
     * @param string $modulename Name of an module.
     *
     * @return boolean true when the module has plugins.
     */
    public static function hasModulePlugins($modulename)
    {
        $pluginClasses = self::loadAllPlugins();
        $hasPlugins = false;

        foreach ($pluginClasses as $pluginClass) {
            $parts = explode('_', $pluginClass);

            if ($parts[0] == 'ModulePlugin' && $parts[1] == $modulename) {
                $hasPlugins = true;
                break;
            }
        }

        return $hasPlugins;
    }

    /**
     * Install plugin.
     *
     * @param string $className Plugin class name.
     *
     * @throws LogicException If plugin is already installed.
     * @return boolean
     */
    public static function install($className)
    {
        $plugin = self::loadPlugin($className);
        if ($plugin instanceof Zikula_Plugin_AlwaysOnInterface) {
            // as it stands, these plugins cannot be installed since they are always on
            // and cannot be disabled (required only for really base thing).
            return true;
        }

        if ($plugin->isInstalled()) {
            throw new LogicException(__f('Plugin %s is already installed', $className));
        }

        if (!$plugin->install()) {
            return false;
        }

        $state = [
            'state' => self::ENABLED,
            'version' => $plugin->getMetaVersion()
        ];
        self::setState($plugin->getServiceId(), $state);

        return true;
    }

    /**
     * Upgrade plugin.
     *
     * @param string $className Plugin class name.
     *
     * @throws LogicException If plugin is not installed.
     * @throws LogicException If installed version and plugin version are equal.
     * @return boolean
     */
    public static function upgrade($className)
    {
        $plugin = self::loadPlugin($className);
        if (!$plugin->isInstalled()) {
            throw new LogicException(__f('Plugin %s is not installed', $className));
        }

        $state = self::getState($plugin->getServiceId(), self::getDefaultState());
        if (version_compare($plugin->getMetaVersion(), $state['version'], '<=')) {
            throw new LogicException(__f('Installed version and plugin version are equal, nothing to do for %s', $className));
        }

        $result = $plugin->upgrade($state['version']);
        if ($result) {
            $state['version'] = ($result == true) ? $plugin->getMetaVersion() : $result;
            self::setState($plugin->getServiceId(), $state);

            return true;
        }

        return false;
    }

    /**
     * Uninstall plugin.
     *
     * @param string $className Plugin class name.
     *
     * @throws LogicException If plugin is not installed.
     * @return boolean
     */
    public static function uninstall($className)
    {
        $plugin = self::loadPlugin($className);
        if (!$plugin->isInstalled()) {
            throw new LogicException(__f('Plugin %s is not installed', $className));
        }

        self::disable($className);

        if ($plugin->uninstall()) {
            self::delState($plugin->getServiceId());

            return true;
        }

        return false;
    }

    /**
     * Disable plugin.
     *
     * @param string $className Plugin class name.
     *
     * @throws LogicException If plugin is not installed.
     * @return boolean
     */
    public static function disable($className)
    {
        $plugin = self::loadPlugin($className);
        if (!$plugin->isInstalled()) {
            throw new LogicException(__f('Plugin %s is not installed', $className));
        }

        $state = self::getState($plugin->getServiceId());
        $state['state'] = self::DISABLED;
        self::setState($plugin->getServiceId(), $state);
        $plugin->postDisable();

        return true;
    }

    /**
     * Enable plugin.
     *
     * @param string $className Plugin class name.
     *
     * @throws LogicException If plugin is not installed.
     * @return boolean
     */
    public static function enable($className)
    {
        $plugin = self::loadPlugin($className);
        if (!$plugin->isInstalled()) {
            throw new LogicException(__f('Plugin %s is not installed', $className));
        }

        $state = self::getState($plugin->getServiceId());
        $state['state'] = self::ENABLED;
        self::setState($plugin->getServiceId(), $state);
        $plugin->postEnable();

        return true;
    }

    /**
     * Is plugin available (by service id).
     *
     * @param string $id Service Id, normalized classname, e.g. systemplugin.zend.plugin.
     *
     * @return boolean
     */
    public static function isAvailable($id)
    {
        $sm = ServiceUtil::getManager();
        if (!$sm->has($id)) {
            return false;
        }

        $plugin = $sm->get($id);
        if ($plugin->hasBooted() && $plugin->isInstalled() && $plugin->isEnabled()) {
            return true;
        }

        return false;
    }

    /**
     * Calculates plugin service id from Plugin class name.
     *
     * @param string $className Plugin class name.
     *
     * @return string ServiceID.
     */
    public static function getServiceId($className)
    {
        $p = explode('_', $className);
        if (count($p) == 3) {
            $className = "{$p[0]}_{$p[1]}";
        } elseif (count($p) == 4) {
            $className = "{$p[0]}_{$p[1]}_{$p[2]}";
        }

        return strtolower(str_replace('_', '.', $className));
    }
}
