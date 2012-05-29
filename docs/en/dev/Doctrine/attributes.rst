===========================================
 Adding attributes to your Doctrine2 module
===========================================

Getting started
===============

Preconditions
-------------

You need an existing Doctrine 2 entity to which you would like to add attributes support to.
In this guide we will use a *User* entity::

    namespace YourModule\Entity;
    
    use Zikula\Core\Doctrine\EntityAccess;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     * @ORM\Table(name="yourmodule_user")
     */
    class User extends EntityAccess
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
Apart from the Doctrine 2 entity of your module, you will need another entity that will hold the attributes.
In this guide we will create a *UserAttribute* class.
**User** is the name of the entity and **Attribute** is our attributes specific suffix::

    namespace YourModule\Entity;

    use Zikula\Core\Doctrine\EntityAccess;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     * @ORM\Table(name="yourmodule_user_attribute")
     */
    class UserAttribute extends EntityAccess
    {
        /**
        * @ORM\Id
        * @ORM\ManyToOne(targetEntity="User", inversedBy="attributes")
        * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
        */
        private $user;

        /**
        * @ORM\Id
        * @ORM\Column(type="string", length=80)
        */
        private $name;

        /**
        * @ORM\Column(type="text")
        */
        private $value;

        public function __construct($user, $name, $value)
        {
            $this->setUser($user);
            $this->setAttribute($name, $value);
        }

        public function getUser()
        {
            return $this->user;
        }

        public function setUser($user)
        {
            $this->user = $user;
        }

        public function getName()
        {
            return $this->name;
        }

        public function setName($name)
        {
            $this->name = $name;
        }

        public function getValue()
        {
            return $this->value;
        }

        public function setValue($value)
        {
            $this->value = $value;
        }

        public function setAttribute($name, $value)
        {
            $this->setName($name);
            $this->setValue($value);
        }
    }

In the above example, the attribute 'user' becomes a ManyToOne association to the original (*User*) entity. 
The referencedColumnName (in this case "id") must match the joined column name on the target Entity.

We also need to add an inverse side of the association to the original (*User*) entity::
  
    /**
     * @ORM\OneToMany(targetEntity="UserAttribute", 
     *                mappedBy="user", 
     *                cascade={"all"},
     *                orphanRemoval=true,
     *                indexBy="name")
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
    
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }
    
    public function setAttribute($name, $value)
    {
        if (isset($this->attributes[$name])) {
            $this->attributes[$name]->setValue($value);
        } else {
            $this->attributes[$name] = new UserAttribute($this, $name, $value);
        }
    }
    
    public function delAttribute($name)
    {
        if (isset($this->attributes[$name])) {
            $this->attributes->remove($name);
        }
    }

The inversedBy attribute of the @ManyToOne annotation (in this case "attributes") must match with this new class attribute name.
Also the mappedBy attribute of the @OneToMany annotation must match with the the class attribute in the *UserAttribute* subclass.


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
    $user->delAttribute('url');
    
    $entityManager->persist($user);
  
Access all attributes

    $user = // ...
    $urlValue = $user->getAttributes()->get('url)->getValue();

Database Tables
===============

In Doctrine2 based attributes every entity gets its own table.


Upgrade of old DBUtil based attributes
======================================
Use an SQL like this to move the data to the new table::

    INSERT INTO mymodule_user_yourmodule (entityId, name, value) SELECT o.object_id, o.attribute_name, o.value FROM objectdata_attributes o WHERE o.object_type = 'yourmodule_oldtable' 

Do not forgot to delete old data in the objectdata_attributes table!

Example
=======
The Users and Categories modules are good examples of the implementation of attributes.