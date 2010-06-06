<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Category selector
 *
 * This plugin creates a category selector using a dropdown list.
 * The selected value of the base dropdown list will be set to ID of the selected category.
 *
 * @package pnForm
 * @subpackage Plugins
 */
function smarty_function_formcategoryselector($params, &$render)
{
    return $render->registerPlugin('Form_Plugin_CategorySelector', $params);
}
