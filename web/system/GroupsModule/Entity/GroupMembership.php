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

namespace GroupsModule\Entity;
use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * GroupMembership entity class.
 *
 * We use annotations to define the entity mappings to database (see http://www.doctrine-project.org/docs/orm/2.1/en/reference/basic-mapping.html).
 *
 * @ORM\Entity
 * @ORM\Table(name="group_membership",indexes={@ORM\index(name="gid_uid",columns={"uid","gid"})})
 */
class GroupMembership extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $gid;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $uid;
    

    /**
     * constructor
     */
    public function __construct()
    {
        $this->gid = 0;
        $this->uid = 0;
    }

    /**
     * get the gid of the group membership
     *
     * @return integer the group membership's gid
     */
    public function getGid()
    {
        return $this->gid;
    }

    /**
     * set the gid for the group membership
     *
     * @param integer $gid the group membership's gid
     */
    public function setGid($gid)
    {
        $this->gid = $gid;
    }
    
    /**
     * get the uid of the group membership
     *
     * @return integer the group membership's uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * set the uid for the group membership
     *
     * @param integer $uid the group membership's uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }
}
