<?php

namespace Acme\AddressBookModule\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A named group of contacts.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ContactGroup
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Contact", inversedBy="groups")
     */
    private $members;

    /**
     * Creates a new group.
     */
    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    /**
     * Returns the ID.
     *
     * @return integer The group's ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the name.
     *
     * @param string $name The group's name
     *
     * @return ContactGroup The group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the name.
     *
     * @return string The group's name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Adds a member.
     *
     * @param Contact $member The new member
     *
     * @return $this This object
     */
    public function addMember(Contact $member)
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);

            $member->addGroup($this);
        }

        return $this;
    }

    /**
     * Removes a member.
     *
     * @param Contact $member The removed member
     *
     * @return $this This object
     */
    public function removeMember(Contact $member)
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);

            $member->removeGroup($this);
        }

        return $this;
    }

    /**
     * Returns all members.
     *
     * @return Contact[] The group's members
     */
    public function getMembers()
    {
        return $this->members->toArray();
    }
}
