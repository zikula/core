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
 * available parameters:
 *  - assign      if set, the language will be assigned to this variable
 *
 * Example
 * <meta http-equiv="Content-Type" content="text/html; charset=<!--[charset]-->">
 *
 * @param    array    $params     All attributes passed to this function from the template
 * @param    object   $smarty     Reference to the Smarty object
 * @return   string   the charset
 */
function smarty_function_charset($params, &$smarty)
{
    $return = ZLanguage::getEncoding();

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $return);
    } else {
        return $return;
    }
}
