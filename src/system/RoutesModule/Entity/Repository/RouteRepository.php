<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Entity\Repository;

use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Zikula\RoutesModule\Entity\Repository\Base\AbstractRouteRepository;

/**
 * Repository class used to implement own convenience methods for performing certain DQL queries.
 *
 * This is the concrete repository class for route entities.
 */
class RouteRepository extends AbstractRouteRepository
{
    /**
     * Deletes all custom routes for a certain bundle.
     *
     * @throws InvalidArgumentException Thrown if invalid parameters are received
     */
    public function deleteByBundle(string $bundleName): void
    {
        // check id parameter
        if (empty($bundleName)) {
            throw new InvalidArgumentException('Invalid bundle name received.');
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->mainEntityClass, 'tbl')
           ->where('tbl.bundle = :bundle')
           ->setParameter('bundle', $bundleName);
        $query = $qb->getQuery();
        $query->execute();
    }
}
