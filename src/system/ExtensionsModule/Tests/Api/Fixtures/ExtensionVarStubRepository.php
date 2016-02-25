<?php
/**
 * Copyright 2015 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Tests\Api\Fixtures;

use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionVarRepositoryInterface;
use Zikula\ExtensionsModule\Entity\ExtensionVarEntity;

class ExtensionVarStubRepository implements ExtensionVarRepositoryInterface
{
    private $entities;

    /**
     * StubRepository constructor.
     */
    public function __construct()
    {
        $datas = [
            ['modname' => 'FooExtension',
                'name' => 'bar',
                'value' => 'test',
            ],
            ['modname' => 'BarExtension',
                'name' => 'bar',
                'value' => 7,
            ],
            ['modname' => 'BarExtension',
                'name' => 'name',
                'value' => 'steve',
            ],
            ['modname' => 'BarExtension',
                'name' => 'string',
                'value' => 'xyz',
            ],
        ];
        foreach ($datas as $data) {
            $entity = new ExtensionVarEntity();
            $entity->merge($data);
            $this->entities[] = $entity;
        }
    }

    public function remove(ExtensionVarEntity $entity)
    {
    }

    public function persistAndFlush(ExtensionVarEntity $entity)
    {
        return true;
    }

    public function deleteByExtensionAndName($extensionName, $variableName)
    {
        if (isset($this->entities[$extensionName][$variableName])) {
            unset($this->entities[$extensionName][$variableName]);
        }

        return true;
    }

    public function deleteByExtension($extensionName)
    {
        if (isset($this->entities[$extensionName])) {
            unset($this->entities[$extensionName]);
        }

        return true;
    }

    public function findAll()
    {
        return $this->entities;
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return isset($this->entities[$criteria['modname']][$criteria['name']]) ? $this->entities[$criteria['modname']][$criteria['name']] : [];
    }
}
