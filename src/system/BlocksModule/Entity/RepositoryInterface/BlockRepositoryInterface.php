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

namespace Zikula\BlocksModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use Zikula\BlocksModule\Entity\BlockEntity;

interface BlockRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * @return mixed
     */
    public function getFilteredBlocks(array $filter = []);

    public function persistAndFlush(BlockEntity $entity): void;

    public function remove($blocks): void;
}
