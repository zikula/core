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

namespace Zikula\ExtensionsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Zikula\ExtensionsBundle\Repository\ExtensionVarRepository;

#[ORM\Entity(repositoryClass: ExtensionVarRepository::class)]
#[ORM\Table(name: 'module_vars')]
class ExtensionVarEntity extends EntityAccess
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(length: 64)]
    #[Assert\Length(min: 1, max: 64)]
    private string $modname;

    #[ORM\Column(length: 64)]
    #[Assert\Length(min: 1, max: 64)]
    private string $name;

    #[ORM\Column(type: Types::TEXT, length: 512)]
    #[Assert\Length(min: 1, max: 512)]
    private string $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getValue()
    {
        // temporarily suppress E_NOTICE to avoid using @unserialize
        $errorReporting = error_reporting(error_reporting() ^ E_NOTICE);

        try {
            $result = unserialize($this->value);
        } catch (\ErrorException $exception) {
            $result = null;
            // Warning: Class __PHP_Incomplete_Class has no unserializer
            // may happen during CLI upgrades for example
            // see also https://github.com/symfony/symfony/issues/20654
        }

        error_reporting($errorReporting);

        return $result;
    }

    public function setValue($value): self
    {
        $this->value = serialize($value);

        return $this;
    }
}
