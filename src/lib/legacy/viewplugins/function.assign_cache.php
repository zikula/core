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
 * Assign a value caching its parameters if cache is enabled.
 *
 * Available attributes:
 *  - var    (string) The template variable to assign
 *  - value  (mixed)  The value to assign
 *
 * Example:
 *
 *  Having an $obj loaded from the DB, use assign_cache to cache some of its values,
 *  and use it later safely, even inside a cached template:
 *
 *  <samp>{assign_cache var='author' value=$obj.cr_uid}</samp>
 *
 *  And use that cached value later in another plugin:
 *
 *  <samp>{useravatar uid=$author}</samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return void
 */
function smarty_function_assign_cache($params, Zikula_View $view)
{
    if (!isset($params['var'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('assign_cache', 'var')));

        return false;
    }

    if (!isset($params['value'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('assign_cache', 'value')));

        return false;
    }

    $view->assign($params['var'], $params['value']);

    return;
}
