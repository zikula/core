<?php

namespace Zikula\Core\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SessionInfo
 *
 * @ORM\Table(name="session_info")
 * @ORM\Entity
 */
class SessionInfo
{
    /**
     * @var string $sessid
     *
     * @ORM\Column(name="sessid", type="string", length=40, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $sessid;

    /**
     * @var string $ipaddr
     *
     * @ORM\Column(name="ipaddr", type="string", length=32, nullable=false)
     */
    private $ipaddr;

    /**
     * @var datetime $lastused
     *
     * @ORM\Column(name="lastused", type="datetime", nullable=true)
     */
    private $lastused;

    /**
     * @var integer $uid
     *
     * @ORM\Column(name="uid", type="integer", nullable=true)
     */
    private $uid;

    /**
     * @var boolean $remember
     *
     * @ORM\Column(name="remember", type="boolean", nullable=false)
     */
    private $remember;

    /**
     * @var text $vars
     *
     * @ORM\Column(name="vars", type="text", nullable=false)
     */
    private $vars;


    /**
     * Get sessid
     *
     * @return string 
     */
    public function getSessid()
    {
        return $this->sessid;
    }

    /**
     * Set ipaddr
     *
     * @param string $ipaddr
     * @return SessionInfo
     */
    public function setIpaddr($ipaddr)
    {
        $this->ipaddr = $ipaddr;
        return $this;
    }

    /**
     * Get ipaddr
     *
     * @return string 
     */
    public function getIpaddr()
    {
        return $this->ipaddr;
    }

    /**
     * Set lastused
     *
     * @param datetime $lastused
     * @return SessionInfo
     */
    public function setLastused($lastused)
    {
        $this->lastused = $lastused;
        return $this;
    }

    /**
     * Get lastused
     *
     * @return datetime 
     */
    public function getLastused()
    {
        return $this->lastused;
    }

    /**
     * Set uid
     *
     * @param integer $uid
     * @return SessionInfo
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * Get uid
     *
     * @return integer 
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Set remember
     *
     * @param boolean $remember
     * @return SessionInfo
     */
    public function setRemember($remember)
    {
        $this->remember = $remember;
        return $this;
    }

    /**
     * Get remember
     *
     * @return boolean 
     */
    public function getRemember()
    {
        return $this->remember;
    }

    /**
     * Set vars
     *
     * @param text $vars
     * @return SessionInfo
     */
    public function setVars($vars)
    {
        $this->vars = $vars;
        return $this;
    }

    /**
     * Get vars
     *
     * @return text 
     */
    public function getVars()
    {
        return $this->vars;
    }
}