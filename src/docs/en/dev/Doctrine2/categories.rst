================================
 Categories Doctrine2 extension
================================

Getting started
===============

Preconditions
-------------

You need a existing doctrine2 entity to which you would like add categories support to.
In this guide we will use a *User* entity::

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     * @ORM\Table(name="yourmodule_user")
     */
    class YourModule_Entity_User extends Zikula_EntityAccess
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


Entities
--------
The categories extension provides a new abstract class: *Zikula_Doctrine2_Entity_EntityCategory*.
You need to create a subclass of that class specific to the entity you would like
to add categories support to. In this guide we create a *UserCategory* class.
**User** is the name of the entity and **Category** is our categories specific suffix::

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     * @ORM\Table(name="yourmodule_user_category",
     *            uniqueConstraints={@ORM\UniqueConstraint(name="cat_unq",columns={"registryId", "categoryId", "entityId"})})
     */
    class YourModule_Entity_UserCategory extends Zikula_Doctrine2_Entity_EntityCategory
    {
        /**
         * @ORM\ManyToOne(targetEntity="YourModule_Entity_User", inversedBy="categories")
         * @ORM\JoinColumn(name="entityId", referencedColumnName="id")
         * @var YourModule_Entity_User
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

The abstract class forces you to implement the **getEntity** & **setEntity** methods.
These methods forece you to create an new class attribute. 
This attribute becomes a ManyToOne assocation to the original (*User*) entity. 
The column name "entityId" in @JoinColumn and @UniqueConstraint must match.

We need to add a inverse side of the assocation to the original (*User*) entity::
  
    /**
     * @ORM\OneToMany(targetEntity="YourModule_Entity_UserCategory", 
     *                mappedBy="entity", cascade={"all"}, 
     *                orphanRemoval=true, indexBy="categoryRegistryId")
     */
    private $categories;

    public function __construct()
    {
        $this->categories = new Doctrine\Common\Collections\ArrayCollection();
    }

    // getter and setter for $categories

The inversedBy attribute of the @ManyToOne annotation must match with this new class attribute name.
The mappedBy attribute of the @OneToMany annotation must match with the the class attribute in 
the *EntityCategory* subclass.


Install code
------------
List you *EntityCategory* subclass in the DoctrineHelper::createSchema() method call.

Add this code to your install method to add an entry to the categories registry::
    
    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Global');
    CategoryRegistryUtil::insertEntry('YourModule', 'MyEntity', 'Main', $rootcat['id']);


Working with the entities
-------------------------

Assign an category to the **Main** property::

    $user = // ...
    $registry = CategoryRegistryUtil::getRegisteredModuleCategory('YourModule', 'MyEntity', 'Main');
    $category = $entityManager->find('Zikula_Doctrine2_Entity_Category', $categoryId);
    $user->getCategories()->set($this->registryId, new YourModule_Entity_UserCategory($registry['id'], $category, $user));

    $entityManager->persist($user);


Change category of the **Main** property::

    $user = // ...
    $registry = CategoryRegistryUtil::getRegisteredModuleCategory('YourModule', 'MyEntity', 'Main');
    $category = $entityManager->find('Zikula_Doctrine2_Entity_Category', $categoryId);
    $user->getCategories()->get($registry['id'])->setCategory($category);
    
    $entityManager->persist($user);

Unassign the category of the **Main** property::

    $user = // ...
    $user->getCategories()->remove($registry['id']);
    
    $entityManager->persist($user);
  
Access category data of the **Main** property::
    
    $user = // ...
    $registry = CategoryRegistryUtil::getRegisteredModuleCategory('YourModule', 'MyEntity', 'Main');
    $categoryName = $user->getCategories()->get($registry['id'])->getCategory()->getName();
    // see Zikula_Doctrine2_Entity_Category class 

Database Tables
===============

DBUtil based categories uses a single table to store every category of every row of every table.

In Doctrine2 based categories every entity gets its own table.


Form Framework integration
==========================

The 'formcategoryselector' form plugin supports doctrine2 based categories.

In your Handler's initialize method::

    // load and assign registred categories
    $categories  = CategoryRegistryUtil::getRegisteredModuleCategories('YourModule', 'MyEntity', 'id');
    $view->assign('registries', $categories);
    $view->assign('user', $user);

In your edit template::

    {foreach from=$registries item="registryCid" key="registryId"}
        <div class="z-formrow">
            {formlabel for="category_`$registryId`" __text="Category"}
            {formcategoryselector id="category_`$registryId`" category=$registryCid 
                                  dataField="categories" group="user" registryId=$registryId doctrine2=true}
        </div>
    {/foreach}

**user** in the group attribute is the **user** of the $view->assign method call.
**categories** in the dataField attribute is the categories specific class attribute 
in your entity.


Upgrade of old DBUtil based categories
======================================
Use an SQL like this to move the data to the new table::

    INSERT INTO yourmodule_user_category (entityId, registryId, categoryId) SELECT o.obj_id, o.reg_id, o.category_id FROM categories_mapobj o WHERE o.modname = 'YourModule' o.tablename = 'yourmodule_oldtable' 

Do not forgot to delete old data in the categories_mapobj table!

Example
=======
The ExampleDoctrine module located in /src/docs/examples/modules/ExampleDoctrine/ 
uses this doctrine extension in one of his entities.