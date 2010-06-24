<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Render
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * DateFormat.
 * 
 * Params can be:
 *  format   The date format we wish to convert to (optional) (default='Y-m-d').
 *  datetime The datetime we wish to convert.
 *  assign   The smarty variable we wish to assign the result to (optional).
 * 
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the Smarty object.
 * 
 * @return string
 */
function smarty_function_dateformat($params, &$smarty)
{
    if (!isset($params['datetime'])) {
        $params['datetime'] = null;
    }

    if (!isset($params['format']) || empty($params['format'])) {
        $params['format'] = null;
    }

    $res = DateUtil::getDatetime($params['datetime'], $params['format']);

    if (isset($params['assign']) && $params['assign']) {
        $smarty->assign($params['assign'], $res);
    } else {
        return $res;
    }
}