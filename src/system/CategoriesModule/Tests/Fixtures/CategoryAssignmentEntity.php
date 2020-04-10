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

namespace Zikula\CategoriesModule\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;
use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;

/**
 * @ORM\Entity
 */
class CategoryAssignmentEntity extends AbstractCategoryAssignment
{
    /**
     * @ORM\ManyToOne(targetEntity="CategorizableEntity", inversedBy="categoryAssignments")
     * @ORM\JoinColumn(name="entityId", referencedColumnName="id")
     */
    private $entity;

    public function getEntity(): CategorizableEntity
    {
        return $this->entity;
    }

    public function setEntity($entity): void
    {
        $this->entity = $entity;
    }
}
