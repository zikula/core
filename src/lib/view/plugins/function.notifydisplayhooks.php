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
 * Zikula_View function notify display hooks.
 *
 * This function notify display hooks.
 *
 * Available parameters:
 * - 'eventname' The name of the hook event [required].
 * - 'subject'   The subject of the event (array or object) [required].
 * - 'id'        The ID field/property of the index, default 'id' [required].
 * - 'module'    The caller of this hook, defaults to current module [required].
 * - 'returnurl' The return URL, defaults to URL of called page, [required].
 * - 'assign'    If set, the results array is assigned to the named variable instead display [optional].
 * - all remaining parameters are passed to the hook via the args param in the event.
 *
 * Example:
 *  {notifydisplayhooks eventname='news.item.ui.view' subject=$subject returnurl=$returnurl}
 *  {notifydisplayhooks eventname='news.item.ui.view' subject=$subject returnurl=$returnurl assign='displayhooks'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @see smarty_function_notifydisplayhooks()
 * 
 * @return void The results must be assigned to variable in assigned.
 */
function smarty_function_notifydisplayhooks($params, $view)
{
    $eventManager = $view->getEventManager();

    if (!isset($params['eventname'])) {
        trigger_error(__f('Error! "%1$s" must be set in %2$s', array('eventname', 'notifydisplayhooks')));
    }
    $eventname = $params['eventname'];

    $params['id'] = isset($params['id']) ? $params['id'] : 'id';
    $params['returnurl'] = isset($params['returnurl']) ? $params['returnurl'] : System::getCurrentUrl();
    $params['module'] = $module = isset($params['module']) ? $params['module'] : $view->getTopLevelModule();

    $subject = isset($params['subject']) ? $params['subject'] : null;
    $assign  = isset($params['assign']) ? $params['assign'] : false;
    $data    = array();

    unset($params['eventname']);
    unset($params['subject']);
    unset($params['assign']);

    // create event and notify
    $event = new Zikula_Event($eventname, $subject, $params, $data);
    $results = $eventManager->notify($event)->getData();

    // sort display hooks
    $results = HookUtil::sortDisplayHooks($module, $results);

    // assign results, this plugin does not return any display
    if ($assign) {
        $view->assign($assign, $results);
        return;
    }

    $output = '';
    foreach ($results as $result) {
        $output .= "$result\n";
    }

    return $output;
}
