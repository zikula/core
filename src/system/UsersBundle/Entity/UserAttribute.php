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

namespace Zikula\UsersBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\UsersBundle\Repository\UserAttributeRepository;

/**
 * User attributes store extra information about each user account.
 */
#[ORM\Entity(repositoryClass: UserAttributeRepository::class)]
#[ORM\Table(name: 'users_attributes')]
class UserAttribute
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'attributes')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'uid', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Id]
    #[ORM\Column(length: 80)]
    #[Assert\Length(min: 1, max: 80)]
    private string $name;

    #[ORM\Column(type: Types::TEXT)]
    private $value;

    /**
     * non-persisted property
     */
    private string $extra;

    /**
     * @param mixed $value
     */
    public function __construct(User $user, string $name, $value)
    {
        $this->setUser($user);
        $this->setAttribute($name, $value);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

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

    public function getValue()
    {
        return $this->value;
    }

    public function setValue(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setAttribute(string $name, mixed $value): self
    {
        $this->setName($name);
        $this->setValue($value);

        return $this;
    }

    public function getExtra(): string
    {
        return $this->extra;
    }

    public function setExtra(string $extra): self
    {
        $this->extra = $extra;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
