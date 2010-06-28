<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
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
function smarty_outputfilter_pagevars_notcombined($source, &$smarty)
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


    if (is_array($stylesheets) && !empty($stylesheets)) {
        foreach ($stylesheets as $stylesheet) {
            if (empty($stylesheet))
                continue;
                // check if the stylesheets is in the additional_header array
            _smarty_outputfilter_pagevars_notcombined_clean_additional_header($additional_header, $stylesheet);
            if ($themeinfo['xhtml']) {
                $return .= '<link rel="stylesheet" href="' . DataUtil::formatForDisplay($stylesheet) . '" type="text/css" />' . "\n";
            } else {
                $return .= '<link rel="stylesheet" href="' . DataUtil::formatForDisplay($stylesheet) . '" type="text/css">' . "\n";
            }
        }
    }

    if (is_array($javascripts) && !empty($javascripts)) {
        // check for prototype and ajax
        if (in_array('javascript/ajax/prototype.js', $javascripts) && !in_array('javascript/helpers/Zikula.js', $javascripts)) {
            // prototype found, we also load ajax.js now
            $javascripts[] = 'javascript/helpers/Zikula.js';
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
        $return .= '<script type="text/javascript">/* <![CDATA[ */ document.location.entrypoint="' . System::getVar('entrypoint', 'index.php') . '"; document.location.pnbaseURL="' . System::getBaseUrl() . '"; ';
        // check if the ajaxtimeout is configured and not the defsult value of 5000, in this case add the value in the inline js for refernce in ajax.js
        $ajaxtimeout = System::getVar('ajaxtimeout', 5000);
        if ($ajaxtimeout != 5000) {
            $return .= 'document.location.ajaxtimeout=' . (int)DataUtil::formatForDisplay($ajaxtimeout) . ';';
        }
        $return .= ' /* ]]> */</script>' . "\n";
        foreach ($javascripts as $javascript) {
            if (empty($javascript)) {
                continue;
            }
            // check if the javascript is in the additional_header array
            _smarty_outputfilter_pagevars_notcombined_clean_additional_header($additional_header, $javascript);
            $return .= '<script type="text/javascript" src="' . DataUtil::formatForDisplay($javascript) . '"></script>' . "\n";
        }
    }

    $rawtext = PageUtil::getVar('rawtext');
    if (is_array($rawtext) && !empty($rawtext)) {
        $return .= implode("\n", $rawtext) . "\n";
    }

    // implode the remaining additional header global to a string
    if (isset($additional_header) && count($additional_header) > 0) {
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
        $footersource = @implode("\n", $footervars) . "\n</body>";
        $source = str_replace('</body>', $footersource, $source);
    }

    // replace the string in the template source
    if (stristr($source, '<!-- pagevars -->')) {
        $source = str_replace('<!-- pagevars -->', $return, $source);
    } else {
        $source = str_replace('</head>', $return . "\n</head>", $source);
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
function _smarty_outputfilter_pagevars_notcombined_clean_additional_header(&$additional_header, $pagevar)
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
