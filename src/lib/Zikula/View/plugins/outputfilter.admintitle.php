<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_View outputfilter to add a title to all admin pages
 *
 * @param string      $source Output source.
 * @param Zikula_View $view   Reference to Zikula_View instance.
 *
 * @return string
 */
function smarty_outputfilter_admintitle($source, $view)
{
    // get all the matching H1 tags
    $regex = "/<h1>[^<]*<\/h1>/";
    preg_match_all($regex, $source, $h1);
    $regex = "/<h2>[^<]*<\/h2>/";
    preg_match_all($regex, $source, $h2);
    $regex = "/<h3>[^<]*<\/h3>/";
    preg_match_all($regex, $source, $h3);

    // init vars
    $titleargs = array();
    $header1 = $header2 = $header3 = '';

    // set the title
    // header level 1
    if (isset($h1[0]) && isset($h1[0][1])) {
        $header1 = $h1[0][1];
    }
    if (isset($header1) && !empty($header1)) {
        $titleargs[] = $header1;
    }
    // header level 2
    if (isset($h2[0][0])) {
        $header2 = $h2[0][0];
    }
    if (isset($header2) && !empty($header2)) {
        $titleargs[] = $header2;
    }
    // header level 3
    if (isset($h3[0]) && isset($h3[0][1])) {
        $header3 = $h3[0][1];
    }
    if (isset($header3) && !empty($header3)) {
        $titleargs[] = $header3;
    }

    if (!empty($titleargs)) {
        PageUtil::setVar('title', System::getVar('sitename') . ' - ' . strip_tags(implode(' / ', $titleargs)));
    }

    // return the modified source
    return $source;
}