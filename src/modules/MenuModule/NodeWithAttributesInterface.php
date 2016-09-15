<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule;

use Knp\Menu\NodeInterface;

/**
 * Interface implemented by a node to construct a menu from a tree.
 */
interface NodeWithAttributesInterface extends NodeInterface
{
    /**
     * Get the attributes to be added to this node
     *
     * @return array
     */
    public function getAttributes();

    /**
     * @return boolean
     */
    public function hasAttributes();
}
