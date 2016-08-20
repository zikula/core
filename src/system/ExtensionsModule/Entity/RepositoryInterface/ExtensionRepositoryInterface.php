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

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

interface ExtensionRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * @param $name
     * @return ExtensionEntity
     */
    public function get($name);

    public function getPagedCollectionBy(array $criteria, array $orderBy = null, $limit = 0, $offset = 1);

    public function getIndexedArrayCollection($indexBy);

    public function updateName($oldName, $newName);

    public function persistAndFlush($entity);

    public function removeAndFlush($entity);
}
