<?php
/**
 * Zikula Application Framework
 *
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package      Zikula_System_Modules
 * @subpackage   Zikula_Admin
 */


/**
 * Smarty function to display the category menu for admin links. This also adds the
 * navtabs.css to the page vars array for stylesheets.
 *
 * Admin
 * {admincategorymenu}
 *
 * @see          function.admincategorymenu.php::smarty_function_admincategoreymenu()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      $view        Reference to the Zikula_View object
 * @return       string      the results of the module function
 */
function smarty_function_admincategorymenu($params, $view)
{
    PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet('Admin'));

    $modinfo = ModUtil::getInfoFromName($view->getTplVar('toplevelmodule'));

    $acid = ModUtil::apiFunc('Admin', 'admin', 'getmodcategory', array('mid' => $modinfo['id']));

    return ModUtil::func('Admin', 'admin', 'categorymenu', array('acid' => $acid));
}
