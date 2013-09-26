<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula\Core\FilterUtil
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\FilterUtil;

/**
 * FilterUtil build interface
 */
interface BuildInterface
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
     * Get the Doctrine2 expression object
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Value.
     *
     * @return Expr\Base Doctrine2 expression
     */
    public function getExprObj($field, $op, $value);
}
