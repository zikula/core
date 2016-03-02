<?php

namespace Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity;

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
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="owner", type="string", length=40, nullable=false)
     */
    private $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="subowner", type="string", length=40, nullable=true)
     */
    private $subowner;

    /**
     * @var integer
     *
     * @ORM\Column(name="pareaid", type="integer", nullable=false)
     */
    private $pareaid;

    /**
     * @var string
     *
     * @ORM\Column(name="hooktype", type="string", length=20, nullable=false)
     */
    private $hooktype;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=20, nullable=false)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="classname", type="string", length=60, nullable=false)
     */
    private $classname;

    /**
     * @var string
     *
     * @ORM\Column(name="method", type="string", length=60, nullable=false)
     */
    private $method;

    /**
     * @var string
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
     * @return HookProviderEntity
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
     * @return HookProviderEntity
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
     * @return HookProviderEntity
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
     * @return HookProviderEntity
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
     * @return HookProviderEntity
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
     * @return HookProviderEntity
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
     * @return HookProviderEntity
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
     * @return HookProviderEntity
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
