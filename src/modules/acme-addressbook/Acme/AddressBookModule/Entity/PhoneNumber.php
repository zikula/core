<?php

namespace Acme\AddressBookModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A phone number.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PhoneNumber
{
    /**
     * A private phone number.
     */
    const TYPE_PRIVATE = 'private';

    /**
     * A work phone number.
     */
    const TYPE_WORK = 'work';

    /**
     * A non-specified phone number type.
     */
    const TYPE_OTHER = 'other';

    /**
     * The available phone number types.
     *
     * @var array
     */
    private static $types = array(
        self::TYPE_PRIVATE => 'Private',
        self::TYPE_WORK => 'Work',
        self::TYPE_OTHER => 'Other',
    );

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
     * @ORM\Column(name="type", type="string", length=255)
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback="getAvailableTypes")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255)
     *
     * @Assert\NotBlank
     * @Assert\Regex("/^[0-9\-\s]+$/", message="The phone number should only contain numbers (""0""-""9""), hyphens (""-"") and whitespace ("" "").")
     */
    private $value;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="Contact", inversedBy="phoneNumbers")
     *
     * @Assert\NotNull
     */
    private $contact;

    /**
     * Returns the available phone number types.
     *
     * Valid types are TYPE_PRIVATE, TYPE_WORK and TYPE_OTHER.
     *
     * @return string[] The types
     */
    public static function getAvailableTypes()
    {
        return array_keys(self::$types);
    }

    /**
     * Returns the names of the available phone number types.
     *
     * @return string[] The type names indexed by the types
     */
    public static function getAvailableTypeNames()
    {
        return self::$types;
    }

    /**
     * Returns the ID.
     *
     * @return integer The phone number's ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the type.
     *
     * @return string The phone number's type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param string $type The phone number's type
     *
     * @return $this This object
     *
     * @throws \InvalidArgumentException If the type is none of the constants
     *                                   TYPE_PRIVATE, TYPE_WORK and TYPE_OTHER
     */
    public function setType($type)
    {
        if (!isset(self::$types[$type])) {
            throw new \InvalidArgumentException(
                'The type should be one of TYPE_PRIVATE, TYPE_WORK and TYPE_OTHER.'
            );
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Returns the value.
     *
     * The value is the string representation of the phone number, for example
     * "+43-1234567".
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
     * The value is the string representation of the phone number, for example
     * "+43-1234567".
     *
     * @param $value The value
     *
     * @return $this This object
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Sets the contact this phone number address belongs to.
     *
     * A contact can only be set once and not be changed after that. If you try
     * to change it, an exception is thrown.
     *
     * @param Contact $contact The contact
     *
     * @return $this This object
     *
     * @throws \RuntimeException If you try to change the contact of a phone
     *                           number that already belongs to a contact
     */
    public function setContact(Contact $contact)
    {
        if ($contact !== $this->contact) {
            if (null !== $this->contact) {
                throw new \RuntimeException('Phone numbers must not be moved to different contacts.');
            }

            $this->contact = $contact;

            $contact->addPhoneNumber($this);
        }

        return $this;
    }
}
