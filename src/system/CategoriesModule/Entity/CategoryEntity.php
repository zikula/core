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

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Category entity.
 *
 * @ORM\Entity(repositoryClass="Zikula\CategoriesModule\Entity\Repository\CategoryRepository")
 * @ORM\Table(name="categories_category",indexes={@ORM\Index(name="idx_categories_is_leaf",columns={"is_leaf"}),
 *                                                @ORM\Index(name="idx_categories_name",columns={"name"}),
 *                                                @ORM\Index(name="idx_categories_ipath",columns={"ipath","is_leaf","status"}),
 *                                                @ORM\Index(name="idx_categories_status",columns={"status"}),
 *                                                @ORM\Index(name="idx_categories_ipath_status",columns={"ipath","status"})})
 */
class CategoryEntity extends EntityAccess
{
    /**
     * The id of the category
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;

    /**
     * The parent id of the category
     *
     * @ORM\ManyToOne(targetEntity="CategoryEntity", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * @var CategoryEntity
     */
    private $parent;

    /**
     * Any children of this category
     *
     * @ORM\OneToMany(targetEntity="CategoryEntity", mappedBy="parent")
     * @var CategoryEntity
     */
    private $children;

    /**
     * Is the category locked?
     *
     * @ORM\Column(type="boolean", name="is_locked")
     * @var boolean
     */
    private $is_locked;

    /**
     * Is this a leaf category?
     *
     * @ORM\Column(type="boolean", name="is_leaf")
     * @var boolean
     */
    private $is_leaf;

    /**
     * The name of the category
     *
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $name;

    /**
     * The value of the category
     *
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $value;

    /**
     * The sort value for the category
     *
     * @ORM\Column(type="integer", name="sort_value")
     * @var integer
     */
    private $sort_value;

    /**
     * The display name for the category
     *
     * @ORM\Column(type="array", name="display_name")
     * @var array
     */
    private $display_name;

    /**
     * The display description for the category
     *
     * @ORM\Column(type="array", name="display_desc")
     * @var array
     */
    private $display_desc;

    /**
     * The fully qualified path to the category in the tree
     *
     * @ORM\Column(type="text")
     * @var string
     */
    private $path;

    /**
     * The numeric version of the fully qualified path
     *
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $ipath;

    /**
     * The status of the category
     *
     * @ORM\Column(type="string", length=1)
     * @var string
     */
    private $status;

    /**
     * Any attributes of this category
     *
     * @ORM\OneToMany(targetEntity="CategoryAttributeEntity",
     *                mappedBy="category",
     *                cascade={"all"},
     *                orphanRemoval=true,
     *                indexBy="name")
     */
    private $attributes;

    /**
     * The user id of the creator of the category
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="cr_uid", referencedColumnName="uid")
     */
    private $cr_uid;

    /**
     * The user id of the last updater of the category
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="lu_uid", referencedColumnName="uid")
     */
    private $lu_uid;

    /**
     * The creation timestamp of the category
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $cr_date;

    /**
     * The last updated timestamp of the category
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $lu_date;

    /**
     * Same as the status property - maintain BC
     *
     * @deprecated since 1.4.0 use status property instead
     *
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
        $this->is_locked = false; //  was 0
        $this->is_leaf = false; // was 0
        $this->name = '';
        $this->value = '';
        $this->sort_value = 2147483647;
        $this->display_name = [];
        $this->display_desc = [];
        $this->path = '';
        $this->ipath = '';
        $this->status = 'A';
        $this->obj_status = 'A';

        $this->attributes = new ArrayCollection();
    }

    /**
     * get the category id
     *
     * @return int the category id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * set the category id
     *
     * @param int $id the category id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * get the categories parent id
     *
     * @return CategoryEntity
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * set the categories parent id
     *
     * @param CategoryEntity $parent
     */
    public function setParent(CategoryEntity $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * get the categories childen
     *
     * @return array the child categories
     */
    public function getChildren()
    {
        return !empty($this->children) ? $this->children : [];
    }

    /**
     * set the categories childen
     *
     * @param array $children the child categories
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * get the category locked status
     *
     * @return bool locked status flag
     */
    public function getIs_locked()
    {
        return $this->is_locked;
    }

    /**
     * Alias layer for Symfony Forms
     * @return bool
     */
    public function getIsLocked()
    {
        return $this->getIs_locked();
    }

    /**
     * get the category locked status
     *
     * @param bool $is_locked locked status flag
     */
    public function setIs_locked($is_locked)
    {
        $this->is_locked = $is_locked;
    }

    /**
     * Alias layer for Symfony Forms
     * @param $isLocked
     */
    public function setIsLocked($isLocked)
    {
        $this->setIs_locked($isLocked);
    }

    /**
     * get the category leaf status
     *
     * @return bool leaf status flag
     */
    public function getIs_leaf()
    {
        return $this->is_leaf;
    }

    /**
     * Alias layer for Symfony Forms
     * @return bool
     */
    public function getIsLeaf()
    {
        return $this->getIs_leaf();
    }

    /**
     * set the category leaf status
     *
     * @param bool $is_leaf leaft status flag
     */
    public function setIs_leaf($is_leaf)
    {
        $this->is_leaf = $is_leaf;
    }

    /**
     * Alias layer for Symfony Forms
     * @param $isLeaf
     */
    public function setIsLeaf($isLeaf)
    {
        $this->setIs_leaf($isLeaf);
    }

    /**
     * get the category name
     *
     * @return string the category name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * set the category name
     *
     * @param string $name the category name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * get the category value
     *
     * @return string the category name
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * set the category value
     *
     * @param string $value the category name
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * get the category sort value
     *
     * @return int the category name
     */
    public function getSort_value()
    {
        return $this->sort_value;
    }

    /**
     * set the category sort value
     *
     * @param int $sort_value the category name
     */
    public function setSort_value($sort_value)
    {
        $this->sort_value = $sort_value;
    }

    /**
     * get the category display name
     * @param $lang
     *
     * @return array the category display name
     */
    public function getDisplay_name($lang = null)
    {
        if (!empty($lang)) {
            return $this->display_name[$lang];
        }

        return $this->display_name;
    }

    /**
     * set the category display name
     *
     * @param array $display_name the category display name array
     */
    public function setDisplay_name($display_name)
    {
        $this->display_name = $display_name;
    }

    /**
     * get the category display description
     * @param $lang
     *
     * @return array|string the category display description
     */
    public function getDisplay_desc($lang = null)
    {
        if (!empty($lang)) {
            return $this->display_desc[$lang];
        }

        return $this->display_desc;
    }

    /**
     * set the category display description
     *
     * @param array $display_desc the category display description
     */
    public function setDisplay_desc($display_desc)
    {
        $this->display_desc = $display_desc;
    }

    /**
     * get the fully qualified category path
     *
     * @return string the category path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * set the fully qualified category path
     *
     * @param string $path the category path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * get the numeric fully qualified category path
     *
     * @return string the category path
     */
    public function getIPath()
    {
        return $this->ipath;
    }

    /**
     * set the numeric fully qualified category path
     *
     * @param string $ipath the category path
     */
    public function setIPath($ipath)
    {
        $this->ipath = $ipath;
    }

    /**
     * get the category status
     *
     * @return bool the category status
     */
    public function getStatus()
    {
        return $this->status == 'A';
    }

    /**
     * set the category status
     *
     * @param bool $status the category status
     */
    public function setStatus($status)
    {
        if (is_bool($status)) {
            $status = $status ? 'A' : 'I';
        }
        $this->status = $status;
    }

    /**
     * set the creation date of the category
     *
     * @param mixed $cr_date the creation date
     */
    public function setCr_date($cr_date)
    {
        $this->cr_date = $cr_date;
    }

    /**
     * get the creation date of the category
     *
     * @return mixed the creation date
     */
    public function getCr_date()
    {
        return $this->cr_date;
    }

    /**
     * set the creation user id of the category
     *
     * @param int $cr_uid the user id
     */
    public function setCr_uid($cr_uid)
    {
        $this->cr_uid = $cr_uid;
    }

    /**
     * get the creation user id
     *
     * @return int the user id
     */
    public function getCr_uid()
    {
        return $this->cr_uid;
    }

    /**
     * set the last updated timestamp of the category
     *
     * @param mixed $lu_date the last updated timestamp
     */
    public function setLu_date($lu_date)
    {
        $this->lu_date = $lu_date;
    }

    /**
     * get the last updated timestamp of the category
     *
     * @return mixed the last updated timestamp
     */
    public function getLu_date()
    {
        return $this->lu_date;
    }

    /**
     * set the user id of the user who last updated the category
     *
     * @param int $lu_uid the user id
     */
    public function setLu_uid($lu_uid)
    {
        $this->lu_uid = $lu_uid;
    }

    /**
     * get the user id of the user who last updated the category
     *
     * @return int the user id
     */
    public function getLu_uid()
    {
        return $this->lu_uid;
    }

    /**
     * set the status of the object
     *
     * @param string $obj_status the object status
     */
    public function setObj_status($obj_status)
    {
        $this->obj_status = $obj_status;
    }

    /**
     * get the status of the object
     *
     * @return string the object status
     */
    public function getObj_status()
    {
        return $this->obj_status;
    }

    /**
     * get the attributes of the category
     *
     * @return ArrayCollection the category's attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * set the attributes for the category
     *
     * @param ArrayCollection $attributes the attributes for the category
     */
    public function setAttributes(ArrayCollection $attributes)
    {
        $this->attributes = $attributes;
    }

    public function addAttribute(CategoryAttributeEntity $attribute)
    {
        $attribute->setCategory($this);
        $this->attributes->add($attribute);
    }

    public function removeAttribute(CategoryAttributeEntity $attribute)
    {
        $this->attributes->removeElement($attribute);
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
     * Get the lock status of the category
     *
     * @deprecated since 1.4.0 use getIs_locked instead
     *
     * @return bool|int
     */
    public function getLocked()
    {
        return $this->getIs_locked();
    }

    /**
     * Set the lock status of the category
     *
     * @deprecated since 1.4.0 use setIs_locked instead
     *
     * @param bool $locked
     */
    public function setLocked($locked)
    {
        $this->setIs_locked($locked);
    }

    /**
     * Get the leaf status of the category
     *
     * @deprecated since 1.4.0 use getIs_leaf instead
     *
     * @return bool|int
     */
    public function getLeaf()
    {
        return $this->getIs_leaf();
    }

    /**
     * Set the leaf status of the category
     *
     * @deprecated since 1.4.0 use setIs_leaf instead
     *
     * @param bool $leaf
     */
    public function setLeaf($leaf)
    {
        $this->setIs_leaf($leaf);
    }

    /**
     * Get the sort value of the category
     *
     * @deprecated since 1.4.0 use getSort_value instead
     *
     * @return int the sort value
     */
    public function getSortValue()
    {
        return $this->getSort_value();
    }

    /**
     * Set the sort value of the category
     *
     * @deprecated since 1.4.0 use setSort_value instead
     *
     * @param int $sortValue the sort value
     */
    public function setSortValue($sortValue)
    {
        $this->setSort_value($sortValue);
    }

    /**
     * Get the display name(s) of the category
     *
     * @deprecated since 1.4.0 use getDisplay_name instead
     *
     * @return array the display name(s)
     */
    public function getDisplayName()
    {
        return $this->getDisplay_name();
    }

    /**
     * Get the display name(s) of the category
     *
     * @deprecated since 1.4.0 use setDisplay_name instead
     *
     * @param array $displayName the display name(s)
     */
    public function setDisplayName($displayName)
    {
        $this->setDisplay_name($displayName);
    }

    /**
     * Get the display description(s) of the category
     *
     * @deprecated since 1.4.0 use getDisplay_desc instead
     *
     * @return array the display description(s)
     */
    public function getDisplayDesc()
    {
        return $this->getDisplay_desc();
    }

    /**
     * Set the display description(s) of the category
     *
     * @deprecated since 1.4.0 use setDisplay_desc instead
     *
     * @param array $displayDesc the display descriptions(s)
     */
    public function setDisplayDesc($displayDesc)
    {
        $this->setDisplay_desc($displayDesc);
    }
}
