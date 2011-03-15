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
