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
 * Zikula_Form_View implementation for system and module plugins.
 * To get Zikula_Form_View_Plugin instance:
 *  * <code>
 *   $view = FormUtil::newForm('pluginName', $this); // $this has to be instance of Zikula_Controller_AbstractPlugin
 * </code>
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_View_Plugin extends Zikula_Form_View
{
    public function __construct(Zikula_ServiceManager $serviceManager, $module, $pluginName, $caching = null)
    {
        @trigger_error('Legacy form system is deprecated, please use Symfony forms instead.', E_USER_DEPRECATED);

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
     * @return string The plugin name
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * Setup the current instance of the Zikula_View class and return it back to the module.
     *
     * @param string       $moduleName Module name
     * @param string       $pluginName Plugin name
     * @param integer|null $caching    Whether or not to cache (Zikula_View::CACHE_*) or use config variable (null)
     * @param string       $cache_id   Cache Id
     *
     * @return Zikula_View_Plugin instance
     */
    public static function getPluginInstance($moduleName, $pluginName, $caching = null, $cache_id = null)
    {
        $serviceManager = ServiceUtil::getManager();
        $serviceId = strtolower(sprintf('zikula.renderplugin.%s.%s', $moduleName, $pluginName));

        if (!$serviceManager->has($serviceId)) {
            $view = new self($serviceManager, $moduleName, $pluginName, $caching);
            $serviceManager->set($serviceId, $view);
        } else {
            return $serviceManager->get($serviceId);
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
            $view->domain = ZLanguage::getModulePluginDomain($view->module[$moduleName]['name'], $view->getPluginName());
        } elseif ($view->module[$moduleName]['type'] == ModUtil::TYPE_CORE) {
            $view->domain = ZLanguage::getSystemPluginDomain($view->getPluginName());
        }

        return $view;
    }

    /**
     * Add a plugins dir to _plugin_dir property array.
     *
     * @param string $module Module name
     * @param string $plugin Plugin name
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

        switch ($modinfo['type']) {
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
     * @param string $template Template name
     *
     * @return string Template path
     */
    public function get_template_path($template)
    {
        static $cache = [];

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
            $search_path = [
                //"config/plugins/$configPath/{$this->pluginName}/templates", //global path
                "{$base}plugins/{$this->pluginName}/templates"
            ];

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
