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
use Zikula\UsersModule\Entity\UserEntity;

/**
 * GroupApplication entity class.
 *
 * @ORM\Entity(repositoryClass="Zikula\GroupsModule\Entity\Repository\GroupApplicationRepository")
 * @ORM\Table(name="group_applications")
 */
class GroupApplicationEntity extends EntityAccess
{
    /**
     * id of the group application
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $app_id;

    /**
     * user id of the applicant
     * This is a unidirectional relationship with UserEntity
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
     */
    private $user;

    /**
     * group id for the application
     * This is a bidirectional relationship with GroupEntity
     * @ORM\ManyToOne(targetEntity="Zikula\GroupsModule\Entity\GroupEntity", inversedBy="applications")
     * @ORM\JoinColumn(name="gid", referencedColumnName="gid")
     */
    private $group;

    /**
     * Details of the application
     *
     * @ORM\Column(type="text")
     */
    private $application;

    /**
     * Status of the application
     *
     * @ORM\Column(type="smallint")
     */
    private $status;

    public function __construct()
    {
        $this->user = null;
        $this->group = null;
        $this->application = '';
        $this->status = 0;
    }

    public function getAppId()
    {
        return $this->app_id;
    }

    public function setAppId($app_id)
    {
        $this->app_id = $app_id;
    }

    /**
     * @return UserEntity
     */
    public function getUser()
    {
        return $this->user;
    }

    public function setUser(UserEntity $user)
    {
        $this->user = $user;
    }

    /**
     * @return GroupEntity
     */
    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup(GroupEntity $group)
    {
        $this->group = $group;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function setApplication($application)
    {
        $this->application = $application;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }
}
