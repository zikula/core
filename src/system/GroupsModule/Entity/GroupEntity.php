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
     * @ORM\Column(type="string", length=190, unique=true)
     * @Assert\Length(min="1", max="190")
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
     * @Assert\Length(min="1", max="200")
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

    public function getGid(): ?int
    {
        return $this->gid;
    }

    public function setGid(int $gid): self
    {
        $this->gid = $gid;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getGtype(): int
    {
        return $this->gtype;
    }

    public function setGtype(int $gtype): self
    {
        $this->gtype = $gtype;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getNbumax(): int
    {
        return $this->nbumax;
    }

    public function setNbumax(int $nbumax): self
    {
        $this->nbumax = $nbumax;

        return $this;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(UserEntity $user): self
    {
        $this->users[] = $user;

        return $this;
    }

    public function removeUser(UserEntity $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function removeAllUsers(): self
    {
        $this->users->clear();

        return $this;
    }

    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function setApplications(Collection $applications): self
    {
        $this->applications = $applications;

        return $this;
    }
}
