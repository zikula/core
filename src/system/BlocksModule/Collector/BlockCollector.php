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

namespace Zikula\BlocksModule\Collector;

use Zikula\BlocksModule\BlockHandlerInterface;

/**
 * Class BlockCollector
 */
class BlockCollector
{
    /**
     * @var array
     */
    private $blocks;

    /**
     * @param BlockHandlerInterface[] $blocks
     */
    public function __construct(iterable $blocks = [])
    {
        $this->blocks = [];
        foreach ($blocks as $block) {
            $this->add($block);
        }
    }

    /**
     * Add a block to the collection.
     */
    public function add(BlockHandlerInterface $block): void
    {
        $this->blocks[get_class($block)] = $block;
    }

    /**
     * Get a block from the collection by service.id.
     */
    public function get(string $id): ?BlockHandlerInterface
    {
        return $this->blocks[$id] ?? null;
    }

    /**
     * Get all the blocks in the collection.
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }
}
