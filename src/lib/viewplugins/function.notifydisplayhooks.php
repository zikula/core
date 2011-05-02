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
 * - 'id'        The ID if the subject.
 * - 'returnurl' The return URL, defaults to URL of called page, [required].
 * - 'assign'    If set, the results array is assigned to the named variable instead display [optional].
 * - 'caller'    This is filled in automatically - for normal use this field is not required.
 * - all remaining parameters are passed to the hook via the args param in the event.
 *
 * Example:
 *  {notifydisplayhooks eventname='news.hook.item.ui.view' subject=$subject id=$id returnurl=$returnurl}
 *  {notifydisplayhooks eventname='news.hook.item.ui.view' subject=$subject id=$id returnurl=$returnurl assign='displayhooks'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @see    smarty_function_notifydisplayhooks()
 *
 * @return void The results must be assigned to variable in assigned.
 */
function smarty_function_notifydisplayhooks($params, Zikula_View $view)
{
    if (!isset($params['eventname'])) {
        trigger_error(__f('Error! "%1$s" must be set in %2$s', array('eventname', 'notifydisplayhooks')));
    }
    $eventname = $params['eventname'];

    $params['id'] = isset($params['id']) ? $params['id'] : null;
    $params['returnurl'] = isset($params['returnurl']) ? $params['returnurl'] : System::getCurrentUrl();
    $caller = isset($params['caller']) ? $params['caller'] : $view->getController()->getName();

    $subject = isset($params['subject']) ? $params['subject'] : null;
    $assign  = isset($params['assign']) ? $params['assign'] : false;
    $data    = array();

    unset($params['eventname']);
    unset($params['subject']);
    unset($params['assign']);

    // Add the Zikula_View instance as an argument
    $params['view'] = $view;

    // create event and notify
    $hook = new Zikula_Hook($eventname, $caller, $subject, $params, $data);
    $results = $view->getServiceManager()->getService('zikula.hookmanager')->notify($hook)->getData();

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
