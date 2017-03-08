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
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Zikula\CategoriesModule\Entity\Repository\CategoryRepository")
 * @ORM\Table(name="categories_category",indexes={@ORM\Index(name="idx_categories_is_leaf",columns={"is_leaf"}),
 *                                                @ORM\Index(name="idx_categories_name",columns={"name"}),
 *                                                @ORM\Index(name="idx_categories_status",columns={"status"})})
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
     * @var Zikula_Doctrine2_Entity_Category
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
        return null;
    }

    public function setSortValue($sortValue)
    {
        // do nothing
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

        return implode('/', $path);
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
            $attribute = new CategoryAttributeEntity();
            $attribute->setCategory($this);
            $attribute->setName($name);
            $attribute->setValue($value);
            $this->attributes[$name] = $attribute;
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
     * @return Zikula_Doctrine2_Entity_Category
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param Zikula_Doctrine2_Entity_Category $root
     */
    public function setRoot(Zikula_Doctrine2_Entity_Category $root)
    {
        $this->root = $root;
    }
}
