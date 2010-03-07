<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty insert function to dynamically get current status/error message
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
 *   <!--[insert name="getstatusmsg"]-->
 *   <!--[insert name="getstatusmsg" style="color:red;"]-->
 *   <!--[insert name="getstatusmsg" class="statusmessage" tag="span"]-->
 *
 * @param $params
 * @param $smarty
 * @return string
 */
function smarty_insert_getstatusmsg($params, &$smarty)
{
    $assign = isset($params['assign'])  ? $params['assign']  : null;
    $class  = isset($params['class'])   ? $params['class']   : null;
    $style  = isset($params['style'])   ? $params['style']   : null;
    $tag    = isset($params['tag'])     ? $params['tag']     : null;

    //prepare output var
    $output = '';

    // $msgStatus = pnGetStatusMsg();
    // we do not use pnGetStatusMsg() because we need to know if we have to
    // show a status or an error
    $msgStatus = SessionUtil::getVar('_ZStatusMsg');
    $msgtype   = ($class ? $class : 'z-statusmsg');
    SessionUtil::delVar('_ZStatusMsg');

    $msgError = SessionUtil::getVar('_ZErrorMsg');
    SessionUtil::delVar('_ZErrorMsg');
    // Error message overrides status message
    if (!empty($msgError)) {
        $msgStatus = $msgError;
        $msgtype   = ($class ? $class : 'z-errormsg');
    }

    if ($assign) {
        $smarty->assign($assign, $msgStatus);
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

