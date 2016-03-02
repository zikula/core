<?php

namespace Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HookArea
 *
 * @ORM\Table(name="hook_area", indexes={@ORM\Index(name="areaidx", columns={"areaname"})})
 * @ORM\Entity
 */
class HookAreaEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="owner", type="string", length=40, nullable=false)
     */
    private $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="subowner", type="string", length=40, nullable=true)
     */
    private $subowner;

    /**
     * @var string
     *
     * @ORM\Column(name="areatype", type="string", length=1, nullable=false)
     */
    private $areatype;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=20, nullable=false)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="areaname", type="string", length=100, nullable=false)
     */
    private $areaname;

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
     * Set owner
     *
     * @param string $owner
     * @return HookAreaEntity
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set subowner
     *
     * @param string $subowner
     * @return HookAreaEntity
     */
    public function setSubowner($subowner)
    {
        $this->subowner = $subowner;

        return $this;
    }

    /**
     * Get subowner
     *
     * @return string
     */
    public function getSubowner()
    {
        return $this->subowner;
    }

    /**
     * Set areatype
     *
     * @param string $areatype
     * @return HookAreaEntity
     */
    public function setAreatype($areatype)
    {
        $this->areatype = $areatype;

        return $this;
    }

    /**
     * Get areatype
     *
     * @return string
     */
    public function getAreatype()
    {
        return $this->areatype;
    }

    /**
     * Set category
     *
     * @param string $category
     * @return HookAreaEntity
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set areaname
     *
     * @param string $areaname
     * @return HookAreaEntity
     */
    public function setAreaname($areaname)
    {
        $this->areaname = $areaname;

        return $this;
    }

    /**
     * Get areaname
     *
     * @return string
     */
    public function getAreaname()
    {
        return $this->areaname;
    }
}
