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
 * Concatenate several values together and assign the resultant string to a template variable.
 *
 * Available attributes:
 *  - 1..10 (string)    The 1st through 10th value(s) we wish to assign
 *  - name  (string)    The name of the template variable to which the
 *                      concatenated string will be assigned
 *  - html  (bool)      (optional) If the specified value(s) contain HTML,
 *                      this should be set to true (or 1)
 *
 * Examples:
 *
 *  Concatenate the template variables $myVar1, $myVar2 and $myVar2 and store
 *  the resultant string in the template variable $myString:
 *
 *  <samp>{assign_concat name='myString' 1=$myVar1 2=$myVar2 3=$myVar3}</samp>
 *
 *  Concatenate the template variables $myVar1, $myVar2 and $myVar2 and store
 *  the resultant string in the template variable $myString. The string contains
 *  HTML, therefore it is passed through DataUtil::formatForDisplayHTML:
 *
 *  <samp>{assign_concat name='myString' 1=$myVar1 2=$myVar2 3=$myVar3 html=true}</samp>
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the {@link Renderer} object.
 *
 * @return Void
 */
function smarty_function_assign_concat($params, &$smarty)
{
    if (!$params['name']) {
        $smarty->trigger_error(__f('Invalid %1$s passed to %2$s.', array('name', 'assign_concat')));
        return false;
    }

    $txt = '';
    for ($i=1; $i<10; $i++) {
        $txt .= isset($params[$i]) ? $params[$i] : '';
    }

    if (isset($params['html']) && $params['html']) {
        $smarty->assign($params['name'], DataUtil::formatForDisplayHTML($txt));
    } else {
        $smarty->assign($params['name'], $txt);
    }
    return;
}