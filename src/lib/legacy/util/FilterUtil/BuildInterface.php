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
 * FilterUtil build interface
 *
 * @deprecated since 1.4.0
 * @see Zikula\Core\FilterUtil
 */
interface FilterUtil_BuildInterface
{
    /**
     * Adds fields to list in common way.
     *
     * @param mixed $fields Fields to add.
     *
     * @return void
     */
    public function addFields($fields);

    /**
     * Get fields in list.
     *
     * @return mixed Fields in list.
     */
    public function getFields();

    /**
     * Activates/Enables operators.
     *
     * @param mixed $op Operators to activate.
     *
     * @return void
     */
    public function activateOperators($op);

    /**
     * Get operators.
     *
     * Returns an array of operators each as an array of fields
     * to use the plugin for. "-" means default for all fields.
     *
     * @return array Set of Operators and Arrays
     */
    public function getOperators();

    /**
     * Get SQL
     *
     * Return SQL WHERE and DBUtil JOIN array as array('where' => , 'join' =>).
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Value.
     *
     * @return array $where
     */
    public function getSQL($field, $op, $value);

    /**
     * Get DQL
     *
     * Return DQL WHERE and it's params as array('where' => , 'params' =>).
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Value.
     *
     * @return array $where
     */
    public function getDql($field, $op, $value);
}
