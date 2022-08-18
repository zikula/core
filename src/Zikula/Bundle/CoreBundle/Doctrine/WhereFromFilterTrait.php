<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Doctrine;

use DateTime;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;

/**
 * Class WhereFromFilterTrait
 */
trait WhereFromFilterTrait
{
    /**
     * Construct a QueryBuilder Expr object suitable for use in QueryBuilder->where(Expr).
     * filter = [field => value, field => value, field => ['operator' => '!=', 'operand' => value], ...]
     * when value is not an array, operator is assumed to be '='
     */
    private function whereFromFilter(QueryBuilder $qb, array $filter = [], string $exprType = 'and', string $alias = 'u'): Composite
    {
        $exprType = in_array($exprType, ['and', 'or']) ? $exprType : 'and';
        $exprMethod = mb_strtolower($exprType) . 'X';
        /** @var Composite $expr */
        $expr = $qb->expr()->{$exprMethod}();
        $i = 1; // parameter counter
        foreach ($filter as $field => $value) {
            if ('groups' === $field) {
                $field = 'gid';
                $alias = 'g';
            }
            if (!is_array($value)) {
                $value = [
                    'operator' => '=',
                    'operand' => $value,
                ];
            }
            if (preg_match('/^IS (NOT )?NULL/i', $value['operator'], $matches)) {
                $method = isset($matches[1]) ? 'isNotNull' : 'isNull';
                $expr->add($qb->expr()->{$method}($alias . '.' . $field));
            } else {
                if (is_bool($value['operand'])) {
                    $dbValue = $value['operand'] ? '1' : '0';
                } elseif (is_int($value['operand']) || is_array($value['operand']) || $value['operand'] instanceof DateTime) {
                    $dbValue = $value['operand'];
                } else {
                    $dbValue = (string) $value['operand'];
                }
                $methodMap = [
                    '=' => 'eq',
                    '>' => 'gt',
                    '<' => 'lt',
                    '>=' => 'gte',
                    '<=' => 'lte',
                    '<>' => 'neq',
                    '!=' => 'neq',
                    'like' => 'like',
                    'notLike' => 'notLike',
                    'in' => 'in',
                    'notIn' => 'notIn',
                ];
                $method = $methodMap[$value['operator']];

                $expr->add($qb->expr()->{$method}($alias . '.' . $field, '?' . $i));
                $qb->setParameter($i, $dbValue);
            }
            $i++;
        }

        return $expr;
    }
}
