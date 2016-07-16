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
 * Util class to manage stylesheets and javascript files
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
        $config = [
            'entrypoint' => System::getVar('entrypoint', 'index.php'),
            'baseURL' => System::getBaseUrl(),
            'baseURI' => System::getBaseUri() . '/',
            'ajaxtimeout' => (int)System::getVar('ajaxtimeout', 5000),
            'lang' => ZLanguage::getLanguageCode(),
            'sessionName' => session_name(),
            'uid' => (int)UserUtil::getVar('uid')
        ];

        $polyfill_features = PageUtil::getVar('polyfill_features');
        // merge in features added via twig
        $featuresFromTwig = ServiceUtil::get('zikula_core.common.theme.pagevars')->get('polyfill_features', []);
        $polyfill_features = array_unique(array_merge($polyfill_features, $featuresFromTwig));

        if (!empty($polyfill_features)) {
            $config['polyfillFeatures'] = implode(' ', $polyfill_features);
        }

        $config = DataUtil::formatForDisplay($config);
        $return .= "<script type=\"text/javascript\">/* <![CDATA[ */ \n";
        if (System::isLegacyMode()) {
            $return .= 'document.location.entrypoint="' . $config['entrypoint'] . '";';
            $return .= 'document.location.pnbaseURL="' . $config['baseURL'] . '"; ';
            $return .= 'document.location.ajaxtimeout=' . $config['ajaxtimeout'] . ";\n";
        }
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
     * @param bool   $combine   Should files be combined
     * @param string $cache_dir Path to cache directory
     * @param array  $themeinfo array of info on current theme
     * @param bool   $isAdminController
     *
     * @return array Array with two array containing the files to be embedded into HTML HEAD
     */
    public static function prepareJCSS($combine = false, $cache_dir = null, $themeinfo = [], $isAdminController = false)
    {
        $combine = $combine && is_writable($cache_dir);

        // get page vars
        $javascripts = PageUtil::getVar('javascript');
        $stylesheets = PageUtil::getVar('stylesheet');

        // add html5shiv centrally
        $javascripts[] = 'web/html5shiv/dist/html5shiv.js';

        if (System::isLegacyMode()) {
            $replaceLightbox = false;
            // check if we need to perform ligthbox replacement -- javascript
            if (is_array($javascripts) && !empty($javascripts)) {
                $key = array_search('javascript/ajax/lightbox.js', $javascripts);
                if ($key && !is_readable('javascript/ajax/lightbox.js')) {
                    $javascripts[$key] = 'javascript/helpers/Zikula.ImageViewer.js';
                    $replaceLightbox = true;
                }
            }
            // check if we need to perform ligthbox replacement -- css
            if ($replaceLightbox) {
                $key = array_search('javascript/ajax/lightbox/lightbox.css', $stylesheets);
                if ($key) {
                    $stylesheets[$key] = 'javascript/helpers/ImageViewer/ImageViewer.css';
                }
            }
        }
        $javascripts = self::prepareJavascripts($javascripts);
        // update stylesheets as there might be some additions for js
        $stylesheets = array_merge((array)$stylesheets, (array)PageUtil::getVar('stylesheet'));
        $stylesheets = self::prepareStylesheets($stylesheets, $themeinfo, $isAdminController);
        if ($combine) {
            $javascripts = (array)self::save($javascripts, 'js', $cache_dir);
            $stylesheets = (array)self::save($stylesheets, 'css', $cache_dir);
        }
        $jcss = [
            'stylesheets' => $stylesheets,
            'javascripts' => $javascripts
        ];
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
     * @param array   $stylesheets List of demanded stylesheets
     * @param array   $themeinfo   array of info on current theme
     * @param boolean $isAdminController
     *
     * @return array List of stylesheets
     */
    public static function prepareStylesheets($stylesheets, $themeinfo = [], $isAdminController = false)
    {
        if (ThemeUtil::getVar('noCoreCss', false)) {
            $initStyle = null;
        } else {
            $initStyle = ['style/core.css'];
        }
        // Add generic stylesheet as the first stylesheet.
        $event = new \Zikula\Core\Event\GenericEvent('stylesheet', [], $initStyle);
        $coreStyle = EventUtil::getManager()->dispatch('pageutil.addvar_filter', $event)->getData();
        if (!is_array($stylesheets)) {
            $stylesheets = [];
        }
        // Add legacy stylesheet
        if (System::isLegacyMode('1.4.0')) {
            array_unshift($stylesheets, 'style/legacy.css');
        }
        // Add core stylesheet
        array_unshift($stylesheets, $coreStyle[0]);

        // is theme a 1.4.0 type bundle?
        $theme = null;
        if (!empty($themeinfo)) {
            $theme = ThemeUtil::getTheme($themeinfo['name']);
        }

        // Add bootstrap stylesheet only for 1.4.x type themes or if an admin controller is in use
        if (isset($theme) || $isAdminController) {
            $overrideBootstrapPath = ThemeUtil::getVar('bootstrapPath', ''); // allows for theme override of bootstrap css path
            if (empty($overrideBootstrapPath)) {
                $bootstrapFontAwesomePath = ServiceUtil::getManager()->getParameter('zikula.stylesheet.bootstrap-font-awesome.path');
            } else {
                $bootstrapFontAwesomePath = $overrideBootstrapPath;
            }
            array_unshift($stylesheets, $bootstrapFontAwesomePath);
            // Add font-awesome
            if (!empty($overrideBootstrapPath)) {
                $fontAwesomePath = ServiceUtil::getManager()->getParameter('zikula.stylesheet.fontawesome.min.path');
                array_unshift($stylesheets, $fontAwesomePath);
            }
            $stylesheets = array_unique(array_values($stylesheets));
        }
        $iehack = '<!--[if IE]><link rel="stylesheet" type="text/css" href="style/core_iehacks.css" media="print,projection,screen" /><![endif]-->';
        PageUtil::addVar('header', $iehack);

        return $stylesheets;
    }

    /**
     * Procedure for managing javascript files.
     *
     * Verify demanded files, translate script aliases to real paths, resolve dependencies.
     * Check if gettext is needed and if so add to list file with translations.
     *
     * @param array $javascripts List of javascript files
     *
     * @return array List of javascript files
     */
    public static function prepareJavascripts($javascripts)
    {
        $sm = ServiceUtil::getManager();
        array_unshift($javascripts, 'jquery', $sm->getParameter('zikula.javascript.bootstrap.min.path'), 'javascript/helpers/bootstrap-zikula.js');
        if ($sm->getParameter('env') == 'prod' && file_exists(realpath('web/js/fos_js_routes.js'))) {
            array_unshift($javascripts, 'web/bundles/fosjsrouting/js/router.js', 'web/js/fos_js_routes.js');
        } else {
            $routeScript = $sm->get('router')->generate('fos_js_routing_js', ['callback' => 'fos.Router.setData']);
            array_unshift($javascripts, 'web/bundles/fosjsrouting/js/router.js', $routeScript);
        }
        // first resolve any dependencies
        $javascripts = self::resolveDependencies($javascripts);
        // set proper file paths for aliased scripts
        $coreScripts = self::scriptsMap();
        $styles = [];
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
            $params = [
                'lang' => ZLanguage::getLanguageCode()
            ];
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

            return System::getBaseUri() . '/mo2json.php?' . $params;
        }

        return false;
    }

    /**
     * Method to resolve scripts dependencies basing on scripts map from JCSSUtil: scriptsMap.
     *
     * @param array $javascripts List of javascript files to verify
     * @param array &$resolved   List of already resolved scripts
     *
     * @return array List of javascript files
     */
    private static function resolveDependencies($javascripts, &$resolved = [])
    {
        $coreScripts = self::scriptsMap();
        $withDeps = [];
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
     * @param string $script Script path or alias to verify
     *
     * @return string Script path or alias
     */
    public static function getScriptName($script)
    {
        $script = self::handleLegacy($script);
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
     * Internal procedure for managing legacy script paths.
     *
     * @param string $script Script path to check
     *
     * @return string Verified script path
     */
    private static function handleLegacy($script)
    {
        // Handle legacy references to non-minimised scripts.
        if (strpos($script, 'javascript/livepipe/') === 0) {
            $script = 'livepipe';
        } elseif (strpos($script, 'javascript/ajax/') === 0) {
            switch ($script) {
                case 'javascript/ajax/validation.js':
                    $script = 'validation';
                    break;
                case 'javascript/ajax/unittest.js':
                    $script = 'javascript/ajax/unittest.min.js';
                    break;
                case 'javascript/ajax/prototype.js':
                case 'javascript/ajax/builder.js':
                case 'javascript/ajax/controls.js':
                case 'javascript/ajax/dragdrop.js':
                case 'javascript/ajax/effects.js':
                case 'javascript/ajax/slider.js':
                case 'javascript/ajax/sound.js':
                    $script = 'prototype';
                    break;
            }
            if (strpos($script, 'javascript/ajax/scriptaculous') === 0) {
                $script = 'prototype';
            }
        } elseif (System::isLegacyMode() && (strpos($script, 'system/') === 0 || strpos($script, 'modules/') === 0)) {
            // check for customized javascripts
            $custom = str_replace(['javascript/', 'pnjavascript/'], '', $script);
            $custom = str_replace(['modules', 'system'], 'config/javascript', $custom);
            if (file_exists($custom)) {
                $script = $custom;
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
        $scripts = [
            'prototype' => [
                'path' => 'javascript/ajax/proto_scriptaculous.combined.min.js',
                'require' => ['zikula'],
                'aliases' => ['prototype', 'scriptaculous']
            ],
            'jquery' => [
                'path' => 'web/jquery/jquery.min.js',
                'require' => ['noconflict', 'jquery-migrate']
            ],
            'jquery-ui' => [
                'path' => 'web/jquery-ui/jquery-ui.min.js',
                'require' => ['jquery']
            ],
            'noconflict' => [
                'path' => 'javascript/jquery_config.js'
            ],
            'jquery-migrate' => [
                'path' => 'web/jquery/jquery-migrate.min.js'
            ],
            'livepipe' => [
                'path' => 'javascript/livepipe/livepipe.combined.min.js',
                'require' => ['prototype']
            ],
            'zikula' => [
                'path' => 'javascript/helpers/Zikula.js',
                'require' => ['prototype'],
                'aliases' => ['javascript/ajax/ajax.js']
            ],
            'zikula.ui' => [
                'path' => 'javascript/helpers/Zikula.UI.js',
                'require' => ['prototype', 'livepipe', 'zikula'],
                'styles' => ['javascript/helpers/Zikula.UI.css'],
                'gettext' => true
            ],
            'zikula.imageviewer' => [
                'path' => 'javascript/helpers/Zikula.ImageViewer.js',
                'require' => ['prototype', 'zikula'],
                'styles' => ['javascript/helpers/ImageViewer/ImageViewer.css'],
                'aliases' => ['imageviewer', 'lightbox'],
                'gettext' => true
            ],
            'zikula.itemlist' => [
                'path' => 'javascript/helpers/Zikula.itemlist.js',
                'require' => ['prototype', 'zikula']
            ],
            'zikula.tree' => [
                'path' => 'javascript/helpers/Zikula.Tree.js',
                'require' => ['prototype', 'zikula'],
                'styles' => ['javascript/helpers/Tree/Tree.css']
            ],
            'validation' => [
                'path' => 'javascript/ajax/validation.min.js',
                'require' => ['prototype']
            ],
            'polyfill' => [
                'path' => 'javascript/js-webshim/minified/polyfiller.js',
                'require' => ['jquery', 'polyfill.init']
            ],
            'polyfill.init' => [
                'path' => 'javascript/js-webshim/minified/polyfiller.init.js'
            ]
        ];
        if (System::isDevelopmentMode()) {
            $prototypeUncompressed = [
                'prototype' => [
                    'path' => 'javascript/ajax/original_uncompressed/prototype.js',
                    'require' => ['zikula', 'builder', 'controls', 'dragdrop', 'effects', 'slider', 'sound'],
                    'aliases' => ['prototype', 'scriptaculous']
                ],
                'scriptaculous' => [
                    'path' => 'javascript/ajax/original_uncompressed/prototype.js',
                    'require' => ['prototype']
                ],
                'effects' => [
                    'path' => 'javascript/ajax/original_uncompressed/effects.js'
                ],
                'builder' => [
                    'path' => 'javascript/ajax/original_uncompressed/builder.js'
                ],
                'controls' => [
                    'path' => 'javascript/ajax/original_uncompressed/controls.js'
                ],
                'dragdrop' => [
                    'path' => 'javascript/ajax/original_uncompressed/dragdrop.js'
                ],
                'slider' => [
                    'path' => 'javascript/ajax/original_uncompressed/slider.js'
                ],
                'sound' => [
                    'path' => 'javascript/ajax/original_uncompressed/sound.js'
                ]
            ];
            $livepipeUncompressed = [
                'livepipe' => [
                    'path' => 'javascript/livepipe/original_uncompressed/livepipe.js',
                    'require' => ['prototype', 'contextmenu', 'cookie', 'event_behavior', 'hotkey', 'progressbar', 'rating', 'resizable', 'scrollbar', 'selection', 'selectmultiple', 'tabs', 'textarea', 'window']
                ],
                'contextmenu' => [
                    'path' => 'javascript/livepipe/original_uncompressed/contextmenu.js'
                ],
                'cookie' => [
                    'path' => 'javascript/livepipe/original_uncompressed/cookie.js'
                ],
                'event_behavior' => [
                    'path' => 'javascript/livepipe/original_uncompressed/event_behavior.js'
                ],
                'hotkey' => [
                    'path' => 'javascript/livepipe/original_uncompressed/hotkey.js'
                ],
                'progressbar' => [
                    'path' => 'javascript/livepipe/original_uncompressed/progressbar.js'
                ],
                'rating' => [
                    'path' => 'javascript/livepipe/original_uncompressed/rating.js'
                ],
                'resizable' => [
                    'path' => 'javascript/livepipe/original_uncompressed/resizable.js'
                ],
                'scrollbar' => [
                    'path' => 'javascript/livepipe/original_uncompressed/scrollbar.js'
                ],
                'selection' => [
                    'path' => 'javascript/livepipe/original_uncompressed/selection.js'
                ],
                'selectmultiple' => [
                    'path' => 'javascript/livepipe/original_uncompressed/selectmultiple.js'
                ],
                'tabs' => [
                    'path' => 'javascript/livepipe/original_uncompressed/tabs.js'
                ],
                'textarea' => [
                    'path' => 'javascript/livepipe/original_uncompressed/textarea.js'
                ],
                'window' => [
                    'path' => 'javascript/livepipe/original_uncompressed/window.js'
                ]
            ];
            $jQueryUncompressed = [
                'jquery' => [
                    'path' => 'web/jquery/jquery.js',
                    'require' => ['noconflict', 'jquery-migrate']
                ],
                'noconflict' => [
                    'path' => 'javascript/jquery_config.js'
                ],
                'jquery-migrate' => [
                    'path' => 'web/jquery/jquery-migrate.min.js'
                ]
            ];
            $jQueryUiUncompressed = [
                'jquery-ui' => [
                    'path' => 'web/jquery-ui/jquery-ui.js',
                    'require' => ['jquery']
                ]
            ];
            $polyfillUncompressed = [
                'polyfill' => [
                    'path' => 'javascript/js-webshim/dev/polyfiller.js',
                    'require' => ['jquery', 'polyfill.init']
                ],
                'polyfill.init' => [
                    'path' => 'javascript/js-webshim/dev/polyfiller.init.js'
                ]
            ];

            $scripts = array_merge($prototypeUncompressed, $jQueryUncompressed, $jQueryUiUncompressed, $livepipeUncompressed, array_slice($scripts, 5), $polyfillUncompressed);
        }

        return $scripts;
    }

    /**
     * Save combined pagevars.
     *
     * @param array  $files     Files
     * @param string $ext       Extention
     * @param string $cache_dir Cache directory
     *
     * @return array Array of file with combined pagevars file and remote files
     */
    private static function save($files, $ext, $cache_dir)
    {
        $themevars = ModUtil::getVar('ZikulaThemeModule');
        $lifetime = $themevars['cssjscombine_lifetime'];
        $hash = md5(serialize($files) . UserUtil::getTheme());

        $cachedFile = "{$cache_dir}/{$hash}_{$ext}.php";
        $cachedFileUri = "{$hash}_{$ext}.php";

        if (is_readable($cachedFile) && (($lifetime == -1) || (filemtime($cachedFile) + $lifetime) > time())) {
            return System::getBaseUri() . '/jcss.php?f=' . $cachedFileUri;
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
        $includedFiles = [];
        $outputFiles = [];
        $contents = [];
        $dest = fopen($cachedFile, 'w');
        foreach ($files as $file) {
            if (empty($file)) {
                continue;
            }

            // skip remote files from combining
            if (is_file($file)) {
                self::readfile($contents, $file, $ext);
                $includedFiles[] = $file;
            } else {
                $outputFiles[] = $file;
            }
        }

        array_unshift($contents, "/* --- Combined file written: " . DateUtil::getDateTime() . " */\n\n");
        array_unshift($contents, "/* --- Combined files:\n" . implode("\n", $includedFiles) . "\n*/\n\n");

        $contents = implode('', $contents);
        // optional minify
        if ($themevars['cssjsminify'] && $ext == 'css') {
            // Remove comments.
            $contents = trim(preg_replace('/\/\*.*?\*\//s', '', $contents));
            // Compress whitespace.
            $contents = preg_replace('/\s+/', ' ', $contents);
            // Additional whitespace optimisation -- spaces around certain tokens is not required by CSS
            $contents = preg_replace('/\s*(;|\{|\}|:|,)\s*/', '\1', $contents);
        }

        global $ZConfig;
        $signingKey = md5(serialize($ZConfig['DBInfo']['databases']['default']));
        $signature = md5($contents . $ctype . $lifetime . $themevars['cssjscompress'] . $signingKey);
        $data = [
            'contents' => $contents,
            'ctype' => $ctype,
            'lifetime' => $lifetime,
            'gz' => $themevars['cssjscompress'],
            'signature' => $signature
        ];
        fwrite($dest, serialize($data));
        fclose($dest);
        $combined = System::getBaseUri() . '/jcss.php?f=' . $cachedFileUri;
        array_unshift($outputFiles, $combined);

        return $outputFiles;
    }

    /**
     * Reads an file and add its contents to the $contents array.
     *
     * This function includes the content of all "@import" statements (recursive).
     *
     * @param array  &$contents Array to save content to
     * @param string $file      Path to file
     * @param string $ext       Can be 'css' or 'js'
     *
     * @return void
     */
    private static function readfile(&$contents, $file, $ext)
    {
        if (!file_exists($file)) {
            return;
        }
        $source = fopen($file, 'r');
        if (!$source) {
            return;
        }

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
                $newLine = '';
                // parse line char by char
                for ($i = 0; $i < $lineParse_length; $i++) {
                    $char = $lineParse[$i];
                    $nextchar = $i < ($lineParse_length - 1) ? $lineParse[$i + 1] : '';
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
                                if ($url[0] == '"' | $url[0] == "'") {
                                    $url = substr($url, 1, strlen($url) - 2);
                                }
                                // fix url
                                $url = dirname($file) . '/' . $url;
                                if (!$wasCommentHack) {
                                    // clear buffer
                                    $contents[] = $newLine;
                                    $newLine = '';
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
                                if ($url[0] == '"' | $url[0] == "'") {
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
                                $newLine = '';
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
                    $newLine = self::cssFixPath($newLine, explode('/', dirname($file)));
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

    /**
     * Fix paths in CSS files.
     *
     * @param string $line     CSS file line
     * @param string $filepath Path to original file
     *
     * @return string
     */
    private static function cssFixPath($line, $filepath)
    {
        $regexpurl = '/url\([\'"]?([\.\/]*)(.*?)[\'"]?\)/i';
        if (false === strpos($line, 'url')) {
            return $line;
        }

        preg_match_all($regexpurl, $line, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if ((strpos($match[1], '/') !== 0) && (substr($match[2], 0, 7) != 'http://') && (substr($match[2], 0, 8) != 'https://')) {
                $depth = substr_count($match[1], '../') * -1;
                $path = $depth < 0 ? array_slice($filepath, 0, $depth) : $filepath;
                $path = implode('/', $path);
                $path = !empty($path) ? $path . '/' : '';
                $line = str_replace($match[0], "url('{$path}{$match[2]}')", $line);
            }
        }

        return $line;
    }
}
