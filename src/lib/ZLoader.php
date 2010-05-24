<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

include 'lib/i18n/ZGettextFunctions.php';

define('ZLOADER_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

// setup vendors in include path
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR);

include 'Smarty/Smarty.class.php';

/**
 * ZLoader
 */
class ZLoader
{
    private static $map;

    private static $autoloaders;

    public static function register()
    {
        self::$map = self::map();
        spl_autoload_register(array('ZLoader', 'autoload'));
        self::$autoloaders = new KernelClassLoader();
        self::$autoloaders->spl_autoload_register();
        self::addAutoloader('Doctrine', ZLOADER_PATH . '/vendor/Doctrine');
        include ZLOADER_PATH. 'legacy/Loader.php';
        include ZLOADER_PATH. 'legacy/Api.php';

        // load eventhandlers from config/EventHandlers directory if any.
        EventManagerUtil::attachCustomHandlers('config/EventHandlers');

        // setup core events.
        EventManagerUtil::attach('core.init', array('SystemListenersUtil', 'sessionLogging'));
        EventManagerUtil::attach('core.postinit', array('SystemListenersUtil', 'systemHooks'));
    }

    public static function addAutoloader($namespace, $path = '', $separator = '_')
    {
        if (self::$autoloaders->hasAutoloader($namespace)) {
            return;
        }
        
        self::$autoloaders->register($namespace, $path, $separator);
    }

    /**
     * Simple autoloader
     *
     * @param string $class
     */
    public static function autoload($class)
    {
        // load from maps
        $map = self::$map;
        if (isset($map[$class])) {
            $path = ZLOADER_PATH . "$map[$class]/$class.php";
            if (file_exists($path)) {
                return include $path;
            }
        }

        // generic PEAR style namespace to path, i.e Foo_Bar -> Foo/Bar.php
        if (strpos($class, '_')) {
            $array = explode('_', $class);
            $prefix = (isset($map[$array[0]]) ? $map[$array[0]] . '/' : '');
            $path = ZLOADER_PATH . $prefix . str_replace('_', '/', $class) . '.php';
            if (file_exists($path)) {
                return include $path;
            }
        }

        $file = "lib/$class.php";
        if (file_exists($file)) {
            return include $file;
        }
    }

    public static function map()
    {
        return array(
            'ZLanguage' => 'i18n',
            'ZI18n' => 'i18n',
            'ZL10n' => 'i18n',
            'ZLocale' => 'i18n',
            'ZGettext' => 'i18n',
            'ZMO' => 'i18n',
            'ZWorkflow' => 'workflow',
            'ZWorkflowParser' => 'workflow',
            'Renderer' => 'render',
            'Theme' => 'render',
            'DBObject' => 'dbobject',
            'DBObjectArray' => 'dbobject',
            'DBUtil' => 'util',
            'BlockUtil' => 'util',
            'DBConnectionStack' => 'util',
            'AjaxUtil' => 'util',
            'CacheUtil' => 'util',
            'CategoryRegistryUtil' => 'util',
            'CategoryUtil' => 'util',
            'CookieUtil' => 'util',
            'DataUtil' => 'util',
            'DateUtil' => 'util',
            'EventManagerUtil' => 'util',
            'FileUtil' => 'util',
            'FilterUtil' => 'util',
            'FormUtil' => 'util',
            'HtmlUtil' => 'util',
            'LogUtil' => 'util',
            'ModUtil' => 'util',
            'ObjectUtil' => 'util',
            'PageUtil' => 'util',
            'RandomUtil' => 'util',
            'SecurityUtil' => 'util',
            'SessionUtil' => 'util',
            'StringUtil' => 'util',
            'ThemeUtil' => 'util',
            'UserUtil' => 'util',
            'ValidationUtil' => 'util',
            'WorkflowUtil' => 'util',
            'SystemListenersUtil' => 'util',
            'Loader' => 'legacy',
            'ZLanguageBrowser' => 'i18n',
            'EventManager' => 'EventManager',
            'Event' => 'EventManager',
            'CustomEventHandler' => 'EventManager');
    }
}
