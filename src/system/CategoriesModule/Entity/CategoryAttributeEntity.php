<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\CategoriesModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * CategoryAttribute entity class.
 *
 * We use annotations to define the entity mappings to database (see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html).
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
     * constructor
     *
     * @param int    $category the category id
     * @param string $name     the name of the attribute
     * @param string $value    the value of the attribute
     */
    public function __construct($category, $name, $value)
    {
        $this->setCategory($category);
        $this->setAttribute($name, $value);
    }

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
    public function setCategory($category)
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
