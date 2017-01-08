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
 * Smarty function to display the category menu for admin links. This also adds the
 * navtabs.css to the page vars array for stylesheets.
 *
 * Admin
 * {admincategorymenu}
 *
 * @deprecated remove at Core-2.0
 * @see          function.admincategorymenu.php::smarty_function_admincategorymenu()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        \Zikula_View $view        Reference to the Zikula_View object
 * @return       string      the results of the module function
 */
function smarty_function_admincategorymenu($params, \Zikula_View $view)
{
    PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet('ZikulaAdminModule'));

    $modinfo = ModUtil::getInfoFromName($view->getTplVar('toplevelmodule'));
    $acid = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getmodcategory', ['mid' => $modinfo['id']]);

    $path = ['_controller' => 'ZikulaAdminModule:Admin:categorymenu', 'acid' => $acid];
    $subRequest = $view->getRequest()->duplicate([], null, $path);

    return $view->getContainer()
        ->get('http_kernel')
        ->handle($subRequest, \Symfony\Component\HttpKernel\HttpKernelInterface::SUB_REQUEST)
        ->getContent();
}
