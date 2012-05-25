<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Doctrine\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * Category registry entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="categories_registry",indexes={@ORM\index(name="idx_categories_registry",columns={"modname","entityname","property"})})
 */
class CategoryRegistry extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", type="integer", nullable=false)
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60)
     * @var string
     */
    private $modname;

    /**
     * @ORM\Column(type="string", length=60)
     * @var string
     */
    private $entityname;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $property;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $category_id;


    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getModname()
    {
        return $this->modname;
    }

    public function setModname($modname)
    {
        $this->modname = $modname;
    }

    public function getEntityname()
    {
        return $this->entityname;
    }

    public function setEntityname($entityname)
    {
        $this->entityname = $entityname;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function setProperty($property)
    {
        $this->property = $property;
    }

    public function getCategory_Id()
    {
        return $this->category_id;
    }

    public function setCategory_Id($category_id)
    {
        $this->category_id = $category_id;
    }
}
