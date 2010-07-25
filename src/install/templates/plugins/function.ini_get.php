<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

function smarty_function_ini_get($params, &$smarty)
{
    if (!isset($params['varname'])) {
        $smarty->trigger_error("ini_get: parameter 'varname' required");
        return false;
    }

    // check if the file exists
    $result = ini_get($params['varname']);

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
