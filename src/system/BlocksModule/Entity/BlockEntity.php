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

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\BlocksModule\Api\BlockApi;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

/**
 * Block entity class.
 *
 * @ORM\Entity(repositoryClass="Zikula\BlocksModule\Entity\Repository\BlockRepository")
 * @ORM\Table(name="blocks",indexes={@ORM\Index(name="active_idx",columns={"active"})})
 */
class BlockEntity extends EntityAccess
{
    /**
     * The block id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $bid;

    /**
     * The block key
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    private $bkey;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    private $blocktype;

    /**
     * The block title
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\AtLeastOneOf(
     *     @Assert\Blank(),
     *     @Assert\Length(min="1", max="255")
     * )
     * @var string
     */
    private $title;

    /**
     * The block description
     *
     * @ORM\Column(type="text")
     * @var string
     */
    private $description;

    /**
     * A serialized array of block properties
     *
     * @ORM\Column(type="array")
     * @var string
     */
    private $properties;

    /**
     * The id of the module owning the block
     *
     * @var ExtensionEntity
     * @ORM\ManyToOne(targetEntity="Zikula\ExtensionsModule\Entity\ExtensionEntity")
     * @ORM\JoinColumn(name="mid", referencedColumnName="id")
     */
    private $module;

    /**
     * The display filter to apply to the block
     *
     * @ORM\Column(name="filter", type="array")
     */
    private $filters;

    /**
     * The active status of the block
     *
     * @ORM\Column(type="integer")
     * @var int
     */
    private $active;

    /**
     * The last updated timestamp of the block
     *
     * @ORM\Column(type="datetime", name="last_update")
     */
    private $lastUpdate;

    /**
     * The language of the block
     *
     * @ORM\Column(type="string", length=30)
     * @Assert\AtLeastOneOf(
     *     @Assert\Blank(),
     *     @Assert\Length(min="1", max="30")
     * )
     * @var string
     */
    private $language;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Zikula\BlocksModule\Entity\BlockPlacementEntity",
     *     mappedBy="block",
     *     cascade={"remove", "persist"},
     *     orphanRemoval=true)
     */
    private $placements;

    public function __construct()
    {
        $this->bkey = '';
        $this->title = '';
        $this->description = '';
        $this->blocktype = '';
        $this->properties = [];
        $this->module = 0;
        $this->filters = [];
        $this->active = BlockApi::BLOCK_ACTIVE;
        $this->lastUpdate = new DateTime('now');
        $this->language = '';
        $this->placements = new ArrayCollection();
    }

    public function getBid(): ?int
    {
        return $this->bid;
    }

    public function setBid(int $bid): self
    {
        $this->bid = $bid;

        return $this;
    }

    public function getBkey(): string
    {
        return $this->bkey;
    }

    public function setBkey(string $bkey): self
    {
        $this->bkey = $bkey;

        return $this;
    }

    public function getBlocktype(): string
    {
        return $this->blocktype;
    }

    public function setBlocktype(string $blocktype): self
    {
        $this->blocktype = $blocktype;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties = []): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function getModule(): ExtensionEntity
    {
        return $this->module;
    }

    public function setModule(ExtensionEntity $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters = []): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function getActive(): int
    {
        return $this->active;
    }

    public function setActive(int $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getLastUpdate(): DateTimeInterface
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(): self
    {
        $this->lastUpdate = new DateTime('now');

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

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

    public function getPositions(): ArrayCollection
    {
        $positions = new ArrayCollection();
        foreach ($this->getPlacements() as $placement) {
            $positions->add($placement->getPosition());
        }

        return $positions;
    }

    /**
     * Set BlockPlacementsEntity from provided ArrayCollection of positionEntity
     * requires
     *   cascade={"remove, "persist"}
     *   orphanRemoval=true
     *   on the association of $this->placements
     */
    public function setPositions(ArrayCollection $positions): self
    {
        // remove placements and skip existing placements.
        foreach ($this->placements as $placement) {
            if (!$positions->contains($placement->getPosition())) {
                $this->placements->removeElement($placement);
            } else {
                // remove from positions to add
                $positions->removeElement($placement->getPosition());
            }
        }

        // add new placements
        foreach ($positions as $position) {
            $placement = new BlockPlacementEntity();
            $placement->setPosition($position);
            // sortorder is irrelevant at this stage.
            $placement->setBlock($this); // auto-adds placement
        }

        return $this;
    }
}
