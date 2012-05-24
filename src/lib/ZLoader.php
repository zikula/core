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
// For < PHP 5.3.0
if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 8192);
}
if (!defined('E_USER_DEPRECATED')) {
    define('E_USER_DEPRECATED', 16384);
}

if (!extension_loaded('xdebug')) {
    set_exception_handler('exception_handler');
}

include 'lib/i18n/ZGettextFunctions.php';
include 'lib/Zikula/KernelClassLoader.php';

define('ZLOADER_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

// setup vendors in include path
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR);

include 'Smarty/Smarty.class.php';

/**
 * ZLoader.
 */
class ZLoader
{
    /**
     * Map.
     *
     * @var array
     */
    private static $map;
    /**
     * Autoloaders.
     *
     * @var Zikula_KernelClassLoader
     */
    private static $autoloaders;

    /**
     * Base setup.
     *
     * @return void
     */
    public static function register()
    {
        self::$map = self::map();
        spl_autoload_register(array('ZLoader', 'autoload'));
        self::$autoloaders = new Zikula_KernelClassLoader();
        self::$autoloaders->spl_autoload_register();
        self::addAutoloader('Doctrine', ZLOADER_PATH . '/vendor/Doctrine');
        self::addAutoloader('Categories', 'system/Categories/lib');
        self::addAutoloader('Zend_Log', ZLOADER_PATH . '/vendor');
        self::addAutoloader('Symfony', ZLOADER_PATH . '/vendor', '\\');
    }

    /**
     * Add new autoloader to the stack.
     *
     * @param string $namespace Namespace.
     * @param string $path      Path.
     * @param string $separator Separator, _ or \\.
     *
     * @return void
     */
    public static function addAutoloader($namespace, $path = '', $separator = '_')
    {
        if (self::$autoloaders->hasAutoloader($namespace)) {
            return;
        }

        self::$autoloaders->register($namespace, $path, $separator);
    }

    /**
     * Simple PEAR autoloader and handling for non-PEAR classes.
     *
     * @param string $class Class name.
     *
     * @return boolean
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

        // Classloader for SystemPlugin
        if (strpos($class, 'SystemPlugin') === 0) {
            $array = explode('_', $class);
            $pluginName = $array[1];
            $name = substr($class, strlen("SystemPlugin_{$pluginName}") + 1, strlen($class));
            $path = str_replace('_', '/', "plugins/$pluginName/lib/$pluginName/$name.php");
            if (file_exists($path)) {
                return include $path;
            }
        }

        // Classloader for ModulePlugin
        if (strpos($class, 'ModulePlugin') === 0) {
            $array = explode('_', $class);
            $moduleName = $array[1];
            $pluginName = $array[2];
            $modinfo = ModUtil::getInfoFromName($moduleName);
            $base = ($modinfo['type'] == ModUtil::TYPE_MODULE) ? 'modules' : 'system';
            $name = substr($class, strlen("ModulePlugin_{$moduleName}_{$pluginName}") + 1, strlen($class));
            $path = str_replace('_', '/', "$base/$moduleName/plugins/$pluginName/lib/$pluginName/$name.php");
            if (file_exists($path)) {
                return include $path;
            }
        }

        // Classloader for ModulePlugin
        if (strpos($class, 'Themes') === 0) {
            $array = explode('_', $class);
            $themeName = $array[1];
            $name = substr($class, strlen("Themes") + 1, strlen($class));
            $path = str_replace('_', '/', "themes/$themeName/lib/$name.php");
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

    /**
     * Provides map for simple autoloader.
     *
     * @return array Class locations.
     */
    public static function map()
    {
        return array(
                'ZLanguage' => 'i18n',
                'ZI18n' => 'i18n',
                'ZL10n' => 'i18n',
                'ZLocale' => 'i18n',
                'ZGettext' => 'i18n',
                'ZMO' => 'i18n',
                'ZLanguageBrowser' => 'i18n',
                'DBObject' => 'dbobject',
                'DBObjectArray' => 'dbobject',
                'DBUtil' => 'util',
                'BlockUtil' => 'util',
                'AjaxUtil' => 'util',
                'CacheUtil' => 'util',
                'CategoryRegistryUtil' => 'util',
                'CategoryUtil' => 'util',
                'CookieUtil' => 'util',
                'DataUtil' => 'util',
                'DateUtil' => 'util',
                'DoctrineUtil' => 'util',
                'EventUtil' => 'util',
                'FileUtil' => 'util',
                'FilterUtil' => 'util',
                'FormUtil' => 'util',
                'HookUtil' => 'util',
                'HtmlUtil' => 'util',
                'JCSSUtil' => 'util',
                'LogUtil' => 'util',
                'ModUtil' => 'util',
                'ObjectUtil' => 'util',
                'PluginUtil' => 'util',
                'PageUtil' => 'util',
                'RandomUtil' => 'util',
                'SecurityUtil' => 'util',
                'ServiceUtil' => 'util',
                'SessionUtil' => 'util',
                'StringUtil' => 'util',
                'System' => 'util',
                'ThemeUtil' => 'util',
                'UserUtil' => 'util',
                'ValidationUtil' => 'util',
                'Loader' => 'legacy',
                'sfYaml' => 'vendor/Doctrine/Doctrine/Parser/sfYaml', // needed to use Doctrine_Parser since we dont use Doctrine's autoloader
        );
    }

}

/**
 * Exit.
 *
 * @param string  $msg  Message.
 * @param boolean $html True for html.
 *
 * @deprecated since 1.3.0
 *
 * @return false
 */
function z_exit($msg, $html = true)
{
    if ($html) {
        $msg = DataUtil::formatForDisplayHTML($msg);
    }
    LogUtil::registerError($msg);
    trigger_error($msg, E_USER_ERROR);

    return false;
    //throw new Zikula_Exception_Fatal($msg);
}

/**
 * Default exception handler.
 *
 * PHP by default doesn't display uncaught exception stacktraces in HTML.
 * This function halts execution of PHP after is finishes.
 *
 * @param Exception $e Exception to handle.
 *
 * @return void
 */
function exception_handler(Exception $e)
{
    echo "<pre>";
    echo 'Uncaught exception ' . $e->getMessage() . ' in ' . $e->getFile() . ' line, ' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "</pre>";
}
