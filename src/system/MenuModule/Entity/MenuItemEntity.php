<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
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
        $this->options = []; //new ArrayCollection();
        //        $this->options = [
        //            'routeParameters' => [],
        //            'attributes' => [],
        //            'linkAttributes' => [],
        //            'childrenAttributes' => [],
        //            'labelAttributes' => [],
        //            'extras' => [],
        //            'display' => true,
        //            'displayChildren' => true,
        //        ];
        $this->children = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setRoot(self $root)
    {
        $this->root = $root;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function setParent(self $parent = null)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getName()
    {
        return $this->title;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    public function setOptions($options = [])
    {
        $this->options = $options;
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function removeOption($name)
    {
        unset($this->options[$name]);
    }

    public function hasOptions()
    {
        return !empty($this->options);
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getAttributes()
    {
        return $this->options['attributes'];
    }

    public function getAttribute($name, $default = null)
    {
        return isset($this->options['attributes'][$name]) ? $this->options['attributes'][$name] : $default;
    }

    public function setAttributes($attributes)
    {
        $this->options['attributes'] = $attributes;
    }

    public function setAttribute($name, $value)
    {
        $this->options['attributes'][$name] = $value;
    }

    public function removeAttribute($name)
    {
        unset($this->options['attributes'][$name]);
    }

    public function hasAttributes()
    {
        return !empty($this->options['attributes']);
    }

    public function getLinkAttributes()
    {
        return $this->options['linkAttributes'];
    }

    public function getLinkAttribute($name, $default = null)
    {
        return isset($this->options['linkAttributes'][$name]) ? $this->options['linkAttributes'][$name] : $default;
    }

    public function setLinkAttributes($attributes)
    {
        $this->options['linkAttributes'] = $attributes;
    }

    public function setLinkAttribute($name, $value)
    {
        $this->options['linkAttributes'][$name] = $value;
    }

    public function removeLinkAttribute($name)
    {
        unset($this->options['linkAttributes'][$name]);
    }

    public function hasLinkAttributes()
    {
        return !empty($this->options['linkAttributes']);
    }

    public function getChildrenAttributes()
    {
        return $this->options['childrenAttributes'];
    }

    public function getChildrenAttribute($name, $default = null)
    {
        return isset($this->options['childrenAttributes'][$name]) ? $this->options['childrenAttributes'][$name] : $default;
    }

    public function setChildrenAttributes($attributes)
    {
        $this->options['childrenAttributes'] = $attributes;
    }

    public function setChildrenAttribute($name, $value)
    {
        $this->options['childrenAttributes'][$name] = $value;
    }

    public function removeChildrenAttribute($name)
    {
        unset($this->options['childrenAttributes'][$name]);
    }

    public function hasChildrenAttributes()
    {
        return !empty($this->options['childrenAttributes']);
    }

    public function getLabelAttributes()
    {
        return $this->options['labelAttributes'];
    }

    public function getLabelAttribute($name, $default = null)
    {
        return isset($this->options['labelAttributes'][$name]) ? $this->options['labelAttributes'][$name] : $default;
    }

    public function setLabelAttributes($attributes)
    {
        $this->options['labelAttributes'] = $attributes;
    }

    public function setLabelAttribute($name, $value)
    {
        $this->options['labelAttributes'][$name] = $value;
    }

    public function removeLabelAttribute($name)
    {
        unset($this->options['labelAttributes'][$name]);
    }

    public function hasLabelAttributes()
    {
        return !empty($this->options['labelAttributes']);
    }

    public function getExtras()
    {
        return $this->options['extras'];
    }

    public function getExtra($name, $default = null)
    {
        return isset($this->options['extras'][$name]) ? $this->options['extras'][$name] : $default;
    }

    public function setExtras($attributes)
    {
        $this->options['extras'] = $attributes;
    }

    public function setExtra($name, $value)
    {
        $this->options['extras'][$name] = $value;
    }

    public function removeExtra($name)
    {
        unset($this->options['extras'][$name]);
    }

    public function hasExtras()
    {
        return !empty($this->options['extras']);
    }

    public function toJson($prefix = '')
    {
        return json_encode([
            'id' => $prefix . $this->id,
            'title' => $this->title,
            'text' => $this->title,
            'options' => $this->options,
            'parent' => $this->parent->getId(),
            'root' => null !== $this->root ? $this->root->getId() : null
        ]);
    }
}
