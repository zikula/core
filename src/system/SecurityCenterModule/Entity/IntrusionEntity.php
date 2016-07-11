<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * Intrusion
 *
 * @ORM\Entity(repositoryClass="Zikula\SecurityCenterModule\Entity\Repository\IntrusionRepository")
 * @ORM\Table(name="sc_intrusion")
 */
class IntrusionEntity extends EntityAccess
{
    /**
     * ID of the entity
     *
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * name of the entity
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    private $name;

    /**
     * tag
     *
     * @var string
     *
     * @ORM\Column(name="tag", type="string", length=40, nullable=true)
     */
    private $tag;

    /**
     * value
     *
     * @var text
     *
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    private $value;

    /**
     * page called when intrusion was detected
     *
     * @var text
     *
     * @ORM\Column(name="page", type="text", nullable=false)
     */
    private $page;

    /**
     * user id assoicated with the intrusion
     *
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
     */
    private $user;

    /**
     * ip address of the intrustion
     *
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=40, nullable=false)
     */
    private $ip;

    /**
     * impact
     *
     * @var integer
     *
     * @ORM\Column(name="impact", type="integer", nullable=false)
     */
    private $impact;

    /**
     * filters
     *
     * @var text
     *
     * @ORM\Column(name="filters", type="text", nullable=false)
     */
    private $filters;

    /**
     * timestamp of the intrusion
     *
     * @var \Datetime
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
     * @return IntrusionEntity
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
     * @return IntrusionEntity
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
     * @return IntrusionEntity
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
     * @return IntrusionEntity
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
     * @param \Zikula\UsersModule\Entity\UserEntity $user
     * @return IntrusionEntity
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Zikula\UsersModule\Entity\UserEntity
     */
    public function getUser()
    {
        return $this->user;
    }

    public function getUid()
    {
        return $this->getUser()->getUid();
    }

    public function getUsername()
    {
        return $this->getUser()->getUname();
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return IntrusionEntity
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
     * @return IntrusionEntity
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
     * @return IntrusionEntity
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
     * @param \Datetime $date
     * @return IntrusionEntity
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \Datetime
     */
    public function getDate()
    {
        return $this->date;
    }
}
