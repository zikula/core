<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * Group entity class.
 *
 * @ORM\Entity(repositoryClass="Zikula\GroupsModule\Entity\Repository\GroupRepository")
 * @ORM\Table(name="`groups`")
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
     */
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

    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function setApplications(Collection $applications): void
    {
        $this->applications = $applications;
    }

    public function getGid(): ?int
    {
        return $this->gid;
    }

    public function setGid(int $gid): void
    {
        $this->gid = $gid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getGtype(): int
    {
        return $this->gtype;
    }

    public function setGtype(int $gtype): void
    {
        $this->gtype = $gtype;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function getNbumax(): int
    {
        return $this->nbumax;
    }

    public function setNbumax(int $nbumax): void
    {
        $this->nbumax = $nbumax;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(UserEntity $user): void
    {
        $this->users[] = $user;
    }

    public function removeUser(UserEntity $user): void
    {
        $this->users->removeElement($user);
    }

    public function removeAllUsers(): void
    {
        $this->users->clear();
    }
}
