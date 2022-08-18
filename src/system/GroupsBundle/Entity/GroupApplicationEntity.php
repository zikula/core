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

namespace Zikula\GroupsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Zikula\GroupsBundle\Repository\GroupApplicationRepository;
use Zikula\UsersBundle\Entity\UserEntity;

#[ORM\Entity(repositoryClass: GroupApplicationRepository::class)]
#[ORM\Table(name: 'groups_application')]
class GroupApplicationEntity extends EntityAccess
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $app_id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
    private UserEntity $user;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(name: 'gid', referencedColumnName: 'gid')]
    private GroupEntity $group;

    /**
     * Details of the application
     */
    #[ORM\Column(type: Types::TEXT)]
    private string $application;

    /**
     * Status of the application
     */
    #[ORM\Column(type: TYPES::SMALLINT)]
    private int $status;

    public function __construct()
    {
        $this->application = '';
        $this->status = 0;
    }

    public function getAppId(): ?int
    {
        return $this->app_id;
    }

    public function setAppId(int $app_id): self
    {
        $this->app_id = $app_id;

        return $this;
    }

    public function getUser(): UserEntity
    {
        return $this->user;
    }

    public function setUser(UserEntity $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getGroup(): GroupEntity
    {
        return $this->group;
    }

    public function setGroup(GroupEntity $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getApplication(): string
    {
        return $this->application;
    }

    public function setApplication(string $application): self
    {
        $this->application = $application;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
