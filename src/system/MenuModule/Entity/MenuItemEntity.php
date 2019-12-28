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

namespace Zikula\MenuModule\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\Menu\NodeInterface;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * Represents one menu item in a nested set.
 * Extends NodeInterface in order to integrate with KnpMenu.
 *
 * @ORM\Entity(repositoryClass="Zikula\MenuModule\Entity\Repository\MenuItemRepository")
 * @ORM\Table(name="menu_items")
 * @Gedmo\Tree(type="nested")
 */
class MenuItemEntity extends EntityAccess implements NodeInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(length=64)
     */
    private $title;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="MenuItemEntity")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="MenuItemEntity", inversedBy="children")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="MenuItemEntity", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * @ORM\Column(type="array")
     * possible keys
     * [
     *  'route' => null,
     *  'routeParameters' => [],
     *  'icon' => null,
     *  'uri' => null,
     *  'label' => null,
     *  'attributes' => [],
     *  'linkAttributes' => [],
     *  'childrenAttributes' => [],
     *  'labelAttributes' => [],
     *  'extras' => [],
     *  'current' => null,
     *  'display' => true,
     *  'displayChildren' => true,
     * ]
     */
    private $options;

    /**
     * MenuItemEntity constructor.
     */
    public function __construct()
    {
        $this->title = '';
        $this->options = []; /*new ArrayCollection();
        $this->options = [
            'routeParameters' => [],
            'attributes' => [],
            'linkAttributes' => [],
            'childrenAttributes' => [],
            'labelAttributes' => [],
            'extras' => [],
            'display' => true,
            'displayChildren' => true,
        ];*/
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getLft(): int
    {
        return $this->lft;
    }

    public function getLvl(): int
    {
        return $this->lvl;
    }

    public function getRgt(): int
    {
        return $this->rgt;
    }

    public function setRoot(self $root): void
    {
        $this->root = $root;
    }

    public function getRoot(): self
    {
        return $this->root;
    }

    public function setParent(self $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getName(): string
    {
        return $this->title;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    public function setOptions(array $options = []): void
    {
        $this->options = $options;
    }

    public function setOption(string $name, $value): void
    {
        $this->options[$name] = $value;
    }

    public function removeOption(string $name): void
    {
        unset($this->options[$name]);
    }

    public function hasOptions(): bool
    {
        return !empty($this->options);
    }

    public function getChildren(): \Traversable
    {
        return $this->children;
    }

    public function getAttributes(): array
    {
        return $this->hasAttributes() ? $this->options['attributes'] : [];
    }

    public function getAttribute(string $name, $default = null)
    {
        return $this->options['attributes'][$name] ?? $default;
    }

    public function setAttributes(array $attributes = []): void
    {
        $this->options['attributes'] = $attributes;
    }

    public function setAttribute(string $name, $value): void
    {
        $this->options['attributes'][$name] = $value;
    }

    public function removeAttribute(string $name): void
    {
        unset($this->options['attributes'][$name]);
    }

    public function hasAttributes(): bool
    {
        return !empty($this->options['attributes']);
    }

    public function getLinkAttributes(): array
    {
        return $this->hasLinkAttributes() ? $this->options['linkAttributes'] : [];
    }

    public function getLinkAttribute(string $name, $default = null)
    {
        return $this->options['linkAttributes'][$name] ?? $default;
    }

    public function setLinkAttributes(array $attributes = []): void
    {
        $this->options['linkAttributes'] = $attributes;
    }

    public function setLinkAttribute(string $name, $value): void
    {
        $this->options['linkAttributes'][$name] = $value;
    }

    public function removeLinkAttribute(string $name): void
    {
        unset($this->options['linkAttributes'][$name]);
    }

    public function hasLinkAttributes(): bool
    {
        return !empty($this->options['linkAttributes']);
    }

    public function getChildrenAttributes(): array
    {
        return $this->hasChildrenAttributes() ? $this->options['childrenAttributes'] : [];
    }

    public function getChildrenAttribute(string $name, $default = null)
    {
        return $this->options['childrenAttributes'][$name] ?? $default;
    }

    public function setChildrenAttributes(array $attributes = []): void
    {
        $this->options['childrenAttributes'] = $attributes;
    }

    public function setChildrenAttribute(string $name, $value): void
    {
        $this->options['childrenAttributes'][$name] = $value;
    }

    public function removeChildrenAttribute(string $name): void
    {
        unset($this->options['childrenAttributes'][$name]);
    }

    public function hasChildrenAttributes(): bool
    {
        return !empty($this->options['childrenAttributes']);
    }

    public function getLabelAttributes(): array
    {
        return $this->hasLabelAttributes() ? $this->options['labelAttributes'] : [];
    }

    public function getLabelAttribute(string $name, $default = null)
    {
        return $this->options['labelAttributes'][$name] ?? $default;
    }

    public function setLabelAttributes(array $attributes = []): void
    {
        $this->options['labelAttributes'] = $attributes;
    }

    public function setLabelAttribute(string $name, $value): void
    {
        $this->options['labelAttributes'][$name] = $value;
    }

    public function removeLabelAttribute(string $name): void
    {
        unset($this->options['labelAttributes'][$name]);
    }

    public function hasLabelAttributes(): bool
    {
        return !empty($this->options['labelAttributes']);
    }

    public function getExtras(): array
    {
        return $this->hasExtras() ? $this->options['extras'] : [];
    }

    public function getExtra(string $name, $default = null)
    {
        return $this->options['extras'][$name] ?? $default;
    }

    public function setExtras(array $attributes = []): void
    {
        $this->options['extras'] = $attributes;
    }

    public function setExtra(string $name, $value): void
    {
        $this->options['extras'][$name] = $value;
    }

    public function removeExtra(string $name): void
    {
        unset($this->options['extras'][$name]);
    }

    public function hasExtras(): bool
    {
        return !empty($this->options['extras']);
    }

    public function toJson($prefix = ''): string
    {
        return json_encode([
            'id' => $prefix . $this->id,
            'title' => $this->title,
            'text' => $this->title,
            'options' => $this->options,
            'parent' => null !== $this->getParent() ? $this->getParent()->getId() : null,
            'root' => null !== $this->getRoot() ? $this->getRoot()->getId() : null
        ]);
    }
}
