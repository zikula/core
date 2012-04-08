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
 * Pop a field of an array and assign its value to a template variable.
 *
 * Available attributes:
 *  - array  (string) Name of the template array variable to process
 *  - field  (string) Name of the array field to assign then unset
 *  - unset  (bool)   Flag to specify if the field must be unset or not (default: true)
 *  - assign (string) Name of the assign variable to setup (optional)
 *
 * Example:
 *
 *  Having an $objarray in our template, we want to process its fields in different
 *  sections of the template, so we get the needed fields separately on the desired positions,
 *  clearing the array in the process.
 *
 *  For instance, the $hooks array resulted of notify the 'display_view' hooks, can be
 *  processed individually using this plugin:
 *
 *  <samp>{array_pop array='hooks' field='EZComments'}</samp>
 *  <samp>{array_pop array='hooks' field='EZComments' assign='comments'}</samp>
 *
 *  And display later the remaining ones with a foreach.
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed False on failure, void if the value is assigned, or the value extracted itself.
 */
function smarty_function_array_pop($params, Zikula_View $view)
{
    if (!isset($params['array']) || !$params['array']) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('assign_cache', 'var')));
        return false;
    }

    if (!isset($params['field']) || !$params['field']) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('assign_cache', 'value')));
        return false;
    }

    $unset = isset($params['unset']) ? (bool)$params['unset'] : true;
    $value = null;

    $array = $view->getTplVar($params['array']);

    if ($array && isset($array[$params['field']])) {
        $value = $array[$params['field']];

        if ($unset) {
            unset($array[$params['field']]);
        }

        $view->assign($params['array'], $array);
    }

    if (isset($params['assign']) && $params['assign']) {
        $view->assign($params['assign'], $value);
    } else {
        return $value;
    }
}
