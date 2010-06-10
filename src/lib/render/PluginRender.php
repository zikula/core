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


class PluginRender extends Renderer
{
    public $pluginName;

    public function __construct($module = 'zikula', $pluginName, $caching = null)
    {
        parent::__construct($module, $caching);
        $this->pluginName = $pluginName;
        $modinfo = $this->module[$module];
        if ($modinfo['type'] == ModUtil::TYPE_CORE) {
            $path = "plugins/{$pluginName}/templates/plugins";
        } else {
            $base = ($modinfo['type'] == ModUtil::TYPE_MODULE) ? 'module' : 'system';
            $modPath = $modinfo['name'];
            $path = "$base/$modPath/plugins/{$pluginName}/templates/plugins";
        }
        array_push($this->plugins_dir, $path);
    }


    /**
     * setup the current instance of the Renderer class and return it back to the module
     */
    public static function getInstance($moduleName, $pluginName, $caching = null, $cache_id = null, $add_core_data = false)
    {
        $sm = ServiceUtil::getManager();
        $serviceId = strtolower(sprintf('zikula.renderplugin.%s.%s', $moduleName, $pluginName));
        if (!$sm->hasService($serviceId)) {
            $render = new self($moduleName, $pluginName, $caching);
            $sm->attachService($serviceId, $render);
        } else {
            $render = $sm->getService($serviceId);
        }

        if (!is_null($caching)) {
            $render->caching = $caching;
        }

        if (!is_null($cache_id)) {
            $render->cache_id = $cache_id;
        }

        if ($moduleName === null) {
            $moduleName = $render->toplevelmodule;
        }

        if (!array_key_exists($moduleName, $render->module)) {
            $render->module[$moduleName] = ModUtil::getInfo(ModUtil::getIdFromName($moduleName));
            //$instance->modinfo = ModUtil::getInfo(ModUtil::getIdFromName($module));
            $render->_add_plugins_dir($moduleName);
        }

        if ($add_core_data) {
            $render->add_core_data();
        }

        // for {gt} template plugin to detect gettext domain
        if ($render->module[$moduleName]['type'] == ModUtil::TYPE_MODULE) {
            $render->renderDomain = ZLanguage::getModulePluginDomain($render->module[$moduleName]['name'], $render->pluginName);
        } elseif ($render->module[$moduleName]['type'] == ModUtil::TYPE_CORE) {
            $render->renderDomain = ZLanguage::getSystemPluginDomain($render->module[$moduleName]['name'], $render->pluginName);
        }

        return $render;
    }

    /**
     * add a plugins dir to _plugin_dir array
     *
     * This function takes  module name and adds two path two the plugins_dir array
     * when existing
     *
     * @param   string   $module    well known module name
     * @access  private
     */
    private function _add_plugins_dir($module, $plugin)
    {
        if (empty($module)) {
            return;
        }

        $modinfo = ModUtil::getInfo(ModUtil::getIdFromName($module));
        if (!$modinfo) {
            return;
        }

        switch ($modinfo['type'])
        {
            case ModUtil::TYPE_SYSTEM:
                $mod_plugs = "system/$modinfo[directory]/plugins/$plugin/templates/plugins";
                break;
            case ModUtil::TYPE_MODULE:
                $mod_plugs = "modules/$modinfo[directory]/plugins/$plugin/templates/plugins";
                break;
            case ModUtil::TYPE_CORE:
                $mod_plugs = "plugins/$plugin/templates/plugins";
                break;
        }


        if (file_exists($mod_plugs)) {
            array_push($this->plugins_dir, $mod_plugs);
        }
    }

    /**
     * Checks which path to use for required template
     *
     * @param string $template
     */
    public function get_template_path($template)
    {
        static $cache = array();

        if (isset($cache[$template])) {
            return $cache[$template];
        }

        // the current module
        $modname = ModUtil::getName();

        foreach ($this->module as $module => $modinfo) {
            // prepare the values for OS
            $os_modname = DataUtil::formatForOS($modname);
            $os_module = DataUtil::formatForOS($module);
            //$os_theme = DataUtil::formatForOS($this->theme);
            $os_dir = ($modinfo['type'] == ModUtil::TYPE_MODULE) ? 'modules' : 'system';

            $ostemplate = DataUtil::formatForOS($template);

            // check the module for which we're looking for a template is the
            // same as the top level mods. This limits the places to look for
            // templates.
            $base = ($modinfo['type'] == ModUtil::TYPE_CORE) ? '' : "$os_dir/$os_module/";
            $configPath = ($modinfo['type'] == ModUtil::TYPE_CORE) ? 'zikula/' : "$os_module/";
            $search_path = array(
                        //"config/plugins/$configPath/{$this->pluginName}/templates", //global path
                        "{$base}plugins/{$this->pluginName}/templates",
                );
                        var_dump($search_path);

            foreach ($search_path as $path) {
                if (is_readable("$path/$ostemplate")) {
                    $cache[$template] = $path;
                    return $path;
                }
            }
        }

        // when we arrive here, no path was found
        return false;
    }
}