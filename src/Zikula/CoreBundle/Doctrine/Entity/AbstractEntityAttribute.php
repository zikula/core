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

namespace Zikula\Bundle\CoreBundle\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

/**
 * Base class of many-to-many association between any entity and attribute.
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractEntityAttribute extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="1", max="255")
     */
    protected string $name;

    /**
     * @ORM\Column(type="array")
     */
    protected string $value;

    abstract public function getEntity()/* TODO add return type: mixed*/;

    abstract public function setEntity($entity)/* TODO add return type: self*/;

    public function __construct(string $name, string $value, $entity)
    {
        $this->setName($name)->setValue($value)->setEntity($entity);
    }

    public function getId(): int
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

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
