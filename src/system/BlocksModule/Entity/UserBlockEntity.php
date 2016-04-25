<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserBlock entity class.
 * @deprecated remove at Core-2.0 (unused)
 * @ORM\Entity
 * @ORM\Table(name="userblocks",indexes={@ORM\Index(name="uid_bid_idx",columns={"uid","bid"})})
 */
class UserBlockEntity extends EntityAccess
{
    /**
     * The id of the user
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $uid;

    /**
     * The id of the block
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $bid;

    /**
     * The active flag for the user block
     *
     * @ORM\Column(type="integer")
     */
    private $active;

    /**
     * The timestamp of the last update
     *
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
     * @return \Datetime the block's last updated time
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
