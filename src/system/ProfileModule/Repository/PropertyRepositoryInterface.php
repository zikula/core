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

namespace Zikula\ProfileModule\Repository;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use Zikula\ProfileModule\Entity\PropertyEntity;

interface PropertyRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * @return PropertyEntity[]
     */
    public function getIndexedActive(): array;

    /**
     * @return PropertyEntity[]
     */
    public function getDynamicFieldsSpecification(): array;
}
