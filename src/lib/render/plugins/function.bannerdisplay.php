<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to display .
 *
 * This function takes a identifier and returns a banner from the banners module
 *
 * Available parameters:
 *   - id:       id of the banner group as defined in the banners module
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 * <!--[pnbannerdisplay id=0]-->
 *
 * @deprecated
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        sting
 * @return       string      the banner
 */
function smarty_function_bannerdisplay ($params, &$smarty)
{
    $id     = isset($params['id'])     ? (int)$params['id'] : 0;
    $assign = isset($params['assign']) ? $params['assign']  : null;

    if (pnModAvailable('Banners'))  {
        $result = pnModFunc('Banners', 'user', 'display', array('type' => $id));
        if ($assign) {
            $smarty->assign($assign, $result);
        } else {
            return $result;
        }
    } else {
        return '&nbsp;';
    }
}
