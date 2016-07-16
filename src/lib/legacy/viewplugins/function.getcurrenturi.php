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
 * Zikula_View function to obtain current URI
 *
 * This function obtains the current request URI.
 * Unlike the API function getcurrenturi, the results of this function are already
 * sanitized to display, so it should not be passed to the safetext modifier.
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - and any additional ones to override for the current request
 *
 * Example
 *   {getcurrenturi}
 *   {getcurrenturi lang='de'}
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string The current URI
 */
function smarty_function_getcurrenturi($params, Zikula_View $view)
{
    $assign = null;
    if (isset($params['assign'])) {
        $assign = $params['assign'];
        unset($params['assign']);
    }

    $result = htmlspecialchars(System::getCurrentUri($params));

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}
