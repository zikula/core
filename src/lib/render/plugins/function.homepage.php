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
 * Plugin to return the homepage address.
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 *   {homepage}
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param object &$smarty Reference to the Smarty object.
 *
 * @return string The base URL of the site.
 */
function smarty_function_homepage($params, &$smarty)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;

    $result = htmlspecialchars(System::getHomepageUrl());

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}
