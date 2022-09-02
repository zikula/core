<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ProfileBundle\Repository;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use Zikula\ProfileBundle\Entity\Property;

interface PropertyRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * @return Property[]
     */
    public function getIndexedActive(): array;

    /**
     * @return Property[]
     */
    public function getDynamicFieldsSpecification(): array;
}
