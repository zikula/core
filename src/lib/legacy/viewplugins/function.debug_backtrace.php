<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
