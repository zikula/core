<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Entity\RepositoryInterface;

use Zikula\ExtensionsModule\Entity\ExtensionEntity;

interface ExtensionRepositoryInterface
{
    /**
     * @return ExtensionEntity[]
     */
    public function findAll();

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @return ExtensionEntity[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @return ExtensionEntity
     */
    public function findOneBy(array $criteria, array $orderBy = null);

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
