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
 * Extension Entity.
 *
 * @ORM\Entity(repositoryClass="Zikula\ExtensionsModule\Entity\Repository\ExtensionRepository")
 * @ORM\Table(name="extensions")
 */
class ExtensionEntity extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Length(min="0", max="64", allowEmptyString="false")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="`type`", type="integer", length=2)
     * @var int
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Length(min="0", max="64", allowEmptyString="false")
     * @var string
     */
    private $displayname;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Length(min="0", max="64", allowEmptyString="false")
     * @var string
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="0", max="255", allowEmptyString="true")
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\Length(min="0", max="10", allowEmptyString="false")
     * @var string
     */
    private $version;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\Length(min="0", max="50", allowEmptyString="true")
     * @var string
     */
    private $icon;

    /**
     * @ORM\Column(type="array")
     * @var array
     */
    private $capabilities = [];

    /**
     * @ORM\Column(type="integer", length=2)
     * @var int
     */
    private $state;

    /**
     * @ORM\Column(type="array")
     * @var array
     */
    private $securityschema = [];

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Length(min="0", max="64", allowEmptyString="false")
     * @var string
     */
    private $coreCompatibility;

    public function __construct()
    {
        $this->name = '';
        $this->description = '';
        $this->icon = '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getDisplayname(): string
    {
        return $this->displayname;
    }

    public function setDisplayname(string $displayname): void
    {
        $this->displayname = $displayname;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getIcon(): string
    {
        return $this->icon ?? '';
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon ?? '';
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    public function setCapabilities(array $capabilities): void
    {
        $this->capabilities = $capabilities;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function getSecurityschema(): array
    {
        return $this->securityschema;
    }

    public function setSecurityschema(array $securityschema): void
    {
        $this->securityschema = $securityschema;
    }

    public function getCoreCompatibility(): string
    {
        return $this->coreCompatibility;
    }

    public function setCoreCompatibility(string $coreCompatibility): void
    {
        $this->coreCompatibility = $coreCompatibility;
    }
}
