<?php

namespace PageLock\Entity;

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
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var datetime $cdate
     *
     * @ORM\Column(name="cdate", type="datetime", nullable=false)
     */
    private $cdate;

    /**
     * @var datetime $edate
     *
     * @ORM\Column(name="edate", type="datetime", nullable=false)
     */
    private $edate;

    /**
     * @var string $session
     *
     * @ORM\Column(name="session", type="string", length=50, nullable=false)
     */
    private $session;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=false)
     */
    private $title;

    /**
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
     * @return Pagelock
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
     * @param datetime $cdate
     * @return Pagelock
     */
    public function setCdate($cdate)
    {
        $this->cdate = $cdate;
        return $this;
    }

    /**
     * Get cdate
     *
     * @return datetime
     */
    public function getCdate()
    {
        return $this->cdate;
    }

    /**
     * Set edate
     *
     * @param datetime $edate
     * @return Pagelock
     */
    public function setEdate($edate)
    {
        $this->edate = $edate;
        return $this;
    }

    /**
     * Get edate
     *
     * @return datetime
     */
    public function getEdate()
    {
        return $this->edate;
    }

    /**
     * Set session
     *
     * @param string $session
     * @return Pagelock
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
     * @return Pagelock
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
     * @return Pagelock
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