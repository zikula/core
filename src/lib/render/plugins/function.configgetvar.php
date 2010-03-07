<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to get configuration variable
 *
 * This function obtains a configuration variable from the Zikula system.
 *
 * Note that the results should be handled by the pnvarprepfordisplay or the
 * pnvarprephtmldisplay modifier before being displayed.
 *
 *
 * Available parameters:
 *   - name:     The name of the config variable to obtain
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 *   Welcome to <!--[pnconfiggetvar name='sitename']-->!
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        bool        $html        (optional) If true then result will be treated as html content
 * @param        string      $assign      (optional) If set then result will be assigned to this template variable
 * @param        string      $default     (optional) The default value to return if the config variable is not set
 * @return       string      the value of the last status message posted, or void if no status message exists
 */
function smarty_function_configgetvar($params, &$smarty)
{
    $name      = isset($params['name'])    ? $params['name']    : null;
    $default   = isset($params['default']) ? $params['default'] : null;
    $html      = isset($params['html'])    ? $params['html']    : null;
    $assign    = isset($params['assign'])  ? $params['assign']  : null;

    if (!$name) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pnconfiggetvar', 'name')));
        return false;
    }

    $result = pnConfigGetVar($name, $default);

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        if (is_bool($html) && $html) {
            return DataUtil::formatForDisplayHTML($result);
        } else {
            return DataUtil::formatForDisplay($result);
        }
    }
}
