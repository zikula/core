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
class Zikula_View extends Smarty implements Zikula_TranslatableInterface
{
    /**
     * Module info array, indexed by module name.
     *
     * @var array
     */
    public $module;

    /**
     * Top level module.
     *
     * @var string
     */
    public $toplevelmodule;

    /**
     * Module name.
     *
     * @var string
     */
    public $moduleName;

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
     * Template path (populated by fetch).
     * 
     * @var string
     */
    protected $templatePath;

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
     * The service manager instance.
     *
     * @var Zikula_ServiceManager
     */
    protected $serviceManager;

    /**
     * The event manager instance.
     *
     * @var Zikula_EventManager
     */
    protected $eventManager;

    /**
     * Request object.
     *
     * @var Zikula_Request_Http
     */
    protected $request;

    /**
     * Zikula controller.
     *
     * @var Zikula_AbstractController
     */
    protected $controller;

    /**
     * Templates.
     *
     * @var array
     */
    protected $templatePaths = array();

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager.
     * @param string                $moduleName     Module name ("zikula" for system plugins).
     * @param boolean|null          $caching        Whether or not to cache (boolean) or use config variable (null).
     */
    public function __construct(Zikula_ServiceManager $serviceManager, $moduleName = '', $caching = null)
    {
        $this->serviceManager = $serviceManager;
        $this->eventManager = $this->serviceManager->getService('zikula.eventmanager');
        $this->request = $this->serviceManager->getService('request');

        // set the error reporting level
        $this->error_reporting = isset($GLOBALS['ZConfig']['Debug']['error_reporting']) ? $GLOBALS['ZConfig']['Debug']['error_reporting'] : E_ALL;
        $this->allow_php_tag = true;

        // Initialize the module property with the name of
        // the topmost module. For Hooks, Blocks, API Functions and others
        // you need to set this property to the name of the respective module!
        $this->toplevelmodule = ModUtil::getName();
        $this->moduleName = ModUtil::getName();
        if (!$moduleName) {
            $moduleName = $this->toplevelmodule;
        }
        $this->module = array($moduleName => ModUtil::getInfoFromName($moduleName));

        // initialise environment vars
        $this->language = ZLanguage::getLanguageCode();
        $this->baseurl = System::getBaseUrl();
        $this->baseuri = System::getBaseUri();

        //---- Plugins handling -----------------------------------------------
        // add plugin paths
        $this->themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));
        $this->theme = $theme = $this->themeinfo['directory'];

        $this->modinfo = ModUtil::getInfoFromName($moduleName);

        switch ($this->module[$moduleName]['type'])
        {
            case ModUtil::TYPE_MODULE :
                $mpluginPath = "modules/" . $this->module[$moduleName]['directory'] . "/templates/plugins";
                $mpluginPathOld = "modules/" . $this->module[$moduleName]['directory'] . "/pntemplates/plugins";
                break;
            case ModUtil::TYPE_SYSTEM :
                $mpluginPath = "system/" . $this->module[$moduleName]['directory'] . "/templates/plugins";
                $mpluginPathOld = "system/" . $this->module[$moduleName]['directory'] . "/pntemplates/plugins";
                break;
            default:
                $mpluginPath = "system/" . $this->module[$moduleName]['directory'] . "/templates/plugins";
                $mpluginPathOld = "system/" . $this->module[$moduleName]['directory'] . "/pntemplates/plugins";
        }

        // Add standard plugin search path
        $this->addPluginDir('config/plugins'); // Official override
        $this->addPluginDir("themes/$theme/templates/modules/$moduleName/plugins"); // Module override in themes
        $this->addPluginDir("themes/$theme/plugins"); // Theme plugins
        $this->addPluginDir($mpluginPath); // Plugins for current module
        if (System::isLegacyMode()) {
            $this->addPluginDir($mpluginPathOld); // Module plugins (legacy paths)
            $this->addPluginDir('lib/legacy/plugins'); // Core legacy plugins
        }
        $this->addPluginDir('lib/viewplugins'); // Core plugins

        // check if the recent 'type' parameter in the URL is admin and if yes,
        // include system/Admin/templates/plugins to the plugins_dir array
        $type = FormUtil::getPassedValue('type', null, 'GETPOST');
        if ($type === 'admin') {
            $this->addPluginDir('system/Admin/templates/plugins');
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
        $this->cache_dir = CacheUtil::getLocalDir() . '/view_cache';
        $this->compile_check = ModUtil::getVar('Theme', 'render_compile_check');
        $this->force_compile = ModUtil::getVar('Theme', 'render_force_compile');

        $this->compile_dir = CacheUtil::getLocalDir() . '/view_compiled';
        $this->compile_id = '';
        $this->cache_id = '';
        $this->expose_template = (ModUtil::getVar('Theme', 'render_expose_template') == true) ? true : false;
        $this->register_block('nocache', 'Zikula_View_block_nocache', false);

        // register resource type 'z' this defines the way templates are searched
        // during {include file='my_template.tpl'} this enables us to store selected module
        // templates in the theme while others can be kept in the module itself.
        $this->register_resource('z', array('z_get_template',
                                            'z_get_timestamp',
                                            'z_get_secure',
                                            'z_get_trusted'));

        // set 'z' as default resource type
        $this->default_resource_type = 'z';

        // For ajax requests we use the short urls filter to 'fix' relative paths
        if (($this->serviceManager->getService('zikula')->getStage() & Zikula_Core::STAGE_AJAX) && System::getVar('shorturls')) {
            $this->load_filter('output', 'shorturls');
        }

        // register prefilters
        $this->register_prefilter('z_prefilter_add_literal');

        if ($GLOBALS['ZConfig']['System']['legacy_prefilters']) {
            $this->register_prefilter('z_prefilter_legacy');
        }

        $this->register_prefilter('z_prefilter_gettext_params');
        $this->register_prefilter('z_prefilter_notifyfilters');

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
        if ($this->module[$moduleName]['type'] == ModUtil::TYPE_MODULE) {
            $this->domain = ZLanguage::getModuleDomain($this->module[$moduleName]['name']);
        }

        // make render object available to modifiers
        parent::assign('zikula_view', $this);

        // Add ServiceManager and EventManager to all templates
        parent::assign('serviceManager', $this->serviceManager);
        parent::assign('eventManager', $this->eventManager);
        parent::assign('zikula_core', $this->serviceManager->getService('zikula'));
        parent::assign('modvars', ModUtil::getModvars()); // Get all modvars from any modules that have accessed their modvars at least once.

        $this->add_core_data();

        // Metadata for SEO
        if (!isset($this->serviceManager['zikula_view.metatags'])) {
            $this->serviceManager['zikula_view.metatags'] = new ArrayObject(array());
        }

        parent::assign('metatags', $this->serviceManager['zikula_view.metatags']);

        // add some useful data
        $this->assign(array('module' => $moduleName,
                            'modinfo' => $this->modinfo,
                            'themeinfo' => $this->themeinfo));

        $event = new Zikula_Event('view.init', $this);
        $this->eventManager->notify($event);
    }

    /**
     * Retrieve an array of module information, indexed by module name.
     *
     * @return array An array containing the module info, indexed by module name.
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Get the request.
     * 
     * @return Zikula_Request_Http
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the Zikula controller.
     * 
     * @return Zikula_AbstractController
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Set the controller property.
     * 
     * @param Zikula_AbstractController $controller Controller to set.
     *
     * @return void
     */
    public function setController(Zikula_AbstractController $controller)
    {
        $this->controller = $controller;
    }


    /**
     * Retrieve module name.
     *
     * @return string Module name.
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Retrieve the name of the top-level module.
     *
     * @return string The name of the top-level module.
     */
    public function getToplevelmodule()
    {
        return $this->toplevelmodule;
    }

    /**
     * Retrieve the module info array for the top-level module (or the module specified in the constructor).
     *
     * @return array The module info array.
     */
    public function getModinfo()
    {
        return $this->modinfo;
    }

    /**
     * Retrieve the name of the current theme.
     *
     * @return string The name of the current theme.
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Retrieve the theme info array for the current theme.
     *
     * @return array The theme info array.
     */
    public function getThemeinfo()
    {
        return $this->themeinfo;
    }

    /**
     * Retrieve the current language code.
     *
     * @return string The current language code.
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Retrieve the site's base URL.
     *
     * The value returned is the same as {@link System::getBaseUrl()}.
     *
     * @return string The base URL.
     */
    public function getBaseurl() {
        return $this->baseurl;
    }

    /**
     * Retrieve the site's base URI.
     *
     * The value returned is the same as {@link System::getBaseUri()}.
     *
     * @return string The base URI.
     */
    public function getBaseuri()
    {
        return $this->baseuri;
    }

    /**
     * Return the current userdb flag.
     *
     * @return boolean Set if Theme is an active module and templates stored in database.
     */
    public function getUserdb()
    {
        return $this->userdb;
    }

    /**
     * Retrieve the current setting for the 'render_expose_template' Theme module variable.
     *
     * @return boolean True if The 'render_expose_template' Theme module template is true.
     */
    public function getExpose_template()
    {
        return $this->expose_template;
    }

    /**
     * Retrieves the gettext domain for the module, as {@link ZLanguage::getModuleDomain()}.
     *
     * If the module is a system module this is not set.
     *
     * @return string The gettext domain for the module, or null for system modules.
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get ServiceManager.
     *
     * @return Zikula_ServiceManager The service manager.
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Get EventManager.
     *
     * @return Zikula_Eventmanager The event manager.
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Add a plugin dir to the search path.
     *
     * Avoids adding duplicates.
     *
     * @param string $dir The directory to add.
     *
     * @return Zikula_View This instance.
     */
    public function addPluginDir($dir)
    {
        if (in_array($dir, $this->plugins_dir) || !is_dir($dir)) {
            // TODO - !is_dir(...) should probably throw an exception.
            return $this;
        }

        array_push($this->plugins_dir, $dir);
        return $this;
    }

    /**
     * Translate.
     *
     * @param string $msgid String to be translated.
     *
     * @return string The $msgid translated by gettext.
     */
    public function __($msgid)
    {
        return __($msgid, $this->domain);
    }

    /**
     * Translate with sprintf().
     *
     * @param string       $msgid  String to be translated.
     * @param string|array $params Args for sprintf().
     *
     * @return string The $msgid translated by gettext.
     */
    public function __f($msgid, $params)
    {
        return __f($msgid, $params, $this->domain);
    }

    /**
     * Translate plural string.
     *
     * @param string $singular Singular instance.
     * @param string $plural   Plural instance.
     * @param string $count    Object count.
     *
     * @return string Translated string.
     */
    public function _n($singular, $plural, $count)
    {
        return _n($singular, $plural, $count, $this->domain);
    }

    /**
     * Translate plural string with sprintf().
     *
     * @param string       $sin    Singular instance.
     * @param string       $plu    Plural instance.
     * @param string       $n      Object count.
     * @param string|array $params Sprintf() arguments.
     *
     * @return string The $sin or $plu translated by gettext, based on $n.
     */
    public function _fn($sin, $plu, $n, $params)
    {
        return _fn($sin, $plu, $n, $params, $this->domain);
    }

    /**
     * Setup the current instance of the Zikula_View class and return it back to the module.
     *
     * @param string       $module   Module name.
     * @param boolean|null $caching  Whether or not to cache (boolean) or use config variable (null).
     * @param string       $cache_id Cache Id.
     *
     * @return Zikula_View This instance.
     */
    public static function getInstance($module = null, $caching = null, $cache_id = null)
    {
        if (is_null($module)) {
            $module = ModUtil::getName();
        }

        $serviceManager = ServiceUtil::getManager();
        $serviceId = strtolower(sprintf('zikula.view.%s', $module));
        if (!$serviceManager->hasService($serviceId)) {
            $view = new self($serviceManager, $module, $caching);
            $serviceManager->attachService($serviceId, $view);
        } else {
            $view = $serviceManager->getService($serviceId);
        }

        if (!is_null($caching)) {
            $view->caching = $caching;
        }

        if (!is_null($cache_id)) {
            $view->cache_id = $cache_id;
        }

        if ($module === null) {
            $module = $view->toplevelmodule;
        }

        if (!array_key_exists($module, $view->module)) {
            $view->module[$module] = ModUtil::getInfoFromName($module);
            //$instance->modinfo = ModUtil::getInfoFromName($module);
            $view->_add_plugins_dir($module);
        }

        // for {gt} template plugin to detect gettext domain
        if ($view->module[$module]['type'] == ModUtil::TYPE_MODULE) {
            $view->domain = ZLanguage::getModuleDomain($view->module[$module]['name']);
        }

        if (System::isLegacyMode()) {
            // load the usemodules configuration if exists
            $modpath = ($view->module[$module]['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';
            $usepath = "$modpath/" . $view->module[$module]['directory'] . '/templates/config';
            $usepathOld = "$modpath/" . $view->module[$module]['directory'] . '/pntemplates/config';
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
                            $view->_add_plugins_dir(trim($addmod));
                        }
                    }
                }
            }
        }

        return $view;
    }

    /**
     * Get module plugin Zikula_View_Plugin instance.
     *
     * @param string       $modName    Module name.
     * @param string       $pluginName Plugin name.
     * @param boolean|null $caching    Whether or not to cache (boolean) or use config variable (null).
     * @param string       $cache_id   Cache Id.
     *
     * @return Zikula_View_Plugin The plugin instance.
     */
    public static function getModulePluginInstance($modName, $pluginName, $caching = null, $cache_id = null)
    {
        return Zikula_View_Plugin::getInstance($modName, $pluginName, $caching, $cache_id);
    }

    /**
     * Get system plugin Zikula_View_Plugin instance.
     *
     * @param string       $pluginName Plugin name.
     * @param boolean|null $caching    Whether or not to cache (boolean) or use config variable (null).
     * @param string       $cache_id   Cache Id.
     *
     * @return Zikula_View_Plugin The plugin instance.
     */
    public static function getSystemPluginInstance($pluginName, $caching = null, $cache_id = null)
    {
        $modName = 'zikula';
        return Zikula_View_Plugin::getPluginInstance($modName, $pluginName, $caching, $cache_id);
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
        if (isset($this->templateCache[$template])) {
            return $this->templateCache[$template];
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

            $ostemplate = DataUtil::formatForOS($template);

            $relativepath = "$os_dir/$os_module/templates";
            $templatefile = "$relativepath/$ostemplate";
            $override = self::getTemplateOverride($templatefile);
            if ($override === false) {
                // no override present
                if (!System::isLegacyMode()) {
                    if (is_readable($templatefile)) {
                        $this->templateCache[$template] = $relativepath;
                        return $relativepath;
                    } else {
                        return false;
                    }
                }
            } else {
                if (is_readable($override)) {
                    $path = substr($override, 0, strrpos($override, $ostemplate));
                    $this->templateCache[$template] = $path;
                    return $path;
                }
            }
            
            // The rest of this code is scheduled for removal from 1.4.0 - drak
            
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
                    $this->templateCache[$template] = $path;
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
     * @param boolean $reset      Reset singleton defaults (optional). deprecated.
     *
     * @return string The template output.
     */
    public function fetch($template, $cache_id = null, $compile_id = null, $display = false, $reset = true)
    {
        $this->_setup_template($template);

        if (is_null($cache_id)) {
            $cache_id = $this->cache_id;
        }

        if (is_null($compile_id)) {
            $compile_id = $this->compile_id;
        }

        $this->template = $this->template_dir . '/' . $template;
        $output = parent::fetch($template, $cache_id, $compile_id, $display);

        if ($this->expose_template == true) {
            $template = DataUtil::formatForDisplay($template);
            $output = "\n<!-- Start " . $this->template_dir . "/$template -->\n" . $output . "\n<!-- End " . $this->template_dir . "/$template -->\n";
        }

        $event = new Zikula_Event('view.postfetch', $this, array('template' => $template), $output);
        return $this->eventManager->notify($event)->getData();
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
     * Returns an auto_id for auto-file-functions.
     *
     * @param string $cache_id   The cache ID (optional).
     * @param string $compile_id The compile ID (optional).
     *
     * @return string|null The auto_id, or null if neither $cache_id nor $compile_id are set.
     */
    function _get_auto_id($cache_id=null, $compile_id=null)
    {
        if (isset($cache_id)) {
            $auto_id = (isset($compile_id) && !empty($compile_id)) ? $cache_id . '_' . $compile_id  : $cache_id;
        } elseif (isset($compile_id)) {
            $auto_id = $compile_id;
        }

        if (isset($auto_id)) {
            return md5($auto_id);
        }

        return null;
    }

    /**
     * Get a concrete filename for automagically created content.
     *
     * @param string $auto_base   The base path.
     * @param string $auto_source The file name (optional).
     * @param string $auto_id     The ID (optional).
     *
     * @return string The concrete path and file name to the content.
     * 
     * @staticvar string|null
     * @staticvar string|null
     */
    function _get_auto_filename($auto_base, $auto_source = null, $auto_id = null)
    {
        $path = $auto_base . '/';

        $multilingual = System::getVar('multilingual');

        if ($multilingual == 1) {
            $path .= $this->language . '/';
        }

        if ($this instanceof Zikula_View_Theme) {
            $path .= $this->themeinfo['directory'] . '/';
        } elseif ($this instanceof Zikula_View_Plugin) {
            $path .= $this->themeinfo['directory'] . '/' . $this->modinfo['directory'] . '/' . $this->pluginName . '/';
        } else {
            $path .= $this->themeinfo['directory'] . '/' . $this->modinfo['directory'] . '/';
        }
        
        if (!file_exists($path)) {
            mkdir($path, $this->serviceManager['system.chmod_dir'], true);
        }

        // format auto_source for os to make sure that id does not contain 'ugly' characters
        $auto_source = DataUtil::formatForOS($auto_source);

        // create a hash from default dsn + $auto_source and use it in the filename
        $hash = md5(serialize($this->serviceManager['databases'] . '+' . $auto_source));
        $filebase = FileUtil::getFilebase($auto_source);
        $filebase_hashed = $filebase . '-' . $hash;
        
        // include auto_id in the filename 
        if (isset($auto_id) && !empty($auto_id)) {
            $filebase_hashed = $auto_id . '-' . $filebase_hashed;
        }
        
        // replace the original filebase with the hashed one
        $file = str_replace($filebase, $filebase_hashed, $auto_source);

        return $path.$file;
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
        if (is_null($cache_id)) {
            $cache_id = $this->cache_id;
        }

        if (is_null($compile_id)) {
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
        $cache_dir = $this->cache_dir;

        $cached_files = FileUtil::getFiles($cache_dir, true, false, array('tpl'), null, false);

        if ($template == null) {
            if ($expire == null) {
                foreach ($cached_files as $cf) {
                    unlink(realpath($cf));
                }
            } else {
                // actions for when $exp_time is not null
            }
        } else {
            if ($expire == null) {
                $auto_id = self::_get_auto_id($cache_id, $compile_id);
                $auto_filename = self::_get_auto_filename($cache_dir, $template, $auto_id);
                
                if (isset($auto_id) && !empty($auto_id)) {
                    if (file_exists($auto_filename)) {
                        unlink($auto_filename);
                    }
                } else {
                    $template_filebase = FileUtil::getFilebase($template);
                    foreach ($cached_files as $cf) {
                        if (strpos($cf, $template_filebase) !== false) {
                            unlink(realpath($cf));
                        }
                    }
                }
            } else {
                // actions for when $expire is not null
            }
        }

        return true;
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
        return $this->clear_cache(null, null, null, $exp_time);
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
        $compile_dir = $this->compile_dir;

        $compiled_files = FileUtil::getFiles($compile_dir, true, false, array('php', 'inc'), null, false);

        if ($exp_time == null) {
            foreach ($compiled_files as $cf) {
                unlink(realpath($cf));
            }
        } else {
            // actions for when $exp_time is not null
        }

        return true;
    }

    /**
     * Assign variable to template.
     *
     * @param string $key   Variable name.
     * @param mixed  $value Value.
     *
     * @return Zikula_View
     */
    function assign($key, $value = null)
    {
        $this->_assign_check($key);
        parent::assign($key, $value);
        return $this;
    }

    /**
     * Assign variable to template by reference.
     *
     * @param string $key    Variable name.
     * @param mixed  &$value Value.
     *
     * @return Zikula_View
     */
    function assign_by_ref($key, &$value)
    {
        $this->_assign_check($key);
        parent::assign_by_ref($key, $value);
        return $this;
    }

    /**
     * Prevent certain variables from being overwritten.
     *
     * @param string $key The protected variable key.
     *
     * @return void
     */
    protected function _assign_check($key)
    {
        if (is_array($key)) {
            foreach ($key as $v) {
                self::_assign_check($v);
            }
            return;
        }

        if (is_string($key)) {
            switch (strtolower($key))
            {
                case 'zikula_view':
                case 'zikula_core':
                case 'modvars':
                case 'metatags':
                case 'coredata':
                case 'servicemanager':
                case 'eventmanager':
                    $this->trigger_error(__f('%s is a protected template variable and may not be assigned', $key));
                    break;
            }
        }
    }

    /**
     * Set up paths for the template.
     *
     * This function sets the template and the config path according
     * to where the template is found (Theme or Module directory)
     *
     * @param string $template The template name.
     *
     * @return void
     */
    public function _setup_template($template)
    {
        // default directory for templates
        $this->template_dir = $this->get_template_path($template);
        $this->templatePath = $this->template_dir . '/' . $template;
        $this->config_dir = $this->template_dir . '/config';
    }

    /**
     * Get template path.
     *
     * This is calculated by _setup_template() invoked during fetch().
     *
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    /**
     * Get template paths.
     *
     * @return array
     */
    public function getTemplatePaths()
    {
        return $this->templatePaths;
    }

    /**
     * add a plugins dir to _plugin_dir array
     *
     * This function takes  module name and adds two path two the plugins_dir array
     * when existing
     *
     * @param string $module Well known module name.
     *
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
        $this->addPluginDir("$modpath/$modinfo[directory]/templates/plugins");

        if (System::isLegacyMode()) {
            $this->addPluginDir("$modpath/$modinfo[directory]/pntemplates/plugins");
        }
    }

    /**
     * Add core data to the template.
     *
     * This function adds some basic data to the template depending on the
     * current user and the Zikula settings.  There is no need to call this as it's
     * invoked automatically on instanciation.
     *
     * In legacy mode 'coredata' will contain the module vars, but not when disabled.
     * This is just for BC legacy - to access module vars there is a 'modvars' property
     * assigned to all templates.
     *
     * @return Zikula_View
     */
    public function add_core_data()
    {
        if (!isset($this->serviceManager['zikula_view.coredata'])) {
            $this->serviceManager['zikula_view.coredata'] = new ArrayObject(array());
        }

        $core = $this->serviceManager['zikula_view.coredata'];
        $core['version_num'] = Zikula_Core::VERSION_NUM;
        $core['version_id'] = Zikula_Core::VERSION_ID;
        $core['version_sub'] = Zikula_Core::VERSION_SUB;
        $core['logged_in'] = UserUtil::isLoggedIn();
        $core['language'] = $this->language;
        
        // add userdata
        $core['user'] = UserUtil::getVars(SessionUtil::getVar('uid'));

        if (System::isLegacyMode()) {
            // add modvars of current modules
            foreach ($this->module as $module => $dummy) {
                if (!empty($module)) {
                    $core[$module] = ModUtil::getVar($module);
                }
            }

            // add mod vars of all modules supplied as parameter
            $modulenames = func_get_args();
            foreach ($modulenames as $modulename) {
                // if the modulename is empty do nothing
                if (!empty($modulename) && !is_array($modulename) && !array_key_exists($modulename, $this->module)) {
                    // check if user wants to have config
                    if ($modulename == ModUtil::CONFIG_MODULE) {
                        $ZConfig = ModUtil::getVar(ModUtil::CONFIG_MODULE);
                        foreach ($ZConfig as $key => $value) {
                            // gather all config vars
                            $core['ZConfig'][$key] = $value;
                        }
                    } else {
                        $core[$modulename] = ModUtil::getVar($modulename);
                    }
                }
            }

            $this->assign('pncore', $core);
        }

        // Module vars
        parent::assign('coredata', $core);

        return $this;
    }

    /**
     * Retrieve the name of the directory where templates are located.
     *
     * @return string The directory name.
     */
    public function getTemplate_dir()
    {
        return $this->template_dir;
    }

    /**
     * Set the name of the directory where templates are located.
     *
     * @param string $template_dir The directory name.
     *
     * @return $this
     */
    public function setTemplate_dir($template_dir)
    {
        $this->template_dir = $template_dir;
        return $this;
    }

    /**
     * Retrieve the directory where compiled templates are located.
     *
     * @return string The directory name.
     */
    public function getCompile_dir()
    {
        return $this->compile_dir;
    }

    /**
     * Set the directory where compiled templates are located.
     *
     * @param string $compile_dir The directory name.
     *
     * @return $this
     */
    public function setCompile_dir($compile_dir)
    {
        $this->compile_dir = $compile_dir;
        return $this;
    }

    /**
     * Retrieve the directgory where config files are located.
     *
     * @return string The directory name.
     */
    public function getConfig_dir()
    {
        return $this->config_dir;
    }

    /**
     * Set the directgory where config files are located.
     *
     * @param string $config_dir The directory name.
     *
     * @return $this
     */
    public function setConfig_dir($config_dir)
    {
        $this->config_dir = $config_dir;
        return $this;
    }

    /**
     * Retrieve the directories that are searched for plugins.
     *
     * @return array An array of directory names.
     */
    public function getPlugins_dir()
    {
        return $this->plugins_dir;
    }

    /**
     * Set an array of directories that are searched for plugins.
     *
     * @param array $plugins_dir An array of directory names.
     *
     * @return $this
     */
    public function setPlugins_dir($plugins_dir)
    {
        $this->plugins_dir = $plugins_dir;
        return $this;
    }

    /**
     * Retrieve whether debugging mode is enabled or disabled.
     *
     * @return boolean True if enabled, otherwise false.
     */
    public function getDebugging()
    {
        return $this->debugging;
    }

    /**
     * Enable or disable debugging mode.
     *
     * If debugging is enabled, a debug console window will display when the page loads (make sure your browser
     * allows unrequested popup windows)
     *
     * @param boolean $debugging True to enable, otherwise false.
     *
     * @return $this
     */
    public function setDebugging($debugging)
    {
        $this->debugging = $debugging;
        return $this;
    }

    /**
     * Retrieve the PHP error reporting level to be used within this class.
     *
     * @see    error_reporting()
     *
     * @return integer The PHP error reporting level.
     */
    public function getError_reporting()
    {
        return $this->error_reporting;
    }

    /**
     * Set the PHP error reporting level to be used for this class.
     *
     * @param integer $error_reporting The PHP error reporting level.
     *
     * @see    error_reporting()
     *
     * @return $this
     */
    public function setError_reporting($error_reporting)
    {
        $this->error_reporting = $error_reporting;
        return $this;
    }

    /**
     * Retrieve the custom path to the debug console template.
     *
     * If empty, the default template is used.
     *
     * @return string The custom path to the debug console template.
     */
    public function getDebug_tpl()
    {
        return $this->debug_tpl;
    }

    /**
     * Set a custom path to the debug console template.
     *
     * If empty, the default template is used.
     *
     * @param string $debug_tpl The custom path to the debug console template.
     *
     * @return $this
     */
    public function setDebug_tpl($debug_tpl)
    {
        $this->debug_tpl = $debug_tpl;
        return $this;
    }

    /**
     * Retrieve whether debugging is enable-able from the browser.
     *
     * Values:
     * <ul>
     *  <li>NONE => no debugging control allowed</li>
     *  <li>URL => enable debugging when SMARTY_DEBUG is found in the URL.</li>
     * </ul>
     *
     * @return string Either 'NONE' or 'URL'.
     */
    public function getDebugging_ctrl()
    {
        return $this->debugging_ctrl;
    }

    /**
     * Set whether debugging is enable-able from the browser.
     *
     * Values:
     * <ul>
     *  <li>NONE => no debugging control allowed</li>
     *  <li>URL => enable debugging when SMARTY_DEBUG is found in the URL.</li>
     * </ul>
     *
     * http://www.example.com/index.php?SMARTY_DEBUG
     *
     * @param string $debugging_ctrl Either 'NONE' or 'URL'.
     *
     * @return $this
     */
    public function setDebugging_ctrl($debugging_ctrl)
    {
        $this->debugging_ctrl = $debugging_ctrl;
        return $this;
    }

    /**
     * Retrieve the flag that controls whether to check for recompiling or not.
     *
     * Recompiling does not need to happen unless a template or config file is changed.
     * Typically you enable this during development, and disable for production.
     *
     * @return boolean True if checked, otherwise false.
     */
    public function getCompile_check()
    {
        return $this->compile_check;
    }

    /**
     * Set compile check.
     *
     * @param boolean $doCompileCheck If true, checks for compile will be performed.
     *
     * @return $this
     */
    public function setCompile_check($doCompileCheck)
    {
        $this->compile_check = $doCompileCheck;
        return $this;
    }

    /**
     * Retrieve whether templates are forced to be compiled.
     *
     * @return boolean True if templates are forced to be compiled, otherwise false.
     */
    public function getForce_compile()
    {
        return $this->force_compile;
    }

    /**
     * Set whether templates are forced to be compiled.
     *
     * @param boolean $force_compile True to force compilation, otherwise false.
     *
     * @return $this
     */
    public function setForce_compile($force_compile)
    {
        $this->force_compile = $force_compile;
        return $this;
    }

    /**
     * Retrieve whether caching is enabled.
     *
     * Values:
     * <ul>
     *  <li>0 = no caching</li>
     *  <li>1 = use class cache_lifetime value</li>
     *  <li>2 = use cache_lifetime in cache file</li>
     * </ul>
     *
     * @return integer A code indicating whether caching is enabled.
     */
    public function getCaching()
    {
        return $this->caching;
    }

    /**
     * Set Caching.
     *
     * @param boolean $boolean True or false.
     *
     * @return $this
     */
    public function setCaching($boolean)
    {
        $this->caching = (bool)$boolean;
        return $this;
    }

    /**
     * Retrieve the current cache ID.
     *
     * @return string The current cache ID.
     */
    public function getCache_id()
    {
        return $this->cache_id;
    }

    /**
     * Set cache ID.
     *
     * @param string $id Cache ID.
     *
     * @return $this
     */
    public function setCache_Id($id)
    {
        $this->cache_id = $id;
        return $this;
    }

    /**
     * Retrieve the number of seconds cached content will persist.
     *
     * Special values:
     * <ul>
     *  <li>0 = always regenerate cache</li>
     *  <li>-1 = never expires</li>
     * </ul>
     *
     * @return integer The number of seconds cached content will persist.
     */
    public function getCache_lifetime()
    {
        return $this->cache_lifetime;
    }

    /**
     * Set cache lifetime.
     *
     * @param integer $time Lifetime in seconds.
     *
     * @return $this
     */
    public function setCache_lifetime($time)
    {
        $this->cache_lifetime = $time;
        return $this;
    }

    /**
     * Retrieve the name of the directory for cache files.
     *
     * @return string The name of the cache file directory.
     */
    public function getCache_dir()
    {
        return $this->cache_dir;
    }

    /**
     * Set the name of the directory for cache files.
     *
     * @param string $cache_dir The name of the cache file directory.
     *
     * @return $this
     */
    public function setCache_dir($cache_dir)
    {
        $this->cache_dir = $cache_dir;
        return $this;
    }

    /**
     * Retrieve whether If-Modified-Since headers are respected.
     *
     * Only used when caching is enabled (see (@link setCaching())). If true, then If-Modified-Since headers
     * are respected with cached content, and appropriate HTTP headers are sent.
     * This way repeated hits to a cached page do not send the entire page to the
     * client every time.
     *
     * @return boolean True if If-Modified-Since headers are respected, otherwise false.
     */
    public function getCache_modified_check()
    {
        return $this->cache_modified_check;
    }

    /**
     * Set whether If-Modified-Since headers are respected.
     *
     * Only used when caching is enabled (see (@link setCaching())). If true, then If-Modified-Since headers
     * are respected with cached content, and appropriate HTTP headers are sent.
     * This way repeated hits to a cached page do not send the entire page to the
     * client every time.
     *
     * @param boolean $cache_modified_check True to respect If-Modified-Since headers, otherwise false.
     *
     * @return $this
     */
    public function setCache_modified_check($cache_modified_check)
    {
        $this->cache_modified_check = $cache_modified_check;
        return $this;
    }

    /**
     * Retrieve how "<?php ... ?>" tags in templates are handled.
     *
     * Possible values:
     * <ul>
     *  <li>SMARTY_PHP_PASSTHRU -> print tags as plain text</li>
     *  <li>SMARTY_PHP_QUOTE    -> escape tags as entities</li>
     *  <li>SMARTY_PHP_REMOVE   -> remove php tags</li>
     *  <li>SMARTY_PHP_ALLOW    -> execute php tags</li>
     * </ul>
     *
     * @return integer A code indicating how php tags in templates are handled.
     */
    public function getPhp_handling()
    {
        return $this->php_handling;
    }

    /**
     * Set how "<?php ... ?>" tags in templates are handled.
     *
     * Possible values:
     * <ul>
     *  <li>SMARTY_PHP_PASSTHRU -> print tags as plain text</li>
     *  <li>SMARTY_PHP_QUOTE    -> escape tags as entities</li>
     *  <li>SMARTY_PHP_REMOVE   -> remove php tags</li>
     *  <li>SMARTY_PHP_ALLOW    -> execute php tags</li>
     * </ul>
     *
     * @param integer $php_handling A code indicating how php tags in templates are to be handled.
     *
     * @return $this
     */
    public function setPhp_handling($php_handling)
    {
        $this->php_handling = $php_handling;
        return $this;
    }

    /**
     * Retrieve whether template security is enabled or disabled.
     *
     * When enabled, many things are restricted in the templates that normally would go unchecked. This is useful when
     * untrusted parties are editing templates and you want a reasonable level of security.
     * (no direct execution of PHP in templates for example)
     *
     * @return boolean True if enabled, otherwise false.
     */
    public function getSecurity()
    {
        return $this->security;
    }

    /**
     * Enable or disable template security.
     *
     * When enabled, many things are restricted in the templates that normally would go unchecked. This is useful when
     * untrusted parties are editing templates and you want a reasonable level of security.
     * (no direct execution of PHP in templates for example)
     *
     * @param boolean $security True to enable, otherwise false.
     *
     * @return $this
     */
    public function setSecurity($security)
    {
        $this->security = $security;
        return $this;
    }

    /**
     * Retrieve the list of template directories that are considered secure.
     *
     * @return array An array of secure template directories.
     */
    public function getSecure_dir()
    {
        return $this->secure_dir;
    }

    /**
     * Set the list of template directories that are considered secure.
     *
     * This is used only if template security enabled (see {@link setSecurity()}). One directory per array
     * element.  The template directory (see {@link setTemplate_dir()}) is in this list implicitly.
     *
     * @param array $secure_dir An array of secure template directories.
     *
     * @return $this
     */
    public function setSecure_dir($secure_dir)
    {
        $this->secure_dir = $secure_dir;
        return $this;
    }

    /**
     * Retrieve an array of security settings, only used if template security is enabled (see {@link setSecurity()).
     *
     * @return array An array of security settings.
     */
    public function getSecurity_settings()
    {
        return $this->security_settings;
    }

    /**
     * Set an array of security settings, only used if template security is enabled (see {@link setSecurity()).
     *
     * @param array $security_settings An array of security settings.
     *
     * @return $this
     */
    public function setSecurity_settings($security_settings)
    {
        $this->security_settings = $security_settings;
        return $this;
    }

    /**
     * Retrieve an array of directories where trusted php scripts reside.
     *
     * @return array An array of trusted directories.
     */
    public function getTrusted_dir()
    {
        return $this->trusted_dir;
    }

    /**
     * Set an array of directories where trusted php scripts reside.
     *
     * Template security (see {@link setSecurity()) is disabled during their inclusion/execution.
     *
     * @param array $trusted_dir An array of trusted directories.
     *
     * @return $this
     */
    public function setTrusted_dir($trusted_dir)
    {
        $this->trusted_dir = $trusted_dir;
        return $this;
    }

    /**
     * Retrieve the left delimiter used for template tags.
     *
     * @return string The delimiter.
     */
    public function getLeft_delimiter()
    {
        return $this->left_delimiter;
    }

    /**
     * Set the left delimiter used for template tags.
     *
     * @param string $left_delimiter The delimiter.
     *
     * @return $this
     */
    public function setLeft_delimiter($left_delimiter)
    {
        $this->left_delimiter = $left_delimiter;
        return $this;
    }

    /**
     * Retrieve the right delimiter used for template tags.
     *
     * @return string The delimiter.
     */
    public function getRight_delimiter()
    {
        return $this->right_delimiter;
    }

    /**
     * Set the right delimiter used for template tags.
     *
     * @param string $right_delimiter The delimiter.
     *
     * @return $this
     */
    public function setRight_delimiter($right_delimiter)
    {
        $this->right_delimiter = $right_delimiter;
        return $this;
    }

    /**
     * Retrieve the order in which request variables are registered, similar to variables_order in php.ini.
     *
     * E = Environment, G = GET, P = POST, C = Cookies, S = Server
     *
     * @return string The string indicating the order, e.g., 'EGPCS'.
     */
    public function getRequest_vars_order()
    {
        return $this->request_vars_order;
    }

    /**
     * Set the order in which request variables are registered, similar to variables_order in php.ini.
     *
     * E = Environment, G = GET, P = POST, C = Cookies, S = Server
     *
     * @param string $request_vars_order A string indicating the order, e.g., 'EGPCS'.
     *
     * @return $this
     */
    public function setRequest_vars_order($request_vars_order)
    {
        $this->request_vars_order = $request_vars_order;
        return $this;
    }

    /**
     * Retrieve whether $HTTP_*_VARS[] (request_use_auto_globals=false) are used as request-vars or $_*[]-vars.
     *
     * @return boolean True if auto globals are used, otherwise false.
     */
    public function getRequest_use_auto_globals()
    {
        return $this->request_use_auto_globals;
    }

    /**
     * Set whether $HTTP_*_VARS[] (request_use_auto_globals=false) are used as request-vars or $_*[]-vars.
     *
     * Note: if request_use_auto_globals is true, then $request_vars_order has
     * no effect, but the php-ini-value "gpc_order"
     *
     * @param boolean $request_use_auto_globals True to use auto globals, otherwise false.
     *
     * @return $this
     */
    public function setRequest_use_auto_globals($request_use_auto_globals)
    {
        $this->request_use_auto_globals = $request_use_auto_globals;
        return $this;
    }

    /**
     * Retrieve the compile ID used to compile different sets of compiled files for the same templates.
     *
     * @return string|null The compile id, or null if none.
     */
    public function getCompile_id()
    {
        return $this->compile_id;
    }

    /**
     * Set this if you want different sets of compiled files for the same templates.
     *
     * This is useful for things like different languages.
     * Instead of creating separate sets of templates per language, you
     * set different compile_ids like 'en' and 'de'.
     *
     * @param string|null $compile_id The compile id, or null.
     *
     * @return $this
     */
    public function setCompile_id($compile_id)
    {
        $this->compile_id = $compile_id;
        return $this;
    }

    /**
     * Retrieve whether or not sub dirs in the cache/ and templates_c/ directories are used.
     *
     * @return boolean True if sub dirs are used, otherwise false.
     */
    public function getUse_sub_dirs()
    {
        return $this->use_sub_dirs;
    }

    /**
     * Set whether or not to use sub dirs in the cache/ and templates_c/ directories.
     *
     * Sub directories better organized, but may not work well with PHP safe mode enabled.
     *
     * @param boolean $use_sub_dirs True to use sub dirs, otherwise false.
     *
     * @return $this
     */
    public function setUse_sub_dirs($use_sub_dirs)
    {
        $this->use_sub_dirs = $use_sub_dirs;
        return $this;
    }

    /**
     * Retrieve a list of the modifiers applied to all template variables.
     *
     * @return array An array of default modifiers.
     */
    public function getDefault_modifiers()
    {
        return $this->default_modifiers;
    }

    /**
     * Set a list of the modifiers to apply to all template variables.
     *
     * Put each modifier in a separate array element in the order you want
     * them applied. example: <code>array('escape:"htmlall"');</code>
     *
     * @param array $default_modifiers An array of default modifiers.
     *
     * @return $this
     */
    public function setDefault_modifiers($default_modifiers)
    {
        $this->default_modifiers = $default_modifiers;
        return $this;
    }

    /**
     * Retrieve the resource type used when not specified at the beginning of the resource path (see {@link Smarty::$default_resource_type}).
     *
     * @return string The resource type used.
     */
    public function getDefault_resource_type()
    {
        return $this->default_resource_type;
    }

    /**
     * Set the resource type to be used when not specified at the beginning of the resource path (see {@link Smarty::$default_resource_type}).
     *
     * @param string $default_resource_type The resource type to use.
     *
     * @return $this
     */
    public function setDefault_resource_type($default_resource_type)
    {
        $this->default_resource_type = $default_resource_type;
        return $this;
    }

    /**
     * Retrieve the name of the function used for cache file handling.
     *
     * If not set, built-in caching is used.
     *
     * @return string|null The name of the function, or null if built-in caching is used.
     */
    public function getCache_handler_func()
    {
        return $this->cache_handler_func;
    }

    /**
     * Set the name of the function used for cache file handling.
     *
     * If not set, built-in caching is used.
     *
     * @param string|null $cache_handler_func The name of the function, or null to use built-in caching.
     *
     * @return $this
     */
    public function setCache_handler_func($cache_handler_func)
    {
        $this->cache_handler_func = $cache_handler_func;
        return $this;
    }

    /**
     * Retrieve whether filters are automatically loaded or not.
     *
     * @return boolean True if automatically loaded, otherwise false.
     */
    public function getAutoload_filters()
    {
        return $this->autoload_filters;
    }

    /**
     * Set whether filters are automatically loaded or not.
     *
     * @param boolean $autoload_filters True to automatically load, otherwise false.
     *
     * @return $this
     */
    public function setAutoload_filters($autoload_filters)
    {
        $this->autoload_filters = $autoload_filters;
        return $this;
    }

    /**
     * Retrieve if config file vars of the same name overwrite each other or not.
     *
     * @return boolean True if overwritten, otherwise false.
     */
    public function getConfig_overwrite()
    {
        return $this->config_overwrite;
    }

    /**
     * Set if config file vars of the same name overwrite each other or not.
     *
     * If disabled, same name variables are accumulated in an array.
     *
     * @param boolean $config_overwrite True to overwrite, otherwise false.
     *
     * @return $this
     */
    public function setConfig_overwrite($config_overwrite)
    {
        $this->config_overwrite = $config_overwrite;
        return $this;
    }

    /**
     * Retrieve whether or not to automatically booleanize config file variables.
     *
     * If enabled, then the strings "on", "true", and "yes" are treated as boolean
     * true, and "off", "false" and "no" are treated as boolean false.
     *
     * @return boolean True if config variables are booleanized, otherwise false.
     */
    public function getConfig_booleanize()
    {
        return $this->config_booleanize;
    }

    /**
     * Set whether or not to automatically booleanize config file variables.
     *
     * If enabled, then the strings "on", "true", and "yes" are treated as boolean
     * true, and "off", "false" and "no" are treated as boolean false.
     *
     * @param boolean $config_booleanize True to booleanize, otherwise false.
     *
     * @return $this
     */
    public function setConfig_booleanize($config_booleanize)
    {
        $this->config_booleanize = $config_booleanize;
        return $this;
    }

    /**
     * Retrieve whether hidden sections [.foobar] in config files are readable from the tempalates or not.
     *
     * @return boolean True if hidden sections readable, otherwise false.
     */
    public function getConfig_read_hidden()
    {
        return $this->config_read_hidden;
    }

    /**
     * Set whether hidden sections [.foobar] in config files are readable from the tempalates or not.
     *
     * Normally you would never allow this since that is the point behind hidden sections: the application can access
     * them, but the templates cannot.
     *
     * @param boolean $config_read_hidden True to make hidden sections readable, otherwise false.
     *
     * @return $this
     */
    public function setConfig_read_hidden($config_read_hidden)
    {
        $this->config_read_hidden = $config_read_hidden;
        return $this;
    }

    /**
     * Retrieve the flag that indicates whether newlines are automatically corrected in config files.
     *
     * This indicates whether or not automatically fix newlines in config files.
     * It basically converts \r (mac) or \r\n (dos) to \n
     *
     * @return boolean True if automatically fixed, otherwise false.
     */
    public function getConfig_fix_newlines()
    {
        return $this->config_fix_newlines;
    }

    /**
     * Set the flag that corrects newlines automatically in config files.
     *
     * This indicates whether or not automatically fix newlines in config files.
     * It basically converts \r (mac) or \r\n (dos) to \n
     *
     * @param boolean $config_fix_newlines True to automatically fix, otherwise false.
     *
     * @return $this
     */
    public function setConfig_fix_newlines($config_fix_newlines)
    {
        $this->config_fix_newlines = $config_fix_newlines;
        return $this;
    }

    /**
     * Retrieve the name of the PHP function that will be called if a template cannot be found.
     *
     * @return string The name of the PHP function called if a template cannot be found.
     */
    public function getDefault_template_handler_func()
    {
        return $this->default_template_handler_func;
    }

    /**
     * Set the name of the PHP function that will be called if a template cannot be found.
     *
     * @param string $default_template_handler_func The name of the PHP function to call if a template cannot be found.
     *
     * @return $this
     */
    public function setDefault_template_handler_func($default_template_handler_func)
    {
        $this->default_template_handler_func = $default_template_handler_func;
        return $this;
    }

    /**
     * Retrieve the name of the file that contains the compiler class.
     *
     * This could be a full pathname, or relative to the php_include path.
     *
     * @see    Smarty::$compiler_file
     * @see    setCompilerClass()
     *
     * @return string The name of the file that contains the compiler class.
     */
    public function getCompiler_file()
    {
        return $this->compiler_file;
    }

    /**
     * Set the name of the file that contains the compiler class.
     *
     * This can a full pathname, or relative to the php_include path.
     *
     * @param string $compiler_file The name of the file that contains the compiler class.
     *
     * @see    Smarty::$compiler_file
     * @see    setCompilerClass()
     *
     * @return $this
     */
    public function setCompiler_file($compiler_file)
    {
        $this->compiler_file = $compiler_file;
        return $this;
    }

    /**
     * Retrieve the name of the class used to compile templates.
     *
     * @return string The name of the class used to compile templates.
     */
    public function getCompiler_class()
    {
        return $this->compiler_class;
    }

    /**
     * Set the name of the class that will be used to compile templates.
     *
     * @param string $compiler_class The name of the class used to compile templates.
     *
     * @return $this
     */
    public function setCompiler_class($compiler_class)
    {
        $this->compiler_class = $compiler_class;
        return $this;
    }

    /**
     * Retrieve the template variables array ({@link Smarty::$_tpl_vars}).
     *
     * @return array The template variables array.
     */
    public function get_tpl_vars()
    {
        return $this->_tpl_vars;
    }

    /**
     * Get a template variable.
     *
     * @param string $key Key of assigned template variable.
     *
     * @return mixed
     */
    public function get_tpl_var($key)
    {
        if (!array_key_exists($key, $this->_tpl_vars)) {
            throw new InvalidArgumentException(__f('%s does not exist as as assigned variable', $key));
        }

        return $this->_tpl_vars[$key];
    }

    /**
     * Set the template variables array ({@link Smarty::$_tpl_vars}).
     *
     * @param array $_tpl_vars The template variables array.
     *
     * @return $this
     */
    public function set_tpl_vars($_tpl_vars)
    {
        $this->_tpl_vars = $_tpl_vars;
        return $this;
    }

    /**
     * Retrieve the compile ID.
     *
     * @return string The compile ID.
     */
    public function get_compile_id()
    {
        return $this->_compile_id;
    }

    /**
     * Set the compile ID.
     *
     * @param string $_compile_id The compile ID.
     *
     * @return $this
     */
    public function set_compile_id($_compile_id)
    {
        $this->_compile_id = $_compile_id;
        return $this;
    }

    /**
     * Retrieve the info that makes up a cache file ({@link Smarty::$_cache_info}).
     *
     * @return array Array of info that makes up a cache file.
     */
    public function get_cache_info()
    {
        return $this->_cache_info;
    }

    /**
     * Set the info that makes up a cache file ({@link Smarty::$_cache_info}).
     *
     * @param array $_cache_info Array of info that makes up a cache file.
     *
     * @return $this
     */
    public function set_cache_info($_cache_info)
    {
        $this->_cache_info = $_cache_info;
        return $this;
    }

    /**
     * Retrieve the file permissions ({@link Smarty::$_file_perms}).
     *
     * @return int File permissions.
     */
    public function get_file_perms()
    {
        return $this->_file_perms;
    }

    /**
     * Set the file permissions ({@link Smarty::$_file_perms}).
     *
     * @param int $_file_perms File permissions; use an octal number, e.g. set_file_perms(0664).
     *
     * @return $this
     */
    public function set_file_perms($_file_perms)
    {
        $this->_file_perms = $_file_perms;
        return $this;
    }

    /**
     * Retrieve the directory permissions ({@link Smarty::$_dir_perms}).
     *
     * @return int Directory permissions.
     */
    public function get_dir_perms()
    {
        return $this->_dir_perms;
    }

    /**
     * Set the directory permissions ({@link Smarty::$_dir_perms}).
     *
     * @param int $_dir_perms Directory permissions; use an octal number, e.g. set_dir_perms(0771).
     *
     * @return $this
     */
    public function set_dir_perms($_dir_perms)
    {
        $this->_dir_perms = $_dir_perms;
        return $this;
    }

    /**
     * Retrieve the {@link Smarty::$_reg_objects} registered objects.
     *
     * @return array Registered objects array.
     */
    public function get_reg_objects()
    {
        return $this->_reg_objects;
    }

    /**
     * Set the {@link Smarty::$_reg_objects} registered objects.
     *
     * @param array $_reg_objects Registered objects.
     *
     * @return $this
     */
    public function set_reg_objects($_reg_objects)
    {
        $this->_reg_objects = $_reg_objects;
        return $this;
    }

    /**
     * Retrieve the array keeping track of plugins (see {@link Smarty::$_plugins}.
     *
     * @return array An array of plugins by type.
     */
    public function get_plugins()
    {
        return $this->_plugins;
    }

    /**
     * Set the array keeping track of plugins (see {@link Smarty::$_plugins}.
     *
     * @param array $_plugins An array of plugins by type.
     *
     * @return $this
     */
    public function set_plugins($_plugins)
    {
        $this->_plugins = $_plugins;
        return $this;
    }

    /**
     * Retrieve the value of {@link Smarty::$_cache_serials}.
     *
     * @return array Cache serials.
     */
    public function get_cache_serials()
    {
        return $this->_cache_serials;
    }

    /**
     * Setter for {@link Smarty::$_cache_serials}
     *
     * @param array $_cache_serials Cache serials.
     *
     * @return $this
     */
    public function set_cache_serials($_cache_serials)
    {
        $this->_cache_serials = $_cache_serials;
        return $this;
    }

    /**
     * Retrieve the value of {@link Smarty::$_cache_include}.
     *
     * @return string Name of optional cache include file.
     */
    public function get_cache_include()
    {
        return $this->_cache_include;
    }

    /**
     * Setter for {@link Smarty::$_cache_include}.
     *
     * @param string $_cache_include Name of optional cache include file.
     *
     * @return $this
     */
    public function set_cache_include($_cache_include)
    {
        $this->_cache_include = $_cache_include;
        return $this;
    }

    /**
     * Retrieve the value of {@link Smarty::$_cache_including}.
     *
     * @return boolean True if the current code is used in a compiled include, otherwise false.
     */
    public function get_cache_including()
    {
        return $this->_cache_including;
    }

    /**
     * Setter for {@link Smarty::$_cache_including}.
     *
     * @param boolean $_cache_including Indicate if the current code is used in a compiled include.
     *
     * @return $this
     */
    public function set_cache_including($_cache_including)
    {
        $this->_cache_including = $_cache_including;
        return $this;
    }

    /**
     * Execute a template override event.
     *
     * @param string $template Path to template.
     *
     * @throws InvalidArgumentException If event handler returns a non-existent template.
     *
     * @return mixed String if found, false if no override present.
     */
    public static function getTemplateOverride($template)
    {
        $event = new Zikula_Event('zikula_view.template_override', null, array(), $template);
        EventUtil::getManager()->notify($event);

        if ($event->isStopped()) {
            $ostemplate = DataUtil::formatForOS($event->getData());
            if (is_readable($ostemplate)) {
                return $ostemplate;
            } else {
                throw new InvalidArgumentException(__f('zikula_view.template_override returned a non-existent template path %s', $ostemplate));
            }
        }

        return false;
    }

    /**
     * Disable or enable add the module wrapper.
     *
     * @param boolean $wrap False to disable wrapper, true to enable it.
     *
     * @return $this
     */
    public function setWrapper($wrap)
    {
        if ($this->modinfo['name'] == $this->toplevelmodule) {
            Zikula_View_Theme::getInstance()->system = !$wrap;
        }
        return $this;
    }
}

/**
 * Smarty block function to prevent template parts from being cached
 *
 * @param array       $param   Tag parameters.
 * @param string      $content Block content.
 * @param Zikula_View $view    Reference to smarty instance.
 *
 * @return string
 **/
function Zikula_View_block_nocache($param, $content, $view)
{
    return $content;
}

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
function z_get_template($tpl_name, &$tpl_source, $view)
{
    // determine the template path and store the template source

    // get path, checks also if tpl_name file_exists and is_readable
    $tpl_path = $view->get_template_path($tpl_name);

    if ($tpl_path !== false) {
        $tpl_source = file_get_contents(DataUtil::formatForOS($tpl_path . '/' . $tpl_name));
        return true;
    }

    return LogUtil::registerError(__f('Error! The template [%1$s] is not available in the [%2$s] module.', array(
            $tpl_name,
            $view->toplevelmodule)));
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
function z_get_timestamp($tpl_name, &$tpl_timestamp, $view)
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
function z_get_secure($tpl_name, $view)
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
function z_get_trusted($tpl_name, $view)
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
 * @param string      $tpl_source The template's source prior to prefiltering.
 * @param Zikula_View $view       A reference to the Zikula_View object.
 *
 * @return string The prefiltered template contents.
 */
function z_prefilter_add_literal($tpl_source, $view)
{
    return preg_replace_callback('`(<(script|style)[^>]*>)(.*?)(</\2>)`s', 'z_prefilter_add_literal_callback', $tpl_source);
}

/**
 * Prefilter for gettext parameters.
 *
 * @param string      $tpl_source The template's source prior to prefiltering.
 * @param Zikula_View $view       A reference to the Zikula_View object.
 *
 * @return string The prefiltered template contents.
 */
function z_prefilter_gettext_params($tpl_source, $view)
{
    return preg_replace('#((?:(?<!\{)\{(?!\{)(?:\s*)|\G)(?:.+?))__([a-zA-Z0-9]+=([\'"])(?:\\\\?+.)*?\3)#', '$1$2|gt:\$zikula_view', $tpl_source);
}

/**
 * Prefilter for legacy tag delemitters.
 *
 * @param string      $source The template's source prior to prefiltering.
 * @param Zikula_View $view   A reference to the Zikula_View object.
 *
 * @return string The prefiltered template contents.
 */
function z_prefilter_legacy($source, $view)
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

/**
 * Prefilter for hookable filters.
 *
 * @param string      $tpl_source The template's source prior to prefiltering.
 * @param Zikula_View $view       A reference to the Zikula_View object.
 *
 * @return string The prefiltered template contents.
 */
function z_prefilter_notifyfilters($tpl_source, $view)
{
    return preg_replace('#((?:(?<!\{)\{(?!\{)(?:\s*)|\G)(?:.*?))(\|notifyfilters(?:([\'"])(?:\\\\?+.)*?\3|[^\s|}])*)#', '$1$2:\$zikula_view', $tpl_source);
}