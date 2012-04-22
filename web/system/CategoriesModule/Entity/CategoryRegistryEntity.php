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
     * @return CategoryRegistryEntity
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
     * @return CategoryRegistryEntity
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
     * @return CategoryRegistryEntity
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
     * @return CategoryRegistryEntity
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
     * @return CategoryRegistryEntity
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

}