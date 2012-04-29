<?php

namespace SecurityCenterModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * ScIntrusion
 *
 * @ORM\Table(name="sc_intrusion")
 * @ORM\Entity
 */
class Intrusion extends EntityAccess
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
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    private $name;

    /**
     * @var string $tag
     *
     * @ORM\Column(name="tag", type="string", length=40, nullable=true)
     */
    private $tag;

    /**
     * @var text $value
     *
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    private $value;

    /**
     * @var text $page
     *
     * @ORM\Column(name="page", type="text", nullable=false)
     */
    private $page;
    
    /**
     * @ORM\ManyToOne(targetEntity="\UsersModule\Entity\User")
     * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
     */
    private $user;

    /**
     * @var string $ip
     *
     * @ORM\Column(name="ip", type="string", length=40, nullable=false)
     */
    private $ip;

    /**
     * @var integer $impact
     *
     * @ORM\Column(name="impact", type="integer", nullable=false)
     */
    private $impact;

    /**
     * @var text $filters
     *
     * @ORM\Column(name="filters", type="text", nullable=false)
     */
    private $filters;

    /**
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;


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
     * @return ScIntrusion
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
     * Set tag
     *
     * @param string $tag
     * @return ScIntrusion
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * Get tag
     *
     * @return string 
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set value
     *
     * @param text $value
     * @return ScIntrusion
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get value
     *
     * @return text 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set page
     *
     * @param text $page
     * @return ScIntrusion
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Get page
     *
     * @return text 
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set user
     *
     * @param \UsersModule\Entity\User $user
     * @return ScIntrusion
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return \UsersModule\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return ScIntrusion
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set impact
     *
     * @param integer $impact
     * @return ScIntrusion
     */
    public function setImpact($impact)
    {
        $this->impact = $impact;
        return $this;
    }

    /**
     * Get impact
     *
     * @return integer 
     */
    public function getImpact()
    {
        return $this->impact;
    }

    /**
     * Set filters
     *
     * @param text $filters
     * @return ScIntrusion
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Get filters
     *
     * @return text 
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set date
     *
     * @param datetime $date
     * @return ScIntrusion
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return datetime 
     */
    public function getDate()
    {
        return $this->date;
    }
}