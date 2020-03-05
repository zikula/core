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

namespace Zikula\GroupsModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

interface GroupRepositoryInterface extends ObjectRepository, Selectable
{
    public function setTranslator(TranslatorInterface $translator): void;

    /**
     * Returns the amount of groups.
     */
    public function countGroups(int $groupType = null, int $excludedState = null): int;

    /**
     * Returns groups for given arguments.
     */
    public function getGroups(
        array $filters = [],
        array $exclusions = [],
        array $sorting = [],
        int $limit = 0,
        int $offset = 0
    ): array;

    public function findAllAndIndexBy(string $indexField): array;

    public function getGroupNamesById(bool $includeAll = true, bool $includeUnregistered = true): array;

    public function getGroupByName(string $name = '', int $excludedGroupId = 0): array;
}
