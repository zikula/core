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
 * Object array selector.
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_function_selector_object_array($params, Zikula_View $view)
{
    $selectedValue  = (isset($params['selectedValue'])  ? $params['selectedValue']  : 0);
    $defaultValue   = (isset($params['defaultValue'])   ? $params['defaultValue']   : 0);
    $defaultText    = (isset($params['defaultText'])    ? $params['defaultText']    : '');
    $allValue       = (isset($params['allValue'])       ? $params['allValue']       : 0);
    $allText        = (isset($params['allText'])        ? $params['allText']        : '');
    $field          = (isset($params['field'])          ? $params['field']          : 'id');
    $displayField   = (isset($params['displayField'])   ? $params['displayField']   : 'name');
    $displayField2  = (isset($params['displayField2'])  ? $params['displayField2']  : '');
    $fieldSeparator = (isset($params['fieldSeparator']) ? $params['fieldSeparator'] : ', ');
    $name           = (isset($params['name'])           ? $params['name']           : 'selector');
    $class          = (isset($params['class'])          ? $params['class']          : '');
    $where          = (isset($params['where'])          ? $params['where']          : '');
    $sort           = (isset($params['sort'])           ? $params['sort']           : '');
    $modname        = (isset($params['modname'])        ? $params['modname']        : '');
    $submit         = (isset($params['submit'])         ? $params['submit']         : false);
    $disabled       = (isset($params['disabled'])       ? $params['disabled']       : false);
    $multipleSize   = (isset($params['multipleSize'])   ? $params['multipleSize']   : 1);
    $entity         = (isset($params['entity'])         ? true                      : false);

    $method = $entity ? 'getSelector_EntityArray' : 'getSelector_ObjectArray';

    // get all but force execution of new query for object get
    if (!$where) {
        $where = ' ';
    }

    return HtmlUtil::$method($modname, $class, $name, $field, $displayField, $where, $sort,
                              $selectedValue, $defaultValue, $defaultText, $allValue, $allText,
                              $displayField2, $submit, $disabled, $fieldSeparator, $multipleSize);
}
