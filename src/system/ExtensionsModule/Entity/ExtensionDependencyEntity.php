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

namespace Zikula\ExtensionsModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * Extension dependencies.
 *
 * @ORM\Entity(repositoryClass="Zikula\ExtensionsModule\Entity\Repository\ExtensionDependencyRepository")
 * @ORM\Table(name="module_deps")
 */
class ExtensionDependencyEntity extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $modid;

    /**
     * @ORM\Column(type="string", length=64)
     * @var string
     */
    private $modname;

    /**
     * @ORM\Column(type="string", length=10)
     * @var string
     */
    private $minversion;

    /**
     * @ORM\Column(type="string", length=10)
     * @var string
     */
    private $maxversion;

    /**
     * @ORM\Column(type="integer", length=64)
     * @var integer
     */
    private $status;

    /**
     * Non-persisted data
     * The reason of a dependency is not saved into the database to avoid multilingual problems but loaded from Version.php.
     * @var string
     */
    private $reason = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getModid(): int
    {
        return $this->modid;
    }

    public function setModid(int $modid): void
    {
        $this->modid = $modid;
    }

    public function getModname(): string
    {
        return $this->modname;
    }

    public function setModname(string $modname): void
    {
        $this->modname = $modname;
    }

    public function getMinversion(): string
    {
        return $this->minversion;
    }

    public function setMinversion(string $minVersion): void
    {
        $this->minversion = $minVersion;
    }

    public function getMaxversion(): string
    {
        return $this->maxversion;
    }

    public function setMaxversion(string $maxVersion): void
    {
        $this->maxversion = $maxVersion;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * Get the reason for a dependency.
     *
     * Note: The reason of a dependency is not saved into the database to avoid multilingual problems but loaded during sync.
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * Non-persisted data.
     *
     * Note: The reason of a dependency is not saved into the database to avoid multilingual problems but loaded during sync.
     */
    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }
}
