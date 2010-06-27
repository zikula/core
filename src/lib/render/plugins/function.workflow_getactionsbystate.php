<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Render
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to display the available workflow actions for the current item state.
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the Smarty object.
 *
 * @return stringHTML code with the available workflow actions for the current item state.
 */
function smarty_function_workflow_getactionsbystate($params, &$smarty)
{
    if (!isset($params['schema'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pnworkflow_getactionsbystate', 'schema')));
        return false;
    }

    if (!isset($params['module'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pnworkflow_getactionsbystate', 'module')));
        return false;
    }

    if (!isset($params['state'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pnworkflow_getactionsbystate', 'state')));
        return false;
    }

    $actions = WorkflowUtil::getActionsByState($params['schema'], $params['module'], $params['state']);
    $ak = array_keys($actions);
    $options = array();
    foreach ($ak as $action) {
        $options[] = $action;
    }

    return HtmlUtil::FormSelectMultipleSubmit($name, $options);
}
