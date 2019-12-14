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

namespace Zikula\RoutesModule\Listener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Zikula\Core\Doctrine\EntityAccess;
use Zikula\RoutesModule\Listener\Base\AbstractEntityLifecycleListener;

/**
 * Event subscriber implementation class for entity lifecycle events.
 */
class EntityLifecycleListener extends AbstractEntityLifecycleListener
{
    public function postLoad(LifecycleEventArgs $args): void
    {
        /** @var EntityAccess $entity */
        $entity = $args->getObject();
        if (
            !$this->isEntityManagedByThisBundle($entity)
            || !method_exists($entity, 'get_objectType')
        ) {
            return;
        }

        if ('cli' === PHP_SAPI) {
            return;
        }

        if (null === $this->container->get('request_stack')->getCurrentRequest()) {
            return;
        }

        parent::postLoad($args);
    }
}
