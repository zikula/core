<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * Group entity class.
 *
 * @ORM\Entity(repositoryClass="Zikula\GroupsModule\Entity\Repository\GroupRepository")
 * @ORM\Table(name="groups")
 */
class GroupEntity extends EntityAccess
{
    /**
     * group id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $gid;

    /**
     * group name
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;

    /**
     * group type
     *
     * @ORM\Column(type="smallint")
     */
    private $gtype;

    /**
     * group description
     *
     * @ORM\Column(type="string", length=200)
     */
    private $description;

    /**
     * state of the group
     *
     * @ORM\Column(type="smallint")
     */
    private $state;

    /**
     * maximum membership count
     *
     * @ORM\Column(type="integer")
     */
    private $nbumax;

    /**
     * @ORM\ManyToMany(targetEntity="Zikula\UsersModule\Entity\UserEntity", mappedBy="groups", indexBy="uid")
     * @ORM\JoinTable(
     *      joinColumns={@ORM\JoinColumn(name="gid", referencedColumnName="gid", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="uid", referencedColumnName="uid")}
     *      )
     * @ORM\OrderBy({"uname" = "ASC"})
     **/
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="Zikula\GroupsModule\Entity\GroupApplicationEntity", mappedBy="group")
     */
    private $applications;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->name = '';
        $this->gtype = 0;
        $this->description = '';
        $this->state = 0;
        $this->nbumax = 0;
        $this->users = new ArrayCollection();
        $this->applications = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * @param mixed $applications
     */
    public function setApplications($applications)
    {
        $this->applications = $applications;
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

    /**
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * GroupEntity is the 'Inverse side'
     * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html#owning-and-inverse-side-on-a-manytomany-association
     * @param UserEntity $user
     */
    public function addUser(UserEntity $user)
    {
        $this->users[] = $user;
    }

    public function removeUser(UserEntity $user)
    {
        $this->users->removeElement($user);
    }

    public function removeAllUsers()
    {
        $this->users->clear();
    }
}
