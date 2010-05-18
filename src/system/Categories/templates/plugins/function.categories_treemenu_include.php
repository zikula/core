<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Categories
 */
/**
 * Smarty function to display the generated menu tree data 
 * 
 * @author       Robert gasch
 * @since        01/11/2004
 * @see
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      the results of the module function
 */
function smarty_function_categories_treemenu_include ($params, &$smarty) 
{
//    PageUtil::addVar('stylesheet', 'javascript/phplayersmenu/layersmenu.css');
    PageUtil::addVar('stylesheet', 'javascript/phplayersmenu/layerstreemenu.css');
    PageUtil::addVar('javascript', 'javascript/phplayersmenu/libjs/layersmenu-browser_detection.js');
    PageUtil::addVar('javascript', 'javascript/phplayersmenu/libjs/layersmenu-library.js');
    PageUtil::addVar('javascript', 'javascript/phplayersmenu/libjs/layersmenu.js');
    PageUtil::addVar('javascript', 'javascript/phplayersmenu/libjs/layerstreemenu-cookies.js');
    return;
}
