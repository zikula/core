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
 * Zikula_View function to obtain status message
 *
 * This function obtains the last status message posted for this session.
 * The status message exists in one of two session variables: '_ZStatusMsg' for a
 * status message, or '_ZErrorMsg' for an error message. If both a status and an
 * error message exists then the error message is returned.
 *
 * This is is a destructive function - it deletes the two session variables
 * '_ZStatusMsg' and 'erorrmsg' during its operation.
 *
 * Note that you must not cache the outputs from this function, as its results
 * change aech time it is called. The Zikula developers are looking for ways to
 * automise this.
 *
 *
 * Available parameters:
 *   - assign:   If set, the status message is assigned to the corresponding variable instead of printed out
 *   - style, class: If set, the status message is being put in a div tag with the respective attributes
 *   - tag:      You can specify if you would like a span or a div tag
 *
 * Example
 *   {getstatusmsg}
 *   {getstatusmsg style='color:red;'}
 *   {getstatusmsg class='statusmessage' tag='span'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @todo prevent this function from being cached
 * @deprecated
 *
 * @return string|void The value of the last status message posted, or void if no status message exists.
 */
function smarty_function_getstatusmsg($params, Zikula_View $view)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated, please use {%2$s} instead.', array('getstatusmsg', 'insert name="getstatusmsg"')), E_USER_DEPRECATED);

    $assign = isset($params['assign'])  ? $params['assign']  : null;
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

    if ($assign) {
        $view->assign($assign, $msgStatus);

        return;
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
