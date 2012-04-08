<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminModule entity class.
 *
 * We use annotations to define the entity mappings to database (see http://www.doctrine-project.org/docs/orm/2.1/en/reference/basic-mapping.html).
 *
 * @ORM\Entity(repositoryClass="Admin_Entity_Repository_AdminModule")
 * @ORM\Table(name="admin_module",indexes={@ORM\index(name="mid_cid",columns={"mid","cid"})})
 */
class Admin_Entity_AdminModule extends Zikula_EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $amid;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $mid;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $cid;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $sortorder;
    

    /**
     * constructor 
     */
    public function __construct()
    {
        $this->mid = 0;
        $this->cid = 0;
        $this->sortorder = 0;
    }

    /**
     * get the id of the module/category association
     * 
     * @return integer the module/category association id 
     */
    public function getAmid()
    {
        return $this->amid;
    }
    
    /**
     * set the id for the module/category association
     * 
     * @param integer $amid the module/category association id
     */
    public function setAmid($amid)
    {
        $this->amid = $amid;
    }
    
    /**
     * get the id of the module
     * 
     * @return integer the module id 
     */
    public function getMid()
    {
        return $this->mid;
    }
    
    /**
     * set the id for the module
     * 
     * @param integer $mid the module id
     */
    public function setMid($mid)
    {
        $this->mid = $mid;
    }
    
    /**
     * get the id of the category
     * 
     * @return integer the category id 
     */
    public function getCid()
    {
        return $this->cid;
    }
    
    /**
     * set the id for the category
     * 
     * @param integer $cid the category id
     */
    public function setCid($cid)
    {
        $this->cid = $cid;
    }
    
    /**
     * get the sortorder of the module/category association
     * 
     * @return integer the module/category association sortorder
     */
    public function getSortorder()
    {
        return $this->sortorder;
    }
    
    /**
     * set the sortorder for the module/category association
     * 
     * @param integer $sortorder the module/category association sortorder
     */
    public function setSortorder($sortorder)
    {
        $this->sortorder = $sortorder;
    }
}
