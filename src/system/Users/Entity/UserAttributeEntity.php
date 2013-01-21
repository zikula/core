<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Users\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserAttribute entity class.
 *
 * We use annotations to define the entity mappings to database (see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html).
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
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UserEntity", inversedBy="attributes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="uid")
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

    /**
     * constructor
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
