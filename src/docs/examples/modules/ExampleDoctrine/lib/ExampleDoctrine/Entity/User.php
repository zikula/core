<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package ZikulaExamples_ExampleDoctrine
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo; // Add a behavous
use DoctrineExtensions\StandardFields\Mapping\Annotation as ZK;


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
    
    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="create")
     */
    private $createdUserId;
    
    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="update")
     */
    private $updatedUserId;
    
    /**
     * @ORM\OneToMany(targetEntity="ExampleDoctrine_Entity_UserCategory", 
     *                mappedBy="entity", cascade={"all"}, 
     *                orphanRemoval=true, indexBy="categoryRegistryId")
     */
    private $categories;
    
    /**
     * @ORM\OneToMany(targetEntity="ExampleDoctrine_Entity_UserAttribute", 
     *                mappedBy="entity", cascade={"all"}, 
     *                orphanRemoval=true, indexBy="name")
     */
    private $attributes;
    
    /**
     * @ORM\OneToOne(targetEntity="ExampleDoctrine_Entity_UserMetadata", 
     *               mappedBy="entity", cascade={"all"},
     *               orphanRemoval=true)
     * @var ExampleDoctrine_Entity_UserMetadata
     */
    private $metadata;

    public function __construct()
    {
        $this->categories = new Doctrine\Common\Collections\ArrayCollection();
        $this->attributes = new Doctrine\Common\Collections\ArrayCollection();
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
        if(isset($this->attributes[$name])) {
            if($value == null) {
                $this->attributes->remove($name);
            } else {
                $this->attributes[$name]->setValue($value);
            }
        } else {
            $this->attributes[$name] = new ExampleDoctrine_Entity_UserAttribute($name, $value, $this);
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
    
    public function setMetadata(ExampleDoctrine_Entity_UserMetadata $metadata)
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
