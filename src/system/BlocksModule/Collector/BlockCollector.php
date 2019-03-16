<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
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
     * Constructor.
     *
     * @param BlockHandlerInterface[] $blocks
     */
    public function __construct(iterable $blocks)
    {
        $this->blocks = [];
        foreach ($blocks as $block) {
            $this->add($block);
        }
    }

    /**
     * Add a block to the collection.
     *
     * @param BlockHandlerInterface $block
     */
    public function add(BlockHandlerInterface $block)
    {
        $id = str_replace('\\', '_', get_class($block));

        $this->blocks[$id] = $block;
    }

    /**
     * Get a block from the collection by service.id.
     *
     * @param $id
     * @return null
     */
    public function get($id)
    {
        return isset($this->blocks[$id]) ? $this->blocks[$id] : null;
    }

    /**
     * Get all the blocks in the collection.
     *
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocks;
    }
}
