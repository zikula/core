---
currentMenu: developer-categories
---
# AbstractCategoryAssignment

The categories module provides an abstract base class in `\Zikula\CategoriesModule\Entity\AbstractCategoryAssignment`. This
is not a category, but rather a category assignment. It is recommended to name your entity properties accordingly to reflect
the more accurate naming, e.g. `categoryAssignments` instead of `categories`.

## Purpose

The class exists to make connection to Core categories easier for third-party entities. Simply create a child class
Entity that extends `AbstractCategoryAssignment` and define the required methods. In your Entity, define the assignment
property as OneToMany:

```php
/**
 * Category assignments
 *
 * @ORM\OneToMany(targetEntity="Zikula\PagesModule\Entity\CategoryAssignmentEntity",
 *                mappedBy="entity", cascade={"remove", "persist"},
 *                orphanRemoval=true, fetch="EAGER")
 */
private $categoryAssignments;
```

Getter/Setter may vary by implementation, but remember you are not getting/setting a *category* but rather a 
CategoryAssignment. Therefore your getter/setter must accommodate this based on the data they work with. See the 
[CategoriesType](CategoriesType.md) doc for an example.

## Implementation preconditions

You need a existing doctrine2 entity to which you would like add categories support to.
In this guide we will use a `UserEntity`.

```php
namespace Acme\YourModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

/**
 * @ORM\Entity
 * @ORM\Table(name="yourmodule_user")
 */
class UserEntity extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(length=30)
     */
    private $username;

    /**
     * @ORM\Column(length=30)
     */
    private $password;

    // getter and setter
}
```

## Entities

The categories module provides the following abstract class: `Zikula\CategoriesModule\Entity\AbstractCategoryAssignment`.
You need to create a subclass of that class specific to the entity you would like
to add categories support to. In this guide we create a `UserCategoryAssignmentsEntity` class.
**UserEntity** is the name of the entity and **Category** is our categories specific suffix:

```php
namespace Acme\YourModule\Entity;

use Acme\YourModule\Entity\UserEntity;
use Doctrine\ORM\Mapping as ORM;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;

/**
 * @ORM\Entity
 * @ORM\Table(name="yourmodule_user_category",
 *            uniqueConstraints={@ORM\UniqueConstraint(name="cat_unq",columns={"registryId", "categoryId", "entityId"})})
 */
class UserCategoryAssignmentsEntity extends AbstractCategoryAssignment
{
    /**
     * @ORM\ManyToOne(targetEntity="Acme\YourModule\Entity\UserEntity", inversedBy="categories")
     * @ORM\JoinColumn(name="entityId", referencedColumnName="id")
     * @var UserEntity
     */
    private $entity;

    public function getEntity()
    {
        return $this->entity;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
}
```

The abstract class forces you to implement the **getEntity** and **setEntity** methods.
These methods force you to create an new class attribute. 
This attribute becomes a ManyToOne association to the original `UserEntity`. 
The column name `entityId` in `@JoinColumn` and `@UniqueConstraint` must match.

We need to add a inverse side of the association to the original `UserEntity`

```php
use Doctrine\Common\Collections\ArrayCollection;

// ...

/**
 * @ORM\OneToMany(targetEntity="Acme\YourModule\Entity\UserCategoryAssignmentsEntity", 
 *                mappedBy="entity", cascade={"all"}, 
 *                orphanRemoval=true, indexBy="categoryRegistryId")
 */
private $categories;

public function __construct()
{
    $this->categories = new ArrayCollection();
}

// getter and setter for $categories
```

The `inversedBy` attribute of the `@ManyToOne` annotation must match with this new class attribute name.
The `mappedBy` attribute of the `@OneToMany` annotation must match with the the class attribute in 
the `AbstractCategoryAssignment` subclass.

## Install code

List your `AbstractCategoryAssignment` subclass in the `$this->schemaTool->create()` method call.

## Working with Categories

See the [ZikulaPagesModule](https://github.com/zikula-modules/pages) for examples on how to install categories (and category registries) as well as adding
deleting, and editing categories in the relationships with the entity.
