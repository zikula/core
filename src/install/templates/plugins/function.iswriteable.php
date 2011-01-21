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
function smarty_function_iswriteable($params, &$smarty)
{
    if (!isset($params['file'])) {
        return false;
    }
    $file = $params['file'];

    $result = is_writable($file);
    
    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
