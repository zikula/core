<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;

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
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="integer", name="registryId")
     * @var integer
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

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getCategoryRegistryId()
    {
        return $this->categoryRegistryId;
    }

    public function setCategoryRegistryId($categoryRegistryId)
    {
        $this->categoryRegistryId = $categoryRegistryId;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(CategoryEntity $category)
    {
        $this->category = $category;
    }
}
