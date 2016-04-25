<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ObjectdataAttributes
 *
 * @ORM\Table(name="objectdata_attributes", indexes={@ORM\Index(name="object_type", columns={"object_type"}), @ORM\Index(name="object_id", columns={"object_id"})})
 * @ORM\Entity
 */
class ObjectdataAttributes
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="attribute_name", type="string", length=80, nullable=false)
     */
    private $attributeName;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    private $objectId;

    /**
     * @var string
     *
     * @ORM\Column(name="object_type", type="string", length=80, nullable=false)
     */
    private $objectType;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="obj_status", type="string", length=1, nullable=false)
     */
    private $objStatus;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cr_date", type="datetime", nullable=false)
     */
    private $crDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="cr_uid", type="integer", nullable=false)
     */
    private $crUid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lu_date", type="datetime", nullable=false)
     */
    private $luDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="lu_uid", type="integer", nullable=false)
     */
    private $luUid;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set attributeName
     *
     * @param string $attributeName
     * @return ObjectdataAttributes
     */
    public function setAttributeName($attributeName)
    {
        $this->attributeName = $attributeName;

        return $this;
    }

    /**
     * Get attributeName
     *
     * @return string
     */
    public function getAttributeName()
    {
        return $this->attributeName;
    }

    /**
     * Set objectId
     *
     * @param integer $objectId
     * @return ObjectdataAttributes
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId
     *
     * @return integer
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set objectType
     *
     * @param string $objectType
     * @return ObjectdataAttributes
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;

        return $this;
    }

    /**
     * Get objectType
     *
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return ObjectdataAttributes
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set objStatus
     *
     * @param string $objStatus
     * @return ObjectdataAttributes
     */
    public function setObjStatus($objStatus)
    {
        $this->objStatus = $objStatus;

        return $this;
    }

    /**
     * Get objStatus
     *
     * @return string
     */
    public function getObjStatus()
    {
        return $this->objStatus;
    }

    /**
     * Set crDate
     *
     * @param \DateTime $crDate
     * @return ObjectdataAttributes
     */
    public function setCrDate($crDate)
    {
        $this->crDate = $crDate;

        return $this;
    }

    /**
     * Get crDate
     *
     * @return \DateTime
     */
    public function getCrDate()
    {
        return $this->crDate;
    }

    /**
     * Set crUid
     *
     * @param integer $crUid
     * @return ObjectdataAttributes
     */
    public function setCrUid($crUid)
    {
        $this->crUid = $crUid;

        return $this;
    }

    /**
     * Get crUid
     *
     * @return integer
     */
    public function getCrUid()
    {
        return $this->crUid;
    }

    /**
     * Set luDate
     *
     * @param \DateTime $luDate
     * @return ObjectdataAttributes
     */
    public function setLuDate($luDate)
    {
        $this->luDate = $luDate;

        return $this;
    }

    /**
     * Get luDate
     *
     * @return \DateTime
     */
    public function getLuDate()
    {
        return $this->luDate;
    }

    /**
     * Set luUid
     *
     * @param integer $luUid
     * @return ObjectdataAttributes
     */
    public function setLuUid($luUid)
    {
        $this->luUid = $luUid;

        return $this;
    }

    /**
     * Get luUid
     *
     * @return integer
     */
    public function getLuUid()
    {
        return $this->luUid;
    }
}
