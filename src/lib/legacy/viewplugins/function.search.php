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
 * Zikula_View function to display a search form.
 *
 * Available parameters:
 *  - active            a comma-separated list of modules to be searched.
 *  - bool              the boolean operation to be performed (AND or OR)
 *  - label             the label to show before the search box
 *  - button            the text to be displayed on the search button
 *  - size              the size of the input box
 *  - value             the default value of the input box
 *  - search_accesskey  the access key of the Search box
 *  - search_tabindex   the tabindex of the Search box (defaults to 0)
 *  - search_class      the CSS class to assign to the Search box
 *  - button_accesskey  the access key of the Search button
 *  - button_class      the CSS class to assign to the Search button
 *  - button_tabindex   the tabindex of the Search button (defaults to 0)
 *  - class             the CSS class to assign to the form
 *  - js                use javascript to automatically clear the default value (defaults to true)
 *
 * Example
 * {search}
 *
 * {ml name="_SEARCH" assign="search_label"}
 * {search active="faqs, stories" label=$search_label class="pnsearchform"}
 *
 * Note
 * IE (incorrectly) treats a form as a block element rather than an inline
 * element. This, if you want the search box to display as expected on IE,
 * you should use a custom CSS class to style the search box (use the class
 * parameter of this plugin), and add
 *   display: inline;
 * to this class in your style sheet.
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string The search box
 */
function smarty_function_search($params, Zikula_View $view)
{
    // set some defaults
    $class            = (!isset($params['class']) || empty($params['class'])) ? '' : 'class="' . DataUtil::formatForDisplay($params['class']) .'"';
    $size             = (!isset($params['size']) || empty($params['size'])) ? 12 : $params['size'];
    $search_tabindex  =  (!isset($params['search_tabindex']) || empty($params['search_tabindex'])) ? 0 : (int)$params['search_tabindex'];
    $search_accesskey = (!isset($params['search_accesskey']) || empty($params['search_accesskey'])) ? '' : 'accesskey="'.DataUtil::formatForDisplay($params['search_accesskey']).'" ';
    $search_class     = (!isset($params['search_class']) || empty($params['search_class'])) ? '' : 'class="'.DataUtil::formatForDisplay($params['search_class']).'" ';
    $value            = (!isset($params['value']) || empty($params['value'])) ? '' : $params['value'];
    $js               = (!isset($params['js']) || empty($params['js'])) ? '' : ' onblur="if (this.value==\'\')this.value=\''.$params['value'].'\';" onfocus="if (this.value==\''.$params['value'].'\')this.value=\'\';"';
    $bool             = (!isset($params['bool']) || ('OR' != $params['bool'])) ? 'AND' : $params['bool'];
    $button_tabindex  =  (!isset($params['button_tabindex']) || empty($params['button_tabindex'])) ? 0 : (int)$params['button_tabindex'];
    $button_accesskey = (!isset($params['button_accesskey']) || empty($params['button_accesskey'])) ? '' : 'accesskey="'.DataUtil::formatForDisplay($params['button_accesskey']).'" ';
    $button_class     = (!isset($params['button_class']) || empty($params['button_class'])) ? '' : 'class="'.DataUtil::formatForDisplay($params['button_class']).'" ';

    // Staring the search box
    $searchbox  = '<form ' . $class . ' action="'.DataUtil::formatForDisplay(ModUtil::url('ZikulaSearchModule', 'user', 'search')).'" method="post">'."\n";
    $searchbox .= ' <div>'."\n";
    $searchbox .= '  <input type="hidden" name="overview" value="1" />'."\n";
    $searchbox .= '  <input type="hidden" name="bool" value="'.DataUtil::formatForDisplay($bool).'" />'."\n";

    // Loop through the active modules and assign them
    if (isset($params['active'])) {
        $active_modules = explode(',', $params['active']);
        foreach ($active_modules as $active_module) {
            $active_module = trim($active_module);
            $searchbox .= '  <input type="hidden" name="active_'.DataUtil::formatForDisplay($active_module).'" value="1" />'."\n";
        }
    }

    if (isset($params['label'])) {
        $searchbox .= '  <label for="search_plugin_q">'.DataUtil::formatForDisplay($params['label']).'</label>'."\n";
    }

    $searchbox .= '  <input id="search_plugin_q" type="text" name="q" value="'.$value.'" size="'.DataUtil::formatForDisplay($size).'" tabindex="'.DataUtil::formatForDisplay($search_tabindex).'" '.$search_class.$search_accesskey.$js.' />'."\n";

    if (isset($params['button'])) {
        $searchbox .= '  <input type="submit" value="'.DataUtil::formatForDisplay($params['button']).'" tabindex="'.DataUtil::formatForDisplay($button_tabindex).'"'.$button_accesskey.$button_class.' />'."\n";
    }

    $searchbox .= ' </div>'."\n";
    $searchbox .= '</form>'."\n";

    return $searchbox;
}
