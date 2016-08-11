<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string|void The language, null if params['assign'] is true
 */
function smarty_function_lang($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;
    $fs     = isset($params['fs']) ? $params['fs'] : false;

    $result = ($fs ? ZLanguage::transformFS(ZLanguage::getLanguageCode()) : ZLanguage::getLanguageCode());

    if ($assign) {
        $view->assign($assign, $result);

        return;
    }

    return $result;
}
