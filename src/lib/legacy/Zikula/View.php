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
 * Zikula_View class.
 *
 * @deprecated
 */
class Zikula_View extends Smarty implements Zikula_TranslatableInterface
{
    const CACHE_DISABLED = 0;
    const CACHE_ENABLED = 1;
    const CACHE_INDIVIDUAL = 2;

    /**
     * Translation domain of the calling module.
     *
     * @var string
     */
    public $domain;

    /**
     * Module info array, indexed by module name.
     *
     * @var array
     */
    public $module;

    /**
     * Module info.
     *
     * @var array
     */
    public $modinfo;

    /**
     * Top level module.
     *
     * @var string
     */
    public $toplevelmodule;

    /**
     * Type.
     *
     * @var integer
     */
    public $type;

    /**
     * Function.
     *
     * @var string
     */
    public $func;

    /**
     * Language.
     *
     * @var string
     */
    public $language;

    /**
     * Homepage flag.
     *
     * @var boolean
     */
    public $homepage;

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
     * The service manager instance.
     *
     * @var Zikula_ServiceManager
     */
    protected $serviceManager;

    /**
     * The event manager instance.
     *
     * @var Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
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
     * Cache Id.
     *
     * @var string
     */
    public $cache_id;

    /**
     * Whether or not to expose template.
     *
     * @var boolean
     */
    public $expose_template;

    /**
     * Template path (populated by fetch).
     *
     * @var string
     */
    protected $templatePath;

    /**
     * Templates.
     *
     * @var array
     */
    protected $templateCache = [];

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager
     * @param string                $moduleName     Module name ("zikula" for system plugins)
     * @param integer|null          $caching        Whether or not to cache (Zikula_View::CACHE_*) or use config variable (null)
     */
    public function __construct(Zikula_ServiceManager $serviceManager, $moduleName = '', $caching = null)
    {
        @trigger_error('Smarty is deprecated, please use Twig instead.', E_USER_DEPRECATED);

        $this->serviceManager = $serviceManager;
        $this->eventManager = $this->serviceManager->get('event_dispatcher');
        $this->request = $this->serviceManager->get('request_stack')->getCurrentRequest();

        // set the error reporting level
        $this->error_reporting = isset($GLOBALS['ZConfig']['Debug']['error_reporting']) ? $GLOBALS['ZConfig']['Debug']['error_reporting'] : E_ALL;
        $this->error_reporting &= ~E_USER_DEPRECATED;
        $this->allow_php_tag = true;

        // get variables from input
        $module = FormUtil::getPassedValue('module', null, 'GETPOST', FILTER_SANITIZE_STRING);
        $type   = FormUtil::getPassedValue('type', 'user', 'GETPOST', FILTER_SANITIZE_STRING);
        $func   = FormUtil::getPassedValue('func', 'main', 'GETPOST', FILTER_SANITIZE_STRING);

        // set vars based on the module structures
        $pathInfo = null !== $this->request ? $this->request->getPathInfo() : '/';
        $routeParams = isset($pathInfo) && $pathInfo != '/' ? $this->serviceManager->get('router')->match($pathInfo) : null;
        if (!isset($routeParams) && $pathInfo == '/') {
            $this->homepage = true;
            $this->type = System::getVar('starttype');
            $this->func = System::getVar('startfunc');
        } elseif (isset($routeParams) && isset($routeParams['_zkType']) && isset($routeParams['_zkFunc'])) {
            $this->type = $routeParams['_zkType'];
            $this->func = $routeParams['_zkFunc'];
        } else {
            $this->homepage = PageUtil::isHomepage();
            $this->type = strtolower(!$this->homepage ? $type : System::getVar('starttype'));
            $this->func = strtolower(!$this->homepage ? $func : System::getVar('startfunc'));
        }

        // Initialize the module property with the name of
        // the topmost module. For Hooks, Blocks, API Functions and others
        // you need to set this property to the name of the respective module!
        $masterRequest = $this->serviceManager->get('request_stack')->getMasterRequest();
        $masterRequestModule = !empty($routeParams['_zkModule']) ? $routeParams['_zkModule'] : null;
        if (!isset($masterRequestModule) && null !== $masterRequest && $masterRequest->attributes->get('_route') == 'zikula_hook_hook_edit' && $masterRequest->attributes->has('moduleName')) {
            // accommodates HookBundle
            $masterRequestModule = $masterRequest->attributes->get('moduleName');
        }
        $this->toplevelmodule = isset($masterRequestModule) ? $masterRequestModule : ModUtil::getName();

        if (!$moduleName) {
            $moduleName = $this->toplevelmodule;
        }
        $this->modinfo = ModUtil::getInfoFromName($moduleName);
        $this->module  = [$moduleName => $this->modinfo];

        // initialise environment vars
        $this->language = ZLanguage::getLanguageCode();
        $this->baseurl = System::getBaseUrl();
        $this->baseuri = System::getBaseUri();

        // system info
        $this->themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));
        $this->theme = $theme = $this->themeinfo['directory'];
        $themeBundle = ThemeUtil::getTheme($this->themeinfo['name']);

        //---- Plugins handling -----------------------------------------------
        // add plugin paths
        switch ($this->modinfo['type']) {
            case ModUtil::TYPE_MODULE:
                $mpluginPathNew = "modules/" . $this->modinfo['directory'] . "/Resources/views/plugins";
                $mpluginPath = "modules/" . $this->modinfo['directory'] . "/templates/plugins";

                break;
            case ModUtil::TYPE_SYSTEM:
                $mpluginPathNew = "system/" . $this->modinfo['directory'] . "/Resources/views/plugins";
                $mpluginPath = "system/" . $this->modinfo['directory'] . "/templates/plugins";

                break;
            default:
                $mpluginPathNew = "system/" . $this->modinfo['directory'] . "/Resources/views/plugins";
                $mpluginPath = "system/" . $this->modinfo['directory'] . "/templates/plugins";
        }

        // add standard plugin search path
        $this->plugins_dir = [];
        $this->addPluginDir('config/plugins'); // Official override
        $this->addPluginDir('lib/legacy/viewplugins'); // Core plugins
        $this->addPluginDir(isset($themeBundle) ? $themeBundle->getRelativePath() . '/plugins' : "themes/$theme/plugins"); // Theme plugins
        $this->addPluginDir('plugins'); // Smarty core plugins
        $this->addPluginDir($mpluginPathNew); // Plugins for current module
        $this->addPluginDir($mpluginPath); // Plugins for current module

        // include plugins of the Admin module to the plugins_dir array
        // restricting this to only load when `type=admin` (or similar) fails now since type is not automatically any longer.
        if (!$this instanceof Zikula_View_Theme) {
            $this->addPluginDir('system/AdminModule/Resources/views/plugins');
        } else {
            $this->load_filter('output', 'admintitle');
        }

        // theme plugins module overrides
        $themePluginsPath = isset($themeBundle) ? $themeBundle->getRelativePath() . '/modules/$moduleName/plugins' : "themes/$theme/templates/modules/$moduleName/plugins";
        $this->addPluginDir($themePluginsPath);

        //---- Cache handling -------------------------------------------------
        if ($caching && in_array((int)$caching, [0, 1, 2])) {
            $this->caching = (int)$caching;
        } else {
            $this->caching = (int)ModUtil::getVar('ZikulaThemeModule', 'render_cache');
        }

        $this->compile_id  = '';
        $this->cache_id    = '';

        // template compilation
        $this->compile_dir    = CacheUtil::getLocalDir('view_compiled');
        $this->compile_check  = ModUtil::getVar('ZikulaThemeModule', 'render_compile_check');
        $this->force_compile  = ModUtil::getVar('ZikulaThemeModule', 'render_force_compile');
        // template caching
        $this->cache_dir      = CacheUtil::getLocalDir('view_cache');
        $this->cache_lifetime = ModUtil::getVar('ZikulaThemeModule', 'render_lifetime');

        $this->expose_template = (ModUtil::getVar('ZikulaThemeModule', 'render_expose_template') == true) ? true : false;

        // register resource type 'z' this defines the way templates are searched
        // during {include file='my_template.tpl'} this enables us to store selected module
        // templates in the theme while others can be kept in the module itself.
        $this->register_resource('z', ['Zikula_View_Resource',
                                       'z_get_template',
                                       'z_get_timestamp',
                                       'z_get_secure',
                                       'z_get_trusted']);

        // set 'z' as default resource type
        $this->default_resource_type = 'z';

        // process some plugins specially when Render cache is enabled
        if (!$this instanceof Zikula_View_Theme && $this->caching) {
            $this->register_nocache_plugins();
        }

        // register the 'nocache' block to allow dynamic zones caching templates
        $this->register_block('nocache', ['Zikula_View_Resource', 'block_nocache'], false);

        // For ajax requests we use the short urls filter to 'fix' relative paths
        //        if (($this->serviceManager->get('zikula')->getStage() & Zikula_Core::STAGE_AJAX) && System::getVar('shorturls')) {
        $this->load_filter('output', 'shorturls');
        //        }

        // register prefilters
        $this->register_prefilter('z_prefilter_add_literal');

        $this->register_prefilter('z_prefilter_gettext_params');
        //$this->register_prefilter('z_prefilter_notifyfilters');

        // assign some useful settings
        $this->assign('homepage', $this->homepage)
             ->assign('modinfo', $this->modinfo)
             ->assign('module', $moduleName)
             ->assign('toplevelmodule', $this->toplevelmodule)
             ->assign('type', $this->type)
             ->assign('func', $this->func)
             ->assign('lang', $this->language)
             ->assign('themeinfo', $this->themeinfo)
             ->assign('themepath', isset($themeBundle) ? $themeBundle->getRelativePath() : $this->baseurl . 'themes/' . $theme)
             ->assign('baseurl', $this->baseurl)
             ->assign('baseuri', $this->baseuri)
             ->assign('moduleBundle', ModUtil::getModule($moduleName)) // is NULL for pre-1.4.0-type modules
             ->assign('themeBundle', $themeBundle);

        if (isset($themeBundle)) {
            $stylePath = $themeBundle->getRelativePath() . "/Resources/public/css";
            $javascriptPath = $themeBundle->getRelativePath() . "/Resources/public/js";
            $imagePath = $themeBundle->getRelativePath() . "/Resources/public/images";
            $imageLangPath = $themeBundle->getRelativePath() . "/Resources/public/images/" . $this->language;
        } else {
            $stylePath = $this->baseurl . "themes/$theme/style";
            $javascriptPath = $this->baseurl . "themes/$theme/javascript";
            $imagePath = $this->baseurl . "themes/$theme/images";
            $imageLangPath = $this->baseurl . "themes/$theme/images/" . $this->language;
        }
        $this->assign('stylepath', $stylePath)
             ->assign('scriptpath', $javascriptPath)
             ->assign('imagepath', $imagePath)
             ->assign('imagelangpath', $imageLangPath);

        // for {gt} template plugin to detect gettext domain
        if ($this->modinfo['type'] == ModUtil::TYPE_MODULE) {
            $this->domain = ZLanguage::getModuleDomain($this->modinfo['name']);
        }

        // make render object available to modifiers
        parent::assign('zikula_view', $this);

        // add ServiceManager, EventManager and others to all templates
        parent::assign('serviceManager', $this->serviceManager);
        parent::assign('eventManager', $this->eventManager);
        //        parent::assign('zikula_core', $this->serviceManager->get('zikula'));
        parent::assign('request', $this->request);
        $modvars = ModUtil::getModvars(); // Get all modvars from any modules that have accessed their modvars at least once.
        // provide compatibility 'alias' array keys
        // @todo remove after v1.4.0
        if (isset($modvars['ZikulaAdminModule'])) {
            $modvars['Admin'] = $modvars['ZikulaAdminModule'];
        }
        if (isset($modvars['ZikulaBlocksModule'])) {
            $modvars['Blocks'] = $modvars['ZikulaBlocksModule'];
        }
        if (isset($modvars['ZikulaCategoriesModule'])) {
            $modvars['Categories'] = $modvars['ZikulaCategoriesModule'];
        }
        if (isset($modvars['ZikulaExtensionsModule'])) {
            $modvars['Extensions'] = $modvars['ZikulaExtensionsModule'];
        }
        if (isset($modvars['ZikulaGroupsModule'])) {
            $modvars['Groups'] = $modvars['ZikulaGroupsModule'];
        }
        if (isset($modvars['ZikulaMailerModule'])) {
            $modvars['Mailer'] = $modvars['ZikulaMailerModule'];
        }
        if (isset($modvars['ZikulaPageLockModule'])) {
            $modvars['PageLock'] = $modvars['ZikulaPageLockModule'];
        }
        if (isset($modvars['ZikulaPermissionsModule'])) {
            $modvars['Permissions'] = $modvars['ZikulaPermissionsModule'];
        }
        if (isset($modvars['ZikulaSearchModule'])) {
            $modvars['Search'] = $modvars['ZikulaSearchModule'];
        }
        if (isset($modvars['ZikulaSecurityCenterModule'])) {
            $modvars['SecurityCenter'] = $modvars['ZikulaSecurityCenterModule'];
        }
        if (isset($modvars['ZikulaSettingsModule'])) {
            $modvars['Settings'] = $modvars['ZikulaSettingsModule'];
        }
        if (isset($modvars['ZikulaThemeModule'])) {
            $modvars['Theme'] = $modvars['ZikulaThemeModule'];
        }
        if (isset($modvars['ZikulaUsersModule'])) {
            $modvars['Users'] = $modvars['ZikulaUsersModule'];
        }
        // end compatibility aliases
        parent::assign('modvars', $modvars);

        $this->add_core_data();

        // metadata for SEO
        if (!$this->serviceManager->hasParameter('zikula_view.metatags')) {
            $this->serviceManager->setParameter('zikula_view.metatags', new ArrayObject([]));
        }

        parent::assign('metatags', $this->serviceManager->getParameter('zikula_view.metatags'));

        if (isset($themeBundle) && $themeBundle->isTwigBased()) {
            // correct asset urls when smarty output is wrapped by twig theme
            $this->load_filter('output', 'asseturls');
        }

        $event = new \Zikula\Core\Event\GenericEvent($this);
        $this->eventManager->dispatch('view.init', $event);
    }

    /**
     * Setup the current instance of the Zikula_View class and return it back to the module.
     *
     * @param string       $module   Module name
     * @param integer|null $caching  Whether or not to cache (Zikula_View::CACHE_*) or use config variable (null)
     * @param string       $cache_id Cache Id
     *
     * @return Zikula_View This instance
     */
    public static function getInstance($module = null, $caching = null, $cache_id = null)
    {
        if (is_null($module)) {
            $module = ModUtil::getName();
            if ($module === false) {
                // fallback if no module is given or called (see #2303)
                $module = ModUtil::CONFIG_MODULE;
            }
        }

        $serviceManager = ServiceUtil::getManager();
        $serviceId = strtolower(sprintf('zikula.view.%s', $module));
        if (!$serviceManager->has($serviceId)) {
            $view = new self($serviceManager, $module, $caching);
            $serviceManager->set($serviceId, $view);
        } else {
            $view = $serviceManager->get($serviceId);
        }

        if (!is_null($caching)) {
            $view->caching = (int)$caching;
        }

        if (!is_null($cache_id)) {
            $view->cache_id = $cache_id;
        }

        if (!$module) {
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
            $usemod_confs = [];
            $usemod_confs[] = "$usepath/usemodules.txt";
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
     * @param string       $modName    Module name
     * @param string       $pluginName Plugin name
     * @param integer|null $caching    Whether or not to cache (Zikula_View::CACHE_*) or use config variable (null)
     * @param string       $cache_id   Cache Id
     *
     * @return Zikula_View_Plugin The plugin instance
     */
    public static function getModulePluginInstance($modName, $pluginName, $caching = null, $cache_id = null)
    {
        return Zikula_View_Plugin::getPluginInstance($modName, $pluginName, $caching, $cache_id);
    }

    /**
     * Get system plugin Zikula_View_Plugin instance.
     *
     * @param string       $pluginName Plugin name
     * @param integer|null $caching    Whether or not to cache (Zikula_View::CACHE_*) or use config variable (null)
     * @param string       $cache_id   Cache Id
     *
     * @return Zikula_View_Plugin The plugin instance
     */
    public static function getSystemPluginInstance($pluginName, $caching = null, $cache_id = null)
    {
        $modName = 'zikula';

        return Zikula_View_Plugin::getPluginInstance($modName, $pluginName, $caching, $cache_id);
    }

    /**
     * Internal registration of Zikula core's plugins sensible to cache.
     *
     * Basically the user-based ones and those that has relation with the theme/pagevars.
     *
     * @return void
     */
    protected function register_nocache_plugins()
    {
        // disables the cache for them and do not load them yet
        // that happens later when required
        $delayed_load = true;
        $cacheable    = false;

        //// blocks
        // checkgroup
        Zikula_View_Resource::register($this, 'block', 'checkgroup', $delayed_load, $cacheable, ['gid']);
        // checkpermissionblock
        Zikula_View_Resource::register($this, 'block', 'checkpermissionblock', $delayed_load, $cacheable, ['component', 'instance']);
        // pageaddvarblock
        Zikula_View_Resource::register($this, 'block', 'pageaddvarblock', $delayed_load, $cacheable, ['name']);

        //// plugins
        // ajaxheader
        Zikula_View_Resource::register($this, 'function', 'ajaxheader', $delayed_load, $cacheable, ['modname', 'filename', 'noscriptaculous', 'validation', 'lightbox', 'imageviewer', 'assign']);
        // assign_cache
        Zikula_View_Resource::register($this, 'function', 'assign_cache', $delayed_load, $cacheable, ['var', 'value']);
        // checkpermission
        Zikula_View_Resource::register($this, 'function', 'checkpermission', $delayed_load, $cacheable, ['component', 'instance', 'level', 'assign']);
        // formutil_getfieldmarker
        Zikula_View_Resource::register($this, 'function', 'formutil_getfieldmarker', $delayed_load, $cacheable, ['objectType', 'validation', 'field', 'assign']);
        // formutil_getpassedvalue
        Zikula_View_Resource::register($this, 'function', 'formutil_getpassedvalue', $delayed_load, $cacheable, ['assign', 'html', 'key', 'name', 'default', 'source', 'noprocess']);
        // formutil_getvalidationerror
        Zikula_View_Resource::register($this, 'function', 'formutil_getvalidationerror', $delayed_load, $cacheable, ['objectType', 'field', 'assign']);
        // notifydisplayhooks
        Zikula_View_Resource::register($this, 'function', 'notifydisplayhooks', $delayed_load, $cacheable, ['eventname', 'id', 'urlobject', 'assign']);
        // notifyevent
        Zikula_View_Resource::register($this, 'function', 'notifyevent', $delayed_load, $cacheable, ['eventname', 'eventsubject', 'eventdata', 'assign']);
        // pageaddvar
        Zikula_View_Resource::register($this, 'function', 'pageaddvar', $delayed_load, $cacheable, ['name', 'value']);
        // pagegetvar
        Zikula_View_Resource::register($this, 'function', 'pagegetvar', $delayed_load, $cacheable, ['name', 'html', 'assign']);
        // pager
        Zikula_View_Resource::register($this, 'function', 'pager', $delayed_load, $cacheable, ['modname', 'type', 'func', 'rowcount', 'limit', 'posvar', 'owner', 'template', 'includeStylesheet', 'anchorText', 'maxpages', 'display', 'class', 'processDetailLinks', 'processUrls', 'optimize']);
        // pageregistervar
        Zikula_View_Resource::register($this, 'function', 'pageregistervar', $delayed_load, $cacheable, ['name']);
        // pagesetvar
        Zikula_View_Resource::register($this, 'function', 'pagesetvar', $delayed_load, $cacheable, ['name', 'value']);
        // servergetvar
        Zikula_View_Resource::register($this, 'function', 'servergetvar', $delayed_load, $cacheable, ['name', 'default', 'assign']);
        // sessiondelvar
        Zikula_View_Resource::register($this, 'function', 'sessiondelvar', $delayed_load, $cacheable, ['name', 'path', 'assign']);
        // sessiongetvar
        Zikula_View_Resource::register($this, 'function', 'sessiongetvar', $delayed_load, $cacheable, ['name', 'assign', 'default', 'path']);
        // sessionsetvar
        Zikula_View_Resource::register($this, 'function', 'sessionsetvar', $delayed_load, $cacheable, ['name', 'value', 'path', 'assign']);
        // setmetatag
        Zikula_View_Resource::register($this, 'function', 'setmetatag', $delayed_load, $cacheable, ['name', 'value']);
        // themegetvar
        Zikula_View_Resource::register($this, 'function', 'themegetvar', $delayed_load, $cacheable, ['name', 'default', 'assign']);
        // themesetvar
        Zikula_View_Resource::register($this, 'function', 'themesetvar', $delayed_load, $cacheable, ['name', 'value']);
        // user
        Zikula_View_Resource::register($this, 'function', 'user', $delayed_load, $cacheable);
        // useravatar - without uid caching
        Zikula_View_Resource::register($this, 'function', 'useravatar', $delayed_load, $cacheable);
        // usergetvar
        Zikula_View_Resource::register($this, 'function', 'usergetvar', $delayed_load, $cacheable, ['assign', 'default', 'name', 'uid']);
        // userlinks
        Zikula_View_Resource::register($this, 'function', 'userlinks', $delayed_load, $cacheable, ['start', 'end', 'seperator']);
        // userloggedin
        Zikula_View_Resource::register($this, 'function', 'userloggedin', $delayed_load, $cacheable, ['assign']);
        // userwelcome
        Zikula_View_Resource::register($this, 'function', 'userwelcome', $delayed_load, $cacheable);
        // zdebug
        Zikula_View_Resource::register($this, 'function', 'zdebug', $delayed_load, $cacheable);
    }

    /**
     * Checks whether requested template exists.
     *
     * @param string $template Template name
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
     * @param string $template Template name
     *
     * @return string Template path
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

            try {
                $bundle = $this->getContainer()->get('kernel')->getBundle($module);
                $bundlePath = $relativepath = $bundle->getRelativePath().'/Resources/views';
            } catch (\InvalidArgumentException $e) {
            }

            if (!isset($bundlePath)) {
                $relativepath = "$os_dir/$os_module/Resources/views";
                if (!is_dir($relativepath)) {
                    $relativepath = "$os_dir/$os_module/templates";
                }
            }

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

            // The rest of this code is scheduled for removal from 1.5.0 - drak

            // check the module for which we're looking for a template is the
            // same as the top level mods. This limits the places to look for
            // templates.
            if ($module == $modname) {
                $search_path = [
                    "themes/$os_theme/templates/modules/$os_module", // themepath
                    "config/templates/$os_module", //global path
                    $relativepath,
                    "$os_dir/$os_module/templates" // modpath
                ];
            } else {
                $search_path = [
                    "themes/$os_theme/templates/modules/$os_module/$os_modname", // themehookpath
                    "themes/$os_theme/templates/modules/$os_module", // themepath
                    "config/templates/$os_module/$os_modname", //globalhookpath
                    "config/templates/$os_module", //global path
                    $relativepath,
                    "$os_dir/$os_module/templates/$os_modname", //modhookpath
                    "$os_dir/$os_module/templates" // modpath
                ];
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
            $this->serviceManager['zikula_view.coredata'] = new ArrayObject([]);
        }

        $core = $this->serviceManager['zikula_view.coredata'];
        $core['version_num'] = Zikula_Core::VERSION_NUM;
        $core['version_id'] = Zikula_Core::VERSION_ID;
        $core['version_sub'] = Zikula_Core::VERSION_SUB;
        $core['logged_in'] = UserUtil::isLoggedIn();
        $core['language'] = $this->language;

        // add userdata
        $core['user']   = UserUtil::getVars(SessionUtil::getVar('uid'));

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
     * Executes & returns the template results.
     *
     * This returns the template output instead of displaying it.
     * Supply a valid template name.
     * As an optional second parameter, you can pass a cache id.
     * As an optional third parameter, you can pass a compile id.
     *
     * @param string  $template   The name of the template
     * @param string  $cache_id   The cache ID (optional)
     * @param string  $compile_id The compile ID (optional)
     * @param boolean $display    Whether or not to display directly (optional)
     * @param boolean $reset      Reset singleton defaults (optional). deprecated
     *
     * @return string The template output
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
        $output = $this->_fetch($template, $cache_id, $compile_id, $display);

        if ($this->expose_template == true) {
            $template = DataUtil::formatForDisplay($template);
            //$output = "\n<!-- Start " . $this->template_dir . "/$template -->\n" . $output . "\n<!-- End " . $this->template_dir . "/$template -->\n";
            // Changed comment into conditional statement for IE<10
            $output = "\n<!--[if !IE]> Start " . $this->template_dir . "/$template <![endif]-->\n" . $output . "\n<!-- End " . $this->template_dir . "/$template -->\n";
        }

        $event = new \Zikula\Core\Event\GenericEvent($this, ['template' => $template], $output);

        return $this->eventManager->dispatch('view.postfetch', $event)->getData();
    }

    /**
     * Executes & displays the template results.
     *
     * This displays the template.
     * Supply a valid template name.
     * As an optional second parameter, you can pass a cache id.
     * As an optional third parameter, you can pass a compile id.
     *
     * @param string $template   The name of the template
     * @param string $cache_id   The cache ID (optional)
     * @param string $compile_id The compile ID (optional)
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
     * @param string $cache_id   The cache ID (optional)
     * @param string $compile_id The compile ID (optional)
     *
     * @return string|null The auto_id, or null if neither $cache_id nor $compile_id are set
     */
    public function _get_auto_id($cache_id = null, $compile_id = null)
    {
        if (!empty($cache_id)) {
            $this->_filter_auto_id($cache_id);
        }
        if (!empty($compile_id)) {
            $this->_filter_auto_id($compile_id);
        }

        $auto_id = $cache_id . (!empty($compile_id) ? '/'.$compile_id : '');

        $auto_id = trim($auto_id, '/');

        return $auto_id;
    }

    /**
     * utility method to filter the IDs of not desired chars.
     *
     * @param string &$id Cache or compile ID to filter
     *
     * @return void
     */
    protected function _filter_auto_id(&$id)
    {
        // convert some chars used as separators
        $id = str_replace([':', '=', ','], '_', $id);
        // convert the "Smarty cache groups" | to paths
        $id = str_replace('|', '/', $id);
        // and remove anything outside the acceptable range
        $id = preg_replace('#[^a-zA-Z0-9-_/]+#', '', $id);
    }

    /**
     * Get a concrete filename for automagically created content.
     *
     * @param string $path        The base path
     * @param string $auto_source The file name (optional)
     * @param string $auto_id     The ID (optional)
     *
     * @return string The concrete path and file name to the content
     */
    public function _get_auto_filename($path, $auto_source = null, $auto_id = null, $themedir = null)
    {
        // enables a flags to detect when is treating compiled templates
        $tocompile = ($path == $this->compile_dir) ? true : false;

        // format auto_source for os to make sure that id does not contain 'ugly' characters
        $auto_source = DataUtil::formatForOS($auto_source);

        // build a hierarchical directory path
        $path .= '/' . $this->modinfo['directory'];

        if ($this instanceof Zikula_View_Plugin) {
            $path .= '_' . $this->getPluginName();
        }

        // add the cache_id path if set
        $path .= !empty($auto_id) ? '/' . $auto_id : '';

        // takes in account the source subdirectory
        if ($auto_source) {
            if (strpos($auto_source, 'file:') === 0) {
                // This is an absolute path needing special handling.
                $auto_source = substr($auto_source, 5);
                $cwd = DataUtil::formatForOS(getcwd());
                if (strpos($auto_source, $cwd) !== 0) {
                    throw new \RuntimeException('The template path cannot be outside the Zikula root.');
                }

                $path .= '/absolutepath' . substr(dirname($auto_source), strlen($cwd));
            } else {
                $path .= strpos($auto_source, '/') !== false ? '/' . dirname($auto_source) : '';
            }
        }

        // make sure the path exists to write the compiled/cached template there
        if (!file_exists($path)) {
            mkdir($path, $this->serviceManager['system.chmod_dir'], true);
        }

        // if there's a explicit source, it
        if ($auto_source) {
            $path .= '/';

            $extension = FileUtil::getExtension($auto_source);

            // isolates the filename on the source path passed
            $path .= FileUtil::getFilebase($auto_source);

            // add theme and language to our path
            if (empty($themedir)) {
                $themedir = $this->themeinfo['directory'];
            }
            $path .= '--t_'.$themedir.'-l_' . $this->language;

            // if we are not compiling, end with a suffix
            if (!$tocompile) {
                $path .= ($extension ? ".$extension" : '');
            }
        }

        return $path;
    }

    /**
     * Finds out if a template is already cached.
     *
     * This returns true if there is a valid cache for this template.
     *
     * @param string $template   The name of the template
     * @param string $cache_id   The cache ID (optional)
     * @param string $compile_id The compile ID (optional)
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
     * Internal function to delete cache of templates.
     *
     * @param string  $tplpath  Relative template path
     * @param string  $template Template partial filename
     * @param integer $expire   Expire limit of the cached templates
     *
     * @return boolean True on success, false otherwise
     */
    protected function rmtpl($tplpath, $template, $expire = null)
    {
        if (!$template || !is_dir($tplpath) || !is_readable($tplpath)) {
            return false;
        }

        $filebase = FileUtil::getFilebase($template);

        $dh = opendir($tplpath);
        while (($entry = readdir($dh)) !== false) {
            if ($entry != '.' && $entry != '..') {
                $path = $tplpath . DIRECTORY_SEPARATOR . $entry;

                if (is_dir($path)) {
                    // search recusively
                    $this->rmtpl($path, $template, $expire);
                } elseif (strpos($entry, $filebase) === 0) {
                    // delete the files that matches the template base filename
                    $this->_unlink($path, $expire);
                }
            }
        }
        closedir($dh);

        return true;
    }

    /**
     * Internal function to delete cache directories and files.
     *
     * @param string  $dirname Relative cache directory path
     * @param integer $expire  Expire limit of the cached templates
     * @param boolean $rmbase  Remove the passed directory too (default: true)
     *
     * @return boolean True on success, false otherwise
     */
    protected function rmdir($dirname, $expire = null, $rmbase = true)
    {
        if (!is_dir($dirname) || !is_readable($dirname)) {
            return false;
        }

        $dh = opendir($dirname);
        while (($entry = readdir($dh)) !== false) {
            if ($entry != '.' && $entry != '..' && $entry != 'index.html') {
                $path = $dirname . DIRECTORY_SEPARATOR . $entry;

                if (is_dir($path)) {
                    // remove recursively
                    $this->rmdir($path, $expire, true);
                } elseif ($expire !== false) {
                    // check expiration time of cached templates
                    $this->_unlink($path, $expire);
                } else {
                    // delete compiled templates directly
                    unlink($path);
                }
            }
        }
        closedir($dh);

        if ($rmbase) {
            return rmdir($dirname);
        }

        return true;
    }

    /**
     * Clears a temporary folder for a auto_id and/or template.
     *
     * This returns true if the operation was successful.
     *
     * @param string $tmpdir   Name of the temporary folder to clear
     * @param string $auto_id  The cache and compile ID
     * @param string $template The name of the template
     * @param string $expire   Minimum age in sec. the cache file must be before it will get cleared (optional)
     *
     * @return boolean
     */
    protected function clear_folder($tmpdir, $auto_id = null, $template = null, $expire = null, $themedir = null)
    {
        if (!$auto_id && !$template) {
            $result = $this->rmdir($tmpdir, $expire, false);
        } else {
            $autofolder = $this->_get_auto_filename($tmpdir, null, $auto_id, $themedir);

            if ($template) {
                $result = $this->rmtpl($autofolder, $template, $expire);
            } else {
                $result = $this->rmdir($autofolder, $expire);
            }
        }

        return $result;
    }

    /**
     * Clears the cache for a specific template or cache_id.
     *
     * @param string $template   The name of the template
     * @param string $cache_id   The cache ID (optional)
     * @param string $compile_id The compile ID (optional)
     * @param string $expire     Minimum age in sec. the cache file must be before it will get cleared (optional)
     *
     * @return boolean True on success, false otherwise
     */
    public function clear_cache($template = null, $cache_id = null, $compile_id = null, $expire = null, $themedir = null)
    {
        if (is_null($compile_id) && $template) {
            $compile_id = $this->compile_id;
        }

        $auto_id = $this->_get_auto_id($cache_id, $compile_id);

        return $this->clear_folder($this->cache_dir, $auto_id, $template, $expire, $themedir);
    }

    /**
     * Clears all view cache for a module.
     *
     * @return boolean True on success, false otherwise
     */
    public function clear_cache_module($moduledir = null)
    {
        if (is_null($moduledir)) {
            $moduledir = $this->modinfo['directory'];
        }

        return $this->clear_folder($this->cache_dir .'/'. $moduledir);
    }

    /**
     * Clear all compiled templates.
     *
     * Needs to clear the cache too as non cached plugins information will need regeneration too.
     *
     * @return boolean True if success, false otherwise
     */
    public function clear_compiled()
    {
        if ($this->clear_folder($this->compile_dir, null, null, false)) {
            return $this->clear_all_cache();
        }

        return false;
    }

    /**
     * Clear all cached templates.
     *
     * @param string $expire Expire time
     *
     * @return boolean Results of clear_cache with null parameters
     */
    public function clear_all_cache($expire = null)
    {
        return $this->clear_cache(null, null, null, $expire);
    }

    /**
     * Set up paths for the template.
     *
     * This function sets the template and the config path according
     * to where the template is found (Theme or Module directory)
     *
     * @param string $template The template name
     *
     * @return void
     */
    public function _setup_template($template)
    {
        // default directory for templates
        $this->template_dir = $this->get_template_path($template);
        $this->templatePath = $this->template_dir . '/' . $template;
        $this->config_dir   = $this->template_dir . '/config';
    }

    /**
     * Add a plugin dir to the search path.
     *
     * Avoids adding duplicates.
     *
     * @param string  $dir  The directory to add
     * @param boolean $push Whether to push the new dir to the bottom of the stack (default: true)
     *
     * @return Zikula_View This instance
     */
    public function addPluginDir($dir, $push = true)
    {
        if (in_array($dir, $this->plugins_dir) || !@is_dir($dir)) {
            return $this;
        }

        if ($push) {
            array_push($this->plugins_dir, $dir);
        } else {
            $this->plugins_dir = array_merge([$dir], $this->plugins_dir);
        }

        return $this;
    }

    /**
     * add a plugins dir to _plugin_dir array
     *
     * This function takes  module name and adds two path two the plugins_dir array
     * when existing
     *
     * @param string $module Well known module name
     *
     * @return void
     */
    protected function _add_plugins_dir($module)
    {
        if (empty($module)) {
            return;
        }

        $modinfo = ModUtil::getInfoFromName($module);
        if (!$modinfo) {
            return;
        }

        $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';
        if (is_dir("$modpath/$modinfo[directory]/Resources/views/plugins")) {
            $this->addPluginDir("$modpath/$modinfo[directory]/Resources/views/plugins");

            return;
        }

        if (is_dir("$modpath/$modinfo[directory]/templates/plugins")) {
            $this->addPluginDir("$modpath/$modinfo[directory]/templates/plugins");
        }
    }

    /**
     * Execute a template override event.
     *
     * @param string $template Path to template
     *
     * @throws InvalidArgumentException If event handler returns a non-existent template
     *
     * @return mixed String if found, false if no override present
     */
    public static function getTemplateOverride($template)
    {
        $event = new \Zikula\Core\Event\GenericEvent(null, [], $template);
        EventUtil::getManager()->dispatch('zikula_view.template_override', $event);

        if ($event->isPropagationStopped()) {
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
     * Assign variable to template.
     *
     * @param string $key   Variable name
     * @param mixed  $value Value
     *
     * @return Zikula_View
     */
    public function assign($key, $value = null)
    {
        $this->_assign_check($key);
        parent::assign($key, $value);

        return $this;
    }

    /**
     * Assign variable to template by reference.
     *
     * @param string $key Variable name
     * @param mixed  &$value Value
     *
     * @return Zikula_View
     */
    public function assign_by_ref($key, &$value)
    {
        $this->_assign_check($key);
        parent::assign_by_ref($key, $value);

        return $this;
    }

    /**
     * Prevent certain variables from being overwritten.
     *
     * @param string $key The protected variable key
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
            switch (strtolower($key)) {
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
     * Translate.
     *
     * @param string $msgid String to be translated
     *
     * @return string The $msgid translated by gettext
     */
    public function __($msgid)
    {
        return __($msgid, $this->domain);
    }

    /**
     * Translate with sprintf().
     *
     * @param string       $msgid  String to be translated
     * @param string|array $params Args for sprintf()
     *
     * @return string The $msgid translated by gettext
     */
    public function __f($msgid, $params)
    {
        return __f($msgid, $params, $this->domain);
    }

    /**
     * Translate plural string.
     *
     * @param string $singular Singular instance
     * @param string $plural   Plural instance
     * @param string $count    Object count
     *
     * @return string Translated string
     */
    public function _n($singular, $plural, $count)
    {
        return _n($singular, $plural, $count, $this->domain);
    }

    /**
     * Translate plural string with sprintf().
     *
     * @param string       $sin    Singular instance
     * @param string       $plu    Plural instance
     * @param string       $n      Object count
     * @param string|array $params Sprintf() arguments
     *
     * @return string The $sin or $plu translated by gettext, based on $n
     */
    public function _fn($sin, $plu, $n, $params)
    {
        return _fn($sin, $plu, $n, $params, $this->domain);
    }

    /**
     * Retrieves the gettext domain for the module, as {@link ZLanguage::getModuleDomain()}.
     *
     * If the module is a system module this is not set.
     *
     * @return string The gettext domain for the module, or null for system modules
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Retrieve an array of module information, indexed by module name.
     *
     * @return array An array containing the module info, indexed by module name
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Retrieve the module info array for the top-level module (or the module specified in the constructor).
     *
     * @return array The module info array
     */
    public function getModInfo()
    {
        return $this->modinfo;
    }

    /**
     * Retrieve the name of the top-level module.
     *
     * @return string The name of the top-level module
     */
    public function getTopLevelModule()
    {
        return $this->toplevelmodule;
    }

    /**
     * Retrieve module name.
     *
     * @return string Module name
     */
    public function getModuleName()
    {
        return $this->toplevelmodule;
    }

    /**
     * Retrive the name of the controller type.
     *
     * @return string The name of the controller type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Retrive the name of the controller function.
     *
     * @return string The name of the controller function
     */
    public function getFunc()
    {
        return $this->func;
    }

    /**
     * Retrieve the current language code.
     *
     * @return string The current language code
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Retrieve the name of the current theme.
     *
     * @return string The name of the current theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Retrieve the theme info array for the current theme.
     *
     * @param string $key Field to retrieve of the theme info
     *
     * @return array The theme info array
     */
    public function getThemeInfo($key = null)
    {
        if ($key && array_key_exists($key, $this->themeinfo)) {
            return $this->themeinfo[$key];
        }

        return $this->themeinfo;
    }

    /**
     * Set a value or all the theme info array.
     *
     * @param mixed  $value Value to assign
     * @param string $key   Field to set on the theme info
     *
     * @return void
     */
    public function setThemeInfo($value, $key = null)
    {
        if ($key) {
            $this->themeinfo[$key] = $value;
        }

        $this->themeinfo = $value;
    }

    /**
     * Retrieve the site's base URL.
     *
     * The value returned is the same as {@link System::getBaseUrl()}.
     *
     * @return string The base URL
     */
    public function getBaseUrl()
    {
        return $this->baseurl;
    }

    /**
     * Retrieve the site's base URI.
     *
     * The value returned is the same as {@link System::getBaseUri()}.
     *
     * @return string The base URI
     */
    public function getBaseUri()
    {
        return $this->baseuri;
    }

    /**
     * Get ServiceManager.
     *
     * @deprecated since 1.4.0
     * @see getContainer()
     *
     * @return Zikula_ServiceManager The service manager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Get ServiceManager.
     *
     * @return Zikula_ServiceManager The service manager
     */
    public function getContainer()
    {
        return $this->serviceManager;
    }

    /**
     * Get EventManager.
     *
     * @deprecated since 1.4.0
     * @see getDispatcher()
     *
     * @return Zikula_Eventmanager The event manager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Get EventManager.
     *
     * @return Zikula_Eventmanager The event manager
     */
    public function getDispatcher()
    {
        return $this->eventManager;
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
     * @param Zikula_AbstractController $controller Controller to set
     *
     * @return void
     */
    public function setController(Zikula_AbstractController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Retrieve the current setting for the 'render_expose_template' Theme module variable.
     *
     * @return boolean True if The 'render_expose_template' Theme module template is true
     */
    public function getExposeTemplate()
    {
        return $this->expose_template;
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
     * Retrieve the name of the directory where templates are located.
     *
     * @return string The directory name
     */
    public function getTemplateDir()
    {
        return $this->template_dir;
    }

    /**
     * Retrieve the template variables array ({@link Smarty::$_tpl_vars}).
     *
     * @return array The template variables array
     */
    public function getTplVars()
    {
        return $this->_tpl_vars;
    }

    /**
     * Get a template variable.
     *
     * @param string $key Key of assigned template variable
     *
     * @return mixed
     */
    public function getTplVar($key)
    {
        if (!array_key_exists($key, $this->_tpl_vars)) {
            return null;
        }

        return $this->_tpl_vars[$key];
    }

    /**
     * Retrieve the compile ID used to compile different sets of compiled files for the same templates.
     *
     * @return string|null The compile id, or null if none
     */
    public function getCompileId()
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
     * @param string|null $compile_id The compile id, or null
     *
     * @return $this
     */
    public function setCompileId($compile_id)
    {
        $this->compile_id = $compile_id;

        return $this;
    }

    /**
     * Retrieve the directory where compiled templates are located.
     *
     * @return string The directory name
     */
    public function getCompileDir()
    {
        return $this->compile_dir;
    }

    /**
     * Set the directory where compiled templates are located.
     *
     * @param string $compile_dir The directory name
     *
     * @return $this
     */
    public function setCompileDir($compile_dir)
    {
        $this->compile_dir = $compile_dir;

        return $this;
    }

    /**
     * Retrieve the flag that controls whether to check for recompiling or not.
     *
     * Recompiling does not need to happen unless a template or config file is changed.
     * Typically you enable this during development, and disable for production.
     *
     * @return boolean True if checked, otherwise false
     */
    public function getCompileCheck()
    {
        return $this->compile_check;
    }

    /**
     * Set compile check.
     *
     * @param boolean $doCompileCheck If true, checks for compile will be performed
     *
     * @return $this
     */
    public function setCompileCheck($doCompileCheck)
    {
        $this->compile_check = $doCompileCheck;

        return $this;
    }

    /**
     * Retrieve whether templates are forced to be compiled.
     *
     * @return boolean True if templates are forced to be compiled, otherwise false
     */
    public function getForceCompile()
    {
        return $this->force_compile;
    }

    /**
     * Set whether templates are forced to be compiled.
     *
     * @param boolean $force_compile True to force compilation, otherwise false
     *
     * @return $this
     */
    public function setForceCompile($force_compile)
    {
        $this->force_compile = $force_compile;

        return $this;
    }

    /**
     * Retrieve whether caching is enabled.
     *
     * @return integer A code indicating whether caching is enabled
     */
    public function getCaching()
    {
        return $this->caching;
    }

    /**
     * Set Caching.
     *
     * Possible value:
     * <ul>
     *  <li>0 = no caching</li>
     *  <li>1 = use class cache_lifetime value</li>
     *  <li>2 = use cache_lifetime in cache file</li>
     * </ul>
     *
     * @param integer $caching Cache value to set
     *
     * @return $this
     */
    public function setCaching($caching)
    {
        $this->caching = (int)$caching;

        return $this;
    }

    /**
     * Retrieve the current cache ID.
     *
     * @return string The current cache ID
     */
    public function getCacheId()
    {
        return $this->cache_id;
    }

    /**
     * Set cache ID.
     *
     * @param string $id Cache ID
     *
     * @return $this
     */
    public function setCacheId($id)
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
     * @return integer The number of seconds cached content will persist
     */
    public function getCacheLifetime()
    {
        return $this->cache_lifetime;
    }

    /**
     * Set cache lifetime.
     *
     * @param integer $time Lifetime in seconds
     *
     * @return $this
     */
    public function setCacheLifetime($time)
    {
        $this->cache_lifetime = $time;

        return $this;
    }

    /**
     * Retrieve the name of the directory for cache files.
     *
     * @return string The name of the cache file directory
     */
    public function getCacheDir()
    {
        return $this->cache_dir;
    }

    /**
     * Set the name of the directory for cache files.
     *
     * @param string $cache_dir The name of the cache file directory
     *
     * @return $this
     */
    public function setCacheDir($cache_dir)
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
     * @return boolean True if If-Modified-Since headers are respected, otherwise false
     */
    public function getCacheModifiedCheck()
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
     * @param boolean $cache_modified_check True to respect If-Modified-Since headers, otherwise false
     *
     * @return $this
     */
    public function setCacheModifiedCheck($cache_modified_check)
    {
        $this->cache_modified_check = $cache_modified_check;

        return $this;
    }

    /**
     * Retrieve the directgory where config files are located.
     *
     * @return string The directory name
     */
    public function getConfigDir()
    {
        return $this->config_dir;
    }

    /**
     * Set the directgory where config files are located.
     *
     * @param string $config_dir The directory name
     *
     * @return $this
     */
    public function setConfigDir($config_dir)
    {
        $this->config_dir = $config_dir;

        return $this;
    }

    /**
     * Retrieve the directories that are searched for plugins.
     *
     * @return array An array of directory names
     */
    public function getPluginsDir()
    {
        return $this->plugins_dir;
    }

    /**
     * Set an array of directories that are searched for plugins.
     *
     * @param array $plugins_dir An array of directory names
     *
     * @return $this
     */
    public function setPluginsDir($plugins_dir)
    {
        $this->plugins_dir = $plugins_dir;

        return $this;
    }

    /**
     * Retrieve whether debugging mode is enabled or disabled.
     *
     * @return boolean True if enabled, otherwise false
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
     * @param boolean $debugging True to enable, otherwise false
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
     * @return integer The PHP error reporting level
     */
    public function getErrorReporting()
    {
        return $this->error_reporting;
    }

    /**
     * Set the PHP error reporting level to be used for this class.
     *
     * @param integer $error_reporting The PHP error reporting level
     *
     * @see    error_reporting()
     *
     * @return $this
     */
    public function setErrorReporting($error_reporting)
    {
        $this->error_reporting = $error_reporting;

        return $this;
    }

    /**
     * Retrieve the custom path to the debug console template.
     *
     * If empty, the default template is used.
     *
     * @return string The custom path to the debug console template
     */
    public function getDebugTpl()
    {
        return $this->debug_tpl;
    }

    /**
     * Set a custom path to the debug console template.
     *
     * If empty, the default template is used.
     *
     * @param string $debug_tpl The custom path to the debug console template
     *
     * @return $this
     */
    public function setDebugTpl($debug_tpl)
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
     * @return string Either 'NONE' or 'URL'
     */
    public function getDebuggingCtrl()
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
     * @param string $debugging_ctrl Either 'NONE' or 'URL'
     *
     * @return $this
     */
    public function setDebuggingCtrl($debugging_ctrl)
    {
        $this->debugging_ctrl = $debugging_ctrl;

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
     * @return integer A code indicating how php tags in templates are handled
     */
    public function getPhpHandling()
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
     * @param integer $php_handling A code indicating how php tags in templates are to be handled
     *
     * @return $this
     */
    public function setPhpHandling($php_handling)
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
     * @return boolean True if enabled, otherwise false
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
     * @param boolean $security True to enable, otherwise false
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
     * @return array An array of secure template directories
     */
    public function getSecureDir()
    {
        return $this->secure_dir;
    }

    /**
     * Set the list of template directories that are considered secure.
     *
     * This is used only if template security enabled (see {@link setSecurity()}). One directory per array
     * element.  The template directory (see {@link setTemplateDir()}) is in this list implicitly.
     *
     * @param array $secure_dir An array of secure template directories
     *
     * @return $this
     */
    public function setSecureDir($secure_dir)
    {
        $this->secure_dir = $secure_dir;

        return $this;
    }

    /**
     * Retrieve an array of security settings, only used if template security is enabled (see {@link setSecurity()}).
     *
     * @return array An array of security settings
     */
    public function getSecuritySettings()
    {
        return $this->security_settings;
    }

    /**
     * Set an array of security settings, only used if template security is enabled (see {@link setSecurity()}).
     *
     * @param array $security_settings An array of security settings
     *
     * @return $this
     */
    public function setSecuritySettings($security_settings)
    {
        $this->security_settings = $security_settings;

        return $this;
    }

    /**
     * Retrieve an array of directories where trusted php scripts reside.
     *
     * @return array An array of trusted directories
     */
    public function getTrustedDir()
    {
        return $this->trusted_dir;
    }

    /**
     * Set an array of directories where trusted php scripts reside.
     *
     * Template security (see @link setSecurity()) is disabled during their inclusion/execution.
     *
     * @param array $trusted_dir An array of trusted directories
     *
     * @return $this
     */
    public function setTrustedDir($trusted_dir)
    {
        $this->trusted_dir = $trusted_dir;

        return $this;
    }

    /**
     * Retrieve the left delimiter used for template tags.
     *
     * @return string The delimiter
     */
    public function getLeftDelimiter()
    {
        return $this->left_delimiter;
    }

    /**
     * Set the left delimiter used for template tags.
     *
     * @param string $left_delimiter The delimiter
     *
     * @return $this
     */
    public function setLeftDelimiter($left_delimiter)
    {
        $this->left_delimiter = $left_delimiter;

        return $this;
    }

    /**
     * Retrieve the right delimiter used for template tags.
     *
     * @return string The delimiter
     */
    public function getRightDelimiter()
    {
        return $this->right_delimiter;
    }

    /**
     * Set the right delimiter used for template tags.
     *
     * @param string $right_delimiter The delimiter
     *
     * @return $this
     */
    public function setRightDelimiter($right_delimiter)
    {
        $this->right_delimiter = $right_delimiter;

        return $this;
    }

    /**
     * Retrieve the order in which request variables are registered, similar to variables_order in php.ini.
     *
     * E = Environment, G = GET, P = POST, C = Cookies, S = Server
     *
     * @return string The string indicating the order, e.g., 'EGPCS'
     */
    public function getRequestVarsOrder()
    {
        return $this->request_vars_order;
    }

    /**
     * Set the order in which request variables are registered, similar to variables_order in php.ini.
     *
     * E = Environment, G = GET, P = POST, C = Cookies, S = Server
     *
     * @param string $request_vars_order A string indicating the order, e.g., 'EGPCS'
     *
     * @return $this
     */
    public function setRequestVarsOrder($request_vars_order)
    {
        $this->request_vars_order = $request_vars_order;

        return $this;
    }

    /**
     * Retrieve whether $HTTP_*_VARS[] (request_use_auto_globals=false) are used as request-vars or $_*[]-vars.
     *
     * @return boolean True if auto globals are used, otherwise false
     */
    public function getRequestUseAutoGlobals()
    {
        return $this->request_use_auto_globals;
    }

    /**
     * Set whether $HTTP_*_VARS[] (request_use_auto_globals=false) are used as request-vars or $_*[]-vars.
     *
     * Note: if request_use_auto_globals is true, then $request_vars_order has
     * no effect, but the php-ini-value "gpc_order"
     *
     * @param boolean $request_use_auto_globals True to use auto globals, otherwise false
     *
     * @return $this
     */
    public function setRequestUseAutoGlobals($request_use_auto_globals)
    {
        $this->request_use_auto_globals = $request_use_auto_globals;

        return $this;
    }

    /**
     * Retrieve whether or not sub dirs in the cache/ and templates_c/ directories are used.
     *
     * @return boolean True if sub dirs are used, otherwise false
     */
    public function getUseSubDirs()
    {
        return $this->use_sub_dirs;
    }

    /**
     * Set whether or not to use sub dirs in the cache/ and templates_c/ directories.
     *
     * Sub directories better organized, but may not work well with PHP safe mode enabled.
     *
     * @param boolean $use_sub_dirs True to use sub dirs, otherwise false
     *
     * @return $this
     */
    public function setUseSubDirs($use_sub_dirs)
    {
        $this->use_sub_dirs = $use_sub_dirs;

        return $this;
    }

    /**
     * Retrieve a list of the modifiers applied to all template variables.
     *
     * @return array An array of default modifiers
     */
    public function getDefaultModifiers()
    {
        return $this->default_modifiers;
    }

    /**
     * Set a list of the modifiers to apply to all template variables.
     *
     * Put each modifier in a separate array element in the order you want
     * them applied. example: <code>['escape:"htmlall"'];</code>
     *
     * @param array $default_modifiers An array of default modifiers
     *
     * @return $this
     */
    public function setDefaultModifiers($default_modifiers)
    {
        $this->default_modifiers = $default_modifiers;

        return $this;
    }

    /**
     * Retrieve the resource type used when not specified at the beginning of the resource path (see {@link Smarty::$default_resource_type}).
     *
     * @return string The resource type used
     */
    public function getDefaultResourceType()
    {
        return $this->default_resource_type;
    }

    /**
     * Set the resource type to be used when not specified at the beginning of the resource path (see {@link Smarty::$default_resource_type}).
     *
     * @param string $default_resource_type The resource type to use
     *
     * @return $this
     */
    public function setDefaultResourceType($default_resource_type)
    {
        $this->default_resource_type = $default_resource_type;

        return $this;
    }

    /**
     * Retrieve the name of the function used for cache file handling.
     *
     * If not set, built-in caching is used.
     *
     * @return string|null The name of the function, or null if built-in caching is used
     */
    public function getCacheHandlerFunc()
    {
        return $this->cache_handler_func;
    }

    /**
     * Set the name of the function used for cache file handling.
     *
     * If not set, built-in caching is used.
     *
     * @param string|null $cache_handler_func The name of the function, or null to use built-in caching
     *
     * @return $this
     */
    public function setCacheHandlerFunc($cache_handler_func)
    {
        $this->cache_handler_func = $cache_handler_func;

        return $this;
    }

    /**
     * Retrieve whether filters are automatically loaded or not.
     *
     * @return boolean True if automatically loaded, otherwise false
     */
    public function getAutoloadFilters()
    {
        return $this->autoload_filters;
    }

    /**
     * Set whether filters are automatically loaded or not.
     *
     * @param boolean $autoload_filters True to automatically load, otherwise false
     *
     * @return $this
     */
    public function setAutoloadFilters($autoload_filters)
    {
        $this->autoload_filters = $autoload_filters;

        return $this;
    }

    /**
     * Retrieve if config file vars of the same name overwrite each other or not.
     *
     * @return boolean True if overwritten, otherwise false
     */
    public function getConfigOverwrite()
    {
        return $this->config_overwrite;
    }

    /**
     * Set if config file vars of the same name overwrite each other or not.
     *
     * If disabled, same name variables are accumulated in an array.
     *
     * @param boolean $config_overwrite True to overwrite, otherwise false
     *
     * @return $this
     */
    public function setConfigOverwrite($config_overwrite)
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
     * @return boolean True if config variables are booleanized, otherwise false
     */
    public function getConfigBooleanize()
    {
        return $this->config_booleanize;
    }

    /**
     * Set whether or not to automatically booleanize config file variables.
     *
     * If enabled, then the strings "on", "true", and "yes" are treated as boolean
     * true, and "off", "false" and "no" are treated as boolean false.
     *
     * @param boolean $config_booleanize True to booleanize, otherwise false
     *
     * @return $this
     */
    public function setConfigBooleanize($config_booleanize)
    {
        $this->config_booleanize = $config_booleanize;

        return $this;
    }

    /**
     * Retrieve whether hidden sections [.foobar] in config files are readable from the tempalates or not.
     *
     * @return boolean True if hidden sections readable, otherwise false
     */
    public function getConfigReadHidden()
    {
        return $this->config_read_hidden;
    }

    /**
     * Set whether hidden sections [.foobar] in config files are readable from the tempalates or not.
     *
     * Normally you would never allow this since that is the point behind hidden sections: the application can access
     * them, but the templates cannot.
     *
     * @param boolean $config_read_hidden True to make hidden sections readable, otherwise false
     *
     * @return $this
     */
    public function setConfigReadHidden($config_read_hidden)
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
     * @return boolean True if automatically fixed, otherwise false
     */
    public function getConfigFixNewlines()
    {
        return $this->config_fix_newlines;
    }

    /**
     * Set the flag that corrects newlines automatically in config files.
     *
     * This indicates whether or not automatically fix newlines in config files.
     * It basically converts \r (mac) or \r\n (dos) to \n
     *
     * @param boolean $config_fix_newlines True to automatically fix, otherwise false
     *
     * @return $this
     */
    public function setConfigFixNewlines($config_fix_newlines)
    {
        $this->config_fix_newlines = $config_fix_newlines;

        return $this;
    }

    /**
     * Retrieve the name of the PHP function that will be called if a template cannot be found.
     *
     * @return string The name of the PHP function called if a template cannot be found
     */
    public function getDefaultTemplateHandlerFunc()
    {
        return $this->default_template_handler_func;
    }

    /**
     * Set the name of the PHP function that will be called if a template cannot be found.
     *
     * @param string $default_template_handler_func The name of the PHP function to call if a template cannot be found
     *
     * @return $this
     */
    public function setDefaultTemplateHandlerFunc($default_template_handler_func)
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
     * @return string The name of the file that contains the compiler class
     */
    public function getCompilerFile()
    {
        return $this->compiler_file;
    }

    /**
     * Set the name of the file that contains the compiler class.
     *
     * This can a full pathname, or relative to the php_include path.
     *
     * @param string $compiler_file The name of the file that contains the compiler class
     *
     * @see    Smarty::$compiler_file
     * @see    setCompilerClass()
     *
     * @return $this
     */
    public function setCompilerFile($compiler_file)
    {
        $this->compiler_file = $compiler_file;

        return $this;
    }

    /**
     * Retrieve the name of the class used to compile templates.
     *
     * @return string The name of the class used to compile templates
     */
    public function getCompilerClass()
    {
        return $this->compiler_class;
    }

    /**
     * Set the name of the class that will be used to compile templates.
     *
     * @param string $compiler_class The name of the class used to compile templates
     *
     * @return $this
     */
    public function setCompilerClass($compiler_class)
    {
        $this->compiler_class = $compiler_class;

        return $this;
    }

    /**
     * Retrieve the info that makes up a cache file ({@link Smarty::$_cache_info}).
     *
     * @return array Array of info that makes up a cache file
     */
    public function getCacheInfo()
    {
        return $this->_cache_info;
    }

    /**
     * Set the info that makes up a cache file ({@link Smarty::$_cache_info}).
     *
     * @param array $_cache_info Array of info that makes up a cache file
     *
     * @return $this
     */
    public function setCacheInfo($_cache_info)
    {
        $this->_cache_info = $_cache_info;

        return $this;
    }

    /**
     * Retrieve the file permissions ({@link Smarty::$_file_perms}).
     *
     * @return int File permissions
     */
    public function getFilePerms()
    {
        return $this->_file_perms;
    }

    /**
     * Set the file permissions ({@link Smarty::$_file_perms}).
     *
     * @param int $_file_perms File permissions; use an octal number, e.g. set_file_perms(0664)
     *
     * @return $this
     */
    public function setFilePerms($_file_perms)
    {
        $this->_file_perms = $_file_perms;

        return $this;
    }

    /**
     * Retrieve the directory permissions ({@link Smarty::$_dir_perms}).
     *
     * @return int Directory permissions
     */
    public function getDirPerms()
    {
        return $this->_dir_perms;
    }

    /**
     * Set the directory permissions ({@link Smarty::$_dir_perms}).
     *
     * @param int $_dir_perms Directory permissions; use an octal number, e.g. set_dir_perms(0771)
     *
     * @return $this
     */
    public function setDirPerms($_dir_perms)
    {
        $this->_dir_perms = $_dir_perms;

        return $this;
    }

    /**
     * Retrieve the {@link Smarty::$_reg_objects} registered objects.
     *
     * @return array Registered objects array
     */
    public function getRegObjects()
    {
        return $this->_reg_objects;
    }

    /**
     * Set the {@link Smarty::$_reg_objects} registered objects.
     *
     * @param array $_reg_objects Registered objects
     *
     * @return $this
     */
    public function setRegObjects($_reg_objects)
    {
        $this->_reg_objects = $_reg_objects;

        return $this;
    }

    /**
     * Retrieve the array keeping track of plugins (see {@link Smarty::$_plugins}).
     *
     * @return array An array of plugins by type
     */
    public function getPlugins()
    {
        return $this->_plugins;
    }

    /**
     * Set the array keeping track of plugins (see {@link Smarty::$_plugins}).
     *
     * @param array $_plugins An array of plugins by type
     *
     * @return $this
     */
    public function setPlugins($_plugins)
    {
        $this->_plugins = $_plugins;

        return $this;
    }

    /**
     * Retrieve the value of {@link Smarty::$_cache_serials}.
     *
     * @return array Cache serials
     */
    public function getCacheSerials()
    {
        return $this->_cache_serials;
    }

    /**
     * Setter for {@link Smarty::$_cache_serials}
     *
     * @param array $_cache_serials Cache serials
     *
     * @return $this
     */
    public function setCacheSerials($_cache_serials)
    {
        $this->_cache_serials = $_cache_serials;

        return $this;
    }

    /**
     * Retrieve the value of {@link Smarty::$_cache_include}.
     *
     * @return string Name of optional cache include file
     */
    public function getCacheInclude()
    {
        return $this->_cache_include;
    }

    /**
     * Setter for {@link Smarty::$_cache_include}.
     *
     * @param string $_cache_include Name of optional cache include file
     *
     * @return $this
     */
    public function setCacheInclude($_cache_include)
    {
        $this->_cache_include = $_cache_include;

        return $this;
    }

    /**
     * Retrieve the value of {@link Smarty::$_cache_including}.
     *
     * @return boolean True if the current code is used in a compiled include, otherwise false
     */
    public function getCacheIncluding()
    {
        return $this->_cache_including;
    }

    /**
     * Setter for {@link Smarty::$_cache_including}.
     *
     * @param boolean $_cache_including Indicate if the current code is used in a compiled include
     *
     * @return $this
     */
    public function setCacheIncluding($_cache_including)
    {
        $this->_cache_including = $_cache_including;

        return $this;
    }

    /**
     * Disable or enable add the module wrapper.
     *
     * @param boolean $wrap False to disable wrapper, true to enable it
     *
     * @return $this
     */
    public function setWrapper($wrap)
    {
        if ($this->modinfo['name'] == $this->toplevelmodule) {
            Zikula_View_Theme::getInstance()->themeinfo['system'] = !$wrap;
        }

        return $this;
    }

    /**
     * Smarty override to customize the core.process_cached_inserts
     *
     * @param string  $template   The name of the template
     * @param string  $cache_id   The cache ID (optional)
     * @param string  $compile_id The compile ID (optional)
     * @param boolean $display    Whether or not to display directly (optional)
     *
     * @return string The template output
     */
    public function _fetch($resource_name, $cache_id = null, $compile_id = null, $display = false)
    {
        static $_cache_info = [];

        $_smarty_old_error_level = $this->debugging ? error_reporting() : error_reporting(isset($this->error_reporting)
               ? $this->error_reporting : error_reporting() & ~E_NOTICE);
        $_smarty_old_error_level &= ~E_USER_DEPRECATED;

        if (!$this->debugging && $this->debugging_ctrl == 'URL') {
            $_query_string = $this->request_use_auto_globals ? $_SERVER['QUERY_STRING'] : $GLOBALS['HTTP_SERVER_VARS']['QUERY_STRING'];
            if (@strstr($_query_string, $this->_smarty_debug_id)) {
                if (@strstr($_query_string, $this->_smarty_debug_id . '=on')) {
                    // enable debugging for this browser session
                    @setcookie('SMARTY_DEBUG', true);
                    $this->debugging = true;
                } elseif (@strstr($_query_string, $this->_smarty_debug_id . '=off')) {
                    // disable debugging for this browser session
                    @setcookie('SMARTY_DEBUG', false);
                    $this->debugging = false;
                } else {
                    // enable debugging for this page
                    $this->debugging = true;
                }
            } else {
                $this->debugging = (bool)($this->request_use_auto_globals ? @$_COOKIE['SMARTY_DEBUG'] : @$GLOBALS['HTTP_COOKIE_VARS']['SMARTY_DEBUG']);
            }
        }

        if ($this->debugging) {
            // capture time for debugging info
            $_params = [];
            require_once SMARTY_CORE_DIR . 'core.get_microtime.php';
            $_debug_start_time = smarty_core_get_microtime($_params, $this);
            $this->_smarty_debug_info[] = [
                'type'      => 'template',
                'filename'  => $resource_name,
                'depth'     => 0
            ];
            $_included_tpls_idx = count($this->_smarty_debug_info) - 1;
        }

        if (!isset($compile_id)) {
            $compile_id = $this->compile_id;
        }

        $this->_compile_id = $compile_id;
        $this->_inclusion_depth = 0;

        if ($this->caching) {
            // save old cache_info, initialize cache_info
            array_push($_cache_info, $this->_cache_info);
            $this->_cache_info = [];
            $_params = [
                'tpl_file' => $resource_name,
                'cache_id' => $cache_id,
                'compile_id' => $compile_id,
                'results' => null
            ];
            require_once SMARTY_CORE_DIR . 'core.read_cache_file.php';
            if (smarty_core_read_cache_file($_params, $this)) {
                $_smarty_results = $_params['results'];
                if (!empty($this->_cache_info['insert_tags'])) {
                    $_params = ['plugins' => $this->_cache_info['insert_tags']];
                    require_once SMARTY_CORE_DIR . 'core.load_plugins.php';
                    smarty_core_load_plugins($_params, $this);
                    $_params = ['results' => $_smarty_results];
                    // ZIKULA OVERRIDE
                    require_once 'lib/legacy/viewplugins/zikula.process_cached_inserts.php';
                    $_smarty_results = smarty_core_process_cached_inserts($_params, $this);
                }
                if (!empty($this->_cache_info['cache_serials'])) {
                    $_params = ['results' => $_smarty_results];
                    require_once SMARTY_CORE_DIR . 'core.process_compiled_include.php';
                    $_smarty_results = smarty_core_process_compiled_include($_params, $this);
                }

                if ($display) {
                    if ($this->debugging) {
                        // capture time for debugging info
                        $_params = [];
                        require_once SMARTY_CORE_DIR . 'core.get_microtime.php';
                        $this->_smarty_debug_info[$_included_tpls_idx]['exec_time'] = smarty_core_get_microtime($_params, $this) - $_debug_start_time;
                        require_once SMARTY_CORE_DIR . 'core.display_debug_console.php';
                        $_smarty_results .= smarty_core_display_debug_console($_params, $this);
                    }
                    if ($this->cache_modified_check) {
                        $_server_vars = ($this->request_use_auto_globals) ? $_SERVER : $GLOBALS['HTTP_SERVER_VARS'];
                        $_last_modified_date = @substr($_server_vars['HTTP_IF_MODIFIED_SINCE'], 0, strpos($_server_vars['HTTP_IF_MODIFIED_SINCE'], 'GMT') + 3);
                        $_gmt_mtime = gmdate('D, d M Y H:i:s', $this->_cache_info['timestamp']).' GMT';
                        if (@count($this->_cache_info['insert_tags']) == 0
                            && !$this->_cache_serials
                            && $_gmt_mtime == $_last_modified_date) {
                            if (php_sapi_name() == 'cgi') {
                                header('Status: 304 Not Modified');
                            } else {
                                header('HTTP/1.1 304 Not Modified');
                            }
                        } else {
                            header('Last-Modified: '.$_gmt_mtime);
                            echo $_smarty_results;
                        }
                    } else {
                        echo $_smarty_results;
                    }
                    error_reporting($_smarty_old_error_level);
                    // restore initial cache_info
                    $this->_cache_info = array_pop($_cache_info);

                    return true;
                } else {
                    error_reporting($_smarty_old_error_level);
                    // restore initial cache_info
                    $this->_cache_info = array_pop($_cache_info);

                    return $_smarty_results;
                }
            } else {
                $this->_cache_info['template'][$resource_name] = true;
                if ($this->cache_modified_check && $display) {
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
                }
            }
        }

        // load filters that are marked as autoload
        if (count($this->autoload_filters)) {
            foreach ($this->autoload_filters as $_filter_type => $_filters) {
                foreach ($_filters as $_filter) {
                    $this->load_filter($_filter_type, $_filter);
                }
            }
        }

        $_smarty_compile_path = $this->_get_compile_path($resource_name);

        // if we just need to display the results, don't perform output
        // buffering - for speed
        $_cache_including = $this->_cache_including;
        $this->_cache_including = false;
        if ($display && !$this->caching && count($this->_plugins['outputfilter']) == 0) {
            if ($this->_is_compiled($resource_name, $_smarty_compile_path) || $this->_compile_resource($resource_name, $_smarty_compile_path)) {
                include $_smarty_compile_path;
            }
        } else {
            ob_start();
            if ($this->_is_compiled($resource_name, $_smarty_compile_path) || $this->_compile_resource($resource_name, $_smarty_compile_path)) {
                include $_smarty_compile_path;
            }
            $_smarty_results = ob_get_contents();
            ob_end_clean();

            foreach ((array)$this->_plugins['outputfilter'] as $_output_filter) {
                $_smarty_results = call_user_func_array($_output_filter[0], [$_smarty_results, &$this]);
            }
        }

        if ($this->caching) {
            $_params = [
                'tpl_file' => $resource_name,
                'cache_id' => $cache_id,
                'compile_id' => $compile_id,
                'results' => $_smarty_results
            ];
            require_once SMARTY_CORE_DIR . 'core.write_cache_file.php';
            smarty_core_write_cache_file($_params, $this);
            // ZIKULA OVERRIDE
            require_once 'lib/legacy/viewplugins/zikula.process_cached_inserts.php';
            $_smarty_results = smarty_core_process_cached_inserts($_params, $this);

            if ($this->_cache_serials) {
                // strip nocache-tags from output
                $_smarty_results = preg_replace('!(\{/?nocache\:[0-9a-f]{32}#\d+\})!s', '', $_smarty_results);
            }
            // restore initial cache_info
            $this->_cache_info = array_pop($_cache_info);
        }
        $this->_cache_including = $_cache_including;

        if ($display) {
            if (isset($_smarty_results)) {
                echo $_smarty_results;
            }
            if ($this->debugging) {
                // capture time for debugging info
                $_params = [];
                require_once SMARTY_CORE_DIR . 'core.get_microtime.php';
                $this->_smarty_debug_info[$_included_tpls_idx]['exec_time'] = (smarty_core_get_microtime($_params, $this) - $_debug_start_time);
                require_once SMARTY_CORE_DIR . 'core.display_debug_console.php';
                echo smarty_core_display_debug_console($_params, $this);
            }
            error_reporting($_smarty_old_error_level);

            return;
        }

        error_reporting($_smarty_old_error_level);
        if (isset($_smarty_results)) {
            return $_smarty_results;
        }
    }
}

/**
 * Callback function for preg_replace_callback.
 *
 * Allows the use of {{ and }} as delimiters within certain tags,
 * even if they use { and } as block delimiters.
 *
 * @param array $matches The $matches array from preg_replace_callback, containing the matched groups
 *
 * @return string The replacement string for the match
 */
function z_prefilter_add_literal_callback($matches)
{
    $tagOpen = $matches[1];
    $script = $matches[3];
    $tagClose = $matches[4];

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
 * @param string      $tpl_source The template's source prior to prefiltering
 * @param Zikula_View $view       A reference to the Zikula_View object
 *
 * @return string The prefiltered template contents
 */
function z_prefilter_add_literal($tpl_source, $view)
{
    return preg_replace_callback('`(<(script|style)[^>]*>)(.*?)(</\2>)`s', 'z_prefilter_add_literal_callback', $tpl_source);
}

/**
 * Prefilter for gettext parameters.
 *
 * @param string      $tpl_source The template's source prior to prefiltering
 * @param Zikula_View $view       A reference to the Zikula_View object
 *
 * @return string The prefiltered template contents
 */
function z_prefilter_gettext_params($tpl_source, $view)
{
    return preg_replace('#((?:(?<!\{)\{(?!\{)(?:\s*)|\G)(?:.+?))__([a-zA-Z0-9][a-zA-Z_0-9]*=([\'"])(?:\\\\?+.)*?\3)#', '$1$2|gt:\$zikula_view', $tpl_source);
}
