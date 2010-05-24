<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Renderer class
 */

class Renderer extends Smarty
{
    private static $instance;

    public $module;
    public $toplevelmodule;
    public $modinfo;
    public $theme;
    public $themeinfo;
    public $language;
    public $baseurl;
    public $baseuri;
    public $cache_id;

    /**
     * Set if Theme is an active module and templates stored in database
     */
    public $userdb;

    public $expose_template;

    // translation domain of the calling module
    public $renderDomain;

    public function __construct($module = '', $caching = null)
    {
        // this property is Dwoo only but doesnt affect Smarty.
        $this->show_compat_errors = true;
        parent::__construct();

        // set the error reporting level
        $this->error_reporting = isset($GLOBALS['ZConfig']['Debug']['error_reporting']) ? $GLOBALS['ZConfig']['Debug']['error_reporting'] : E_ALL;
        $this->allow_php_tag = true;
        //$this->auto_literal = false;

        // Initialize the module property with the name of
        // the topmost module. Foor Hooks, Blocks, API Functions and others
        // you need to set this property to the name of the respective module!
        $this->toplevelmodule = ModUtil::getName();
        if (!$module) {
            $module = $this->toplevelmodule;
        }
        $this->module = array($module => ModUtil::getInfo(ModUtil::getIdFromName($module)));

        // initialise environment vars
        $this->language = ZLanguage::getLanguageCode();
        $this->baseurl = System::getBaseUrl();
        $this->baseuri = System::getBaseUri();

        //---- Plugins handling -----------------------------------------------
        // add plugin paths
        $this->themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));
        $this->theme = $theme = $this->themeinfo['directory'];

        $this->modinfo = $modinfo = ModUtil::getInfo(ModUtil::getIdFromName($module));
        $modpath = ($this->module[$module]['type'] == 3) ? 'system' : 'modules';
        switch ($this->module[$module]['type'])
        {
            case 2:
                $mpluginPath = "modules/" . $this->module[$module]['directory'] . "/templates/plugins";
                $mpluginPathOld = "modules/" . $this->module[$module]['directory'] . "/pntemplates/plugins";
                break;
            case 3:
                $mpluginPath = "system/" . $this->module[$module]['directory'] . "/templates/plugins";
                $mpluginPathOld = "system/" . $this->module[$module]['directory'] . "/pntemplates/plugins";
                break;
            default:
                $mpluginPath = "system/" . $this->module[$module]['directory'] . "/templates/plugins";
                $mpluginPathOld = "system/" . $this->module[$module]['directory'] . "/pntemplates/plugins";
        }

        $pluginpaths = array(
                'lib/render/plugins',
                'config/plugins',
                "themes/$theme/templates/modules/$module/plugins",
                "themes/$theme/plugins",
                $mpluginPath,
                $mpluginPathOld);

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
        $this->register_block('nocache', 'Renderer_block_nocache', false);

        // register resource type 'z' this defines the way templates are searched
        // during {include file='my_template.html'} this enables us to store selected module
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
        if ($this->module[$module]['i18n']) {
            $this->renderDomain = ZLanguage::getModuleDomain($this->module[$module]['name']);
        }

        // make render object available to modifiers
        $this->assign_by_ref('renderObject', $this, array('module' => $module, 'modinfo' => $modinfo, 'themeinfo' => $this->themeinfo));

        // This event sends $this as the subject so you can modify as required:
        // e.g.  $event->getSubject()->register_prefilter('foo');
        $event = new Event('render.init', $this);
        EventManagerUtil::notify($event);
    }

    /**
     * setup the current instance of the Renderer class and return it back to the module
     */
    public static function getInstance($module = null, $caching = null, $cache_id = null, $add_core_data = false)
    {
        if (!self::$instance) {
            self::$instance = new self($module, $caching);
        }

        if (!is_null($caching)) {
            self::$instance->caching = $caching;
        }

        if (!is_null($cache_id)) {
            self::$instance->cache_id = $cache_id;
        }

        if ($module === null) {
            $module = self::$instance->toplevelmodule;
        }

        if (!array_key_exists($module, self::$instance->module)) {
            self::$instance->module[$module] = ModUtil::getInfo(ModUtil::getIdFromName($module));
            //$instance->modinfo = ModUtil::getInfo(ModUtil::getIdFromName($module));
            self::$instance->_add_plugins_dir($module);
        }

        if ($add_core_data) {
            self::$instance->add_core_data();
        }

        // for {gt} template plugin to detect gettext domain
        if (self::$instance->module[$module]['i18n']) {
            self::$instance->renderDomain = ZLanguage::getModuleDomain(self::$instance->module[$module]['name']);
        }

        // load the usemodules configuration if exists
        $modpath = (self::$instance->module[$module]['type'] == 3) ? 'system' : 'modules';
        $usepath = "$modpath/" . self::$instance->module[$module]['directory'] . '/templates/config';
        $usepathOld = "$modpath/" . self::$instance->module[$module]['directory'] . '/pntemplates/config';
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
                        self::$instance->_add_plugins_dir(trim($addmod));
                    }
                }
            }
        }

        return self::$instance;
    }

    /**
     * Checks whether requested template exists.
     *
     * @param string $template
     */
    public function template_exists($template)
    {
        return (bool) $this->get_template_path($template);
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
        $modgetname = ModUtil::getName();

        foreach ($this->module as $module => $modinfo) {
            // prepare the values for OS
            $os_pnmodgetname = DataUtil::formatForOS($modgetname);
            $os_module = DataUtil::formatForOS($module);
            $os_theme = DataUtil::formatForOS($this->theme);

            // Define the locations in which we will look for templates
            // (in this order)
            // Note: Paths 1, 3, 5 and 7 - This allows for the hook or block functions
            // (such as ratings and comments) to use different templates depending
            // on the top level module. e.g. the comments dialog can be different
            // for news  and polls...
            // They are only evaluated when the calling module is not the current one.
            //
            // 1. The top level module directory in the requested module folder
            // in the theme directory.
            $array = array("themes/$os_theme/templates/modules/$os_module/$os_pnmodgetname",
                    "themes/$os_theme/templates/modules/$os_module",
                    "config/templates/$os_module/$os_pnmodgetname",
                    "config/templates/$os_module",
                    "modules/$os_module/templates/$os_pnmodgetname",
                    "modules/$os_module/templates",
                    "system/$os_module/templates/$os_pnmodgetname",
                    "system/$os_module/templates",
                    "modules/$os_module/pntemplates/$os_pnmodgetname",
                    "modules/$os_module/pntemplates",
                    "system/$os_module/pntemplates/$os_pnmodgetname",
                    "system/$os_module/pntemplates",
            );

            $themehookpath = "themes/$os_theme/templates/modules/$os_module/$os_pnmodgetname";
            // 2. The module directory in the current theme.
            $themepath = "themes/$os_theme/templates/modules/$os_module";
            // 3. The global template directory for the current top level module
            $globalhookpath = "config/templates/$os_module/$os_pnmodgetname";
            // 4. The global template directory
            $globalpath = "config/templates/$os_module";
            // 5. The top level module directory in the requested module folder
            // in the modules sub folder.
            $modhookpath = "modules/$os_module/pntemplates/$os_pnmodgetname";
            // 6. The module directory in the modules sub folder.
            $modpath = "modules/$os_module/pntemplates";
            // 7. The top level module directory in the requested module folder
            // in the system sub folder.
            $syshookpath = "system/$os_module/pntemplates/$os_pnmodgetname";
            // 8. The module directory in the system sub folder.
            $syspath = "system/$os_module/pntemplates";

            $ostemplate = DataUtil::formatForOS($template); //.'.htm';


            // check the module for which we're looking for a template is the
            // same as the top level mods. This limits the places to look for
            // templates.
            if ($module == $modgetname) {
                $search_path = array(

                        "themes/$os_theme/templates/modules/$os_module", // themepath
                        "config/templates/$os_module", //global path
                        "modules/$os_module/templates", // modpath
                        "system/$os_module/templates", // sysmodpath
                        "modules/$os_module/pntemplates", // modpath old
                        "system/$os_module/pntemplates", //sys mod path old
                );
            } else {
                $search_path = array("themes/$os_theme/templates/modules/$os_module/$os_pnmodgetname", // themehookpath
                        "themes/$os_theme/templates/modules/$os_module", // themepath
                        "config/templates/$os_module/$os_pnmodgetname", //globalhookpath
                        "config/templates/$os_module", //global path
                        "modules/$os_module/templates/$os_pnmodgetname", //modhookpath
                        "modules/$os_module/templates", // modpath
                        "system/$os_module/templates/$os_pnmodgetname", //sysmodhookpath
                        "system/$os_module/templates", // sysmodpath
                        "modules/$os_module/pntemplates/$os_pnmodgetname", // modhookpathold
                        "modules/$os_module/pntemplates", // modpath old
                        "system/$os_module/pntemplates/$os_pnmodgetname", //sysmodhookpath old
                        "system/$os_module/pntemplates", //sys mod path old
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
     * executes & returns the template results
     *
     * This returns the template output instead of displaying it.
     * Supply a valid template name.
     * As an optional second parameter, you can pass a cache id.
     * As an optional third parameter, you can pass a compile id.
     *
     * @param   string   $template    the name of the template
     * @param   string   $cache_id    (optional) the cache ID
     * @param   string   $compile_id  (optional) the compile ID
     * @param   boolean  $display
     * @param   boolean  $reset (optional) reset singleton defaults
     * @return  string   the template output
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
     * executes & displays the template results
     *
     * This displays the template.
     * Supply a valid template name.
     * As an optional second parameter, you can pass a cache id.
     * As an optional third parameter, you can pass a compile id.
     *
     * @param   string   $template    the name of the template
     * @param   string   $cache_id    (optional) the cache ID
     * @param   string   $compile_id  (optional) the compile ID
     * @return  void
     */
    public function display($template, $cache_id = null, $compile_id = null)
    {
        echo $this->fetch($template, $cache_id, $compile_id);
    }

    /**
     * finds out if a template is already cached
     *
     * This returns true if there is a valid cache for this template.
     * Right now, we are just passing it to the original Smarty function.
     * We might introduce a function to decide if the cache is in need
     * to be refreshed...
     *
     * @param   string   $template    the name of the template
     * @param   string   $cache_id    (optional) the cache ID
     * @return  boolean
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
     * clears the cache for a specific template
     *
     * This returns true if there is a valid cache for this template.
     * Right now, we are just passing it to the original Smarty function.
     * We might introduce a function to decide if the cache is in need
     * to be refreshed...
     *
     * @param   string   $template    the name of the template
     * @param   string   $cache_id    (optional) the cache ID
     * @param   string   $compile_id  (optional) the compile ID
     * @param   string   $expire      (optional) minimum age in sec. the cache file must be before it will get cleared.
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
     * clear all cached templates
     *
     * @param string $exp_time expire time
     * @return boolean results of {@link smarty_core_rm_auto()}
     */
    public function clear_all_cache($exp_time = null)
    {
        $res = parent::clear_cache(null, null, null, $exp_time);
        // recreate index.html file
        fclose(fopen($this->cache_dir . '/index.html', 'w'));
        return $res;
    }

    /**
     * clear all compiled templates
     *
     * @param string $exp_time expire time
     * @return boolean results of {@link smarty_core_rm_auto()}
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
     * set up paths for the template
     *
     * This function sets the template and the config path according
     * to where the template is found (Theme or Module directory)
     *
     * @param   string   $template   the template name
     * @access  private
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
     * @param   string   $module    well known module name
     * @access  private
     */
    private function _add_plugins_dir($module)
    {
        if (empty($module)) {
            return;
        }

        $modinfo = ModUtil::getInfo(ModUtil::getIdFromName($module));
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
     * add core data to the template
     *
     * This function adds some basic data to the template depending on the
     * current user and the PN settings.
     *
     * @param   list of module names. all mod vars of these modules will be included too
     *          The mod vars of the current module will always be included
     * @return  boolean true if ok, otherwise false
     * @access  public
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
        return true;
    }
}

/**
 * Smarty block function to prevent template parts from being cached
 *
 * @param $param
 * @param $content
 * @param $smarty
 * @return string
 **/
function Renderer_block_nocache($param, $content, &$smarty)
{
    return $content;
}

/**
 * Smarty resource function to determine correct path for template inclusion
 *
 * For more information about parameters see http://smarty.php.net/manual/en/template.resources.php
 *
 * @access  private
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

function z_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
{
    // get the timestamp of the last change of the $tpl_name file
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

function z_get_secure($tpl_name, &$smarty)
{
    // assume all templates are secure
    return true;
}

function z_get_trusted($tpl_name, &$smarty)
{
    // not used for templates
    return;
}

/**
 * Callback function for preg_replace_callback. Allows the use of {{ and }} as
 * delimiters within certain tags, even if they use { and } as block delimiters.
 *
 * @param   array   $matches    The $matches array from preg_replace_callback,
 *                              containing the matched groups.
 * @return  string  The replacement string for the match.
 */
function z_prefilter_add_literal_callback($matches)
{
    $tagOpen = $matches[1];
    $script = $matches[3];
    $tagClose = $matches[4];
    $script = str_replace('{{', '{/literal}{', $script);
    $script = str_replace('}}', '}{literal}', $script);

    $result = $tagOpen . '{literal}' . $script . '{/literal}' . $tagClose;

    return $result;
}

/**
 * Prefilter for tags that might contain { or } as block delimiters, such as
 * <script> or <style>. Allows the use of {{ and }} as smarty delimiters,
 * even if the language uses { and } as block delimters. Adds {literal} and
 * {/literal} to the specified opening and closing tags, and converts
 * {{ and }} to {/literal}{ and }{literal}.
 *
 * Tags affected: <script> and <style>
 *
 * @param   string  $tpl_source The template's source prior to prefiltering.
 * @param   Smarty  $smarty     A reference to the renderer object.
 * @return  string  The prefiltered template contents.
 */
function z_prefilter_add_literal($tpl_source, &$smarty)
{
    return preg_replace_callback('`(<(script|style)[^>]*>)(.*?)(</\2>)`s', 'z_prefilter_add_literal_callback', $tpl_source);
}

function z_prefilter_gettext_params($tpl_source, &$smarty)
{
    $tpl_source = (preg_replace_callback('#\{(.*?)\}#', create_function('$m', 'return z_prefilter_gettext_params_callback($m);'), $tpl_source));
    $tpl_source = (preg_replace_callback('#%%%(("|\')(.*)("|\'))%%%#', create_function('$m', 'return "{gt text=" . $m[1] ."}";'), $tpl_source));
    return $tpl_source;
}

function z_prefilter_gettext_params_callback($m)
{
    $m[1] = preg_replace('#__([a-zA-Z0-9]+=".*?(?<!\\\)")#', '$1|gt:$renderObject', $m[1]);
    $m[1] = preg_replace('#__([a-zA-Z0-9]+=\'.*?(?<!\\\)\')#', '$1|gt:$renderObject', $m[1]);
    return '{' . $m[1] . '}';
}

function z_prefilter_legacy($source, &$smarty)
{
    // save browserhacks like <!--[if lte IE 7]>
    $source = str_replace('<!--%91', '<@!@-@-@%@9@1', str_replace('%93-->', '%@9@3@-@-@>', $source));
    // rewrite the old delimiters to new
    $source = str_replace('<!--[', '{', str_replace(']-->', '}', $source));
    // handle delimiters inside <script> blocks.
    $source = preg_replace_callback('#\{(.*?)\}#', create_function('$m', 'return z_prefilter_legacy_callback($m);'), $source);
    // restore browser hacks
   return str_replace('<@!@-@-@%@9@1', '<!--[', str_replace('%@9@3@-@-@>', ']-->', $source));
}

function z_prefilter_legacy_callback($m)
{
    $m[1] = str_replace('|pndate_format', '|dateformat', $m[1]);
    $m[1] = preg_replace('#^(\s*)(/{0,1})pn([a-zA-Z0-9_]+)(\s*|$)#', '$1$2$3$4', $m[1]);
    $m[1] = preg_replace('#\|pn#', '|', $m[1]);
    return "{{$m[1]}}";
}
