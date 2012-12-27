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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Category entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="categories_category",indexes={@ORM\index(name="idx_categories_is_leaf",columns={"is_leaf"}),
 *                                                @ORM\index(name="idx_categories_name",columns={"name"}),
 *                                                @ORM\index(name="idx_categories_ipath",columns={"ipath","is_leaf","status"}),
 *                                                @ORM\index(name="idx_categories_status",columns={"status"}),
 *                                                @ORM\index(name="idx_categories_ipath_status",columns={"ipath","status"})})
 */
class Category extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Zikula\Core\Doctrine\Entity\Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * @var \Zikula\Core\Doctrine\Entity\Category
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Zikula\Core\Doctrine\Entity\Category", mappedBy="parent")
     * @var \Zikula\Core\Doctrine\Entity\Category
     */
    private $children;

    /**
     * @ORM\Column(type="boolean", name="is_locked")
     * @var boolean
     */
    private $is_locked;

    /**
     * @ORM\Column(type="boolean", name="is_leaf")
     * @var boolean
     */
    private $is_leaf;

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
    private $sort_value;

    /**
     * @ORM\Column(type="array", name="display_name")
     * @var array
     */
    private $display_name;

    /**
     * @ORM\Column(type="array", name="display_desc")
     * @var array
     */
    private $display_desc;

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
     * @ORM\Column(type="string", length=1)
     * @var string
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="Zikula\Core\Doctrine\Entity\CategoryAttribute",
     *                mappedBy="category",
     *                cascade={"all"},
     *                orphanRemoval=true,
     *                indexBy="name")
     */
    private $attributes;


    /**
     * constructor
     */
    public function __construct()
    {
        $this->parent = null;
        $this->children = null;
        $this->is_locked = 0;
        $this->is_leaf = 0;
        $this->name = '';
        $this->value = '';
        $this->sort_value = 2147483647;
        $this->display_name = array();
        $this->display_desc = array();
        $this->path = '';
        $this->ipath = '';
        $this->status = 'I';

        $this->attributes = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Category $parent)
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

    public function getIs_locked()
    {
        return $this->is_locked;
    }

    public function setIs_locked($is_locked)
    {
        $this->is_locked = $is_locked;
    }

    public function getIs_leaf()
    {
        return $this->is_leaf;
    }

    public function setIs_leaf($is_leaf)
    {
        $this->is_leaf = $is_leaf;
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

    public function getSort_value()
    {
        return $this->sort_value;
    }

    public function setSort_value($sort_value)
    {
        $this->sort_value = $sort_value;
    }

    public function getDisplay_name()
    {
        return $this->display_name;
    }

    public function setDisplay_name($display_name)
    {
        $this->display_name = $display_name;
    }

    public function getDisplay_desc()
    {
        return $this->display_desc;
    }

    public function setDisplay_desc($display_desc)
    {
        $this->display_desc = $display_desc;
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

    /**
     * get the attributes of the category
     *
     * @return \Zikula\Core\Doctrine\Entity\CategoryAttribute the category's attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * set the attributes for the category
     *
     * @param \Zikula\Core\Doctrine\Entity\CategoryAttribute $attributes the attributes for the category
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
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
            $this->attributes[$name] = new CategoryAttribute($this, $name, $value);
        }
    }

    /**
     * delete a single attribute of the category
     *
     * @param $name string attribute name
     */
    public function delAttribute($name)
    {
        if (isset($this->attributes[$name])) {
            $this->attributes->remove($name);
        }
    }

}
