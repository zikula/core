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

namespace Zikula\ExtensionsModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

/**
 * Extension dependencies.
 *
 * @ORM\Entity(repositoryClass="Zikula\ExtensionsModule\Entity\Repository\ExtensionDependencyRepository")
 * @ORM\Table(name="extension_deps")
 */
class ExtensionDependencyEntity extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $modid;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Length(min="1", max="64")
     * @var string
     */
    private $modname;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\AtLeastOneOf(
     *     @Assert\Blank(),
     *     @Assert\Length(min="1", max="10")
     * )
     * @var string
     */
    private $minversion;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\AtLeastOneOf(
     *     @Assert\Blank(),
     *     @Assert\Length(min="1", max="10")
     * )
     * @var string
     */
    private $maxversion;

    /**
     * @ORM\Column(type="integer", length=64)
     * @var int
     */
    private $status;

    /**
     * Non-persisted data
     * The reason of a dependency is not saved into the database to avoid multilingual problems
     * but loaded from composer.json.
     * @var string
     */
    private $reason;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getModid(): int
    {
        return $this->modid;
    }

    public function setModid(int $modid): self
    {
        $this->modid = $modid;

        return $this;
    }

    public function getModname(): string
    {
        return $this->modname;
    }

    public function setModname(string $modname): self
    {
        $this->modname = $modname;

        return $this;
    }

    public function getMinversion(): string
    {
        return $this->minversion;
    }

    public function setMinversion(string $minVersion): self
    {
        $this->minversion = $minVersion;

        return $this;
    }

    public function getMaxversion(): string
    {
        return $this->maxversion;
    }

    public function setMaxversion(string $maxVersion): self
    {
        $this->maxversion = $maxVersion;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the reason for a dependency.
     *
     * Note: The reason of a dependency is not saved into the database to avoid multilingual problems but loaded during sync.
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Non-persisted data.
     *
     * Note: The reason of a dependency is not saved into the database to avoid multilingual problems but loaded during sync.
     */
    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }
}
