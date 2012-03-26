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

use Symfony\Component\ClassLoader\UniversalClassLoader;

if (!extension_loaded('xdebug')) {
    set_exception_handler('exception_handler');
}

include 'lib/i18n/ZGettextFunctions.php';

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
     * Autoloaders.
     *
     * @var UniversalClassLoader
     */
    private static $autoloaders;

    /**
     * Autoloaders.
     *
     * @var \Zikula\Framework\ModuleClassLoader
     */
    private static $moduleLoader;

    /**
     * Base setup.
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register(array('ZLoader', 'autoload'));

        $autoloader = new UniversalClassLoader();
        $autoloader->register();
        $autoloader->registerNamespaces(array(
            'Zikula' => ZLOADER_PATH,
        ));

        self::$autoloaders = new UniversalClassLoader();
        self::$autoloaders->register();
        self::addAutoloader('Doctrine', ZLOADER_PATH . '/vendor/Doctrine1', '_');
        self::addAutoloader('Zikula', ZLOADER_PATH . '/legacy', '_');
        self::addAutoloader('Categories', 'system/Categories/lib', '_');
        self::addAutoloader('Zend', ZLOADER_PATH . '/vendor', '_');

        $mapClassLoader = new \Symfony\Component\ClassLoader\MapClassLoader(self::map());
        $mapClassLoader->register();

        self::$moduleLoader = new \Zikula\Framework\ModuleClassLoader();
        self::$moduleLoader->spl_autoload_register();
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
        if (in_array($namespace, self::$autoloaders->getNamespaces())) {
            return;
        }

        if ($separator == '_') {
            return self::$autoloaders->registerPrefix($namespace.'_', $path);
        }

        self::$autoloaders->register($namespace, $path, $separator);
    }

    public static function addModule($namespace, $path)
    {
        if (self::$moduleLoader->hasAutoloader($namespace)) {
            return;
        }

        self::$moduleLoader->register($namespace, $path);
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
        // Classloader for SystemPlugin
        if (strpos($class, 'SystemPlugin') === 0) {
            $pluginClass = str_replace('_', '\\', $class);
            $array = explode('\\', $pluginClass);
            $pluginName = $array[1];
            $name = substr($pluginClass, strlen("SystemPlugin\\{$pluginName}") + 1, strlen($pluginClass));
            $path = str_replace('\\', '/', "plugins/$pluginName/lib/$pluginName/$name.php");
            if (file_exists($path)) {
                return include $path;
            }
            $path = str_replace('\\', '/', "plugins/$pluginName/$name.php");
            if (file_exists($path)) {
                return include $path;
            }
        }

        // Classloader for ModulePlugin
        if (strpos($class, 'ModulePlugin') === 0) {
            $pluginClass = str_replace('_', '\\', $class);
            $array = explode('\\', $pluginClass);
            $moduleName = $array[1];
            $pluginName = $array[2];
            $modinfo = ModUtil::getInfoFromName($moduleName);
            $base = ($modinfo['type'] == ModUtil::TYPE_MODULE) ? 'modules' : 'system';
            $name = substr($pluginClass, strlen("ModulePlugin\\{$moduleName}\\{$pluginName}") + 1, strlen($pluginClass));
            $path = str_replace('\\', '/', "$base/$moduleName/plugins/$pluginName/lib/$pluginName/$name.php");
            if (file_exists($path)) {
                return include $path;
            }
            $path = str_replace('\\', '/', "$base/$moduleName/plugins/$pluginName/$name.php");
            if (file_exists($path)) {
                return include $path;
            }
        }

        // Classloader for Themes
        if (strpos($class, 'Themes\\') === 0 || strpos($class, 'Themes_') === 0) {
            $themeClass = str_replace('_', '\\', $class);
            $array = explode('\\', $themeClass);
            $themeName = $array[1];
            $name = substr($themeClass, strlen("Themes") + 1, strlen($themeClass));
            $path = str_replace('\\', '/', "themes/$themeName/lib/$name.php");
            if (file_exists($path)) {
                return include $path;
            }

            $path = str_replace('\\', '/', "themes/$themeName/$name.php");
            if (file_exists($path)) {
                return include $path;
            }
        }

        // generic PEAR style namespace to path, i.e Foo_Bar -> Foo/Bar.php
        if (strpos($class, '_') || strpos($class, '\\')) {
            $pearClass = str_replace('_', '\\', $class);
            $array = explode('\\', $pearClass);
            $prefix = (isset($map[$array[0]]) ? $map[$array[0]] . '/' : '');
            $path = ZLOADER_PATH . $prefix . str_replace('\\', '/', $pearClass) . '.php';
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
            'ZLanguage' => ZLOADER_PATH . '/i18n/ZLanguage.php',
            'ZI18n' => ZLOADER_PATH . '/i18n/ZI18n.php',
            'ZL10n' => ZLOADER_PATH . '/i18n/ZL10n.php',
            'ZLocale' => ZLOADER_PATH . '/i18n/ZLocale.php',
            'ZGettext' => ZLOADER_PATH . '/i18n/ZGettext.php',
            'ZMO' => ZLOADER_PATH . 'i18n/ZMO.php',
            'ZLanguageBrowser' => ZLOADER_PATH . 'i18n/ZLanguageBrowser.php',
            'DBObject' => ZLOADER_PATH . 'dbobject/DBObject.php',
            'DBObjectArray' => ZLOADER_PATH . 'dbobject/DBObjctArray.php',
            'DBUtil' => ZLOADER_PATH . 'util/DBUtil.php',
            'BlockUtil' => ZLOADER_PATH . 'util/BlockUtil.php',
            'AjaxUtil' => ZLOADER_PATH . 'util/AjaxUtil.php',
            'CacheUtil' => ZLOADER_PATH . 'util/CacheUtil.php',
            'CategoryRegistryUtil' => ZLOADER_PATH . 'util/CategoryRegistryUtil.php',
            'CategoryUtil' => ZLOADER_PATH . 'util/CategoryUtil.php',
            'CookieUtil' => ZLOADER_PATH . 'util/CookieUtil.php',
            'DataUtil' => ZLOADER_PATH . 'util/DataUtil.php',
            'DateUtil' => ZLOADER_PATH . 'util/DateUtil.php',
            'DoctrineHelper' => ZLOADER_PATH . 'util/DoctrineHelper.php',
            'DoctrineUtil' => ZLOADER_PATH . 'util/DoctrineUtil.php',
            'EventUtil' => ZLOADER_PATH . 'util/EventUtil.php',
            'FileUtil' => ZLOADER_PATH . 'util/FileUtil.php',
            'FilterUtil' => ZLOADER_PATH . 'util/FilterUtil.php',
            'FormUtil' => ZLOADER_PATH . 'util/FormUtil.php',
            'HookUtil' => ZLOADER_PATH . 'util/HookUtil.php',
            'HtmlUtil' => ZLOADER_PATH . 'util/HtmlUtil.php',
            'JCSSUtil' => ZLOADER_PATH . 'util/JCSSUtil.php',
            'LogUtil' => ZLOADER_PATH . 'util/LogUtil.php',
            'ModUtil' => ZLOADER_PATH . 'util/ModUtil.php',
            'ObjectUtil' => ZLOADER_PATH . 'util/ObjectUtil.php',
            'PluginUtil' => ZLOADER_PATH . 'util/PluginUtil.php',
            'PageUtil' => ZLOADER_PATH . 'util/PageUtil.php',
            'RandomUtil' => ZLOADER_PATH . 'util/RandomUtil.php',
            'SecurityUtil' => ZLOADER_PATH . 'util/SecurityUtil.php',
            'ServiceUtil' => ZLOADER_PATH . 'util/ServiceUtil.php',
            'SessionUtil' => ZLOADER_PATH . 'util/SessionUtil.php',
            'StringUtil' => ZLOADER_PATH . 'util/StringUtil.php',
            'System' => ZLOADER_PATH . 'util/System.php',
            'ThemeUtil' => ZLOADER_PATH . 'util/ThemeUtil.php',
            'UserUtil' => ZLOADER_PATH . 'util/UserUtil.php',
            'ValidationUtil' => ZLOADER_PATH . 'util/ValidationUtil.php',
            'Loader' => ZLOADER_PATH . 'legacy/Loader.php',
            'sfYaml' => ZLOADER_PATH . 'vendor/Doctrine1/Doctrine/Parser/sfYaml/sfYaml.php', // needed to use Doctrine_Parser since we dont use Doctrine's autoloader
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
