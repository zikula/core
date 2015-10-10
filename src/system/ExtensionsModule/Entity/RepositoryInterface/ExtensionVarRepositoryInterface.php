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