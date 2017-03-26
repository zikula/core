<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @deprecated remove at Core-2.0
 * CategoriesMapobj
 *
 * @ORM\Table(name="categories_mapobj")
 * @ORM\Entity
 */
class CategoriesMapobj
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
     * @ORM\Column(name="modname", type="string", length=60, nullable=false)
     */
    private $modname;

    /**
     * @var string
     *
     * @ORM\Column(name="tablename", type="string", length=60, nullable=false)
     */
    private $tablename;

    /**
     * @var integer
     *
     * @ORM\Column(name="obj_id", type="integer", nullable=false)
     */
    private $objId;

    /**
     * @var string
     *
     * @ORM\Column(name="obj_idcolumn", type="string", length=60, nullable=false)
     */
    private $objIdcolumn;

    /**
     * @var integer
     *
     * @ORM\Column(name="reg_id", type="integer", nullable=false)
     */
    private $regId;

    /**
     * @var string
     *
     * @ORM\Column(name="reg_property", type="string", length=60, nullable=false)
     */
    private $regProperty;

    /**
     * @var integer
     *
     * @ORM\Column(name="category_id", type="integer", nullable=false)
     */
    private $categoryId;

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
     * Set modname
     *
     * @param string $modname
     * @return CategoriesMapobj
     */
    public function setModname($modname)
    {
        $this->modname = $modname;

        return $this;
    }

    /**
     * Get modname
     *
     * @return string
     */
    public function getModname()
    {
        return $this->modname;
    }

    /**
     * Set tablename
     *
     * @param string $tablename
     * @return CategoriesMapobj
     */
    public function setTablename($tablename)
    {
        $this->tablename = $tablename;

        return $this;
    }

    /**
     * Get tablename
     *
     * @return string
     */
    public function getTablename()
    {
        return $this->tablename;
    }

    /**
     * Set objId
     *
     * @param integer $objId
     * @return CategoriesMapobj
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId
     *
     * @return integer
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set objIdcolumn
     *
     * @param string $objIdcolumn
     * @return CategoriesMapobj
     */
    public function setObjIdcolumn($objIdcolumn)
    {
        $this->objIdcolumn = $objIdcolumn;

        return $this;
    }

    /**
     * Get objIdcolumn
     *
     * @return string
     */
    public function getObjIdcolumn()
    {
        return $this->objIdcolumn;
    }

    /**
     * Set regId
     *
     * @param integer $regId
     * @return CategoriesMapobj
     */
    public function setRegId($regId)
    {
        $this->regId = $regId;

        return $this;
    }

    /**
     * Get regId
     *
     * @return integer
     */
    public function getRegId()
    {
        return $this->regId;
    }

    /**
     * Set regProperty
     *
     * @param string $regProperty
     * @return CategoriesMapobj
     */
    public function setRegProperty($regProperty)
    {
        $this->regProperty = $regProperty;

        return $this;
    }

    /**
     * Get regProperty
     *
     * @return string
     */
    public function getRegProperty()
    {
        return $this->regProperty;
    }

    /**
     * Set categoryId
     *
     * @param integer $categoryId
     * @return CategoriesMapobj
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get categoryId
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set objStatus
     *
     * @param string $objStatus
     * @return CategoriesMapobj
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
     * @return CategoriesMapobj
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
     * @return CategoriesMapobj
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
     * @return CategoriesMapobj
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
     * @return CategoriesMapobj
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
