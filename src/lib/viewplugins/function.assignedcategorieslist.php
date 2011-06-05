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
 * Retrieve an HTML unordered list of the categories assigned to a specified item.
 *
 * The assigned categories are retrieved from $item['__CATEGORIES__'] (DBUtil) or  $item['Categories'] (Doctrine).
 *
 * Available attributes:
 *  - item  (array) The item from which to retrieve the assigned categories.
 *
 * Example:
 *
 * <samp>{assignedcategorieslist item=$myVar}</samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return string The HTML code for an unordered list containing the item's
 *                assigned categories. If no categories are assigned to the
 *                item, then the list will contain a single list-item (<li>)
 *                with a message to that effect.
 */
function smarty_function_assignedcategorieslist($params, Zikula_View $view)
{
    if (!isset($params['item'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('assignedcategorieslist', 'item')));
        return false;
    }

    $lang = ZLanguage::getLanguageCode();

    if (isset($params['item']['Categories']) && !empty($params['item']['Categories'])) {
        $categories = $params['item']['Categories'];
    } elseif (isset($params['item']['__CATEGORIES__']) && !empty($params['item']['__CATEGORIES__'])) {
        $categories = $params['item']['__CATEGORIES__'];
    } else {
        $categories = array();
    }

    $result = "<ul>\n";
    if (!empty($categories)) {
        foreach ($categories as $property => $category) {
            if (isset($category['Category'])) {
                $category = $category['Category'];
            }
            $result .= "<li>\n";
            if (isset($category['display_name'][$lang])) {
                $result .= $category['display_name'][$lang];
            } else if (isset($category['name'])) {
                $result .= $category['name'];
            }
            $result .= "</li>\n";
        }
    } else {
        $result .= '<li>' . DataUtil::formatForDisplay(__('No assigned categories.')) . '</li>';
    }
    $result .= "</ul>\n";

    return $result;
}