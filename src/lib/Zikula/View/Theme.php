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
     * Theme name.
     *
     * @var string
     */
    public $name;

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


    // base user information

    /**
     * User id.
     *
     * @var integer
     */
    public $uid;

    /**
     * Whether or not the user is logged in.
     *
     * @var boolean.
     */
    public $isloggedin;


    // publics to identify our page

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
    public function __construct(Zikula_ServiceManager $serviceManager, $themeName)
    {
        // store our theme information
        $this->themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themeName));
        // prevents any case mismatch
        $themeName = $this->themeinfo['name'];

        foreach (array('name', 'directory', 'version', 'state', 'xhtml') as $key) {
            $this->$key = $this->themeinfo[$key];
        }

        parent::__construct($serviceManager);

        if ($this->themeinfo['i18n']) {
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

        $event = new Zikula_Event('theme.preinit', $this);
        $this->eventManager->notify($event);

        // change some base settings from our parent class
        // template compilation
        $this->compile_dir   = CacheUtil::getLocalDir('Theme_compiled');
        $this->compile_check = ModUtil::getVar('Theme', 'compile_check');
        $this->force_compile = ModUtil::getVar('Theme', 'force_compile');
        // template caching
        $this->cache_dir = CacheUtil::getLocalDir('Theme_cache');
        $this->caching   = (int)ModUtil::getVar('Theme', 'enablecache');
        //if ($this->caching) {
        //    $this->cache_modified_check = true;
        //}

        // if caching and is not an admin controller
        if ($this->caching && strpos($this->type, 'admin') !== 0) {
            $modulesnocache = explode(',', ModUtil::getVar('Theme', 'modulesnocache'));
            if (in_array($this->toplevelmodule, $modulesnocache)) {
                $this->caching = Zikula_View::CACHE_DISABLED;
            }
        } else {
            $this->caching = Zikula_View::CACHE_DISABLED;
        }

        // halt caching for write operations to prevent strange things happening
        if (isset($_POST) && count($_POST) != 0) {
            $this->caching = Zikula_View::CACHE_DISABLED;
        }
        // and also for GET operations with csrftoken/authkey
        if (isset($_GET['csrftoken']) || isset($_GET['authkey'])) {
            $this->caching = Zikula_View::CACHE_DISABLED;
        }

        $this->cache_lifetime = ModUtil::getVar('Theme', 'cache_lifetime');

        // assign all our base template variables
        $this->_base_vars();

        // define the plugin directories
        $this->_plugin_dirs();

        // load the theme configuration
        $this->load_config();

        // check for cached output
        // turn on caching, check for cached output and then disable caching
        // to prevent blocks from being cached
        if ($this->caching && $this->is_cached($this->themeconfig['page'], $this->cache_id)) {
            $this->display($this->themeconfig['page'], $this->cache_id);
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
     * Get Theme instance.
     *
     * @param string       $themeName  Theme name.
     * @param integer|null $caching  Whether or not to cache (Zikula_View::CACHE_*) or use config variable (null).
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
        if (!$this->themeinfo['system'] && (bool)$this->themeconfig['modulewrapper']) {
            $maincontent = '<div id="z-maincontent" class="'.($this->homepage ? 'z-homepage ' : '').'z-module-' . DataUtil::formatForDisplay(strtolower($this->toplevelmodule)) . '">' . $maincontent . '</div>';
        }

        $event = new Zikula_Event('theme.prefetch', $this, array(), $maincontent);
        $maincontent = $this->eventManager->notify($event)->getData();

        // Assign the main content area to the template engine
        $this->assign('maincontent', $maincontent);

        // render the page using the correct template
        $output = $this->fetch($this->themeconfig['page'], $this->cache_id);

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

        $bid      = $block['bid'];
        $bkey     = strtolower($block['bkey']);
        $position = strtolower($block['position']);

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
        $this->caching = Zikula_View::CACHE_DISABLED;

        $return = '';
        // determine the correct template and construct the output
        if (isset($this->themeconfig['blockinstances'][$bid]) && !empty($this->themeconfig['blockinstances'][$bid])) {
            $return .= $this->fetch($this->themeconfig['blockinstances'][$bid]);

        } elseif (isset($this->themeconfig['blocktypes'][$bkey]) && !empty($this->themeconfig['blocktypes'][$bkey])) {
            $return .= $this->fetch($this->themeconfig['blocktypes'][$bkey]);

        } elseif (isset($this->themeconfig['blockpositions'][$position]) && !empty($this->themeconfig['blockpositions'][$position])) {
            $return .= $this->fetch($this->themeconfig['blockpositions'][$position]);

        } elseif (!empty($this->themeconfig['block'])) {
            $return .= $this->fetch($this->themeconfig['block']);

        } else {
            if (!empty($block['title'])) {
                $return .= '<h4>' . DataUtil::formatForDisplay($block['title']) . ' ' . $block['minbox'] . '</h4>';
            }
            $return .= $block['content'];
        }

        // HACK: Save/restore output filters
        $this->_plugins['outputfilter'] = $outputfilters;
        // HACK: Save/restore cache settings
        $this->caching = $caching;

        if ((bool)$this->themeconfig['blockwrapper']) {
            $return = '<div class="z-block z-blockposition-' . DataUtil::formatForDisplay($block['position']) . ' z-bkey-' . DataUtil::formatForDisplay(strtolower($block['bkey'])) . ' z-bid-' . DataUtil::formatForDisplay($block['bid']) . '">' . "\n" . $return . "</div>\n";
        }

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
        $themeDir = DataUtil::formatForOS($this->directory);
        $osTemplate = DataUtil::formatForOS($template);

        $relativePath = "themes/$themeDir/templates";
        $templateFile = "$relativePath/$osTemplate";
        $override = self::getTemplateOverride($templateFile);
        if ($override === false) {
            if (!System::isLegacyMode()) {
                if (is_readable($templateFile)) {
                    $this->templateCache[$template] = $relativePath;
                    return $relativePath;
                } else {
                    return false;
                }
            }
        } else {
            if (is_readable($override)) {
                $path = substr($override, 0, strrpos($override, $osTemplate));
                $this->templateCache[$template] = $path;
                return $path;
            }
        }

        // The rest of this code is scheduled for removal from 1.4.0 - drak

        // Define the locations in which we will look for templates
        // (in this order)
        // 1. Master template path
        $masterPath = "themes/$themeDir/templates";
        // 2. The module template path
        $modulePath = "themes/$themeDir/templates/modules";
        // 4. The block template path
        $blockPath = "themes/$themeDir/templates/blocks";

        $search_path = array($masterPath, $modulePath, $blockPath);
        foreach ($search_path as $path) {
            if (is_readable("$path/$osTemplate")) {
                $this->templateCache[$template] = $path;
                return $path;
            }
        }

        // when we arrive here, no path was found
        return false;
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
     * Get a concrete filename for automagically created content.
     *
     * Generates a filename path like: Theme / auto_id [/ source_dir / filename-l{lang}.ext]
     * the final part gets generated only if $auto_source is specified.
     *
     * @param string $path        The base path.
     * @param string $auto_source The file name (optional).
     * @param string $auto_id     The ID (optional).
     *
     * @return string The concrete path and file name to the content.
     */
    function _get_auto_filename($path, $auto_source = null, $auto_id = null)
    {
        // enables a flags to detect when is treating compiled templates
        $tocompile = ($path == $this->compile_dir) ? true : false;

        // format auto_source for os to make sure that id does not contain 'ugly' characters
        $auto_source = DataUtil::formatForOS($auto_source);

        // add the Theme name as first folder
        $path .= '/' . $this->directory;

        // the last folder is the cache_id if set
        $path .= !empty($auto_id) ? '/' . $auto_id : '';

        // takes in account the source subdirectory
        $path .= strpos($auto_source, '/') !== false ? '/' . dirname($auto_source) : '';

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
            // if we are compiling we do not include cache variables
            if (!$tocompile) {
                // add the variable stuff only if $auto_source is present
                // to allow a easy flush cache for all the languages (if needed)
                $path .= '-l';
                if (System::getVar('multilingual') == 1) {
                    $path .= $this->language;
                }
                // end with a suffix convention of filename--Themename-lang.ext
                $path .= ($extension ? ".$extension" : '');
            }
        }

        return $path;
    }

    /**
     * Assign template vars for base theme paths and other useful variables.
     *
     * @return void
     */
    private function _base_vars()
    {
        // identify the page type
        $this->pagetype = 'module';
        if ((stristr(System::serverGetVar('PHP_SELF'), 'admin.php') || strtolower($this->type) == 'admin')) {
            $this->pagetype = 'admin';
        } elseif (empty($module)) {
            $this->pagetype = 'home';
        }

        // set some basic class variables from Zikula
        $this->isloggedin = UserUtil::isLoggedIn();
        $this->uid = UserUtil::getVar('uid');

        // assign the query string
        $this->qstring = System::serverGetVar('QUERY_STRING', '');

        // assign the current script
        $this->requesturi = System::getCurrentUri();

        // define the cache_id if not set yet
        if ($this->caching && !$this->cache_id) {
            // module / type / function / uid_X|guest / customargs|homepage/startpageargs
            $this->cache_id = $this->toplevelmodule . '/' . $this->type . '/' . $this->func
                            . '/' . UserUtil::getUidCacheString()
                            . (!$this->homepage ? $this->_get_customargs() : '/homepage/' . str_replace(',', '/', System::getVar('startargs')));
        }

        // assign some basic paths for the engine
        $this->template_dir  = $this->themepath . '/templates'; // default directory for templates

        $this->themepath     = 'themes/' . $this->directory;
        $this->imagepath     = $this->themepath . '/images';
        $this->imagelangpath = $this->themepath . '/images/' . $this->language;
        $this->stylepath     = $this->themepath . '/style';
        $this->scriptpath    = $this->themepath . '/javascript';

        // make the base vars available to all templates
        $this->assign('module', $this->toplevelmodule)
             ->assign('uid', $this->uid)
             ->assign('loggedin', $this->isloggedin)
             ->assign('pagetype', $this->pagetype)
             ->assign('themepath', $this->themepath)
             ->assign('imagepath', $this->imagepath)
             ->assign('imagelangpath', $this->imagelangpath)
             ->assign('stylepath', $this->stylepath)
             ->assign('scriptpath', $this->scriptpath);

        // load the theme variables
        $variables = ModUtil::apiFunc('Theme', 'user', 'getvariables', array('theme' => $this->name));
        $this->assign($variables['variables']);
    }

    /**
     * Normalizes the current page parameters.
     *
     * Used on the page cache_id and the pageassignments keys.
     *
     * @return string Custom arguments string.
     */
    private function _get_customargs()
    {
        static $customargs;

        if (isset($customargs)) {
            return $customargs;
        }

        // parse the query string into individual arguments discarding common arguments
        // common arguments are ones that we don't want affecting our url matching or ones that are
        // already considered; These are same args defined as reserved by the MDG.
        $customargs = '';
        if ($this->pagetype != 'admin' && System::getVar('shorturls')) {
            // remove the base URI and the entrypoint from the request URI
            $customargs = str_replace(System::getBaseUri(), '', $this->requesturi);
            $entrypoint = System::getVar('entrypoint');
            $customargs = str_replace("/{$entrypoint}/", '/', $customargs);
            $customargs = ($customargs == '/') ? '' : $customargs;
        } else {
            $queryparts = explode('&', $this->qstring);
            foreach ($queryparts as $querypart) {
                if (!stristr($querypart, 'module=') && !stristr($querypart, 'type=') && !stristr($querypart, 'func=') && !stristr($querypart, 'theme=') && !stristr($querypart, 'authid=') && !stristr($querypart, 'csrftoken=')) {
                    $customargs .= '/' . $querypart;
                }
            }
        }

        return $customargs;
    }

    /**
     * Load the base theme configuration.
     *
     * Can be used into the system but if the themeconfig['page'] is changed the cache
     * gets canceled because there will be no match between initial cache_id and default page.
     * Try to change only theme variables that changes the behaviour of the output.
     *
     * @return void
     */
    public function load_config()
    {
        if (!$this->themeconfig) {
            // load the page configurations
            $pageconfigurations = ModUtil::apiFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $this->name));

            // identify and load the correct module configuration

            // checks homepage match
            if ($this->homepage && isset($pageconfigurations['*home'])) {
                // allow us to match any non-zikula query string
                $homeWithArgs = 'home' . '/' . $this->qstring;
                if (isset($pageconfigurations[$homeWithArgs])) {
                    $file = $pageconfigurations[$homeWithArgs]['file'];
                } else {
                    $file = $pageconfigurations['*home']['file'];
                }
                $file = $pageconfigurations['*home']['file'];

            // identify a type match
            } elseif (isset($pageconfigurations['*'.$this->type])) {
                $file = $pageconfigurations['*'.$this->type]['file'];

            // identify an admin-like type
            } else if (strpos($this->type, 'admin') === 0 && isset($pageconfigurations['*admin'])) {
                $file = $pageconfigurations['*admin']['file'];

            // search for arguments match
            } else {
                $customargs = $this->toplevelmodule . '/' . $this->type . '/' . $this->func . $this->_get_customargs();
                // find any page configurations that match in a sub string comparison
                $match = '';
                $matchlength = 0;
                foreach (array_keys($pageconfigurations) as $pageconfiguration) {
                    if (stristr($customargs, $pageconfiguration) && $matchlength < strlen($pageconfiguration)) {
                        $match = $pageconfiguration;
                        $matchlength = strlen($match);
                        if (isset($pageconfigurations[$pageconfiguration]['important']) && $pageconfigurations[$pageconfiguration]['important']) {
                            break;
                        }
                    }
                }
                if (!empty($match)) {
                    $file = $pageconfigurations[$match]['file'];
                }
            }

            if (empty($file)) {
                $file = $pageconfigurations['master']['file'];
            }

            // load the page configuration
            $this->themeconfig = ModUtil::apiFunc('Theme', 'user', 'getpageconfiguration', array('theme' => $this->name, 'filename' => $file));

            // check if we've not got a valid theme configation
            if (!$this->themeconfig['page']) {
                $file = 'master.ini';
                $this->themeconfig = ModUtil::apiFunc('Theme', 'user', 'getpageconfiguration', array('theme' => $this->name, 'filename' => $file));
            }
        }

        if (empty($this->themeconfig['page'])) {
            throw new Exception(__f("Empty 'page' specified in your theme page configuration on file %s.", array($file)));
        }

        // register any filters
        if (!empty($this->themeconfig['filters'])) {
            // check for output filters
            if (isset($this->themeconfig['filters']['outputfilters']) && !empty($this->themeconfig['filters']['outputfilters'])) {
                $filters = $this->themeconfig['filters']['outputfilters'];
                $filters = !is_array($filters) ? explode(',', $filters) : $filters;
                foreach ($filters as $filter) {
                    $this->load_filter('output', $filter);
                }
            }
            // check for pre filters
            if (isset($this->themeconfig['filters']['prefilters']) && !empty($this->themeconfig['filters']['prefilters'])) {
                $filters = $this->themeconfig['filters']['prefilters'];
                $filters = !is_array($filters) ? explode(',', $filters) : $filters;
                foreach ($filters as $filter) {
                    $this->load_filter('pre', $filter);
                }
            }
            // check for post filters
            if (isset($this->themeconfig['filters']['postfilters']) && !empty($this->themeconfig['filters']['postfilters'])) {
                $filters = $this->themeconfig['filters']['postfilters'];
                $filters = !is_array($filters) ? explode(',', $filters) : $filters;
                foreach ($filters as $filter) {
                    $this->load_filter('post', $filter);
                }
            }
        }

        // load the pageconfiguration variables
        if (!empty($this->themeconfig['variables'])) {
            $this->assign($this->themeconfig['variables']);
        }

        // load the palette if set
        if (!empty($this->themeconfig['palette'])) {
            $palette = ModUtil::apiFunc('Theme', 'user', 'getpalette', array('theme' => $this->name, 'palette' => $this->themeconfig['palette']));
            $this->assign('palette', $palette);
        }

        $event = new Zikula_Event('theme.load_config', $this);
        $this->eventManager->notify($event);
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
            $event->stop();
        }
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

        // The configuration has been changed, so we clear all caches.
        // clear Zikula_View_Theme cache
        self::clear_all_cache();
        // clear Zikula_View cache (really needed?)
        Zikula_View::getInstance()->clear_all_cache();

        return true;
    }

    /**
     * Clears the Theme configuration located on the temporary directory.
     *
     * @return boolean True on success, false otherwise.
     */
    public function clear_theme_config()
    {
        $configdir = CacheUtil::getLocalDir('Theme_Config');

        return $this->clear_folder($configdir, null, null, null);
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
    public function getThemePath()
    {
        return $this->themepath;
    }

    /**
     * Retrieve the path to the theme's images.
     *
     * @return string The path to the theme's images.
     */
    public function getImagePath()
    {
        return $this->imagepath;
    }

    /**
     * Retrieve the path to the theme's language-specific images.
     *
     * @return string The path to the theme's language-specific images.
     */
    public function getImageLangPath()
    {
        return $this->imagelangpath;
    }

    /**
     * Retrieve the path to the theme's stylesheets.
     *
     * @return string The path to the theme's stylesheets.
     */
    public function getStylePath()
    {
        return $this->stylepath;
    }

    /**
     * Retrieve the path to the theme's javascript files.
     *
     * @return string The path to the theme's javascript files.
     */
    public function getScriptPath()
    {
        return $this->scriptpath;
    }

    /**
     * Retrieve the theme configuration.
     *
     * @return array The contents of the theme configuration (or the master configuration).
     */
    public function getThemeConfig()
    {
        return $this->themeconfig;
    }

    /**
     * Indicates whether this is a home page or not.
     *
     * @return boolean True if this is a home page (module name is empty), otherwise false.
     */
    public function isHomePage()
    {
        return $this->homepage;
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
     * Indicates whether the current user is logged in.
     *
     * @return boolean True if the current user is logged in, false if the current user is anonymous (a guest).
     */
    public function getIsLoggedIn()
    {
        return $this->isloggedin;
    }

    /**
     * The current page's type.
     *
     * @return string One of 'module', 'admin' or 'home'.
     */
    public function getPageType()
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
    public function getRequestUri()
    {
        return $this->requesturi;
    }

    /**
     * Set the current cache ID.
     *
     * @param string $cache_id Cache ID to set.
     *
     * @return void
     */
    public function setCacheId($cache_id)
    {
        $this->cache_id = $cache_id;
    }

    /**
     * Set the theme configuration.
     *
     * @param array $themeconfig Theme configuration array to set.
     *
     * @return void
     */
    public function setThemeConfig($themeconfig)
    {
        if ($themeconfig && is_array($themeconfig)) {
            $this->themeconfig = $themeconfig;
        }
    }
}
