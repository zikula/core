<?php

namespace CategoriesModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoriesRegistry
 *
 * @ORM\Table(name="categories_registry")
 * @ORM\Entity
 */
class CategoryRegistryEntity
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
     * @var string $property
     *
     * @ORM\Column(name="property", type="string", length=60, nullable=false)
     */
    private $property;

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
     * @return CategoriesRegistry
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
     * @return CategoriesRegistry
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
     * Set property
     *
     * @param string $property
     * @return CategoriesRegistry
     */
    public function setProperty($property)
    {
        $this->property = $property;
        return $this;
    }

    /**
     * Get property
     *
     * @return string 
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set categoryId
     *
     * @param integer $categoryId
     * @return CategoriesRegistry
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
     * @return CategoriesRegistry
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
     * @return CategoriesRegistry
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
     * @return CategoriesRegistry
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
     * @return CategoriesRegistry
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
     * @return CategoriesRegistry
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