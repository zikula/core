<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Field array selector.
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_function_selector_field_array($params, Zikula_View $view)
{
    $selectedValue = isset($params['selectedValue']) ? $params['selectedValue'] : 0;
    $allText = isset($params['allText']) ? $params['allText'] : '';
    $allValue = isset($params['allValue']) ? $params['allValue'] : 0;
    $defaultText = isset($params['defaultText']) ? $params['defaultText'] : '';
    $defaultValue = isset($params['defaultValue']) ? $params['defaultValue'] : 0;
    $selectedValue = isset($params['selectedValue']) ? $params['selectedValue'] : '';
    $field = isset($params['field']) ? $params['field'] : 'id';
    $modname = isset($params['modname']) ? $params['modname'] : '';
    $name = isset($params['name']) ? $params['name'] : '';
    $table = isset($params['table']) ? $params['table'] : '';
    $where = isset($params['where']) ? $params['where'] : '';
    $sort = isset($params['sort']) ? $params['sort'] : '';
    $submit = isset($params['submit']) ? $params['submit'] : 0;
    $distinct = isset($params['distinct']) ? $params['distinct'] : 0;
    $assocKey = isset($params['assocKey']) ? $params['assocKey'] : '';
    $disabled = isset($params['disabled']) ? $params['disabled'] : 0;
    $truncate = isset($params['truncate']) ? $params['truncate'] : 0;
    $multipleSize = isset($params['multipleSize']) ? $params['multipleSize'] : 1;

    return HtmlUtil::getSelector_FieldArray($modname, $table, $name, $field, $where, $sort,
            $selectedValue, $defaultValue, $defaultText, $allValue, $allText, $assocKey,
            $distinct, $submit, $disabled, $truncate, $multipleSize);
}
