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

namespace Zikula\Core\Doctrine\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base class of many-to-many assocation between any entity and Category.
 *
 * @ORM\MappedSuperclass
 */
abstract class EntityCategory extends EntityAccess
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
     * @ORM\ManyToOne(targetEntity="Zikula\Core\Doctrine\Entity\Category")
     * @ORM\JoinColumn(name="categoryId", referencedColumnName="id")
     * @var \Zikula\Core\Doctrine\Entity\Category
     */
    private $category;

    public function __construct($registryId,
                                Category $category,
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

    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    public abstract function getEntity();

    public abstract function setEntity($entity);
}

