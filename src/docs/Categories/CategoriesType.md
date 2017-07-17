CategoriesType
==============

classname: \Zikula\CategoriesModule\Form\Type\CategoriesType

The CategoriesModule provides a CategoriesType form type for ease of use with Symfony Forms.

###Class Naming for CategoryAssignmentEntity

The "categories" term is often interchanged with the Entity that defines the assignment of the category (which is not 
the same thing, but a common misunderstanding). To help clarify this, a new naming scheme has been used here:
`categoryAssignments` instead of simply `categories`. See the AbstractCategoryAssignment.md doc for more information.

Assuming you have registered your entity to utilize at least one branch of categories in the Categories module.

Assuming your Entities are set up like this:

PageEntity
----------

    class PageEntity extends \Zikula\Core\Doctrine\EntityAccess
    {
        /**
         * id
         *
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        private $id;

        /**
         * category assignments
         *
         * @ORM\OneToMany(targetEntity="Zikula\PagesModule\Entity\CategoryAssignmentEntity",
         *                mappedBy="entity", cascade={"remove", "persist"},
         *                orphanRemoval=true, fetch="EAGER")
         */
        private $categoryAssignments;
    
        /**
         * Get page category assignments
         *
         * @return \Doctrine\Common\Collections\ArrayCollection
         */
        public function getCategoryAssignments()
        {
            return $this->categoryAssignments;
        }
    
        /**
         * Set page category assignments
         *
         * @param ArrayCollection $assignments
         */
        public function setCategoryAssignments(ArrayCollection $assignments)
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
        }

    /**
     * Check if a collection contains an element based only on two criteria (categoryRegistryId, category).
     * @param ArrayCollection $collection
     * @param CategoryAssignmentEntity $element
     * @return bool|int
     */
    private function collectionContains(ArrayCollection $collection, CategoryAssignmentEntity $element)
    {
        foreach ($collection as $key => $collectionAssignment) {
            /** @var \Zikula\PagesModule\Entity\CategoryAssignmentEntity $collectionAssignment */
            if ($collectionAssignment->getCategoryRegistryId() == $element->getCategoryRegistryId()
                && $collectionAssignment->getCategory() == $element->getCategory()
            ) {

                return $key;
            }
        }

        return false;
    }


CategoryAssignmentEntity
------------------------

    use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;

    class CategoryAssignmentEntity extends AbstractCategoryAssignment
    {
        /**
         * @ORM\ManyToOne(targetEntity="Zikula\PagesModule\Entity\PageEntity", inversedBy="assignments")
         * @ORM\JoinColumn(name="entityId", referencedColumnName="pageid")
         * @var \Zikula\PagesModule\Entity\PageEntity
         */
        private $entity;
    
        /**
         * Set entity
         *
         * @return \Zikula\PagesModule\Entity\PageEntity
         */
        public function getEntity()
        {
            return $this->entity;
        }
    
        /**
         * Set entity
         *
         * @param \Zikula\PagesModule\Entity\PageEntity $entity
         */
        public function setEntity($entity)
        {
            $this->entity = $entity;
        }
    
    }

PageType
--------

    class PageType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('categoryAssignments', 'Zikula\CategoriesModule\Form\Type\CategoriesType', [
                'required' => false,
                'multiple' => true,
                'module' => 'ZikulaPagesModule',
                'entity' => 'PageEntity',
                'entityCategoryClass' => 'Zikula\PagesModule\Entity\CategoryAssignmentEntity',
            ]);
        }
    }

### Required Options

 - `module` - The Common (short) name for the providing module.
 - `entity` - The Common (short) name of the entity used in the Category Registry.
 - `entityCategoryClass` - The FqCN of the assignment entity for the category relation.
 - `em` - An instance of a Doctrine Object Manager.

### Optional Options

 - `required` - (boolean) is the field required (default `true`).
 - `multiple` - (boolean) allow multiple selections (default `false`).
 - `includeGrandChildren` - @deprecated - use 'direct'.
 - `direct` - (boolean) use only direct children or include all descendant generations (default `true`).
 - `attr` - (array) attributes array for each select box (default `[]`).
