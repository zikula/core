<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Render
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty outputfilter to add page variables and additional header global into page header
 *
 * By default this output filter places page variable output immediately prior to the closing
 * head tag (</head>). The output can, optionally, be placed anywhere in the template by adding
 * the HTML comment <!-- pagevars --> to the page template. Note that this must always be in
 * the header for the output to function correctly.
 *
 * @param string $source  Output source.
 * @param Smarty &$smarty Reference to Smarty instance.
 * 
 * @return string
 */
function smarty_outputfilter_pagevars($source, &$smarty)
{
    $return = '';

    // We need to make sure that the content of the oldstyle additional_header array does
    // lead to duplicate headers if the same output is also defined in the PageVars.
    // This is complicated as the format differs:
    // PageVar for javascript: path/to/javascript.js
    // additional_header: <script type="text/javascript" src="path/to/javascript"></script> or different
    // We go the easy way and check if the value of a pagevar is part of the additional_header value (which
    // it is in the example above)
    // This will be done for stylesheet and javascript pagevars only right now. We can extend this if necessary.
    global $additional_header;

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));

    // get any javascript page vars
    $javascripts = PageUtil::getVar('javascript');

    // get any stylesheet page vars
    $stylesheets = PageUtil::getVar('stylesheet');
    // Add generic stylesheet as the first stylesheet.
    if (is_array($stylesheets)) {
        array_unshift($stylesheets, 'styles/core.css');
    } else {
        $stylesheets = array('styles/core.css');
    }

    // check if we need to perform ligthbox replacement -- javascript
    if (is_array($javascripts) && !empty($javascripts)) {
        $key = array_search('javascript/ajax/lightbox.js', $javascripts);
        if ($key && !is_readable('javascript/ajax/lightbox.js')) {
            $javascripts[$key] = 'javascript/helpers/Zikula.ImageViewer.js';
            $replaceLightbox = true;
        }
    }

    // check if we need to perform ligthbox replacement -- css
    if (isset($replaceLightbox) && $replaceLightbox === true) {
        $key = array_search('javascript/ajax/lightbox/lightbox.css', $stylesheets);
        if ($key) {
            $stylesheets[$key] = 'javascript/helpers/ImageViewer/ImageViewer.css';
        }
    }

    // create the html tag link for the stylesheet file
    foreach ($stylesheets as $s => $stylesheet) {
        if (empty($stylesheet)) {
            unset($stylesheets[$s]);
            continue;
        }
        // check if the stylesheets is in the additional_header array
        _smarty_outputfilter_pagevars_clean_additional_header($additional_header, $stylesheet);
    }
    $stylesheets = array_unique(array_values($stylesheets));
    // Perform a check on import and expand those for packing later on
    $stylesheetFile = _smarty_outputfilter_pagevars_save($stylesheets,'css',$smarty->cache_dir);
    if ($themeinfo['xhtml']) {
        $return .= '<link rel="stylesheet" href="'.DataUtil::formatForDisplay($stylesheetFile).'" type="text/css" />'."\n";
    } else {
        $return .= '<link rel="stylesheet" href="'.DataUtil::formatForDisplay($stylesheetFile).'" type="text/css">'."\n";
    }

    if (is_array($javascripts) && !empty($javascripts)) {
        // some check for prototype/ajax/scriptaculous
        $enableSplice = false;
        $javascriptLinksToCheck = array('javascript/ajax/prototype.js',
                                        'javascript/ajax/scriptaculous.js',
                                        'javascript/ajax/scriptaculous.js?load=builder',
                                        'javascript/ajax/scriptaculous.js?load=effects',
                                        'javascript/ajax/scriptaculous.js?load=dragdrop',
                                        'javascript/ajax/scriptaculous.js?load=controls',
                                        'javascript/ajax/scriptaculous.js?load=slider',
                                        'javascript/ajax/scriptaculous.js?load=sound',
                                        'javascript/ajax/ajax.js');

        $javascriptNewLinks     = array('javascript/ajax/prototype.js',
                                        'javascript/ajax/scriptaculous.js',
                                        'javascript/ajax/builder.js',
                                        'javascript/ajax/effects.js',
                                        'javascript/ajax/dragdrop.js',
                                        'javascript/ajax/controls.js',
                                        'javascript/ajax/slider.js',
                                        'javascript/ajax/sound.js',
                                        'javascript/ajax/ajax.js');

        foreach ($javascripts as $key => $currentJS) {
            if (in_array($currentJS, $javascriptLinksToCheck)) {
                unset($javascripts[$key]);
                $enableSplice = true;
            }
        }

        if ($enableSplice) {
            array_splice($javascripts,0,0,$javascriptNewLinks);
        }

        // Ugly but necessary inline javascript for now: Some javascripts, eg. the lightbox, need to know the path to the system and
        // the entrypoint as well (which can be configured in the settings) otherwise they may fail in case of short urls being
        // enabled. We will now add some inline javascript to extend the DOM:
        //
        // document.location.entrypoint: will be set to what is configured to be the entrypoint
        // document.location.pnbaseURL: will point to the result of System::getBaseUrl();
        //
        // todo: make his more unobtrusive, but how? Dynamic javascript creation might be a performance problem. Any idea here
        // is highly appreciated! [landseer]
        //
        $return .= '<script type="text/javascript">/* <![CDATA[ */ document.location.entrypoint="' . System::getVar('entrypoint', 'index.php') . '"; document.location.pnbaseURL="' . System::getBaseUrl(). '"; ';
        // check if the ajaxtimeout is configured and not the defsult value of 5000, in this case add the value in the inline js for refernce in ajax.js
        $ajaxtimeout = System::getVar('ajaxtimeout', 5000);
        if ($ajaxtimeout != 5000) {
            $return .= 'document.location.ajaxtimeout=' . (int)DataUtil::formatForDisplay($ajaxtimeout). ';';
        }
        $return .= ' /* ]]> */</script>' . "\n";
        foreach ($javascripts as $j => $javascript) {
            if (empty($javascript)) {
                unset($javascripts[$j]);
                continue;
            }
            // check if the javascript is in the additional_header array
            _smarty_outputfilter_pagevars_clean_additional_header($additional_header, $javascript);
        }
        $javascripts = array_unique(array_values($javascripts));
        $javascriptFile = _smarty_outputfilter_pagevars_save($javascripts, 'js', $smarty->cache_dir);
        $return .= '<script type="text/javascript" src="'.DataUtil::formatForDisplay($javascriptFile).'"></script>'."\n";
    }

    $rawtext = PageUtil::getVar('rawtext');
    if (is_array($rawtext) && !empty($rawtext)) {
        $return .= implode("\n", $rawtext) . "\n";
    }

    // implode the remaining additional header global to a string
    if (isset($additional_header) && count($additional_header)>0) {
        $return .= @implode("\n", $additional_header) . "\n";
    }

    // if we've got some page vars to add the header wrap the output in
    // suitable identifiying comments when in development mode
    $return = trim($return);
    if (!empty($return) && System::getVar('development') != 0) {
        $return = "<!-- zikula pagevars -->\n" . $return . "\n<!-- /zikula pagevars -->";
    }

    // get any body page vars
    $bodyvars = PageUtil::getVar('body');
    if (!empty($bodyvars)) {
        $bodyattribs = '<body ' . @implode(' ', $bodyvars) . '>';
        $source = str_replace('<body>', $bodyattribs, $source);
    }

    // get any footer page vars
    $footervars = PageUtil::getVar('footer');
    if (!empty($footervars)) {
        $footersource = @implode("\n", $footervars)."\n</body>";
        $source = str_replace('</body>', $footersource, $source);
    }

    // replace the string in the template source
    if (stristr($source, '<!-- pagevars -->')) {
        $source = str_replace('<!-- pagevars -->', $return, $source);
    } else {
        $source = str_replace('</head>', $return."\n</head>", $source);
    }
    // return the modified source
    return $source;
}

/**
 * Clean additional header.
 * 
 * @param array  &$additional_header Additional header.
 * @param string $pagevar            Pagevar.
 * 
 * @return void
 */
function _smarty_outputfilter_pagevars_clean_additional_header(&$additional_header, $pagevar)
{
    $ahcount = count($additional_header);
    if ($ahcount == 0) {
        return;
    }

    $new_header = array();
    for ($i = 0; $i < $ahcount; $i++) {
        if (!empty($additional_header[$i])) {
            if (stristr($additional_header[$i], $pagevar) != false) {
                // gotcha -found pagevar in additional_header string
            } else {
                // skip this
                // not found, keep the additional_header for later checks or output
                $new_header[] = $additional_header[$i];
            }
        }
    }
    $additional_header = $new_header;
    return;
}

/**
 * Save combined pagevars.
 * 
 * @param array  $files     Files.
 * @param string $ext       Extention.
 * @param string $cache_dir Cache directory.
 * 
 * @return string Combined pagevars file.
 */
function _smarty_outputfilter_pagevars_save($files, $ext, $cache_dir)
{
    $themevars = ModUtil::getVar('Theme');

    $lifetime = $themevars['cssjscombine_lifetime'];
    $hash = md5(serialize($files).UserUtil::getTheme());

    $cachedFile    = "{$cache_dir}/{$hash}_{$ext}.php";
    $cachedFileUri = "{$hash}_{$ext}.php";
    if (is_readable($cachedFile) && (filemtime($cachedFile) + $lifetime) > time()) {
        return "jcss.php?f=$cachedFileUri";
    }

    switch($ext) {
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

    $contents = array();
    $dest = fopen($cachedFile, 'w');

    $contents[] = "/* --- Combined file written: " . DateUtil::getDateTime() . " */\n\n";
    foreach ($files as $file) {
        _smarty_outputfilter_pagevars_readfile($contents, $file, $ext);
    }
    
    $contents = implode('', $contents);

    // optional minify
    if ($themevars['cssjsminify']) {
        if ($ext == 'css') {
            // Compress whitespace.
            $contents = preg_replace('/\s+/', ' ', $contents);
            // Remove comments.
            $contents = trim(preg_replace('/\/\*.*?\*\//', '', $contents));
        }
    }

    global $ZConfig;
    $signingKey = md5($ZConfig['DBInfo']['default']['dsn']);
    $signature = md5($contents.$ctype.$lifetime.$themevars['cssjscompress'].$signingKey);
    $data = array('contents' => $contents, 'ctype' => $ctype, 'lifetime' => $lifetime, 'gz' => $themevars['cssjscompress'], 'signature' => $signature);
    fwrite($dest, serialize($data));
    fclose($dest);
    return "jcss.php?f=$cachedFileUri";
}

if (!function_exists('_smarty_outputfilter_pagevars_readfile')) {
    /**
     * Reads an file and add its contents to the $contents array.
     *
     * This function includes the content of all @import statements (recursive).
     *
     * @param array  &$contents Array to save content to.
     * @param string $file      Path to file.
     * @param string $ext       Can be 'css' or 'js'.
     * 
     * @return void
     */
    function _smarty_outputfilter_pagevars_readfile(&$contents, $file, $ext)
    {
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
                        $nextchar = $i < ($lineParse_length-1) ? $lineParse{$i+1} : "";
                        
                        if (!$inMultilineComment && $char == '/' && $nextchar == '*') {
                            // a multiline comment starts here
                            $inMultilineComment = true;
                            $wasCommentHack = false;
                            $newLine .= $char.$nextchar;
                            $i++;

                        } else if ($inMultilineComment && $char == '*' && $nextchar == '/') {
                            // a multiline comment stops here
                            $inMultilineComment = false;
                            $newLine .= $char.$nextchar;
                            if (substr($lineParse, $i-3, 8) == '/*\*//*/') {
                                $wasCommentHack = true;
                                $i += 3; // move to end of hack process hack as it where
                                $newLine .= '/*/'; // fix hack comment because we lost some chars with $i += 3
                            }
                            $i++;

                        } else if ($importsAllowd && $char == '@' && substr($lineParse, $i, 7) == '@import') {
                            // an @import starts here
                            $lineParseRest = trim(substr($lineParse, $i + 7));
                            if (strtolower(substr($lineParseRest, 0, 3)) == 'url') {
                                // the @import uses url to specify the path
                                $posEnd = strpos($lineParse, ';', $i);
                                $charsEnd = substr($lineParse, $posEnd - 1, 2);
                                if ($charsEnd == ');') {
                                    // used url() without media
                                    $start = strpos($lineParseRest, '(')+1;
                                    $end = strpos($lineParseRest, ')');
                                    $url = substr($lineParseRest, $start, $end - $start);
                                    if ($url{0} == '"' | $url{0} == "'") {
                                        $url = substr($url, 1, strlen($url)-2);
                                    }

                                    // fix url
                                    $url = dirname($file) . '/' .$url;

                                    if (!$wasCommentHack) {
                                        // clear buffer
                                        $contents[] = $newLine;
                                        $newLine = "";
                                        // process include
                                        _smarty_outputfilter_pagevars_readfile($contents, $url, $ext);
                                    } else {
                                        $newLine .= '@import url("'.$url.'");';
                                    }

                                    // skip @import statement
                                    $i += $posEnd - $i;
                                } else {
                                    // @import contains media type so we can't include its contents.
                                    // We need to fix the url instead.

                                    $start = strpos($lineParseRest, '(')+1;
                                    $end = strpos($lineParseRest, ')');
                                    $url = substr($lineParseRest, $start, $end - $start);
                                    if ($url{0} == '"' | $url{0} == "'") {
                                        $url = substr($url, 1, strlen($url)-2);
                                    }

                                    // fix url
                                    $url = dirname($file) . '/' .$url;

                                    // readd @import with fixed url
                                    $newLine .= '@import url("' . $url .'")' . substr($lineParseRest, $end+1, strpos($lineParseRest, ';') - $end - 1) . ';';

                                    // skip @import statement
                                    $i += $posEnd - $i;
                                }
                            } else if (substr($lineParseRest, 0, 1) == '"' || substr($lineParseRest, 0, 1) == '\'') {
                                // the @import uses an normal string to specify the path
                                $posEnd = strpos($lineParseRest, ';');
                                $url = substr($lineParseRest, 1, $posEnd-2);
                                $posEnd = strpos($lineParse, ';', $i);

                                // fix url
                                $url = dirname($file) . '/' .$url;

                                if (!$wasCommentHack) {
                                    // clear buffer
                                    $contents[] = $newLine;
                                    $newLine = "";
                                    // process include
                                    _smarty_outputfilter_pagevars_readfile($contents, $url, $ext);
                                } else {
                                    $newLine .= '@import url("'.$url.'");';
                                }

                                // skip @import statement
                                $i += $posEnd - $i;
                            }

                        } else if (!$inMultilineComment && $char != ' ' && $char != "\n" && $char != "\r\n" && $char != "\r") {
                            // css rule found -> stop processing of @import statements
                            $importsAllowd = false;
                            $newLine .= $char;
                            
                        } else {
                            $newLine .= $char;
                        }
                    }

                    // fix other paths after @import processing
                    if (!$importsAllowd) {
                        $newLine = _smarty_outputfilter_pagevars_cssfixPath($newLine, explode('/', dirname($file)));
                    }

                    $contents[] = $newLine;
                } else {
                    $contents[] = fgets($source, 4096);
                }
            }
            fclose($source);
            $contents[] = "\n\n";
        }
    }
}

if (!function_exists('_smarty_outputfilter_pagevars_cssfixPath')) {
    /**
     * Fix paths in CSS files.
     * 
     * @param string $line     CSS file line.
     * @param string $filepath Path to original file.
     * 
     * @return tring
     */
    function _smarty_outputfilter_pagevars_cssfixPath($line, $filepath)
    {
        $regexpurl = '/url\([\'"]?([\.\/]*)(.*?)[\'"]?\)/i';
        if (strpos($line,'url') !== false) {
            preg_match_all($regexpurl, $line, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                if (strpos($match[1], '/') !== 0) {
                    $depth = substr_count($match[1],'../') * -1;
                    $path = $depth < 0 ? array_slice($filepath, 0, $depth) : $filepath;
                    $path = implode('/', $path);
                    $path = !empty($path) ? $path . '/' : '';
                    $line = str_replace($match[0], "url('{$path}{$match[2]}')", $line);
                }
            }
        }
        return $line;
    }
}
