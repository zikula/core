<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * Hook area doctrine2 entity.
 * 
 * @ORM\Entity
 * @ORM\Table(name="hook_area",indexes={@ORM\index(name="areaidx", columns={"areaname"})})
 */
class Zikula_Doctrine2_Entity_HookArea {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var integer 
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=40)
     * @var string 
     */
    private $owner;
    
    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     * @var integer 
     */
    private $subowner;
    
    /**
     * @ORM\Column(type="string", length=1)
     * @var string 
     */
    private $areatype;
    
    /**
     * @ORM\Column(type="string", length=20)
     * @var string 
     */
    private $category;
    
    /**
     * @ORM\Column(type="string", length=100)
     * @var string 
     */    
    private $areaname;
    
    /* getters and setters */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function getSubowner()
    {
        return $this->subowner;
    }

    public function setSubowner($subowner)
    {
        $this->subowner = $subowner;
    }

    public function getAreatype()
    {
        return $this->areatype;
    }

    public function setAreatype($areatype)
    {
        $this->areatype = $areatype;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function getAreaname()
    {
        return $this->areaname;
    }

    public function setAreaname($areaname)
    {
        $this->areaname = $areaname;
    }

}
