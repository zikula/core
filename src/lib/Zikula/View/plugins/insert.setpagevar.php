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
 * Zikula_View function to set a single value page variable
 *
 * This function sets a page-specific variable from the Zikula system. Only single value pagevars are supported by
 * this insert!
 *
 * Available parameters:
 *   - var:     The name of the single value page variable to set
 *   - value:    The value of the page variable to set
 *
 * Zikula doesn't impose any restriction on the page variable's name except for duplicate
 * and reserved names. As of this writing, the list of supported names consists of
 * <ul>
 * <li>title</li>
 * <li>description</li>
 * </ul>
 *
 * Example
 *   {insert name='setpagevar' var='title' value='mytitle'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_insert_setpagevar($params, $view)
{
    $var   = isset($params['var'])  ? $params['var']  : null;
    $value = isset($params['value']) ? $params['value'] : null;

    if (!$var) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('setpagevar', 'var')));
        return false;
    }
    if (!$value) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('setpagevar', 'value')));
        return false;
    }

    PageUtil::setVar($var, $value);
    return;
}
