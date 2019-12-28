<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zikula\Core\Doctrine\EntityAccess;
use Zikula\UsersModule\Entity\UserEntity;

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
     * @var UserEntity
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="cr_uid", referencedColumnName="uid")
     */
    private $cr_uid;

    /**
     * The user id of the last updater of the category
     *
     * @var UserEntity
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="lu_uid", referencedColumnName="uid")
     */
    private $lu_uid;

    /**
     * The creation timestamp of the category
     *
     * @var DateTime
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $cr_date;

    /**
     * The last updated timestamp of the category
     *
     * @var DateTime
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getParent(): ?CategoryEntity
    {
        return $this->parent;
    }

    public function setParent(CategoryEntity $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getChildren(): Collection
    {
        return !empty($this->children) ? $this->children : new ArrayCollection();
    }

    public function setChildren(Collection $children): void
    {
        $this->children = $children;
    }

    public function getIs_locked(): bool
    {
        return $this->is_locked;
    }

    /**
     * Alias for Symfony Forms.
     */
    public function getIsLocked(): bool
    {
        return $this->getIs_locked();
    }

    public function setIs_locked(bool $is_locked): void
    {
        $this->is_locked = $is_locked;
    }

    /**
     * Alias for Symfony Forms
     */
    public function setIsLocked(bool $isLocked): void
    {
        $this->setIs_locked($isLocked);
    }

    public function getIs_leaf(): bool
    {
        return $this->is_leaf;
    }

    /**
     * Alias for Symfony Forms
     */
    public function getIsLeaf(): bool
    {
        return $this->getIs_leaf();
    }

    public function setIs_leaf(bool $is_leaf): void
    {
        $this->is_leaf = $is_leaf;
    }

    /**
     * Alias for Symfony Forms
     */
    public function setIsLeaf(bool $isLeaf): void
    {
        $this->setIs_leaf($isLeaf);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return array|string the category display name(s)
     */
    public function getDisplay_name(string $lang = null)
    {
        if (!empty($lang)) {
            return $this->display_name[$lang] ?? $this->display_name['en'] ?? $this->name;
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

    public function setDisplay_name(array $display_name): void
    {
        $this->display_name = $display_name;
    }

    /**
     * Alias to self::setDisplay_name() required for PropertyAccess of collection form type
     */
    public function setDisplayName(array $display_name): void
    {
        $this->setDisplay_name($display_name);
    }

    /**
     * @return array|string the category display description
     */
    public function getDisplay_desc(string $lang = null)
    {
        if (!empty($lang)) {
            return $this->display_desc[$lang] ?? $this->display_desc['en'] ?? '';
        }

        return $this->display_desc;
    }

    /**
     * Alias to self::getDisplay_desc() required for Twig property access
     */
    public function getDisplayDesc(string $lang = null)
    {
        return $this->getDisplay_desc($lang);
    }

    public function setDisplay_desc(array $display_desc): void
    {
        $this->display_desc = $display_desc;
    }

    /**
     * Alias to self::setDisplay_desc() required for PropertyAccess of collection form type
     */
    public function setDisplayDesc(array $display_desc): void
    {
        $this->setDisplay_desc($display_desc);
    }

    public function getStatus(): bool
    {
        return 'A' === $this->status;
    }

    public function setStatus($status): void
    {
        if (is_bool($status)) {
            $status = $status ? 'A' : 'I';
        }
        $this->status = $status;
    }

    public function getCr_date(): DateTime
    {
        return $this->cr_date;
    }

    public function setCr_date(DateTime $cr_date): void
    {
        $this->cr_date = $cr_date;
    }

    public function getCr_uid(): UserEntity
    {
        return $this->cr_uid;
    }

    public function setCr_uid(UserEntity $cr_uid): void
    {
        $this->cr_uid = $cr_uid;
    }

    public function getLu_date(): DateTime
    {
        return $this->lu_date;
    }

    public function setLu_date(DateTime $lu_date): void
    {
        $this->lu_date = $lu_date;
    }

    public function getLu_uid(): UserEntity
    {
        return $this->lu_uid;
    }

    public function setLu_uid(UserEntity $lu_uid): void
    {
        $this->lu_uid = $lu_uid;
    }

    public function getAttributes(): ArrayCollection
    {
        return $this->attributes;
    }

    public function setAttributes(ArrayCollection $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function addAttribute(CategoryAttributeEntity $attribute): void
    {
        $attribute->setCategory($this);
        $this->attributes->add($attribute);
    }

    public function removeAttribute(CategoryAttributeEntity $attribute): void
    {
        $this->attributes->removeElement($attribute);
    }

    public function setAttribute(string $name, string $value): void
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

    public function delAttribute(string $name): void
    {
        if (isset($this->attributes[$name])) {
            $this->attributes->remove($name);
        }
    }

    public function getLft(): int
    {
        return $this->lft;
    }

    public function setLft(int $lft): void
    {
        $this->lft = $lft;
    }

    public function getLvl(): int
    {
        return $this->lvl;
    }

    public function setLvl(int $lvl): void
    {
        $this->lvl = $lvl;
    }

    public function getRgt(): int
    {
        return $this->rgt;
    }

    public function setRgt(int $rgt): void
    {
        $this->rgt = $rgt;
    }

    public function getRoot(): self
    {
        return $this->root;
    }

    public function setRoot(CategoryEntity $root): void
    {
        $this->root = $root;
    }

    public function toJson(string $prefix = '', string $locale = 'en'): string
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
            'root' => null !== $this->getRoot() ? $this->getRoot()->getId() : null
        ]);
    }

    /**
     * Required for repository->recover() method.
     */
    public function __toString(): string
    {
        return $this->name;
    }

    public function __clone()
    {
        $this->id = null;
    }
}
