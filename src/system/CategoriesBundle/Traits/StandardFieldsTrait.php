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
use Zikula\UsersBundle\Entity\User;

trait StandardFieldsTrait
{
    /**
     * The user id of the creator of this entity
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'createdBy', referencedColumnName: 'id')]
    #[Gedmo\Blameable(on: 'create')]
    protected ?User $createdBy = null;

    /**
     * The creation timestamp of this entity
     */
    #[ORM\Column(name: 'createdDate', type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    protected ?DateTimeInterface $createdDate = null;

    /**
     * The user id of the last update of this entity
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'updatedBy', referencedColumnName: 'id')]
    #[Gedmo\Blameable(on: 'update')]
    protected ?User $updatedBy = null;

    /**
     * The last updated timestamp of this entity
     */
    #[ORM\Column(name: 'updatedDate', type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    protected ?DateTimeInterface $updatedDate = null;

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
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

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): self
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
