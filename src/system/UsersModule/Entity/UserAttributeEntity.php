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

namespace Zikula\UsersModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

/**
 * UserAttribute entity class.
 *
 * @ORM\Entity(repositoryClass="Zikula\UsersModule\Entity\Repository\UserAttributeRepository")
 * @ORM\Table(name="users_attributes")
 *
 * User attributes table.
 * Stores extra information about each user account.
 */
class UserAttributeEntity extends EntityAccess
{
    /**
     * user id to which the attribute belongs
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UserEntity", inversedBy="attributes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="uid", onDelete="CASCADE")
     * @var int
     */
    private $user;

    /**
     * the name of the attribute
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=80)
     * @Assert\Length(min="1", max="80")
     * @var string
     */
    private $name;

    /**
     * the value for the attribute
     *
     * @ORM\Column(type="text")
     * @var string
     */
    private $value;

    /**
     * non-persisted property
     *
     * @var string
     */
    private $extra;

    /**
     * @param mixed $value
     */
    public function __construct(UserEntity $user, string $name, $value)
    {
        $this->setUser($user);
        $this->setAttribute($name, $value);
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

    /**
     * @param mixed $value
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function setAttribute(string $name, $value): self
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
