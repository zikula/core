<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Entity\Factory;

use Zikula\RoutesModule\Entity\Factory\Base\AbstractEntityInitialiser;
use Zikula\RoutesModule\Entity\RouteEntity;

/**
 * Entity initialiser class used to dynamically apply default values to newly created entities.
 */
class EntityInitialiser extends AbstractEntityInitialiser
{
    public function initRoute(RouteEntity $entity): RouteEntity
    {
        $entity = parent::initRoute($entity);

        // always add route to the end of the list
        $entity->setSort(-1);

        return $entity;
    }
}
