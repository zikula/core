<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * UserAttribute entity class.
 *
 * @ORM\Entity
 * @ORM\Table(name="users_attributes")
 *
 * User attributes table.
 * Stores extra information about each user account.
 */
class UserAttributeEntity extends EntityAccess
{
    /**
     * user id to which the attribute belongs
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UserEntity", inversedBy="attributes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="uid")
     */
    private $user;

    /**
     * the name of the attribute
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=80)
     */
    private $name;

    /**
     * the value for the attribute
     *
     * @ORM\Column(type="text")
     */
    private $value;

    /**
     * constructor
     *
     * @param int    $user  user id
     * @param string $name  name of the attribute
     * @param string $value value of the attribute
     */
    public function __construct($user, $name, $value)
    {
        $this->setUser($user);
        $this->setAttribute($name, $value);
    }

    /**
     * get the user item
     *
     * @return User the user item
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * set the user item
     *
     * @param User $user the user item
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * get the name of the attribute
     *
     * @return string the attribute's name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * set the name for the attribute
     *
     * @param string $name the attribute's name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * get the value of the attribute
     *
     * @return string the attribute's value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * set the value for the attribute
     *
     * @param string $value the attribute's value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * set the attribute
     *
     * @param string $name the attribute's name
     * @param string $value the attribute's value
     */
    public function setAttribute($name, $value)
    {
        $this->setName($name);
        $this->setValue($value);
    }
}
