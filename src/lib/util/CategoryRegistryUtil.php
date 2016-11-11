<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;

/**
 * CategoryRegistryUtil
 */
class CategoryRegistryUtil
{
    /**
     * Delete a category registry entry
     *
     * @param string  $modname The module to create a property for
     * @param integer $entryID The category-id to bind this property to
     *
     * @return boolean The DB insert operation result code cast to a boolean
     */
    public static function deleteEntry($modname, $entryID = null)
    {
        if (!isset($modname) || !$modname) {
            throw new \InvalidArgumentException(__f("Error! Received invalid parameter '%s'", 'modname'));
        }

        $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');

        $params = ['modname' => $modname];
        if ($entryID) {
            $params = ['id' => $entryID];
        }

        $entity = $em->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')->findOneBy($params);
        if ($entity) {
            $em->remove($entity);
            $em->flush();
        }

        return true;
    }

    /**
     * Create a category registry entry
     *
     * @param string  $modname    The module to create a property for
     * @param string  $entityname The module entity to create a property for
     * @param string  $property   The property name
     * @param integer $categoryID The category-id to bind this property to
     *
     * @return boolean The DB insert operation result code cast to a boolean
     */
    public static function insertEntry($modname, $entityname, $property, $categoryID)
    {
        return self::_processEntry($modname, $entityname, $property, $categoryID);
    }

    /**
     * Update a category registry entry.
     *
     * @param integer $entryID    The id of the existing entry we wish to update
     * @param string  $modname    The module to create a property for
     * @param string  $entityname The module entity to create a property for
     * @param string  $property   The property name
     * @param integer $categoryID The category-id to bind this property to
     *
     * @return boolean The DB insert operation result code cast to a boolean
     */
    public static function updateEntry($entryID, $modname, $entityname, $property, $categoryID)
    {
        if (!isset($entryID) || !$entryID) {
            throw new \InvalidArgumentException(__f("Error! Received invalid parameter '%s'", 'entryID'));
        }

        return self::_processEntry($modname, $entityname, $property, $categoryID, $entryID);
    }

    /**
     * Create or update a category registry entry.
     *
     * @param string  $modname    The module to create a property for
     * @param string  $entityname The module entity to create a property for
     * @param string  $property   The property name
     * @param integer $categoryID The category-id to bind this property to
     * @param integer $entryID    The id of the existing entry we wish to update (optional) (default=null)
     *
     * @return boolean The DB insert operation result code cast to a boolean
     */
    private static function _processEntry($modname, $entityname, $property, $categoryID, $entryID = null)
    {
        if (!isset($modname) || !$modname) {
            throw new \InvalidArgumentException(__f("Error! Received invalid parameter '%s'", 'modname'));
        }
        if (!isset($entityname) || !$entityname) {
            throw new \InvalidArgumentException(__f("Error! Received invalid parameter '%s'", 'entityname'));
        }
        if (!isset($property) || !$property) {
            throw new \InvalidArgumentException(__f("Error! Received invalid parameter '%s'", 'property'));
        }
        if (!isset($categoryID) || !$categoryID) {
            throw new \InvalidArgumentException(__f("Error! Received invalid parameter '%s'", 'categoryID'));
        }

        $data = [
            'modname' => $modname,
            'entityname' => $entityname,
            'property' => $property,
            'category_id' => $categoryID,
            'id' => $entryID ? $entryID : null
        ];

        return self::registerModuleCategory($data);
    }

    /**
     * Register a module category.
     *
     * @param array $catreg The array of category map data objects
     *
     * @return boolean The DB insert operation result code cast to a boolean
     */
    public static function registerModuleCategory($catreg)
    {
        if (!$catreg) {
            return false;
        }

        $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');

        if (isset($catreg['id'])) {
            $entity = $em->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')->find($catreg['id']);
        } else {
            $entity = new CategoryRegistryEntity();
        }

        $entity->merge($catreg);
        $em->persist($entity);
        $em->flush();

        return true;
    }

    /**
     * Register module categories.
     *
     * @param array $catregs The array of category map data objects
     *
     * @return boolean
     */
    public static function registerModuleCategories($catregs)
    {
        if (!$catregs) {
            return false;
        }

        $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');

        foreach ($catregs as $catreg) {
            if ($catreg['id']) {
                $entity = $em->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')->find($catreg['id']);
            } else {
                $entity = new CategoryRegistryEntity();
            }

            $entity->merge($catreg);
            $em->persist($entity);
        }

        $em->flush();

        return true;
    }

    /**
     * Get registered Categories for a module.
     *
     * @param string $modname       The module name
     * @param string $entityname    The entity name for which we wish to get the property for
     *
     * @return array The associative field array of registered categories for the specified module
     */
    public static function getRegisteredModuleCategories($modname, $entityname, $arraykey = 'property')
    {
        if (!$modname || !$entityname) {
            throw new \InvalidArgumentException(__f('Error! Received invalid specifications %1$s, %2$s.', [$modname, $entityname]));
        }

        static $cache = [];
        if (isset($cache[$modname][$entityname])) {
            return $cache[$modname][$entityname];
        }

        /** @var $em Doctrine\ORM\EntityManager */
        $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');

        $rCategories = $em->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')->findBy(['modname' => $modname, 'entityname' => $entityname], ['id' => 'ASC']);

        $fArr = [];

        /** @var $rCategory CategoryRegistryEntity */
        foreach ($rCategories as $rCategory) {
            $rCategory = $rCategory->toArray();
            $fArr[$rCategory[$arraykey]] = $rCategory['category_id'];
        }

        $cache[$modname][$entityname] = $fArr;

        return $fArr;
    }

    /**
     * Get registered category for module property.
     *
     * @param string $modname       The module we wish to get the property for
     * @param string $entityname    The entity name for which we wish to get the property for
     * @param string $property      The property name
     * @param string $default       The default value to return if the requested value is not set (optional) (default=null)
     *
     * @return array The associative field array of registered categories for the specified module
     */
    public static function getRegisteredModuleCategory($modname, $entityname, $property, $default = null)
    {
        if (!$modname || !$property) {
            return $default;
        }

        $fArr = self::getRegisteredModuleCategories($modname, $entityname);

        if ($fArr && isset($fArr[$property]) && $fArr[$property]) {
            return $fArr[$property];
        }

        // if we have a path default, we get the ID
        if ($default && !is_int($default)) {
            $cat = CategoryUtil::getCategoryByPath($default);
            if ($cat) {
                $default = $cat['id'];
            }
        }

        return $default;
    }

    /**
     * Get the IDs of the property registers.
     *
     * @param string $modname       The module name
     * @param string $entityname    The entity name for which we wish to get the property for
     *
     * @return array The associative field array of register ids for the specified module
     */
    public static function getRegisteredModuleCategoriesIds($modname, $entityname)
    {
        if (!$modname || !$entityname) {
            throw new \InvalidArgumentException(__f('Error! Received invalid specifications %1$s, %2$s.', [$modname, $entityname]));
        }

        $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');

        $rCategories = $em->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')->findBy(['modname' => $modname, 'entityname' => $entityname]);

        $fArr = [];

        foreach ($rCategories as $rCategory) {
            $fArr[$rCategory['property']] = $rCategory['id'];
        }

        return $fArr;
    }
}
