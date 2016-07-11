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
 * Retrieve and display the value of a category field (by default, the category's path).
 *
 * Available attributes:
 *  - id        (numeric|string)    if a numeric value is specified, then the
 *                                  category id, if a string is specified, then
 *                                  the category's path.
 *  - idcolumn  (string)            field to use as the unique ID, either 'id',
 *                                  'path', or 'ipath' (optional,
 *                                  default: 'id' if the id attribute is numeric,
 *                                  'path' if the id attribute is not numeric)
 *  - field     (string)            category field to return (optional, default: path)
 *  - html      (boolean)           if set, return HTML (optional, default: false)
 *  - assign    (string)            the name of a template variable to assign the
 *                                  output to, instead of returning it to the template. (optional)
 *
 * Examples:
 *
 * Get the path of category #1 and assign it to the template variable $category:
 *
 * <samp>{category_path id='1' assign='category'}</samp>
 *
 * Get the path of the category with an ipath of '/1/3/28/30' and display it.
 *
 * <samp>{category_path id='/1/3/28/30' idcolumn='ipath' field='path'}</samp>
 *
 * Get the parent_id of the category with a path of
 * '/__SYSTEM__/General/ActiveStatus/Active' and assign it to the template
 * variable $parentid. Then use that template variable to retrieve and display
 * the parent's path.
 *
 * <samp>{category_path id='/__SYSTEM__/General/ActiveStatus/Active' field='parent_id' assign='parentid'}</samp>
 * <samp>{category_path id=$parentid}</samp>
 *
 * Example from a Content module template: get the sort value of the current
 * page's category and assign it to the template variable $catsortvalue:
 *
 * <samp>{category_path id=$page.categoryId field='sort_value' assign='catsortvalue'}</samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return void|string The value of the specified category field.
 */
function smarty_function_category_path($params, Zikula_View $view)
{
    $assign    = isset($params['assign'])   ? $params['assign']   : null;
    $id        = isset($params['id'])       ? $params['id']       : 0;
    $idcolumn  = isset($params['idcolumn']) ? $params['idcolumn'] : (is_numeric($id) ? 'id' : 'path');
    $field     = isset($params['field'])    ? $params['field']    : 'path';
    $html      = isset($params['html'])     ? $params['html']     : false;

    if (!$id) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['category_path', 'id']));
    }

    if (!$idcolumn) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['category_path', 'idcolumn']));
    } elseif (($idcolumn != 'id') && ($idcolumn != 'path') && ($idcolumn != 'ipath')) {
        $view->trigger_error(__f('Error! in %1$s: invalid value for the %2$s parameter (%3$s).', ['category_path', 'idcolumn', $idcolumn]));
    }

    if (!$field) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['category_path', 'field']));
    }

    $result = null;
    if ($idcolumn == 'id') {
        $cat = CategoryUtil::getCategoryByID($id);
    } elseif (($idcolumn == 'path') || ($idcolumn == 'ipath')) {
        $cat = CategoryUtil::getCategoryByPath($id, $idcolumn);
    }

    if ($cat) {
        if (isset($cat[$field])) {
            $result = $cat[$field];
        } else {
            $view->trigger_error(__f('Error! Category [%1$s] does not have the field [%2$s] set.', [$id, $field]));

            return;
        }
    } else {
        $view->trigger_error(__f('Error! Cannot retrieve category with ID %s.', DataUtil::formatForDisplay($id)));

        return;
    }

    if ($assign) {
        $view->assign($params['assign'], $result);
    } else {
        if (isset($html) && is_bool($html) && $html) {
            return DataUtil::formatForDisplayHTML($result);
        } else {
            return DataUtil::formatForDisplay($result);
        }
    }
}
