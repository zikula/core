<?php

namespace Acme\AddressBookModule\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A personal contact.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Contact
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
     * @ORM\Column(name="firstName", type="string", length=255)
     *
     * @Assert\NotBlank
     * @Assert\Length(min=2, minMessage="Please enter at least two characters.")
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255)
     *
     * @Assert\NotBlank
     * @Assert\Length(min=2, minMessage="Please enter at least two characters.")
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text")
     */
    private $address;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Email", mappedBy="contact", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @Assert\NotNull
     * @Assert\Valid(traverse=true)
     */
    private $emails;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="PhoneNumber", mappedBy="contact", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @Assert\NotNull
     * @Assert\Valid
     */
    private $phoneNumbers;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ContactGroup", mappedBy="members")
     */
    private $groups;

    /**
     * Constructs a new contact.
     */
    public function __construct()
    {
        $this->emails = new ArrayCollection();
        $this->phoneNumbers = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

    /**
     * Returns the ID.
     *
     * @return integer The ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the first name.
     *
     * @param string $firstName The contact's first name
     *
     * @return Contact The contact
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Returns the first name.
     *
     * @return string The contact's first name
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Sets the last name.
     *
     * @param string $lastName The contact's last name
     *
     * @return Contact The contact
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Returns the last name.
     *
     * @return string The contact's last name
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Returns the full name.
     *
     * The full name is a simple concatenation of the first name and the last
     * name, with a space in between, for example "John Doe".
     *
     * @return string The contact's full name
     */
    public function getFullName()
    {
        return $this->firstName.' '.$this->lastName;
    }

    /**
     * Sets the postal address.
     *
     * @param string $address The contact's postal address
     *
     * @return Contact The contact
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Returns the postal address.
     *
     * @return string The contact's postal address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Adds an email address.
     *
     * @param Email $email The email address to add
     *
     * @return $this This object
     */
    public function addEmail(Email $email)
    {
        if (!$this->emails->contains($email)) {
            $this->emails->add($email);

            $email->setContact($this);
        }

        return $this;
    }

    /**
     * Removes an email address.
     *
     * @param Email $email The email address to remove
     *
     * @return $this This object
     */
    public function removeEmail(Email $email)
    {
        $this->emails->removeElement($email);

        return $this;
    }

    /**
     * Returns all email addresses.
     *
     * @return Email[] The contact's email addresses
     */
    public function getEmails()
    {
        return $this->emails->toArray();
    }

    /**
     * Adds a phone number.
     *
     * @param PhoneNumber $phoneNumber The phone number to add
     *
     * @return $this This object
     */
    public function addPhoneNumber(PhoneNumber $phoneNumber)
    {
        if (!$this->phoneNumbers->contains($phoneNumber)) {
            $this->phoneNumbers->add($phoneNumber);

            $phoneNumber->setContact($this);
        }

        return $this;
    }

    /**
     * Removes a phone number.
     *
     * @param PhoneNumber $phoneNumber The phone number to remove
     *
     * @return $this This object
     */
    public function removePhoneNumber(PhoneNumber $phoneNumber)
    {
        $this->phoneNumbers->removeElement($phoneNumber);

        return $this;
    }

    /**
     * Returns all phone numbers.
     *
     * @return PhoneNumber[] The contact's phone numbers
     */
    public function getPhoneNumbers()
    {
        return $this->phoneNumbers->toArray();
    }

    /**
     * Adds the contact to a group.
     *
     * @param ContactGroup $group The group to add the contact to
     *
     * @return $this This object
     */
    public function addGroup(ContactGroup $group)
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);

            $group->addMember($this);
        }

        return $this;
    }

    /**
     * Removes the contact from a group.
     *
     * @param ContactGroup $group The group to remove the contact from
     *
     * @return $this This object
     */
    public function removeGroup(ContactGroup $group)
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);

            $group->removeMember($this);
        }

        return $this;
    }

    /**
     * Removes all groups that the contact belongs to.
     *
     * @return ContactGroup[] The contact's groups
     */
    public function getGroups()
    {
        return $this->groups->toArray();
    }
}
