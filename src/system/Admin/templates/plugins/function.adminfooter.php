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
 * Smarty function to close the admin container.
 *
 * Admin
 * {adminfooter}
 *
 * @see          function.adminfooter.php::smarty_function_adminfooter()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      $view        Reference to the Zikula_View object
 * @return       string      the results of the module function
 */
function smarty_function_adminfooter($params, $view)
{
    return ModUtil::func('Admin', 'admin', 'adminfooter');
}
