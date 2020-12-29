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
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

/**
 * UserVerification entity class.
 *
 * We use annotations to define the entity mappings to database (see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html).
 *
 * @ORM\Entity(repositoryClass="Zikula\ZAuthModule\Entity\Repository\UserVerificationRepository")
 * @ORM\Table(name="users_verifychg")
 *
 * Account-change verification table.
 * Holds a one-time use, expirable verification code used when a user needs to change his email address,
 * reset his password and has not answered any security questions,
 * or when a new user is registering with the site for the first time.
 */
class UserVerificationEntity extends EntityAccess
{
    /**
     * ID: Primary ID of the verification record. Not related to the uid.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * Change type: a code indicating what type of change action created this record.
     *
     * @ORM\Column(type="smallint")
     * @var int
     */
    private $changetype;

    /**
     * User ID: Primary ID of the user record to which this verification record is related. Foreign key to users table.
     *
     * @ORM\Column(type="integer")
     * @var int
     */
    private $uid;

    /**
     * New e-mail address: If the change type indicates that this verification record was created as a result of a user changing his e-mail address,
     * then this field holds the new address temporarily until the verification is complete.
     * Only after the verification code is received back from the user (thus, verifying the new e-mail address) is the new e-mail address saved to the user's account record.
     *
     * @ORM\Column(type="string", length=60)
     * @Assert\AtLeastOneOf(
     *     @Assert\Blank(),
     *     @Assert\Length(min="0", max="60")
     * )
     * @var string
     */
    private $newemail;

    /**
     * Verification Code: The verification code last sent to the user to verify the requested action, as a salted hash of the value sent.
     *
     * @ORM\Column(type="string", length=138)
     * @Assert\AtLeastOneOf(
     *     @Assert\Blank(),
     *     @Assert\Length(min="0", max="138")
     * )
     * @var string
     */
    private $verifycode;

    /**
     * Date/Time created: The date and time the verification record was created, as a UTC date/time, used to expire the record.
     *
     * @ORM\Column(type="datetime", name="created_dt")
     * @var DateTime
     */
    private $createdDate;

    /**
     * constructor
     */
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

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getChangetype(): int
    {
        return $this->changetype;
    }

    public function setChangetype(int $changetype): void
    {
        $this->changetype = $changetype;
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }

    public function getNewemail(): string
    {
        return $this->newemail;
    }

    public function setNewemail(string $newemail): void
    {
        $this->newemail = $newemail;
    }

    public function getVerifycode(): string
    {
        return $this->verifycode;
    }

    public function setVerifycode(string $verifycode): void
    {
        $this->verifycode = $verifycode;
    }

    public function getCreatedDate(): DateTime
    {
        return $this->createdDate;
    }

    /**
     * @param string|DateTime $createdDate the user verification's created date
     */
    public function setCreatedDate($createdDate): void
    {
        if ($createdDate instanceof DateTime) {
            $this->createdDate = $createdDate;
        } else {
            $this->createdDate = new DateTime($createdDate);
        }
    }
}
