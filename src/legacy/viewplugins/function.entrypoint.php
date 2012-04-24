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
 * Zikula_View function to return the entry point to the site as configured in the settings
 *
 * This function returns the value of the config var entrypoint
 *
 * Available parameters:
 *   - none
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The value of the config var entrypoint.
 */
function smarty_function_entrypoint($params, Zikula_View $view)
{
    return System::getVar('entrypoint', 'index.php');
}
