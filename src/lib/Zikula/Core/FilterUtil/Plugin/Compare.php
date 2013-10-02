<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license    GNU/LGPv3 (or at your option any later version).
 * @package    FilterUtil
 * @subpackage Filter
 *
 *             Please see the NOTICE file distributed with this source code for further
 *             information regarding copyright and licensing.
 */
namespace Zikula\Core\FilterUtil\Plugin;

use Zikula\Core\FilterUtil;

/**
 * Provide a set of default filter operations.
 */
class Compare extends FilterUtil\AbstractBuildPlugin
{

    /**
     * Constructor.
     *
     * @param array $fields  Set of fields to use, see setFields() (optional) (default=null).
     * @param array $ops     Operators to enable, see activateOperators() (optional) (default=null).
     * @param bool  $default set the plugin to default (optional) (default=true).
     */
    public function __construct($fields = null, $ops = null, $default = true)
    {
        parent::__construct($fields, $ops, $default);
    }

    /**
     * Returns the operators the plugin can handle.
     *
     * @return array Operators.
     */
    public function availableOperators()
    {
        return array(
            'eq',
            'ne',
            'lt',
            'le',
            'gt',
            'ge',
            'search',
            'like',
            'likefirst',
            'likelast',
            'null',
            'notnull'
        );
    }

    /**
     * Get the Doctrine2 expression object
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Value.
     *
     * @return Expr\Base Doctrine2 expression
     */
    public function getExprObj($field, $op, $value)
    {
        $config = $this->config;
        $column = $config->addAliasTo($field);
        $config->testFieldExists($column);
        $expr = $config->getQueryBuilder()->expr();

        switch ($op) {
            case 'eq':
                return $expr->eq($column, $config->toParam($value, 'compare', $field));

            case 'ne':
                return $expr->neq($column, $config->toParam($value, 'compare', $field));

            case 'lt':
                return $expr->lt($column, $config->toParam($value, 'compare', $field));

            case 'le':
                return $expr->lte($column, $config->toParam($value, 'compare', $field));

            case 'gt':
                return $expr->gt($column, $config->toParam($value, 'compare', $field));

            case 'ge':
                return $expr->gte($column, $config->toParam($value, 'compare', $field));

            case 'search':
                return $expr->like($column, $config->toParam('%' . $value . '%', 'compare', $field));

            case 'like':
                return $expr->like($column, $config->toParam($value, 'compare', $field));

            case 'likefirst':
                return $expr->like($column, $config->toParam('%' . $value, 'compare', $field));

            case 'likelast':
                return $expr->like($column, $config->toParam($value . '%', 'compare', $field));

            case 'null':
                return $expr->orX($expr->isNull($column), $expr->eq($column, ''));

            case 'notnull':
                return $expr->orX($expr->isNotNull($column), $expr->neq($column, ''));
        }
    }
}
