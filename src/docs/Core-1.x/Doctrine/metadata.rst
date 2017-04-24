================================
 Metadata Doctrine extension
================================

IMPORTANT NOTE: THIS FEATURE IS DEPRECATED AND REMOVED IN FUTURE VERSIONS OF ZIKULA.

Getting started
===============

Preconditions
-------------

You need a existing doctrine2 entity to which you would like add metadata support to.
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
The metadata extension provides a new abstract class: *Zikula_Doctrine2_Entity_EntityMetadata*.
You need to create a subclass of that class specific to the entity you would like
to add metadata support to. In this guide we create a *UserMetadta* class.
**User** is the name of the entity and **Metadata** is our metadata specific suffix::

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     * @ORM\Table(name="yourmodule_user_metadata")
     */
    class YourModule_Entity_UserMetadata extends Zikula_Doctrine2_Entity_EntityMetadata
    {
        /**
         * @ORM\OneToOne(targetEntity="YourModule_Entity_User", inversedBy="metadata")
         * @ORM\JoinColumn(name="entityId", referencedColumnName="id", unique=true)
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
     * @ORM\OneToOne(targetEntity="YourModule_Entity_UserMetadata",
     *               mappedBy="entity", cascade={"all"},
     *               orphanRemoval=true)
     * @var YourModule_Entity_UserMetadata
     */
    private $metadata;

    // getter and setter

The inversedBy attribute of the @ManyToOne annotation must match with this new class attribute name.
The mappedBy attribute of the @OneToMany annotation must match with the the class attribute in
the *EntityAttribute* subclass.


Install code
------------
List you *EntityMetadata* subclass in the DoctrineHelper::createSchema() method call.


Working with the entities
-------------------------

Set/change metadata

    $user = // ...

    if($user->getMetadata() == null) {
        $user->setMetadata(new ExampleDoctrine_Entity_UserMetadata($user));
    }
    $user->getMetadata()->setKeywords('a,b,c');

    $entityManager->persist($user);


Access metadata

    $user = // ...
    $keywords = $user->getMetadata()->getKeywords();

Database Tables
===============

DBUtil based metadata uses a single table to store metadata of every row of every table.

In Doctrine2 based metadata every entity gets its own table.

Example
=======
The ExampleDoctrine module located in /src/docs/examples/modules/ExampleDoctrine/
uses this doctrine extension in one of his entities.
