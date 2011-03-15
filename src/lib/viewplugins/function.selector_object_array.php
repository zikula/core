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
 * Object array selector.
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_function_selector_object_array ($params, Zikula_View $view)
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

    // get all but force execution of new query for object get
    if (!$where) {
        $where = ' ';
    }

    return HtmlUtil::getSelector_ObjectArray ($modname, $class, $name, $field, $displayField, $where, $sort,
                                              $selectedValue, $defaultValue, $defaultText, $allValue, $allText,
                                              $displayField2, $submit, $disabled, $fieldSeparator, $multipleSize);
}
