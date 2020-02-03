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

use DateTime;
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
     * @Assert\Length(min="0", max="255", allowEmptyString="false")
     * @var string
     */
    private $bkey;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="0", max="255", allowEmptyString="false")
     * @var string
     */
    private $blocktype;

    /**
     * The block title
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="0", max="255", allowEmptyString="true")
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
     * @Assert\Length(min="0", max="30", allowEmptyString="true")
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

    public function setBid(int $bid): void
    {
        $this->bid = $bid;
    }

    public function getBkey(): string
    {
        return $this->bkey;
    }

    public function setBkey(string $bkey): void
    {
        $this->bkey = $bkey;
    }

    public function getBlocktype(): string
    {
        return $this->blocktype;
    }

    public function setBlocktype(string $blocktype): void
    {
        $this->blocktype = $blocktype;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties = []): void
    {
        $this->properties = $properties;
    }

    public function getModule(): ExtensionEntity
    {
        return $this->module;
    }

    public function setModule(ExtensionEntity $module): void
    {
        $this->module = $module;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters = []): void
    {
        $this->filters = $filters;
    }

    public function getActive(): int
    {
        return $this->active;
    }

    public function setActive(int $active): void
    {
        $this->active = $active;
    }

    public function getLastUpdate(): DateTime
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(): void
    {
        $this->lastUpdate = new DateTime('now');
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function getPlacements(): Collection
    {
        return $this->placements;
    }

    public function addPlacement(BlockPlacementEntity $placement): void
    {
        if (!$this->placements->contains($placement)) {
            $this->placements->add($placement);
        }
    }

    public function removePlacement(BlockPlacementEntity $placement): void
    {
        if ($this->placements->contains($placement)) {
            $this->placements->removeElement($placement);
        }
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
    public function setPositions(ArrayCollection $positions): void
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
    }
}
