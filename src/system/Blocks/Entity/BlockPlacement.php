<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * BlockPlacement entity class.
 *
 * We use annotations to define the entity mappings to database (see http://www.doctrine-project.org/docs/orm/2.1/en/reference/basic-mapping.html).
 *
 * @ORM\Entity(repositoryClass="Blocks_Entity_Repository_BlockPlacement")
 * @ORM\Table(name="block_placements",indexes={@ORM\index(name="bid_pid_idx",columns={"bid","pid"})})
 */
class Blocks_Entity_BlockPlacement extends Zikula_EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $pid;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $bid;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $sortorder;
    

    /* constructor */
    public function __construct()
    {
        $this->pid = 0;
        $this->bid = 0;
        $this->sortorder = 0;
    }

    /* getters & setters */
    public function getPid()
    {
        return $this->pid;
    }

    public function setPid($pid)
    {
        $this->pid = $pid;
    }
    
    public function getBid()
    {
        return $this->bid;
    }

    public function setBid($bid)
    {
        $this->bid = $bid;
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
