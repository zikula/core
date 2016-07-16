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
 * Zikula_View function to get a country name from a given country name.
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - code:     Country code to get the corresponding name for
 *
 * Example
 *   {get_country_name_for_country_code  code=ZZ}
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string The value of the last status message posted, or void if no status message exists
 */
function smarty_function_get_country_name_for_country_code($params, Zikula_View $view)
{
    $code   = strtolower(isset($params['code']) ? $params['code'] : 'ZZ');
    $assign = isset($params['assign']) ? $params['assign'] : null;

    $countries = ZLanguage::countryMap();
    if (isset($countries[$code])) {
        $result = $countries[$code];
    } else {
        $result = $countries['ZZ'];
    }

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}
