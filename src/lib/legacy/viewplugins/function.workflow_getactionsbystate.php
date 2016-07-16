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
 * Zikula_View function to display the available workflow actions for the current item state.
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string HTML code with the available workflow actions for the current item state
 */
function smarty_function_workflow_getactionsbystate($params, Zikula_View $view)
{
    if (!isset($params['schema'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['workflow_getactionsbystate', 'schema']));

        return false;
    }

    if (!isset($params['module'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['workflow_getactionsbystate', 'module']));

        return false;
    }

    if (!isset($params['state'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['workflow_getactionsbystate', 'state']));

        return false;
    }

    $actions = WorkflowUtil::getActionsByState($params['schema'], $params['module'], $params['state']);
    $ak = array_keys($actions);
    $options = [];
    foreach ($ak as $action) {
        $options[] = $action;
    }

    return HtmlUtil::FormSelectMultipleSubmit($name, $options);
}
