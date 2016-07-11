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
 *   {html_select_languages name=language selected=en}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @deprecated smarty_function_html_select_locales()
 * @return string The value of the last status message posted, or void if no status message exists.
 */
function smarty_function_html_select_languages($params, Zikula_View $view)
{
    if (!isset($params['name']) || empty($params['name'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['html_select_languages', 'name']));

        return false;
    }

    require_once $view->_get_plugin_filepath('function', 'html_options');

    $params['output'] = [];
    $params['values'] = [];
    if (isset($params['all']) && $params['all']) {
        $params['values'][] = '';
        $params['output'][] = DataUtil::formatForDisplay(__('All'));
        unset($params['all']);
    }

    if (isset($params['installed']) && $params['installed']) {
        $languagelist = ZLanguage::getInstalledLanguageNames();
        unset($params['installed']);
    } else {
        $languagelist = ZLanguage::languageMap();
    }

    $params['output'] = array_merge($params['output'], DataUtil::formatForDisplay(array_values($languagelist)));
    $params['values'] = array_merge($params['values'], DataUtil::formatForDisplay(array_keys($languagelist)));

    $assign = isset($params['assign']) ? $params['assign'] : null;
    unset($params['assign']);

    $html_result = smarty_function_html_options($params, $view);

    if (!empty($assign)) {
        $view->assign($assign, $html_result);
    } else {
        return $html_result;
    }
}
