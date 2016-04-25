<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Zikula\BlocksModule\Entity\BlockPositionEntity", inversedBy="placements")
     * @ORM\JoinColumn(name="pid", referencedColumnName="pid", nullable=false)
     */
    private $position;

    /**
     * The id of the block
     *
     * @var \Zikula\BlocksModule\Entity\BlockEntity
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Zikula\BlocksModule\Entity\BlockEntity", inversedBy="placements")
     * @ORM\JoinColumn(name="bid", referencedColumnName="bid", nullable=false)
     */
    private $block;

    /**
     * The sort order of the block within the position
     *
     * @ORM\Column(type="integer")
     */
    private $sortorder;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->sortorder = 0;
    }

    /**
     * @return BlockPositionEntity
     */
    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition(BlockPositionEntity $position = null)
    {
        if ($this->position !== null) {
            $this->position->removePlacement($this);
        }

        if ($position !== null) {
            $position->addPlacement($this);
        }

        $this->position = $position;

        return $this;
    }

    /**
     * @return BlockEntity
     */
    public function getBlock()
    {
        return $this->block;
    }

    public function setBlock(BlockEntity $block = null)
    {
        if ($this->block !== null) {
            $this->block->removePlacement($this);
        }

        if ($block !== null) {
            $block->addPlacement($this);
        }

        $this->block = $block;

        return $this;
    }

    /**
     * get the sortorder of the placement
     *
     * @return integer the placement
     */
    public function getSortorder()
    {
        return $this->sortorder;
    }

    /**
     * set the sortorder for the placement
     *
     * @param integer $sortorder the placement
     */
    public function setSortorder($sortorder)
    {
        $this->sortorder = $sortorder;
    }

    /**
     * @ORM\PreRemove
     */
    public function preRemoveCallback()
    {
        $this->setPosition(null);
        $this->setBlock(null);
    }

    /**
     * @deprecated
     * @return mixed
     */
    public function getPid()
    {
        return $this->position->getPid();
    }

    /**
     * @deprecated
     * @return int
     */
    public function getBid()
    {
        return $this->block->getBid();
    }
}
