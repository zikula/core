<?php

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
    

    /* constructor */
    public function __construct()
    {
        $this->mid = 0;
        $this->cid = 0;
        $this->sortorder = 0;
    }

    /* getters & setters */
    public function getAmid()
    {
        return $this->amid;
    }
    
    public function setAmid($amid)
    {
        $this->amid = $amid;
    }
    
    public function getMid()
    {
        return $this->mid;
    }
    
    public function setMid($mid)
    {
        $this->mid = $mid;
    }
    
    public function getCid()
    {
        return $this->cid;
    }
    
    public function setCid($cid)
    {
        $this->cid = $cid;
    }
    
    public function getSortorder()
    {
        return $this->sortorder;
    }
    
    public function setSortorder($sortorder)
    {
        $this->sortorder = $sortorder;
    }
}
