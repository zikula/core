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
 * Get field marker.
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - field:    The field for which we wish to get the field marker
 *   - validationErrors: the validation errors
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_function_formutil_getfieldmarker($params, Zikula_View $view)
{
    // allow empty validation info -> return nothing
    if (!isset($params['validation'])) {
        return '';
    }

    $marker = FormUtil::getFieldMarker($params['objectType'], $params['validation'], $params['field']);

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $marker);
    } else {
        return $marker;
    }
}
