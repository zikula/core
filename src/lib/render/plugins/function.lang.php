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
 * Smarty function to get the site's language
 *
 * available parameters:
 *  - assign      if set, the language will be assigned to this variable
 *
 * Example
 * <html lang="<!--[lang]-->">
 *
 * @param    array    $params     All attributes passed to this function from the template
 * @param    object   $smarty     Reference to the Smarty object
 * @return   string   the language
 */
function smarty_function_lang($params, &$smarty)
{
    $assign = isset($params['assign']) ? $params['assign']  : null;
    $fs     = isset($params['fs']) ? $params['fs'] : false;

    $result = ($fs ? ZLanguage::transformFS(ZLanguage::getLanguageCode()) : ZLanguage::getLanguageCode());

    if ($assign) {
        $smarty->assign($assign, $result);
        return;
    }

    return $result;
}
