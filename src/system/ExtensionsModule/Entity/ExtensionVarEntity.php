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
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

/**
 * Extension var registry.
 *
 * @ORM\Entity(repositoryClass="Zikula\ExtensionsModule\Entity\Repository\ExtensionVarRepository")
 * @ORM\Table(name="module_vars")
 */
class ExtensionVarEntity extends EntityAccess
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
    private $modname;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Length(min="0", max="64", allowEmptyString="false")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="text", length=512)
     * @Assert\Length(min="0", max="512", allowEmptyString="false")
     * @var string
     */
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModname(): string
    {
        return $this->modname;
    }

    public function setModname(string $modname): void
    {
        $this->modname = $modname;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getValue()
    {
        return @unserialize($this->value);
    }

    public function setValue($value): void
    {
        $this->value = serialize($value);
    }
}
