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
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

/**
 * CategoryAttribute entity class.
 *
 * @ORM\Entity
 * @ORM\Table(name="categories_attributes")
 *
 * Category attributes table.
 * Stores extra information about each category.
 */
class CategoryAttributeEntity extends EntityAccess
{
    /**
     * The id of the category the attribute belongs to
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="CategoryEntity", inversedBy="attributes")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * The name of the attribute
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=80)
     * @Assert\Length(min="1", max="80")
     * @var string
     */
    private $name;

    /**
     * The value of the attribute
     *
     * @ORM\Column(type="text")
     * @var string
     */
    private $value;

    public function getCategory(): CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(CategoryEntity $category): self
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
