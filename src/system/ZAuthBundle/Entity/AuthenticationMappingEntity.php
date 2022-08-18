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

namespace Zikula\ZAuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Zikula\UsersBundle\Validator\Constraints as UsersAssert;
use Zikula\ZAuthBundle\Repository\AuthenticationMappingRepository;
use Zikula\ZAuthBundle\Validator\Constraints as ZAuthAssert;

#[ORM\Entity(repositoryClass: AuthenticationMappingRepository::class)]
#[ORM\Table(name: 'zauth_authentication_mapping')]
#[ZAuthAssert\ValidUserFields]
class AuthenticationMappingEntity extends EntityAccess implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column]
    #[Assert\Length(min: 1, max: 255)]
    private string $method;

    #[ORM\Column]
    private int $uid;

    #[ORM\Column]
    #[UsersAssert\ValidUname]
    #[Assert\Length(min: 1, max: 255)]
    private string $uname;

    #[ORM\Column]
    #[UsersAssert\ValidEmail]
    #[Assert\Length(min: 1, max: 255)]
    private string $email;

    #[ORM\Column]
    private bool $verifiedEmail;

    /**
     * Password: User's password for logging in.
     * This value is salted and hashed.
     */
    #[ORM\Column]
    #[Assert\Length(min: 1, max: 255)]
    #[ZAuthAssert\ValidPassword()]
    private string $pass;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function setUid(int $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getUname(): ?string
    {
        return $this->uname;
    }

    public function setUname(string $uname): self
    {
        $this->uname = $uname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isVerifiedEmail(): bool
    {
        return $this->verifiedEmail;
    }

    public function setVerifiedEmail(bool $verifiedEmail): self
    {
        $this->verifiedEmail = $verifiedEmail;

        return $this;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function setPass(?string $pass): self
    {
        if (isset($pass)) {
            $this->pass = $pass;
        }

        return $this;
    }

    public function getUserEntityData(): array
    {
        return [
            'uid' => $this->getUid(),
            'uname' => $this->getUname(),
            'email' => $this->getEmail()
        ];
    }

    public function getRoles()
    {
        // not implemented
    }

    public function getPassword()
    {
        return $this->pass;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->uid;
    }

    public function eraseCredentials()
    {
        // not implemented
    }
}
