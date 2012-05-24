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
 * Set key in $metatags array.
 *
 * Available attributes:
 *  - name  (string) The name of the configuration variable to obtain
 *  - value (string) Value.
 *
 * Examples:
 *
 * <samp><p>Welcome to {setmetatag name='description' value='Description goes here}!</p></samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return void
 */
function smarty_function_setmetatag($params, Zikula_View $view)
{
    $name = isset($params['name']) ? $params['name'] : null;
    $value = isset($params['value']) ? $params['value'] : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('setmetatag', 'name')));

        return false;
    }

    $sm = $view->getServiceManager();
    $sm['zikula_view.metatags'][$name] = DataUtil::formatForDisplay($value);
}
