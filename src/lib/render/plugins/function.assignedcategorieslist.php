<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to return the html code for opentable() located in theme.php.
 *
 * @param array $params All attributes passed to this function from the template
 * @param  object &$smarty Reference to the Smarty object
 * @return string  the html code for opentable() located in theme.php
 */
function smarty_function_assignedcategorieslist($params, &$smarty)
{
    if (!isset($params['item'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('assignedcategorieslist', 'item')));
        return false;
    }

    $lang = ZLanguage::getLanguageCode();

    $result = "<ul>\n";
    if (!empty($params['item']['__CATEGORIES__'])) {
        foreach ($params['item']['__CATEGORIES__'] as $category) {
            $result .= "<li>\n";
            if (isset($category['display_name'][$lang])) {
                $result .= $category['display_name'][$lang];
            } else {
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
