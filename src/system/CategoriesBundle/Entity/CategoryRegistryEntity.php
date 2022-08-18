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
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Zikula\CategoriesBundle\Repository\CategoryRegistryRepository;
use Zikula\CategoriesBundle\Traits\StandardFieldsTrait;

#[ORM\Entity(repositoryClass: CategoryRegistryRepository::class)]
#[ORM\Table(name: 'categories_registry')]
#[
    ORM\Index(fields: ['modname', 'entityname'], name: 'idx_categories_registry')
]
class CategoryRegistryEntity extends EntityAccess
{
    use StandardFieldsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 60)]
    #[Assert\Length(min: 1, max: 60)]
    private string $modname;

    #[ORM\Column(length: 60)]
    #[Assert\Length(min: 1, max: 60)]
    private string $entityname;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 1, max: 255)]
    private string $property;

    #[ORM\ManyToOne(inversedBy: 'attributes')]
    #[ORM\JoinColumn(name: 'category_id')]
    private CategoryEntity $category;

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

    public function getModname(): ?string
    {
        return $this->modname;
    }

    public function setModname(string $modname): self
    {
        $this->modname = $modname;

        return $this;
    }

    public function getEntityname(): ?string
    {
        return $this->entityname;
    }

    public function setEntityname(string $entityname): self
    {
        $this->entityname = $entityname;

        return $this;
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }

    public function setProperty(string $property): self
    {
        $this->property = $property;

        return $this;
    }

    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(CategoryEntity $category): self
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
