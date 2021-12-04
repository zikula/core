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

namespace Zikula\BlocksModule\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

/**
 * BlockPosition entity class.
 *
 * @ORM\Entity(repositoryClass="Zikula\BlocksModule\Entity\Repository\BlockPositionRepository")
 * @ORM\Table(name="block_positions",indexes={@ORM\Index(name="name_idx",columns={"name"})})
 */
class BlockPositionEntity extends EntityAccess
{
    /**
     * The id of the block postion
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $pid;

    /**
     * The name of the block position
     *
     * @Assert\Regex("/^[a-zA-Z0-9\-\_]+$/")
     * @Assert\Length(max="255")
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $name;

    /**
     * The description of the block position
     *
     * @Assert\Length(max="255")
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $description;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Zikula\BlocksModule\Entity\BlockPlacementEntity",
     *     mappedBy="position",
     *     cascade={"remove"}
     * )
     * @ORM\OrderBy({"sortorder" = "ASC"})
     */
    private $placements;

    public function __construct()
    {
        $this->name = '';
        $this->description = '';
        $this->placements = new ArrayCollection();
    }

    public function getPid(): ?int
    {
        return $this->pid;
    }

    public function setPid(int $pid): self
    {
        $this->pid = $pid;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPlacements(): Collection
    {
        return $this->placements;
    }

    public function addPlacement(BlockPlacementEntity $placement): self
    {
        if (!$this->placements->contains($placement)) {
            $this->placements->add($placement);
        }

        return $this;
    }

    public function removePlacement(BlockPlacementEntity $placement): self
    {
        if ($this->placements->contains($placement)) {
            $this->placements->removeElement($placement);
        }

        return $this;
    }
}
