<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

/**
 * HookRuntime
 *
 * @ORM\Table(name="hook_runtime")
 * @ORM\Entity(repositoryClass="Zikula\Bundle\HookBundle\Repository\HookRuntimeRepository")
 */
class HookRuntimeEntity extends EntityAccess
{
    use HookEntityTrait;

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="sowner", type="string", length=60, nullable=false)
     * @Assert\Length(min="0", max="60")
     * @var string
     */
    private $sowner;

    /**
     * @ORM\Column(name="powner", type="string", length=60, nullable=false)
     * @Assert\Length(min="0", max="60")
     * @var string
     */
    private $powner;

    /**
     * @ORM\Column(name="sareaid", type="string", length=512, nullable=false)
     * @Assert\Length(min="0", max="512")
     * @var string
     */
    private $sareaid;

    /**
     * @ORM\Column(name="pareaid", type="string", length=512, nullable=false)
     * @Assert\Length(min="0", max="512")
     * @var string
     */
    private $pareaid;

    /**
     * @ORM\Column(name="eventname", type="string", length=120, nullable=false)
     * @Assert\Length(min="0", max="120")
     * @var string
     */
    private $eventname;

    /**
     * @ORM\Column(name="classname", type="string", length=120, nullable=false)
     * @Assert\Length(min="0", max="120")
     * @var string
     */
    private $classname;

    /**
     * @ORM\Column(name="method", type="string", length=60, nullable=false)
     * @Assert\Length(min="0", max="60")
     * @var string
     */
    private $method;

    /**
     * @ORM\Column(name="priority", type="integer", nullable=false)
     * @var int
     */
    private $priority;

    public function setEventname(string $eventname): void
    {
        $this->eventname = $eventname;
    }

    public function getEventname(): string
    {
        return $this->eventname;
    }

    public function setClassname(string $classname): void
    {
        $this->classname = $classname;
    }

    public function getClassname(): string
    {
        return $this->classname;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
