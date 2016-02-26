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
 *   - eventName:  Name of the hook event.
 *
 * Example
 *   {$foo|notifyfilters:'news.filterhook.articles'}
 *
 * @param string      $content   The contents to filter.
 * @param string      $eventName Hook event name.
 *
 * @return string The modified output.
 */
function smarty_modifier_notifyfilters($content, $eventName)
{
    $hook = new Zikula_FilterHook($eventName, $content); // @todo Zikula_FilterHook maintains BC. In 1.5.0 change to \Zikula\Bundle\HookBundle\Hook\FilterHook($content);

    return ServiceUtil::getManager()->get('hook_dispatcher')->dispatch($eventName, $hook)->getData();
}
