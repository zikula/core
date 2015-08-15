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
 * Zikula_View function to display a drop down list of cache lifetime values.
 *
 * Available parameters:
 *   - name:     Name for the control (optional) if not present then only the option tags are output.
 *   - id:       ID for the control.
 *   - selected: Selected value
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Examples
 *
 *     {html_select_cache_lifetime name='cache_lifetime' selected='cache_lifetime'}
 *
 *     <select name="cache_lifetime">
 *         <option value="">{gt text='Select'}</option>
 *         {html_select_cache_lifetime selected=$cache_lifetime}
 *     </select>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The value of the last status message posted, or void if no status message exists.
 */
function smarty_function_html_select_cache_lifetime($params, Zikula_View $view)
{
    $options = array(
        -1 => __('Never Expire'),
        0 => __('Continually Regenerate'),
        1800 => __('Half-Hour'),
        3600 => __('One Hour'),
        7200 => __('Two Hours'),
        10800 => __('Three Hours'),
		14400 => __('Four Hours'),
		18000 => __('Five Hours'),
		21600 => __('Six Hours'),
		25200 => __('Seven Hours'),
		28800 => __('Eight Hours'),
		32400 => __('Nine Hours'),
		36000 => __('Ten Hours'),
		39600 => __('Eleven Hours'),
		43200 => __('Twelve Hours'),
		46800 => __('Thirteen Hours'),
        50400 => __('Fourteen Hours'),
		54000 => __('Fifteen Hours'),
		57600 => __('Sixteen Hours'),
		61200 => __('Seventeen Hours'),
		64800 => __('Eighteen Hours'),
		68400 => __('Nineteen Hours'),
		72000 => __('Twenty Hours'),
		75600 => __('Twenty-One Hours'),
		79200 => __('Twenty-Two Hours'),
		82800 => __('Twenty-Three Hours'),
		86400 => __('One Day'),
		604800	=> __('One Week'),
		2592000	=> __('One Month'),
		31449600 => __('One Year')
    );

    require_once $view->_get_plugin_filepath('function','html_options');
    
    $output = smarty_function_html_options(array(
        'id' => ((isset($params['id'])) ? $params['id'] : null),
        'name' => ((isset($params['name'])) ? $params['name'] : null),
        'options' => $options,
        'selected' => ((isset($params['selected'])) ? $params['selected'] : null)
    ), $view);

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $output);
    } else {
        return $output;
    }
}
