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
 * Smarty function to set a session variable
 *
 * This function sets a session-specific variable in the Zikula system.
 *
 * Note that the results should be handled by the safetext or the safehtml
 * modifier before being displayed.
 *
 *
 * Available parameters:
 *   - name:    The name of the session variable to obtain
 *   - value:   The value for the session variable
 *   - assign:  If set, the result is assigned to the corresponding variable instead of printed out
 *
 * Example
 *   {sessionsetvar name='foo' value='bar'}
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Smarty object
 * @param string      $name   The name of the session variable to obtain
 *
 * @return mixed
 */
function smarty_function_sessionsetvar($params, Zikula_View $view)
{
    $assign  = isset($params['assign'])  ? $params['assign']  : null;
    $name    = isset($params['name'])    ? $params['name']    : null;
    $value   = isset($params['value'])   ? $params['value']   : null;
    $path    = isset($params['path'])    ? $params['path']    : '/';

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('sessionsetvar', 'name')));
        return false;
    }

    if (!$value) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('sessionsetvar', 'value')));
        return false;
    }

    $result = $view->getRequest()->getSession()->set($name, $value, $path);

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}
