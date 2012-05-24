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
 * Zikula_View function to get the site's language.
 *
 * Available parameters:
 *  - assign      if set, the language will be assigned to this variable
 *
 * Example
 * <html lang="{lang}">
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string|void The language, null if params['assign'] is true.
 */
function smarty_function_lang($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign']  : null;
    $fs     = isset($params['fs']) ? $params['fs'] : false;

    $result = ($fs ? ZLanguage::transformFS(ZLanguage::getLanguageCode()) : ZLanguage::getLanguageCode());

    if ($assign) {
        $view->assign($assign, $result);

        return;
    }

    return $result;
}
