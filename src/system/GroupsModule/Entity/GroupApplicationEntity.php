<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
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
     * @var int
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
     * @var string
     */
    private $application;

    /**
     * Status of the application
     *
     * @ORM\Column(type="smallint")
     * @var int
     */
    private $status;

    public function __construct()
    {
        $this->application = '';
        $this->status = 0;
    }

    public function getAppId(): ?int
    {
        return $this->app_id;
    }

    public function setAppId(int $app_id): void
    {
        $this->app_id = $app_id;
    }

    public function getUser(): UserEntity
    {
        return $this->user;
    }

    public function setUser(UserEntity $user): void
    {
        $this->user = $user;
    }

    public function getGroup(): GroupEntity
    {
        return $this->group;
    }

    public function setGroup(GroupEntity $group): void
    {
        $this->group = $group;
    }

    public function getApplication(): string
    {
        return $this->application;
    }

    public function setApplication(string $application): void
    {
        $this->application = $application;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }
}
