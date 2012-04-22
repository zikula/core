<?php

namespace CategoriesModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoriesCategory
 *
 * @ORM\Table(name="categories_category")
 * @ORM\Entity
 */
class CategoryEntity
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
     * @var integer $parentId
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=false)
     */
    private $parentId;

    /**
     * @var boolean $isLocked
     *
     * @ORM\Column(name="is_locked", type="boolean", nullable=false)
     */
    private $isLocked;

    /**
     * @var boolean $isLeaf
     *
     * @ORM\Column(name="is_leaf", type="boolean", nullable=false)
     */
    private $isLeaf;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string $value
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=false)
     */
    private $value;

    /**
     * @var integer $sortValue
     *
     * @ORM\Column(name="sort_value", type="integer", nullable=false)
     */
    private $sortValue;

    /**
     * @var text $displayName
     *
     * @ORM\Column(name="display_name", type="text", nullable=false)
     */
    private $displayName;

    /**
     * @var text $displayDesc
     *
     * @ORM\Column(name="display_desc", type="text", nullable=false)
     */
    private $displayDesc;

    /**
     * @var text $path
     *
     * @ORM\Column(name="path", type="text", nullable=false)
     */
    private $path;

    /**
     * @var string $ipath
     *
     * @ORM\Column(name="ipath", type="string", length=255, nullable=false)
     */
    private $ipath;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=1, nullable=false)
     */
    private $status;

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
     * Set parentId
     *
     * @param integer $parentId
     * @return CategoriesCategory
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * Get parentId
     *
     * @return integer 
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set isLocked
     *
     * @param boolean $isLocked
     * @return CategoriesCategory
     */
    public function setIsLocked($isLocked)
    {
        $this->isLocked = $isLocked;
        return $this;
    }

    /**
     * Get isLocked
     *
     * @return boolean 
     */
    public function getIsLocked()
    {
        return $this->isLocked;
    }

    /**
     * Set isLeaf
     *
     * @param boolean $isLeaf
     * @return CategoriesCategory
     */
    public function setIsLeaf($isLeaf)
    {
        $this->isLeaf = $isLeaf;
        return $this;
    }

    /**
     * Get isLeaf
     *
     * @return boolean 
     */
    public function getIsLeaf()
    {
        return $this->isLeaf;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return CategoriesCategory
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return CategoriesCategory
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
     * Set sortValue
     *
     * @param integer $sortValue
     * @return CategoriesCategory
     */
    public function setSortValue($sortValue)
    {
        $this->sortValue = $sortValue;
        return $this;
    }

    /**
     * Get sortValue
     *
     * @return integer 
     */
    public function getSortValue()
    {
        return $this->sortValue;
    }

    /**
     * Set displayName
     *
     * @param text $displayName
     * @return CategoriesCategory
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * Get displayName
     *
     * @return text 
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set displayDesc
     *
     * @param text $displayDesc
     * @return CategoriesCategory
     */
    public function setDisplayDesc($displayDesc)
    {
        $this->displayDesc = $displayDesc;
        return $this;
    }

    /**
     * Get displayDesc
     *
     * @return text 
     */
    public function getDisplayDesc()
    {
        return $this->displayDesc;
    }

    /**
     * Set path
     *
     * @param text $path
     * @return CategoriesCategory
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get path
     *
     * @return text 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set ipath
     *
     * @param string $ipath
     * @return CategoriesCategory
     */
    public function setIpath($ipath)
    {
        $this->ipath = $ipath;
        return $this;
    }

    /**
     * Get ipath
     *
     * @return string 
     */
    public function getIpath()
    {
        return $this->ipath;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return CategoriesCategory
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set objStatus
     *
     * @param string $objStatus
     * @return CategoriesCategory
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
     * @return CategoriesCategory
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
     * @return CategoriesCategory
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
     * @return CategoriesCategory
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
     * @return CategoriesCategory
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