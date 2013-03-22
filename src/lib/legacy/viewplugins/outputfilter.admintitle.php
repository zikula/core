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
 * @param string            $source Output source.
 * @param Zikula_View_Theme $view   Reference to Zikula_View_Theme instance.
 *
 * @return string
 */
function smarty_outputfilter_admintitle($source, $view)
{
    // get the first heading tags
    // module - usually display module
    preg_match("/<h2>([^<]*)<\/h2>/", $source, $header2);
    // function pagetitle
    preg_match("/<h3>([^<]*)<\/h3>/", $source, $header3);

    // init the args
    $titleargs = array();

    // checks for header level 3
    if ($header3 && isset($header3[1]) && $header3[1]) {
        $titleargs[] = $header = strip_tags($header3[1]);
        // put its value on any z-adminpage-func element
        $source = preg_replace('/z-admin-pagefunc">(.*?)</', 'z-adminpage-func">'.$header.'<', $source, 1);
    }

    // checks for header level 2
    if ($header2 && isset($header2[1]) && $header2[1]) {
        $titleargs[] = $header = strip_tags($header2[1]);
        // put its value on any z-adminpage-func element
        $source = preg_replace('/z-admin-pagemodule">(.*?)</', 'z-admin-pagemodule">'.$header.'<', $source, 1);
    }

    if (!empty($titleargs)) {
        $titleargs[] = __('Administration');
        $titleargs[] = System::getVar('sitename');

        $title  = implode(' - ', $titleargs);
        $source = preg_replace('/<title>(.*?)<\/title>/', '<title>'.$title.'</title>', $source, 1);
    }

    // return the modified page source
    return $source;
}
