---
currentMenu: categories
---
# AbstractCategoryAssignment

The categories bundle provides an abstract base class in `\Zikula\CategoriesBundle\Entity\AbstractCategoryAssignment`. This
is not a category, but rather a category assignment. It is recommended to name your entity properties accordingly to reflect
the more accurate naming, e.g. `categoryAssignments` instead of `categories`.

## Purpose

The class exists to make connection to Core categories easier for third-party entities. Simply create a child class
Entity that extends `AbstractCategoryAssignment` and define the required methods. In your Entity, define the assignment
property as OneToMany:

```php
use Zikula\PagesBundle\Entity\CategoryAssignmentEntity;

// ...

#[ORM\OneToMany(targetEntity: CategoryAssignmentEntity::class, mappedBy: 'entity', cascade: ['persist', 'remove'], orphanRemoval: true, fetch: 'EAGER')]
private Collection $categoryAssignments;
```

Getter/Setter may vary by implementation, but remember you are not getting/setting a *category* but rather a 
CategoryAssignment. Therefore your getter/setter must accommodate this based on the data they work with. See the 
[CategoriesType](CategoriesType.md) doc for an example.

## Implementation preconditions

You need a existing doctrine2 entity to which you would like add categories support to.
In this guide we will use a `UserEntity`.

```php
namespace Acme\YourBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'yourbundle_user')]
class UserEntity
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(length: 30)]
    private string $username;

    #[ORM\Column(length: 30)]
    private string $password;

    // getter and setter
}
```

## Entities

The categories bundle provides the following abstract class: `Zikula\CategoriesBundle\Entity\AbstractCategoryAssignment`.
You need to create a subclass of that class specific to the entity you would like
to add categories support to. In this guide we create a `UserCategoryAssignmentsEntity` class.
**UserEntity** is the name of the entity and **Category** is our categories specific suffix:

```php
namespace Acme\YourBundle\Entity;

use Acme\YourBundle\Entity\UserEntity;
use Doctrine\ORM\Mapping as ORM;
use Zikula\CategoriesBundle\Entity\AbstractCategoryAssignment;

#[ORM\Entity]
#[ORM\Table(name: 'yourbundle_user_category')]
#[ORM\UniqueConstraint(columns: ['registryId', 'categoryId', 'entityId'], name: 'cat_unq')]
class UserCategoryAssignmentsEntity extends AbstractCategoryAssignment
{
    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn(name: 'entityId', referencedColumnName: 'id')]
    private UserEntity $entity;

    public function getEntity(): UserEntity
    {
        return $this->entity;
    }

    public function setEntity(UserEntity $entity): self
    {
        $this->entity = $entity;

        return $this;
    }
}
```

The abstract class forces you to implement the **getEntity** and **setEntity** methods.
These methods force you to create an new class attribute. 
This attribute becomes a ManyToOne association to the original `UserEntity`. 
The column name `entityId` in `@JoinColumn` and `@UniqueConstraint` must match.

We need to add a inverse side of the association to the original `UserEntity`

```php
use Acme\YourBundle\Entity\UserCategoryAssignmentsEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

// …

#[ORM\OneToMany(targetEntity: UserCategoryAssignmentsEntity::class, mappedBy: 'entity', cascade: ['all'],  orphanRemoval: true, indexBy: 'categoryRegistryId')]
/**
 * @var UserCategoryAssignmentsEntity[]
 */
private Collection $categories;

public function __construct()
{
    $this->categories = new ArrayCollection();
}

// getter and setter for $categories
```

The `inversedBy` attribute of the `@ManyToOne` annotation must match with this new class attribute name.
The `mappedBy` attribute of the `@OneToMany` annotation must match with the the class attribute in 
the `AbstractCategoryAssignment` subclass.

## Working with Categories

See the [ZikulaPagesBundle](https://github.com/zikula-modules/pages) for examples on how to install categories (and category registries) as well as adding
deleting, and editing categories in the relationships with the entity.
