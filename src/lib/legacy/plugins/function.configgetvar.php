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
 * DataUtil::formatForDisplay. If it is to be displayed, the safetext
 * modifier should be used.</i>
 *
 * Examples:
 *
 * <samp><p>Welcome to {configgetvar name='sitename'}!</p></samp>
 *
 * <samp>{configgetvar name='sitename' assign='thename'}</samp><br>
 * <samp><p>Welcome to {$thename|safetext}!</p></samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed The value of the configuration variable.
 */
function smarty_function_configgetvar($params, $view)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated.', array('configgetvar')), E_USER_DEPRECATED);

    $name      = isset($params['name'])    ? $params['name']    : null;
    $default   = isset($params['default']) ? $params['default'] : null;
    $html      = isset($params['html'])    ? $params['html']    : null;
    $assign    = isset($params['assign'])  ? $params['assign']  : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('configgetvar', 'name')));

        return false;
    }

    $result = System::getVar($name, $default);

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        if (is_bool($html) && $html) {
            return DataUtil::formatForDisplayHTML($result);
        } else {
            return DataUtil::formatForDisplay($result);
        }
    }
}
