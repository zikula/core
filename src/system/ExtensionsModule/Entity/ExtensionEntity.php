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
     * @Assert\Length(min="1", max="64")
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
     * @Assert\Length(min="1", max="64")
     * @var string
     */
    private $displayname;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Length(min="1", max="64")
     * @var string
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\AtLeastOneOf(
     *     @Assert\Blank(),
     *     @Assert\Length(min="1", max="255")
     * )
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\Length(min="1", max="10")
     * @var string
     */
    private $version;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\AtLeastOneOf(
     *     @Assert\Blank(),
     *     @Assert\Length(min="1", max="50")
     * )
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
     * @Assert\Length(min="1", max="64")
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

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDisplayname(): string
    {
        return $this->displayname;
    }

    public function setDisplayname(string $displayname): self
    {
        $this->displayname = $displayname;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon ?? '';
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon ?? '';

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    public function setCapabilities(array $capabilities): self
    {
        $this->capabilities = $capabilities;

        return $this;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getSecurityschema(): array
    {
        return $this->securityschema;
    }

    public function setSecurityschema(array $securityschema): self
    {
        $this->securityschema = $securityschema;

        return $this;
    }

    public function getCoreCompatibility(): string
    {
        return $this->coreCompatibility;
    }

    public function setCoreCompatibility(string $coreCompatibility): self
    {
        $this->coreCompatibility = $coreCompatibility;

        return $this;
    }
}
