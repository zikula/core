<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */


function smarty_function_formcontextmenureference($params, &$render)
{
    $output = $render->registerPlugin('Form_Plugin_ContextMenu_Reference', $params);
    if (array_key_exists('assign', $params)) {
        $render->assign($params['assign'], $output);
    } else {
        return $output;
    }
}
