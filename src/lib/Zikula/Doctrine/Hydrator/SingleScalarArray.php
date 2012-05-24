<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
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
 * Doctrine hydrator to hydrate an array of values instead of an array of arrays.
 *
 * Examples:
 * <ul>
 * <li>load all values of an column (with unique result using DISTINCT)
 * <code>
 * $array = Doctrine_Query::create()
 *              ->select("DISTINCT myColumn")
 *              ->from("MyTable")
 *              ->execute(array(), DoctrineUtil::HYDRATE_SINGLE_SCALAR_ARRAY)
 * // $array is array(0 => "myColumn value 1", 1 => "myColumn value 2")
 * </code>
 * </li>
 *
 * <li>use an column as array key
 * <code>
 * $array = Doctrine_Query::create()
 *              ->select("myKeyColumn, myColumn")
 *              ->from("MyTable INDEXBY myKeyColumn")
 *              ->execute(array(), DoctrineUtil::HYDRATE_SINGLE_SCALAR_ARRAY)
 * // $array is array("key1" => "myColumn value 1", "key2" => "myColumn value 2")
 * </code>
 * </li>
 * </ul>
 *
 */
class Zikula_Doctrine_Hydrator_SingleScalarArray extends Doctrine_Hydrator_Abstract
{
    /**
     * Hydrates the select results.
     *
     * @param mixed $stmt Doctrine statement.
     *
     * @return array Hydration result (never null).
     */
    public function hydrateResultSet($stmt)
    {
        // setup aliases and assoc informations.
        reset($this->_queryComponents);
        $rootAlias = key($this->_queryComponents);
        $rootTablePrefix = array_flip($this->_tableAliases);
        $rootTablePrefix = $rootTablePrefix[$rootAlias];
        $rootComponent = $this->_queryComponents[$rootAlias];
        $isAssoc = isset($rootComponent['map']) && !empty($rootComponent['map']);

        // load rows from db
        $resultRows = $stmt->fetchAll(Doctrine::FETCH_ASSOC);
        $fieldArray = array();

        if ($isAssoc) {
            $assocColumnName = $rootTablePrefix . '__' . $rootComponent['map'];
            foreach ($resultRows as $resultRow) {
                $key = $resultRow[$assocColumnName];
                unset($resultRow[$assocColumnName]);
                reset($resultRow);
                $fieldArray[$key] = current($resultRow);
            }
        } else {
            foreach ($resultRows as $resultRow) {
                reset($resultRow);
                $fieldArray[] = current($resultRow);
            }
        }

        return $fieldArray;
    }
}
