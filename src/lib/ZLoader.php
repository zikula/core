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

use Symfony\Component\ClassLoader\ClassLoader;

if (!extension_loaded('xdebug')) {
    set_exception_handler('exception_handler');
}

define('ZLOADER_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

// setup vendors in include path
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR);

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
     * @var ClassLoader
     */
    private static $autoloaders;

    /**
     * Base setup.
     *
     * @return void
     */
    public static function register()
    {

        spl_autoload_register(array('ZLoader', 'autoload'));
        self::$autoloaders = new ClassLoader();
        self::$autoloaders->register();
        self::addAutoloader('Categories', 'system');
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
    public static function addAutoloader($namespace, $paths = '', $separator = '_')
    {
        $separator = $separator === '\\' ? '' : $separator;
        self::$autoloaders->addPrefix($namespace.$separator, $paths);
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
