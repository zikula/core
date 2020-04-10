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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CategorizableEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="CategoryAssignmentEntity",
     *                mappedBy="entity", cascade={"remove", "persist"},
     *                orphanRemoval=true, fetch="EAGER")
     */
    private $categoryAssignments;

    public function __construct()
    {
        $this->categoryAssignments = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCategoryAssignments(): ArrayCollection
    {
        return $this->categoryAssignments;
    }

    public function setCategoryAssignments(ArrayCollection $categoryAssignments): void
    {
        $this->categoryAssignments = $categoryAssignments;
    }
}
