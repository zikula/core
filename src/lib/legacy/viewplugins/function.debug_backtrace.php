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
 * Zikula_View function to generate a backtrace for debugging purposes.
 *
 * Available parameters:
 *   - fulltrace        include parts of stack trace after the call to the error handler -
 *                        by default these are excluded as they're not relevant.
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The URL.
 */
function smarty_function_debug_backtrace($params, Zikula_View $view)
{
    if (!isset($params['fulltrace'])) {
        return prayer(array_slice(debug_backtrace(), 8));
    } else {
        return prayer(debug_backtrace());
    }
}
