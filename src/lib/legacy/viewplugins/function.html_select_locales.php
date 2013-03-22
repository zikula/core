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
 * Zikula_View function to display a drop down list of languages
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - name:     Name for the control
 *   - id:       ID for the control
 *   - selected: Selected value
 *   - installed: if set only show languages existing in languages folder
 *   - all:      show dummy entry '_ALL' on top of the list with empty value
 *
 * Example
 *   {html_select_locales name=locale selected=en}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The value of the last status message posted, or void if no status message exists.
 */
function smarty_function_html_select_locales($params, Zikula_View $view)
{
    if (!isset($params['name']) || empty($params['name'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('html_select_locales', 'name')));

        return false;
    }

    require_once $view->_get_plugin_filepath('function','html_options');

    $values = $output = array();
    if (isset($params['all']) && $params['all']) {
        $values[] = '';
        $output[]= DataUtil::formatForDisplay(__('All'));
    }

    $installed = ZLanguage::getInstalledLanguageNames();
    $output = array_merge($output, DataUtil::formatForDisplay(array_values($installed)));
    $values = array_merge($values, DataUtil::formatForDisplay(array_keys($installed)));

    $html_result = smarty_function_html_options(array('output'       => $output,
                                                      'values'       => $values,
                                                      'selected'     => isset($params['selected']) ? $params['selected'] : null,
                                                      'id'           => isset($params['id']) ? $params['id'] : null,
                                                      'name'         => $params['name']),
                                                $view);

    if (isset($params['assign']) && !empty($params['assign'])) {
        $view->assign($params['assign'], $html_result);
    } else {
        return $html_result;
    }
}
