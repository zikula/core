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
 * Zikula_View function notify event.
 *
 * This function notify an event.
 *
 * Available parameters:
 * - 'eventname'    The name of the event [required].
 * - 'eventsubject' The ID if the subject.
 * - 'eventdata'    Data.
 *
 * OR:
 * - 'eventobject'  An event object [required].
 *
 * AND:
 * - 'assign'       If set, the event object's data ($event->getData()) is assigned to the named variable instead displayed [optional].
 * - all remaining parameters are passed to the event via the args param in the event.
 *
 * Example:
 *  {notifyevent eventname='module.event.name' eventsubject=$subject eventdata=$data arg1=$arg1 arg2=arg2}
 *  {notifyevent eventname='module.event.name' eventsubject=$subject eventdata=$data arg1=$arg1 arg2=arg2 assign=$data}
 *  {notifyevent eventname='module.event.name' arg1=$arg1 arg2=arg2 assign=$data}
 *  {notifyevent eventobject=$eventObject}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @see    smarty_function_notifyevent()
 *
 * @return void The results must be assigned to variable in assigned.
 */
function smarty_function_notifyevent($params, Zikula_View $view)
{
    if (isset($params['assign'])) {
        $assign = $params['assign'];
        unset($params['assign']);
    } else {
        $assign = false;
    }

    if (isset($params['eventobject'])) {
        $event = $params['eventobject'];
        unset($params['eventobject']);
    } else {
        if (isset($params['eventname'])) {
            $eventName = $params['eventname'];
            unset($params['eventname']);
        } else {
            return trigger_error(__('eventname is a required param for {notifyevent} plugin.'));
        }

        if (isset($params['eventsubject'])) {
            $eventSubject = $params['eventsubject'];
            unset($params['eventsubject']);
        } else {
            $eventSubject = null;
        }

        if (isset($params['eventdata'])) {
            $eventData = $params['eventdata'];
            unset($params['eventdata']);
        } else {
            $eventData = null;
        }

        $event = new Zikula_Event($eventName, $eventSubject, $params, $eventData);
    }

    $view->getEventManager()->notify($event);

    // assign results, this plugin does not return any display
    if ($assign) {
        $view->assign($assign, $event->getData());

        return;
    }

    return $event->getData();
}
