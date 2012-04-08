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
 * Zikula_View function to obtain base URL for this site
 *
 * This function obtains the base URL for the site. The base url is defined as the
 * full URL for the site minus any file information  i.e. everything before the
 * 'index.php' from your start page.
 * Unlike the API function System::getHost, the results of this function are already
 * sanitized to display, so it should not be passed to the DataUtil::formatForDisplay modifier.
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 *   {gethost}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The base URL of the site.
 */
function smarty_function_gethost ($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;

    $result = htmlspecialchars(System::getHost());

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}
