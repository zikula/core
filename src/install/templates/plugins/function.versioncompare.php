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

function smarty_function_versioncompare($params, &$smarty)
{
    if (!isset($params['minversion'])) {
        return false;
    }

    // check if version is sufficient
    if (version_compare(phpversion(), $params['minversion'], ">=")) {
        $return = true;
    } else {
        $return = false;
    } 

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $return);
    } else {
        return $return;
    }
}
