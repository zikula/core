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
 * Retrieve an HTML unordered list of the categories assigned to a specified item.
 *
 * The assigned categories are retrieved from $item['__CATEGORIES__'] (DBUtil) or  $item['Categories'] (Doctrine).
 * However, if we are using Doctrine 2, the categories are passed as param.
 *
 * Available attributes:
 *  - item  (array) The item from which to retrieve the assigned categories.
 * or
 *  - categories  (object) The item's categories.
 *  - doctrine2   (boolean) true or false if using doctrine2 or not.
 *
 * Example:
 *
 * <samp>{assignedcategorieslist item=$myVar}</samp>
 * <samp>{assignedcategorieslist categories=$myCategories doctrine2=true}</samp>
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
    if (isset($params['doctrine2']) && (bool)$params['doctrine2'] == true) {
        if (!isset($params['categories'])) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('assignedcategorieslist', 'categories')));

            return false;
        }
    } elseif (!isset($params['item'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('assignedcategorieslist', 'item')));

        return false;
    }

    $lang = ZLanguage::getLanguageCode();

    $result = "<ul>\n";

    if (isset($params['doctrine2']) && (bool)$params['doctrine2'] == true) {
        if (count($params['categories']) > 0) {
            foreach ($params['categories'] as $category) {
                if (!is_object($category)) {
                    continue;
                }
                if ($category instanceof \Zikula\Core\Doctrine\Entity\AbstractEntityCategory) {
                    if (!is_object($category->getCategory())) {
                        continue;
                    }
                    $name = $category->getCategory()->getName();
                    $display_name = $category->getCategory()->getDisplay_name();
                } elseif ($category instanceof \Zikula\CategoriesModule\Entity\CategoryEntity) {
                    $name = $category->getName();
                    $display_name = $category->getDisplay_name();
                }

                if (isset($display_name[$lang]) && !empty($display_name[$lang])) {
                    $result .= "<li>\n" . $display_name[$lang] . "</li>\n";
                } elseif (isset($name) && !empty($name)) {
                    $result .= "<li>\n" . $name . "</li>\n";
                }
            }
        } else {
            $result .= '<li>' . DataUtil::formatForDisplay(__('No assigned categories.')) . '</li>';
        }
    } else {
        if (isset($params['item']['Categories']) && !empty($params['item']['Categories'])) {
            $categories = $params['item']['Categories'];
        } elseif (isset($params['item']['__CATEGORIES__']) && !empty($params['item']['__CATEGORIES__'])) {
            $categories = $params['item']['__CATEGORIES__'];
        } else {
            $categories = array();
        }

        if (!empty($categories)) {
            foreach ($categories as $property => $category) {
                if (isset($category['Category'])) {
                    $category = $category['Category'];
                }
                $result .= '';
                if (isset($category['display_name'][$lang])) {
                    $result .= "<li>\n" . $category['display_name'][$lang] . "</li>\n";
                } elseif (isset($category['name'])) {
                    $result .= "<li>\n" . $category['name'] . "</li>\n";
                }
            }
        } else {
            $result .= '<li>' . DataUtil::formatForDisplay(__('No assigned categories.')) . '</li>';
        }
    }

    $result .= "</ul>\n";

    return $result;
}
