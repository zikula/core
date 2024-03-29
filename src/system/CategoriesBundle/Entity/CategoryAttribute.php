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

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category attributes store extra information about each category.
 */
#[ORM\Entity]
#[ORM\Table(name: 'categories_attributes')]
class CategoryAttribute
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'attributes')]
    #[ORM\JoinColumn(name: 'category_id')]
    private Category $category;

    #[ORM\Id]
    #[ORM\Column(length: 80)]
    #[Assert\Length(min: 1, max: 80)]
    private string $name;

    #[ORM\Column(type: Types::TEXT)]
    private string $value;

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setAttribute(string $name, string $value): self
    {
        $this->setName($name)
            ->setValue($value);

        return $this;
    }
}
