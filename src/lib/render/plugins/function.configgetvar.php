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
 * Obtain and display a configuration variable from the Zikula system.
 *
 * Available attributes:
 *  - name      (string)    The name of the configuration variable to obtain
 *  - html      (bool)      If set, the output is prepared for display by
 *                          DataUtil::formatForDisplayHTML instead of
 *                          DataUtil::formatForDisplay
 *  - assign    (string)    the name of a template variable to assign the
 *                          output to, instead of returning it to the template. (optional)
 *
 * <i>Note that if the the result is assigned to a template variable, it is not
 * prepared for display by either DataUtil::formatForDisplayHTML or
 * DataUtil::formatForDisplay. If it is to be displayed, the varprepfordisplay
 * or varprephtmldisplay should be used.</i>
 *
 * Examples:
 *
 * <samp><p>Welcome to {pnconfiggetvar name='sitename'}!</p></samp>
 *
 * <samp>{pnconfiggetvar name='sitename' assign='thename'}</samp><br>
 * <samp><p>Welcome to {$thename|varprepfordisplay}!</p></samp>
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the {@link Renderer} object.
 *
 * @return mixed The value of the configuration variable.
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