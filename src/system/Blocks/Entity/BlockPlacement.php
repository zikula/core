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


    /**
     * constructor
     */
    public function __construct()
    {
        $this->pid = 0;
        $this->bid = 0;
        $this->sortorder = 0;
    }

    /**
     * get the id of the placement in the placement/block association
     *
     * @return integer the placement's id in the placement/block association
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * set the id for the placement in the placement/block association
     *
     * @param integer $pid the placement's id in the placement/block association
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * get the id of the block in the placement/block association
     *
     * @return integer the block's id in the placement/block association
     */
    public function getBid()
    {
        return $this->bid;
    }

    /**
     * set the id for the block in the placement/block association
     *
     * @param integer $bid the block's id in the placement/block association
     */
    public function setBid($bid)
    {
        $this->bid = $bid;
    }

    /**
     * get the sortorder of the placement/block association
     *
     * @return integer the placement/block association sortorder
     */
    public function getSortorder()
    {
        return $this->sortorder;
    }

    /**
     * set the sortorder for the placement/block association
     *
     * @param integer $sortorder the placement/block association sortorder
     */
    public function setSortorder($sortorder)
    {
        $this->sortorder = $sortorder;
    }
}
