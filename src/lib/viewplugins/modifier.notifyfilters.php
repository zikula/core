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
 *   - eventName:  Name of the event.
 *
 * Example
 *   {$foo|notifyfilters:'news.filterhook.articles'}
 *
 * @param string      $string    The contents to filter.
 * @param string      $eventName The contents to filter.
 * @param Zikula_View $view      Zikula_View instance (added automatically).
 *
 * @return string The modified output.
 */
function smarty_modifier_notifyfilters($string, $eventName, $view)
{
    $event = new Zikula_Event($eventName, $view, array('caller' => $view->getTopLevelModule()), $string);
    return EventUtil::getManager()->notify($event)->getData();
}
