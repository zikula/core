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
 * Zikula_View function to generate the title for the page
 *
 * Available parameters:
 *  - assign     if set, the title will be assigned to this variable
 *  - separator  if set, the title elements will be seperated using this string
 *               (optional: default '::')
 *  - noslogan   if set, the slogan will not be appended
 *  - nositename if set, the sitename will not be appended
 *
 * Example
 * {title}
 * {title separator='/'}
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Zikula_View &$view Reference to the Zikula_View object.
 *
 * @see    function.title.php::smarty_function_title()
 * @return string The title.
 */
function smarty_function_title($params, &$view)
{
    if (!isset($params['separator'])) {
        $separator = ' :: ';
    } else {
        $separator = " $params[separator] ";
    }

    $slogan = '';
    if (!isset($params['noslogan'])) {
        $slogan = PageUtil::getVar('description');
    }

    $sitename = '';
    if (!isset($params['nositename'])) {
        $sitename = System::getVar('sitename');
    }

    // init vars
    $title = '';
    $titleargs = array();

    // default for standard page output
    $pagetitle = PageUtil::getVar('title');
    if ($pagetitle) {
        if (is_array($pagetitle)) {
            $titleargs = $pagetitle;
        } else {
            $titleargs[] = $pagetitle;
        }
    } elseif (isset($GLOBALS['info']) && is_array($GLOBALS['info'])) {
        // article page output
        $titleargs[] = $GLOBALS['info']['title'];
    }

    // append sitename and/or slogan
    if (!empty($sitename)) {
        $titleargs[] = $sitename;
    }
    if (!empty($slogan)) {
        $titleargs[] = $slogan;
    }

    // strip tags from the title
    $title = strip_tags(implode($separator, $titleargs));

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $title);
    } else {
        return $title;
    }
}
