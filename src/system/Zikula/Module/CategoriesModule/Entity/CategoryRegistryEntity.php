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

namespace Zikula\Module\CategoriesModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Category registry entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="categories_registry",indexes={@ORM\Index(name="idx_categories_registry",columns={"modname","entityname"})})
 */
class CategoryRegistryEntity extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
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

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Zikula\Module\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="cr_uid", referencedColumnName="uid")
     */
    protected $cr_uid;

    /**
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="Zikula\Module\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="lu_uid", referencedColumnName="uid")
     */
    protected $lu_uid;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $cr_date;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $lu_date;

    /**
     * @ORM\Column(type="string", length=1)
     * @var string
     */
    protected $obj_status = 'A';

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

    /**
     * @param mixed $cr_date
     */
    public function setCrDate($cr_date)
    {
        $this->cr_date = $cr_date;
    }

    /**
     * @return mixed
     */
    public function getCrDate()
    {
        return $this->cr_date;
    }

    /**
     * @param mixed $cr_uid
     */
    public function setCrUid($cr_uid)
    {
        $this->cr_uid = $cr_uid;
    }

    /**
     * @return mixed
     */
    public function getCrUid()
    {
        return $this->cr_uid;
    }

    /**
     * @param mixed $lu_date
     */
    public function setLuDate($lu_date)
    {
        $this->lu_date = $lu_date;
    }

    /**
     * @return mixed
     */
    public function getLuDate()
    {
        return $this->lu_date;
    }

    /**
     * @param mixed $lu_uid
     */
    public function setLuUid($lu_uid)
    {
        $this->lu_uid = $lu_uid;
    }

    /**
     * @return mixed
     */
    public function getLuUid()
    {
        return $this->lu_uid;
    }

    /**
     * @param string $obj_status
     */
    public function setObjStatus($obj_status)
    {
        $this->obj_status = $obj_status;
    }

    /**
     * @return string
     */
    public function getObjStatus()
    {
        return $this->obj_status;
    }


}
