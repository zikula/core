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
 * Smarty function to get the users language
 *
 * This function determines the recent users language
 *
 * Available parameters:
 *   - assign:  If set, the result is assigned to the corresponding variable instead of printed out
 *
 * Example
 *   <!--[pnusergetlang name="foobar" ]-->
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        string      $assign      (optional) The name of the variable to assign the result to
 * @return       string      The recent users language
 */
function smarty_function_usergetlang($params,&$smarty)
{
    $assign = isset($params['assign'])  ? $params['assign']  : null;

    $result = ZLanguage::getLanguageCodeLegacy();

    if ($assign) {
        $smarty->assign($assign, $result);
        return;
    }
    return $result;
}
