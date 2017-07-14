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
use Zikula\ExtensionsModule\Entity\ExtensionVarEntity;

interface ExtensionVarRepositoryInterface extends ObjectRepository, Selectable
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
     * @param $oldName
     * @param $newName
     * @return mixed
     */
    public function updateName($oldName, $newName);
}
