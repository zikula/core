<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Render
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to return the entry point to the site as configured in the settings
 *
 * This function returns the value of the config var entrypoint
 *
 * Available parameters:
 *   - none
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the Smarty object.
 * 
 * @return string The value of the config var entrypoint.
 */
function smarty_function_entrypoint($params, &$smarty)
{
    return System::getVar('entrypoint', 'index.php');
}
