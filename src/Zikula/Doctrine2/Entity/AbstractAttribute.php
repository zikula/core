<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute doctrine2 entity.
 * 
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="object_type", type="string")
 * @ORM\DiscriminatorMap({"categories_category" = "Zikula_Doctrine2_Entity_CategoryAttribute"})
 * @ORM\Table(name="objectdata_attributes")
 */
abstract class Zikula_Doctrine2_Entity_AbstractAttribute extends Zikula_EntityAccess {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=80, name="attribute_name")
     * @var string 
     */
    private $name;
    /**
     * @ORM\Column(type="text")
     * @var string 
     */
    private $value;
    /**
     * @ORM\Column(type="string", length=1, name="obj_status")
     * @var string
     */
    private $objectStatus;
    
    public function __construct($objectId, $objectType, $objectStatus, $name, $value)
    {
        $this->SetObjectID($objectId);
        $this->objectType = $objectType;
        $this->objectStatus = $objectStatus;
        $this->name = $name;
        $this->value = $value;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getObjectStatus()
    {
        return $this->objectStatus;
    }

    public function setObjectStatus($objectStatus)
    {
        $this->objectStatus = $objectStatus;
    }
    
    public function getObjectType()
    {
        return $this->objectType;
    }

    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }
    
    abstract public function getObjectId();

    abstract public function setObjectId($objectId);
}
