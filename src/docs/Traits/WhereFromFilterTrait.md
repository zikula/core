WhereFromFilterTrait
====================

name: \Zikula\Core\Doctrine\WhereFromFilterTrait

Adds the following methods to your class:

 - whereFromFilter(QueryBuilder $qb, array $filter, $exprType = 'and')

Construct a QueryBuilder Expr object suitable for use in QueryBuilder->where(Expr).
filter = [field => value, field => value, field => ['operator' => '!=', 'operand' => value], ...]
when value is not an array, operator is assumed to be '='

This is used in \Zikula\UsersModule\Entity\Repository\UserRepository as one example.
