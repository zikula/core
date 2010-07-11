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
 * Zikula_View class.
 */
class Zikula_View extends Smarty
{
    /**
     * Module name.
     *
     * @var string
     */
    public $module;

    /**
     * Top level module.
     *
     * @var string
     */
    public $toplevelmodule;

    /**
     * Module info.
     *
     * @var array
     */
    public $modinfo;

    /**
     * Theme name.
     *
     * @var string
     */
    public $theme;

    /**
     * Theme info.
     *
     * @var array
     */
    public $themeinfo;

    /**
     * Language.
     *
     * @var string
     */
    public $language;

    /**
     * Base Url.
     *
     * @var string
     */
    public $baseurl;

    /**
     * Base Uri.
     *
     * @var string
     */
    public $baseuri;

    /**
     * Cache Id.
     *
     * @var string
     */
    public $cache_id;

    /**
     * Set if Theme is an active module and templates stored in database.
     *
     * @var boolean
     */
    public $userdb;

    /**
     * Whether or not to expose template.
     *
     * @var boolean
     */
    public $expose_template;

    /**
     * Translation domain of the calling module.
     *
     * @var string
     */
    public $domain;

    /**
     * @var Zikula_ServiceManager
     */
    protected $serviceManager;

    /**
     * @var Zikula_EventManager
     */
    protected $eventManager;

    /**
     * Constructor.
     *
     * @param string       $module  Module name ("zikula" for system plugins).
     * @param boolean|null $caching Whether or not to cache (boolean) or use config variable (null).
     */
    public function __construct($module = '', $caching = null)
    {
        parent::__construct();

        $this->serviceManager = ServiceUtil::getManager();
        $this->eventManager = EventUtil::getManager();

        // set the error reporting level
        $this->error_reporting = isset($GLOBALS['ZConfig']['Debug']['error_reporting']) ? $GLOBALS['ZConfig']['Debug']['error_reporting'] : E_ALL;
        $this->allow_php_tag = true;
        //$this->auto_literal = false;

        // Initialize the module property with the name of
        // the topmost module. For Hooks, Blocks, API Functions and others
        // you need to set this property to the name of the respective module!
        $this->toplevelmodule = ModUtil::getName();
        if (!$module) {
            $module = $this->toplevelmodule;
        }
        $this->module = array($module => ModUtil::getInfoFromName($module));

        // initialise environment vars
        $this->language = ZLanguage::getLanguageCode();
        $this->baseurl = System::getBaseUrl();
        $this->baseuri = System::getBaseUri();

        //---- Plugins handling -----------------------------------------------
        // add plugin paths
        $this->themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));
        $this->theme = $theme = $this->themeinfo['directory'];

        $this->modinfo = $modinfo = ModUtil::getInfoFromName($module);
        $modpath = ($this->module[$module]['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';
        switch ($this->module[$module]['type'])
        {
            case ModUtil::TYPE_MODULE :
                $mpluginPath = "modules/" . $this->module[$module]['directory'] . "/templates/plugins";
                $mpluginPathOld = "modules/" . $this->module[$module]['directory'] . "/pntemplates/plugins";
                break;
            case ModUtil::TYPE_SYSTEM :
                $mpluginPath = "system/" . $this->module[$module]['directory'] . "/templates/plugins";
                $mpluginPathOld = "system/" . $this->module[$module]['directory'] . "/pntemplates/plugins";
                break;
            default:
                $mpluginPath = "system/" . $this->module[$module]['directory'] . "/templates/plugins";
                $mpluginPathOld = "system/" . $this->module[$module]['directory'] . "/pntemplates/plugins";
        }

        $pluginpaths[] = 'lib/view/plugins';
        if (System::isLegacyMode()) {
            $pluginpaths[] = 'lib/legacy/plugins';
        }
        $pluginpaths[] = 'config/plugins';
        $pluginpaths[] = "themes/$theme/templates/modules/$module/plugins";
        $pluginpaths[] = "themes/$theme/plugins";
        $pluginpaths[] = $mpluginPath;
        $pluginpaths[] = $mpluginPathOld;

        foreach ($pluginpaths as $pluginpath) {
            if (file_exists($pluginpath)) {
                array_push($this->plugins_dir, $pluginpath);
            }
        }

        // check if the recent 'type' parameter in the URL is admin and if yes,
        // include (modules|system)/Admin/pntemplates/plugins to the plugins_dir array
        $type = FormUtil::getPassedValue('type', null, 'GETPOST');
        if ($type === 'admin') {
            array_push($this->plugins_dir, 'system/Admin/templates/plugins');
            $this->load_filter('output', 'admintitle');
        }

        //---- Cache handling -------------------------------------------------
        if (isset($caching) && is_bool($caching)) {
            $this->caching = $caching;
        } else {
            $this->caching = ModUtil::getVar('Theme', 'render_cache');
        }

        if (isset($_POST) && count($_POST) != 0) {
            // write actions should not be cached or weird things happen
            $this->caching = false;
        }

        $this->cache_lifetime = ModUtil::getVar('Theme', 'render_lifetime');
        $this->cache_dir = CacheUtil::getLocalDir() . '/Renderer_cache';
        $this->compile_check = ModUtil::getVar('Theme', 'render_compile_check');
        $this->force_compile = ModUtil::getVar('Theme', 'render_force_compile');

        $this->compile_dir = CacheUtil::getLocalDir() . '/Renderer_compiled';
        $this->compile_id = $this->toplevelmodule . '_' . $theme . '_' . Zlanguage::getLanguageCode();
        $this->cache_id = '';
        $this->expose_template = (ModUtil::getVar('Theme', 'render_expose_template') == true) ? true : false;
        $this->register_block('nocache', 'Zikula_View_block_nocache', false);

        // register resource type 'z' this defines the way templates are searched
        // during {include file='my_template.tpl'} this enables us to store selected module
        // templates in the theme while others can be kept in the module itself.
        $this->register_resource('z', array(
                'z_get_template',
                'z_get_timestamp',
                'z_get_secure',
                'z_get_trusted'));

        // set 'z' as default resource type
        $this->default_resource_type = 'z';

        // For ajax requests we use the short urls filter to 'fix' relative paths
        if (($GLOBALS['loadstages'] & System::CORE_STAGES_AJAX) && System::getVar('shorturls')) {
            $this->load_filter('output', 'shorturls');
        }

        // register prefilters
        $this->register_prefilter('z_prefilter_add_literal');

        if ($GLOBALS['ZConfig']['System']['legacy_prefilters']) {
            $this->register_prefilter('z_prefilter_legacy');
        }

        $this->register_prefilter('z_prefilter_gettext_params');

        // Assign some useful theme settings
        //$this->assign(ThemeUtil::getVar()); // TODO A [investigate - this appears to always be empty and causes loops] (drak)
        $this->assign('baseurl', $this->baseurl);
        $this->assign('baseuri', $this->baseuri);
        $this->assign('themepath', $this->baseurl . 'themes/' . $theme);
        $this->assign('stylepath', $this->baseurl . 'themes/' . $theme . '/style');
        $this->assign('scriptpath', $this->baseurl . 'themes/' . $theme . '/javascript');
        $this->assign('imagepath', $this->baseurl . 'themes/' . $theme . '/images');
        $this->assign('imagelangpath', $this->baseurl . 'themes/' . $theme . '/images/' . $this->language);

        // for {gt} template plugin to detect gettext domain
        if ($this->module[$module]['type'] == ModUtil::TYPE_MODULE) {
            $this->domain = ZLanguage::getModuleDomain($this->module[$module]['name']);
        }

        // make render object available to modifiers
        $this->assign('zikula_view', $this);

        // Add ServiceManager and EventManager to all templates
        $this->assign('serviceManager', $this->serviceManager);
        $this->assign('eventManager', $this->eventManager);

        // add some useful data
        $this->assign(array('module' => $module, 'modinfo' => $this->modinfo, 'themeinfo' => $this->themeinfo));

        // This event sends $this as the subject so you can modify as required:
        // e.g.  $event->getSubject()->register_prefilter('foo');
        $event = new Zikula_Event('render.init', $this);
        $this->eventManager->notify($event);
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getToplevelmodule()
    {
        return $this->toplevelmodule;
    }

    public function getModinfo()
    {
        return $this->modinfo;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function getThemeinfo()
    {
        return $this->themeinfo;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getBaseurl() {
        return $this->baseurl;
    }

    public function getBaseuri()
    {
        return $this->baseuri;
    }

    public function getCache_id()
    {
        return $this->cache_id;
    }

    public function getUserdb()
    {
        return $this->userdb;
    }

    public function getExpose_template()
    {
        return $this->expose_template;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Set cache ID.
     *
     * @param string $id Cache ID.
     *
     * @return Zikula_View
     */
    public function setCache_Id($id)
    {
        $this->cache_id = $cache_id;
        return $this;
    }

    /**
     * Set compile check.
     *
     * @param $boolean
     *
     * @retrun Zikula_View
     */
    public function setCompile_check($boolean)
    {
        $this->compile_check = $boolean;
        return $this;
    }

    /**
     * Setup the current instance of the Zikula_View class and return it back to the module.
     *
     * @param string       $module        Module name.
     * @param boolean|null $caching       Whether or not to cache (boolean) or use config variable (null).
     * @param string       $cache_id      Cache Id.
     * @param boolean      $add_core_data Add core data to render data.
     *
     * @return Zikula_View
     */
    public static function getInstance($module = null, $caching = null, $cache_id = null, $add_core_data = false)
    {
        if (is_null($module)) {
            $module = ModUtil::getName();
        }

        $sm = ServiceUtil::getManager();
        $serviceId = strtolower(sprintf('zikula.render.%s', $module));
        if (!$sm->hasService($serviceId)) {
            $render = new self($module, $caching);
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

        if ($module === null) {
            $module = $render->toplevelmodule;
        }

        if (!array_key_exists($module, $render->module)) {
            $render->module[$module] = ModUtil::getInfoFromName($module);
            //$instance->modinfo = ModUtil::getInfoFromName($module);
            $render->_add_plugins_dir($module);
        }

        if ($add_core_data) {
            $render->add_core_data();
        }

        // for {gt} template plugin to detect gettext domain
        if ($render->module[$module]['type'] == ModUtil::TYPE_MODULE) {
            $render->domain = ZLanguage::getModuleDomain($render->module[$module]['name']);
        }

        // load the usemodules configuration if exists
        $modpath = ($render->module[$module]['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';
        $usepath = "$modpath/" . $render->module[$module]['directory'] . '/templates/config';
        $usepathOld = "$modpath/" . $render->module[$module]['directory'] . '/pntemplates/config';
        $usemod_confs = array();
        $usemod_confs[] = "$usepath/usemodules.txt";
        $usemod_confs[] = "$usepathOld/usemodules.txt";
        $usemod_confs[] = "$usepath/usemodules"; // backward compat for < 1.2 // TODO A depreciate from 1.4
        // load the config file
        foreach ($usemod_confs as $usemod_conf) {
            if (is_readable($usemod_conf) && is_file($usemod_conf)) {
                $additionalmodules = file($usemod_conf);
                if (is_array($additionalmodules)) {
                    foreach ($additionalmodules as $addmod) {
                        $render->_add_plugins_dir(trim($addmod));
                    }
                }
            }
        }

        return $render;
    }

    /**
     * Get module plugin renderer instance.
     *
     * @param string       $modName       Module name.
     * @param string       $pluginName    Plugin name.
     * @param boolean|null $caching       Whether or not to cache (boolean) or use config variable (null).
     * @param string       $cache_id      Cache Id.
     * @param boolean      $add_core_data Add core data to render data.
     *
     * @return PluginRender
     */
    public static function getModulePluginInstance($modName, $pluginName, $caching = null, $cache_id = null, $add_core_data = false)
    {
        return Zikula_View_Plugin::getInstance($modName, $pluginName, $caching, $cache_id, $add_core_data);
    }

    /**
     * Get system plugin renderer instance.
     *
     * @param string       $pluginName    Plugin name.
     * @param boolean|null $caching       Whether or not to cache (boolean) or use config variable (null).
     * @param string       $cache_id      Cache Id.
     * @param boolean      $add_core_data Add core data to render data.
     *
     * @return PluginRender
     */
    public static function getSystemPluginInstance($pluginName, $caching = null, $cache_id = null, $add_core_data = false)
    {
        $modName = 'zikula';
        return Zikula_View_Plugin::getInstance($modName, $pluginName, $caching, $cache_id, $add_core_data);
    }

    /**
     * Checks whether requested template exists.
     *
     * @param string $template Template name.
     *
     * @return boolean
     */
    public function template_exists($template)
    {
        return (bool)$this->get_template_path($template);
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
        $modname = ModUtil::getName();

        foreach ($this->module as $module => $modinfo) {
            // prepare the values for OS
            $module = $modinfo['name'];

            $os_modname = DataUtil::formatForOS($modname);
            $os_module = DataUtil::formatForOS($module);
            $os_theme = DataUtil::formatForOS($this->theme);
            $os_dir = $modinfo['type'] == ModUtil::TYPE_MODULE ? 'modules' : 'system';

            $ostemplate = DataUtil::formatForOS($template); //.'.tpl';

            // check the module for which we're looking for a template is the
            // same as the top level mods. This limits the places to look for
            // templates.
            if ($module == $modname) {
                $search_path = array(
                        "themes/$os_theme/templates/modules/$os_module", // themepath
                        "config/templates/$os_module", //global path
                        "$os_dir/$os_module/templates", // modpath
                        "$os_dir/$os_module/pntemplates", // modpath old
                );
            } else {
                $search_path = array("themes/$os_theme/templates/modules/$os_module/$os_modname", // themehookpath
                        "themes/$os_theme/templates/modules/$os_module", // themepath
                        "config/templates/$os_module/$os_modname", //globalhookpath
                        "config/templates/$os_module", //global path
                        "$os_dir/$os_module/templates/$os_modname", //modhookpath
                        "$os_dir/$os_module/templates", // modpath
                        "$os_dir/$os_module/pntemplates/$os_modname", // modhookpathold
                        "$os_dir/$os_module/pntemplates", // modpath old
                );
            }

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

    /**
     * Executes & returns the template results.
     *
     * This returns the template output instead of displaying it.
     * Supply a valid template name.
     * As an optional second parameter, you can pass a cache id.
     * As an optional third parameter, you can pass a compile id.
     *
     * @param string  $template   The name of the template.
     * @param string  $cache_id   The cache ID (optional).
     * @param string  $compile_id The compile ID (optional).
     * @param boolean $display    Whether or not to display directly (optional).
     * @param boolean $reset      Reset singleton defaults (optional).
     *
     * @return string The template output.
     */
    public function fetch($template, $cache_id = null, $compile_id = null, $display = false, $reset = true)
    {
        $this->_setup_template($template);

        if (!is_null($cache_id)) {
            $cache_id = $this->baseurl . '_' . $this->toplevelmodule . '_' . $cache_id;
        } else {
            $cache_id = $this->baseurl . '_' . $this->toplevelmodule . '_' . $this->cache_id;
        }

        $output = parent::fetch($template, $cache_id, $compile_id, $display);

        if ($this->expose_template == true) {
            $template = DataUtil::formatForDisplay($template);
            $output = "\n<!-- Start " . $this->template_dir . "/$template -->\n" . $output . "\n<!-- End " . $this->template_dir . "/$template -->\n";
        }

        // now we've got our output from this module reset our instance
        if ($reset) {
            //             $this->module = $this->toplevelmodule;
        }

        return $output;
    }

    /**
     * Executes & displays the template results.
     *
     * This displays the template.
     * Supply a valid template name.
     * As an optional second parameter, you can pass a cache id.
     * As an optional third parameter, you can pass a compile id.
     *
     * @param string $template   The name of the template.
     * @param string $cache_id   The cache ID (optional).
     * @param string $compile_id The compile ID (optional).
     *
     * @return boolean
     */
    public function display($template, $cache_id = null, $compile_id = null)
    {
        echo $this->fetch($template, $cache_id, $compile_id);
        return true;
    }

    /**
     * Finds out if a template is already cached.
     *
     * This returns true if there is a valid cache for this template.
     * Right now, we are just passing it to the original Smarty function.
     * We might introduce a function to decide if the cache is in need
     * to be refreshed...
     *
     * @param string $template   The name of the template.
     * @param string $cache_id   The cache ID (optional).
     * @param string $compile_id The compile ID (optional).
     *
     * @return boolean
     */
    public function is_cached($template, $cache_id = null, $compile_id = null)
    {
        // insert the condition to check the cache here!
        // if (functioncheckdb($this -> module)) {
        //        return parent :: clear_cache($template, $this -> cache_id);
        //}
        $this->_setup_template($template);

        if ($cache_id) {
            $cache_id = $this->baseurl . '_' . $this->toplevelmodule . '_' . $cache_id;
        } else {
            $cache_id = $this->baseurl . '_' . $this->toplevelmodule . '_' . $this->cache_id;
        }

        if (!isset($compile_id)) {
            $compile_id = $this->compile_id;
        }

        return parent::is_cached($template, $cache_id, $compile_id);
    }

    /**
     * Clears the cache for a specific template.
     *
     * This returns true if there is a valid cache for this template.
     * Right now, we are just passing it to the original Smarty function.
     * We might introduce a function to decide if the cache is in need
     * to be refreshed...
     *
     * @param string $template   The name of the template.
     * @param string $cache_id   The cache ID (optional).
     * @param string $compile_id The compile ID (optional).
     * @param string $expire     Minimum age in sec. the cache file must be before it will get cleared (optional).
     *
     * @return  boolean
     */
    public function clear_cache($template = null, $cache_id = null, $compile_id = null, $expire = null)
    {
        if ($cache_id) {
            $cache_id = $this->baseurl . '_' . $this->toplevelmodule . '_' . $cache_id;
        } else {
            $cache_id = $this->baseurl . '_' . $this->toplevelmodule . '_' . $this->cache_id;
        }

        return parent::clear_cache($template, $cache_id, $compile_id, $expire);
    }

    /**
     * Clear all cached templates.
     *
     * @param string $exp_time Expire time.
     *
     * @return boolean Results of {@link smarty_core_rm_auto()}.
     */
    public function clear_all_cache($exp_time = null)
    {
        $res = parent::clear_cache(null, null, null, $exp_time);
        // recreate index.html file
        fclose(fopen($this->cache_dir . '/index.html', 'w'));
        return $res;
    }

    /**
     * Clear all compiled templates.
     *
     * @param string $exp_time Expire time.
     *
     * @return boolean Results of {@link smarty_core_rm_auto()}.
     */
    public function clear_compiled($exp_time = null)
    {
        //unset($this->compile_id); // commented out because this causes an E_NOTICE in Smarty.class.php:1156
        $res = parent::clear_compiled_tpl(null, null, $exp_time);
        // recreate index.html file
        fclose(fopen($this->compile_dir . '/index.html', 'w'));
        return $res;
    }

    /**
     * Assign variable to template.
     *
     * @param string $tpl_var Variable name.
     * @param mixed  $value   Value.
     *
     * @return Zikula_View
     */
    function assign($tpl_var, $value = null)
    {
        parent::assign($tpl_var, $value);
        return $this;
    }

    /**
     * Set Caching.
     *
     * @param boolean $boolean True or false.
     *
     * @return Zikula_View
     */
    public function setCaching($boolean)
    {
        $this->caching = (bool)$boolean;
        return $this;
    }

    /**
     * Set cache lifetime.
     *
     * @param integer $time Lifetime in seconds.
     *
     * @return Zikula_View
     */
    public function setCache_lifetime($time)
    {
        $this->cache_lifetime = $time;
        return $this;
    }

    /**
     * Set up paths for the template.
     *
     * This function sets the template and the config path according
     * to where the template is found (Theme or Module directory)
     *
     * @param string $template The template name.
     *
     * @access private
     * @return void
     */
    public function _setup_template($template)
    {
        // default directory for templates
        $this->template_dir = $this->get_template_path($template);
        //echo $this->template_dir . '<br>';
        $this->config_dir = $this->template_dir . '/config';
    }

    /**
     * add a plugins dir to _plugin_dir array
     *
     * This function takes  module name and adds two path two the plugins_dir array
     * when existing
     *
     * @param string $module Well known module name.
     *
     * @access private
     * @return void
     */
    private function _add_plugins_dir($module)
    {
        if (empty($module)) {
            return;
        }

        $modinfo = ModUtil::getInfoFromName($module);
        if (!$modinfo) {
            return;
        }

        $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';
        $mod_plugs = "$modpath/$modinfo[directory]/templates/plugins";
        $mod_plugsOld = "$modpath/$modinfo[directory]/pntemplates/plugins";

        if (file_exists($mod_plugs)) {
            array_push($this->plugins_dir, $mod_plugs);
        }

        if (file_exists($mod_plugsOld)) {
            array_push($this->plugins_dir, $mod_plugsOld);
        }
    }

    /**
     * Add core data to the template.
     *
     * This function adds some basic data to the template depending on the
     * current user and the PN settings.
     *
     * @return Zikula_View
     */
    public function add_core_data()
    {
        $core = array();
        $core['version_num'] = System::VERSION_NUM;
        $core['version_id'] = System::VERSION_ID;
        $core['version_sub'] = System::VERSION_SUB;
        $core['logged_in'] = UserUtil::isLoggedIn();
        $core['language'] = $this->language;
        $core['themeinfo'] = $this->themeinfo;

        // add userdata
        $core['user'] = UserUtil::getVars(SessionUtil::getVar('uid'));

        // add modvars of current modules
        foreach ($this->module as $module => $dummy) {
            $core[$module] = ModUtil::getVar($module);
        }

        // add mod vars of all modules supplied as parameter
        $modulenames = func_get_args();
        foreach ($modulenames as $modulename) {
            // if the modulename is empty do nothing
            if (!empty($modulename) && !is_array($modulename) && !array_key_exists($modulename, $this->module)) {
                // check if user wants to have /PNConfig
                if ($modulename == PN_CONFIG_MODULE) {
                    $ZConfig = ModUtil::getVar(PN_CONFIG_MODULE);
                    foreach ($ZConfig as $key => $value) {
                        // gather all config vars
                        $core['ZConfig'][$key] = $value;
                    }
                } else {
                    $core[$modulename] = ModUtil::getVar($modulename);
                }
            }
        }

        // Module vars
        // TODO A [move old pncore assignment into pnRender subclass] (Guite)
        $this->assign('pncore', $core);
        $this->assign('zcore', $core);
        return $this;
    }
}

/**
 * Smarty block function to prevent template parts from being cached
 *
 * @param array  $param   Tag parameters.
 * @param string $content Block content.
 * @param Smarty &$smarty Reference to smarty instance.
 *
 * @return string
 **/
function Zikula_View_block_nocache($param, $content, &$smarty)
{
    return $content;
}

/**
 * Smarty resource function to determine correct path for template inclusion.
 *
 * For more information about parameters see http://smarty.php.net/manual/en/template.resources.php.
 *
 * @param string $tpl_name    Template name.
 * @param string &$tpl_source Template source.
 * @param Smarty &$smarty     Reference to Smarty instance.
 *
 * @access private
 * @return boolean
 */
function z_get_template($tpl_name, &$tpl_source, &$smarty)
{
    // determine the template path and store the template source
    // in $tpl_source


    // get path, checks also if tpl_name file_exists and is_readable
    $tpl_path = $smarty->get_template_path($tpl_name);

    if ($tpl_path !== false) {
        $tpl_source = file_get_contents(DataUtil::formatForOS($tpl_path . '/' . $tpl_name));
        if ($tpl_source !== false) {
            return true;
        }
    }

    return LogUtil::registerError(__f('Error! The template [%1$s] is not available in the [%2$s] module.', array(
            $tpl_name,
            $smarty->toplevelmodule)));
}

/**
 * Get the timestamp of the last change of the $tpl_name file.
 *
 * @param string $tpl_name       Template name.
 * @param string &$tpl_timestamp Template timestamp.
 * @param Smarty &$smarty        Reference to Smarty instance.
 *
 * @return boolean
 */
function z_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
{
    // get path, checks also if tpl_name file_exists and is_readable
    $tpl_path = $smarty->get_template_path($tpl_name);

    if ($tpl_path !== false) {
        $tpl_timestamp = filemtime(DataUtil::formatForOS($tpl_path . '/' . $tpl_name));
        if ($tpl_timestamp !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Checks whether or not a template is secure.
 *
 * @param string $tpl_name Template name.
 * @param Smarty &$smarty  Reference to Smarty instance.
 *
 * @return boolean
 */
function z_get_secure($tpl_name, &$smarty)
{
    // assume all templates are secure
    return true;
}

/**
 * Whether or not the template is trusted.
 *
 * @param string $tpl_name Template name.
 * @param Smarty &$smarty  Reference to Smarty instance.
 *
 * @return void
 */
function z_get_trusted($tpl_name, &$smarty)
{
    // not used for templates
    return;
}

/**
 * Callback function for preg_replace_callback.
 *
 * Allows the use of {{ and }} as delimiters within certain tags,
 * even if they use { and } as block delimiters.
 *
 * @param array $matches The $matches array from preg_replace_callback, containing the matched groups.
 *
 * @return string The replacement string for the match.
 */
function z_prefilter_add_literal_callback($matches)
{
    $tagOpen = $matches[1];
    $script = $matches[3];
    $tagClose = $matches[4];

    if (System::hasLegacyTemplates()) {
        $script = str_replace('<!--[', '{{', str_replace(']-->', '}}', $script));
    }
    $script = str_replace('{{', '{/literal}{', str_replace('}}', '}{literal}', $script));

    return $tagOpen . '{literal}' . $script . '{/literal}' . $tagClose;
}

/**
 * Prefilter for tags that might contain { or } as block delimiters.
 *
 * Such as <script> or <style>. Allows the use of {{ and }} as smarty delimiters,
 * even if the language uses { and } as block delimters. Adds {literal} and
 * {/literal} to the specified opening and closing tags, and converts
 * {{ and }} to {/literal}{ and }{literal}.
 *
 * Tags affected: <script> and <style>.
 *
 * @param string $tpl_source The template's source prior to prefiltering.
 * @param Smarty &$smarty    A reference to the renderer object.
 *
 * @return string The prefiltered template contents.
 */
function z_prefilter_add_literal($tpl_source, &$smarty)
{
    return preg_replace_callback('`(<(script|style)[^>]*>)(.*?)(</\2>)`s', 'z_prefilter_add_literal_callback', $tpl_source);
}

/**
 * Prefilter for gettext parameters.
 *
 * @param string $tpl_source The template's source prior to prefiltering.
 * @param Smarty &$smarty    A reference to the renderer object.
 *
 * @return string The prefiltered template contents.
 */
function z_prefilter_gettext_params($tpl_source, &$smarty)
{
    $tpl_source = (preg_replace_callback('#\{(.*?)\}#', create_function('$m', 'return z_prefilter_gettext_params_callback($m);'), $tpl_source));
    $tpl_source = (preg_replace_callback('#%(("|\')(.*)("|\'))%#', create_function('$m', 'return "{gt text=" . $m[1] ."}";'), $tpl_source));
    return $tpl_source;
}

/**
 * Callback function for self::z_prefilter_gettext_params().
 *
 * @param string $m Tag token.
 *
 * @return string
 */
function z_prefilter_gettext_params_callback($m)
{
    $m[1] = preg_replace('#__([a-zA-Z0-9]+=".*?(?<!\\\)")#', '$1|gt:$zikula_view', $m[1]);
    $m[1] = preg_replace('#__([a-zA-Z0-9]+=\'.*?(?<!\\\)\')#', '$1|gt:$zikula_view', $m[1]);
    return '{' . $m[1] . '}';
}

/**
 * Prefilter for legacy tag delemitters.
 *
 * @param string $source  The template's source prior to prefiltering.
 * @param Smarty &$smarty A reference to the renderer object.
 *
 * @return string The prefiltered template contents.
 */
function z_prefilter_legacy($source, &$smarty)
{
    // rewrite the old delimiters to new.
    $source = str_replace('<!--[', '{', str_replace(']-->', '}', $source));

    // handle old plugin names and return.
    return preg_replace_callback('#\{(.*?)\}#', create_function('$m', 'return z_prefilter_legacy_callback($m);'), $source);
}

/**
 * Callback function for self::z_prefilter_legacy().
 *
 * @param string $m Tag token.
 *
 * @return string
 */
function z_prefilter_legacy_callback($m)
{
    $m[1] = str_replace('|pndate_format', '|dateformat', $m[1]);
    $m[1] = str_replace('pndebug', 'zdebug', $m[1]);
    $m[1] = preg_replace('#^(\s*)(/{0,1})pn([a-zA-Z0-9_]+)(\s*|$)#', '$1$2$3$4', $m[1]);
    $m[1] = preg_replace('#\|pn#', '|', $m[1]);
    return "{{$m[1]}}";
}
