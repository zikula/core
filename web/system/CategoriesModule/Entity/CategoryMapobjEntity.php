<?php

namespace CategoriesModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoriesMapobj
 *
 * @ORM\Table(name="categories_mapobj")
 * @ORM\Entity
 */
class CategoryMapobjEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $modname
     *
     * @ORM\Column(name="modname", type="string", length=60, nullable=false)
     */
    private $modname;

    /**
     * @var string $tablename
     *
     * @ORM\Column(name="tablename", type="string", length=60, nullable=false)
     */
    private $tablename;

    /**
     * @var integer $objId
     *
     * @ORM\Column(name="obj_id", type="integer", nullable=false)
     */
    private $objId;

    /**
     * @var string $objIdcolumn
     *
     * @ORM\Column(name="obj_idcolumn", type="string", length=60, nullable=false)
     */
    private $objIdcolumn;

    /**
     * @var integer $regId
     *
     * @ORM\Column(name="reg_id", type="integer", nullable=false)
     */
    private $regId;

    /**
     * @var string $regProperty
     *
     * @ORM\Column(name="reg_property", type="string", length=60, nullable=false)
     */
    private $regProperty;

    /**
     * @var integer $categoryId
     *
     * @ORM\Column(name="category_id", type="integer", nullable=false)
     */
    private $categoryId;

    /**
     * @var string $objStatus
     *
     * @ORM\Column(name="obj_status", type="string", length=1, nullable=false)
     */
    private $objStatus;

    /**
     * @var datetime $crDate
     *
     * @ORM\Column(name="cr_date", type="datetime", nullable=false)
     */
    private $crDate;

    /**
     * @var integer $crUid
     *
     * @ORM\Column(name="cr_uid", type="integer", nullable=false)
     */
    private $crUid;

    /**
     * @var datetime $luDate
     *
     * @ORM\Column(name="lu_date", type="datetime", nullable=false)
     */
    private $luDate;

    /**
     * @var integer $luUid
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
     * @param datetime $crDate
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
     * @return datetime 
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
     * @param datetime $luDate
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
     * @return datetime 
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