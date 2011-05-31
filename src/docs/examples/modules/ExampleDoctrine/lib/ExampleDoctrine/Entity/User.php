<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
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

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo; // Add a behavous


/**
 * User entity class.
 *
 * We use annotations to define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="exampledoctrine_user")
 */
class ExampleDoctrine_Entity_User extends Zikula_EntityAccess
{
    /**
     * The following are annotations which define the id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Annotation for this field definition.
     *
     * @ORM\Column(length=30)
     * @Gedmo\Sluggable
     */
    private $username;

    /**
     * Annotation for this field definition.
     *
     * @ORM\Column(length=30)
     */
    private $password;

    /**
     * @Gedmo\Slug
     * @ORM\Column(length=64, unique=true)
     */
    private $slug;

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setUser($username, $password)
    {
        $this->setUsername($username);
        $this->setPassword($password);
    }
}
