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
use Doctrine\Common\Persistence\ObjectRepository;

interface BlockPositionRepositoryInterface extends ObjectRepository, Selectable
{
    public function findByName(string $name);

    /**
     * Get an array of position names indexed by the id.
     */
    public function getPositionChoiceArray(): array;
}
