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

namespace Zikula\UsersModule\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * UserSession entity class.
 *
 * @ORM\Entity(repositoryClass="Zikula\UsersModule\Entity\Repository\UserSessionRepository")
 * @ORM\Table(name="session_info")
 *
 * Sessions Table.
 * Stores per-user session information for users who are logged in.
 * (Note: Users who use the "remember me" option when logging in remain logged in across multiple visits for a defined period of time, therefore their session record remains active.)
 */
class UserSessionEntity extends EntityAccess
{
    /**
     * Session ID: Primary identifier
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=60)
     * @ORM\GeneratedValue(strategy="NONE")
     * @Assert\Length(min="0", max="60", allowEmptyString="false")
     * @var string
     */
    private $sessid;

    /**
     * IP Address: The user's IP address for the session.
     *
     * @ORM\Column(type="string", length=40)
     * @Assert\Length(min="0", max="40", allowEmptyString="false")
     * @var string
     */
    private $ipaddr;

    /**
     * Last Used Date/Time: Date/time this session record was last used for the user.
     * NOTE: This is stored as an SQL datetime, which is highly dependent on both PHP's timezone setting, and on the database server's timezone setting. If they do not match, then inconsistencies will propogate.
     * If Zikula is moved to a new database server with a different time zone configuration, then these dates/times will be interpreted based on the new time zone, not the original one!
     *
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    private $lastused;

    /**
     * User ID: Primary ID of the user record to which this session record is related. Foreign key to users table.
     *
     * @ORM\Column(type="integer")
     * @var int
     */
    private $uid;

    /**
     * Remember Me?: Whether the last successful login by the user (which creted this session record) used the "remember me" option to remain logged in between visits.
     *
     * @ORM\Column(type="smallint")
     * @var int
     */
    private $remember;

    /**
     * Session Variables: Per-user/per-session variables. (Serialized)
     *
     * @ORM\Column(type="text")
     * @var string
     */
    private $vars;

    /**
     * constructor
     */
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

    public function setSessid(string $sessid): void
    {
        $this->sessid = $sessid;
    }

    public function getIpaddr(): string
    {
        return $this->ipaddr;
    }

    public function setIpaddr(string $ipaddr): void
    {
        $this->ipaddr = $ipaddr;
    }

    public function getLastused(): DateTime
    {
        return $this->lastused;
    }

    /**
     * @param string|DateTime $lastused the user session's last used datetime
     */
    public function setLastused($lastused): void
    {
        if ($lastused instanceof DateTime) {
            $this->lastused = $lastused;
        } else {
            $this->lastused = new DateTime($lastused);
        }
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }

    public function getRemember(): int
    {
        return $this->remember;
    }

    public function setRemember(int $remember): void
    {
        $this->remember = $remember;
    }

    public function getVars(): string
    {
        return $this->vars;
    }

    public function setVars(string $vars): void
    {
        $this->vars = $vars;
    }
}
