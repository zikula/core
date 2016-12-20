<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
 *
 * @deprecated since 1.4.0
 */
abstract class Zikula_Doctrine2_Entity_AbstractAttribute extends Zikula_EntityAccess
{
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
        @trigger_error('AbstractAttribute is deprecated.', E_USER_DEPRECATED);

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
