<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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

    protected static $defaultState = array('state' => self::NOTINSTALLED, 'version' => 0);

    protected static $plugins;

    public static function getVar($name, $default = null)
    {
        return ModUtil::getVar(self::CONFIG, $name, $default);
    }

    public static function delVar($name)
    {
        return ModUtil::delVar(self::CONFIG, $name);
    }

    public static function setVar($name, $value)
    {
        return ModUtil::setVar(self::CONFIG, $name, $value);
    }

    public static function hasVar($name)
    {
        return ModUtil::hasVar(self::CONFIG, $name);
    }

    public static function getDefaultState()
    {
        return self::$defaultState;
    }

//    public static function getState($name)
//    {
//        $plugin = self::getVar($name, null);
//        $plugin = (is_null($plugin)) ? self::$defaultState : $plugin;
//        return $plugin['state'];
//    }

    /**
     * Load all plugins in path.
     * 
     * @staticvar <type> $loaded
     * @param <type> $path
     * @param <type> $namespace
     * @return <type>
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
            $file = $dir . DIRECTORY_SEPARATOR . 'Plugin.php';
            if (!file_exists($file)) {
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
     * @param string $className
     * 
     * @return object Plugin class.
     */
    public static function loadPlugin($className)
    {
        if (isset(self::$plugins[$className])) {
            return self::$plugins[$className];
        }

        $plugin = new $className;
        if (!$plugin instanceof AbstractPlugin) {
            throw new LogicException(sprintf('Class %s must be an instance of AbstractPlugin', $className));
        }

        if ($plugin->isInstalled() && $plugin->isEnabled()) {
            $plugin->preInitialize();
            $plugin->initialize();
            $plugin->postInitialize();
            $plugin->attach();
        }

        self::$plugins[$className] = $plugin;
        return self::$plugins[$className];
    }

    public static function getPlugin($className)
    {
        if (isset(self::$plugins[$className])) {
            return self::$plugins[$className];
        }
    }

    /**
     * Discover all plugins.
     *
     * @param boolean $modulesOnly False to include system plugins.
     *
     * @return array Of plugins paths.
     */
    public static function getAllPlugins($modulesOnly = true)
    {
        $pluginsArray = array();
        if (!$modulesOnly) {
            $pluginsArray = FileUtil::getFiles('plugins', false, false, null, 'd');
        }
        
        $dirs = array('system', 'modules');
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
     * Loads all plugins.
     *
     * For use by Plugin manager (installer).
     *
     * @return array of plugin classes loaded.
     */
    public static function loadAllPlugins()
    {
        $classNames = array();
        $plugins = self::getAllPlugins();
        foreach ($plugins as $plugin) {
            $plugin = realpath($plugin);
            $file = $plugin . DIRECTORY_SEPARATOR . "Plugin.php";
            if (!file_exists($file)) {
                throw new RuntimeException(sprintf('%s must exist', $file));
            }
            include_once $file;
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

    public static function install($className)
    {
        $plugin = self::loadPlugin($className);
        if ($plugin->isInstalled()) {
            throw new LogicException(__f('Plugin %s is already installed', $className));
        }

        if (!$plugin->install()) {
            return false;
        }

        $state = array('state' => self::ENABLED, 'version' => $plugin::VERSION);
        self::setVar($plugin->getModVarName(), $state);
    }

    public static function upgrade($className)
    {
        $plugin = self::loadPlugin($className);
        if (!$plugin->isInstalled()) {
            throw new LogicException(__f('Plugin %s is not installed', $className));
        }

        $state = self::getVar($plugin->getModVarName(), self::getDefaultState());
        if (version_compare($plugin::VERSON, $state['version'], '>=') ) {
            throw new LogicError(__f('Installed version and plugin version are equal, nothing to do for %s', $className));
        }

        if ($plugin->upgrade($state['version'])) {
            $state['version'] = $plugin::VESION;
            self::setVar($plugin->getModVarName(), $state);
            return true;
        }

        return false;
    }

    public static function remove($className)
    {
        $plugin = self::loadPlugin($className);
        if (!$plugin->isInstalled()) {
            throw new LogicException(__f('Plugin %s is not installed', $className));
        }

        self::disable($className);

        if ($plugin->remove()) {
            self::delVar($plugin->getModVarName());
            return true;
        }

        return false;
    }

    public static function disable($className)
    {
        $plugin = self::loadPlugin($className);
        if (!$plugin->isInstalled()) {
            throw new LogicException(__f('Plugin %s is not installed', $className));
        }

        $state = PluginUtil::getVar($plugin->getModVarName());
        $state['state'] = PluginUtil::DISABLED;
        PluginUtil::setVar($plugin->getModVarName(), $state);
        $plugin->postDisable();
        return true;
    }

    public static function enable($className)
    {
        $plugin = self::loadPlugin($className);
        if (!$plugin->isInstalled()) {
            throw new LogicException(__f('Plugin %s is not installed', $className));
        }

        $state = PluginUtil::getVar($plugin->getModVarName());
        $state['state'] = PluginUtil::ENABLED;
        PluginUtil::setVar($plugin->getModVarName(), $state);
        $plugin->postEnable();
        return true;
    }
}