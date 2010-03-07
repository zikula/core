<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to display the welcome message
 *
 * Example
 * <!--[blockposition name=left]-->
 *
 * available parameters:
 * - name      name of the block position to display
 * - assign    if set, the title will be assigned to this variable
 * - implode   if set, the indiviual blocks in the position will be 'imploded' to a single string (default:true)
 *
 * @see          function.blockposition.php::smarty_function_blockposition()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        string      name         The name of the block position to render
 * @param        string      assign       Assign the output to template variable
 * @param        string      name         'implode' the output to a single string
 * @return       string      the output of the block position
 */
function smarty_function_blockposition($params, &$smarty)
{
    // fix the core positions for a better name
    if ($params['name'] == 'l') $params['name'] = 'left';
    if ($params['name'] == 'r') $params['name'] = 'right';
    if ($params['name'] == 'c') $params['name'] = 'center';

    if (!isset($params['name'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('blockposition', 'name')));
        return false;
    }

    $implode = (isset($params['implode']) && isset($params['assign'])) ? (bool)$params['implode'] : true;

    $return = pnBlockDisplayPosition($params['name'], false, $implode);
    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $return);
    } else {
        return $return;
    }
}
