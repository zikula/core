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

namespace Zikula\CategoriesModule\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Core\Doctrine\EntityAccess;
use Zikula\UsersModule\Entity\UserEntity;

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
     * @var int
     */
    private $id;

    /**
     * The module name owning this entry
     *
     * @ORM\Column(type="string", length=60)
     * @Assert\Length(min="0", max="60", allowEmptyString="false")
     * @var string
     */
    private $modname;

    /**
     * The name of the entity
     *
     * @ORM\Column(type="string", length=60)
     * @Assert\Length(min="0", max="60", allowEmptyString="false")
     * @var string
     */
    private $entityname;

    /**
     * The property of the entity
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="0", max="255", allowEmptyString="false")
     * @var string
     */
    private $property;

    /**
     * The category to map this entity to
     *
     * @ORM\ManyToOne(targetEntity="CategoryEntity", inversedBy="attributes")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @var CategoryEntity
     */
    private $category;

    /**
     * The user id of the creator of this entity
     *
     * @var UserEntity
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="cr_uid", referencedColumnName="uid")
     */
    protected $cr_uid;

    /**
     * The user id of the last update of this entity
     *
     * @var UserEntity
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="lu_uid", referencedColumnName="uid")
     */
    protected $lu_uid;

    /**
     * The creation timestamp of this entity
     *
     * @var DateTime
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $cr_date;

    /**
     * The last updated timestamp of this entity
     *
     * @var DateTime
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $lu_date;

    /**
     * The status of the entity
     *
     * @ORM\Column(type="string", length=1)
     * @Assert\Length(min="0", max="1", allowEmptyString="false")
     * @var string
     */
    protected $obj_status = 'A';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getModname(): ?string
    {
        return $this->modname;
    }

    public function setModname(string $modname): void
    {
        $this->modname = $modname;
    }

    public function getEntityname(): ?string
    {
        return $this->entityname;
    }

    public function setEntityname(string $entityname): void
    {
        $this->entityname = $entityname;
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }

    public function setProperty(string $property): void
    {
        $this->property = $property;
    }

    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(CategoryEntity $category): void
    {
        $this->category = $category;
    }

    public function getCr_date(): DateTime
    {
        return $this->cr_date;
    }

    public function setCr_date(DateTime $cr_date): void
    {
        $this->cr_date = $cr_date;
    }

    public function getCr_uid(): UserEntity
    {
        return $this->cr_uid;
    }

    public function setCr_uid(UserEntity $cr_uid): void
    {
        $this->cr_uid = $cr_uid;
    }

    public function getLu_date(): DateTime
    {
        return $this->lu_date;
    }

    public function setLu_date(DateTime $lu_date): void
    {
        $this->lu_date = $lu_date;
    }

    public function getLu_uid(): UserEntity
    {
        return $this->lu_uid;
    }

    public function setLu_uid(UserEntity $lu_uid): void
    {
        $this->lu_uid = $lu_uid;
    }

    public function getObj_status(): string
    {
        return $this->obj_status;
    }

    public function setObj_status(string $obj_status): void
    {
        $this->obj_status = $obj_status;
    }
}
