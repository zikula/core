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
 * Zikula_View function to display a drop down list of module stylesheets.
 *
 * Available parameters:
 *   - modname   The module name to show the styles for
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - id:       ID for the control
 *   - name:     Name for the control
 *   - exclude   Comma seperated list of files to exclude (optional)
 *   - selected: Selected value
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The value of the last status message posted, or void if no status message exists.
 */
function smarty_function_html_select_modulestylesheets($params, Zikula_View $view)
{
    if (!isset($params['modname'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('html_select_modulestylesheets', 'modname')));

        return false;
    }

    if (isset($params['exclude'])) {
        $exclude = explode(',', trim($params['exclude']));
    } else {
        $exclude = array();
    }

    $modstyleslist = ModUtil::apiFunc('Admin', 'admin', 'getmodstyles', array('modname' => $params['modname'], 'exclude' => $exclude));

    require_once $view->_get_plugin_filepath('function','html_options');
    $output = smarty_function_html_options(array('values'  => $modstyleslist,
                                                 'output'  => $modstyleslist,
                                                 'selected' => isset($params['selected']) ? $params['selected'] : null,
                                                 'name'     => isset($params['name'])     ? $params['name']     : null,
                                                 'id'       => isset($params['id'])       ? $params['id']       : null),
                                                 $view);

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $output);
    } else {
        return $output;
    }
}
