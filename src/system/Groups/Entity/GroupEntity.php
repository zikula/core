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

namespace Groups\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * Group entity class.
 *
 * We use annotations to define the entity mappings to database (see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html).
 *
 * @ORM\Entity
 * @ORM\Table(name="groups")
 */
class GroupEntity extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $gid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="smallint")
     */
    private $gtype;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $prefix;

    /**
     * @ORM\Column(type="smallint")
     */
    private $state;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbuser;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbumax;

    /**
     * @ORM\Column(type="integer")
     */
    private $link;

    /**
     * @ORM\Column(type="integer")
     */
    private $uidmaster;


    /**
     * constructor
     */
    public function __construct()
    {
        $this->name = '';
        $this->gtype = 0;
        $this->description = '';
        $this->prefix = '';
        $this->state = 0;
        $this->nbuser = 0;
        $this->nbumax = 0;
        $this->link = 0;
        $this->uidmaster = 0;
    }

    /**
     * get the gid of the group
     *
     * @return integer the group's gid
     */
    public function getGid()
    {
        return $this->gid;
    }

    /**
     * set the gid for the group
     *
     * @param integer $gid the group's gid
     */
    public function setGid($gid)
    {
        $this->gid = $gid;
    }

    /**
     * get the name of the group
     *
     * @return string the group's name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * set the name for the group
     *
     * @param string $name the group's name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * get the gtype of the group
     *
     * @return integer the group's gtype
     */
    public function getGtype()
    {
        return $this->gtype;
    }

    /**
     * set the gtype for the group
     *
     * @param integer $gtype the group's gtype
     */
    public function setGtype($gtype)
    {
        $this->gtype = $gtype;
    }

    /**
     * get the description of the group
     *
     * @return string the group's description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * set the description for the group
     *
     * @param string $description the group's description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * get the prefix of the group
     *
     * @return string the group's prefix
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * set the prefix for the group
     *
     * @param string $prefix the group's prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * get the state of the group
     *
     * @return integer the group's state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * set the state for the group
     *
     * @param integer $state the group's state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * get the nbuser of the group
     *
     * @return integer the group's nbuser
     */
    public function getNbuser()
    {
        return $this->nbuser;
    }

    /**
     * set the nbuser for the group
     *
     * @param integer $nbuser the group's nbuser
     */
    public function setNbuser($nbuser)
    {
        $this->nbuser = $nbuser;
    }

    /**
     * get the nbumax of the group
     *
     * @return integer the group's nbumax
     */
    public function getNbumax()
    {
        return $this->nbumax;
    }

    /**
     * set the nbumax for the group
     *
     * @param integer $nbumax the group's nbumax
     */
    public function setNbumax($nbumax)
    {
        $this->nbumax = $nbumax;
    }

    /**
     * get the link of the group
     *
     * @return integer the group's link
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * set the link for the group
     *
     * @param integer $link the group's link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * get the uidmaster of the group
     *
     * @return integer the group's uidmaster
     */
    public function getUidmaster()
    {
        return $this->uidmaster;
    }

    /**
     * set the uidmaster for the group
     *
     * @param integer $uidmaster the group's uidmaster
     */
    public function setUidmaster($uidmaster)
    {
        $this->uidmaster = $uidmaster;
    }
}
