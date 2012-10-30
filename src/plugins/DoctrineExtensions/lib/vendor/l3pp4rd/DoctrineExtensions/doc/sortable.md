# Sortable behavior extension for Doctrine2

**Sortable** behavior will maintain a position field for ordering
entities.

Features:

- Automatic handling of position index
- Group entity ordering by one or more fields
- Can be nested with other behaviors
- Annotation, Yaml and Xml mapping support for extensions

[blog_reference]: http://gediminasm.org/article/sortable-behavior-extension-for-doctrine2 "Sortable extension will enable ordering on any entity or its relation"
[blog_test]: http://gediminasm.org/test "Test extensions on this blog"

**Note:**

- Public [Sortable repository](http://github.com/l3pp4rd/DoctrineExtensions "Sortable extension on Github") is available on github
- Last update date: **2012-01-02**

**Portability:**

- **Sortable** is now available as [Bundle](http://github.com/stof/StofDoctrineExtensionsBundle)
ported to **Symfony2** by **Christophe Coevoet**, together with all other extensions

This article will cover the basic installation and functionality of **Sortable**
behavior

Content:

- [Including](#including-extension) the extension
- Entity [example](#entity-mapping)
- [Yaml](#yaml-mapping) mapping example
- [Xml](#xml-mapping) mapping example
- Basic usage [examples](#basic-examples)


<a name="including-extension"></a>

## Setup and autoloading

Read the [documentation](http://github.com/l3pp4rd/DoctrineExtensions/blob/master/doc/annotations.md#em-setup)
or check the [example code](http://github.com/l3pp4rd/DoctrineExtensions/tree/master/example)
on how to setup and use the extensions in most optimized way.

<a name="entity-mapping"></a>

## Sortable Entity example:

### Sortable annotations:

- **@Gedmo\Mapping\Annotation\SortableGroup** it will use this field for **grouping**
- **@Gedmo\Mapping\Annotation\SortablePosition** it will use this column to store **position** index

**Note:** that Sortable interface is not necessary, except in cases there
you need to identify entity as being Sortable. The metadata is loaded only once then
cache is activated

**Note:** that you should register SortableRepository (or a subclass) as the repository in the Entity
annotation to benefit from its query methods.

``` php
<?php
namespace Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="items")
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class Item
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=64)
     */
    private $name;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

    /**
     * @Gedmo\SortableGroup
     * @ORM\Column(name="category", type="string", length=128)
     */
    private $category;

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }
    
    public function setCategory($category)
    {
        $this->category = $category;
    }
    
    public function getCategory()
    {
        return $this->category;
    }
}
```

<a name="yaml-mapping"></a>

## Yaml mapping example

Yaml mapped Item: **/mapping/yaml/Entity.Item.dcm.yml**

```
---
Entity\Item:
  type: entity
  table: items
  id:
    id:
      type: integer
      generator:
        strategy: AUTO
  fields:
    name:
      type: string
      length: 64
    position:
      type: integer
      gedmo:
        - sortablePosition
    category:
      type: string
      length: 128
      gedmo:
        - sortableGroup
```

<a name="xml-mapping"></a>

## Xml mapping example

``` xml
<?xml version="1.0" encoding="UTF-8"?>
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:gedmo="http://gediminasm.org/schemas/orm/doctrine-extensions-mapping">
    <entity name="Entity\Item" table="items">
        <id name="id" type="integer" column="id">
            <generator strategy="AUTO"/>
        </id>

        <field name="name" type="string" length="128">
        </field>

        <field name="position" type="integer">
            <gedmo:sortable-position/>
        </field>
        <field name="category" type="string" length="128">
            <gedmo:sortable-group />
        </field>
    </entity>
</doctrine-mapping>
```

<a name="basic-examples"></a>

## Basic usage examples:

### To save **Items** at the end of the sorting list simply do:

``` php
<?php
// By default, items are appended to the sorting list
$item1 = new Item();
$item1->setName('item 1');
$item1->setCategory('category 1');
$this->em->persist($item1);

$item2 = new Item();
$item2->setName('item 2');
$item2->setCategory('category 1');
$this->em->persist($item2);

$this->em->flush();

echo $item1->getPosition();
// prints: 0
echo $item2->getPosition();
// prints: 1
```

### Save **Item** at a given position:

``` php
<?php
$item1 = new Item();
$item1->setName('item 1');
$item1->setCategory('category 1');
$this->em->persist($item1);

$item2 = new Item();
$item2->setName('item 2');
$item2->setCategory('category 1');
$this->em->persist($item2);

$item0 = new Item();
$item0->setName('item 0');
$item0->setCategory('category 1');
$item0->setPosition(0);
$this->em->persist($item0);

$this->em->flush();

$repo = $this->em->getRepository('Entity\\Item');
$items = $repo->getBySortableGroupsQuery(array('category' => 'category 1'))->getResult();
foreach ($items as $item) {
    echo "{$item->getPosition()}: {$item->getName()}\n";
}
// prints:
// 0: item 0
// 1: item 1
// 2: item 2
```

### Reordering the sorted list:

``` php
<?php
$item1 = new Item();
$item1->setName('item 1');
$item1->setCategory('category 1');
$this->em->persist($item1);

$item2 = new Item();
$item2->setName('item 2');
$item2->setCategory('category 1');
$this->em->persist($item2);

$this->em->flush();

// Update the position of item2
$item2->setPosition(0);
$this->em->persist($item2);

$this->em->flush();

$repo = $this->em->getRepository('Entity\\Item');
$items = $repo->getBySortableGroupsQuery(array('category' => 'category 1'))->getResult();
foreach ($items as $item) {
    echo "{$item->getPosition()}: {$item->getName()}\n";
}
// prints:
// 0: item 2
// 1: item 1
```
