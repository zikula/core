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

namespace Zikula\ZAuthModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Zikula\UsersModule\Validator\Constraints as UsersAssert;
use Zikula\ZAuthModule\Validator\Constraints as ZAuthAssert;

/**
 * @ORM\Entity(repositoryClass="Zikula\ZAuthModule\Entity\Repository\AuthenticationMappingRepository")
 * @ORM\Table(name="zauth_authentication_mapping")
 * @ZAuthAssert\ValidUserFields()
 */
class AuthenticationMappingEntity extends EntityAccess implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\Length(min="0", max="255", allowEmptyString="false")
     * @var string
     */
    private $method;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $uid;

    /**
     * @ORM\Column(type="string")
     * @Assert\Length(min="0", max="255", allowEmptyString="false")
     * @UsersAssert\ValidUname()
     * @var string
     */
    private $uname;

    /**
     * @ORM\Column(type="string")
     * @Assert\Length(min="0", max="255", allowEmptyString="false")
     * @UsersAssert\ValidEmail()
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $verifiedEmail;

    /**
     * Password: User's password for logging in.
     * This value is salted and hashed. The salt is stored in this field, delimited from the hash with a dollar sign character ($).
     *
     * @ORM\Column(type="string")
     * @Assert\Length(min="0", max="255", allowEmptyString="false")
     * @ZAuthAssert\ValidPassword()
     * @var string
     */
    private $pass;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }

    public function getUname(): ?string
    {
        return $this->uname;
    }

    public function setUname(string $uname): void
    {
        $this->uname = $uname;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function isVerifiedEmail(): bool
    {
        return $this->verifiedEmail;
    }

    public function setVerifiedEmail(bool $verifiedEmail): void
    {
        $this->verifiedEmail = $verifiedEmail;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function setPass(string $pass): void
    {
        $this->pass = $pass;
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
