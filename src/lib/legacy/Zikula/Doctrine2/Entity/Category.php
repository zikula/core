<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zikula\CategoriesModule\Entity\CategoryAttributeEntity;

/**
 * Category doctrine2 entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="categories_category")
 *
 * @deprecated since 1.4.0
 */
class Zikula_Doctrine2_Entity_Category extends Zikula_EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Zikula_Doctrine2_Entity_Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * @var Zikula_Doctrine2_Entity_Category
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Zikula_Doctrine2_Entity_Category", mappedBy="parent")
     * @var Zikula_Doctrine2_Entity_Category[]
     */
    private $children;

    /**
     * @ORM\Column(type="boolean", name="is_locked")
     * @var boolean
     */
    private $locked;

    /**
     * @ORM\Column(type="boolean", name="is_leaf")
     * @var boolean
     */
    private $leaf;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $value;

    /**
     * @ORM\Column(type="integer", name="sort_value")
     * @var integer
     */
    private $sortValue;

    /**
     * @ORM\Column(type="array", name="display_name")
     * @var array
     */
    private $displayName;

    /**
     * @ORM\Column(type="array", name="display_desc")
     * @var array
     */
    private $displayDesc;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $ipath;

    /**
     * @ORM\Column(type="smallint")
     * @var integer
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="Zikula\CategoriesModule\Entity\CategoryAttributeEntity",
     *                mappedBy="category",
     *                cascade={"all"},
     *                orphanRemoval=true,
     *                indexBy="name")
     */
    private $attributes;

    public function __construct()
    {
        @trigger_error('This category entity is deprecated.', E_USER_DEPRECATED);

        $this->attributes = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Zikula_Doctrine2_Entity_Category $parent)
    {
        $this->parent = $parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function getLocked()
    {
        return $this->locked;
    }

    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    public function getLeaf()
    {
        return $this->leaf;
    }

    public function setLeaf($leaf)
    {
        $this->leaf = $leaf;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getSortValue()
    {
        return $this->sortValue;
    }

    public function setSortValue($sortValue)
    {
        $this->sortValue = $sortValue;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    public function getDisplayDesc()
    {
        return $this->displayDesc;
    }

    public function setDisplayDesc($displayDesc)
    {
        $this->displayDesc = $displayDesc;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getIPath()
    {
        return $this->ipath;
    }

    public function setIPath($ipath)
    {
        $this->ipath = $ipath;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * set a single attribute for the category
     *
     * @param $name string attribute name
     * @param $value string attribute value
     */
    public function setAttribute($name, $value)
    {
        if (isset($this->attributes[$name])) {
            $this->attributes[$name]->setValue($value);
        } else {
            $this->attributes[$name] = new CategoryAttributeEntity($this, $name, $value);
        }
    }
}
