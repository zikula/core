<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Component\FilterUtil\Plugin;

use Doctrine\ORM\Query\Expr\Base as BaseExpr;
use Zikula\Component\FilterUtil;

/**
 * Provide a set of default filter operations.
 */
class ComparePlugin extends FilterUtil\AbstractBuildPlugin
{
    /**
     * Returns the operators the plugin can handle.
     *
     * @return array Operators.
     */
    public function availableOperators()
    {
        return [
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
        ];
    }

    /**
     * Get the Doctrine expression object
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Value.
     *
     * @return BaseExpr Doctrine expression
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
                return $expr->like($column, $config->toParam('%'.$value.'%', 'compare', $field));

            case 'like':
                return $expr->like($column, $config->toParam($value, 'compare', $field));

            case 'likefirst':
                return $expr->like($column, $config->toParam('%'.$value, 'compare', $field));

            case 'likelast':
                return $expr->like($column, $config->toParam($value.'%', 'compare', $field));

            case 'null':
                return $expr->orX($expr->isNull($column), $expr->eq($column, ''));

            case 'notnull':
                return $expr->orX($expr->isNotNull($column), $expr->neq($column, ''));
        }
    }
}
