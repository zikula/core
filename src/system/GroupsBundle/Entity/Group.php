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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\GroupsBundle\Repository\GroupRepository;
use Zikula\UsersBundle\Entity\User;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: 'groups_group')]
class Group
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $gid;

    #[ORM\Column(length: 190, unique: true)]
    #[Assert\Length(min: 1, max: 190)]
    private string $name;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $gtype;

    #[ORM\Column(length: 200)]
    #[Assert\Length(min: 1, max: 200)]
    private string $description;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $state;

    // maximum membership count
    #[ORM\Column]
    private int $nbumax;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'groups', indexBy: 'uid')]
    #[ORM\JoinTable(name: 'group_membership')]
    #[ORM\JoinColumn(name: 'gid', referencedColumnName: 'gid', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'uid', referencedColumnName: 'uid')]
    #[ORM\OrderBy(['uname' => 'ASC'])]
    /** @var User[] */
    private Collection $users;

    public function __construct()
    {
        $this->name = '';
        $this->gtype = 0;
        $this->description = '';
        $this->state = 0;
        $this->nbumax = 0;
        $this->users = new ArrayCollection();
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

    public function addUser(User $user): self
    {
        $this->users[] = $user;

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function removeAllUsers(): self
    {
        $this->users->clear();

        return $this;
    }
}
