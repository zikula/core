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
 * Zikula_View function to display a drop down list of themes.
 *
 * Available parameters:
 *   - name:     Name for the control (optional) if not present then only the option tags are output
 *   - id:       ID for the control
 *   - selected: Selected value
 *   - filter:   Filter themes use (possible values: ThemeUtil::FILTER_ALL (default) ThemeUtil::FILTER_USER, ThemeUtil::FILTER_SYSTEM, ThemeUtil::FILTER_ADMIN
 *   - state:    Filter themes by state (possible values: ThemeUtil::STATE_ALL (default), ThemeUtil::STATE_ACTIVE, ThemeUtil::STATE_INACTIVE
 *   - type:     Filter themes by type (possible values: ThemeUtil::TYPE_ALL (default), ThemeUtil::TYPE_XANTHIA3
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Examples
 *
 *     {html_select_themes name=mytheme selected=mythemechoice}
 *
 *     <select name="mytheme">
 *         <option value="">{ml name=_DEFAULT}</option>
 *         {html_select_themes selected=$mythemechoice}
 *     </select>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The value of the last status message posted, or void if no status message exists.
 */
function smarty_function_html_select_themes($params, Zikula_View $view)
{
    if (!isset($params['filter']) || !defined($params['filter'])) {
        $filter = ThemeUtil::FILTER_ALL;
    } else {
        $filter = constant($params['filter']);
    }
    if (!isset($params['state']) || !defined($params['state'])) {
        $state = ThemeUtil::STATE_ALL;
    } else {
        $state = constant($params['state']);
    }
    if (!isset($params['type']) || !defined($params['type'])) {
        $type = ThemeUtil::TYPE_ALL;
    } else {
        $type = constant($params['type']);
    }

    $themelist = array();
    $themes = ThemeUtil::getAllThemes($filter, $state, $type);
    if (!empty($themes)) {
        foreach ($themes as $theme) {
            $themelist[$theme['name']] = $theme['displayname'];
        }
    }
    natcasesort($themelist);

    require_once $view->_get_plugin_filepath('function','html_options');
    $output = smarty_function_html_options(array('options'  => $themelist,
                                                 'selected' => isset($params['selected']) ? $params['selected'] : null,
                                                 'name'     => isset($params['name'])     ? $params['name']     : null,
                                                 'id'       => isset($params['id'])       ? $params['id']       : null),
                                                 $view);

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $output);
    } else {
        return $output;
    }
}
