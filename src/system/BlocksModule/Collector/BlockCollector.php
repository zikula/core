<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Collector;

use Zikula\BlocksModule\BlockHandlerInterface;

/**
 * Class BlockCollector
 * @package Zikula\BlocksModule\Collector
 */
class BlockCollector
{
    /**
     * @var array ['service.id' => ServiceObject]
     */
    private $blocks;

    public function __construct()
    {
        $this->blocks = [];
    }

    /**
     * Add a block to the collection.
     * @param $id
     * @param BlockHandlerInterface $block
     */
    public function add($id, BlockHandlerInterface $block)
    {
        $this->blocks[$id] = $block;
    }

    /**
     * Get a block from the collection by service.id.
     * @param $id
     * @return null
     */
    public function get($id)
    {
        return isset($this->blocks[$id]) ? $this->blocks[$id] : null;
    }

    /**
     * Get all the blocks in the collection.
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocks;
    }
}
