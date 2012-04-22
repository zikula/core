<?php

namespace CategoriesModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoriesMapmeta
 *
 * @ORM\Table(name="categories_mapmeta")
 * @ORM\Entity
 */
class CategoryMapmetaEntity
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
     * @var integer $metaId
     *
     * @ORM\Column(name="meta_id", type="integer", nullable=false)
     */
    private $metaId;

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
     * Set metaId
     *
     * @param integer $metaId
     * @return CategoriesMapmeta
     */
    public function setMetaId($metaId)
    {
        $this->metaId = $metaId;
        return $this;
    }

    /**
     * Get metaId
     *
     * @return integer 
     */
    public function getMetaId()
    {
        return $this->metaId;
    }

    /**
     * Set categoryId
     *
     * @param integer $categoryId
     * @return CategoriesMapmeta
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
     * @return CategoriesMapmeta
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
     * @return CategoriesMapmeta
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
     * @return CategoriesMapmeta
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
     * @return CategoriesMapmeta
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
     * @return CategoriesMapmeta
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