---
currentMenu: dev-doctrine
---
# WhereFromFilterTrait

The trait implemented by `\Zikula\Bundle\CoreBundle\Doctrine\WhereFromFilterTrait` adds the following methods to your class:

- `whereFromFilter(QueryBuilder $qb, array $filter, $exprType = 'and')`

Construct a QueryBuilder `Expr` object suitable for use in `QueryBuilder->where(Expr)`.
`filter = [field => value, field => value, field => ['operator' => '!=', 'operand' => value], …]`
when value is not an array, operator is assumed to be '='

This is used in `\Zikula\UsersBundle\Repository\UserRepository` as one example.
