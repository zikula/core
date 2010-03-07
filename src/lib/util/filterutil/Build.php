<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */


interface FilterUtil_Build
{
    /**
     * Adds fields to list in common way
     *
     * @access public
     * @param mixed $fields Fields to add
     */
    public function addFields($fields);

    /**
     * Get fields in list
     *
     * @access public
     * @return mixed Fields in list
     */
    public function getFields();

    /**
     * Activate/Enable operators
     *
     * @access public
     * @param mixed $op Operators to activate
     */
    public function activateOperators($op);

    /**
     * Get operators
     *
     * returns an array of operators each as an array of fields
     * to use the plugin for. "-" means default for all fields.
     *
     * @access public
     * @return array Set of Operators and Arrays
     */
    public function getOperators();

    /**
     * Get SQL
     *
     * Return SQL WHERE and DBUtil JOIN array as array('where' => , 'join' =>)
     *
     * @param string $field Field name
     * @param string $op Operator
     * @param string $value Value
     * @return array $where
     * @access public
     */
    public function getSQL($field, $op, $value);
}