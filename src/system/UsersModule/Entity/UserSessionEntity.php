<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Entity;

use Doctrine\ORM\Mapping as ORM;
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
     */
    private $sessid;

    /**
     * IP Address: The user's IP address for the session.
     *
     * @ORM\Column(type="string", length=32)
     */
    private $ipaddr;

    /**
     * Last Used Date/Time: Date/time this session record was last used for the user.
     * NOTE: This is stored as an SQL datetime, which is highly dependent on both PHP's timezone setting, and on the database server's timezone setting. If they do not match, then inconsistencies will propogate.
     * If Zikula is moved to a new database server with a different time zone configuration, then these dates/times will be interpreted based on the new time zone, not the original one!
     *
     * @ORM\Column(type="datetime")
     */
    private $lastused;

    /**
     * User ID: Primary ID of the user record to which this session record is related. Foreign key to users table.
     *
     * @ORM\Column(type="integer")
     */
    private $uid;

    /**
     * Remember Me?: Whether the last successful login by the user (which creted this session record) used the "remember me" option to remain logged in between visits.
     *
     * @ORM\Column(type="smallint")
     */
    private $remember;

    /**
     * Session Variables: Per-user/per-session variables. (Serialized)
     *
     * @ORM\Column(type="text")
     */
    private $vars;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->sessid = '';
        $this->ipaddr = '';
        $this->lastused = new \DateTime("now");
        $this->uid = 0;
        $this->remember = 0;
        $this->vars = '';
    }

    /**
     * get the session id of the user session
     *
     * @return string the user session's session id
     */
    public function getSessid()
    {
        return $this->sessid;
    }

    /**
     * set the session id for the user session
     *
     * @param string $sessid the user session's session id
     */
    public function setSessid($sessid)
    {
        $this->sessid = $sessid;
    }

    /**
     * get the ip address of the user session
     *
     * @return string the user session's ip address
     */
    public function getIpaddr()
    {
        return $this->ipaddr;
    }

    /**
     * set the ip address for the user session
     *
     * @param string $ipaddr the user session's ip address
     */
    public function setIpaddr($ipaddr)
    {
        $this->ipaddr = $ipaddr;
    }

    /**
     * get the last used datetime of the user session
     *
     * @return \DateTime the user session's last used datetime
     */
    public function getLastused()
    {
        return $this->lastused;
    }

    /**
     * set the last used datetime for the user session
     *
     * @param string|\DateTime $lastused the user session's last used datetime
     */
    public function setLastused($lastused)
    {
        if (!($lastused instanceof \DateTime)) {
            $lastused = new \DateTime($lastused);
        }
        $this->lastused = $lastused;
    }

    /**
     * get the uid of the user session
     *
     * @return integer the user session's uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * set the uid for the user session
     *
     * @param integer $uid the user session's uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * get the remember status of the user session
     *
     * @return integer the user session's remember status
     */
    public function getRemember()
    {
        return $this->remember;
    }

    /**
     * set the remember status for the user session
     *
     * @param integer $remember the user session's remember status
     */
    public function setRemember($remember)
    {
        $this->remember = $remember;
    }

    /**
     * get the vars of the user session
     *
     * @return string the user session's vars
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * set the vars for the user session
     *
     * @param string $vars the user session's vars
     */
    public function setVars($vars)
    {
        $this->vars = $vars;
    }
}
