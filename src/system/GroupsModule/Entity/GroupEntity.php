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
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
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
     * @var int
     */
    private $gid;

    /**
     * group name
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Length(min="0", max="255", allowEmptyString="false")
     * @var string
     */
    private $name;

    /**
     * group type
     *
     * @ORM\Column(type="smallint")
     * @var int
     */
    private $gtype;

    /**
     * group description
     *
     * @ORM\Column(type="string", length=200)
     * @Assert\Length(min="0", max="200", allowEmptyString="false")
     * @var string
     */
    private $description;

    /**
     * state of the group
     *
     * @ORM\Column(type="smallint")
     * @var int
     */
    private $state;

    /**
     * maximum membership count
     *
     * @ORM\Column(type="integer")
     * @var int
     */
    private $nbumax;

    /**
     * @ORM\ManyToMany(targetEntity="Zikula\UsersModule\Entity\UserEntity", mappedBy="groups", indexBy="uid")
     * @ORM\JoinTable(name="group_membership",
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
