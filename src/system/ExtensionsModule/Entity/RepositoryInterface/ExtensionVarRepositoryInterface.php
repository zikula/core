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

namespace Zikula\ExtensionsModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Zikula\ExtensionsModule\Entity\ExtensionVarEntity;

interface ExtensionVarRepositoryInterface extends ObjectRepository, Selectable
{
    public function remove(ExtensionVarEntity $entity): void;

    public function persistAndFlush(ExtensionVarEntity $entity): void;

    public function deleteByExtensionAndName(string $extensionName, string $variableName): bool;

    public function deleteByExtension(string $extensionName): bool;

    public function updateName(string $oldName, string $newName): bool;
}
