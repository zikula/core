<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_View_Resource class.
 */
class Zikula_View_Resource
{
    /**
     * Resources cache
     */
    static $cache = array();

    /**
     * Smarty resource function to determine correct path for template inclusion.
     *
     * For more information about parameters see http://smarty.php.net/manual/en/template.resources.php.
     *
     * @param string      $tpl_name    Template name.
     * @param string      &$tpl_source Template source.
     * @param Zikula_View $view        Reference to Smarty instance.
     *
     * @access private
     * @return boolean
     */
    static function z_get_template($tpl_name, &$tpl_source, $view)
    {
        if (strpos($tpl_name, 'insert.') === 0) {
            return self::z_get_insert($tpl_name, &$tpl_source, $view);
        }

        // determine the template path and store the template source
        // checks also if tpl_name file_exists and is_readable
        $tpl_path = $view->get_template_path($tpl_name);

        if ($tpl_path !== false) {
            $tpl_source = file_get_contents(DataUtil::formatForOS($tpl_path . '/' . $tpl_name));
            return true;
        }

        return LogUtil::registerError(__f('Error! The template [%1$s] is not available in the [%2$s] module.',
                                      array($tpl_name, $view->toplevelmodule)));
    }

    /**
     * Resource function to determine correct path for insert inclusion.
     *
     * @param string      $tpl_name    Template name.
     * @param string      &$tpl_source Template source.
     * @param Zikula_View $view        Reference to Smarty instance.
     *
     * @access private
     * @return boolean
     */
    static function z_get_insert($tpl_name, &$tpl_source, $view)
    {
        if (!isset(self::$cache['inserts'][$tpl_name])) {
            self::$cache['inserts'][$tpl_name] = false;

            foreach ((array)$view->plugins_dir as $_plugin_dir) {
                $filepath = "$_plugin_dir/$tpl_name";

                if (@is_readable($filepath)) {
                    include_once $filepath;

                    $name = str_replace(strrchr($tpl_name, '.'), '', substr($tpl_name, strpos($tpl_name, '.')+1));
                    $insert_func = 'smarty_insert_' . $name;

                    if (!function_exists($insert_func)) {
                        $view->_trigger_fatal_error(__f("[insert] '%s' is not implemented", $name), null, null, __FILE__, __LINE__);
                    }
                    $view->_plugins['insert'][$name] = array($insert_func, null, null, false, true);

                    self::$cache['inserts'][$tpl_name] = true;
                }
            }
        }

        if (!self::$cache['inserts'][$tpl_name]) {
            return LogUtil::registerError(__f('Error! The insert [%1$s] is not available in the [%2$s] module.',
                                          array($tpl_name, $view->toplevelmodule)));
        }

        return true;
    }

    /**
     * Get the timestamp of the last change of the $tpl_name file.
     *
     * @param string      $tpl_name       Template name.
     * @param string      &$tpl_timestamp Template timestamp.
     * @param Zikula_View $view           Reference to Smarty instance.
     *
     * @return boolean
     */
    static function z_get_timestamp($tpl_name, &$tpl_timestamp, $view)
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
    static function z_get_secure($tpl_name, $view)
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
    static function z_get_trusted($tpl_name, $view)
    {
        // not used for templates
        // used on PHP scripts requested by include_php or insert with script attr.
        return;
    }

    /**
     * Smarty block function to prevent template parts from being cached
     *
     * @param array       $param   Tag parameters.
     * @param string      $content Block content.
     * @param Zikula_View $view    Reference to smarty instance.
     *
     * @return string
     */
    static function block_nocache($params, $content, $view)
    {
        if (isset($content)) {
            return $content;
        }
    }
}
