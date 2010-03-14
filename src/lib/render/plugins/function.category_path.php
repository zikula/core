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
 * Retrieve and display the value of a category field (by default, the category's
 * path).
 *
 * Available attributes:
 *  - id        (numeric|string)    if a numeric value is specified, then the
 *                                  category id, if a string is specified, then
 *                                  the category's path.
 *  - idcolumn  (string)            [NOT USED] other field to use as ID (default: id)
 *  - field     (string)            category field to return (optional, default: path)
 *  - html      (boolean)           if set, return HTML (optional, default: false)
 *  - assign    (string)            the name of a template variable to assign the
 *                                  output to, instead of returning it to the template. (optional)
 *
 * Examples:
 *
 * Get the path of category #1 and assign it to the template variable $category:

 * <samp>{category_path id='1' assign='category'}</samp>
 *
 * Example from a Content module template: get the sort value of the current 
 * page's category and assign it to the template variable $catsortvalue:
 *
 * <samp>{category_path id=$page.categoryId field='sort_value' assign='catsortvalue'}</samp>
 *
 * @todo the 'idcolumn' attribute is specified but not used.
 *
 * @param   array   $params     All attributes passed to this function from the template
 * @param   Smarty  &$smarty    Reference to the {@link Renderer} object
 * @return  string  the value of the specified category field.
 */
function smarty_function_category_path($params, &$smarty)
{
    $assign    = isset($params['assign'])   ? $params['assign']   : null;
    $id        = isset($params['id'])       ? $params['id']       : 0;
    $idcolumn  = isset($params['idcolumn']) ? $params['idcolumn'] : 'id';
    $field     = isset($params['field'])    ? $params['field']    : 'path';
    $html      = isset($params['html'])     ? $params['html']     : false;

    if (!$id) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('category_path', 'id')));
    }

    if (!$field) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('category_path', 'field')));
    }

    Loader::loadClass('CategoryUtil');

    $result = null;
    if (is_numeric($id)) {
        $cat = CategoryUtil::getCategoryByID($id);
    } else {
        $cat = CategoryUtil::getCategoryByPath($id, $field);
    }

    if ($cat) {
        if (isset($cat[$field])) {
            $result = $cat[$field];
        } else {
            $smarty->trigger_error(__f('Error! Category [%1$s] does not have the field [%2$s] set.', array($id, $field)));
            return;
        }
    } else {
        $smarty->trigger_error(__f('Error! Cannot retrieve category with ID %s.', DataUtil::formatForDisplay($id)));
        return;
    }

    if ($assign) {
        $smarty->assign($params['assign'], $result);
    } else {
        if (isset($html) && is_bool($html) && $html) {
            return DataUtil::formatForDisplayHTML($result);
        } else {
            return DataUtil::formatForDisplay($result);
        }
    }
}
