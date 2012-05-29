<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace ExampleModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="examplemodule_user_attribute",
 *            uniqueConstraints={@ORM\UniqueConstraint(name="cat_unq",columns={"name", "entityId"})})
 */
class UserAttribute extends \Zikula\Core\Doctrine\Entity\EntityAttribute
{
    /**
     * @ORM\ManyToOne(targetEntity="ExampleModule\Entity\User", inversedBy="attributes")
     * @ORM\JoinColumn(name="entityId", referencedColumnName="id")
     * @var \ExampleModule\Entity\User
     */
    private $entity;
    
    public function getEntity()
    {
        return $this->entity;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
}
