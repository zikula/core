<?php

namespace Zikula\Component\HookDispatcher\Storage\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HookRuntime
 *
 * @ORM\Table(name="hook_runtime")
 * @ORM\Entity
 */
class HookRuntimeEntity
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
     * @var string $sowner
     *
     * @ORM\Column(name="sowner", type="string", length=40, nullable=false)
     */
    private $sowner;

    /**
     * @var string $subsowner
     *
     * @ORM\Column(name="subsowner", type="string", length=40, nullable=true)
     */
    private $subsowner;

    /**
     * @var string $powner
     *
     * @ORM\Column(name="powner", type="string", length=40, nullable=false)
     */
    private $powner;

    /**
     * @var string $subpowner
     *
     * @ORM\Column(name="subpowner", type="string", length=40, nullable=true)
     */
    private $subpowner;

    /**
     * @var integer $sareaid
     *
     * @ORM\Column(name="sareaid", type="integer", nullable=false)
     */
    private $sareaid;

    /**
     * @var integer $pareaid
     *
     * @ORM\Column(name="pareaid", type="integer", nullable=false)
     */
    private $pareaid;

    /**
     * @var string $eventname
     *
     * @ORM\Column(name="eventname", type="string", length=100, nullable=false)
     */
    private $eventname;

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
     * @var integer $priority
     *
     * @ORM\Column(name="priority", type="integer", nullable=false)
     */
    private $priority;


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
     * Set sowner
     *
     * @param string $sowner
     * @return HookRuntime
     */
    public function setSowner($sowner)
    {
        $this->sowner = $sowner;
        return $this;
    }

    /**
     * Get sowner
     *
     * @return string 
     */
    public function getSowner()
    {
        return $this->sowner;
    }

    /**
     * Set subsowner
     *
     * @param string $subsowner
     * @return HookRuntime
     */
    public function setSubsowner($subsowner)
    {
        $this->subsowner = $subsowner;
        return $this;
    }

    /**
     * Get subsowner
     *
     * @return string 
     */
    public function getSubsowner()
    {
        return $this->subsowner;
    }

    /**
     * Set powner
     *
     * @param string $powner
     * @return HookRuntime
     */
    public function setPowner($powner)
    {
        $this->powner = $powner;
        return $this;
    }

    /**
     * Get powner
     *
     * @return string 
     */
    public function getPowner()
    {
        return $this->powner;
    }

    /**
     * Set subpowner
     *
     * @param string $subpowner
     * @return HookRuntime
     */
    public function setSubpowner($subpowner)
    {
        $this->subpowner = $subpowner;
        return $this;
    }

    /**
     * Get subpowner
     *
     * @return string 
     */
    public function getSubpowner()
    {
        return $this->subpowner;
    }

    /**
     * Set sareaid
     *
     * @param integer $sareaid
     * @return HookRuntime
     */
    public function setSareaid($sareaid)
    {
        $this->sareaid = $sareaid;
        return $this;
    }

    /**
     * Get sareaid
     *
     * @return integer 
     */
    public function getSareaid()
    {
        return $this->sareaid;
    }

    /**
     * Set pareaid
     *
     * @param integer $pareaid
     * @return HookRuntime
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
     * Set eventname
     *
     * @param string $eventname
     * @return HookRuntime
     */
    public function setEventname($eventname)
    {
        $this->eventname = $eventname;
        return $this;
    }

    /**
     * Get eventname
     *
     * @return string 
     */
    public function getEventname()
    {
        return $this->eventname;
    }

    /**
     * Set classname
     *
     * @param string $classname
     * @return HookRuntime
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
     * @return HookRuntime
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
     * @return HookRuntime
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

    /**
     * Set priority
     *
     * @param integer $priority
     * @return HookRuntime
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Get priority
     *
     * @return integer 
     */
    public function getPriority()
    {
        return $this->priority;
    }
}