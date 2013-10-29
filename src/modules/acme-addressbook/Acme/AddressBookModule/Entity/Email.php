<?php

namespace Acme\AddressBookModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An email address.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Email
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
     * @ORM\Column(name="value", type="string", length=255)
     *
     * @Assert\NotBlank
     * @Assert\Email(message="Please enter a valid email address.")
     */
    private $value;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="Contact", inversedBy="emails")
     *
     * @Assert\NotNull
     */
    private $contact;

    /**
     * Returns the email address.
     *
     * @return string The email address
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * Returns the ID.
     *
     * @return integer The email's ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the value.
     *
     * The value is the string representation of the email address, for example
     * "john@doe.net".
     *
     * @return string The value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value.
     *
     * The value is the string representation of the email address, for example
     * "john@doe.net".
     *
     * @param string $value The value
     *
     * @return $this This object
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Sets the contact this email address belongs to.
     *
     * A contact can only be set once and not be changed after that. If you try
     * to change it, an exception is thrown.
     *
     * @param Contact $contact The contact
     *
     * @return $this This object
     *
     * @throws \RuntimeException If you try to change the contact of an email
     *                           address that already belongs to a contact
     */
    public function setContact(Contact $contact)
    {
        if ($contact !== $this->contact) {
            if (null !== $this->contact) {
                throw new \RuntimeException('Emails must not be moved to different contacts.');
            }

            $this->contact = $contact;

            $contact->addEmail($this);
        }

        return $this;
    }
}
