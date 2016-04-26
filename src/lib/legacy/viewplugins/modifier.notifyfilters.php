<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
