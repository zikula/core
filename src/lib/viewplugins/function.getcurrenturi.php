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
 * Zikula_View function to obtain current URI
 *
 * This function obtains the current request URI.
 * Unlike the API function getcurrenturi, the results of this function are already
 * sanitized to display, so it should not be passed to the safetext modifier.
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - and any additional ones to override for the current request
 *
 * Example
 *   {getcurrenturi}
 *   {getcurrenturi lang='de'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The current URI.
 */
function smarty_function_getcurrenturi($params, Zikula_View $view)
{
    $assign = null;
    if (isset($params['assign'])) {
        $assign = $params['assign'];
        unset($params['assign']);
    }

    $result = htmlspecialchars(System::getCurrentUri($params));

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}
