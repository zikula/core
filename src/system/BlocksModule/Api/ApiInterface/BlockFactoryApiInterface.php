<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Api\ApiInterface;

use Zikula\BlocksModule\BlockHandlerInterface;

/**
 * Class BlockFactoryApiInterface
 */
interface BlockFactoryApiInterface
{
    /**
     * Factory method to create an instance of a block given its name and the providing module instance.
     * Given block class needs to implement Zikula\BlocksModule\BlockHandlerInterface.
     *
     * @param string $blockClassName
     *
     * @return BlockHandlerInterface
     */
    public function getInstance($blockClassName);
}
