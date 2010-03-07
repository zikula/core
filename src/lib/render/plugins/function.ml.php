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
 * Smarty function to read a Zikula language constant.
 *
 * This function takes a identifier and returns the corresponding language constant.
 *
 * Available parameters:
 *   - name:            Name of the language constant to return
 *   - html:            Treat the language define as HTML
 *   - assign:          If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 * In the lang file:
 * define('_EXAMPLESTRING', 'Hello World')
 *
 * In the template:
 * <!--[pnml name='_EXAMPLEDEFINE']--> returns Hello World
 *
 * _EXAMPLESTRING = 'There are %u% users online';
 *  $usersonline = 10
 * <!--[pnml name='_EXAMPLEDEFINE' u=$usersonline]--> returns There are 10 users online
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        string      assign       The smarty variable to assign the resulting menu HTML to
 * @param        string      noprocess    If set the resulting string constant is not processed
 * @return       string      the language constant
 */
function smarty_function_ml($params, &$smarty)
{
    $assign          = isset($params['assign'])          ? $params['assign']          : null;
    $name            = isset($params['name'])            ? $params['name']            : null;

    if (!$name) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pnml', 'name')));
        return false;
    }

    $result = constant($name);
    if (isset($params['html'])) {
        $result = DataUtil::formatForDisplayHTML($result);
    }

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}
