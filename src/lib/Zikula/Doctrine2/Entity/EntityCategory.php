<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * Base class of many-to-many assocation between any entity and Category.
 *
 * @ORM\MappedSuperclass
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

