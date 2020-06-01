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

namespace Zikula\CategoriesModule\Traits;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * Standard fields trait.
 */
trait StandardFieldsTrait
{
    /**
     * The user id of the creator of this entity
     *
     * @var UserEntity
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="cr_uid", referencedColumnName="uid")
     */
    protected $createdBy;

    /**
     * The creation timestamp of this entity
     *
     * @var DateTimeInterface
     * @ORM\Column(type="datetime", name="cr_date")
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdDate;

    /**
     * The user id of the last update of this entity
     *
     * @var UserEntity
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="lu_uid", referencedColumnName="uid")
     */
    protected $updatedBy;

    /**
     * The last updated timestamp of this entity
     *
     * @var DateTimeInterface
     * @ORM\Column(type="datetime", name="lu_date")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedDate;

    public function getCreatedBy(): UserEntity
    {
        return $this->createdBy;
    }

    public function setCreatedBy(UserEntity $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getCreatedDate(): DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(DateTimeInterface $createdDate): void
    {
        $this->createdDate = $createdDate;
    }

    public function getUpdatedBy(): UserEntity
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(UserEntity $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }

    public function getUpdatedDate(): DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(DateTimeInterface $updatedDate): void
    {
        $this->updatedDate = $updatedDate;
    }
}
