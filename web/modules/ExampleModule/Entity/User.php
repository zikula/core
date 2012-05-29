<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace ExampleModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo; // Add a behaviour
use DoctrineExtensions\StandardFields\Mapping\Annotation as ZK;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * User entity class.
 * We use annotations to define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="examplemodule_user")
 */
class User extends \Zikula\Core\Doctrine\EntityAccess
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
     * @ORM   \Column(length=30)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * Annotation for this field definition.
     *
     * @ORM   \Column(length=30)
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @Gedmo\Slug(fields={"username"})
     * @ORM  \Column(length=64, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="integer")
     * @ZK \StandardFields(type="userid", on="create")
     */
    private $createdUserId;

    /**
     * @ORM\Column(type="integer")
     * @ZK \StandardFields(type="userid", on="update")
     */
    private $updatedUserId;

    /**
     * @ORM           \OneToMany(targetEntity="ExampleModule\Entity\UserCategory",
     *                mappedBy="entity", cascade={"all"},
     *                orphanRemoval=true, indexBy="categoryRegistryId")
     */
    private $categories;

    /**
     * @ORM           \OneToMany(targetEntity="ExampleModule\Entity\UserAttribute",
     *                mappedBy="entity", cascade={"all"},
     *                orphanRemoval=true, indexBy="name")
     */
    private $attributes;

    /**
     * @ORM          \OneToOne(targetEntity="ExampleModule\Entity\UserMetadata",
     *               mappedBy="entity", cascade={"all"},
     *               orphanRemoval=true)
     * @var \ExampleModule\Entity\UserMetadata
     */
    private $metadata;

    public function __construct()
    {
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attributes = new \Doctrine\Common\Collections\ArrayCollection();
    }

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

    public function getCategories()
    {
        return $this->categories;
    }

    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttribute($name, $value)
    {
        if (isset($this->attributes[$name])) {
            if ($value == null) {
                $this->attributes->remove($name);
            } else {
                $this->attributes[$name]->setValue($value);
            }
        } else {
            $this->attributes[$name] = new UserAttribute($name, $value, $this);
        }
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function setMetadata(UserMetadata $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getCreatedUserId()
    {
        return $this->createdUserId;
    }

    public function setCreatedUserId($createdUserId)
    {
        $this->createdUserId = $createdUserId;
    }

    public function getUpdatedUserId()
    {
        return $this->updatedUserId;
    }

    public function setUpdatedUserId($updatedUserId)
    {
        $this->updatedUserId = $updatedUserId;
    }
}
