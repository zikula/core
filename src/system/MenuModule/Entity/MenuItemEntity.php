<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\Menu\NodeInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

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
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(length=64)
     * @Assert\Length(min="1", max="64")
     * @var string
     */
    private $title;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     * @var int
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     * @var int
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     * @var int
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="MenuItemEntity")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     * @var self
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="MenuItemEntity", inversedBy="children")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     * @var self
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
        $this->options = [
            /*'routeParameters' => [],
            'attributes' => [],
            'linkAttributes' => [],
            'childrenAttributes' => [],
            'labelAttributes' => [],
            'extras' => [],
            'display' => true,
            'displayChildren' => true*/
        ];
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
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

    public function getRoot(): ?self
    {
        return $this->root;
    }

    public function setRoot(self $root): self
    {
        $this->root = $root;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(self $parent = null): self
    {
        $this->parent = $parent;

        return $this;
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

    public function setOptions(array $options = []): self
    {
        $this->options = $options;

        return $this;
    }

    public function setOption(string $name, $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function removeOption(string $name): self
    {
        if ($this->hasOption($name)) {
            unset($this->options[$name]);
        }

        return $this;
    }

    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
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

    /**
     * @return mixed
     */
    public function getAttribute(string $name, $default = null)
    {
        return $this->options['attributes'][$name] ?? $default;
    }

    public function setAttributes(array $attributes = []): self
    {
        $this->options['attributes'] = $attributes;

        return $this;
    }

    public function setAttribute(string $name, $value): self
    {
        $this->options['attributes'][$name] = $value;

        return $this;
    }

    public function removeAttribute(string $name): self
    {
        if (isset($this->options['attributes'][$name])) {
            unset($this->options['attributes'][$name]);
        }

        return $this;
    }

    public function hasAttributes(): bool
    {
        return !empty($this->options['attributes']);
    }

    public function getLinkAttributes(): array
    {
        return $this->hasLinkAttributes() ? $this->options['linkAttributes'] : [];
    }

    /**
     * @return mixed
     */
    public function getLinkAttribute(string $name, $default = null)
    {
        return $this->options['linkAttributes'][$name] ?? $default;
    }

    public function setLinkAttributes(array $attributes = []): self
    {
        $this->options['linkAttributes'] = $attributes;

        return $this;
    }

    public function setLinkAttribute(string $name, $value): self
    {
        $this->options['linkAttributes'][$name] = $value;

        return $this;
    }

    public function removeLinkAttribute(string $name): self
    {
        if (isset($this->options['linkAttributes'][$name])) {
            unset($this->options['linkAttributes'][$name]);
        }

        return $this;
    }

    public function hasLinkAttributes(): bool
    {
        return !empty($this->options['linkAttributes']);
    }

    public function getChildrenAttributes(): array
    {
        return $this->hasChildrenAttributes() ? $this->options['childrenAttributes'] : [];
    }

    /**
     * @return mixed
     */
    public function getChildrenAttribute(string $name, $default = null)
    {
        return $this->options['childrenAttributes'][$name] ?? $default;
    }

    public function setChildrenAttributes(array $attributes = []): self
    {
        $this->options['childrenAttributes'] = $attributes;

        return $this;
    }

    public function setChildrenAttribute(string $name, $value): self
    {
        $this->options['childrenAttributes'][$name] = $value;

        return $this;
    }

    public function removeChildrenAttribute(string $name): self
    {
        if (isset($this->options['childrenAttributes'][$name])) {
            unset($this->options['childrenAttributes'][$name]);
        }

        return $this;
    }

    public function hasChildrenAttributes(): bool
    {
        return !empty($this->options['childrenAttributes']);
    }

    public function getLabelAttributes(): array
    {
        return $this->hasLabelAttributes() ? $this->options['labelAttributes'] : [];
    }

    /**
     * @return mixed
     */
    public function getLabelAttribute(string $name, $default = null)
    {
        return $this->options['labelAttributes'][$name] ?? $default;
    }

    public function setLabelAttributes(array $attributes = []): self
    {
        $this->options['labelAttributes'] = $attributes;

        return $this;
    }

    public function setLabelAttribute(string $name, $value): self
    {
        $this->options['labelAttributes'][$name] = $value;

        return $this;
    }

    public function removeLabelAttribute(string $name): self
    {
        if (isset($this->options['labelAttributes'][$name])) {
            unset($this->options['labelAttributes'][$name]);
        }

        return $this;
    }

    public function hasLabelAttributes(): bool
    {
        return !empty($this->options['labelAttributes']);
    }

    public function getExtras(): array
    {
        return $this->hasExtras() ? $this->options['extras'] : [];
    }

    /**
     * @return mixed
     */
    public function getExtra(string $name, $default = null)
    {
        return $this->options['extras'][$name] ?? $default;
    }

    public function setExtras(array $attributes = []): self
    {
        $this->options['extras'] = $attributes;

        return $this;
    }

    public function setExtra(string $name, $value): self
    {
        $this->options['extras'][$name] = $value;

        return $this;
    }

    public function removeExtra(string $name): self
    {
        if (isset($this->options['extras'][$name])) {
            unset($this->options['extras'][$name]);
        }

        return $this;
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
