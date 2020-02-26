<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Zikula\UsersModule\Entity\UserEntity;

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
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;

    /**
     * Name of the entity
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     * @Assert\Length(min="0", max="128", allowEmptyString="false")
     * @var string
     */
    private $name;

    /**
     * Tag
     *
     * @ORM\Column(name="tag", type="string", length=150, nullable=true)
     * @Assert\Length(min="0", max="150", allowEmptyString="true")
     * @var string
     */
    private $tag;

    /**
     * Value
     *
     * @ORM\Column(name="value", type="text", nullable=false)
     * @Assert\NotNull
     * @var string
     */
    private $value;

    /**
     * Page called when intrusion was detected
     *
     * @ORM\Column(name="page", type="text", nullable=false)
     * @Assert\NotNull
     * @var string
     */
    private $page;

    /**
     * User id assoicated with the intrusion
     *
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
     */
    private $user;

    /**
     * Ip address of the intrustion
     *
     * @ORM\Column(name="ip", type="string", length=40, nullable=false)
     * @Assert\Length(min="0", max="40", allowEmptyString="false")
     * @var string
     */
    private $ip;

    /**
     * Impact
     *
     * @ORM\Column(name="impact", type="integer", nullable=false)
     * @Assert\NotNull
     * @var int
     */
    private $impact;

    /**
     * Filters
     *
     * @ORM\Column(name="filters", type="text", nullable=false)
     * @Assert\NotNull
     * @var string
     */
    private $filters;

    /**
     * Timestamp of the intrusion
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     * @Assert\NotNull
     * @var DateTime
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setPage(string $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getPage(): string
    {
        return $this->page;
    }

    public function setUser(UserEntity $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): UserEntity
    {
        return $this->user;
    }

    public function getUid(): int
    {
        return $this->getUser()->getUid();
    }

    public function getUsername(): string
    {
        return $this->getUser()->getUname();
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setImpact(int $impact): self
    {
        $this->impact = $impact;

        return $this;
    }

    public function getImpact(): int
    {
        return $this->impact;
    }

    public function setFilters(string $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function getFilters(): string
    {
        return $this->filters;
    }

    public function setDate(DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }
}
