<?php

namespace Zikula\Component\HookDispatcher\Storage\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HookProvider
 *
 * @ORM\Table(name="hook_provider")
 * @ORM\Entity
 */
class HookProviderEntity
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
     * @var string $owner
     *
     * @ORM\Column(name="owner", type="string", length=40, nullable=false)
     */
    private $owner;

    /**
     * @var string $subowner
     *
     * @ORM\Column(name="subowner", type="string", length=40, nullable=true)
     */
    private $subowner;

    /**
     * @var integer $pareaid
     *
     * @ORM\Column(name="pareaid", type="integer", nullable=false)
     */
    private $pareaid;

    /**
     * @var string $hooktype
     *
     * @ORM\Column(name="hooktype", type="string", length=20, nullable=false)
     */
    private $hooktype;

    /**
     * @var string $category
     *
     * @ORM\Column(name="category", type="string", length=20, nullable=false)
     */
    private $category;

    /**
     * @var string $classname
     *
     * @ORM\Column(name="classname", type="string", length=60, nullable=false)
     */
    private $classname;

    /**
     * @var string $method
     *
     * @ORM\Column(name="method", type="string", length=20, nullable=false)
     */
    private $method;

    /**
     * @var string $serviceid
     *
     * @ORM\Column(name="serviceid", type="string", length=60, nullable=true)
     */
    private $serviceid;


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
     * Set owner
     *
     * @param string $owner
     * @return HookProvider
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * Get owner
     *
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set subowner
     *
     * @param string $subowner
     * @return HookProvider
     */
    public function setSubowner($subowner)
    {
        $this->subowner = $subowner;
        return $this;
    }

    /**
     * Get subowner
     *
     * @return string
     */
    public function getSubowner()
    {
        return $this->subowner;
    }

    /**
     * Set pareaid
     *
     * @param integer $pareaid
     * @return HookProvider
     */
    public function setPareaid($pareaid)
    {
        $this->pareaid = $pareaid;
        return $this;
    }

    /**
     * Get pareaid
     *
     * @return integer
     */
    public function getPareaid()
    {
        return $this->pareaid;
    }

    /**
     * Set hooktype
     *
     * @param string $hooktype
     * @return HookProvider
     */
    public function setHooktype($hooktype)
    {
        $this->hooktype = $hooktype;
        return $this;
    }

    /**
     * Get hooktype
     *
     * @return string
     */
    public function getHooktype()
    {
        return $this->hooktype;
    }

    /**
     * Set category
     *
     * @param string $category
     * @return HookProvider
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set classname
     *
     * @param string $classname
     * @return HookProvider
     */
    public function setClassname($classname)
    {
        $this->classname = $classname;
        return $this;
    }

    /**
     * Get classname
     *
     * @return string
     */
    public function getClassname()
    {
        return $this->classname;
    }

    /**
     * Set method
     *
     * @param string $method
     * @return HookProvider
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Get method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set serviceid
     *
     * @param string $serviceid
     * @return HookProvider
     */
    public function setServiceid($serviceid)
    {
        $this->serviceid = $serviceid;
        return $this;
    }

    /**
     * Get serviceid
     *
     * @return string
     */
    public function getServiceid()
    {
        return $this->serviceid;
    }
}