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

namespace Zikula\ExtensionsModule\Tests\Api\Fixtures;

use Doctrine\Common\Collections\Criteria;
use Zikula\ExtensionsModule\Entity\ExtensionVarEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionVarRepositoryInterface;

class ExtensionVarStubRepository implements ExtensionVarRepositoryInterface
{
    /**
     * @var array
     */
    private $entities;

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
            ['modname' => 'ZConfig',
                'name' => 'systemvar',
                'value' => 'abc',
            ],
        ];
        foreach ($datas as $data) {
            $entity = new ExtensionVarEntity();
            $entity->merge($data);
            $this->entities[] = $entity;
        }
    }

    public function remove(ExtensionVarEntity $entity): void
    {
    }

    public function persistAndFlush(ExtensionVarEntity $entity): void
    {
    }

    public function deleteByExtensionAndName(string $extensionName, string $variableName): bool
    {
        if (isset($this->entities[$extensionName][$variableName])) {
            unset($this->entities[$extensionName][$variableName]);
        }

        return true;
    }

    public function deleteByExtension(string $extensionName): bool
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
        return $this->entities[$criteria['modname']][$criteria['name']] ?? [];
    }

    public function updateName(string $oldName, string $newName): bool
    {
        return true;
    }

    public function find($id)
    {
    }

    public function findOneBy(array $criteria)
    {
    }

    public function getClassName()
    {
    }

    public function matching(Criteria $criteria)
    {
    }
}
