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

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Zikula\ZAuthModule\Repository\UserVerificationRepository;

/**
 * Account-change verification table.
 * Holds a one-time use, expirable verification code used when a user needs to change his email address,
 * reset his password and has not answered any security questions,
 * or when a new user is registering with the site for the first time.
 */
#[ORM\Entity(repositoryClass: UserVerificationRepository::class)]
#[ORM\Table(name: 'users_verifychg')]
class UserVerificationEntity extends EntityAccess
{
    /**
     * ID: Primary ID of the verification record. Not related to the uid.
     */
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    /**
     * Change type: a code indicating what type of change action created this record.
     */
    #[ORM\Column(type: Types::SMALLINT)]
    private int $changetype;

    /**
     * User ID: Primary ID of the user record to which this verification record is related. Foreign key to users table.
     */
    #[ORM\Column]
    private int $uid;

    /**
     * New e-mail address: If the change type indicates that this verification record was created as a result of a user changing his e-mail address,
     * then this field holds the new address temporarily until the verification is complete.
     * Only after the verification code is received back from the user (thus, verifying the new e-mail address) is the new e-mail address saved to the user's account record.
     */
    #[ORM\Column(length: 60)]
    #[Assert\AtLeastOneOf([
        new Assert\Blank(),
        new Assert\Length(min: 1, max: 60)
    ])]
    private string $newemail;

    /**
     * Verification Code: The verification code last sent to the user to verify the requested action, as a salted hash of the value sent.
     */
    #[ORM\Column(length: 138)]
    #[Assert\AtLeastOneOf([
        new Assert\Blank(),
        new Assert\Length(min: 1, max: 138)
    ])]
    private string $verifycode;

    /**
     * Date/Time created: The date and time the verification record was created, as a UTC date/time, used to expire the record.
     */
    #[ORM\Column(name: 'created_dt', type: Types::DATETIME_MUTABLE)]
    private DateTimeInterface $createdDate;

    public function __construct()
    {
        $this->changetype = 0;
        $this->uid = 0;
        $this->newemail = '';
        $this->verifycode = '';
        $this->createdDate = new DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getChangetype(): int
    {
        return $this->changetype;
    }

    public function setChangetype(int $changetype): self
    {
        $this->changetype = $changetype;

        return $this;
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function setUid(int $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getNewemail(): string
    {
        return $this->newemail;
    }

    public function setNewemail(string $newemail): self
    {
        $this->newemail = $newemail;

        return $this;
    }

    public function getVerifycode(): string
    {
        return $this->verifycode;
    }

    public function setVerifycode(string $verifycode): self
    {
        $this->verifycode = $verifycode;

        return $this;
    }

    public function getCreatedDate(): DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(string|DateTimeInterface $createdDate): self
    {
        if ($createdDate instanceof DateTimeInterface) {
            $this->createdDate = $createdDate;
        } else {
            $this->createdDate = new DateTime($createdDate);
        }

        return $this;
    }
}
