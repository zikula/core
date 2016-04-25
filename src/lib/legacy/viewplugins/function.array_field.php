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
 * Assign an array field to a variable or display it in the output.
 *
 * Available attributes:
 *  - array  (mixed)  Name of the template array variable or the array itself to process
 *  - field  (string) Name of the array field to assign
 *  - assign (string) Name of the assign variable to setup (optional)
 *
 * Example:
 *
 *  Having an $objarray in our template, we want to check if a field is set
 *  or extract one field on another var to process it apart.
 *
 *  For instance, we need the localized output of a category. We have the
 *  $category variable, and we pass the display_name to the plugin, to get the local name:
 *
 *  <samp>{array_field array=$category.display_name field=$lang assign='displayname'}</samp>
 *
 *  And if you need to be sure that the value is set, you must test it:
 *
 *  <samp>{if $displayname}</samp>
 *
 *  In the other hand, if you have a field that exists for sure, that is in the first level
 *  of the array and you want to extract it to another variable, you can do:
 *
 *  <samp>{array_field array='category' field='id' assign='cid'}</samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return void
 */
function smarty_function_array_field($params, Zikula_View $view)
{
    if (!isset($params['array'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('assign_cache', 'var')));

        return false;
    }

    if (!isset($params['field'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('assign_cache', 'value')));

        return false;
    }

    $array  = is_array($params['array']) ? $params['array'] : $view->getTplVar($params['array']);
    $field  = isset($params['field']) ? $params['field'] : '';
    $assign = isset($params['assign']) ? $params['assign'] : null;

    $value = null;

    if ($field && is_array($array) && isset($array[$field])) {
        $value = $array[$field];
    }

    if ($assign) {
        $view->assign($params['assign'], $value);
    } else {
        return $value;
    }
}
