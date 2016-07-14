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
 * Smarty function to open the admin container.
 *
 * Admin
 * {adminheader}
 *
 * @see          function.adminheader.php::smarty_function_adminheader()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        \Zikula_View $view        Reference to the Zikula_View object
 * @return       string      the results of the module function
 */
function smarty_function_adminheader($params, $view)
{
    // check to make sure adminmodule is available and route is available
    $router = $view->getContainer()->get('router');
    try {
        $router->generate('zikulaadminmodule_admininterface_header');
    } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
        $url = $view->getContainer()->get('router')->generate('zikularoutesmodule_route_reload', ['lct' => 'admin', 'confirm' => 1]);

        return '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle fa-2x"></i> ' . __f('Routes must be reloaded. Click %s to reload all routes.', '<a href="' . $url . '">' . __('here') . '</a>') . '</div>';
    }

    $path = ['_controller' => 'ZikulaAdminModule:AdminInterface:header'];
    $subRequest = $view->getRequest()->duplicate([], null, $path);

    return $view->getContainer()
        ->get('http_kernel')
        ->handle($subRequest, \Symfony\Component\HttpKernel\HttpKernelInterface::SUB_REQUEST)
        ->getContent();
}
