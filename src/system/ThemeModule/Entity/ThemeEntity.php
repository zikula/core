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

namespace Zikula\ThemeModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;
use Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository;

/**
 * Theme entity class.
 *
 * @ORM\Entity(repositoryClass="Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository")
 * @ORM\Table(name="themes")
 */
class ThemeEntity extends EntityAccess
{
    /**
     * theme id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * theme name
     *
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * theme type
     *
     * @ORM\Column(name="`type`", type="smallint")
     */
    private $type;

    /**
     * display name for theme
     *
     * @ORM\Column(type="string", length=64)
     */
    private $displayname;

    /**
     * theme description
     *
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * theme version
     *
     * @ORM\Column(type="string", length=10)
     */
    private $version;

    /**
     * contact for theme
     *
     * @ORM\Column(type="string", length=255)
     */
    private $contact;

    /**
     * is theme an admin capable theme
     *
     * @ORM\Column(type="smallint")
     */
    private $admin;

    /**
     * is theme an user capable theme
     *
     * @ORM\Column(type="smallint")
     */
    private $user;

    /**
     * is theme an system theme
     *
     * @ORM\Column(name="`system`", type="smallint")
     */
    private $system;

    /**
     * state of the theme
     *
     * @ORM\Column(type="smallint")
     */
    private $state;

    /**
     * is theme xhtml compliant
     *
     * @ORM\Column(type="smallint")
     */
    private $xhtml;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->name = '';
        $this->type = 0;
        $this->displayname = '';
        $this->description = '';
        $this->version = '0.0';
        $this->contact = '';
        $this->admin = 0;
        $this->user = 0;
        $this->system = 0;
        $this->state = ThemeEntityRepository::STATE_INACTIVE;
        $this->xhtml = 1;
    }

    public function getId(): int
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getContact(): string
    {
        return (string)$this->contact;
    }

    public function setContact(/*string */$contact): void
    {
        $this->contact = $contact;
    }

    public function getAdmin(): bool
    {
        return (bool)$this->admin;
    }

    public function setAdmin(bool $admin): void
    {
        $this->admin = $admin;
    }

    public function getUser(): bool
    {
        return (bool)$this->user;
    }

    public function setUser(bool $user): void
    {
        $this->user = $user;
    }

    public function getSystem(): bool
    {
        return (bool)$this->system;
    }

    public function setSystem(bool $system): void
    {
        $this->system = $system;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function getXhtml(): bool
    {
        return (bool)$this->xhtml;
    }

    public function setXhtml(bool $xhtml): void
    {
        $this->xhtml = $xhtml;
    }
}
