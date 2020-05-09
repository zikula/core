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

namespace Zikula\SearchModule\Entity\Repository;

use Doctrine\ORM\QueryBuilder;
use Zikula\Bundle\CoreBundle\Doctrine\Paginator;
use Zikula\Bundle\CoreBundle\Doctrine\PaginatorInterface;

trait RepositoryGetResultsTrait
{
    public function doGetPaginatedResults(
        QueryBuilder $qb,
        array $filters = [],
        array $sorting = [],
        int $page = 1,
        int $pageSize = 25
    ): PaginatorInterface {
        $alias = $qb->getRootAliases()[0];
        // add clauses for where
        if (count($filters) > 0) {
            $i = 1;
            foreach ($filters as $w_key => $w_value) {
                $qb->andWhere($qb->expr()->eq($alias . '.' . $w_key, '?' . $i))
                    ->setParameter($i, $w_value);
                $i++;
            }
        }
        // add clause for ordering
        if (count($sorting) > 0) {
            foreach ($sorting as $sort => $sortdir) {
                $qb->addOrderBy($alias . '.' . $sort, $sortdir);
            }
        }

        return (new Paginator($qb, $pageSize))->paginate($page);
    }
}
