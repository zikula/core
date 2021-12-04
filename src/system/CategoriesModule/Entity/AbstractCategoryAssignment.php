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

namespace Zikula\CategoriesModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

/**
 * Base class of many-to-many association between any entity and Category.
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractCategoryAssignment extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="integer", name="registryId")
     * @var int
     */
    private $categoryRegistryId;

    /**
     * @ORM\ManyToOne(targetEntity="Zikula\CategoriesModule\Entity\CategoryEntity")
     * @ORM\JoinColumn(name="categoryId", referencedColumnName="id")
     * @var CategoryEntity
     */
    private $category;

    abstract public function getEntity();

    abstract public function setEntity($entity);

    public function __construct($registryId, CategoryEntity $category, $entity)
    {
        $this->categoryRegistryId = $registryId;
        $this->category = $category;
        $this->setEntity($entity);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCategoryRegistryId(): int
    {
        return $this->categoryRegistryId;
    }

    public function setCategoryRegistryId(int $categoryRegistryId): self
    {
        $this->categoryRegistryId = $categoryRegistryId;

        return $this;
    }

    public function getCategory(): CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(CategoryEntity $category): self
    {
        $this->category = $category;

        return $this;
    }
}
