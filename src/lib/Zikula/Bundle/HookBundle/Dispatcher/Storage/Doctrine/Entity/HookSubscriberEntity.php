<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HookSubscriber
 *
 * @ORM\Table(name="hook_subscriber")
 * @ORM\Entity
 */
class HookSubscriberEntity
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
     * @ORM\Column(name="sareaid", type="integer", nullable=false)
     */
    private $sareaid;

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
     * @ORM\Column(name="eventname", type="string", length=100, nullable=false)
     */
    private $eventname;

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
     * @return HookSubscriberEntity
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
     * @return HookSubscriberEntity
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
     * Set sareaid
     *
     * @param integer $sareaid
     * @return HookSubscriberEntity
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
     * Set hooktype
     *
     * @param string $hooktype
     * @return HookSubscriberEntity
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
     * @return HookSubscriberEntity
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
     * Set eventname
     *
     * @param string $eventname
     * @return HookSubscriberEntity
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
}
