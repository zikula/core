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

use \Zikula\Common\ServiceManager\ServiceManager;

/**
 * Zikula_View_Plugin for plugin system.
 */
class Zikula_View_Plugin extends Zikula_View
{
    /**
     * The plugin name.
     *
     * @var string
     */
    protected $pluginName;

    /**
     * Constructor.
     *
     * @param ServiceManager $serviceManager ServiceManager.
     * @param string         $module         Module name ("zikula" for system plugins).
     * @param string         $pluginName     Plugin name.
     * @param integer|null   $caching        Whether or not to cache (Zikula_View::CACHE_*) or use config variable (null).
     */
    public function __construct(ServiceManager $serviceManager, $module = 'zikula', $pluginName, $caching = null)
    {
        parent::__construct($serviceManager, $module, $caching);

        $this->pluginName = $pluginName;

        if ($this->modinfo['type'] == ModUtil::TYPE_CORE) {
            $path = "plugins/{$pluginName}/templates/plugins";
        } else {
            $base = ModUtil::getBaseDir($this->modinfo['name']);
            $path = "$base/{$this->modinfo['directory']}/plugins/{$pluginName}/templates/plugins";
        }

        $this->addPluginDir($path);
    }

    /**
     * Plugin name getter.
     *
     * @return string The plugin name.
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * Setup the current instance of the Zikula_View class and return it back to the module.
     *
     * @param string       $moduleName Module name.
     * @param string       $pluginName Plugin name.
     * @param integer|null $caching    Whether or not to cache (Zikula_View::CACHE_*) or use config variable (null).
     * @param string       $cache_id   Cache Id.
     *
     * @return Zikula_View_Plugin instance.
     */
    public static function getPluginInstance($moduleName, $pluginName, $caching = null, $cache_id = null)
    {
        $serviceManager = ServiceUtil::getManager();
        $serviceId = strtolower(sprintf('zikula.renderplugin.%s.%s', $moduleName, $pluginName));

        if (!$serviceManager->hasService($serviceId)) {
            $view = new self($serviceManager, $moduleName, $pluginName, $caching);
            $serviceManager->attachService($serviceId, $view);
        } else {
            return $serviceManager->getService($serviceId);
        }

        if (!is_null($caching)) {
            $view->caching = $caching;
        }

        if (!is_null($cache_id)) {
            $view->cache_id = $cache_id;
        }

        if ($moduleName === null) {
            $moduleName = $view->toplevelmodule;
        }

        if (!array_key_exists($moduleName, $view->module)) {
            $view->module[$moduleName] = ModUtil::getInfoFromName($moduleName);
            //$instance->modinfo = ModUtil::getInfoFromName($module);
            $view->_addPluginsDir($moduleName);
        }

        // for {gt} template plugin to detect gettext domain
        if ($view->module[$moduleName]['type'] == ModUtil::TYPE_MODULE || $view->module[$moduleName]['type'] == ModUtil::TYPE_SYSTEM) {
            $view->renderDomain = ZLanguage::getModulePluginDomain($view->module[$moduleName]['name'], $view->pluginName);
        } elseif ($view->module[$moduleName]['type'] == ModUtil::TYPE_CORE) {
            $view->renderDomain = ZLanguage::getSystemPluginDomain($view->module[$moduleName]['name'], $view->pluginName);
        }

        return $view;
    }

    /**
     * Add a plugins dir to _plugin_dir property array.
     *
     * @param string $module Module name.
     * @param string $plugin Plugin name.
     *
     * @return void
     */
    private function _addPluginsDir($module, $plugin)
    {
        if (empty($module)) {
            return;
        }

        $modinfo = ModUtil::getInfoFromName($module);
        if (!$modinfo) {
            return;
        }

        switch ($modinfo['type'])
        {
            case ModUtil::TYPE_SYSTEM:
                $pluginsDir = "system/{$modinfo['directory']}/plugins/$plugin/templates/plugins";
                break;
            case ModUtil::TYPE_MODULE:
                $pluginsDir = "modules/{$modinfo['directory']}/plugins/$plugin/templates/plugins";
                break;
            case ModUtil::TYPE_CORE:
                $pluginsDir = "plugins/$plugin/templates/plugins";
                break;
        }

        $this->addPluginDir($pluginsDir);
    }

    /**
     * Checks which path to use for required template.
     *
     * @param string $template Template name.
     *
     * @return string Template path.
     */
    public function get_template_path($template)
    {
        static $cache = array();

        if (isset($cache[$template])) {
            return $cache[$template];
        }

        // the current module
        //$modname = ModUtil::getName();

        foreach ($this->module as $module => $modinfo) {
            // prepare the values for OS
            $module = $modinfo['name'];
            $os_module = DataUtil::formatForOS($module);
            //$os_theme = DataUtil::formatForOS($this->theme);
            $os_dir = ($modinfo['type'] == ModUtil::TYPE_MODULE) ? 'modules' : 'system';

            $ostemplate = DataUtil::formatForOS($template);

            // check the module for which we're looking for a template is the
            // same as the top level mods. This limits the places to look for
            // templates.
            $base = ($modinfo['type'] == ModUtil::TYPE_CORE) ? '' : "$os_dir/$os_module/";
            //$configPath = ($modinfo['type'] == ModUtil::TYPE_CORE) ? 'zikula/' : "$os_module/";
            $search_path = array(
                        //"config/plugins/$configPath/{$this->pluginName}/templates", //global path
                        "{$base}plugins/{$this->pluginName}/templates"
            );

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
