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
 * Category registry doctrine2 entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="categories_registry")
 */
class Zikula_Doctrine2_Entity_CategoryRegistry
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $property;

    /**
     * @ORM\Column(type="integer", name="category_id")
     * @var integer
     */
    private $categoryId;
    /**
     * @ORM\Column(type="string", length=60)
     * @var string
     */
    private $modname;
    /**
     * @ORM\Column(type="string", length=60)
     * @var string
     */
    private $tablename;

    public function getId()
    {
        return $this->id;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function setProperty($property)
    {
        $this->property = $property;
    }

    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function getModname()
    {
        return $this->modname;
    }

    public function setModname($modname)
    {
        $this->modname = $modname;
    }

    public function getTablename()
    {
        return $this->tablename;
    }

    public function setTablename($tablename)
    {
        $this->tablename = $tablename;
    }


}
