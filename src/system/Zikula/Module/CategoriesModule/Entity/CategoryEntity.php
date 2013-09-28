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

namespace Zikula\Module\CategoriesModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Category entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="categories_category",indexes={@ORM\Index(name="idx_categories_is_leaf",columns={"is_leaf"}),
 *                                                @ORM\Index(name="idx_categories_name",columns={"name"}),
 *                                                @ORM\Index(name="idx_categories_ipath",columns={"ipath","is_leaf","status"}),
 *                                                @ORM\Index(name="idx_categories_status",columns={"status"}),
 *                                                @ORM\Index(name="idx_categories_ipath_status",columns={"ipath","status"})})
 */
class CategoryEntity extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="CategoryEntity", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * @var CategoryEntity
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="CategoryEntity", mappedBy="parent")
     * @var CategoryEntity
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
     * @ORM\OneToMany(targetEntity="CategoryAttributeEntity",
     *                mappedBy="category",
     *                cascade={"all"},
     *                orphanRemoval=true,
     *                indexBy="name")
     */
    private $attributes;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Zikula\Module\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="cr_uid", referencedColumnName="uid")
     */
    private $cr_uid;

    /**
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="Zikula\Module\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="lu_uid", referencedColumnName="uid")
     */
    private $lu_uid;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $cr_date;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $lu_date;

    /**
     * maintain BC (same as status)
     * @ORM\Column(type="string", length=1)
     * @var string
     */
    private $obj_status = 'A';

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
        $this->status = 'A';
        $this->obj_status = 'A';

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

    public function setParent(CategoryEntity $parent = null)
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
     * @param mixed $cr_date
     */
    public function setCr_date($cr_date)
    {
        $this->cr_date = $cr_date;
    }

    /**
     * @return mixed
     */
    public function getCr_date()
    {
        return $this->cr_date;
    }

    /**
     * @param mixed $cr_uid
     */
    public function setCr_uid($cr_uid)
    {
        $this->cr_uid = $cr_uid;
    }

    /**
     * @return mixed
     */
    public function getCr_uid()
    {
        return $this->cr_uid;
    }

    /**
     * @param mixed $lu_date
     */
    public function setLu_date($lu_date)
    {
        $this->lu_date = $lu_date;
    }

    /**
     * @return mixed
     */
    public function getLu_date()
    {
        return $this->lu_date;
    }

    /**
     * @param mixed $lu_uid
     */
    public function setLu_uid($lu_uid)
    {
        $this->lu_uid = $lu_uid;
    }

    /**
     * @return mixed
     */
    public function getLu_uid()
    {
        return $this->lu_uid;
    }

    /**
     * @param string $obj_status
     */
    public function setObj_status($obj_status)
    {
        $this->obj_status = $obj_status;
    }

    /**
     * @return string
     */
    public function getObj_status()
    {
        return $this->obj_status;
    }

    /**
     * get the attributes of the category
     *
     * @return CategoryAttributeEntity the category's attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * set the attributes for the category
     *
     * @param CategoryAttributeEntity $attributes the attributes for the category
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
            $this->attributes[$name] = new CategoryAttributeEntity($this, $name, $value);
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

    /**
     * @deprecated since 1.3.6
     *
     * @return bool|int
     */
    public function getLocked()
    {
        return $this->getIs_locked();
    }

    /**
     * @deprecated since 1.3.6
     *
     * @param $locked
     */
    public function setLocked($locked)
    {
        $this->setIs_locked($locked);
    }

    /**
     * @deprecated since 1.3.6
     *
     * @return bool|int
     */
    public function getLeaf()
    {
        return $this->getIs_leaf();
    }

    /**
     * @deprecated since 1.3.6
     *
     * @param $leaf
     */
    public function setLeaf($leaf)
    {
        $this->setIs_leaf($leaf);
    }

    /**
     * @deprecated since 1.3.6
     *
     * @return int
     */
    public function getSortValue()
    {
        return $this->getSort_value();
    }

    /**
     * @deprecated since 1.3.6
     *
     * @param $sortValue
     */
    public function setSortValue($sortValue)
    {
        $this->setSort_value($sortValue);
    }

    /**
     * @deprecated since 1.3.6
     *
     * @return array
     */
    public function getDisplayName()
    {
        return $this->getDisplay_name();
    }

    /**
     * @deprecated since 1.3.6
     *
     * @param $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->setDisplay_name($displayName);
    }

    /**
     * @deprecated since 1.3.6
     *
     * @return array
     */
    public function getDisplayDesc()
    {
        return $this->getDisplay_desc();
    }

    /**
     * @deprecated since 1.3.6
     *
     * @param $displayDesc
     */
    public function setDisplayDesc($displayDesc)
    {
        $this->setDisplay_desc($displayDesc);
    }
}
