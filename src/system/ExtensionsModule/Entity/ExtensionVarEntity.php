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
     * @Assert\Length(min="1", max="64")
     * @var string
     */
    private $modname;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Length(min="1", max="64")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="text", length=512)
     * @Assert\Length(min="1", max="512")
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

    public function setValue($value): void
    {
        $this->value = serialize($value);
    }
}
