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
 * Language selector
 *
 * This plugin creates a language selector using a dropdown list.
 * The selected value of the base dropdown list will be set to the 3-letter language code of
 * the selected language.
 *
 * @package pnForm
 * @subpackage Plugins
 */
function smarty_function_formlanguageselector($params, &$render)
{
    return $render->registerPlugin('Form_Plugin_LanguageSelector', $params);
}
