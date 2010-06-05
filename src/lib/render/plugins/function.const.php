<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty plugin to convert string to PHP constant (required to support
 * class constants
 *
 * Example
 *
 *   {const name="ModUtil::TYPE_SYSTEM"}
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        string      assign       The smarty variable to assign the resulting menu HTML to
 * @param        string      noprocess    If set the resulting string constant is not processed
 * @return       string      the language constant
 */
function smarty_function_const($params, &$smarty)
{
    $assign          = isset($params['assign'])          ? $params['assign']          : null;
    $name            = isset($params['name'])            ? $params['name']            : null;

    if (!$name) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('const', 'name')));
        return false;
    }

    $result = constant($name);
    
    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}
