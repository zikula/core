<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Response
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
