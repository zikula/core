<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2004, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Template_Plugins
 * @subpackage Functions
 */

/**
 * Smarty function to to execute a module API function
 *
 * This function calls a calls a specific module API function. It returns whatever the return
 * value of the resultant function is if it succeeds.
 * Note that in contrast to the API function ModUtil::apiFunc you need not to load the
 * module API with ModUtil::loadApi.
 *
 *
 * Available parameters:
 *   - modname:  The well-known name of a module to execute a function from (required)
 *   - type:     The type of function to execute; currently one of 'user' or 'admin' (default is 'user')
 *   - func:     The name of the module function to execute (default is 'main')
 *   - assign:   The name of a variable to which the results are assigned
 *   - all remaining parameters are passed to the module API function
 *
 * Examples
 *   <!--[ModUtil::apiFunc modname='News' type='user' func='get' sid='3']-->
 *
 *   <!--[ModUtil::apiFunc modname='foobar' type='user' func='getfoo' id='1' assign='myfoo']-->
 *   <!--[$myfoo.title]-->
 *
 *
 * @author       Andreas Stratmann
 * @author       Jörg Napp
 * @since        03/05/23
 * @see          function.pnmodfunc.php::smarty_function_modfunc()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      the results of the module API function
 */
function smarty_function_modapifunc($params, &$smarty)
{
    $saveDomain = $smarty->renderDomain;
    $assign  = isset($params['assign'])                  ? $params['assign']  : null;
    $func    = isset($params['func']) && $params['func'] ? $params['func']    : 'main';
    $modname = isset($params['modname'])                 ? $params['modname'] : null;
    $type    = isset($params['type']) && $params['type'] ? $params['type']    : 'user';

    // avoid passing these to ModUtil::apiFunc
    unset($params['modname']);
    unset($params['type']);
    unset($params['func']);
    unset($params['assign']);

    if (!$modname) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('ModUtil::apiFunc', 'modname')));
        return false;
    }

    if (isset($params['modnamefunc'])) {
        $params['modname'] = $params['modnamefunc'];
        unset($params['modnamefunc']);
    }

    $result = ModUtil::apiFunc($modname, $type, $func, $params);

    // ensure the renderDomain wasnt overwritten
    $smarty->renderDomain = $saveDomain;

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}
