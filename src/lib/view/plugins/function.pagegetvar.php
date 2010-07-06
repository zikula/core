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
 * Zikula_View function to get page variable
 *
 * This function obtains a page-specific variable from the Zikula system.
 *
 * Available parameters:
 *   - name:     The name of the page variable to obtain
 *   - html:     If true then result will be treated as html content.
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Zikula doesn't impose any restriction on the page variabl's name except for duplicate
 * and reserved names. As of this writing, the list of reserved names consists of
 * <ul>
 * <li>title</li>
 * <li>description</li>
 * <li>keywords</li>
 * <li>stylesheet</li>
 * <li>javascript</li>
 * <li>body</li>
 * <li>rawtext</li>
 * <li>footer</li>
 * </ul>
 *
 * Example
 *   {pagegetvar name='title'}
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Zikula_View &$view Reference to the Zikula_View object.
 *
 * @return string The module variable.
 */
function smarty_function_pagegetvar($params, &$view)
{
    $assign = isset($params['assign']) ? $params['assign']     : null;
    $html   = isset($params['html'])   ? (bool)$params['html'] : false;
    $name   = isset($params['name'])   ? $params['name']       : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pnpagegetvar', 'name')));
        return false;
    }

    $result = PageUtil::getVar($name);

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        if ($html) {
            return DataUtil::formatForDisplayHTML($result);
        } else {
            return DataUtil::formatForDisplay($result);
        }
    }
}
