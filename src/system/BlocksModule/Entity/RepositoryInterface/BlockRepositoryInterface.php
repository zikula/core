<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Zikula\BlocksModule\Entity\BlockEntity;

interface BlockRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * @param array $filter
     * @return mixed
     */
    public function getFilteredBlocks(array $filter);

    /**
     * @param BlockEntity $entity
     * @return mixed
     */
    public function persistAndFlush(BlockEntity $entity);
}
