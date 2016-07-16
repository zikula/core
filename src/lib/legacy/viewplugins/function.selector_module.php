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
 * selector_module: generate a PN Module selector
 *
 * Parameters:
 *  name          The name of the selector tag
 *  selectedValue The currently selected value
 *  defaultValue  The default value (only used if no selectedValue is supplied)
 *  defaultText   Text to go with the default value
 *  allValue      Wether or not to include an 'All' selector
 *  allText       Text to go with the 'All' select value
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string
 */
function smarty_function_selector_module($params, Zikula_View $view)
{
    $name          = isset($params['name'])          ? $params['name']          : 'defaultselectorname';
    $field         = isset($params['field'])         ? $params['field']         : 'name';
    $selectedValue = isset($params['selectedValue']) ? $params['selectedValue'] : 0;
    $defaultValue  = isset($params['defaultValue'])  ? $params['defaultValue']  : 0;
    $defaultText   = isset($params['defaultText'])   ? $params['defaultText']   : null;
    $allValue      = isset($params['allValue'])      ? $params['allValue']      : false;
    $allText       = isset($params['allText'])       ? $params['allText']       : null;
    $submit        = isset($params['submit'])        ? $params['submit']        : false;
    $disabled      = isset($params['disabled'])      ? $params['disabled']      : false;
    $multipleSize  = isset($params['multipleSize'])  ? $params['multipleSize']  : 1;

    return HtmlUtil::getSelector_Module($name, $selectedValue, $defaultValue, $defaultText, $allValue, $allText,
                                           $submit, $disabled, $multipleSize, $field);
}
