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
 * Drop down list
 *
 * Renders an HTML <select> element with the supplied items.
 *
 * You can set the items directly like this:
 * <code>
 * {formdropdownlist id='mylist' items=$items}
 * </code>
 * with the form event handler code like this:
 * <code>
 * class mymodule_user_testHandler extends Zikula_Form_Handler
 * {
 *   function initialize($view)
 *   {
 *       $items = [
 *           ['text' => 'A', 'value' => '1'],
 *           ['text' => 'B', 'value' => '2'],
 *           ['text' => 'C', 'value' => '3']
 *       ];
 *
 *       $view->assign('items', $items); // Supply items
 *       $view->assign('mylist', 2);     // Supply selected value
 *   }
 * }
 * </code>
 * Or you can set them indirectly using the plugin's databased features:
 * <code>
 * {formdropdownlist id='mylist'}
 * </code>
 * with the form event handler code like this:
 * <code>
 * class mymodule_user_testHandler extends Zikula_Form_Handler
 * {
 *   function initialize($view)
 *   {
 *       $items = [
 *           ['text' => 'A', 'value' => '1'],
 *           ['text' => 'B', 'value' => '2'],
 *           ['text' => 'C', 'value' => '3']
 *       ];
 *
 *       $view->assign('mylistItems', $items);  // Supply items
 *       $view->assign('mylist', 2);            // Supply selected value
 *   }
 * }
 * </code>
 *
 * Selected index is zero based. Selected value is a string - and the PHP null
 * value is also a valid value.
 *
 * Option groups can be added by setting an 'optgroup' attribute on each item.
 * For instance:
 *
 * <code>
 * class mymodule_user_testHandler extends Zikula_Form_Handler
 * {
 *   function initialize($view)
 *   {
 *       $items = [
 *           ['text' => 'A', 'value' => '1', 'optgroup' => 'AAA'],
 *           ['text' => 'B', 'value' => '2', 'optgroup' => 'BBB'],
 *           ['text' => 'C', 'value' => '3', 'optgroup' => 'CCC']
 *       ];
 *
 *       $view->assign('mylistItems', $items);  // Supply items
 *       $view->assign('mylist', 2);            // Supply selected value
 *   }
 * }
 * </code>
 *
 * You can also encourage reuse of dropdown lists by inheriting from
 * the dropdown list into a specialized list a'la MyCategorySelector or
 * MyColorSelector, and then use this plugin where ever you want
 * a category or color selector. In this way you don't have to remember
 * to assign the items to the render every time you need such a selector.
 * In these plugins you must set the items in the load event handler.
 * See {@link Zikula_Form_Plugin_LanguageSelector} for a good example of how this
 * can be done.
 *
 * @param array            $params Parameters passed in the block tag
 * @param Zikula_Form_View $view   Reference to Zikula_Form_View object
 *
 * @return string The rendered output
 */
function smarty_function_formdropdownlist($params, $view)
{
    return $view->registerPlugin('Zikula_Form_Plugin_DropdownList', $params);
}
