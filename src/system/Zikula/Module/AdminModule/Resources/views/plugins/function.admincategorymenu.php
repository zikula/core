<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @copyright Zikula Foundation
 * @package Zikula
 * @subpackage ZikulaAdminModule
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to display the category menu for admin links. This also adds the
 * navtabs.css to the page vars array for stylesheets.
 *
 * Admin
 * {admincategorymenu}
 *
 * @see          function.admincategorymenu.php::smarty_function_admincategorymenu()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      $view        Reference to the Zikula_View object
 * @return       string      the results of the module function
 */
function smarty_function_admincategorymenu($params, $view)
{
    PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet('ZikulaAdminModule'));

    $modinfo = ModUtil::getInfoFromName($view->getTplVar('toplevelmodule'));

    $acid = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getmodcategory', array('mid' => $modinfo['id']));

    return ModUtil::func('ZikulaAdminModule', 'admin', 'categorymenu', array('acid' => $acid));
}
