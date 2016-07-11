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
 * Zikula_View_Resource class.
 *
 * @deprecated
 */
class Zikula_View_Resource
{
    /**
     * Resources cache.
     *
     * @var array
     */
    public static $cache = [];

    /**
     * Dynamic loader of plugins under cache.
     *
     * @param string $method    Method called.
     * @param array  $arguments Array of arguments.
     */
    public function __call($method, $arguments)
    {
        if (preg_match('/^load_([^_]*?)_(.*?)$/', $method, $matches)) {
            $type = $matches[1];
            $name = $matches[2];
            $func = "smarty_{$type}_{$name}";

            switch ($type) {
                case 'function':
                    if (self::load($arguments[1], $type, $name)) {
                        return $func($arguments[0], $arguments[1]);
                    }
                    break;

                case 'block':
                    if (self::load($arguments[2], $type, $name)) {
                        return $func($arguments[0], $arguments[1], $arguments[2]);
                    }
                    break;
            }
        }
    }

    /**
     * Get an instance of this class.
     *
     * @return Zikula_View_Resource This instance.
     */
    public static function getInstance()
    {
        $serviceManager = ServiceUtil::getManager();
        $serviceId = 'zikula.viewresource';
        if (!$serviceManager->has($serviceId)) {
            $obj = new self();
            $serviceManager->set($serviceId, $obj);
        } else {
            $obj = $serviceManager->get($serviceId);
        }

        return $obj;
    }

    /**
     * Smarty resource function to determine correct path for template inclusion.
     *
     * For more information about parameters see http://smarty.php.net/manual/en/template.resources.php.
     *
     * @param string $resource Template name.
     * @param string      &$tpl_source Template source.
     * @param Zikula_View $view Reference to Smarty instance.
     *
     * @return boolean
     */
    public static function z_get_template($resource, &$tpl_source, $view)
    {
        // check if the z resource sent by Smarty is a cached insert
        if (strpos($resource, 'insert.') === 0) {
            return self::z_get_insert($resource, $tpl_source, $view);
        }

        // it is a template
        // determine the template path and store the template source
        $tpl_path = $view->get_template_path($resource);

        if ($tpl_path !== false) {
            $tpl_source = file_get_contents(DataUtil::formatForOS($tpl_path . '/' . $resource));

            return true;
        }

        return LogUtil::registerError(__f('Error! The template [%1$s] is not available in the [%2$s] module.',
                                      [$resource, $view->toplevelmodule]));
    }

    /**
     * Get the timestamp of the last change of the $tpl_name file.
     *
     * @param string $tpl_name Template name.
     * @param string      &$tpl_timestamp Template timestamp.
     * @param Zikula_View $view Reference to Smarty instance.
     *
     * @return boolean
     */
    public static function z_get_timestamp($tpl_name, &$tpl_timestamp, $view)
    {
        // get path, checks also if tpl_name file_exists and is_readable
        $tpl_path = $view->get_template_path($tpl_name);

        if ($tpl_path !== false) {
            $tpl_timestamp = filemtime(DataUtil::formatForOS($tpl_path . '/' . $tpl_name));

            return true;
        }

        return false;
    }

    /**
     * Checks whether or not a template is secure.
     *
     * @param string      $tpl_name Template name.
     * @param Zikula_View $view     Reference to Smarty instance.
     *
     * @return boolean
     */
    public static function z_get_secure($tpl_name, $view)
    {
        // assume all templates are secure
        return true;
    }

    /**
     * Whether or not the template is trusted.
     *
     * @param string      $tpl_name Template name.
     * @param Zikula_View $view     Reference to Smarty instance.
     *
     * @return void
     */
    public static function z_get_trusted($tpl_name, $view)
    {
        // not used for templates
        // used on PHP scripts requested by include_php or insert with script attr.
        return;
    }

    /**
     * Smarty block function to prevent template parts from being cached
     *
     * @param array       $params  Tag parameters.
     * @param string      $content Block content.
     * @param Zikula_View $view    Reference to smarty instance.
     *
     * @return string
     */
    public static function block_nocache($params, $content, $view)
    {
        if (isset($content)) {
            return $content;
        }
    }

    /**
     * Resource function to determine correct path for insert inclusion.
     *
     * @param string $insert Template name.
     * @param string      &$tpl_source Template source.
     * @param Zikula_View $view Reference to Smarty instance.
     *
     * @return boolean
     */
    private static function z_get_insert($insert, &$tpl_source, $view)
    {
        $name = str_replace(strrchr($insert, '.'), '', substr($insert, strpos($insert, '.') + 1));

        if (!isset(self::$cache['insert'][$name])) {
            self::register($view, 'insert', $name, false);
        }

        if (!self::$cache['insert'][$name]) {
            return LogUtil::registerError(__f('Error! The insert [%1$s] is not available in the [%2$s] module.',
                                          [$insert, $view->toplevelmodule]));
        }

        return true;
    }

    /**
     * Resource function to register a resource.
     *
     * @param Zikula_View $view         Reference to Smarty instance.
     * @param string      $type         Type of the resource.
     * @param string      $name         Name of the resource.
     * @param boolean     $delayed_load Whether to register the plugin with lazy load or not (default: true).
     * @param boolean     $cacheable    Flag to register the resource as cacheable (default: false).
     * @param mixed       $cache_attrs  Array of parameters to be cached with the plugin/block call.
     *
     * @return boolean
     */
    public static function register($view, $type, $name, $delayed_load = true, $cacheable = true, $cache_attrs = null)
    {
        if ($delayed_load || self::load($view, $type, $name)) {
            $callable = ($type != 'insert') ? [self::getInstance(), "load_{$type}_{$name}"] : "smarty_{$type}_{$name}";

            $view->_plugins[$type][$name] = [$callable, null, null, $delayed_load, $cacheable, $cache_attrs];

            return true;
        }

        return false;
    }

    /**
     * Resource function to load a resource located inside the plugins folders.
     *
     * @param Zikula_View $view Reference to Smarty instance.
     * @param string      $type Type of the resource.
     * @param string      $name Name of the resource.
     *
     * @return boolean
     */
    public static function load($view, $type, $name)
    {
        if (isset(self::$cache[$type][$name])) {
            return self::$cache[$type][$name];
        }

        self::$cache[$type][$name] = false;

        foreach ((array)$view->plugins_dir as $_plugin_dir) {
            $filepath = "$_plugin_dir/$type.$name.php";

            if (@is_readable($filepath)) {
                include_once $filepath;

                if (!function_exists("smarty_{$type}_{$name}")) {
                    $view->_trigger_fatal_error(__f('[View %1$s] \'%2$s\' is not implemented', [$type, $name]), null, null, __FILE__, __LINE__);

                    return false;
                }

                self::$cache[$type][$name] = true;
                break;
            }
        }

        return self::$cache[$type][$name];
    }
}
