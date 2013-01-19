<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Categories;

class GenericUtil
{
    /**
     * Validate the data for a category
     *
     * @param array $data   The data for the category.
     *
     * @return boolean true/false Whether the provided data is valid.
     */
    public static function validateCategoryData($data)
    {
        $view = \Zikula_View::getInstance();

        if (empty($data['name'])) {
            $msg = $view->__('Error! You did not enter a name for the category.');
            \LogUtil::registerError($msg);
            return false;
        }

        if (empty($data['parent_id'])) {
            $msg = $view->__('Error! You did not provide a parent for the category.');
            \LogUtil::registerError($msg);
            return false;
        }

        // get entity manager
        $em = \ServiceUtil::get('doctrine.entitymanager');

        // process name
        $data['name'] = self::processCategoryName($data['name']);

        // check that we don't have another category with the same name
        // on the same level
        $dql = "
        SELECT count(c.id)
        FROM Zikula\Core\Doctrine\Entity\Category c
        WHERE c.name = '" . $data['name'] . "'
          AND c.parent = " . $data['parent_id'];

        if (isset($data['id']) && is_numeric($data['id'])) {
            $dql .= " AND c.id <> " . $data['id'];
        }

        $query = $em->createQuery($dql);
        $exists = (int)$query->getSingleScalarResult();
        if ($exists > 0) {
            $msg = $view->__f('Category %s must be unique under parent', $data['name']);
            \LogUtil::registerError($msg);
            return false;
        }

        return true;
    }

    /**
     * Process the name of a category
     *
     * @param array $name   The name of the category.
     *
     * @return string the processed name.
     */
    public static function processCategoryName($name)
    {
        // encode slash in name
        return $name = str_replace('/', '&#47;', $name);
    }

    /**
     * Process the parent of a category
     *
     * @param integer $parent_id   The parent_id of the category.
     *
     * @return \Zikula\Core\Doctrine\Entity\Category the parent entity.
     */
    public static function processCategoryParent($parent_id)
    {
        $em = \ServiceUtil::get('doctrine.entitymanager');
        return $em->getReference('Zikula\Core\Doctrine\Entity\Category', $parent_id);
    }

    /**
     * Process the display name of a category
     *
     * @param array $displayname    The display name of the category.
     * @param array $name           The name of the category.
     *
     * @return array the processed display name.
     */
    public static function processCategoryDisplayName($displayname, $name)
    {
        $languages = \ZLanguage::getInstalledLanguages();
        foreach ($languages as $lang) {
            if (!isset($displayname[$lang]) || !$displayname[$lang]) {
                $displayname[$lang] = $name;
            }
        }

        return $displayname;
    }

    /**
     * Process the path of a category
     *
     * @param string $parent_path   The path of the parent category.
     * @param string $category_name The name of the category.
     *
     * @return string the category path.
     */
    public static function processCategoryPath($parent_path, $category_name)
    {
        return $parent_path . '/' . $category_name;
    }

    /**
     * Process the ipath of a category
     *
     * @param string $parent_ipath  The ipath of the parent category.
     * @param string $category_id   The id of the category.
     *
     * @return string the category path.
     */
    public static function processCategoryIPath($parent_ipath, $category_id)
    {
        return $parent_ipath . '/' . $category_id;
    }

    /**
     * Process the attributes of a category
     *
     * @param \Zikula\Core\Doctrine\Entity\Category $category The category to set the attributes for.
     * @param array $attrib_names                             The attribute names.
     * @param array $attrib_values                            The attribute values.
     *
     * @return none.
     */
    public static function processCategoryAttributes($category, $attrib_names, $attrib_values)
    {
        // delete attributes
        if (isset($category['attributes'])) {
            foreach ($category['attributes'] as $attribute) {
                if (!in_array($attribute['name'], $attrib_names)) {
                    $category->delAttribute($attribute['name']);
                }
            }
        }

        // add/update attributes
        foreach ($attrib_names as $attrib_key => $attrib_name) {
            if (!empty($attrib_name)) {
                $category->setAttribute($attrib_name, $attrib_values[$attrib_key]);
            }
        }
    }
}
