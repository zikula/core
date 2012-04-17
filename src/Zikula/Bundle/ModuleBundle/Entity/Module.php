<?php

namespace Zikula\Bundle\ModuleBundle\Entity;

use Doctrine\ORM\Mapping as ORM; 

/**
 * Zikula\ModuleBundle\Entity\Module
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Module
{
    const STATE_NEW = 0;
    const STATE_ACTIVE = 1;
    const STATE_INACTIVE = 2;
    const STATE_NEED_UPGRADE = 3;

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $class
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var smallint $state
     *
     * @ORM\Column(name="state", type="smallint")
     */
    private $state;

    /**
     * @var string $class
     *
     * @ORM\Column(name="version", type="string", length=10)
     */
    private $version;



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
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * Set state
     *
     * @param smallint $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Get state
     *
     * @return smallint
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set version
     *
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}