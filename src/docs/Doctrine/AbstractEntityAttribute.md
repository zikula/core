AbstractEntityAttribute
=======================

class: `Zikula\Core\Doctrine\Entity\AbstractEntityAttribute`

Getting started
===============

Preconditions
-------------

You need a existing doctrine2 entity to which you would like add attributes support to.
In this guide we will use a *User* entity::

    namespace Acme\YourModule\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Zikula\Core\Doctrine\EntityAccess;

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


Entities
--------
The attributes extension provides a new abstract class: `Zikula\Core\Doctrine\Entity\AbstractEntityAttribute`.
You need to create a subclass of that class specific to the entity you would like
to add attributes support to. In this guide we create a `UserAttributeEntity` class.
**UserEntity** is the name of the entity and **Attribute** is our attributes specific suffix::

    namespace Acme\YourModule\Entity;

    use Acme\YourModule\Entity\UserEntity;
    use Doctrine\ORM\Mapping as ORM;
    use Zikula\Core\Doctrine\EntityAccess;
    use Zikula\Core\Doctrine\Entity\AbstractEntityAttribute;

    /**
     * @ORM\Entity
     * @ORM\Table(name="yourmodule_user_attribute",
     *            uniqueConstraints={@ORM\UniqueConstraint(name="cat_unq",columns={"name", "entityId"})})
     */
    class UserAttributeEntity extends AbstractEntityAttribute
    {
        /**
         * @ORM\ManyToOne(targetEntity="Acme\YourModule\Entity\UserEntity", inversedBy="attributes")
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

The abstract class forces you to implement the **getEntity** & **setEntity** methods.
These methods force you to create an new class attribute. 
This attribute becomes a ManyToOne association to the original `UserEntity`. 
The column name "entityId" in `@JoinColumn` and `@UniqueConstraint` must match.

We need to add a inverse side of the association to the original `UserEntity`

    use Acme\YourModule\Entity\UserAttributeEntity;
    use Doctrine\Common\Collections\ArrayCollection;

    // ...

    /**
     * @ORM\OneToMany(targetEntity="Acme\YourModule\Entity\UserAttributeEntity", 
     *                mappedBy="entity", cascade={"all"}, 
     *                orphanRemoval=true, indexBy="name")
     */
    private $attributes;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
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
            $this->attributes[$name] = new UserAttributeEntity($name, $value, $this);
        }
    }

The `inversedBy` attribute of the `@ManyToOne` annotation must match with this new class attribute name.
The `mappedBy` attribute of the `@OneToMany` annotation must match with the class attribute in 
the `AbstractEntityAttribute` subclass.


Install code
------------
List your `AbstractEntityAttribute` subclass in the `$this->schemaTool->create()` method call.


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
