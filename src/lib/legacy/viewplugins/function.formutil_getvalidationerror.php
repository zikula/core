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
 * Get validation errors.
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - field:    The name of the field for which we wish to get the erorr
 *   - indent:   Wether or not to indent the validation error
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_function_formutil_getvalidationerror($params, Zikula_View $view)
{
    $error = FormUtil::getValidationError($params['objectType'], $params['field']);

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $error);
    } else {
        return $error;
    }
}
