<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Render
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to check for the availability of a module
 *
 * This function calls ModUtil::isHooked to determine if two Zikula modules are
 * hooked together. True is returned if the modules are hooked, false otherwise.
 * The result can also be assigned to a template variable.
 *
 * Available parameters:
 *   - tmodname:  The well-known name of the hook module
 *   - smodname:  The well-known name of the calling module
 *   - assign:    The name of a variable to which the results are assigned
 *
 * Examples
 *   {modishooked tmodname='Ratings' smodname='News'}
 *
 *   {modishooked tmodname='bar' smodname='foo' assign='barishookedtofoo'}
 *   {if $barishookedtofoo}.....{/if}
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the Smarty object.
 *
 * @see    function.modishooked.php::smarty_function_modishooked()
 * @return boolean True if the module is available; false otherwise.
 */
function smarty_function_modishooked($params, &$smarty)
{
    $assign   = isset($params['assign'])   ? $params['assign']   : null;
    $smodname = isset($params['smodname']) ? $params['smodname'] : null;
    $tmodname = isset($params['tmodname']) ? $params['tmodname'] : null;

    if (!$tmodname) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('modishooked', 'tmodname')));
        return false;
    }

    if (!$smodname) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('modishooked', 'smodname')));
        return false;
    }

    $result = ModUtil::isHooked($tmodname, $smodname);

    if ($assign) {
        $smarty->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
