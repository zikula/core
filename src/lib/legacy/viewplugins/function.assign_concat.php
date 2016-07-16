<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object
 *
 * @return void
 */
function smarty_function_assign_concat($params, Zikula_View $view)
{
    if (!isset($params['name']) || !$params['name']) {
        $view->trigger_error(__f('Invalid %1$s passed to %2$s.', ['name', 'assign_concat']));

        return false;
    }

    $txt = '';

    $i = 1;
    if (isset($params[$i])) {
        do {
            $txt .= "{$params[$i]}";
            $i++;
        } while (isset($params[$i]));
    }

    if (isset($params['html']) && $params['html']) {
        $view->assign($params['name'], DataUtil::formatForDisplayHTML($txt));
    } else {
        $view->assign($params['name'], $txt);
    }
}
