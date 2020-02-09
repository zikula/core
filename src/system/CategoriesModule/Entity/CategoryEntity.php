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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Zikula\CategoriesModule\Traits\StandardFieldsTrait;

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
    use StandardFieldsTrait;

    /**
     * The id of the category
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     * @var int
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     * @var int
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     * @var int
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="CategoryEntity")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     * @var self
     */
    private $root;

    /**
     * The parent id of the category
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="CategoryEntity", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * @var self
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
    private $locked;

    /**
     * Is this a leaf category?
     *
     * @ORM\Column(type="boolean", name="is_leaf")
     * @var boolean
     */
    private $leaf;

    /**
     * The name of the category
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="0", max="255", allowEmptyString="false")
     * @var string
     */
    private $name;

    /**
     * The value of the category
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="0", max="255", allowEmptyString="true")
     * @var string
     */
    private $value;

    /**
     * The display name for the category
     *
     * @ORM\Column(type="array", name="display_name")
     * @var array
     */
    private $displayName;

    /**
     * The display description for the category
     *
     * @ORM\Column(type="array", name="display_desc")
     * @var array
     */
    private $displayDesc;

    /**
     * The status of the category
     *
     * @ORM\Column(type="string", length=1)
     * @Assert\Length(min="0", max="1", allowEmptyString="false")
     * @var string
     */
    private $status;

    /**
     * The category icon
     *
     * @ORM\Column(type="string", length=50)
     * @Assert\Length(min="0", max="50", allowEmptyString="true")
     * @var string
     */
    private $icon;

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
     * constructor
     * @param array $locales
     */
    public function __construct(array $locales = [])
    {
        $this->locked = false; //  was 0
        $this->leaf = false; // was 0
        $this->name = '';
        $this->value = '';
        $values = [];
        foreach ($locales as $code) {
            $values[$code] = '';
        }
        $this->displayName = $values;
        $this->displayDesc = $values;
        $this->status = 'A';
        $this->icon = '';

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

    public function getLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }

    public function getLeaf(): bool
    {
        return $this->leaf;
    }

    public function setLeaf(bool $leaf): void
    {
        $this->leaf = $leaf;
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
    public function getDisplayName(string $lang = null)
    {
        if (!empty($lang)) {
            return $this->displayName[$lang] ?? $this->displayName['en'] ?? $this->name;
        }

        return $this->displayName;
    }

    public function setDisplayName(array $displayName): void
    {
        $this->displayName = $displayName;
    }

    /**
     * @return array|string the category display description
     */
    public function getDisplayDesc(string $lang = null)
    {
        if (!empty($lang)) {
            return $this->displayDesc[$lang] ?? $this->displayDesc['en'] ?? '';
        }

        return $this->displayDesc;
    }

    public function setDisplayDesc(array $displayDesc): void
    {
        $this->displayDesc = $displayDesc;
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

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon ?? '';
    }

    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function setAttributes(Collection $attributes): void
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
            'text' => $this->getDisplayName($locale),
            'name' => $this->name,
            'displayName' => $this->displayName,
            'displayDesc' => $this->displayDesc,
            'value' => $this->value,
            'status' => $this->status,
            'leaf' => $this->leaf,
            'locked' => $this->locked,
            'parent' => $this->parent->getId(),
            //'children' => $this->children,
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
