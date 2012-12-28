<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Util class to manage stylesheets and javascript files
 *
 */
class JCSSUtil
{

    /**
     * Generate a configuration for javascript and return script tag to embed in HTML HEAD.
     *
     * @return string HTML code with script tag
     */
    public static function getJSConfig()
    {
        $return = '';
        $config = array(
            'entrypoint' => System::getVar('entrypoint', 'index.php'),
            'baseURL' => System::getBaseUrl(),
            'baseURI' => System::getBaseUri() . '/',
            'ajaxtimeout' => (int)System::getVar('ajaxtimeout', 5000),
            'lang' => ZLanguage::getLanguageCode(),
            'sessionName' => session_name(),
            'request' => self::decodeRequest(),
            'isDevelopmentMode' => System::isDevelopmentMode()
        );

        $config = DataUtil::formatForDisplay($config);
        $return .= "<script type=\"text/javascript\">/* <![CDATA[ */ \n";
        $return .= "if (typeof(Zikula) == 'undefined') {var Zikula = {};}\n";
        $return .= "Zikula.Config = " . json_encode($config) . "\n";
        $return .= ' /* ]]> */</script>' . "\n";

        return $return;
    }

    /**
     * The main procedure for managing stylesheets and javascript files.
     *
     * Gets demanded files from PageUtil variables, check them and resolve dependencies.
     * Returns an array with two arrays, containing list of js and css files
     * ready to embedded in the HTML HEAD.
     *
     * @param bool   $combine   Should files be combined.
     * @param string $cache_dir Path to cache directory.
     *
     * @return array Array with two array containing the files to be embedded into HTML HEAD
     */
    public static function prepareJCSS($combine=false, $cache_dir=null)
    {
        $combine = $combine && is_writable($cache_dir);
        $jcss = array();
        // get page vars
        $javascripts = PageUtil::getVar('javascript');
        $stylesheets = PageUtil::getVar('stylesheet');

        $javascripts = self::prepareJavascripts($javascripts, $combine);
        // update stylesheets as there might be some additions for js
        $stylesheets = array_merge((array)$stylesheets, (array)PageUtil::getVar('stylesheet'));
        $stylesheets = self::prepareStylesheets($stylesheets, $combine);

        if ($combine) {
            $javascripts = (array)self::save($javascripts, 'js', $cache_dir);
            $stylesheets = (array)self::save($stylesheets, 'css', $cache_dir);
        }
        $jcss = array(
            'stylesheets' => $stylesheets,
            'javascripts' => $javascripts
        );
        // some core js libs require js gettext - ensure that it will be loaded
        $jsgettext = self::getJSGettext();
        if (!empty($jsgettext)) {
            array_unshift($jcss['javascripts'], $jsgettext);
        }

        return $jcss;
    }

    /**
     * Procedure for managinig stylesheets.
     *
     * @param array $stylesheets List of demanded stylesheets.
     *
     * @return array List of stylesheets
     */
    public static function prepareStylesheets($stylesheets)
    {                    
        if (ThemeUtil::getVar('noCoreCss', false)) {
            $initStyle = null;
        } else {
            $initStyle = array('style/core.css');
        }
        
        // Add generic stylesheet as the first stylesheet.
        $event = new \Zikula\Core\Event\GenericEvent('stylesheet', array(), $initStyle);
        $coreStyle = EventUtil::getManager()->dispatch('pageutil.addvar_filter', $event)->getData();
        if (is_array($stylesheets)) {
            array_unshift($stylesheets, $coreStyle[0]);
        } else {
            $stylesheets = array($coreStyle[0]);
        }
        $stylesheets = array_unique(array_values($stylesheets));

        $iehack = '<!--[if IE]><link rel="stylesheet" type="text/css" href="style/core_iehacks.css" media="print,projection,screen" /><![endif]-->';
        PageUtil::addVar('header', $iehack);

        return $stylesheets;
    }

    /**
     * Procedure for managinig javascript files.
     *
     * Verify demanded files, translate script aliases to real paths, resolve dependencies.
     * Check if gettext is needed and if so add to list file with translations.
     *
     * @param array $javascripts List of javascript files.
     *
     * @return array List of javascript files
     */
    public static function prepareJavascripts($javascripts)
    {
        // first resolve any dependencies
        $javascripts = self::resolveDependencies($javascripts);
        // set proper file paths for aliased scripts
        $coreScripts = self::scriptsMap();
        $styles = array();
        $gettext = false;
        foreach ($javascripts as $i => $script) {
            if (array_key_exists($script, $coreScripts)) {
                $javascripts[$i] = $coreScripts[$script]['path'];
                if (isset($coreScripts[$script]['styles'])) {
                    $styles = array_merge($styles, (array)$coreScripts[$script]['styles']);
                }
                if (isset($coreScripts[$script]['gettext'])) {
                    $gettext = $gettext || $coreScripts[$script]['gettext'];
                }
            }
        }
        if ($gettext) {
            PageUtil::addVar('jsgettext', 'zikula_js');
        }
        if (!empty($styles)) {
            PageUtil::addVar('stylesheet', $styles);
        }

        return $javascripts;
    }

    /**
     * Gets from PageUtil requests for gettext and generates url for file with translations.
     *
     * @return string Url to file with translations
     */
    public static function getJSGettext()
    {
        $jsgettext = PageUtil::getVar('jsgettext');
        if (!empty($jsgettext)) {
            $params = array(
                    'lang' => ZLanguage::getLanguageCode()
            );
            foreach ($jsgettext as $entry) {
                $vars = explode(':', $entry);
                if (isset($vars[0])) {
                    $domain = $vars[0];
                }
                if (isset($vars[1])) {
                    $module = $vars[1];
                }
                if (isset($domain) && !empty($domain)) {
                    $params[$domain] = (isset($module) && !empty($module)) ? $module : $domain;
                }
            }
            $params = http_build_query($params, '', '&');

            return 'mo2json.php?' . $params;
        }

        return false;
    }

    /**
     * Method to resolve scripts dependencies basing on scripts map from JCSSUtil: scriptsMap.
     *
     * @param array $javascripts List of javascript files to verify.
     * @param array &$resolved   List of already resolved scripts.
     *
     * @return array List of javascript files
     */
    private static function resolveDependencies($javascripts, &$resolved = array())
    {
        $coreScripts = self::scriptsMap();
        $withDeps = array();
        foreach ($javascripts as $script) {
            $script = self::getScriptName($script);
            if (isset($coreScripts[$script]) && isset($coreScripts[$script]['require']) && !in_array($script, $resolved)) {
                $resolved[] = $script;
                $required = $coreScripts[$script]['require'];
                $r = self::resolveDependencies($required, $resolved);
                $withDeps = array_merge($withDeps, (array)$r);
            }
            $withDeps[] = $script;
        }
        // set proper order
        $coreNames = array_keys($coreScripts);
        $usedCore = array_intersect($coreNames, $withDeps);
        $ordered = array_unique(array_merge($usedCore, $withDeps));

        return $ordered;
    }

    /**
     * Checks the given script name (alias or path).
     *
     * If this is the core script is returning it's alias.
     * This method also hanldes all legacy for script paths.
     *
     * @param string $script Script path or alias to verify.
     *
     * @return string Script path or alias
     */
    public static function getScriptName($script)
    {
        $coreScripts = self::scriptsMap();
        $_script = strtolower($script);
        if (array_key_exists($_script, $coreScripts)) {
            return $_script;
        }
        foreach ($coreScripts as $name => $meta) {
            if (isset($meta['aliases']) && in_array($_script, (array)$meta['aliases'])) {
                return $name;
            } elseif (isset($meta['path']) && $meta['path'] == $script) {
                return $name;
            }
        }

        return $script;
    }

    /**
     * An array with a list of core scripts.
     *
     * For each script can be defined:
     * - path: the true path to the file
     * - require: other scripts to be loaded along with the file (aliases for core, paths for other)
     * - aliases: aliases used for this script
     * - styles: information about additional files (styles) that should be loaded along with the script
     * - gettext: if script requires a translations
     *
     * When System::isDevelopmentMode precombined versions of scripts (prototype, livepipe and jquery)
     * are replaced by original, uncompressed files
     *
     * @return array List of core scripts
     */
    public static function scriptsMap()
    {
        $scriptsMap = array(
            'json2' => array(
                'production' => array(
                    'path' => 'javascript/polyfills/json2/json2.min.js',
                ),
                'development' => array(
                    'path' => 'javascript/polyfills/json2/json2.js',
                )
            ),
            'storage' => array(
                'production' => array(
                    'path' => 'javascript/polyfills/storage/storage.min.js',
                ),
                'development' => array(
                    'path' => 'javascript/polyfills/storage/storage.js',
                )
            ),
            'jquery' => array(
                'production' => array(
                    'path' => 'javascript/jquery/jquery-1.8.3.min.js',
                    'require' => array('jquery.noconflict'),
                ),
                'development' => array(
                    'path' => 'javascript/jquery/jquery-1.8.3.js',
                )
            ),
            'jquery.noconflict' => array(
                'path' => 'javascript/jquery/noconflict.js',
            ),
            'jquery-ui' => array(
                'production' => array(
                    'path' => 'javascript/jquery-ui/jquery-ui-1.9.2.custom.min.js',
                    'require' => array('jquery'),
                    'styles' => array('javascript/jquery-ui/themes/smoothness/jquery-ui-1.9.2.custom.min.css'),
                ),
                'development' => array(
                    'path' => 'javascript/jquery-ui/jquery-ui-1.9.2.custom.js',
                )
            ),
            'underscore' => array(
                'production' => array(
                    'path' => 'javascript/underscore/underscore.min.js',
                ),
                'development' => array(
                    'path' => 'javascript/underscore/underscore.js',
                )
            ),
            'underscore.string' => array(
                'production' => array(
                    'path' => 'javascript/underscore/underscore.string.min.js',
                    'require' => array('underscore'),
                ),
                'development' => array(
                    'path' => 'javascript/underscore/underscore.string.js',
                )
            ),
            'modernizr' => array(
                'production' => array(
                    'path' => 'javascript/modernizr/modernizr.min.js',
                ),
                'development' => array(
                    'path' => 'javascript/modernizr/modernizr.js',
                )
            ),
            'colorbox' => array(
                'production' => array(
                    'path' => 'javascript/plugins/colorbox/jquery.colorbox-min.js',
                    'aliases' => array('zikula.imageviewer', 'imageviewer', 'lightbox'),
                    'require' => array('jquery', 'javascript/plugins/colorbox/boot.js'),
                    'styles' => array('javascript/plugins/colorbox/colorbox.css'),
                ),
                'development' => array(
                    'path' => 'javascript/plugins/colorbox/jquery.colorbox.js',
                )
            ),
            'contextmenu' => array(
                'production' => array(
                    'path' => 'javascript/plugins/jQuery-contextMenu/jquery.contextMenu.min.js',
                    'require' => array('jquery'),
                    'styles' => array('javascript/plugins/jQuery-contextMenu/jquery.contextMenu.css'),
                ),
                'development' => array(
                    'path' => 'javascript/plugins/jQuery-contextMenu/jquery.contextMenu.js',
                )
            ),
            'zikula' => array(
                'production' => array(
                    'path' => 'javascript/zikula/zikula.min.js',
                    'require' => array('jquery', 'underscore', 'underscore.string', 'modernizr'),
                    'gettext' => true
                ),
                'development' => array(
                    'path' => 'javascript/zikula/zikula.js',
                    'require' => array(
                        'jquery', 'underscore', 'underscore.string', 'modernizr',
                        'javascript/zikula/lang.js',
                        'javascript/zikula/class.js',
                        'javascript/zikula/util.services.js',
                        'javascript/zikula/core.js',
                        'javascript/zikula/factory.js',
                        'javascript/zikula/util.cookie.js',
                        'javascript/zikula/util.gettext.js',
                        'javascript/zikula/dom.js',
                        'javascript/zikula/ajax.js',
                        'javascript/zikula/boot.js'
                    ),
                )
            ),
            'zikula.ui' => array(
                'production' => array(
                    'path' => 'javascript/zikula-plugins/zikula.ui.min.js',
                    'require' => array('zikula', 'jquery-ui'),
                ),
                'development' => array(
                    'path' => 'javascript/zikula-plugins/zikula.ui.js',
                )
            ),
        );

        $isDevelopmentMode = System::isDevelopmentMode();
        $scripts = array_map(function($script) use($isDevelopmentMode) {
            $production = isset($script['production']) ? $script['production'] : $script;
            if ($isDevelopmentMode) {
                // merge into script setup possible development changes
                $dev = isset($script['development']) ? $script['development'] : array();
                $production = array_merge($production, $dev);
            }
            return $production;
        }, $scriptsMap);

        return $scripts;
    }

    /**
     * Save combined pagevars.
     *
     * @param array  $files     Files.
     * @param string $ext       Extention.
     * @param string $cache_dir Cache directory.
     *
     * @return array Array of file with combined pagevars file and remote files
     */
    private static function save($files, $ext, $cache_dir)
    {
        $themevars = ModUtil::getVar('Theme');

        $lifetime = $themevars['cssjscombine_lifetime'];
        $hash = md5(serialize($files) . UserUtil::getTheme());

        $cachedFile = "{$cache_dir}/{$hash}_{$ext}.php";
        $cachedFileUri = "{$hash}_{$ext}.php";
        if (is_readable($cachedFile) && (filemtime($cachedFile) + $lifetime) > time()) {
            return "jcss.php?f=$cachedFileUri";
        }

        switch ($ext) {
            case 'css':
                $ctype = 'text/css';
                break;
            case 'js':
                $ctype = 'text/javascript';
                break;
            default:
                $ctype = 'text/plain';
                break;
        }

        $outputFiles = array();
        $contents = array();
        $dest = fopen($cachedFile, 'w');

        $contents[] = "/* --- Combined file written: " . DateUtil::getDateTime() . " */\n\n";
        $contents[] = "/* --- Combined files:\n" . implode("\n", $files) . "\n*/\n\n";
        foreach ($files as $file) {
            if (!empty($file)) {
                // skip remote files from combining
                if (is_file($file)) {
                    self::readfile($contents, $file, $ext);
                } else {
                    $outputFiles[] = $file;
                }
            }
        }

        $contents = implode('', $contents);

        // optional minify
        if ($themevars['cssjsminify']) {
            if ($ext == 'css') {
                // Remove comments.
                $contents = trim(preg_replace('/\/\*.*?\*\//s', '', $contents));
                // Compress whitespace.
                $contents = preg_replace('/\s+/', ' ', $contents);
                // Additional whitespace optimisation -- spaces around certain tokens is not required by CSS
                $contents = preg_replace('/\s*(;|\{|\}|:|,)\s*/', '\1', $contents);
            }
        }

        global $ZConfig;
        $signingKey = md5(serialize($ZConfig['DBInfo']['databases']['default']));
        $signature = md5($contents . $ctype . $lifetime . $themevars['cssjscompress'] . $signingKey);
        $data = array('contents' => $contents, 'ctype' => $ctype, 'lifetime' => $lifetime, 'gz' => $themevars['cssjscompress'], 'signature' => $signature);
        fwrite($dest, serialize($data));
        fclose($dest);

        $combined = "jcss.php?f=$cachedFileUri";
        array_unshift($outputFiles, $combined);

        return $outputFiles;
    }

    /**
     * Reads an file and add its contents to the $contents array.
     *
     * This function includes the content of all @import statements (recursive).
     *
     * @param array  &$contents Array to save content to.
     * @param string $file Path to file.
     * @param string $ext  Can be 'css' or 'js'.
     *
     * @return void
     */
    private static function readfile(&$contents, $file, $ext)
    {
        if (!file_exists($file)) {
            return;
        }

        $source = fopen($file, 'r');
        if ($source) {
            $filepath = explode('/', dirname($file));
            $contents[] = "/* --- Source file: {$file} */\n\n";
            $inMultilineComment = false;
            $importsAllowd = true;
            $wasCommentHack = false;

            while (!feof($source)) {
                if ($ext == 'css') {
                    $line = fgets($source, 4096);
                    $lineParse = trim($line);
                    $lineParse_length = mb_strlen($lineParse, 'UTF-8');
                    $newLine = "";

                    // parse line char by char
                    for ($i = 0; $i < $lineParse_length; $i++) {
                        $char = $lineParse{$i};
                        $nextchar = $i < ($lineParse_length - 1) ? $lineParse{$i + 1} : "";

                        if (!$inMultilineComment && $char == '/' && $nextchar == '*') {
                            // a multiline comment starts here
                            $inMultilineComment = true;
                            $wasCommentHack = false;
                            $newLine .= $char . $nextchar;
                            $i++;
                        } elseif ($inMultilineComment && $char == '*' && $nextchar == '/') {
                            // a multiline comment stops here
                            $inMultilineComment = false;
                            $newLine .= $char . $nextchar;
                            if (substr($lineParse, $i - 3, 8) == '/*\*//*/') {
                                $wasCommentHack = true;
                                $i += 3; // move to end of hack process hack as it where
                                $newLine .= '/*/'; // fix hack comment because we lost some chars with $i += 3
                            }
                            $i++;
                        } elseif ($importsAllowd && $char == '@' && substr($lineParse, $i, 7) == '@import') {
                            // an @import starts here
                            $lineParseRest = trim(substr($lineParse, $i + 7));
                            if (strtolower(substr($lineParseRest, 0, 3)) == 'url') {
                                // the @import uses url to specify the path
                                $posEnd = strpos($lineParse, ';', $i);
                                $charsEnd = substr($lineParse, $posEnd - 1, 2);
                                if ($charsEnd == ');') {
                                    // used url() without media
                                    $start = strpos($lineParseRest, '(') + 1;
                                    $end = strpos($lineParseRest, ')');
                                    $url = substr($lineParseRest, $start, $end - $start);
                                    if ($url{0} == '"' | $url{0} == "'") {
                                        $url = substr($url, 1, strlen($url) - 2);
                                    }

                                    // fix url
                                    $url = dirname($file) . '/' . $url;

                                    if (!$wasCommentHack) {
                                        // clear buffer
                                        $contents[] = $newLine;
                                        $newLine = "";
                                        // process include
                                        self::readfile($contents, $url, $ext);
                                    } else {
                                        $newLine .= '@import url("' . $url . '");';
                                    }

                                    // skip @import statement
                                    $i += $posEnd - $i;
                                } else {
                                    // @import contains media type so we can't include its contents.
                                    // We need to fix the url instead.

                                    $start = strpos($lineParseRest, '(') + 1;
                                    $end = strpos($lineParseRest, ')');
                                    $url = substr($lineParseRest, $start, $end - $start);
                                    if ($url{0} == '"' | $url{0} == "'") {
                                        $url = substr($url, 1, strlen($url) - 2);
                                    }

                                    // fix url
                                    $url = dirname($file) . '/' . $url;

                                    // readd @import with fixed url
                                    $newLine .= '@import url("' . $url . '")' . substr($lineParseRest, $end + 1, strpos($lineParseRest, ';') - $end - 1) . ';';

                                    // skip @import statement
                                    $i += $posEnd - $i;
                                }
                            } elseif (substr($lineParseRest, 0, 1) == '"' || substr($lineParseRest, 0, 1) == '\'') {
                                // the @import uses an normal string to specify the path
                                $posEnd = strpos($lineParseRest, ';');
                                $url = substr($lineParseRest, 1, $posEnd - 2);
                                $posEnd = strpos($lineParse, ';', $i);

                                // fix url
                                $url = dirname($file) . '/' . $url;

                                if (!$wasCommentHack) {
                                    // clear buffer
                                    $contents[] = $newLine;
                                    $newLine = "";
                                    // process include
                                    self::readfile($contents, $url, $ext);
                                } else {
                                    $newLine .= '@import url("' . $url . '");';
                                }

                                // skip @import statement
                                $i += $posEnd - $i;
                            }
                        } elseif (!$inMultilineComment && $char != ' ' && $char != "\n" && $char != "\r\n" && $char != "\r") {
                            // css rule found -> stop processing of @import statements
                            $importsAllowd = false;
                            $newLine .= $char;
                        } else {
                            $newLine .= $char;
                        }
                    }

                    // fix other paths after @import processing
                    if (!$importsAllowd) {
                        $newLine = self::cssfixPath($newLine, explode('/', dirname($file)));
                    }

                    $contents[] = $newLine;
                } else {
                    $contents[] = fgets($source, 4096);
                }
            }
            fclose($source);
            if ($ext == 'js') {
                $contents[] = "\n;\n";
            } else {
                $contents[] = "\n\n";
            }
        }
    }

    /**
     * Fix paths in CSS files.
     *
     * @param string $line     CSS file line.
     * @param string $filepath Path to original file.
     *
     * @return string
     */
    private static function cssfixPath($line, $filepath)
    {
        $regexpurl = '/url\([\'"]?([\.\/]*)(.*?)[\'"]?\)/i';
        if (strpos($line, 'url') !== false) {
            preg_match_all($regexpurl, $line, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                if (strpos($match[1], '/') !== 0) {
                    $depth = substr_count($match[1], '../') * -1;
                    $path = $depth < 0 ? array_slice($filepath, 0, $depth) : $filepath;
                    $path = implode('/', $path);
                    $path = !empty($path) ? $path . '/' : '';
                    $line = str_replace($match[0], "url('{$path}{$match[2]}')", $line);
                }
            }
        }

        return $line;
    }

    private static function decodeRequest()
    {
        // fixme - does not work with short urls (but $_GET/$_REQUEST are filled)
        $query = ServiceUtil::get('request')->query->all();
        $homepage = false;

        // process the homepage
        if (!isset($query['module']) || empty($query['module'])) {
            $homepage = true;

            // set the start parameters
            $query['module'] = System::getVar('startpage');
            $query['type'] = System::getVar('starttype');
            $query['func'] = System::getVar('startfunc');
            $args = explode(',', System::getVar('startargs'));

            foreach ($args as $arg) {
                if (!empty($arg)) {
                    $argument = explode('=', $arg);
                    $query[$argument[0]] = $argument[1];
                }
            }
        }

        // get module information
        $modinfo = ModUtil::getInfoFromName($query['module']);

        if ($modinfo) {
            $query['module'] = $modinfo['name'];
        }

        // normalize module, type, func and generate view-id
        $query['module'] = mb_strtolower($query['module']);
        $query['type'] = mb_strtolower($query['type']);
        $query['func'] = mb_strtolower($query['func']);

        $viewId = 'homepage';
        if (!empty($query['module'])) {
            $viewId = "{$query['module']}-{$query['type']}-{$query['func']}";
        }

        $request = array(
            'query' => $query,
            'homepage' => $homepage,
            'view-id' => $viewId
        );

        return $request;
    }
}
