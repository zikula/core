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

namespace Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * HookRuntime
 *
 * @ORM\Table(name="hook_runtime")
 * @ORM\Entity(repositoryClass="Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\Repository\HookRuntimeRepository")
 */
class HookRuntimeEntity extends EntityAccess
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="sowner", type="string", length=60, nullable=false)
     * @Assert\Length(min="0", max="60", allowEmptyString="false")
     * @var string
     */
    private $sowner;

    /**
     * @ORM\Column(name="powner", type="string", length=60, nullable=false)
     * @Assert\Length(min="0", max="60", allowEmptyString="false")
     * @var string
     */
    private $powner;

    /**
     * @ORM\Column(name="sareaid", type="string", length=512, nullable=false)
     * @Assert\Length(min="0", max="512", allowEmptyString="false")
     * @var string
     */
    private $sareaid;

    /**
     * @ORM\Column(name="pareaid", type="string", length=512, nullable=false)
     * @Assert\Length(min="0", max="512", allowEmptyString="false")
     * @var string
     */
    private $pareaid;

    /**
     * @ORM\Column(name="eventname", type="string", length=120, nullable=false)
     * @Assert\Length(min="0", max="120", allowEmptyString="false")
     * @var string
     */
    private $eventname;

    /**
     * @ORM\Column(name="classname", type="string", length=120, nullable=false)
     * @Assert\Length(min="0", max="120", allowEmptyString="false")
     * @var string
     */
    private $classname;

    /**
     * @ORM\Column(name="method", type="string", length=60, nullable=false)
     * @Assert\Length(min="0", max="60", allowEmptyString="false")
     * @var string
     */
    private $method;

    /**
     * @ORM\Column(name="priority", type="integer", nullable=false)
     * @var int
     */
    private $priority;

    public function getId(): int
    {
        return $this->id;
    }

    public function setSowner(string $sowner): self
    {
        $this->sowner = $sowner;

        return $this;
    }

    public function getSowner(): string
    {
        return $this->sowner;
    }

    public function setPowner(string $powner): self
    {
        $this->powner = $powner;

        return $this;
    }

    public function getPowner(): string
    {
        return $this->powner;
    }

    public function setSareaid(string $subscriberAreaId): self
    {
        $this->sareaid = $subscriberAreaId;

        return $this;
    }

    public function getSareaid(): string
    {
        return $this->sareaid;
    }

    public function setPareaid(string $providerAreaId): self
    {
        $this->pareaid = $providerAreaId;

        return $this;
    }

    public function getPareaid(): string
    {
        return $this->pareaid;
    }

    public function setEventname(string $eventname): self
    {
        $this->eventname = $eventname;

        return $this;
    }

    public function getEventname(): string
    {
        return $this->eventname;
    }

    public function setClassname(string $classname): self
    {
        $this->classname = $classname;

        return $this;
    }

    public function getClassname(): string
    {
        return $this->classname;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
