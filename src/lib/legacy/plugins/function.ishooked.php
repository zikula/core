<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_View function to check for the availability of a module hook.
 *
 * Available parameters:
 *   - tmodname:  The well-known name of the hook module
 *   - smodname:  The well-known name of the calling module
 *   - assign:    The name of a variable to which the results are assigned
 *
 * Examples
 *   {ishooked tmodname='Ratings' smodname='News'}
 *
 *   {ishooked tmodname='bar' smodname='foo' assign='barishookedtofoo'}
 *   {if $barishookedtofoo}.....{/if}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @see    function.ishooked.php::smarty_function_ishooked()
 *
 * @return boolean True if the module is available; false otherwise.
 */
function smarty_function_ishooked($params, $view)
{
    $assign   = isset($params['assign'])   ? $params['assign']   : null;
    $smodname = isset($params['smodname']) ? $params['smodname'] : null;
    $tmodname = isset($params['tmodname']) ? $params['tmodname'] : null;

    if (!$tmodname) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('modishooked', 'tmodname')));
        return false;
    }

    if (!$smodname) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('modishooked', 'smodname')));
        return false;
    }

    $result = ModUtil::isHooked($tmodname, $smodname);

    if ($assign) {
        $view->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
