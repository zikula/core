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
 * Zikula_View modifier for filter hooks.
 *
 * Available parameters:
 *   - modname:  Name of the calling module.
 * Example
 *
 *   {$foo|callfilterhooks}
 *
 * @param mixed  $string     The contents to filter.
 * @param string $moduleName Module name.
 *
 * @return string The modified output.
 */
function smarty_modifier_callfilterhooks($string, $moduleName=null)
{
    $moduleName = is_null($moduleName) ? ModUtil::getName() : $moduleName;
    $event = new Zikula_Event("$moduleName.display.filter", null, array('module' => $moduleName), $string);
    return EventUtil::getManager()->notify($event)->getData();
}
