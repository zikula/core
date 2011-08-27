<?php

namespace Zikula\ModulesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zikula\ModulesBundle\Entity\Module
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Module
{
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
     * @ORM\Column(name="class", type="string", length=255)
     */
    private $class;

    /**
     * @var smallint $state
     *
     * @ORM\Column(name="state", type="smallint")
     */
    private $state;


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
     * Set class
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
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
}