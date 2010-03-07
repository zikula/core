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
 * Smarty function to get the site's charset
 *
 * This function will return the Zikula version number
 *
 * available parameters:
 *  - assign      if set, the language will be assigned to this variable
 *
 * @param    array    $params     All attributes passed to this function from the template
 * @param    object   $smarty     Reference to the Smarty object
 * @return   string   the version string
 */
function smarty_function_version($params, &$smarty)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;

    $return = PN_VERSION_NUM;

    if ($assign) {
        $smarty->assign($assign, $return);
    } else {
        return $return;
    }
}
