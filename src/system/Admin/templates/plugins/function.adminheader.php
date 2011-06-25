<?php
/**
 * Zikula Application Framework
 *
 * Copyright Zikula Foundation 2011 - Zikula Application Framework
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package      Zikula_System_Modules
 * @subpackage   Zikula_Admin
 */


/**
 * Smarty function to open the admin container.
 *
 * Admin
 * {adminheader}
 *
 * @see          function.adminheader.php::smarty_function_adminheader()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      $view        Reference to the Zikula_View object
 * @return       string      the results of the module function
 */
function smarty_function_adminheader($params, $view)
{
    return ModUtil::func('Admin', 'admin', 'adminheader');
}
