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

namespace Zikula\CategoriesBundle\Traits;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zikula\UsersBundle\Entity\UserEntity;

trait StandardFieldsTrait
{
    /**
     * The user id of the creator of this entity
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'cr_uid', referencedColumnName: 'uid')]
    #[Gedmo\Blameable(on: 'create')]
    protected ?UserEntity $createdBy = null;

    /**
     * The creation timestamp of this entity
     */
    #[ORM\Column(name: 'cr_date', type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    protected ?DateTimeInterface $createdDate = null;

    /**
     * The user id of the last update of this entity
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'lu_uid', referencedColumnName: 'uid')]
    #[Gedmo\Blameable(on: 'update')]
    protected ?UserEntity $updatedBy = null;

    /**
     * The last updated timestamp of this entity
     */
    #[ORM\Column(name: 'lu_date', type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    protected ?DateTimeInterface $updatedDate = null;

    public function getCreatedBy(): ?UserEntity
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?UserEntity $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedDate(): ?DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(?DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedBy(): ?UserEntity
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?UserEntity $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedDate(): ?DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(?DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }
}
