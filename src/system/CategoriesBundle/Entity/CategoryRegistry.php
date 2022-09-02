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
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\CategoriesBundle\Repository\CategoryRegistryRepository;
use Zikula\CategoriesBundle\Traits\StandardFieldsTrait;

#[ORM\Entity(repositoryClass: CategoryRegistryRepository::class)]
#[ORM\Table(name: 'categories_registry')]
#[ORM\Index(fields: ['bundleName', 'entityName'], name: 'idx_categories_registry')]
class CategoryRegistry
{
    use StandardFieldsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 60)]
    #[Assert\Length(min: 1, max: 60)]
    private string $bundleName;

    #[ORM\Column(length: 60)]
    #[Assert\Length(min: 1, max: 60)]
    private string $entityName;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 1, max: 255)]
    private string $property;

    #[ORM\ManyToOne(inversedBy: 'attributes')]
    #[ORM\JoinColumn(name: 'category_id')]
    private Category $category;

    #[ORM\Column(name: 'obj_status', length: 1)]
    #[Assert\Length(min: 1, max: 1)]
    protected string $status = 'A';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getBundleName(): string
    {
        return $this->bundleName;
    }

    public function setBundleName(string $bundleName): self
    {
        $this->bundleName = $bundleName;

        return $this;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): self
    {
        $this->entityName = $entityName;

        return $this;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function setProperty(string $property): self
    {
        $this->property = $property;

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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
