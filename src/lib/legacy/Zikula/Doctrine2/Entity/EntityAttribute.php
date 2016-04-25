<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * Base class of many-to-many association between any entity and attribute.
 *
 * @ORM\MappedSuperclass
 *
 * @deprecated since 1.4.0
 */
abstract class Zikula_Doctrine2_Entity_EntityAttribute extends Zikula_EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="array")
     * @var string
     */
    private $value;

    public function __construct($name,
                                $value,
                                $entity)
    {
        $this->name = $name;
        $this->value = $value;
        $this->setEntity($entity);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    abstract public function getEntity();

    abstract public function setEntity($entity);
}
