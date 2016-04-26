<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    $params['options'] = $timezones;
    if (!isset($params['selected']) || empty($params['selected']) || !isset($timezones[$params['selected']])) {
        $params['selected'] = System::getVar('timezone_offset');
    }
    $params['print_result'] = false;

    return smarty_function_html_options($params, $view);
}
