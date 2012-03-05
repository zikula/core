<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * UserBlock entity class.
 *
 * We use annotations to define the entity mappings to database (see http://www.doctrine-project.org/docs/orm/2.1/en/reference/basic-mapping.html).
 *
 * @ORM\Entity(repositoryClass="Blocks_Entity_Repository_UserBlock")
 * @ORM\Table(name="userblocks",indexes={@ORM\index(name="uid_bid_idx",columns={"uid","bid"})})
 */
class Blocks_Entity_UserBlock extends Zikula_EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $uid;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $bid;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $active;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $last_update;
    

    /* constructor */
    public function __construct()
    {
        $this->uid = 0;
        $this->bid = 0;
        $this->active = 1;
        $this->last_update = new \DateTime("now");
    }

    /* getters & setters */
    public function getUid()
    {
        return $this->uid;
    }

    public function setUid($uid)
    {
        $this->uid = $uid;
    }
    
    public function getBid()
    {
        return $this->bid;
    }

    public function setBid($bid)
    {
        $this->bid = $bid;
    }
    
    public function getActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;
    }
    
    public function getLast_Update()
    {
        return $this->last_update;
    }

    public function setLast_Update()
    {
        $this->last_update = new \DateTime("now");
    }
}
