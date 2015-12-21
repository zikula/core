<?php
/**
 * Copyright Zikula Foundation 2011 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
        $router->generate('zikulaadminmodule_admin_adminheader');
    } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
        $url = $view->getContainer()->get('router')->generate('zikularoutesmodule_route_reload', array('lct' => 'admin', 'confirm' => 1));

        return '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle fa-2x"></i> ' . __f('Routes must be reloaded. Click %s to reload all routes.', "<a href='$url'>" . __('here') . '</a>') . '</div>';
    }

    $path = array('_controller' => 'ZikulaAdminModule:Admin:adminheader');
    $subRequest = $view->getRequest()->duplicate(array(), null, $path);

    return $view->getContainer()
        ->get('http_kernel')
        ->handle($subRequest, \Symfony\Component\HttpKernel\HttpKernelInterface::SUB_REQUEST)
        ->getContent();
}
