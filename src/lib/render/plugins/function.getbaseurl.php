<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to obtain base URL for this site
 *
 * This function obtains the base URL for the site. The base url is defined as the
 * full URL for the site minus any file information  i.e. everything before the
 * 'index.php' from your start page.
 * Unlike the API function System::getBaseUrl, the results of this function are already
 * sanitized to display, so it should not be passed to the DataUtil::formatForDisplay modifier.
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 *   <!--[System::getBaseUrl]-->
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      the base URL of the site
 */
function smarty_function_getbaseurl ($params, &$smarty)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;

    $result = htmlspecialchars(System::getBaseUrl());

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}
