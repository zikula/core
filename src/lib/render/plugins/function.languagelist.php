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
 * Smarty function to display a drop down list of languages.
 *
 * This plugin as been superceded by html_select_languages.
 *
 * @deprecated
 * @param array  $params  All attributes passed to this function from the template.
 * @param object &$smarty Reference to the Smarty object.
 *
 * @return string The value of the last status message posted, or void if no status message exists.
 */
function smarty_function_languagelist($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('function','html_select_languages');
    return smarty_function_html_select_languages($params, $smarty);
}
