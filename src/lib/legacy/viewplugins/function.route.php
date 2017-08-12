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
 * Zikula_View function to create a compatible route for a specific module function.
 *
 * NOTE: This function only works for modules using the Core 1.4.0+ routing specification
 *
 * This function returns a module route string if successful. This is already sanitized to display,
 * so it should not be passed to the safetext modifier.
 *
 * Available parameters:
 *   - name: the route name e.g. `acmewidgetmakermodule_user_construct`
 *   - absolute: whether to generate an absolute URL
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - all remaining parameters are passed to the generator as route parameters
 *
 * Example
 * Create a route to the News 'view' function with parameters 'sid' set to 3
 *   <a href="{route name='zikulanewsmodule_user_display' sid='3'}">Link</a>
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string The route or empty
 */
function smarty_function_route($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;
    unset($params['assign']);
    $name = isset($params['name']) ? $params['name'] : false;
    unset($params['name']);
    $absolute = isset($params['absolute']) ? $params['absolute'] : false;
    unset($params['absolute']);

    /** @var $router \JMS\I18nRoutingBundle\Router\I18nRouter */
    $router = $view->getContainer()->get('router');

    try {
        $route = $router->generate($name, $params, $absolute);
    } catch (Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
        $route = ''; // route does not exist
    }

    if ($assign) {
        $view->assign($assign, $route);
    } else {
        return DataUtil::formatForDisplay($route);
    }
}
