<?php

/*
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
 * ObjectdataLog
 *
 * @ORM\Table(name="objectdata_log")
 * @ORM\Entity
 */
class ObjectdataLog
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
     * @ORM\Column(name="object_type", type="string", length=80, nullable=false)
     */
    private $objectType;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    private $objectId;

    /**
     * @var string
     *
     * @ORM\Column(name="op", type="string", length=16, nullable=false)
     */
    private $op;

    /**
     * @var string
     *
     * @ORM\Column(name="diff", type="text", nullable=true)
     */
    private $diff;

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
     * Set objectType
     *
     * @param string $objectType
     * @return ObjectdataLog
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
     * Set objectId
     *
     * @param integer $objectId
     * @return ObjectdataLog
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
     * Set op
     *
     * @param string $op
     * @return ObjectdataLog
     */
    public function setOp($op)
    {
        $this->op = $op;

        return $this;
    }

    /**
     * Get op
     *
     * @return string
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * Set diff
     *
     * @param string $diff
     * @return ObjectdataLog
     */
    public function setDiff($diff)
    {
        $this->diff = $diff;

        return $this;
    }

    /**
     * Get diff
     *
     * @return string
     */
    public function getDiff()
    {
        return $this->diff;
    }

    /**
     * Set objStatus
     *
     * @param string $objStatus
     * @return ObjectdataLog
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
     * @return ObjectdataLog
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
     * @return ObjectdataLog
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
     * @return ObjectdataLog
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
     * @return ObjectdataLog
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
