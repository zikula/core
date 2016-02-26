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
 * - 'id'        The ID if the subject.
 * - 'urlobject' Zikula_ModUrl instance or null.
 * - 'assign'    If set, the results array is assigned to the named variable instead display [optional].
 * - all remaining parameters are passed to the hook via the args param in the event.
 *
 * Example:
 *  {notifydisplayhooks eventname='news.ui_hooks.item.display_view' id=$id urlobject=$urlObject}
 *  {notifydisplayhooks eventname='news.ui_hooks.item.display_view' id=$id urlobject=$urlObject assign='displayhooks'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @see    smarty_function_notifydisplayhooks()
 *
 * @return string|void if the results are assigned to variable in assigned.
 */
function smarty_function_notifydisplayhooks($params, Zikula_View $view)
{
    if (!isset($params['eventname'])) {
        return trigger_error(__f('Error! "%1$s" must be set in %2$s', array('eventname', 'notifydisplayhooks')));
    }
    $eventname = $params['eventname'];
    $id = isset($params['id']) ? $params['id'] : null;
    $urlObject = isset($params['urlobject']) ? $params['urlobject'] : null;
    if ($urlObject && !($urlObject instanceof \Zikula\Core\UrlInterface)) {
        return trigger_error(__f('Error! "%1$s" must be an instance of %2$s', array('urlobject', '\Zikula\Core\UrlInterface')));
    }
    $assign  = isset($params['assign']) ? $params['assign'] : false;

    // create event and notify
    $hook = new Zikula_DisplayHook($eventname, $id, $urlObject); // @todo Zikula_DisplayHook maintains BC. IN 1.5.0 change to \Zikula\Bundle\HookBundle\Hook\DisplayHook($id, $urlObject);
    $view->getContainer()->get('hook_dispatcher')->dispatch($eventname, $hook);
    $responses = $hook->getResponses();

    // assign results, this plugin does not return any display
    if ($assign) {
        $view->assign($assign, $responses);

        return null;
    }

    $output = '';
    foreach ($responses as $result) {
        $output .= "<div class=\"z-displayhook\">$result</div>\n";
    }

    return $output;
}
