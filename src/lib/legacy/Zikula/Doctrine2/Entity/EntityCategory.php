<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * Base class of many-to-many association between any entity and Category.
 *
 * @ORM\MappedSuperclass
 *
 * @deprecated since 1.4.0
 */
abstract class Zikula_Doctrine2_Entity_EntityCategory extends Zikula_EntityAccess
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
     * @ORM\ManyToOne(targetEntity="Zikula_Doctrine2_Entity_Category")
     * @ORM\JoinColumn(name="categoryId", referencedColumnName="id")
     * @var Zikula_Doctrine2_Entity_Category
     */
    private $category;

    public function __construct($registryId,
                                Zikula_Doctrine2_Entity_Category $category,
                                $entity)
    {
        @trigger_error('This entity category entity is deprecated.', E_USER_DEPRECATED);

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

    public function setCategory(Zikula_Doctrine2_Entity_Category $category)
    {
        $this->category = $category;
    }

    abstract public function getEntity();

    abstract public function setEntity($entity);
}
