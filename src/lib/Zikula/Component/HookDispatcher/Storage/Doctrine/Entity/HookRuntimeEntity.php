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
     * @ORM\Column(name="sowner", type="string", length=40, nullable=false)
     */
    private $sowner;

    /**
     * @var string
     *
     * @ORM\Column(name="subsowner", type="string", length=40, nullable=true)
     */
    private $subsowner;

    /**
     * @var string
     *
     * @ORM\Column(name="powner", type="string", length=40, nullable=false)
     */
    private $powner;

    /**
     * @var string
     *
     * @ORM\Column(name="subpowner", type="string", length=40, nullable=true)
     */
    private $subpowner;

    /**
     * @var integer
     *
     * @ORM\Column(name="sareaid", type="integer", nullable=false)
     */
    private $sareaid;

    /**
     * @var integer
     *
     * @ORM\Column(name="pareaid", type="integer", nullable=false)
     */
    private $pareaid;

    /**
     * @var string
     *
     * @ORM\Column(name="eventname", type="string", length=100, nullable=false)
     */
    private $eventname;

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
     * @var integer
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
     * @return HookRuntimeEntity
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
     * @return HookRuntimeEntity
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
     * @return HookRuntimeEntity
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
     * @return HookRuntimeEntity
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
     * @return HookRuntimeEntity
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
     * @return HookRuntimeEntity
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
     * @return HookRuntimeEntity
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
     * @return HookRuntimeEntity
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
     * @return HookRuntimeEntity
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
     * @return HookRuntimeEntity
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
     * @return HookRuntimeEntity
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
