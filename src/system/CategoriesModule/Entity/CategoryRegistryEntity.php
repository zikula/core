<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Category registry entity.
 *
 * @ORM\Entity(repositoryClass="Zikula\CategoriesModule\Entity\Repository\CategoryRegistryRepository")
 * @ORM\Table(name="categories_registry",indexes={@ORM\Index(name="idx_categories_registry",columns={"modname","entityname"})})
 */
class CategoryRegistryEntity extends EntityAccess
{
    /**
     * The id of the registry entry
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;

    /**
     * The module name owning this entry
     *
     * @ORM\Column(type="string", length=60)
     * @var string
     */
    private $modname;

    /**
     * The name of the entity
     *
     * @ORM\Column(type="string", length=60)
     * @var string
     */
    private $entityname;

    /**
     * The property of the entity
     *
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $property;

    /**
     * The category id to map this entity to
     *
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $category_id;

    /**
     * The user id of the creator of this entity
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="cr_uid", referencedColumnName="uid")
     */
    protected $cr_uid;

    /**
     * The user id of the last update of this entity
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="lu_uid", referencedColumnName="uid")
     */
    protected $lu_uid;

    /**
     * The creation timestamp of this entity
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $cr_date;

    /**
     * The last updated timestamp of this entity
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $lu_date;

    /**
     * The status of the entity
     *
     * @ORM\Column(type="string", length=1)
     * @var string
     */
    protected $obj_status = 'A';

    /**
     * get the registry id
     *
     * @return int the id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * set the registry id
     *
     * @param int $id the id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * get the registry module name
     *
     * @return string the module name
     */
    public function getModname()
    {
        return $this->modname;
    }

    /**
     * set the registry module name
     *
     * @param string $modname the module name
     */
    public function setModname($modname)
    {
        $this->modname = $modname;
    }

    /**
     * get the registry entity name
     *
     * @return string the module name
     */
    public function getEntityname()
    {
        return $this->entityname;
    }

    /**
     * set the registry entity name
     *
     * @param string $entityname the module name
     */
    public function setEntityname($entityname)
    {
        $this->entityname = $entityname;
    }

    /**
     * get the registry property name
     *
     * @return string the property name
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * set the registry property name
     *
     * @param string $property the property name
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * get the registry category id
     *
     * @return int the category id
     */
    public function getCategory_Id()
    {
        return $this->category_id;
    }

    /**
     * set the registry category id
     *
     * @param int $category_id the category id
     */
    public function setCategory_Id($category_id)
    {
        $this->category_id = $category_id;
    }

    /**
     * set the creation date
     *
     * @param mixed $cr_date the creation date
     */
    public function setCr_date($cr_date)
    {
        $this->cr_date = $cr_date;
    }

    /**
     * get the creation date
     *
     * @return mixed the creation date
     */
    public function getCr_date()
    {
        return $this->cr_date;
    }

    /**
     * set the creation user id
     *
     * @param mixed $cr_uid the user id
     */
    public function setCr_uid($cr_uid)
    {
        $this->cr_uid = $cr_uid;
    }

    /**
     * get the creation user id
     *
     * @return int the user id
     */
    public function getCr_uid()
    {
        return $this->cr_uid;
    }

    /**
     * set the last updated date
     *
     * @param mixed $lu_date the date of the last update
     */
    public function setLu_date($lu_date)
    {
        $this->lu_date = $lu_date;
    }

    /**
     * get the last updated date
     *
     * @return mixed the date of the last update
     */
    public function getLu_date()
    {
        return $this->lu_date;
    }

    /**
     * set the user id of the user who last updated the entity
     *
     * @param mixed $lu_uid the user id
     */
    public function setLu_uid($lu_uid)
    {
        $this->lu_uid = $lu_uid;
    }

    /**
     * get the user id of the user who last updated the entity
     *
     * @return int the user id
     */
    public function getLu_uid()
    {
        return $this->lu_uid;
    }

    /**
     * set the status of the entity
     *
     * @param string $obj_status the entity status
     */
    public function setObj_status($obj_status)
    {
        $this->obj_status = $obj_status;
    }

    /**
     * get the status of the entity
     *
     * @return string the entity status
     */
    public function getObj_status()
    {
        return $this->obj_status;
    }
}
