<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Tests\Api\Fixtures;

use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

class ExtensionStubRepository implements ExtensionRepositoryInterface
{
    private $entities = [];

    /**
     * ExtensionStubRepository constructor.
     */
    public function __construct()
    {
        $datas = [
            [
                'name' => 'FooExtension',
                'capabilities' => [
                    CapabilityApiInterface::ADMIN => ['route' => 'foo_admin_route'],
                    CapabilityApiInterface::AUTHENTICATION => ['version' => '1.0']
                ]
            ],
            [
                'name' => 'BarExtension',
                'capabilities' => [
                    CapabilityApiInterface::ADMIN => ['route' => 'bar_admin_route'],
                    CapabilityApiInterface::USER => ['route' => 'bar_user_route'],
                    CapabilityApiInterface::SEARCHABLE => ['class' => 'Acme\\BarExtension\\Search']
                ]
            ],
            [
                'name' => 'BazExtension',
                'capabilities' => [
                    CapabilityApiInterface::ADMIN => ['route' => 'baz_admin_route'],
                    CapabilityApiInterface::HOOK_PROVIDER => ['class' => 'Acme\\BazExtension\\Hook'],
                    CapabilityApiInterface::SEARCHABLE => ['class' => 'Acme\\BazExtension\\Search']
                ]
            ],
            [
                'name' => 'FazExtension',
                'capabilities' => [
                    CapabilityApiInterface::HOOK_SUBSCRIBER => [
                        'class' => 'Acme\\FazExtension\\Hook',
                        CapabilityApiInterface::HOOK_SUBSCRIBE_OWN => true
                    ],
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

    public function findAll()
    {
        return $this->entities;
    }

    public function findOneBy(array $criteria, array $orderBy = null)
    {
        foreach ($this->entities as $entity) {
            $ret = true;
            foreach ($criteria as $prop => $value) {
                $ret = $ret && ($entity[$prop] == $value);
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

    public function getPagedCollectionBy(array $criteria, array $orderBy = null, $limit = 0, $offset = 1)
    {
        return [];
    }

    public function getIndexedArrayCollection($indexBy)
    {
        return [];
    }

    public function updateName($oldName, $newName)
    {
    }

    public function persistAndFlush($entity)
    {
    }

    public function removeAndFlush($entity)
    {
    }
}
