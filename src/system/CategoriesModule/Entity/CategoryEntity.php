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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * Category entity.
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Zikula\CategoriesModule\Entity\Repository\CategoryRepository")
 * @ORM\Table(name="categories_category",indexes={@ORM\Index(name="idx_categories_is_leaf",columns={"is_leaf"}),
 *                                                @ORM\Index(name="idx_categories_name",columns={"name"}),
 *                                                @ORM\Index(name="idx_categories_status",columns={"status"})})
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
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="CategoryEntity")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     */
    private $root;

    /**
     * The parent id of the category
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="CategoryEntity", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * @var CategoryEntity
     */
    private $parent;

    /**
     * Any children of this category
     *
     * @ORM\OneToMany(targetEntity="CategoryEntity", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     * @var ArrayCollection
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
     * constructor
     * @param array $locales
     */
    public function __construct(array $locales = [])
    {
        $this->parent = null;
        $this->children = null;
        $this->is_locked = false; //  was 0
        $this->is_leaf = false; // was 0
        $this->name = '';
        $this->value = '';
        $values = [];
        foreach ($locales as $code) {
            $values[$code] = '';
        }
        $this->display_name = $values;
        $this->display_desc = $values;
        $this->status = 'A';

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
     * @return ArrayCollection the child categories
     */
    public function getChildren()
    {
        return !empty($this->children) ? $this->children : new ArrayCollection();
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
     * get the category display name
     * @param $lang
     *
     * @return array|string the category display name(s)
     */
    public function getDisplay_name($lang = null)
    {
        if (!empty($lang)) {
            if (isset($this->display_desc[$lang])) {
                return $this->display_name[$lang];
            } elseif (isset($this->display_name['en'])) {
                return $this->display_name['en'];
            } else {
                return $this->name;
            }
        }

        return $this->display_name;
    }

    /**
     * Alias to self::getDisplay_name() required for Twig property access
     */
    public function getDisplayName()
    {
        return $this->getDisplay_name();
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
     * Alias to self::setDisplay_name() required for PropertyAccess of collection form type
     *
     * @param array $display_name the category display name array
     */
    public function setDisplayName($display_name)
    {
        $this->setDisplay_name($display_name);
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
     * Alias to self::getDisplay_desc() required for Twig property access
     */
    public function getDisplayDesc($lang = null)
    {
        return $this->getDisplay_desc($lang);
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
<<<<<<< HEAD
=======
     * Alias to self::setDisplay_desc() required for PropertyAccess of collection form type
     *
     * @param array $display_desc the category display description
     */
    public function setDisplayDesc($display_desc)
    {
        $this->setDisplay_desc($display_desc);
    }

    /**
     * get the fully qualified category path
     *
     * @return string the category path
     */
    public function getPath()
    {
        @trigger_error('The path property is deprecated. Use NestedTree functionality instead.', E_USER_DEPRECATED);

        return $this->getPathByField('name');
    }

    /**
     * set the fully qualified category path
     *
     * @param string $path the category path
     */
    public function setPath($path)
    {
        @trigger_error('The path property is no longer available for setting.', E_USER_DEPRECATED);
        // do nothing
    }

    /**
     * get the numeric fully qualified category path
     *
     * @return string the category path
     */
    public function getIPath()
    {
        @trigger_error('The path property is deprecated. Use NestedTree functionality instead.', E_USER_DEPRECATED);

        return $this->getPathByField('id');
    }

    /**
     * @param string $field
     * @return string
     */
    private function getPathByField($field = 'name')
    {
        $path = [];
        $method = 'get' . lcfirst($field);
        $entity = $this;
        do {
            array_unshift($path, $entity->$method());
            $entity = $entity->getParent();
        } while (null !== $entity);

        return '/' . implode('/', $path);
    }

    /**
     * set the numeric fully qualified category path
     *
     * @param string $ipath the category path
     */
    public function setIPath($ipath)
    {
        @trigger_error('The ipath property is no longer available for setting.', E_USER_DEPRECATED);
        // do nothing
    }

    /**
>>>>>>> 1.5
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
            $attribute = new CategoryAttributeEntity();
            $attribute->setCategory($this);
            $attribute->setName($name);
            $attribute->setValue($value);
            $this->attributes[$name] = $attribute;
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
     * @return int
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * @param int $lft
     */
    public function setLft($lft)
    {
        $this->lft = $lft;
    }

    /**
     * @return int
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * @param int $lvl
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;
    }

    /**
     * @return int
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * @param int $rgt
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;
    }

    /**
     * @return CategoryEntity
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param CategoryEntity $root
     */
    public function setRoot(CategoryEntity $root)
    {
        $this->root = $root;
    }

    public function toJson($prefix = '', $locale = 'en')
    {
        return json_encode([
            'id' => $prefix . $this->id,
            'text' => $this->getDisplay_name($locale),
            'name' => $this->name,
            'display_name' => $this->display_name,
            'display_desc' => $this->display_desc,
            'value' => $this->value,
            'status' => $this->status,
            'is_leaf' => $this->is_leaf,
            'is_locked' => $this->is_locked,
            'parent' => $this->parent->getId(),
//            'children' => $this->children,
            'root' => null !== $this->root ? $this->root->getId() : null
        ]);
    }

    /**
     * required for repository->recover() method
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    public function __clone()
    {
        $this->id = null;
    }
}
