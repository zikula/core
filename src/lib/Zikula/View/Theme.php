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
 * Zikula_View_Theme class.
 */
class Zikula_View_Theme extends Zikula_View
{
    // base theme info
    /**
     * Theme Id.
     *
     * @var integer
     */
    public $id;

    /**
     * Theme name.
     *
     * @var string
     */
    public $name;

    /**
     * Display name.
     *
     * @var string
     */
    public $displayname;

    /**
     * Description.
     *
     * @var string
     */
    public $description;

    /**
     * Type.
     *
     * @var integer
     */
    public $type;

    /**
     * Directory.
     *
     * @var string
     */
    public $directory;

    /**
     * Version.
     *
     * @var string
     */
    public $version;

    /**
     * Contact.
     *
     * @var string
     */
    public $contact;

    /**
     * State.
     *
     * @var integer
     */
    public $state;

    /**
     * XHTML capable.
     *
     * @var integer
     */
    public $xhtml;


    // base theme properties

    /**
     * Theme base path.
     *
     * @var string
     */
    public $themepath;

    /**
     * Theme image path.
     *
     * @var string
     */
    public $imagepath;

    /**
     * Theme language image path.
     *
     * @var string
     */
    public $imagelangpath;

    /**
     * Theme stylesheet path.
     *
     * @var string
     */
    public $stylepath;

    /**
     * Theme script path.
     *
     * @var string
     */
    public $scriptpath;


    /**
     * Theme config.
     *
     * @var array
     */
    public $themeconfig;

    /**
     * Cache page.
     *
     * @var boolean
     */
    public $cachepage;

    /**
     * Home.
     *
     * @var boolean
     */
    public $home;

    /**
     * User id.
     *
     * @var integer
     */
    public $uid;

    /**
     * Function.
     *
     * @var string
     */
    public $func;

    // publics to identify our page

    /**
     * Component id.
     *
     * @var integer
     */
    public $componentid;

    /**
     * Page id.
     *
     * @var integer
     */
    public $pageid;

    /**
     * Page type.
     *
     * @var string
     */
    public $pagetype;

    /**
     * Query string.
     *
     * @var string
     */
    public $qstring;

    /**
     * Request Uri.
     *
     * @var string
     */
    public $requesturi;

    /**
     * Permission level.
     *
     * @var constant
     */
    public $permlevel;

    /**
     * Whether or not the user is logged in.
     *
     * @var boolean.
     */
    public $isloggedin;

    /**
     * Internal override Map.
     *
     * @var array
     */
    protected $_overrideMap = array();

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager.
     * @param string                $themeName      Theme name.
     */
    public function __construct($serviceManager, $themeName)
    {
        // store our theme directory
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themeName));
        foreach ($themeinfo as $key => $value) {
            $this->$key = $value;
        }

        parent::__construct($serviceManager);

        if ($themeinfo['i18n']) {
            ZLanguage::bindThemeDomain($this->name);
            // property for {gt} template plugin to detect language domain
            $this->domain = ZLanguage::getThemeDomain($this->name);
        } else {
            $this->domain = null;
        }

        EventUtil::attachCustomHandlers("themes/$themeName/lib/$themeName/EventHandlers");
        if (is_readable("themes/$themeName/templates/overrides.yml")) {
            $this->eventManager->attach('zikula_view.template_override', array($this, '_templateOverride'), 0);
            $this->_overrideMap = Doctrine_Parser::load("themes/$themeName/templates/overrides.yml", 'yml');
        }

        // change some base settings from our parent class
        // template compilation
        $this->compile_dir = CacheUtil::getLocalDir() . '/Theme_compiled';
        $this->compile_check = ModUtil::getVar('Theme', 'compile_check');
        $this->force_compile = ModUtil::getVar('Theme', 'force_compile');
        $this->compile_id = '';
        // template caching
        $this->cache_dir = CacheUtil::getLocalDir() . '/Theme_cache';
        $this->caching = ModUtil::getVar('Theme', 'enablecache');
        $type = FormUtil::getPassedValue('type', null, 'GETPOST');
        if ($this->caching && strtolower($type) != 'admin') {
            $modulesnocache = explode(',', ModUtil::getVar('Theme', 'modulesnocache'));
            if (in_array($this->toplevelmodule, $modulesnocache)) {
                $this->caching = false;
            }
        } else {
            $this->caching = false;
        }

        // halt caching for write operations to prevent strange things happening
        if (isset($_POST) && count($_POST) != 0) {
            $this->caching = false;
        }

        $this->cache_lifetime = ModUtil::getVar('Theme', 'cache_lifetime');

        // assign all our base template variables
        $this->_base_vars();

        // define the plugin directories
        $this->_plugin_dirs();

        // set the config directory
        $this->_set_configdir();

        // load the theme configuration
        $this->load_config();

        // check for cached output
        // turn on caching, check for cached output and then disable caching
        // to prevent blocks from being cached
        if ($this->caching && $this->is_cached($this->themeconfig['page'], $this->pageid)) {
            $this->display($this->themeconfig['page'], $this->pageid);
            System::shutdown();
        }

        // register page vars output filter
        $this->load_filter('output', 'pagevars');

        // register short urls output filter
        if (System::getVar('shorturls')) {
            $this->load_filter('output', 'shorturls');
        }

        // register trim whitespace output filter if requried
        if (ModUtil::getVar('Theme', 'trimwhitespace')) {
            $this->load_filter('output', 'trimwhitespace');
        }

        $event = new Zikula_Event('theme.init', $this);
        $this->eventManager->notify($event);

        // Start the output buffering to capture module output
        ob_start();
    }

    /**
     * Retrieve the theme ID.
     *
     * @return integer The theme ID.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Retrieve the name of the theme.
     *
     * @return string The name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Retrieve the theme's display name.
     *
     * @return string The display name.
     */
    public function getDisplayname()
    {
        return $this->displayname;
    }

    /**
     * Retrieve the theme's description.
     *
     * @return string The description of the theme.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Retrieve the theme type.
     *
     * @return <type>
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Retrieve the theme directory name.
     *
     * @return <type>
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Retrieve the theme's version string.
     *
     * @return string The version of the theme.
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Retrieve the contact information for the theme.
     *
     * @return string The theme's contact information.
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Retrieve the state of the theme.
     *
     * Values:
     * <ul>
     *   <li>ThemeUtil::STATE_ACTIVE</li>
     *   <li>ThemeUtil::STATE_INACTIVE</li>
     *   <li>ThemeUtil::STATE_ALL</li>
     * </ul>
     *
     * @return integer The theme's state.
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Indicates whether the theme is an XHTML-based theme or an HTML-based theme.
     *
     * @return integer 1 for XHTML-capable, otherwise HTML.
     */
    public function getXhtml()
    {
        return $this->xhtml;
    }

    /**
     * Retrieve the path to the theme.
     *
     * @return string The path to the theme.
     */
    public function getThemepath()
    {
        return $this->themepath;
    }

    /**
     * Retrieve the path to the theme's images.
     *
     * @return string The path to the theme's images.
     */
    public function getImagepath()
    {
        return $this->imagepath;
    }

    /**
     * Retrieve the path to the theme's language-specific images.
     *
     * @return string The path to the theme's language-specific images.
     */
    public function getImagelangpath()
    {
        return $this->imagelangpath;
    }

    /**
     * Retrieve the path to the theme's stylesheets.
     *
     * @return string The path to the theme's stylesheets.
     */
    public function getStylepath()
    {
        return $this->stylepath;
    }

    /**
     * Retrieve the path to the theme's javascript files.
     *
     * @return string The path to the theme's javascript files.
     */
    public function getScriptpath()
    {
        return $this->scriptpath;
    }

    /**
     * Retrieve the theme configuration.
     *
     * @return array The contents of the theme configuration (or the master configuration).
     */
    public function getThemeconfig()
    {
        return $this->themeconfig;
    }

    /**
     * Indicates whether this page can/should be cached.
     *
     * @return boolean True if this page can/should be cached, otherwise false.
     */
    public function getCachepage()
    {
        return $this->cachepage;
    }

    /**
     * Indicates whether this is a home page or not.
     *
     * @return boolean True if this is a home page (module name is empty), otherwise false.
     */
    public function getHome()
    {
        return $this->home;
    }

    /**
     * Retrieve the current user's uid.
     *
     * @return numeric The current user's uid.
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Retrive the name of the controller function.
     *
     * @return string The name of the controller function.
     */
    public function getFunc()
    {
        return $this->func;
    }

    /**
     * Retrieve the component ID.
     *
     * @return <type>
     */
    public function getComponentid()
    {
        return $this->componentid;
    }

    /**
     * Retrieve the current page's cache ID.
     *
     * @return an MD5 digest of the page's unique identifying string, computed.
     */
    public function getPageid()
    {
        return $this->pageid;
    }

    /**
     * The current page's type.
     *
     * @return string One of 'module', 'admin' or 'home'.
     */
    public function getPagetype()
    {
        return $this->pagetype;
    }

    /**
     * Retrieve the query string for the current page request.
     *
     * @return string The query string for the current request.
     */
    public function getQstring()
    {
        return $this->qstring;
    }

    /**
     * Retrieve the current page's request URI.
     *
     * @return string The current page's request URI.
     */
    public function getRequesturi()
    {
        return $this->requesturi;
    }

    /**
     * Retrieve the permission level.
     *
     * @return mixed The permission level.
     */
    public function getPermlevel()
    {
        return $this->permlevel;
    }

    /**
     * Indicates whether the current user is logged in.
     *
     * @return boolean True if the current user is logged in, false if the current user is anonymous (a guest).
     */
    public function getIsloggedin()
    {
        return $this->isloggedin;
    }

    /**
     * Get Theme instance.
     *
     * @param string  $themeName  Theme name.
     * @param boolean|null $caching  Whether or not to cache (boolean) or use config variable (null).
     * @param string       $cache_id Cache Id.
     *
     * @return Zikula_Theme This instance.
     */
    public static function getInstance($themeName = '', $caching = null, $cache_id = null)
    {
        if (!$themeName) {
            $themeName = UserUtil::getTheme();
        }

        $serviceId = 'zikula.theme';
        $serviceManager = ServiceUtil::getManager();

        if (!$serviceManager->hasService($serviceId)) {
            $themeInstance = new self($serviceManager, $themeName);
            $serviceManager->attachService($serviceId, $themeInstance);
        } else {
            $themeInstance = $serviceManager->getService($serviceId);
        }

        if (!is_null($caching)) {
            $themeInstance->caching = $caching;
        }

        if (!is_null($cache_id)) {
            $themeInstance->cache_id = $cache_id;
        }

        return $themeInstance;
    }

    /**
     * Display the page output.
     *
     * @access private
     * @return void
     */
    public function themefooter()
    {
        // end output buffering and get module output
        $maincontent = ob_get_contents();
        ob_end_clean();

        // add the module wrapper
        if (!$this->system) {
            $module = $this->toplevelmodule ? $this->toplevelmodule : 'homepage';
            $maincontent = '<div id="z-maincontent" class="z-module-' . DataUtil::formatForDisplay(strtolower($module)) . '">' . $maincontent . '</div>';
        }

        $event = new Zikula_Event('theme.prefetch', $this, array(), $maincontent);
        $maincontent = $this->eventManager->notify($event)->getData();

        // Assign the main content area to the template engine
        $this->assign('maincontent', $maincontent);

        // render the page using the correct template
        $output = $this->fetch($this->themeconfig['page'], $this->pageid);

        $event = new Zikula_Event('theme.postfetch', $this, array(), $output);
        echo $this->eventManager->notify($event)->getData();
    }

    /**
     * Display a block.
     *
     * @param array $block Block information.
     *
     * @return string The rendered output.
     */
    public function themesidebox($block)
    {
        // assign the block information
        $this->assign($block);

        $bid = $block['bid'];
        $bkey = $block['bkey'];
        $position = $block['position'];

        // fix block positions - for now....
        if ($position == 'l') {
            $position = 'left';
        }
        if ($position == 'c') {
            $position = 'center';
        }
        if ($position == 'r') {
            $position = 'right';
        }

        // HACK: Save/restore output filters - we don't want to output-filter blocks
        $outputfilters = $this->_plugins['outputfilter'];
        $this->_plugins['outputfilter'] = array();
        // HACK: Save/restore cache settings
        $caching = $this->caching;
        $this->caching = false;

        // determine the correct template and construct the output
        $return = '';
        if (isset($this->themeconfig['blockinstances'][$bid]) && !empty($this->themeconfig['blockinstances'][$bid])) {
            $return .= $this->fetch($this->themeconfig['blockinstances'][$bid]);
        } elseif (isset($this->themeconfig['blocktypes'][$bkey]) && !empty($this->themeconfig['blocktypes'][$bkey])) {
            $return .= $this->fetch($this->themeconfig['blocktypes'][$bkey]);
        } else if (isset($this->themeconfig['blockpositions'][$position]) && !empty($this->themeconfig['blockpositions'][$position])) {
            $return .= $this->fetch($this->themeconfig['blockpositions'][$position]);
        } else if (isset($this->themeconfig['block']) && !empty($this->themeconfig['block'])) {
            $return .= $this->fetch($this->themeconfig['block']);
        } else {
            if (!empty($block['title'])) {
                $return .= '<h4>' . no__($block['title'], array(), true) . ' ' . $block['minbox'] . '</h4>'; // TODO A [the __() doesnt make sense] (drak)
            }
            $return .= $block['content'];
        }

        // HACK: Save/restore output filters
        $this->_plugins['outputfilter'] = $outputfilters;
        // HACK: Save/restore cache settings
        $this->caching = $caching;

        $return = '<div class="z-block z-blockposition-' . DataUtil::formatForDisplay($block['position']) . ' z-bkey-' . DataUtil::formatForDisplay(strtolower($block['bkey'])) . ' z-bid-' . DataUtil::formatForDisplay($block['bid']) . '">' . "\n" . $return . "</div>\n";

        return $return;
    }

    /**
     * Checks which path to use for required template.
     *
     * @param string $template The template name.
     *
     * @return string Template path.
     */
    public function get_template_path($template)
    {
        if (isset($this->templateCache[$template])) {
            return $this->templateCache[$template];
        }

        // get the theme path to templates
        $os_theme = DataUtil::formatForOS($this->directory);
        $ostemplate = DataUtil::formatForOS($template);

        // Define the locations in which we will look for templates
        // (in this order)
        // 1. Master template path
        $masterpath = "themes/$os_theme/templates";
        // 2. The module template path
        $modulepath = "themes/$os_theme/templates/modules";
        // 4. The block template path
        $blockpath = "themes/$os_theme/templates/blocks";

        $search_path = array($masterpath, $modulepath, $blockpath);
        foreach ($search_path as $path) {
            if (is_readable("$path/$ostemplate")) {
                $this->templateCache[$template] = $path;
                return $path;
            }
        }

        // when we arrive here, no path was found
        return false;
    }

    /**
     * Clear CSS/JS combination cached files.
     *
     * Using this function, the user can clear all CSS/JS combination cached
     * files for the system.
     *
     * @return boolean
     */
    public function clear_cssjscombinecache()
    {
        $cache_dir = $this->cache_dir;

        $cached_files = FileUtil::getFiles($cache_dir, true, false, array('php'), null, false);

        foreach ($cached_files as $cf) {
            unlink(realpath($cf));
        }

        // The configuration has been changed, so we clear all caches for this module.
        // clear Zikula_View_Theme cache
        self::clear_all_cache();
        // clear Zikula_View cache
        Zikula_View::getInstance()->clear_all_cache();

        return true;
    }

    /**
     * Define all our plugin directories.
     *
     * @return void
     */
    private function _plugin_dirs()
    {
        // add theme specific plugins directories, if they exist
        $this->addPluginDir('themes/' . $this->directory . '/plugins');

        if (System::isLegacyMode()) {
            // load the usemodules configuration if exists
            $usemod_conf = 'themes/' . $this->directory . '/templates/config/usemodules.txt';
            // load the config file
            if (is_readable($usemod_conf) && is_file($usemod_conf)) {
                $additionalmodules = file($usemod_conf);
                if (is_array($additionalmodules)) {
                    foreach ($additionalmodules as $addmod) {
                        $this->_add_plugins_dir(trim($addmod));
                    }
                }
            }
        }
    }

    /**
     * Assign template vars for base theme paths and other useful variables.
     *
     * @return void
     */
    private function _base_vars()
    {
        // get variables from input
        $module = FormUtil::getPassedValue('module', null, 'GETPOST', FILTER_SANITIZE_STRING);
        $type = FormUtil::getPassedValue('type', null, 'GETPOST', FILTER_SANITIZE_STRING);
        $func = FormUtil::getPassedValue('func', null, 'GETPOST', FILTER_SANITIZE_STRING);

        // set some basic class variables from the PN environemnt
        $this->isloggedin = UserUtil::isLoggedIn();
        $this->uid = UserUtil::getVar('uid');

        // Assign the query string
        $this->qstring = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        // Assign the current script
        $this->requesturi = System::getCurrentUri();

        // assign some basic paths for the engine
        $this->template_dir = $this->themepath . '/templates'; // default directory for templates


        // define the cache id
        if ($this->isloggedin) {
            $this->pageid = $this->name . $this->requesturi . $this->qstring . $this->language . $this->uid;
        } else {
            $this->pageid = $this->name . $this->requesturi . $this->qstring . $this->language;
        }
        // now strip any non-ascii characters from the page id
        $this->pageid = md5($this->pageid);

        $this->themepath = 'themes/' . $this->directory;
        $this->imagepath = $this->themepath . '/images';
        $this->imagelangpath = $this->themepath . '/images/' . $this->language;
        $this->stylepath = $this->themepath . '/style';
        $this->scriptpath = $this->themepath . '/javascript';

        // set vars based on the module structures
        $this->type = !empty($type) ? $type : 'user';
        $this->func = !empty($func) ? $func : 'main';
        $this->home = (empty($module)) ? true : false;

        // identify and assign the page type
        $this->pagetype = 'module';
        if ((stristr($_SERVER['PHP_SELF'], 'admin.php') || strtolower($this->type) == 'admin')) {
            $this->pagetype = 'admin';
        } else if (empty($module)) {
            $this->pagetype = 'home';
        }
        $this->assign('pagetype', $this->pagetype);

        // make the base vars available to all templates
        $this->assign('lang', $this->language);
        $this->assign('loggedin', $this->isloggedin);
        $this->assign('uid', $this->uid);
        $this->assign('themepath', $this->themepath);
        $this->assign('imagepath', $this->imagepath);
        $this->assign('imagelangpath', $this->imagelangpath);
        $this->assign('stylepath', $this->stylepath);
        $this->assign('scriptpath', $this->scriptpath);
        $this->assign('module', $this->toplevelmodule);
        $this->assign('type', $this->type);
        $this->assign('func', $this->func);

        // load the theme variables
        $inifile = $this->themepath . '/templates/config/themevariables.ini';
        $this->load_vars($inifile, 'variables');
    }

    /**
     * Load the base theme configuration.
     *
     * @return void
     */
    public function load_config()
    {
        // load the page configurations
        $pageconfigurations = ModUtil::apiFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $this->name));

        // parse the query string into individual arguments discarding common arguments
        // common arguments are ones that we don't want affecting our url matching or ones that are
        // already considered; These are same args defined as reserved by the MDG.
        if (System::getVar('shorturls')) {
            if (System::getVar('shorturlstype') == 0) {
                // remove the base URI and the entrypoint from the request URI
                $customargs = str_replace(System::getBaseUri(), '', $this->requesturi);
                $entrypoint = System::getVar('entrypoint');
                $customargs = str_replace("/{$entrypoint}/", '/', $customargs);
            }
        } else {
            $queryparts = explode('&', $this->qstring);
            $customargs = '';
            foreach ($queryparts as $querypart) {
                if (!stristr($querypart, 'module=') && !stristr($querypart, 'type=') && !stristr($querypart, 'func=') && !stristr($querypart, 'theme=') && !stristr($querypart, 'authid=') && !stristr($querypart, 'csrftoken=')) {
                    $customargs .= '/' . $querypart;
                }
            }
        }

        // identify and load the correct module configuration
        $this->cachepage = true;
        if ($this->type == 'user' && isset($pageconfigurations['*user'])) {
            $file = $pageconfigurations['*user']['file'];
        } else if ($this->home && isset($pageconfigurations['*home'])) {
            // allow us to match any non-zikula query string
            $homeWithArgs = 'home' . '/' . $this->qstring;
            if (isset($pageconfigurations[$homeWithArgs])) {
                $file = $pageconfigurations[$homeWithArgs]['file'];
            } else {
                $file = $pageconfigurations['*home']['file'];
            }
            $file = $pageconfigurations['*home']['file'];
        } else if (($this->type == 'admin' || $this->type == 'adminplugin') && isset($pageconfigurations['*admin'])) {
            $this->cachepage = false;
            $file = $pageconfigurations['*admin']['file'];
        } else {
            $customargs = $this->toplevelmodule . '/' . $this->type . '/' . $this->func . $customargs;
            // find any page configurations that match in a sub string comparison
            $match = '';
            $matchlength = 0;
            foreach (array_keys($pageconfigurations) as $pageconfiguration) {
                if (stristr($customargs, $pageconfiguration) && $matchlength < strlen($pageconfiguration)) {
                    $match = $pageconfiguration;
                    $matchlength = strlen($match);
                }
            }
            if ((strtolower($this->type) == 'admin') && isset($pageconfigurations['*admin']) && !stristr($match, 'admin')) {
                $match = '*admin';
            }
            if (strtolower($this->type) == 'admin') {
                $this->cachepage = false;
            }
            if (!empty($match)) {
                $file = $pageconfigurations[$match]['file'];
            }
        }

        if (!isset($file)) {
            $file = $pageconfigurations['master']['file'];
        }

        // load the page configuration
        $this->themeconfig = ModUtil::apiFunc('Theme', 'user', 'getpageconfiguration', array('theme' => $this->name, 'filename' => $file));

        // check if we've not got a valid theme configation
        if (!$this->themeconfig) {
            $file = 'master.ini';
            $this->themeconfig = ModUtil::apiFunc('Theme', 'user', 'getpageconfiguration', array('theme' => $this->name, 'filename' => $file));
        }

        if (empty($this->themeconfig['page'])) {
            throw new Exception(__f("Empty 'page' specified in your theme page configuration on file %s.", array($file)));
        }

        // register any filters
        if (isset($this->themeconfig['filters']) && !empty($this->themeconfig['filters'])) {
            if (isset($this->themeconfig['filters']['outputfilters']) && !empty($this->themeconfig['filters']['outputfilters'])) {
                $this->themeconfig['filters']['outputfilters'] = explode(',', $this->themeconfig['filters']['outputfilters']);
                foreach ($this->themeconfig['filters']['outputfilters'] as $filter) {
                    $this->load_filter('output', $filter);
                }
            }
            if (isset($this->themeconfig['filters']['prefilters']) && !empty($this->themeconfig['filters']['prefilters'])) {
                $this->themeconfig['filters']['prefilters'] = explode(',', $this->themeconfig['filters']['prefilters']);
                foreach ($this->themeconfig['filters']['prefilters'] as $filter) {
                    $this->load_filter('pre', $filter);
                }
            }
            if (isset($this->themeconfig['filters']['postfilters']) && !empty($this->themeconfig['filters']['postfilters'])) {
                $this->themeconfig['filters']['postfilters'] = explode(',', $this->themeconfig['filters']['postfilters']);
                foreach ($this->themeconfig['filters']['postfilters'] as $filter) {
                    $this->load_filter('post', $filter);
                }
            }
        }

        // load the palette if set
        if (isset($this->themeconfig['palette'])) {
            $inifile = $this->themepath . '/templates/config/themepalettes.ini';
            $this->load_vars($inifile, $this->themeconfig['palette'], 'palette');
        }

        // assign the palette
        $this->assign('palette', isset($this->themeconfig['palette']) ? $this->themeconfig['palette'] : null);

        $event = new Zikula_Event('theme.load_config', $this);
        $this->eventManager->notify($event);
    }

    /**
     * Assign a set of vars to the theme.
     *
     * @param string $file    Ini file to parse.
     * @param string $section Name of the ini section to include (if null assign all).
     * @param string $assign  Var name to assign in the theme vars.
     *
     * @return boolean
     */
    private function load_vars($file, $section = null, $assign = null)
    {
        if (!file_exists($file) || !($vars = parse_ini_file($file, true))) {
            return false;
        }

        if (!empty($section) && isset($vars[$section])) {
            $this->assign($assign ? array(
                (string)$assign => $vars[$section]) : $vars[$section]);
            return true;
        }

        $this->assign($assign ? array((string)$assign => $vars) : $vars);
    }

    /**
     * Set the config directory for this theme.
     *
     * @return void
     */
    private function _set_configdir()
    {
        // check for a running configuration in the ztemp/Theme_Config directory
        if (is_dir($dir = CacheUtil::getLocalDir() . '/Theme_Config/' . DataUtil::formatForOS($this->name))) {
            $this->config_dir = $dir;
        } else {
            $this->config_dir = $this->themepath . '/templates/config';
        }
    }

    /**
     * Template override handler for 'zikula_view.template_override'.
     *
     * @param Zikula_Event $event Event handler.
     *
     * @return void
     */
    public function _templateOverride(Zikula_Event $event)
    {
        if (array_key_exists($event->data, $this->_overrideMap)) {
            $event->data = $this->_overrideMap[$event->data];
            $event->setNotified();
        }
    }
}
