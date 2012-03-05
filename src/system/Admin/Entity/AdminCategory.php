<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminCategory entity class.
 *
 * We use annotations to define the entity mappings to database (see http://www.doctrine-project.org/docs/orm/2.1/en/reference/basic-mapping.html).
 *
 * @ORM\Entity(repositoryClass="Admin_Entity_Repository_AdminCategory")
 * @ORM\Table(name="admin_category")
 */
class Admin_Entity_AdminCategory extends Zikula_EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $cid;
    
    /**
     * @ORM\Column(type="string", length=32)
     */
    private $name;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $sortorder;
    

    /* constructor */
    public function __construct()
    {
        $this->name = '';
        $this->description = '';
        $this->sortorder = 0;
    }

    /* getters & setters */
    public function getCid()
    {
        return $this->cid;
    }
    
    public function setCid($cid)
    {
        $this->cid = $cid;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
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
