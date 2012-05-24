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
 * Zikula_View function to add a single page variable value
 *
 * This function sets a page-specific variable from the Zikula system. Only single value pagevars are supported by
 * this insert!
 *
 * Available parameters:
 *   - var:   The name of the single value page variable to set
 *   - value: The value of the page variable to set
 *
 * Zikula doesn't impose any restriction on the page variable's name except for duplicate
 * and reserved names. As of this writing, the list of supported names consists of
 * <ul>
 * <li>title</li>
 * <li>stylesheet</li>
 * <li>javascript</li>
 * <li>body</li>
 * <li>header</li>
 * <li>footer</li>
 * </ul>
 *
 * In addition, if your system is operating in legacy compatibility mode, then
 * the variable 'rawtext' is reserved, and maps to 'header'. (When not operating in
 * legacy compatibility mode, 'rawtext' is not reserved and will not be rendered
 * to the page output by the page variable output filter.)
 *
 * Example
 *   {insert name='pageaddvar' var='javascript' value='path/to/myscript.js'}
 *   {insert name='pageaddvar' var='title' value=$mytitle}
 *
 * Note that $mytitle must be already assigned to the Zikula_View instance
 * before fetch the cached template.
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_insert_pageaddvar($params, $view)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated, please use {%2$s} instead.', array('insert name="pageaddvar" var="stylesheet" value="path/to/file.css"', 'pageaddvar name="stylesheet" value="path/to/file.css"')), E_USER_DEPRECATED);

    $var   = isset($params['var'])  ? $params['var']  : null;
    $value = isset($params['value']) ? $params['value'] : null;

    if (!$var) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('insert.pageaddvar', 'var')));

        return false;
    }
    if (!$value) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('insert.pageaddvar', 'value')));

        return false;
    }

    if (in_array($var, array('stylesheet', 'javascript'))) {
        $value = explode(',', $value);
    }

    PageUtil::addVar($var, $value);

    return;
}
