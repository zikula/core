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
 * Wrapper for ModUtil::isCapable().
 *
 * Param takes 'modules' and 'capability' keys.
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string Translation if it was available.
 */
function smarty_function_iscapable($params, Zikula_View $view)
{
    if (!isset($params['module'])) {
        $view->trigger_error(__('Error! "module" parameter must be specified.'));
    }
    if (!isset($params['capability'])) {
        $view->trigger_error(__('Error! "module" parameter must be specified.'));
    }

    $result = ModUtil::isCapable($module, $params['capability']);

    // assign or return
    if (isset($params['assign'])) {
        $view->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
