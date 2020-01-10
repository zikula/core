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

namespace Zikula\BlocksModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * BlockPlacement entity class.
 *
 * @ORM\Entity
 * @ORM\Table(name="block_placements",indexes={@ORM\Index(name="bid_pid_idx",columns={"bid","pid"})})
 *
 * @ORM\HasLifecycleCallbacks
 */
class BlockPlacementEntity extends EntityAccess
{
    /**
     * The id of the block postion
     *
     * @var BlockPositionEntity
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Zikula\BlocksModule\Entity\BlockPositionEntity", inversedBy="placements")
     * @ORM\JoinColumn(name="pid", referencedColumnName="pid", nullable=false)
     */
    private $position;

    /**
     * The id of the block
     *
     * @var BlockEntity
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Zikula\BlocksModule\Entity\BlockEntity", inversedBy="placements")
     * @ORM\JoinColumn(name="bid", referencedColumnName="bid", nullable=false)
     */
    private $block;

    /**
     * The sort order of the block within the position
     *
     * @ORM\Column(type="integer")
     * @var int
     */
    private $sortorder;

    public function __construct()
    {
        $this->sortorder = 0;
    }

    public function getPosition(): BlockPositionEntity
    {
        return $this->position;
    }

    public function setPosition(BlockPositionEntity $position = null): self
    {
        if (null !== $this->position) {
            $this->position->removePlacement($this);
        }

        if (null !== $position) {
            $position->addPlacement($this);
        }

        $this->position = $position;

        return $this;
    }

    public function getBlock(): BlockEntity
    {
        return $this->block;
    }

    public function setBlock(BlockEntity $block = null): self
    {
        if (null !== $this->block) {
            $this->block->removePlacement($this);
        }

        if (null !== $block) {
            $block->addPlacement($this);
        }

        $this->block = $block;

        return $this;
    }

    public function getSortorder(): int
    {
        return $this->sortorder;
    }

    public function setSortorder(int $sortorder): void
    {
        $this->sortorder = $sortorder;
    }

    /**
     * @ORM\PreRemove
     */
    public function preRemoveCallback(): void
    {
        $this->setPosition();
        $this->setBlock();
    }
}
