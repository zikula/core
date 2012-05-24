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
 * Zikula_View insert function to dynamically get current status/error message
 *
 * This function obtains the last status message posted for this session.
 * The status message exists in one of two session variables: '_ZStatusMsg' for a
 * status message, or '_ZErrorMsg' for an error message. If both a status and an
 * error message exists then the error message is returned.
 *
 * This is is a destructive function - it deletes the two session variables
 * '_ZStatusMsg' and 'erorrmsg' during its operation.
 *
 * Available parameters:
 *   - assign:   If set, the status message is assigned to the corresponding variable instead of printed out
 *   - style, class: If set, the status message is being put in a div tag with the respective attributes
 *   - tag:      You can specify if you would like a span or a div tag
 *
 * Example
 *   {insert name='getstatusmsg'}
 *   {insert name="getstatusmsg" style="color:red;"}
 *   {insert name="getstatusmsg" class="statusmessage" tag="span"}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string|void
 */
function smarty_insert_getstatusmsg($params, $view)
{
    // NOTE: assign parameter is handled by the smarty_core_run_insert_handler(...) function in lib/vendor/Smarty/internals/core.run_insert_handler.php

    $class  = isset($params['class'])   ? $params['class']   : null;
    $style  = isset($params['style'])   ? $params['style']   : null;
    $tag    = isset($params['tag'])     ? $params['tag']     : null;

    //prepare output var
    $output = '';

    // $msgStatus = LogUtil::getStatusMessages();
    // we do not use LogUtil::getStatusMessages() because we need to know if we have to
    // show a status or an error
    $session = $view->getServiceManager()->getService('session');
    $msgStatus = $session->getMessages(Zikula_Session::MESSAGE_STATUS);
    $msgtype   = ($class ? $class : 'z-statusmsg');
    $session->clearMessages(Zikula_Session::MESSAGE_STATUS);
    $msgError = $session->getMessages(Zikula_Session::MESSAGE_ERROR);
    $session->clearMessages(Zikula_Session::MESSAGE_ERROR);

    // Error message overrides status message
    if (!empty($msgError)) {
        $msgStatus = $msgError;
        $msgtype   = ($class ? $class : 'z-errormsg');
    }

    if (empty($msgStatus) || count($msgStatus)==0) {
        return $output;
    }

    // some parameters have been set, so we build the complete tag
    if (!$tag || $tag != 'span') {
        $tag = 'div';
    }

    // need to build a proper error message from message array
    $output = '<' . $tag . ' class="' . $msgtype . '"';
    if ($style) {
        $output .= ' style="' . $style . '"';
    }

    $output .= '>';
    $output .= implode ('<hr />', $msgStatus);
    $output .= '</' . $tag . '>';

    return $output;
}

