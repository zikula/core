================================
 Attributes Doctrine2 extension
================================

Getting started
===============

Preconditions
-------------

You need a existing doctrine2 entity to which you would like add attributes support to.
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
The attributes extension provides a new abstract class: *Zikula_Doctrine2_Entity_EntityAttribute*.
You need to create a subclass of that class specific to the entity you would like
to add attributes support to. In this guide we create a *UserAttribute* class.
**User** is the name of the entity and **Attribute** is our attribues specific suffix::

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     * @ORM\Table(name="yourmodule_user_attribute",
     *            uniqueConstraints={@ORM\UniqueConstraint(name="cat_unq",columns={"name", "entityId"})})
     */
    class YourModule_Entity_UserAttribute extends Zikula_Doctrine2_Entity_EntityAttribute
    {
        /**
         * @ORM\ManyToOne(targetEntity="YourModule_Entity_User", inversedBy="attributes")
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
     * @ORM\OneToMany(targetEntity="YourModule_Entity_UserAttribute", 
     *                mappedBy="entity", cascade={"all"}, 
     *                orphanRemoval=true, indexBy="name")
     */
    private $attributes;

    public function __construct()
    {
        $this->attributes = new Doctrine\Common\Collections\ArrayCollection();
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
    
    public function setAttribute($name, $value)
    {
        if(isset($this->attributes[$name])) {
            if($value == null) {
                $this->attributes->remove($name);
            } else {
                $this->attributes[$name]->setValue($value);
            }
        } else {
            $this->attributes[$name] = new YourModule_Entity_UserAttribute($name, $value, $this);
        }
    }

The inversedBy attribute of the @ManyToOne annotation must match with this new class attribute name.
The mappedBy attribute of the @OneToMany annotation must match with the the class attribute in 
the *EntityAttribute* subclass.


Install code
------------
List you *EntityAttribute* subclass in the DoctrineHelper::createSchema() method call.


Working with the entities
-------------------------

Set/change an attribute

    $user = // ...
    $user->setAttribute('url', 'http://www.example.com');

    $entityManager->persist($user);


remove an attribute

    $user = // ...
    $user->setAttribute('url', null);
    
    $entityManager->persist($user);
  
Access all attributes

    $user = // ...
    $urlValue = $user->getAttributes()->get('url)->getValue();

Database Tables
===============

DBUtil based attributes uses a single table to store every attribute of every row of every table.

In Doctrine2 based attributes every entity gets its own table.


Upgrade of old DBUtil based attributes
======================================
Use an SQL like this to move the data to the new table::

    INSERT INTO mymodule_user_yourmodule (entityId, name, value) SELECT o.object_id, o.attribute_name, o.value FROM objectdata_attributes o WHERE o.object_type = 'yourmodule_oldtable' 

Do not forgot to delete old data in the objectdata_attributes table!

Example
=======
The ExampleDoctrine module located in /src/docs/examples/modules/ExampleDoctrine/ 
uses this doctrine extension in one of his entities.