---
currentMenu: categories
---
# CategoriesType

The CategoriesBundle provides a CategoriesType form type for ease of use with Symfony Forms.
It is implemented by the `\Zikula\CategoriesBundle\Form\Type\CategoriesType` class.

## Class naming for CategoryAssignmentEntity

The "categories" term is often interchanged with the Entity that defines the assignment of the category (which is not 
the same thing, but a common misunderstanding). To help clarify this, a new naming scheme has been used here:
`categoryAssignments` instead of simply `categories`. See the [AbstractCategoryAssignment](AbstractCategoryAssignment.md) doc for more information.

Assuming you have registered your entity to utilize at least one branch of categories in the Categories bundle.

## Example

Assuming your Entities are set up like this:

### PageEntity

```php
use Zikula\PagesBundle\Entity\CategoryAssignmentEntity;

class PageEntity
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\OneToMany(targetEntity: CategoryAssignmentEntity::class, mappedBy: 'entity', cascade: ['persist', 'remove'], orphanRemoval: true, fetch: 'EAGER')]
    private Collection $categoryAssignments;

    public function getCategoryAssignments(): Collection
    {
        return $this->categoryAssignments;
    }

    public function setCategoryAssignments(Collection $assignments): self
    {
        foreach ($this->categoryAssignments as $categoryAssignment) {
            if (false === $key = $this->collectionContains($assignments, $categoryAssignment)) {
                $this->categoryAssignments->removeElement($categoryAssignment);
            } else {
                $assignments->remove($key);
            }
        }
        foreach ($assignments as $assignment) {
            $this->categoryAssignments->add($assignment);
        }

        return $this;
    }

    /**
     * Check if a collection contains an element based only on two criteria (categoryRegistryId, category).
     */
    private function collectionContains(Collection $collection, CategoryAssignmentEntity $element): bool|int
    {
        foreach ($collection as $key => $collectionAssignment) {
            /** @var CategoryAssignmentEntity $collectionAssignment */
            if ($collectionAssignment->getCategoryRegistryId() == $element->getCategoryRegistryId()
                && $collectionAssignment->getCategory() == $element->getCategory()
            ) {
                return $key;
            }
        }

        return false;
    }
```

### CategoryAssignmentEntity

```php
use Zikula\CategoriesBundle\Entity\AbstractCategoryAssignment;
use Zikula\PagesBundle\Entity\PageEntity;

class CategoryAssignmentEntity extends AbstractCategoryAssignment
{
    #[ORM\ManyToOne(inversedBy: 'assignments')]
    #[ORM\JoinColumn(name: 'entityId', referencedColumnName: 'pageid')]
    private PageEntity $entity;

    public function getEntity(): PageEntity
    {
        return $this->entity;
    }

    public function setEntity(PageEntity $entity): self
    {
        $this->entity = $entity;

        return $this;
    }
}
```

### PageType

```php
class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('categoryAssignments', CategoriesType::class, [
            'required' => false,
            'multiple' => true,
            'bundle' => 'ZikulaPagesBundle',
            'entity' => 'PageEntity',
            'entityCategoryClass' => CategoryAssignmentEntity::class,
        ]);
    }
}
```

## Required options

- `bundle` - The Common (short) name for the providing bundle.
- `entity` - The Common (short) name of the entity used in the Category Registry.
- `entityCategoryClass` - The FqCN of the assignment entity for the category relation.
- `em` - An instance of a Doctrine Object Manager.

## Optional options

- `required` - (boolean) is the field required (default `true`).
- `multiple` - (boolean) allow multiple selections (default `false`).
- `direct` - (boolean) use only direct children or include all descendant generations (default `true`).
- `attr` - (array) attributes array for each select box (default `[]`).
- `showRegistryLabels` - (boolean) set to `true` to show a label for each single selector based on the base category assigned in the corresponding registry (default `false`).
