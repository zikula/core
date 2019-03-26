<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;

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
     */
    private $name;

    /**
     * The value of the attribute
     *
     * @ORM\Column(type="text")
     */
    private $value;

    /**
     * get the category item
     *
     * @return CategoryEntity the category item
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * set the category item
     *
     * @param CategoryEntity $category the category item
     */
    public function setCategory(CategoryEntity $category)
    {
        $this->category = $category;
    }

    /**
     * get the name of the attribute
     *
     * @return string the attribute's name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * set the name for the attribute
     *
     * @param string $name the attribute's name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * get the value of the attribute
     *
     * @return string the attribute's value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * set the value for the attribute
     *
     * @param string $value the attribute's value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * set the attribute
     *
     * @param string $name the attribute's name
     * @param string $value the attribute's value
     */
    public function setAttribute($name, $value)
    {
        $this->setName($name);
        $this->setValue($value);
    }
}
