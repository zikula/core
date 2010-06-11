<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Render
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Theme
 *
 * @package Zikula_Core
 * @subpackage Zikula
 */
class Theme extends Renderer
{
    private static $themeInstance;

    // base theme info
    public $id; // id
    public $name; // name
    public $displayname; // display name
    public $description; // description
    public $regid; // registration id
    public $type; // type
    public $directory; // directory
    public $version; // version
    public $official; // official
    public $author; // author(s)
    public $contact; // contact(s)
    public $admin; // admin capable
    public $user; // user capable
    public $system; // system capable
    public $state; // state
    public $credits; // credits
    public $changelog; // changelog
    public $help; // help
    public $license; // license
    public $xhtml; // xhtml capable


    // base theme properties
    public $themepath; // Theme base path
    public $imagepath; // Theme image path
    public $imagelangpath; // Theme image path
    public $stylepath; // Theme stylesheet path
    public $scriptpath; // Theme stylesheet path


    public $themeconfig;
    public $cachepage;
    public $home;
    public $uid;
    public $func;

    // publics to identify our page
    public $componentid;
    public $pageid;
    public $pagetype;
    public $qstring;
    public $requesturi;
    public $permlevel;
    public $isloggedin;

    // gettext domain of the theme
    public $themeDomain;

    public function __construct($theme, $usefilters = true)
    {
        // store our theme directory
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($theme));
        foreach ($themeinfo as $key => $value) {
            $this->$key = $value;
        }

        parent::__construct();

        if ($themeinfo['i18n']) {
            ZLanguage::bindThemeDomain($this->name);
            // property for {gt} template plugin to detect language domain
            $this->themeDomain = ZLanguage::getThemeDomain($this->name);
        } else {
            $this->themeDomain = 'zikula';
        }

        // change some base settings from our parent class
        // template compilation
        $this->compile_dir = CacheUtil::getLocalDir() . '/Theme_compiled';
        $this->compile_check = ModUtil::getVar('Theme', 'compile_check');
        $this->force_compile = ModUtil::getVar('Theme', 'force_compile');
        $this->compile_id = $theme;
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

        // load the theme configuration
        $this->_load_config();

        // check for cached output
        // turn on caching, check for cached output and then disable caching
        // to prevent blocks from being cached
        if ($this->caching && $this->is_cached($this->themeconfig['page'], $this->pageid)) {
            $this->display($this->themeconfig['page'], $this->pageid);
            System::shutdown();
        }

        if ($usefilters) {
            // register page vars output filter
            $pagevarfilter = (ModUtil::getVar('Theme', 'cssjscombine', false) ? 'pagevars' : 'pagevars_notcombined');
            $this->load_filter('output', $pagevarfilter);

            // register short urls output filter
            if (System::getVar('shorturls')) {
                $this->load_filter('output', 'shorturls');
            }

            // register trim whitespace output filter if requried
            if (ModUtil::getVar('Theme', 'trimwhitespace')) {
                $this->load_filter('output', 'trimwhitespace');
            }
        }

        // This event sends $this as the subject so you can modify as required:
        // e.g.  $event->getSubject()->load_filter('output', 'multihook');
        $event = new Zikula_Event('theme.init', $this, array('theme' => $theme, 'usefilters' => $usefilters, 'themeinfo' => $themeinfo));
        EventUtil::notify($event);

        // Start the output buffering to capture module output
        ob_start();
    }

    public static function getInstance($theme = null, $usefilters = true)
    {
        if (!isset($theme)) {
            $theme = UserUtil::getTheme();
        }

        $serviceId = 'zikula.theme';
        $sm = ServiceUtil::getManager();

        if (!$sm->hasService($serviceId)) {
            $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($theme));
            $themeInstance = new self($themeinfo['name'], $usefilters);
            $sm->attachService($serviceId, $themeInstance);
        } else {
            $themeInstance = $sm->getService($serviceId);
        }

        return $themeInstance;
    }

    /**
     * display the page output
     * @access                private
     * @return                none
     */
    public function themefooter()
    {
        // end output buffering and get module output
        $maincontent = ob_get_contents();
        ob_end_clean();

        $event = new Zikula_Event('theme.prefooter', $this, array(), $maincontent);
        EventUtil::notify($event);
        $maincontent = $event->getData();

        // add the module wrapper
        if (!$this->system && (!isset($this->themeconfig['modulewrapper']) || $this->themeconfig['modulewrapper'])) {
            $maincontent = '<div id="z-maincontent" class="z-module-' . DataUtil::formatForDisplay($this->toplevelmodule) . '">' . $maincontent . '</div>';
        }

        // Assign the main content area to the template engine
        $this->assign_by_ref('maincontent', $maincontent);

        // render the page using the correct template
        $this->display($this->themeconfig['page'], $this->pageid);
    }

    /**
     * display a block
     *
     * @param postion
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

        if (!isset($this->themeconfig['blockwrapper']) || $this->themeconfig['blockwrapper']) {
            $return = '<div class="z-block z-blockposition-' . DataUtil::formatForDisplay($block['position']) . ' z-bkey-' . DataUtil::formatForDisplay($block['bkey']) . ' z-bid-' . DataUtil::formatForDisplay($block['bid']) . '">' . "\n" . $return . "</div>\n";
        }

        return $return;
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

        // get the theme path to templates
        $os_theme = DataUtil::formatForOS($this->directory);

        // Define the locations in which we will look for templates
        // (in this order)
        // 1. Master template path
        $masterpath = "themes/$os_theme/templates";
        // 2. The module template path
        $modulepath = "themes/$os_theme/templates/modules";
        // 4. The block template path
        $blockpath = "themes/$os_theme/templates/blocks";

        $ostemplate = DataUtil::formatForOS($template);

        $search_path = array($masterpath, $modulepath, $blockpath);
        foreach ($search_path as $path) {
            if (is_readable("$path/$ostemplate")) {
                $cache[$template] = $path;
                return $path;
            }
        }

        // when we arrive here, no path was found
        return false;
    }

    /**
     * Clear CSS/JS combination cached files
     *
     * Using this function, the user can clear all CSS/JS combination cached
     * files for the system.
     */
    public function clear_cssjscombinecache()
    {
        // Clear the directory
        $files = scandir($this->cache_dir);
        foreach ($files as $file) {
            if (preg_match('#[a-f0-0]*_(js|css)\.php$#', $file)) {
                unlink($this->cache_dir . '/' . $file);
            }
        }

        // The configuration has been changed, so we clear all caches for this module.
        self::clear_all_cache();
        $Renderer = Renderer::getInstance();
        $Renderer->clear_all_cache();

        return true;
    }

    /**
     * define all our plugin directories
     *
     * @access private
     */
    private function _plugin_dirs()
    {
        // add theme specific plugins directories, if they exist
        $themepath = 'themes/' . $this->directory . '/plugins';
        if (file_exists($themepath)) {
            array_push($this->plugins_dir, $themepath);
        }

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

    /**
     * assign template vars for base theme paths and other useful variables
     *
     * @access private
     */
    private function _base_vars()
    {
        // get variables from input
        $module = FormUtil::getPassedValue('module', null, 'GETPOST');
        $type = FormUtil::getPassedValue('type', null, 'GETPOST');
        $func = FormUtil::getPassedValue('func', null, 'GETPOST');

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
        $this->home = (empty($module) && empty($name)) ? true : false;

        // identify and assign the page type
        $this->pagetype = 'module';
        if ((stristr($_SERVER['PHP_SELF'], 'admin.php') || strtolower($this->type) == 'admin')) {
            $this->pagetype = 'admin';
        } else if (empty($name) && empty($module)) {
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
    }

    /**
     * load the base theme configuration
     *
     * @access private
     */
    private function _load_config()
    {
        // set the config directory
        $this->_set_configdir();

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
            } else {
                // remove the base URI, extension, entrypoint, module name and, if it exists, the function name from the request URI
                $extension = System::getVar('shorturlsext');
                $qstring = str_replace(array(
                    System::getBaseUri() . '/',
                    ".{$extension}",
                    $this->type,
                    $this->func,
                    'module-' . $this->toplevelmodule,
                    'module-' . $this->module[$this->toplevelmodule]['url']), '', $this->requesturi);
                $qstring = trim($qstring, '-');
                $argsarray = explode('-', $qstring);
                $argsarray = array_chunk($argsarray, 2);
                foreach ($argsarray as $argarray) {
                    $customargs[] = implode('=', $argarray);
                }
                $customargs = '/' . implode('/', $customargs);
            }
        } else {
            $queryparts = explode('&', $this->qstring);
            $customargs = '';
            foreach ($queryparts as $querypart) {
                if (!stristr($querypart, 'module=') && !stristr($querypart, 'name=') && !stristr($querypart, 'type=') && !stristr($querypart, 'func=') && !stristr($querypart, 'theme=') && !stristr($querypart, 'authid=')) {
                    $customargs .= '/' . $querypart;
                }
            }
        }

        // identify and load the correct module configuration
        $this->cachepage = true;
        if (stristr($_SERVER['PHP_SELF'], 'user') && isset($pageconfigurations['*user'])) {
            $file = $pageconfigurations['*user']['file'];
        } else if (!stristr($_SERVER['PHP_SELF'], 'user') && !stristr($_SERVER['PHP_SELF'], 'admin.php') && $this->home && isset($pageconfigurations['*home'])) {
            $file = $pageconfigurations['*home']['file'];
        } else if (stristr($_SERVER['PHP_SELF'], 'admin.php') && isset($pageconfigurations['*admin'])) {
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
            $this->themeconfig = ModUtil::apiFunc('Theme', 'user', 'getpageconfiguration', array( 'theme' => $this->name, 'filename' => 'master.ini'));
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

        // load the theme settings
        $inifile = $this->themepath . '/templates/config/themevariables.ini';
        $this->load_vars($inifile, 'variables');

        // load the palette
        if (isset($this->themeconfig['palette'])) {
            $inifile = $this->themepath . '/templates/config/themepalettes.ini';
            $this->load_vars($inifile, $this->themeconfig['palette'], 'palette');
        }

        // assign the palette
        $this->assign('palette', isset($this->themeconfig['palette']) ? $this->themeconfig['palette'] : null);
    }

    /**
     * assign a set of vars to the theme
     *
     * @access private
     * @param string $file     ini file to parse
     * @param string $section  name of the ini section to include (if null assign all)
     * @param string $assign  var name to assign in the theme vars
     */
    private function load_vars($file, $section = null, $assign = null)
    {
        if (!file_exists($file) || !($vars = DataUtil::parseIniFile($file))) {
            return false;
        }

        if (!empty($section) && isset($vars[$section])) {
            $this->assign($assign ? array(
                (string) $assign => $vars[$section]) : $vars[$section]);
            return true;
        }

        $this->assign($assign ? array((string) $assign => $vars) : $vars);
    }

    /**
     * set the config directory for this theme
     *
     * @access private
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
}
