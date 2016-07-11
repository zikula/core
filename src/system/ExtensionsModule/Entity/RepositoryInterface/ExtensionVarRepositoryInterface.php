<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Entity\RepositoryInterface;

use Zikula\ExtensionsModule\Entity\ExtensionVarEntity;

interface ExtensionVarRepositoryInterface
{
    /**
     * @param ExtensionVarEntity $entity
     * @return void
     */
    public function remove(ExtensionVarEntity $entity);

    /**
     * @param ExtensionVarEntity $entity
     * @return void
     */
    public function persistAndFlush(ExtensionVarEntity $entity);

    /**
     * @param $extensionName
     * @param $variableName
     * @return bool
     */
    public function deleteByExtensionAndName($extensionName, $variableName);

    /**
     * @param $extensionName
     * @return bool
     */
    public function deleteByExtension($extensionName);

    /**
     * @return array
     */
    public function findAll();

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);
}
