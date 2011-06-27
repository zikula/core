<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Template plugin to display timezone list.
 *
 * Example {timezoneselect selected='Timezone'}.
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   The Zikula_View.
 *
 * @see   function.timezoneselect.php::smarty_function_timezoneselect().
 *
 * @return string The results of the module function.
 */
function smarty_function_timezoneselect($params, Zikula_View $view)
{
    require_once $view->_get_plugin_filepath('function', 'html_options');

    $timezones = DateUtil::getTimezones();
    if (!isset($params['selected']) || empty($params['selected']) || !isset($timezones[$params['selected']])) {
        $params['selected'] = System::getVar('timezone_offset');
    }

    return smarty_function_html_options(array('options' => $timezones, 'selected' => $params['selected'], 'print_result' => false), $view);
}
