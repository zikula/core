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
 * Selector_countries: generate a country list selector.
 *
 * Parameters:
 *  name          The name of the selector tag
 *  selectedValue The currently selected value
 *  defaultValue  The default value (only used if no selectedValue is supplied)
 *  defaultText   Text to go with the default value
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string
 */
function smarty_function_selector_countries($params, Zikula_View $view)
{
    $allValue         = isset($params['allValue']) ? $params['allValue'] : 0;
    $allText          = isset($params['allText']) ? $params['allText'] : '';
    $class            = isset($params['class']) ? $params['class'] : null;
    $defaultValue     = isset($params['defaultValue']) ? $params['defaultValue'] : 0;
    $defaultText      = isset($params['defaultText']) ? $params['defaultText'] : '';
    $disabled         = isset($params['disabled']) ? $params['disable'] : false;
    $id                  = isset($params['id']) ? $params['id'] : null;
    $multipleSize     = isset($params['multipleSize']) ? $params['multipleSize'] : 1;
    $name             = isset($params['name']) ? $params['name'] : 'defautlselectorname';
    $required = (isset($params['required'])) ? (bool)$params['required'] : false;
    $selectedValue    = isset($params['selectedValue']) ? $params['selectedValue'] : 0;
    $submit           = isset($params['submit']) ? $params['submit'] : false;
    $title = (isset($params['title'])) ? (string)$params['title'] : null;

    return HtmlUtil::getSelector_Countries($name, $selectedValue, $defaultValue, $defaultText, $allValue, $allText, $submit, $disabled, $multipleSize, $id, $class, $required, $title);
}
