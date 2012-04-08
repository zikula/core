<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Doctrine
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * An Doctrine_Query subclass with zikula related extensions.
 */
class Zikula_Doctrine_Query extends Doctrine_Query
{
    /**
     * Adds a category condition to this query.
     * 
     * There are three ways to call this method:
     * Way 1: return all rows that are in one of these categories
     * <code>
     * $query->addWhereCategories(array(41,42,43));
     * </code>
     * 
     * Way 2: return all rows that are in category 41 or 42 in the main property 
     *        or in category 43 in the secound property
     * <code>
     * $query->addWhereCategories(array('main' => array(41,42), 'secound' => 43));
     * </code>
     * 
     * Way 3: return all rows that are in category 41 or 42 in the main property 
     *        AND in category 43 in the secound property
     * <code>
     * $query->addWhereCategories(array('main' => array(41,42), 'secound' => 43), true);
     * </code>
     *
     * The queried doctrine model must have the Zikula_Doctrine_Template_Categorisable behavoir.
     *
     * @param array   $categories  Array of category ids or an associative array of property name => category id(s).
     * @param boolean $joinWithAnd True to join properties with AND instead with OR.
     *
     * @return Zikula_Doctrine_Query
     */
    public function addWhereCategories($categories, $joinWithAnd=false)
    {
        // getRootAlias() triggers from parsing => getRoot() works now
        $rootAlias = $this->getRootAlias();

        if (!$this->getRoot()->hasTemplate('Zikula_Doctrine_Template_Categorisable')) {
            throw new LogicException('The doctrine module ' . $this->getRoot()->getClassnameToReturn()
                    . ' does not have the Zikula_Doctrine_Template_Categorisable behavoir');
        }

        // array of category ids
        if (isset($categories[0])) {
            $inDQL = array_fill(0, count($categories), '?');
            $this->addWhere($rootAlias . '.Categories.category_id in (' . implode(',', $inDQL) . ')', $categories);

            // property => category ids array
        } else {
            if ($joinWithAnd) {
                $idField = $this->getRoot()->getIdentifierColumnNames();
                $idField = $idField[0];
                $dqlId = $rootAlias . '.' . $idField;
                $tableId = 1;
                $mapObjTableName = 'GeneratedDoctrineModel_' . $this->getRoot()->getClassnameToReturn() . '_EntityCategory';

                foreach ($categories as $property => $categories) {
                    $categories = (array)$categories;

                    $params = array($property);
                    $params = array_merge($params, $categories);

                    $inDQL = array_fill(0, count($categories), '?');
                    $this->addWhere($dqlId . ' in (SELECT subtbl' . $tableId . '.obj_id
                                                   FROM ' . $mapObjTableName . ' AS subtbl' . $tableId . '
                                                   WHERE subtbl' . $tableId . '.reg_property = ?
                                                   AND subtbl' . $tableId . '.category_id IN (' . implode(',', $inDQL) . '))', $params);

                    $tableId++;
                }
            } else {
                $where = array();
                $params = array();
                foreach ($categories as $property => $categories) {
                    $categories = (array)$categories;

                    $params[] = $property;
                    $params = array_merge($params, $categories);

                    $inDQL = array_fill(0, count($categories), '?');
                    $where[] = '(' . $rootAlias . '.Categories.reg_property = ?
                                    AND ' . $rootAlias . '.Categories.category_id IN (' . implode(',', $inDQL) . '))';
                }

                $this->addWhere(implode(' OR ', $where), $params);
            }
        }

        return $this;
    }
}
