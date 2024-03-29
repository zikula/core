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

namespace Zikula\CategoriesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Base class of many-to-many association between any entity and Category.
 */
#[ORM\MappedSuperclass]
abstract class AbstractCategoryAssignment
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(name: 'registryId')]
    private int $categoryRegistryId;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'categoryId')]
    private Category $category;

    abstract public function getEntity();

    abstract public function setEntity($entity);

    public function __construct($registryId, Category $category, $entity)
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

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
