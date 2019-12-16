<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use LogicException;
use Zikula\MenuModule\Entity\MenuItemEntity;
use Zikula\MenuModule\Entity\RepositoryInterface\MenuItemRepositoryInterface;

class MenuItemRepository extends NestedTreeRepository implements MenuItemRepositoryInterface, ServiceEntityRepositoryInterface
{
    /**
     * Code from Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
     */
    public function __construct(ManagerRegistry $registry)
    {
        $entityClass = MenuItemEntity::class;

        /** @var EntityManagerInterface $manager */
        $manager = $registry->getManagerForClass($entityClass);
        if (null === $manager) {
            throw new LogicException(sprintf('Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity’s metadata.', $entityClass));
        }

        parent::__construct($manager, $manager->getClassMetadata($entityClass));
    }
}
