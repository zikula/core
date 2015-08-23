<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\PageLockModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pagelock
 *
 * @ORM\Table(name="pagelock")
 * @ORM\Entity
 */
class PageLockEntity
{
    /**
     * Pagelock ID
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Pagelock name
     *
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * Creation date of the pagelock
     *
     * @var \Datetime $cdate
     *
     * @ORM\Column(name="cdate", type="datetime", nullable=false)
     */
    private $cdate;

    /**
     * Expiry date of the pagelock
     *
     * @var \Datetime $edate
     *
     * @ORM\Column(name="edate", type="datetime", nullable=false)
     */
    private $edate;

    /**
     * Session ID for this pagelock
     *
     * @var string $session
     *
     * @ORM\Column(name="session", type="string", length=50, nullable=false)
     */
    private $session;

    /**
     * Title of the pagelock
     *
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=false)
     */
    private $title;

    /**
     * IP address of the machine acquiring the pagelock
     *
     * @var string $ipno
     *
     * @ORM\Column(name="ipno", type="string", length=30, nullable=false)
     */
    private $ipno;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return PagelockEntity
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set cdate
     *
     * @param \Datetime $cdate
     * @return PagelockEntity
     */
    public function setCdate($cdate)
    {
        $this->cdate = $cdate;
        return $this;
    }

    /**
     * Get cdate
     *
     * @return \Datetime
     */
    public function getCdate()
    {
        return $this->cdate;
    }

    /**
     * Set edate
     *
     * @param \Datetime $edate
     * @return PagelockEntity
     */
    public function setEdate($edate)
    {
        $this->edate = $edate;
        return $this;
    }

    /**
     * Get edate
     *
     * @return \Datetime
     */
    public function getEdate()
    {
        return $this->edate;
    }

    /**
     * Set session
     *
     * @param string $session
     * @return PagelockEntity
     */
    public function setSession($session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * Get session
     *
     * @return string
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return PagelockEntity
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set ipno
     *
     * @param string $ipno
     * @return PagelockEntity
     */
    public function setIpno($ipno)
    {
        $this->ipno = $ipno;
        return $this;
    }

    /**
     * Get ipno
     *
     * @return string
     */
    public function getIpno()
    {
        return $this->ipno;
    }
}