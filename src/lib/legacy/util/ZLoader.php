<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Composer\Autoload\ClassLoader;

/**
 * ZLoader.
 * @deprecated
 */
class ZLoader
{
    /**
     * Autoloaders.
     *
     * @var ClassLoader
     */
    private static $autoloader;

    /**
     * Base setup.
     *
     * @param $autoloader
     *
     * @return void
     */
    public static function register($autoloader)
    {
        @trigger_error('ZLoader is deprecated. please use Composer and namespaces instead.', E_USER_DEPRECATED);

        spl_autoload_register(['ZLoader', 'autoload']);
        self::$autoloader = $autoloader;
    }

    /**
     * Add new autoloader to the stack.
     *
     * @param string $namespace Namespace
     * @param string $paths
     * @param string $separator Separator, _ or \\
     *
     * @internal param string $path Path
     * @return void
     */
    public static function addAutoloader($namespace, $paths = '', $separator = '_')
    {
        @trigger_error('ZLoader is deprecated. please use Composer and namespaces instead.', E_USER_DEPRECATED);

        $separator = $separator === '\\' ? '' : $separator;

        self::$autoloader->add($namespace.$separator, $paths);
    }

    public static function addPrefix($prefix, $paths)
    {
        @trigger_error('ZLoader is deprecated. please use Composer and namespaces instead.', E_USER_DEPRECATED);

        self::$autoloader->add($prefix, $paths);
    }

    public static function addPrefixPsr4($prefix, $paths)
    {
        @trigger_error('ZLoader is deprecated. please use Composer and namespaces instead.', E_USER_DEPRECATED);

        self::$autoloader->addPsr4($prefix, $paths);
    }

    /**
     * Simple PEAR autoloader and handling for non-PEAR classes.
     *
     * @param string $class Class name
     *
     * @return boolean
     */
    public static function autoload($class)
    {
        @trigger_error('ZLoader is deprecated. please use Composer and namespaces instead.', E_USER_DEPRECATED);

        // Classloader for SystemPlugin
        if (strpos($class, 'SystemPlugin') === 0) {
            $array = explode('_', $class);
            $pluginName = $array[1];
            $name = substr($class, strlen("SystemPlugin_{$pluginName}") + 1, strlen($class));
            $path = str_replace('_', '/', "plugins/$pluginName/$name.php");
            if (file_exists($path)) {
                return include $path;
            }
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
            $path = str_replace('_', '/', "$base/$moduleName/plugins/$pluginName/$name.php");
            if (file_exists($path)) {
                return include $path;
            }
            $path = str_replace('_', '/', "$base/$moduleName/plugins/$pluginName/lib/$pluginName/$name.php");
            if (file_exists($path)) {
                return include $path;
            }
        }

        // Classloader for Themes
        if (strpos($class, 'Themes') === 0) {
            $array = explode('_', $class);
            $themeName = $array[1];
            $name = substr($class, strlen("Themes") + 1, strlen($class));
            $path = str_replace('_', '/', "themes/$themeName/$name.php");
            if (file_exists($path)) {
                return include $path;
            }
            $path = str_replace('_', '/', "themes/$themeName/lib/$name.php");
            if (file_exists($path)) {
                return include $path;
            }
        }

        // generic PEAR style namespace to path, i.e Foo_Bar -> Foo/Bar.php
        if (strpos($class, '_')) {
            $array = explode('_', $class);
            $prefix = (isset($map[$array[0]]) ? $map[$array[0]] . '/' : '');
            $path = __DIR__.'/../'.$prefix . str_replace('_', '/', $class) . '.php';
            if (file_exists($path)) {
                return include $path;
            }
        }
    }
}
