<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package      Zikula_System_Modules
 * @subpackage   Zikula_Admin
 */


/**
 * Smarty function to display the category menu for admin links. This also adds the
 * navtabs.css to the page vars array for stylesheets.
 *
 * Admin
 * <!--[admincategorymenu]-->
 *
 * @author       Frank Schummertz
 * @since        16.01.2005
 * @see          function.admincategorymenu.php::smarty_function_admincategoreymenu()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      $smarty     Reference to the Smarty object
 * @return       string      the results of the module function
 */
function smarty_function_admincategorymenu($params, $smarty)
{
    PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet('Admin'));

    $acid = ModUtil::apiFunc('Admin', 'admin', 'getmodcategory', array('mid' => $smarty->modinfo['id']));
    
    return ModUtil::func('Admin', 'admin', 'categorymenu', array('acid' => $acid));
}
