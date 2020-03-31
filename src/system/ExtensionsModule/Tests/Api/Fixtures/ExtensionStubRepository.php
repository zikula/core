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

namespace Zikula\ExtensionsModule\Tests\Api\Fixtures;

use Doctrine\Common\Collections\Criteria;
use Zikula\Bundle\CoreBundle\Doctrine\Paginator;
use Zikula\Bundle\CoreBundle\Doctrine\PaginatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

class ExtensionStubRepository implements ExtensionRepositoryInterface
{
    /**
     * @var array
     */
    private $entities = [];

    public function __construct()
    {
        $datas = [
            [
                'name' => 'FooExtension',
                'capabilities' => [
                    CapabilityApiInterface::ADMIN => ['route' => 'foo_admin_route'],
                ]
            ],
            [
                'name' => 'BarExtension',
                'capabilities' => [
                    CapabilityApiInterface::ADMIN => ['route' => 'bar_admin_route'],
                    CapabilityApiInterface::USER => ['route' => 'bar_user_route'],
                ]
            ],
            [
                'name' => 'BazExtension',
                'capabilities' => [
                    CapabilityApiInterface::ADMIN => ['route' => 'baz_admin_route'],
                ]
            ],
            [
                'name' => 'FazExtension',
                'capabilities' => [
                    CapabilityApiInterface::CATEGORIZABLE => ['entities' => ['Acme\\BazExtension\\Entity\\FazEntity']]
                ]
            ],
            [
                'name' => 'NoneExtension',
            ],
        ];
        foreach ($datas as $data) {
            $entity = new ExtensionEntity();
            $entity->merge($data);
            $this->entities[] = $entity;
        }
    }

    public function findAll(): array
    {
        return $this->entities;
    }

    public function findOneBy(array $criteria, array $orderBy = null)
    {
        foreach ($this->entities as $entity) {
            $ret = true;
            foreach ($criteria as $prop => $value) {
                $ret = $ret && ($entity[$prop] === $value);
            }
            if ($ret) {
                return $entity;
            }
        }

        return null;
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->entities;
    }

    public function get($name)
    {
        return null;
    }

    public function getPagedCollectionBy(
        array $criteria,
        array $orderBy = null,
        int $pageSize = 0,
        int $page = 1
    ): PaginatorInterface {
        return null;
    }

    public function getIndexedArrayCollection(string $indexBy): array
    {
        return [];
    }

    public function updateName(string $oldName, string $newName): void
    {
    }

    public function persistAndFlush(ExtensionEntity $entity): void
    {
    }

    public function removeAndFlush(ExtensionEntity $entity): void
    {
    }

    public function find($id)
    {
    }

    public function getClassName()
    {
    }

    public function matching(Criteria $criteria)
    {
    }
}
