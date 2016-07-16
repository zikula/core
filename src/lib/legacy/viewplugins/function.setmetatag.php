<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object
 *
 * @return void
 */
function smarty_function_setmetatag($params, Zikula_View $view)
{
    $name = isset($params['name']) ? $params['name'] : null;
    $value = isset($params['value']) ? $params['value'] : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['setmetatag', 'name']));

        return false;
    }

    $container = $view->getContainer();
    $metaTags = $container->hasParameter('zikula_view.metatags') ? $container->getParameter('zikula_view.metatags') : [];
    $metaTags[$name] = DataUtil::formatForDisplay($value);
    $container->setParameter('zikula_view.metatags', $metaTags);
}
