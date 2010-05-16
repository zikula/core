<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * DateFormat
 *
 * @param      format          The date format we wish to convert to (optional) (default='Y-m-d')
 * @param      datetime        The datetime we wish to convert
 * @param      assign          The smarty variable we wish to assign the result to (optional)
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