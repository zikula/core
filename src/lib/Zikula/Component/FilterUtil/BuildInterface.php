<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Component\FilterUtil;

use Doctrine\ORM\Query\Expr\Base as BaseExpr;

/**
 * FilterUtil build interface
 *
 * @deprecated as of 1.5.0
 * @see \Doctrine\ORM\QueryBuilder
 */
interface BuildInterface
{
    /**
     * Adds fields to list in common way.
     *
     * @param mixed $fields Fields to add
     *
     * @return void
     */
    public function addFields($fields);

    /**
     * Get fields in list.
     *
     * @return mixed Fields in list
     */
    public function getFields();

    /**
     * Activates/Enables operators.
     *
     * @param mixed $op Operators to activate
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
     * Get the Doctrine expression object
     *
     * @param string $field Field name
     * @param string $op    Operator
     * @param string $value Value
     *
     * @return BaseExpr Doctrine expression
     */
    public function getExprObj($field, $op, $value);
}
