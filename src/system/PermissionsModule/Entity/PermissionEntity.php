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

namespace Zikula\PermissionsModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

/**
 * Permission entity class.
 *
 * @ORM\Entity(repositoryClass="Zikula\PermissionsModule\Entity\Repository\PermissionRepository")
 * @ORM\Table(name="group_perms")
 */
class PermissionEntity extends EntityAccess
{
    /**
     * permission rule id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $pid;

    /**
     * group id for the rule
     *
     * @ORM\Column(type="integer")
     * @var int
     */
    private $gid;

    /**
     * the place of the rule in the sequence
     *
     * @ORM\Column(type="integer")
     * @var int
     */
    private $sequence;

    /**
     * the component part of the rule
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    private $component;

    /**
     * the instance part of the rule
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    private $instance;

    /**
     * the access level of the rule
     *
     * @ORM\Column(type="integer")
     * @var int
     */
    private $level;

    /**
     * optional comment
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\AtLeastOneOf(
     *     @Assert\Blank(),
     *     @Assert\Length(min="1", max="255")
     * )
     * @var string
     */
    private $comment;

    /**
     * optional colour (Bootstrap contextual class)
     *
     * @ORM\Column(type="string", length=10)
     * @Assert\AtLeastOneOf(
     *     @Assert\Blank(),
     *     @Assert\Length(min="1", max="10")
     * )
     * @var string
     */
    private $colour;

    public function __construct()
    {
        $this->gid = 0;
        $this->sequence = 0;
        $this->component = '';
        $this->instance = '';
        $this->level = 0;
        $this->comment = '';
        $this->colour = '';
    }

    public function getPid(): ?int
    {
        return $this->pid;
    }

    public function setPid(int $pid): self
    {
        $this->pid = $pid;

        return $this;
    }

    public function getGid(): int
    {
        return $this->gid;
    }

    public function setGid(int $gid): self
    {
        $this->gid = $gid;

        return $this;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setSequence(int $sequence): self
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    public function setComponent(string $component): self
    {
        $this->component = $component;

        return $this;
    }

    public function getInstance(): string
    {
        return $this->instance;
    }

    public function setInstance(string $instance): self
    {
        $this->instance = $instance;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getColour(): string
    {
        return $this->colour;
    }

    public function setColour(string $colour): self
    {
        $this->colour = $colour;

        return $this;
    }
}
