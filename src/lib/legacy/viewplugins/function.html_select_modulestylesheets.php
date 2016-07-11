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
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['html_select_modulestylesheets', 'modname']));

        return false;
    }

    $exclude = [];
    if (isset($params['exclude'])) {
        $exclude = explode(',', trim($params['exclude']));
        unset($params['exclude']);
    }

    $params['values'] = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getmodstyles', ['modname' => $params['modname'], 'exclude' => $exclude]);
    unset($params['modname']);
    $params['output'] = $params['values'];

    $assign = isset($params['assign']) ? $params['assign'] : null;
    unset($params['assign']);

    require_once $view->_get_plugin_filepath('function', 'html_options');
    $output = smarty_function_html_options($params, $view);

    if (!empty($assign)) {
        $view->assign($assign, $output);
    } else {
        return $output;
    }
}
