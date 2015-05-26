<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
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
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The route or empty.
 */
function smarty_function_route($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;
    unset($params['assign']);
    $name = isset($params['name']) ? $params['name'] : false;
    unset($params['name']);
    $absolute = isset($params['absolute']) ? $params['absolute'] : false;
    unset($params['absolute']);
    $params['_locale'] = isset($params['_locale']) ? $params['_locale'] : $view->language;

    /** @var $router \JMS\I18nRoutingBundle\Router\I18nRouter */
    $router = $view->getContainer()->get('router');
    $originalRouteCollection = $router->getOriginalRouteCollection()->all();
    if (array_key_exists($name, $originalRouteCollection)) {
        $route = $router->generate($name, $params, $absolute);
    } else {
        $route = ''; // route does not exist
    }

    if ($assign) {
        $view->assign($assign, $route);
    } else {
        return DataUtil::formatForDisplay($route);
    }
}
