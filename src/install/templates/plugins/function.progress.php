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

function smarty_function_progress($params, &$smarty)
{
    if (!isset($params['percent'])) {
        $percent = 0;
    } else {
        $percent = $params['percent'];
    }

    $progress = '<div class="progressbarcontainer"><div class="progress"><span class="bar" style="width:$percent%">';
    $progress .= $percent;
    $progress .= '%</span></div></div>';

    return $progress;
}
