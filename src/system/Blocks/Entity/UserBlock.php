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


    /**
     * constructor
     */
    public function __construct()
    {
        $this->uid = 0;
        $this->bid = 0;
        $this->active = 1;
        $this->last_update = new \DateTime("now");
    }

    /**
     * get the id of the user that the block belongs to
     *
     * @return integer the block's uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * set the user's id for the block
     *
     * @param integer $uid the block's uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * get the id of the block
     *
     * @return integer the block's bid
     */
    public function getBid()
    {
        return $this->bid;
    }

    /**
     * set the id for the block
     *
     * @param integer $bid the block's bid
     */
    public function setBid($bid)
    {
        $this->bid = $bid;
    }

    /**
     * get the status of the block
     *
     * @return integer the status number (0=inactive, 1=active)
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * set the status of the block
     *
     * @param integer $active the status number (0=inactive, 1=active)
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * get last update time of the block
     *
     * @return datetime the block's last updated time
     */
    public function getLast_Update()
    {
        return $this->last_update;
    }

    /**
     * set the last updated time of the block
     *
     * @param none
     */
    public function setLast_Update()
    {
        $this->last_update = new \DateTime("now");
    }
}
