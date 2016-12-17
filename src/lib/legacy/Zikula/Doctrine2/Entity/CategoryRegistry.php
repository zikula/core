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
 * Category registry doctrine2 entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="categories_registry")
 *
 * @deprecated since 1.4.0
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

    public function __construct()
    {
        @trigger_error('This category registry entity is deprecated.', E_USER_DEPRECATED);
    }

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
