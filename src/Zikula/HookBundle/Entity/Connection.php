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

/**
 * @ORM\Table(name="connections")
 * @ORM\Entity(repositoryClass="Zikula\Bundle\HookBundle\Repository\HookConnectionRepository")
 */
class Connection
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @var int
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    private $event;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    private $listener;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $priority;

    public function __construct(string $event, string $listener, int $priority = 0)
    {
        $this->event = $event;
        $this->listener = $listener;
        $this->priority = $priority;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getListener(): string
    {
        return $this->listener;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function incPriority(): void
    {
        $this->priority++;
    }

    public function decPriority(): void
    {
        $this->priority--;
    }
}
