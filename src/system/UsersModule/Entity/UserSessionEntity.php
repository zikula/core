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

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Zikula\UsersModule\Repository\UserSessionRepository;

/**
 * Sessions Table.
 * Stores per-user session information for users who are logged in.
 * (Note: Users who use the "remember me" option when logging in remain logged in across multiple visits for a defined period of time, therefore their session record remains active.)
 */
#[ORM\Entity(repositoryClass: UserSessionRepository::class)]
#[ORM\Table(name: 'session_info')]
class UserSessionEntity extends EntityAccess
{
    #[ORM\Id]
    #[ORM\Column(length: 60)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[Assert\Length(min: 1, max: 60)]
    private string $sessid;

    /**
     * IP Address: The user's IP address for the session.
     */
    #[ORM\Column(length: 40)]
    #[Assert\Length(min: 1, max: 40)]
    private string $ipaddr;

    /**
     * Last Used Date/Time: Date/time this session record was last used for the user.
     * NOTE: This is stored as an SQL datetime, which is highly dependent on both PHP's timezone setting, and on the database server's timezone setting. If they do not match, then inconsistencies will propogate.
     * If Zikula is moved to a new database server with a different time zone configuration, then these dates/times will be interpreted based on the new time zone, not the original one!
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTimeInterface $lastused;

    /**
     * User ID: Primary ID of the user record to which this session record is related. Foreign key to users table.
     */
    #[ORM\Column]
    private int $uid;

    /**
     * Remember Me?: Whether the last successful login by the user (which creted this session record) used the "remember me" option to remain logged in between visits.
     */
    #[ORM\Column(type: Types::SMALLINT)]
    private int $remember;

    /**
     * Session Variables: Per-user/per-session variables. (Serialized)
     */
    #[ORM\Column(type: Types::TEXT)]
    private string $vars;

    public function __construct()
    {
        $this->sessid = '';
        $this->ipaddr = '';
        $this->lastused = new DateTime('now');
        $this->uid = 0;
        $this->remember = 0;
        $this->vars = '';
    }

    public function getSessid(): ?string
    {
        return $this->sessid;
    }

    public function setSessid(string $sessid): self
    {
        $this->sessid = $sessid;

        return $this;
    }

    public function getIpaddr(): string
    {
        return $this->ipaddr;
    }

    public function setIpaddr(string $ipaddr): self
    {
        $this->ipaddr = $ipaddr;

        return $this;
    }

    public function getLastused(): DateTimeInterface
    {
        return $this->lastused;
    }

    public function setLastused(string|DateTimeInterface $lastused): self
    {
        if ($lastused instanceof DateTimeInterface) {
            $this->lastused = $lastused;
        } else {
            $this->lastused = new DateTime($lastused);
        }

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

    public function getRemember(): int
    {
        return $this->remember;
    }

    public function setRemember(int $remember): self
    {
        $this->remember = $remember;

        return $this;
    }

    public function getVars(): string
    {
        return $this->vars;
    }

    public function setVars(string $vars): self
    {
        $this->vars = $vars;

        return $this;
    }
}
