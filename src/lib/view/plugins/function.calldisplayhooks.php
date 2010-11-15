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
 * Zikula_View function call hooks.
 *
 * This function invokes display hookes.
 *
 * Available parameters:
 * - TBA:
 * - 'assign' If set, the results are assigned to the corresponding variable instead of printed out
 * - all remaining parameters are passed to the hook via the args param in the event.
 *
 * Example:
 *     {calldisplayhooks eventname='news.item.ui.view' subject=$subject module='foo' id=$id returnurl=$returnurl assign='displayhooks'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @see smarty_function_calldisplayhooks()
 * 
 * @return string The results of the module function.
 */
function smarty_function_calldisplayhooks($params, $view)
{
    $eventManager = $view->getEventManager();

    $assign = isset($params['assign']) ? $params['assign'] : 'displayhooks';

    if (!isset($params['module'])) {
         $params['module'] = $view->getTopLevelModule();
    }
    $module = $params['module'];

    $subject = isset($params['subject']) ? $params['subject'] : null;
    $type = isset($params['type']) ? $params['type'] : null;
    $params['returnurl'] = isset($params['returnurl']) ? $params['returnurl'] : System::getCurrentUrl();
    $data = new ArrayObject(array());

    unset($params['subject']);
    unset($params['eventname']);
    unset($params['assign']);

    // create event and notify
    $event = new Zikula_Event($type, $subject, $params, $data);
    $results = $eventManager->notify($event)->getData();

    $results = HookUtil::sortDisplayHooks($module, $results);
    $view->assign($assign, $results);
}
